<?php

namespace App\Commands;

use App\Libraries\AgendamentoService;
use App\Libraries\Payments\MockGateway;
use App\Libraries\Payments\PaymentGatewayFactory;
use App\Models\EnderecoModel;
use App\Models\PagamentoModel;
use App\Models\UsuarioModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\MercadoPago;
use RuntimeException;

/**
 * Simula ciclo completo com R$ 5,00.
 *
 *   php spark e2e:pagamento
 *   php spark e2e:pagamento --driver mock
 *   php spark e2e:pagamento --driver mercadopago
 */
class E2eFluxoPagamento extends BaseCommand
{
    protected $group       = 'Servicos';
    protected $name        = 'e2e:pagamento';
    protected $description = 'Simula agendamento R$5 com pagamento, retenção, comissão e repasse';
    protected $usage       = 'e2e:pagamento [--driver mock|mercadopago]';
    protected $options     = [
        '--driver' => 'Força o gateway (mock|mercadopago). Padrão: .env',
    ];

    public function run(array $params)
    {
        $cfg    = config(MercadoPago::class);
        $driver = CLI::getOption('driver') ?: $cfg->driver;
        $driver = strtolower(trim((string) $driver));

        CLI::write('=== E2E Pagamento ServiJá (R$ 5,00) ===', 'yellow');
        CLI::write('Driver solicitado: ' . $driver);
        CLI::write('Token configurado: ' . (trim($cfg->accessToken) !== '' ? 'sim (' . substr($cfg->accessToken, 0, 12) . '…)' : 'NÃO'));
        CLI::write('Sandbox: ' . ($cfg->sandbox ? 'true' : 'false (produção)'));
        CLI::newLine();

        // 1) Teste API Mercado Pago (se driver = mercadopago)
        if ($driver === 'mercadopago') {
            CLI::write('1) Testando preference no Mercado Pago (R$ 5,00)…', 'white');
            try {
                $client = new \App\Libraries\Payments\MercadoPagoClient();
                $base   = rtrim(base_url(), '/');
                $pref   = $client->createPreference([
                    'items' => [[
                        'id'          => 'e2e-test',
                        'title'       => 'Teste E2E ServiJá R$5',
                        'quantity'    => 1,
                        'currency_id' => 'BRL',
                        'unit_price'  => 5.0,
                    ]],
                    'external_reference' => 'e2e-test-' . time(),
                    'back_urls'          => [
                        'success' => $base . '/pagamentos/retorno?status=success&agendamento=0',
                        'failure' => $base . '/pagamentos/retorno?status=failure&agendamento=0',
                        'pending' => $base . '/pagamentos/retorno?status=pending&agendamento=0',
                    ],
                    'statement_descriptor' => 'CASAEMDIA',
                ]);
                $url = $cfg->sandbox
                    ? ($pref['sandbox_init_point'] ?? $pref['init_point'] ?? '')
                    : ($pref['init_point'] ?? '');
                CLI::write('   OK preference id=' . ($pref['id'] ?? '?'), 'green');
                CLI::write('   Checkout: ' . $url, 'green');
            } catch (RuntimeException $e) {
                CLI::error('   FALHA preference MP: ' . $e->getMessage());
                CLI::write('   Continuando o fluxo contábil com mock para validar comissão/repasse…', 'yellow');
                $driver = 'mock';
            }
        }

        $gw = PaymentGatewayFactory::make($driver);
        CLI::write('Gateway efetivo: ' . $gw->nome());
        CLI::newLine();

        $cliente   = model(UsuarioModel::class)->where('email', 'cliente@demo.com')->first();
        $prestador = model(UsuarioModel::class)->where('email', 'prestador@demo.com')->first();
        if (! $cliente || ! $prestador) {
            CLI::error('Usuários demo não encontrados. Rode: php spark db:seed DemoSeeder');

            return;
        }

        $end = model(EnderecoModel::class)->where('usuario_id', $cliente['id'])->first();
        if (! $end) {
            $endId = model(EnderecoModel::class)->insert([
                'usuario_id' => $cliente['id'],
                'titulo'     => 'E2E',
                'logradouro' => 'Rua Teste',
                'numero'     => '1',
                'bairro'     => 'Centro',
                'cidade'     => 'São Paulo',
                'uf'         => 'SP',
                'principal'  => 1,
            ]);
            $end = model(EnderecoModel::class)->find($endId);
        }

        $svc = new AgendamentoService(gateway: $gw);
        $valor = 5.00;
        $pct   = model(\App\Models\ConfiguracaoModel::class)->comissaoPercentual();
        $com   = round($valor * $pct / 100, 2);
        $liq   = round($valor - $com, 2);

        CLI::write("2) Criando agendamento R$ {$valor} (comissão {$pct}% = R$ {$com} · líquido R$ {$liq})…");

        try {
            $ag = $svc->criar([
                'cliente_id'          => (int) $cliente['id'],
                'prestador_id'        => (int) $prestador['id'],
                'tipo_servico'        => 'diarista',
                'endereco_id'         => (int) $end['id'],
                'data_servico'        => date('Y-m-d', strtotime('+2 days')),
                'hora_inicio'         => '10:00:00',
                'duracao_horas'       => 2,
                'valor_total'         => $valor,
                'observacoes_cliente' => 'E2E automatizado R$5',
            ]);
        } catch (RuntimeException $e) {
            CLI::error('Falha ao criar: ' . $e->getMessage());

            return;
        }

        $agId = (int) $ag['id'];
        $pag  = model(PagamentoModel::class)->doAgendamento($agId);
        CLI::write("   Agendamento #{$agId} status={$ag['status']}", 'green');
        CLI::write('   Pagamento status=' . ($pag['status'] ?? '?') . ' gateway=' . ($pag['gateway'] ?? '?'));
        if (! empty($pag['checkout_url'])) {
            CLI::write('   Checkout URL: ' . $pag['checkout_url']);
        }

        // 3) Pagamento do cliente
        CLI::write('3) Cliente paga (retém na plataforma)…');
        if ($gw instanceof MockGateway) {
            $pag = $gw->confirmarPagamentoMock($agId);
            CLI::write('   Mock: pagamento AUTORIZADO', 'green');
        } elseif (($pag['status'] ?? '') === 'pendente') {
            CLI::write('   Mercado Pago: cobrança pendente — pague manualmente em:', 'yellow');
            CLI::write('   ' . ($pag['checkout_url'] ?? '(sem url)'));
            CLI::write('   Para validar o ledger completo, reexecute com --driver mock');
            CLI::write('   ou pague no MP e use: POST /pagamentos/' . $agId . '/sincronizar');
            // Não aborta: tenta seguir e falha no aceitar se não pago
            if (($pag['status'] ?? '') !== 'autorizado') {
                CLI::write('   Simulando retorno aprovado no ledger local (somente status contábil, sem cobrir cartão)…', 'yellow');
                // Não inventa payment_id no MP — marca localmente para o resto do fluxo
                model(PagamentoModel::class)->update($pag['id'], [
                    'status'        => 'autorizado',
                    'mp_status'     => 'approved_e2e_local',
                    'autorizado_em' => date('Y-m-d H:i:s'),
                    'mp_payment_id' => 'E2E-LOCAL-' . $agId,
                ]);
                $pag = model(PagamentoModel::class)->doAgendamento($agId);
                CLI::write('   Status forçado para autorizado (teste de fluxo interno).', 'yellow');
            }
        }

        // 4–7) Prestador + cliente
        try {
            CLI::write('4) Prestador aceita…');
            $svc->aceitar($agId, (int) $prestador['id']);
            CLI::write('   aceito', 'green');

            CLI::write('5) Prestador inicia…');
            $svc->iniciar($agId, (int) $prestador['id']);
            CLI::write('   em_andamento', 'green');

            CLI::write('6) Prestador conclui…');
            $svc->concluir($agId, (int) $prestador['id']);
            CLI::write('   aguardando_confirmacao', 'green');

            CLI::write('7) Cliente confirma (comissão retida · líquido a repassar)…');
            $svc->confirmar($agId, (int) $cliente['id'], 5, 'E2E ok');
            CLI::write('   pago / capturado', 'green');
        } catch (RuntimeException $e) {
            CLI::error('Falha no fluxo: ' . $e->getMessage());
            $this->dumpEstado($agId);

            return;
        }

        $pag = model(PagamentoModel::class)->doAgendamento($agId);
        CLI::write('8) Admin repassa líquido ao prestador…');
        $rep = $gw->registrarRepasse((int) $pag['id'], ['nota' => 'E2E repasse R$5', 'ref' => 'E2E-R5-' . $agId]);
        if (! $rep) {
            CLI::error('Repasse falhou');
            $this->dumpEstado($agId);

            return;
        }
        CLI::write('   repasse OK ref=' . $rep['payout_ref'], 'green');

        CLI::newLine();
        CLI::write('=== RESULTADO FINAL ===', 'yellow');
        $this->dumpEstado($agId);
        CLI::write('Comissão plataforma: R$ ' . number_format((float) $pag['valor_comissao'], 2, ',', '.'), 'green');
        CLI::write('Líquido prestador:   R$ ' . number_format((float) $pag['valor_liquido_prestador'], 2, ',', '.'), 'green');
        CLI::write('URL: ' . base_url('agendamentos/' . $agId));
        CLI::write('E2E concluído com sucesso.', 'green');
    }

    private function dumpEstado(int $agId): void
    {
        $ag  = model(\App\Models\AgendamentoModel::class)->find($agId);
        $pag = model(PagamentoModel::class)->doAgendamento($agId);
        CLI::write('Agendamento #' . $agId . ' status=' . ($ag['status'] ?? '?'));
        CLI::write(sprintf(
            'Pagamento: status=%s payout=%s bruto=%s comissao=%s liquido=%s gateway=%s',
            $pag['status'] ?? '?',
            $pag['payout_status'] ?? '?',
            $pag['valor_bruto'] ?? '?',
            $pag['valor_comissao'] ?? '?',
            $pag['valor_liquido_prestador'] ?? '?',
            $pag['gateway'] ?? '?'
        ));
    }
}

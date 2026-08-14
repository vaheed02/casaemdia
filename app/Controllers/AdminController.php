<?php

namespace App\Controllers;

use App\Libraries\AgendamentoService;
use App\Libraries\Payments\PaymentGatewayFactory;
use App\Models\ConfiguracaoModel;
use App\Models\PagamentoModel;
use App\Models\UsuarioModel;
use Config\MercadoPago;
use RuntimeException;

class AdminController extends BaseController
{
    public function usuarios(): string
    {
        $this->exigeRole('admin');

        return $this->renderPage('pages/admin/usuarios', [
            'title'      => 'Usuários',
            'activePage' => 'admin-usuarios',
            'usuarios'   => model(UsuarioModel::class)->orderBy('nome')->findAll(),
        ]);
    }

    public function pagamentos(): string
    {
        $this->exigeRole('admin');
        $mp = config(MercadoPago::class);

        return $this->renderPage('pages/admin/pagamentos', [
            'title'      => 'Pagamentos',
            'activePage' => 'admin-pagamentos',
            'lista'      => model(PagamentoModel::class)->listarComDetalhes(),
            'totais'     => model(PagamentoModel::class)->totaisPlataforma(),
            'aRepassar'  => model(PagamentoModel::class)->aRepassar(),
            'aLiberar'   => model(PagamentoModel::class)->aguardandoLiberacao(),
            'mpDriver'   => $mp->driver,
            'mpOk'       => $mp->isConfigured(),
        ]);
    }

    public function comissoes(): string
    {
        $this->exigeRole('admin');
        $cfg = model(ConfiguracaoModel::class);

        return $this->renderPage('pages/admin/comissoes', [
            'title'      => 'Comissões da plataforma',
            'activePage' => 'admin-comissao',
            'totais'     => model(PagamentoModel::class)->totaisPlataforma(),
            'lista'      => model(PagamentoModel::class)->listarComDetalhes('capturado'),
            'pct'        => $cfg->comissaoPercentual(),
        ]);
    }

    public function salvarComissao(): mixed
    {
        $this->exigeRole('admin');

        $pct = (float) str_replace(',', '.', (string) $this->request->getPost('comissao_percentual'));
        if ($pct < 0 || $pct > 90) {
            return redirect()->back()->with('error', 'Informe um percentual entre 0 e 90.');
        }

        model(ConfiguracaoModel::class)->salvarValor('comissao_percentual', (string) $pct);

        return redirect()->to(base_url('admin/comissoes'))
            ->with('success', 'Comissão padrão atualizada para ' . number_format($pct, 2, ',', '.') . '%. Novos agendamentos usarão este valor.');
    }

    /**
     * Admin confirma repasse do líquido ao prestador (dinheiro saiu da conta MP da plataforma).
     */
    public function repassar(int $pagamentoId): mixed
    {
        $this->exigeRole('admin');

        $nota = trim((string) $this->request->getPost('nota'));
        $ref  = trim((string) $this->request->getPost('ref'));

        try {
            $pag = PaymentGatewayFactory::make()->registrarRepasse($pagamentoId, [
                'nota' => $nota !== '' ? $nota : null,
                'ref'  => $ref !== '' ? $ref : null,
            ]);
            if ($pag === null) {
                return redirect()->back()->with('error', 'Só é possível repassar pagamentos capturados e com líquido pendente.');
            }

            return redirect()->to(base_url('admin/pagamentos'))
                ->with('success', 'Repasse #' . $pagamentoId . ' registrado. Comissão permanece retida na plataforma.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sincronizar(int $pagamentoId): mixed
    {
        $this->exigeRole('admin');
        $pag = model(PagamentoModel::class)->find($pagamentoId);
        if ($pag === null) {
            return redirect()->back()->with('error', 'Pagamento não encontrado.');
        }

        try {
            $updated = PaymentGatewayFactory::make()->sincronizarPagamento(
                (int) $pag['agendamento_id'],
                $pag['mp_payment_id'] ?: null
            );
            $st = $updated['status'] ?? '?';
            $mp = $updated['mp_status'] ?? '';

            return redirect()->back()->with(
                'success',
                'Sincronizado com o Mercado Pago (sem precisar de webhook). Status: ' . $st
                . ($mp !== '' ? ' · MP ' . $mp : '')
                . (! empty($updated['mp_payment_id']) ? ' · pay ' . $updated['mp_payment_id'] : '')
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin confirma serviço + envia para fila de repasse (quando o cliente não confirma ou o webhook falhou).
     */
    public function liberar(int $pagamentoId): mixed
    {
        $this->exigeRole('admin');
        $pag = model(PagamentoModel::class)->find($pagamentoId);
        if ($pag === null) {
            return redirect()->back()->with('error', 'Pagamento não encontrado.');
        }

        try {
            (new AgendamentoService())->adminLiberarRepasse((int) $pag['agendamento_id']);

            return redirect()->to(base_url('admin/pagamentos'))
                ->with('success', 'Agendamento #' . $pag['agendamento_id'] . ' liberado: comissão retida e líquido na fila de repasse.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

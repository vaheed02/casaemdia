<?php

namespace App\Libraries;

use App\Libraries\Payments\PaymentGatewayFactory;
use App\Libraries\Payments\PaymentGatewayInterface;

/**
 * Fachada de compatibilidade — delega ao driver configurado (mock | mercadopago).
 *
 * @deprecated Prefira PaymentGatewayFactory::make()
 */
class PaymentGateway implements PaymentGatewayInterface
{
    private PaymentGatewayInterface $inner;

    public function __construct(?PaymentGatewayInterface $inner = null)
    {
        $this->inner = $inner ?? PaymentGatewayFactory::make();
    }

    public function nome(): string
    {
        return $this->inner->nome();
    }

    public function criarCobranca(array $dados): array
    {
        return $this->inner->criarCobranca($dados);
    }

    public function sincronizarPagamento(int $agendamentoId, ?string $mpPaymentId = null): ?array
    {
        return $this->inner->sincronizarPagamento($agendamentoId, $mpPaymentId);
    }

    public function capturar(int $agendamentoId): ?array
    {
        return $this->inner->capturar($agendamentoId);
    }

    public function estornar(int $agendamentoId): ?array
    {
        return $this->inner->estornar($agendamentoId);
    }

    public function registrarRepasse(int $pagamentoId, array $extra = []): ?array
    {
        return $this->inner->registrarRepasse($pagamentoId, $extra);
    }

    /** @deprecated use criarCobranca */
    public function autorizar(int $agendamentoId, float $valorBruto, float $comissao, float $liquido): array
    {
        return $this->criarCobranca([
            'agendamento_id' => $agendamentoId,
            'valor_bruto'    => $valorBruto,
            'comissao'       => $comissao,
            'liquido'        => $liquido,
            'descricao'      => 'Serviço #' . $agendamentoId,
        ]);
    }
}

<?php

namespace App\Libraries\Payments;

/**
 * Contrato do gateway de pagamento da plataforma.
 *
 * Modelo financeiro (marketplace com conta única da plataforma):
 * 1) Cliente paga o valor bruto via Mercado Pago → dinheiro na conta da plataforma
 * 2) Comissão fica retida (contábil) na plataforma
 * 3) Líquido do prestador fica "a repassar" até admin/sistema liberar
 */
interface PaymentGatewayInterface
{
    /**
     * Cria cobrança (preference/checkout). Retorna registro em pagamentos.
     *
     * @param array{
     *   agendamento_id:int,
     *   valor_bruto:float,
     *   comissao:float,
     *   liquido:float,
     *   descricao:string,
     *   payer_email?:string,
     *   external_reference?:string
     * } $dados
     */
    public function criarCobranca(array $dados): array;

    /**
     * Sincroniza status a partir do gateway (webhook ou consulta).
     */
    public function sincronizarPagamento(int $agendamentoId, ?string $mpPaymentId = null): ?array;

    /**
     * Contábil: marca captura (serviço confirmado) e deixa repasse pendente.
     */
    public function capturar(int $agendamentoId): ?array;

    /**
     * Estorna / cancela cobrança quando possível.
     */
    public function estornar(int $agendamentoId): ?array;

    /**
     * Marca repasse ao prestador (manual ou automático).
     *
     * @param array{nota?:string, ref?:string} $extra
     */
    public function registrarRepasse(int $pagamentoId, array $extra = []): ?array;

    public function nome(): string;
}

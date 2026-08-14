# Mercado Pago — Casa em Dia

## Modelo financeiro

1. **Cliente paga** o valor bruto no Checkout Pro (Mercado Pago).
2. O dinheiro entra na **conta MP da plataforma** (retenção).
3. Após o serviço confirmado:
   - **Comissão** fica contábil/retida na plataforma.
   - **Líquido do prestador** fica na fila de repasse.
4. **Admin** registra o repasse (PIX/transferência) em Admin → Pagamentos.

## Configuração (.env)

```env
mercadopago.driver = mercadopago
mercadopago.accessToken = APP_USR-xxxxxxxx
mercadopago.publicKey = APP_USR-xxxxxxxx
mercadopago.sandbox = true
mercadopago.statementDescriptor = CASAEMDIA
```

- **Teste:** use credenciais de teste do painel MP.
- **Produção:** `sandbox = false` e tokens de produção.
- **Nunca** envie o access token no chat — só no `.env` do servidor.

## Webhook

No painel Mercado Pago → Webhooks:

```
https://SEU_DOMINIO/webhooks/mercadopago
```

Eventos: pagamentos (`payment`).

## Fluxo no app

1. Cliente agenda serviço → gera cobrança (status `pendente`).
2. Cliente **paga**:
   - **Mercado Pago:** redireciona ao Checkout Pro.
   - **Mock:** tela local “Pagar agora (simular)”.
3. Prestador só **aceita** com pagamento `autorizado` (retido na plataforma).
4. Prestador executa → cliente **confirma serviço** → `capturado` + repasse `pendente` + comissão retida.
5. Admin **marca repassado** após transferir o líquido ao prestador.

Webhook em localhost não chega; em produção configure a URL pública.
No app, o botão **Já paguei — atualizar status** consulta o MP se o webhook atrasar.

## Comissão

- Padrão: `servicos.comissaoPercentual` ou Admin → Comissões.
- Gravada em cada agendamento no momento da criação.

## Local (sem token)

Deixe `mercadopago.driver = mock` e teste todo o fluxo sem conta MP.

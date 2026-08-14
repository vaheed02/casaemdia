# Casa em Dia — Serviços sob demanda

Marketplace web (CodeIgniter 4) baseado no `projeto_modelo`, para conectar **clientes** a **prestadores** (diaristas e passeadores) com fluxo de pagamento e comissão.

## Fluxo do negócio

1. **Cliente agenda** um serviço com um prestador  
2. **Gateway autoriza** (reserva) o valor  
3. **Prestador recebe** a solicitação e **aceita ou rejeita**  
4. Prestador **executa** o serviço (iniciar → concluir)  
5. **Cliente confirma** a execução (e pode avaliar)  
6. **Gateway captura** o pagamento  
7. **Prestador recebe** o líquido e o **app fica com a comissão** (padrão 15%)

### Status do agendamento

`pendente` → `aceito` | `rejeitado` | `cancelado`  
`aceito` → `em_andamento` → `aguardando_confirmacao` → `pago` (após confirmação + captura)

## Requisitos

- PHP 8.2+
- MySQL (Laragon)
- Composer (vendor já incluso na cópia)

## Instalação

1. Banco já pode ser criado assim:

```sql
CREATE DATABASE IF NOT EXISTS servicos_app CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

2. Confirme o `.env` (base URL e banco `servicos_app`).

3. Migrations + seed:

```bash
cd C:\laragon\www\servicos_app
php spark migrate
php spark db:seed DemoSeeder
```

4. Acesse: **http://localhost/servicos_app/login**

   Ou: `iniciar-dev.bat` → **http://localhost:8080/login**

### Contas de demonstração

| Perfil     | E-mail                 | Senha    |
|-----------|------------------------|----------|
| Admin     | admin@demo.com         | demo123  |
| Cliente   | cliente@demo.com       | demo123  |
| Diarista  | prestador@demo.com     | demo123  |
| Passeadora| passeadora@demo.com    | demo123  |

## Estrutura principal

| Caminho | Descrição |
|---------|-----------|
| `app/Libraries/AgendamentoService.php` | Máquina de estados do serviço |
| `app/Libraries/PaymentGateway.php` | Gateway mock (autorizar / capturar / estornar) |
| `app/Config/Servicos.php` | Comissão % e tipos (diarista/passeador) |
| `app/Config/Menu.php` | Menu por perfil |
| `app/Controllers/*` | Auth, catálogo, agendamentos, prestador, admin |

## Papéis

- **cliente** — busca prestadores, agenda, confirma, avalia  
- **prestador** — perfil, solicitações, execução, ganhos  
- **admin** — usuários, pagamentos, comissões  

## Comissão

Configurável em `app/Config/Servicos.php` (`$comissaoPercentual`, padrão **15%**).

No agendamento o valor é quebrado em:

- `valor_total` — pago pelo cliente  
- `comissao_valor` — fica com a plataforma  
- `valor_prestador` — líquido liberado ao prestador  

## Próximos passos sugeridos

- Integrar gateway real (Mercado Pago / Stripe) no lugar do mock  
- Notificações (e-mail / WhatsApp / push)  
- Upload de documentos e verificação de prestador  
- Chat cliente ↔ prestador  
- App mobile (API JSON reutilizando `AgendamentoService`)

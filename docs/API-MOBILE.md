# API Mobile v1 — Casa em Dia

Base: `https://SEU-DOMINIO/api/v1`  
Auth: header `Authorization: Bearer <jwt>`

## Público

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/health` | Health check |
| POST | `/auth/login` | `{ email, senha }` → token + usuario |
| POST | `/auth/register` | `{ nome, email, senha, telefone?, role: cliente\|prestador }` |
| GET | `/tipos` | Tipos de serviço + comissão |

## Autenticado

| Método | Rota | Perfis |
|--------|------|--------|
| GET | `/me` | todos |
| GET | `/dashboard` | todos (payload por role) |
| GET | `/prestadores?tipo=&cidade=` | cliente |
| GET/POST | `/enderecos` | cliente |
| DELETE | `/enderecos/{id}` | cliente |
| GET/POST | `/agendamentos` | cliente cria; listagem por role |
| GET | `/agendamentos/{id}` | dono |
| POST | `/agendamentos/{id}/{acao}` | aceitar, rejeitar, iniciar, concluir, confirmar, cancelar |
| GET | `/pagamentos/{id}` | dono |
| GET | `/pagamentos/{id}/checkout` | cliente (+ `?confirm=1` mock) |
| POST | `/pagamentos/{id}/sincronizar` | dono |
| GET | `/prestador/solicitacoes` | prestador |
| GET | `/prestador/servicos` | prestador |
| GET | `/prestador/ganhos` | prestador |
| GET/POST | `/prestador/perfil` | prestador |

## JWT

`.env`:

```env
api.jwtSecret = sua-chave-secreta-longa
```

## App

Ver pasta `C:\laragon\www\casaemdia-app` (Expo).

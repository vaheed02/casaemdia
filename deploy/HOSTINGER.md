# Deploy na Hostinger — servicos_app (CodeIgniter 4)

O front controller fica em `public/index.php`. Na Hostinger o document root é `public_html`, então o pacote inclui:

1. **Raiz** (`.htaccess`) — reescreve tudo para `public/` e bloqueia `app/`, `vendor/`, `.env`, etc.
2. **`public/.htaccess`** — rotas amigáveis + HTTPS
3. **`.env`** — produção (`CI_ENVIRONMENT = production`)

## Estrutura final em `public_html`

```
public_html/
├── .htaccess          ← da pasta deploy/hostinger
├── .env               ← produção (domínio + MySQL)
├── app/
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   └── css/
├── vendor/
├── writable/
├── composer.json
└── spark
```

**Não** coloque só o conteúdo de `public/` na raiz sem o restante do projeto — o CI4 precisa de `app/`, `vendor/` e `writable/` um nível acima de `public/`.

---

## Opção A — Pacote automático (recomendado)

No PowerShell:

```powershell
cd C:\laragon\www\servicos_app
.\deploy\empacotar-hostinger.ps1 -BaseUrl "https://seudominio.com.br/"
```

Será gerado um ZIP em `deploy\servicos_app-hostinger-....zip`.

### Na Hostinger

1. **hPanel** → **Sites** → seu domínio → **Gerenciador de arquivos**
2. Entre em **`public_html`**
3. (Opcional) Faça backup e limpe arquivos padrão (`default.php`, etc.) se o site for só este app
4. **Upload** do ZIP e **Extrair** na raiz de `public_html`
5. Confirme que `public_html/public/index.php` e `public_html/.htaccess` existem
6. Edite **`.env`**:
   - `app.baseURL` = `https://seudominio.com.br/` (com `/` no final)
   - dados do MySQL do hPanel
7. **Bancos de dados MySQL** no hPanel: crie banco + usuário e importe o schema (migrations/SQL)
8. **PHP** → versão **8.2 ou superior**
9. Pastas em `writable/` com permissão de escrita (755 ou 775)

---

## Opção B — Upload manual (FTP / File Manager)

Copie para `public_html`:

| Local (Laragon) | Destino (Hostinger) |
|-----------------|---------------------|
| `app/` | `public_html/app/` |
| `public/` | `public_html/public/` |
| `vendor/` | `public_html/vendor/` |
| `writable/` | `public_html/writable/` (vazia de logs de dev) |
| `composer.json` / `composer.lock` | `public_html/` |
| `deploy/hostinger/.htaccess` | `public_html/.htaccess` |
| `deploy/hostinger/public.htaccess` | `public_html/public/.htaccess` |
| `deploy/hostinger/.env.example` | `public_html/.env` (renomear e editar) |

**Não envie** (ou não precisa): `tests/`, `deploy/`, `.git/`, logs de `writable/`.

---

## Banco de dados

1. hPanel → **MySQL** → criar banco + usuário
2. Ajuste o `.env` com os dados exatos do painel
3. Rode as migrations (SSH, se disponível):

```bash
cd ~/domains/SEU_DOMINIO/public_html
php spark migrate
php spark db:seed UsuarioSeeder
```

Se não tiver SSH, exporte o banco do Laragon (phpMyAdmin) e importe no phpMyAdmin da Hostinger.

---

## Checklist se o site não abrir

| Sintoma | O que verificar |
|---------|-----------------|
| 500 / tela em branco | `.env` existe? PHP ≥ 8.2? `writable/` com escrita? |
| 404 em todas as rotas | `mod_rewrite` / `.htaccess` na raiz e em `public/` |
| CSS/JS quebrados | `app.baseURL` com domínio correto e `/` no final |
| Erro de banco | host/user/senha/nome do MySQL no `.env` |
| Página de listagem de pastas | Falta o `.htaccess` da raiz |

---

## Desenvolvimento local (Laragon)

Os arquivos em `deploy/hostinger/` **não alteram** o ambiente local (`RewriteBase /servicos_app/`, `.env` de development). Continue usando:

- `http://localhost/servicos_app/`
- ou `iniciar-dev.bat` → porta 8080

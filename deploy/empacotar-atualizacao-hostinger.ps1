# Pacote de ATUALIZAÇÃO (não apaga .env nem dados do banco)
# Sobrescreve app/, assets públicos, index, htaccess e scripts de upgrade.
#
# Uso:
#   cd C:\laragon\www\servicos_app
#   .\deploy\empacotar-atualizacao-hostinger.ps1

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
$Stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$OutDir = Join-Path $Root "deploy\pacotes"
$OutputZip = Join-Path $OutDir "servicos_app-UPDATE-HOSTINGER-$Stamp.zip"
$Stage = Join-Path $env:TEMP "servicos_app_update_$Stamp"

if (-not (Test-Path $OutDir)) { New-Item -ItemType Directory -Path $OutDir | Out-Null }
if (Test-Path $Stage) { Remove-Item $Stage -Recurse -Force }
New-Item -ItemType Directory -Path $Stage | Out-Null

Write-Host "=== Pacote ATUALIZACAO Hostinger (preserva .env e dados) ===" -ForegroundColor Cyan
Write-Host "Staging: $Stage"

# 1) app completo (código)
Write-Host "Copiando app/ ..."
Copy-Item (Join-Path $Root "app") (Join-Path $Stage "app") -Recurse -Force

# 2) Assets de public/ na raiz (estrutura plana)
Write-Host "Copiando assets public/ ..."
$public = Join-Path $Root "public"
Get-ChildItem $public -Force | Where-Object { $_.Name -notin @("index.php", ".htaccess") } | ForEach-Object {
    Copy-Item $_.FullName (Join-Path $Stage $_.Name) -Recurse -Force
}

# 3) Front controller + htaccess Hostinger
$H = Join-Path $Root "deploy\hostinger"
Copy-Item (Join-Path $H "index.php") (Join-Path $Stage "index.php") -Force
Copy-Item (Join-Path $H "public_html.htaccess") (Join-Path $Stage ".htaccess") -Force

# 4) Upgrade DB (seguro se coluna já existir)
Copy-Item (Join-Path $H "upgrade-db.php") (Join-Path $Stage "upgrade-db.php") -Force
Copy-Item (Join-Path $H "upgrade.sql") (Join-Path $Stage "upgrade.sql") -Force
Copy-Item (Join-Path $H "check-setup.php") (Join-Path $Stage "check-setup.php") -Force -ErrorAction SilentlyContinue

# 5) composer + spark (sem vendor — use o vendor já no servidor)
foreach ($f in @("composer.json", "composer.lock", "spark", "preload.php")) {
    $src = Join-Path $Root $f
    if (Test-Path $src) { Copy-Item $src (Join-Path $Stage $f) -Force }
}

# 6) Documentação
$leia = @"
Casa em Dia — ATUALIZAÇÃO Hostinger (pagamento + comissão)
=========================================================
Pacote: servicos_app-UPDATE-HOSTINGER-$Stamp.zip

O QUE ESTE PACOTE FAZ
---------------------
- Atualiza o código PHP (app/)
- Atualiza CSS/assets e index/.htaccess
- Inclui upgrade-db.php (colunas Mercado Pago / tipos de serviço)
- NAO sobrescreve o .env do servidor (credenciais e tokens ficam)
- NAO apaga o banco de dados

COMO APLICAR (hPanel → File Manager)
------------------------------------
1) Em public_html, faça backup rápido:
   - Baixe a pasta app/ (zip pelo File Manager)
   - Ou renomeie app → app_bak_YYYYMMDD

2) Upload deste ZIP em public_html e EXTRAIA na RAIZ
   (substitua arquivos quando o File Manager perguntar)

3) Confirme que o .env ANTIGO ainda está lá com:
   app.baseURL = 'https://SEU-DOMINIO/'
   database.default.*  (user/senha reais)
   mercadopago.driver = mercadopago
   mercadopago.accessToken = APP_USR-...
   mercadopago.publicKey = APP_USR-...
   mercadopago.sandbox = false   (se for produção)

4) Abra UMA vez no navegador:
   https://SEU-DOMINIO/upgrade-db.php
   Deve listar [OK] / [SKIP] nas colunas.

5) APAGUE do servidor (segurança):
   upgrade-db.php
   upgrade.sql
   check-setup.php  (se existir)

6) Teste:
   https://SEU-DOMINIO/login
   Cliente agenda → botão Pagar → Mercado Pago
   Webhook MP (produção):
   https://SEU-DOMINIO/webhooks/mercadopago

FLUXO DE PAGAMENTO NESTA VERSÃO
-------------------------------
1. Cliente agenda (pode informar valor, ex. 5.00)
2. Cliente paga no Mercado Pago (valor retido na plataforma)
3. Prestador aceita → executa → conclui
4. Cliente confirma → comissão retida; líquido a repassar
5. Admin → Pagamentos → repassa ao prestador

SE O VENDOR ESTIVER DESATUALIZADO
---------------------------------
Este pacote NAO inclui vendor/ (mais leve).
Se der erro de classe não encontrada, suba também o ZIP COMPLETO
servicos_app-PUBLIC_HTML-....zip (que inclui vendor).

NÃO reimporte schema.sql em site com dados reais.
"@
Set-Content -Path (Join-Path $Stage "LEIA-ME-ATUALIZACAO.txt") -Value $leia -Encoding UTF8

Copy-Item (Join-Path $Root "deploy\MERCADO_PAGO.md") (Join-Path $Stage "MERCADO_PAGO.md") -Force -ErrorAction SilentlyContinue

# ZIP
if (Test-Path $OutputZip) { Remove-Item $OutputZip -Force }
Write-Host "Compactando..."
Compress-Archive -Path (Join-Path $Stage "*") -DestinationPath $OutputZip -CompressionLevel Optimal
Remove-Item $Stage -Recurse -Force

$sizeMb = [math]::Round((Get-Item $OutputZip).Length / 1MB, 2)
Write-Host ""
Write-Host "=== ATUALIZACAO pronta ===" -ForegroundColor Green
Write-Host $OutputZip
Write-Host "Tamanho: $sizeMb MB"
Write-Host ""
Write-Host "Suba no File Manager da Hostinger em public_html e extraia (sobrescrever)."
Write-Host "Depois abra /upgrade-db.php uma vez e apague o arquivo."

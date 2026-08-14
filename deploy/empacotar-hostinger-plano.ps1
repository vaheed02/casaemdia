# Empacota CI4 em estrutura PLANA para Hostinger public_html
# (index.php na raiz — evita 404 de /index.php e rewrites em public/)
#
# Uso:
#   cd C:\laragon\www\servicos_app
#   .\deploy\empacotar-hostinger-plano.ps1

param(
    [string]$BaseUrl = "https://whitesmoke-tarsier-299141.hostingersite.com/",
    [string]$DbName = "u240559973_diarista",
    [string]$DbUser = "COLE_USUARIO_MYSQL",
    [string]$DbPass = "COLE_SENHA_MYSQL"
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
$Stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$OutDir = Join-Path $Root "deploy\pacotes"
if (-not (Test-Path $OutDir)) { New-Item -ItemType Directory -Path $OutDir | Out-Null }
$OutputZip = Join-Path $OutDir "servicos_app-PUBLIC_HTML-$Stamp.zip"
$Stage = Join-Path $env:TEMP "servicos_app_plano_$Stamp"

if (Test-Path $Stage) { Remove-Item $Stage -Recurse -Force }
New-Item -ItemType Directory -Path $Stage | Out-Null

Write-Host "Estrutura PLANA (index na raiz do public_html)"
Write-Host "Staging: $Stage"

# app, vendor, writable
foreach ($d in @("app", "vendor", "writable")) {
    Write-Host "Copiando $d ..."
    Copy-Item (Join-Path $Root $d) (Join-Path $Stage $d) -Recurse -Force
}

# limpar writable de dev
foreach ($sub in @("cache", "debugbar", "logs", "session", "uploads")) {
    $dir = Join-Path $Stage "writable\$sub"
    if (Test-Path $dir) {
        Get-ChildItem $dir -Force | Where-Object { $_.Name -ne "index.html" } | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
    }
}

# Conteúdo de public/ na RAIZ (assets, css, favicon, robots)
Write-Host "Copiando assets public/ para raiz ..."
$public = Join-Path $Root "public"
Get-ChildItem $public -Force | Where-Object { $_.Name -notin @("index.php", ".htaccess") } | ForEach-Object {
    Copy-Item $_.FullName (Join-Path $Stage $_.Name) -Recurse -Force
}

# index + htaccess de produção (planos)
$H = Join-Path $Root "deploy\hostinger"
Copy-Item (Join-Path $H "index.php") (Join-Path $Stage "index.php") -Force
Copy-Item (Join-Path $H "public_html.htaccess") (Join-Path $Stage ".htaccess") -Force
Copy-Item (Join-Path $H "schema.sql") (Join-Path $Stage "schema.sql") -Force

# composer (opcional no servidor)
foreach ($f in @("composer.json", "composer.lock", "spark", "preload.php")) {
    $src = Join-Path $Root $f
    if (Test-Path $src) { Copy-Item $src (Join-Path $Stage $f) -Force }
}

# .env produção (preencha usuario/senha MySQL e tokens MP no File Manager)
$envLines = @"
CI_ENVIRONMENT = production

app.baseURL = '$BaseUrl'

database.default.hostname = localhost
database.default.database = $DbName
database.default.username = $DbUser
database.default.password = $DbPass
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

servicos.comissaoPercentual = 15

# Mercado Pago — preencha tokens no File Manager da Hostinger
mercadopago.driver = mercadopago
mercadopago.accessToken =
mercadopago.publicKey =
mercadopago.webhookSecret =
mercadopago.sandbox = false
mercadopago.autoPayout = true
mercadopago.statementDescriptor = CASAEMDIA
"@
Set-Content -Path (Join-Path $Stage ".env") -Value $envLines -Encoding UTF8

# Upgrade DB (instalação nova já usa schema; upgrade ajuda se reimportar parcial)
Copy-Item (Join-Path $H "upgrade-db.php") (Join-Path $Stage "upgrade-db.php") -Force
Copy-Item (Join-Path $H "upgrade.sql") (Join-Path $Stage "upgrade.sql") -Force
Copy-Item (Join-Path $H "check-setup.php") (Join-Path $Stage "check-setup.php") -Force -ErrorAction SilentlyContinue

# Extras de documentação
foreach ($extra in @("MERCADO_PAGO.md", "LEIA-ME-HOSTINGER.txt")) {
    $src = Join-Path $Root "deploy\$extra"
    if (Test-Path $src) { Copy-Item $src (Join-Path $Stage $extra) -Force }
}
Copy-Item (Join-Path $H ".env.example") (Join-Path $Stage ".env.example") -Force -ErrorAction SilentlyContinue

# ZIP
if (Test-Path $OutputZip) { Remove-Item $OutputZip -Force }
Write-Host "Criando ZIP..."
Compress-Archive -Path (Join-Path $Stage "*") -DestinationPath $OutputZip -CompressionLevel Optimal
Remove-Item $Stage -Recurse -Force

Write-Host ""
Write-Host "=== PACOTE PRONTO (estrutura plana) ===" -ForegroundColor Green
Write-Host $OutputZip
Write-Host ""
Write-Host "Estrutura no public_html apos extrair:"
Write-Host "  public_html/index.php"
Write-Host "  public_html/.htaccess"
Write-Host "  public_html/.env"
Write-Host "  public_html/app/"
Write-Host "  public_html/vendor/"
Write-Host "  public_html/writable/"
Write-Host "  public_html/assets/  css/"
Write-Host "  public_html/schema.sql"
Write-Host ""
Write-Host "1) Extraia o ZIP na RAIZ de public_html (substitua arquivos antigos)"
Write-Host "2) Edite .env: username e password do MySQL"
Write-Host "3) phpMyAdmin: importe schema.sql no banco $DbName"
Write-Host "4) PHP 8.2+ | writable com permissao de escrita"
Write-Host "5) Acesse: $BaseUrl" "login"

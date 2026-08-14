# Empacota o CodeIgniter 4 (servicos_app) para upload na Hostinger (public_html)
# Uso (PowerShell):
#   cd C:\laragon\www\servicos_app
#   .\deploy\empacotar-hostinger.ps1
#   .\deploy\empacotar-hostinger.ps1 -BaseUrl "https://meusite.com.br/"

param(
    [string]$BaseUrl = "https://SEU_DOMINIO.com.br/",
    [string]$OutputZip = ""
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path (Join-Path $Root "public\index.php"))) {
    $Root = (Get-Location).Path
}

$Stamp = Get-Date -Format "yyyyMMdd-HHmmss"
if ([string]::IsNullOrWhiteSpace($OutputZip)) {
    $OutputZip = Join-Path $Root "deploy\servicos_app-hostinger-$Stamp.zip"
}

$Stage = Join-Path $env:TEMP "servicos_app_hostinger_$Stamp"
if (Test-Path $Stage) { Remove-Item $Stage -Recurse -Force }
New-Item -ItemType Directory -Path $Stage | Out-Null

Write-Host "Projeto: $Root"
Write-Host "Staging: $Stage"

# Pastas e arquivos necessários em produção
$IncludeDirs = @("app", "public", "vendor", "writable")
$IncludeFiles = @("composer.json", "composer.lock", "spark", "preload.php")

foreach ($d in $IncludeDirs) {
    $src = Join-Path $Root $d
    if (-not (Test-Path $src)) {
        throw "Pasta obrigatoria nao encontrada: $src"
    }
    Write-Host "Copiando $d ..."
    Copy-Item $src (Join-Path $Stage $d) -Recurse -Force
}

foreach ($f in $IncludeFiles) {
    $src = Join-Path $Root $f
    if (Test-Path $src) {
        Copy-Item $src (Join-Path $Stage $f) -Force
    }
}

# Limpar writable (manter estrutura, remover logs/sessões/cache de dev)
$WritableKeep = @("cache", "debugbar", "logs", "session", "uploads")
foreach ($sub in $WritableKeep) {
    $dir = Join-Path $Stage "writable\$sub"
    if (Test-Path $dir) {
        Get-ChildItem $dir -Force | Where-Object { $_.Name -ne "index.html" } | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
    }
}

# Aplicar .htaccess de produção (Hostinger)
$HostingerDir = Join-Path $Root "deploy\hostinger"
Copy-Item (Join-Path $HostingerDir ".htaccess") (Join-Path $Stage ".htaccess") -Force
Copy-Item (Join-Path $HostingerDir "public.htaccess") (Join-Path $Stage "public\.htaccess") -Force

# Gerar .env de produção a partir do exemplo (sem sobrescrever se você editar o example)
$EnvExample = Join-Path $HostingerDir ".env.example"
$EnvOut = Join-Path $Stage ".env"
$envText = Get-Content $EnvExample -Raw -Encoding UTF8
$envText = $envText -replace "https://SEU_DOMINIO\.com\.br/", $BaseUrl
Set-Content -Path $EnvOut -Value $envText -Encoding UTF8

# Garantir index.html de proteção nas pastas sensíveis
$ProtectDirs = @(
    "app",
    "writable",
    "writable\cache",
    "writable\logs",
    "writable\session",
    "writable\uploads",
    "writable\debugbar"
)
foreach ($pd in $ProtectDirs) {
    $idx = Join-Path $Stage "$pd\index.html"
    if (-not (Test-Path $idx)) {
        Set-Content -Path $idx -Value "<!DOCTYPE html><title>403</title>" -Encoding UTF8
    }
}

# ZIP
if (Test-Path $OutputZip) { Remove-Item $OutputZip -Force }
Write-Host "Criando ZIP: $OutputZip"
Compress-Archive -Path (Join-Path $Stage "*") -DestinationPath $OutputZip -CompressionLevel Optimal

Remove-Item $Stage -Recurse -Force

Write-Host ""
Write-Host "=== Pacote pronto ===" -ForegroundColor Green
Write-Host $OutputZip
Write-Host ""
Write-Host "Proximos passos:"
Write-Host "1. No hPanel Hostinger, crie o banco MySQL e anote host/user/senha/nome."
Write-Host "2. Suba o ZIP no File Manager em public_html e extraia (ou use FTP)."
Write-Host "3. Edite o .env com dominio real e credenciais do banco."
Write-Host "4. Em public_html/writable: permissoes de escrita (755 ou 775)."
Write-Host "5. PHP 8.2+ no hPanel. Importe o SQL/migrations se necessario."
Write-Host ""
Write-Host "Estrutura esperada em public_html:"
Write-Host "  public_html/.htaccess"
Write-Host "  public_html/.env"
Write-Host "  public_html/app/"
Write-Host "  public_html/public/index.php"
Write-Host "  public_html/vendor/"
Write-Host "  public_html/writable/"

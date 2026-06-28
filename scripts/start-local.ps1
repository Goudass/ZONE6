$ErrorActionPreference = "Stop"
$RootDir = Split-Path -Parent $PSScriptRoot
Set-Location $RootDir

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "Docker nie jest zainstalowany. Zainstaluj Docker Desktop: https://www.docker.com/products/docker-desktop/"
    exit 1
}

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Utworzono plik .env z domyslnymi ustawieniami"
}

$envContent = Get-Content ".env" -Raw
$port = 8080
if ($envContent -match 'WP_PORT=(\d+)') {
    $port = [int]$Matches[1]
}

Write-Host "Uruchamiam WordPress lokalnie..."
docker compose up -d

Write-Host "Czekam na gotowosc WordPress..."
$ready = $false
for ($i = 1; $i -le 60; $i++) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$port" -UseBasicParsing -TimeoutSec 3 -ErrorAction Stop
        $ready = $true
        break
    } catch {
        Start-Sleep -Seconds 2
    }
}

if (-not $ready) {
    Write-Host "WordPress nie odpowiada na porcie $port. Sprawdz: docker compose logs wordpress"
    exit 1
}

Write-Host "Konfiguruje WordPress i motyw..."
if (Get-Command bash -ErrorAction SilentlyContinue) {
    bash "$RootDir/scripts/setup-wordpress.sh"
} else {
    Write-Host "Brak bash — uruchom recznie: docker compose run --rm wpcli core install ..."
    Write-Host "Lub zainstaluj Git Bash / WSL i uruchom ponownie ten skrypt."
}

Write-Host ""
Write-Host "Blog dziala lokalnie!"
Write-Host "  Front:  http://localhost:$port"
Write-Host "  Admin:  http://localhost:$port/wp-admin"
Write-Host "  Login:  admin / admin123"
Write-Host ""
Write-Host "Zatrzymanie: docker compose down"

$ErrorActionPreference = "Stop"

$RootDir  = Split-Path -Parent $PSScriptRoot
$XamppDir = "C:\xampp"
$PhpExe   = Join-Path $XamppDir "php\php.exe"
$SiteDir  = Join-Path $XamppDir "htdocs\adventure-blog"
$wpCli    = Join-Path $RootDir "wp-cli.phar"
$evalFile = Join-Path $PSScriptRoot "seed-sample-route.php"

if (-not (Test-Path $SiteDir)) {
    Write-Host "Brak WordPressa w $SiteDir — uruchom najpierw scripts/setup-xampp.ps1"
    exit 1
}

if (-not (Test-Path $wpCli)) {
    Write-Host "Brak wp-cli.phar — uruchom scripts/setup-xampp.ps1"
    exit 1
}

Write-Host "→ Tworzę przykładową trasę (treść, zdjęcia, GPX)..." -ForegroundColor Cyan
& $PhpExe $wpCli --path=$SiteDir eval-file $evalFile
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

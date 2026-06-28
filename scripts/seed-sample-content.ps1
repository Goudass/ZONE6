$ErrorActionPreference = "Stop"

$RootDir  = Split-Path -Parent $PSScriptRoot
$XamppDir = "C:\xampp"
$PhpExe   = Join-Path $XamppDir "php\php.exe"
$SiteDir  = Join-Path $XamppDir "htdocs\adventure-blog"
$wpCli    = Join-Path $RootDir "wp-cli.phar"
$evalFile = Join-Path $PSScriptRoot "seed-sample-content.php"
$loader   = Join-Path $PSScriptRoot "run-wp-eval.php"

if (-not (Test-Path $SiteDir)) {
    Write-Host "WordPress not found in $SiteDir"
    exit 1
}

Write-Host "Seeding sample posts and routes..." -ForegroundColor Cyan

if (Test-Path $wpCli) {
    & $PhpExe $wpCli --path=$SiteDir eval-file $evalFile
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    exit 0
}

& $PhpExe $loader $SiteDir $evalFile
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Done." -ForegroundColor Green

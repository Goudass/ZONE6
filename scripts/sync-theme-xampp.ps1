$ErrorActionPreference = "Stop"
$RootDir   = Split-Path -Parent $PSScriptRoot
$SiteDir   = "C:\xampp\htdocs\adventure-blog"
$themeDest = Join-Path $SiteDir "wp-content\themes\adventure-blog"
$themeSrc  = Join-Path $RootDir "wp-content\themes\adventure-blog"

if (-not (Test-Path $SiteDir)) {
    Write-Host "Najpierw uruchom: .\scripts\setup-xampp.ps1"
    exit 1
}

if (Test-Path $themeDest) { Remove-Item $themeDest -Recurse -Force }
Copy-Item $themeSrc $themeDest -Recurse -Force
Write-Host "Motyw zsynchronizowany do XAMPP."

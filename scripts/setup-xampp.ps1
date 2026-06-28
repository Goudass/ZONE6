$ErrorActionPreference = "Stop"

$RootDir   = Split-Path -Parent $PSScriptRoot
$XamppDir  = "C:\xampp"
$PhpExe    = Join-Path $XamppDir "php\php.exe"
$MysqlExe  = Join-Path $XamppDir "mysql\bin\mysql.exe"
$HtdocsDir = Join-Path $XamppDir "htdocs"
$SiteDir   = Join-Path $HtdocsDir "adventure-blog"
$SiteUrl   = "http://localhost/adventure-blog"
$DbName    = "adventure_blog"
$DbUser    = "root"
$DbPass    = ""
$AdminUser = "admin"
$AdminPass = "admin123"
$AdminMail = "admin@example.com"

function Write-Step($msg) { Write-Host "`n→ $msg" -ForegroundColor Cyan }

if (-not (Test-Path $PhpExe)) {
    Write-Host "Nie znaleziono XAMPP w $XamppDir"
    Write-Host "Zainstaluj XAMPP: https://www.apachefriends.org/"
    exit 1
}

Write-Step "Sprawdzam Apache i MySQL..."
$apache = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
$mysql  = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if (-not $apache) {
    Write-Host "  Uruchamiam Apache..."
    Start-Process (Join-Path $XamppDir "apache\bin\httpd.exe") -WindowStyle Hidden
    Start-Sleep -Seconds 3
}
if (-not $mysql) {
    Write-Host "  Uruchamiam MySQL..."
    Start-Process (Join-Path $XamppDir "mysql\bin\mysqld.exe") -ArgumentList "--defaults-file=$XamppDir\mysql\bin\my.ini" -WindowStyle Hidden
    Start-Sleep -Seconds 4
}

Write-Step "Tworzę bazę danych..."
$createDb = "CREATE DATABASE IF NOT EXISTS ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($DbPass) {
    & $MysqlExe -u $DbUser -p$DbPass -e $createDb
} else {
    & $MysqlExe -u $DbUser -e $createDb
}

if (-not (Test-Path $SiteDir)) {
    Write-Step "Pobieram WordPress..."
    $zipPath = Join-Path $env:TEMP "wordpress.zip"
    Invoke-WebRequest -Uri "https://wordpress.org/latest.zip" -OutFile $zipPath -UseBasicParsing

    $extractDir = Join-Path $env:TEMP "wp-extract"
    if (Test-Path $extractDir) { Remove-Item $extractDir -Recurse -Force }
    Expand-Archive -Path $zipPath -DestinationPath $extractDir -Force
    Move-Item (Join-Path $extractDir "wordpress") $SiteDir
    Remove-Item $extractDir -Recurse -Force -ErrorAction SilentlyContinue
    Remove-Item $zipPath -Force -ErrorAction SilentlyContinue
    Write-Host "  WordPress zainstalowany w $SiteDir"
} else {
    Write-Host "  WordPress już istnieje w $SiteDir"
}

Write-Step "Podlaczam motyw Adventure Blog (junction)..."
$themeDest = Join-Path $SiteDir "wp-content\themes\adventure-blog"
$themeSrc  = Join-Path $RootDir "wp-content\themes\adventure-blog"
if (Test-Path $themeDest) {
    $item = Get-Item $themeDest -Force
    if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) {
        cmd /c rmdir $themeDest
    } else {
        Remove-Item $themeDest -Recurse -Force
    }
}
cmd /c mklink /J `"$themeDest`" `"$themeSrc`"

Write-Step "Pobieram WP-CLI..."
$wpCli = Join-Path $RootDir "wp-cli.phar"
if (-not (Test-Path $wpCli)) {
    Invoke-WebRequest -Uri "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar" -OutFile $wpCli -UseBasicParsing
}

function Invoke-WpCli {
    param([string[]]$WpArgs)
    & $PhpExe $wpCli --path=$SiteDir @WpArgs
    if ($LASTEXITCODE -ne 0) {
        throw "WP-CLI failed: wp $($WpArgs -join ' ')"
    }
}

$wpConfig = Join-Path $SiteDir "wp-config.php"
if (-not (Test-Path $wpConfig)) {
    Write-Step "Konfiguruje wp-config.php..."
    $configArgs = @(
        "config", "create",
        "--dbname=$DbName",
        "--dbuser=$DbUser",
        "--dbhost=127.0.0.1",
        "--skip-check"
    )
    if ($DbPass) { $configArgs += "--dbpass=$DbPass" }
    Invoke-WpCli $configArgs
}

Write-Step "Instaluje WordPress..."
$installed = $false
try {
    Invoke-WpCli @("core", "is-installed") | Out-Null
    $installed = $true
} catch {
    $installed = $false
}

if (-not $installed) {
    Invoke-WpCli @(
        "core", "install",
        "--url=$SiteUrl",
        "--title=ZONE6.PL",
        "--admin_user=$AdminUser",
        "--admin_password=$AdminPass",
        "--admin_email=$AdminMail",
        "--skip-email"
    )
    Write-Host "  WordPress zainstalowany"
} else {
    Write-Host "  WordPress już zainstalowany"
}

Write-Step "Konfiguruję motyw, strony i menu..."
Invoke-WpCli @("option", "update", "blogdescription", "Outdoor, trasy i przygody - 6zone.pl")
Invoke-WpCli @("rewrite", "structure", "/%postname%/", "--hard")
Invoke-WpCli @("rewrite", "flush", "--hard")
Invoke-WpCli @("theme", "activate", "adventure-blog")

$htaccess = Join-Path $SiteDir ".htaccess"
if (-not (Test-Path $htaccess)) {
    @"
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /adventure-blog/
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /adventure-blog/index.php [L]
</IfModule>
# END WordPress
"@ | Set-Content $htaccess -Encoding ASCII
}

function Get-OrCreatePage($slug, $title, $content = "") {
    $id = Invoke-WpCli @("post", "list", "--post_type=page", "--name=$slug", "--field=ID", "--format=csv") 2>$null
    if (-not $id) {
        $id = Invoke-WpCli @("post", "create", "--post_type=page", "--post_status=publish", "--post_title=$title", "--post_name=$slug", "--post_content=$content", "--porcelain")
        Write-Host "  + $title"
    }
    return $id.Trim()
}

$homeId    = Get-OrCreatePage "start" "Start"
$newsId    = Get-OrCreatePage "aktualnosci" "Aktualnosci" "Najnowsze wpisy i newsy ze szlakow."
$aboutId   = Get-OrCreatePage "o-mnie" "O mnie" ""
$contactId = Get-OrCreatePage "kontakt" "Kontakt" "Masz pytanie? Napisz przez formularz obok."

Invoke-WpCli @("option", "update", "show_on_front", "page")
Invoke-WpCli @("option", "update", "page_on_front", $homeId)
Invoke-WpCli @("option", "update", "page_for_posts", $newsId)

$menuList = Invoke-WpCli @("menu", "list", "--fields=term_id,name", "--format=csv")
$menuId = ($menuList | Where-Object { $_ -match ",Menu glowne$" } | ForEach-Object { ($_ -split ",")[0] } | Select-Object -First 1)

if (-not $menuId) {
    Invoke-WpCli @("menu", "create", "Menu glowne") | Out-Null
    $menuList = Invoke-WpCli @("menu", "list", "--fields=term_id,name", "--format=csv")
    $menuId = ($menuList | Where-Object { $_ -match ",Menu glowne$" } | ForEach-Object { ($_ -split ",")[0] } | Select-Object -First 1)
}

function Add-MenuItemSafe {
    param([string[]]$WpArgs)
    try { Invoke-WpCli $WpArgs } catch { }
}

Add-MenuItemSafe @("menu", "item", "add-post", $menuId, $aboutId)
Add-MenuItemSafe @("menu", "item", "add-post", $menuId, $newsId)
Add-MenuItemSafe @("menu", "item", "add-custom", $menuId, "Trasy rowerowe", "$SiteUrl/typ-trasy/trasy-rowerowe/")
Add-MenuItemSafe @("menu", "item", "add-custom", $menuId, "Tatry", "$SiteUrl/typ-trasy/tatry/")
Add-MenuItemSafe @("menu", "item", "add-custom", $menuId, "Projekty", "$SiteUrl/typ-trasy/projekty/")
Add-MenuItemSafe @("menu", "item", "add-post", $menuId, $contactId)
Add-MenuItemSafe @("menu", "location", "assign", $menuId, "primary")
Add-MenuItemSafe @("menu", "location", "assign", $menuId, "footer")

$gpxPath = Join-Path $RootDir "wp-content\themes\adventure-blog\sample-data\sample-route.gpx"
$gpxId = ""
if (Test-Path $gpxPath) {
    $gpxId = Invoke-WpCli @("media", "import", $gpxPath, "--porcelain") 2>$null
}

$routeId = Invoke-WpCli @("post", "list", "--post_type=trasa", "--name=przykladowa-trasa-tatrzanska", "--field=ID", "--format=csv") 2>$null
if (-not $routeId) {
    $routeId = Invoke-WpCli @(
        "post", "create",
        "--post_type=trasa",
        "--post_status=publish",
        "--post_title=Przykladowa trasa tatrzanska",
        "--post_name=przykladowa-trasa-tatrzanska",
        "--post_content=To przykladowa trasa demo. Podmien opis, zdjecia i plik GPX na wlasne dane.",
        "--porcelain"
    )
    Invoke-WpCli @("post", "meta", "update", $routeId, "_adventure_featured", "1") | Out-Null
    Invoke-WpCli @("post", "meta", "update", $routeId, "_adventure_trudnosc", "srednia") | Out-Null
    Invoke-WpCli @("post", "meta", "update", $routeId, "_adventure_czas", "3h 15min") | Out-Null
    Invoke-WpCli @("post", "meta", "update", $routeId, "_adventure_dystans", "18 km") | Out-Null
    Invoke-WpCli @("post", "meta", "update", $routeId, "_adventure_przewyzszenie", "650 m") | Out-Null
    if ($gpxId) { Invoke-WpCli @("post", "meta", "update", $routeId, "_adventure_gpx_id", $gpxId.Trim()) | Out-Null }
    Invoke-WpCli @("post", "term", "set", $routeId, "typ-trasy", "tatry") | Out-Null
    Write-Host "  + Przykladowa trasa"
}

$postId = Invoke-WpCli @("post", "list", "--post_type=post", "--name=witaj-na-blogu", "--field=ID", "--format=csv") 2>$null
if (-not $postId) {
    Invoke-WpCli @(
        "post", "create",
        "--post_type=post",
        "--post_status=publish",
        "--post_title=Witaj na blogu!",
        "--post_name=witaj-na-blogu",
        "--post_content=To przykladowy wpis w sekcji Aktualnosci."
    ) | Out-Null
    Write-Host "  + Przykladowy wpis"
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host "  Blog dziala lokalnie (XAMPP)!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Strona:  $SiteUrl"
Write-Host "  Admin:   $SiteUrl/wp-admin"
Write-Host "  Login:   $AdminUser / $AdminPass"
Write-Host ""
Write-Host "  Motyw edytujesz tutaj (zmiany widoczne od razu):"
Write-Host "  $themeSrc"
Write-Host ""

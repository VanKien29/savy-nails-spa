$ErrorActionPreference = 'Stop'
$starterRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $starterRoot
$starterPhp = Join-Path $starterRoot '.tools/php'
$starterCommand = Get-Command php -ErrorAction SilentlyContinue
$starterCompatible = $false
if ($starterCommand) {
    & $starterCommand.Source -r 'exit(PHP_VERSION_ID >= 80512 && PHP_VERSION_ID < 80600 ? 0 : 1);'
    $starterCompatible = $LASTEXITCODE -eq 0
}
if (-not $starterCompatible -and (Test-Path -LiteralPath (Join-Path $starterPhp 'php.exe'))) {
    $env:PATH = $starterPhp + ';' + $env:PATH
}
php -r 'exit(PHP_VERSION_ID >= 80512 && PHP_VERSION_ID < 80600 ? 0 : 1);'
if ($LASTEXITCODE -ne 0) { throw 'Install stable PHP >=8.5.12 <8.6 before serving this project.' }
Write-Host ('Using PHP: ' + (Get-Command php).Source)
php artisan serve --host=127.0.0.1 --port=8000

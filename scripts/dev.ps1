param(
    [ValidateRange(1024, 65535)]
    [int]$Port = 8001
)

$ErrorActionPreference = 'Stop'
$starterRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $starterRoot
$starterCommand = Get-Command php -ErrorAction SilentlyContinue
$starterCompatible = $false
if ($starterCommand) {
    & $starterCommand.Source scripts/check-php-runtime.php
    $starterCompatible = $LASTEXITCODE -eq 0
}
if (-not $starterCompatible) {
    foreach ($starterRuntime in @('.tools/php84', '.tools/php')) {
        $starterPhp = Join-Path $starterRoot $starterRuntime
        $starterExecutable = Join-Path $starterPhp 'php.exe'
        if (-not (Test-Path -LiteralPath $starterExecutable)) { continue }
        & $starterExecutable scripts/check-php-runtime.php
        if ($LASTEXITCODE -eq 0) {
            $env:PATH = $starterPhp + ';' + $env:PATH
            $starterCompatible = $true
            break
        }
    }
}
if (-not $starterCompatible) { throw 'Install stable PHP 8.4.27+ (8.4.x) or 8.5.12+ (8.5.x) before serving this project.' }
Write-Host ('Using PHP: ' + (Get-Command php).Source)
php artisan serve --host=127.0.0.1 --port=$Port

# Update .env file with database configuration
$envFile = ".env"
$content = Get-Content $envFile -Raw

$content = $content -replace 'DB_CONNECTION=sqlite', 'DB_CONNECTION=mysql'
$content = $content -replace '# DB_HOST=127.0.0.1', 'DB_HOST=localhost'
$content = $content -replace '# DB_PORT=3306', 'DB_PORT=3306'
$content = $content -replace '# DB_DATABASE=laravel', 'DB_DATABASE=root'
$content = $content -replace '# DB_USERNAME=root', 'DB_USERNAME=root'
$content = $content -replace '# DB_PASSWORD=', 'DB_PASSWORD=123456'

Set-Content $envFile $content
Write-Host ".env file updated successfully!"


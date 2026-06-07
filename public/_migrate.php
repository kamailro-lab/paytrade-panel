<?php
/**
 * 🔧 MIGRATE.PHP — uruchom migracje przez przeglądarkę
 *
 * Backup gdy .cpanel.yml nie wykonał migracji.
 * Otwórz: https://panel.mrtradex.ie/_migrate.php
 *  → skopiuj token z ekranu
 *  → ?token=<token> żeby uruchomić
 *
 * USUŃ ten plik gdy migracja zadziała.
 */

$INSTALLER_TOKEN = 'paytrade-migrate-' . substr(md5(__FILE__ . filemtime(__FILE__)), 0, 8);

if (($_GET['token'] ?? '') !== $INSTALLER_TOKEN) {
    http_response_code(401);
    echo "<h1>Unauthorized</h1>";
    echo "<p>Add ?token=$INSTALLER_TOKEN to URL.</p>";
    echo "<p>Your token: <code>$INSTALLER_TOKEN</code></p>";
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/html; charset=utf-8');
echo "<!doctype html><html><head><title>Migracja</title>";
echo "<style>body{font-family:monospace;padding:20px;max-width:800px;margin:0 auto;background:#f5f5f5} pre{background:white;padding:10px;border:1px solid #ddd;overflow:auto;font-size:13px} .ok{color:green;font-weight:bold} .err{color:red;font-weight:bold}</style>";
echo "</head><body>";
echo "<h1>🔧 Migracja bazy danych</h1>";

use Illuminate\Support\Facades\Artisan;

echo "<h3>Status migracji (przed)</h3><pre>";
Artisan::call('migrate:status', [], $output = new Symfony\Component\Console\Output\BufferedOutput());
echo htmlspecialchars($output->fetch());
echo "</pre>";

echo "<h3>Uruchamiam migrate --force</h3><pre>";
Artisan::call('migrate', ['--force' => true], $output = new Symfony\Component\Console\Output\BufferedOutput());
echo htmlspecialchars($output->fetch());
echo "</pre>";

echo "<h3>Czyszczę cache</h3><pre>";
Artisan::call('config:clear', [], $output = new Symfony\Component\Console\Output\BufferedOutput());
echo htmlspecialchars($output->fetch());
Artisan::call('route:clear', [], $output = new Symfony\Component\Console\Output\BufferedOutput());
echo htmlspecialchars($output->fetch());
Artisan::call('view:clear', [], $output = new Symfony\Component\Console\Output\BufferedOutput());
echo htmlspecialchars($output->fetch());
echo "</pre>";

echo "<hr><h2 class='ok'>✅ Migracja zakończona</h2>";
echo "<p><strong>USUŃ teraz plik <code>public/_migrate.php</code></strong></p>";
echo "<p><a href='/login'>→ Login</a></p>";
echo "</body></html>";

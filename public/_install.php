<?php
/**
 * 🔧 WEB INSTALLER — uruchom RAZ po wgraniu plików.
 * USUŃ TEN PLIK po zakończeniu instalacji (security!).
 *
 * Co robi:
 *  1. Sprawdza wymagania PHP
 *  2. Sprawdza połączenie z bazą
 *  3. Uruchamia migracje (php artisan migrate --force)
 *  4. Wstawia użytkowników (db:seed)
 *  5. storage:link
 *  6. Cache config + routes + views
 */

// Bezpieczeństwo - prosty token żeby tylko user mógł uruchomić
$INSTALLER_TOKEN = 'paytrade-install-' . substr(md5(__FILE__ . filemtime(__FILE__)), 0, 8);

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
echo "<!doctype html><html><head><title>Paytrade Installer</title>";
echo "<style>body{font-family:monospace;padding:20px;max-width:800px;margin:0 auto;background:#f5f5f5} .ok{color:green} .err{color:red} pre{background:white;padding:10px;border:1px solid #ddd;overflow:auto}</style>";
echo "</head><body>";
echo "<h1>🔧 Paytrade — Web Installer</h1>";

function run($cmd, $label) {
    echo "<h3>$label</h3><pre>";
    Artisan::call($cmd, [], $output = new Symfony\Component\Console\Output\BufferedOutput());
    $out = $output->fetch();
    echo htmlspecialchars($out ?: '(no output)');
    echo "</pre>";
    return strpos($out, 'error') === false && strpos($out, 'Exception') === false;
}

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// 1. PHP version
echo "<h3>1. PHP version</h3>";
$phpver = phpversion();
echo "<p>PHP <strong>$phpver</strong> " . (version_compare($phpver, '8.2', '>=') ? '<span class="ok">✓ OK</span>' : '<span class="err">✗ Wymagana 8.2+</span>') . "</p>";

// 2. Required extensions
echo "<h3>2. Wymagane rozszerzenia PHP</h3><ul>";
foreach (['mbstring', 'pdo_mysql', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'curl', 'gd', 'intl'] as $ext) {
    $loaded = extension_loaded($ext);
    echo "<li>$ext " . ($loaded ? '<span class="ok">✓</span>' : '<span class="err">✗ BRAK</span>') . "</li>";
}
echo "</ul>";

// 3. DB connection
echo "<h3>3. Połączenie z bazą danych</h3>";
try {
    DB::connection()->getPdo();
    echo "<p><span class='ok'>✓ Połączono z bazą: " . DB::connection()->getDatabaseName() . "</span></p>";
} catch (Exception $e) {
    echo "<p><span class='err'>✗ Błąd: " . htmlspecialchars($e->getMessage()) . "</span></p>";
    echo "<p>Sprawdź dane w pliku <code>.env</code>: DB_DATABASE, DB_USERNAME, DB_PASSWORD</p>";
    exit;
}

// 4. Migracje
run('migrate --force', '4. Migracje bazy danych');

// 5. Seed (admin user + paytrade user + settings)
run('db:seed --force', '5. Tworzenie użytkowników');

// 6. Storage symlink
run('storage:link', '6. Storage symlink');

// 7. Cache prod
run('config:cache', '7. Cache config');
run('route:cache', '8. Cache routes');
run('view:cache', '9. Cache views');

echo "<hr>";
echo "<h2 class='ok'>✅ Instalacja zakończona!</h2>";
echo "<p><strong>WAŻNE: usuń teraz plik <code>public/_install.php</code></strong></p>";
echo "<p>Loginy:</p><ul>";
echo "<li>Email: <code>info@paytrade.ie</code> · Hasło: <code>paytrade123</code></li>";
echo "<li>Email: <code>admin@cars.ie</code> · Hasło: <code>password</code></li>";
echo "</ul>";
echo "<p><a href='/login' style='display:inline-block;padding:10px 20px;background:#4338ca;color:white;text-decoration:none;border-radius:8px;'>→ Przejdź do logowania</a></p>";

echo "</body></html>";

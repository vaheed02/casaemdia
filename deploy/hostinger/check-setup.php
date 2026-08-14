<?php
/**
 * Diagnóstico temporário — Hostinger
 * Coloque em public_html/check-setup.php e abra no navegador.
 * APAGUE este arquivo depois de corrigir.
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Casa em Dia — check-setup</h1><pre style="white-space:pre-wrap;font:14px/1.45 monospace">';

$ok = static function (string $label, bool $pass, string $detail = ''): void {
    $icon = $pass ? '[OK]' : '[FALHA]';
    echo htmlspecialchars($icon . ' ' . $label . ($detail !== '' ? ' — ' . $detail : ''), ENT_QUOTES, 'UTF-8') . "\n";
};

$root = __DIR__;
$ok('PHP >= 8.2', version_compare(PHP_VERSION, '8.2.0', '>='), 'atual: ' . PHP_VERSION);
$ok('ext mysqli', extension_loaded('mysqli'));
$ok('ext intl', extension_loaded('intl'), extension_loaded('intl') ? 'ok' : 'recomendado');
$ok('ext mbstring', extension_loaded('mbstring'));
$ok('mod_rewrite (provável)', true, 'confirme .htaccess na raiz');

$envPath = $root . DIRECTORY_SEPARATOR . '.env';
$ok('.env existe', is_file($envPath), $envPath);

$env = [];
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $v = trim($v, " \t\"'");
        $env[$k] = $v;
    }
}

$host = $env['database.default.hostname'] ?? 'localhost';
$db   = $env['database.default.database'] ?? '';
$user = $env['database.default.username'] ?? '';
$pass = $env['database.default.password'] ?? '';
$base = $env['app.baseURL'] ?? '';

$ok('app.baseURL', $base !== '', $base);
$ok('DB database preenchido', $db !== '', $db);
$ok('DB username preenchido', $user !== '' && $user !== 'COLE_USUARIO_MYSQL', $user);
$ok('DB password preenchida', $pass !== '' && $pass !== 'COLE_SENHA_MYSQL', $pass === '' ? '(vazia)' : '(definida)');

$writable = $root . DIRECTORY_SEPARATOR . 'writable';
$ok('writable existe', is_dir($writable));
$ok('writable gravável', is_dir($writable) && is_writable($writable));
foreach (['session', 'logs', 'cache'] as $sub) {
    $p = $writable . DIRECTORY_SEPARATOR . $sub;
    $ok("writable/{$sub} gravável", is_dir($p) && is_writable($p));
}

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    $ok('Conexão MySQL', false, $mysqli->connect_error);
} else {
    $ok('Conexão MySQL', true, "host={$host} db={$db}");
    $need = ['usuarios', 'perfis_prestador', 'enderecos', 'agendamentos', 'pagamentos', 'avaliacoes', 'configuracoes'];
    foreach ($need as $t) {
        $res = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($t) . "'");
        $ok("Tabela `{$t}`", $res && $res->num_rows > 0);
    }
    $res = $mysqli->query('SELECT COUNT(*) AS c FROM usuarios');
    if ($res) {
        $c = (int) $res->fetch_assoc()['c'];
        $ok('usuarios com registros', true, (string) $c);
    }
    $mysqli->close();
}

$ok('app/ existe', is_dir($root . '/app'));
$ok('vendor/autoload.php', is_file($root . '/vendor/autoload.php'));
$ok('index.php na raiz', is_file($root . '/index.php'));
$ok('.htaccess na raiz', is_file($root . '/.htaccess'));

echo "\n--- Próximos passos se houver FALHA ---\n";
echo "1) Corrija usuario/senha do MySQL no .env (hPanel → Bancos de dados)\n";
echo "2) Importe schema.sql no phpMyAdmin no banco correto\n";
echo "3) Permissão 755/775 em writable e subpastas\n";
echo "4) APAGUE este arquivo check-setup.php após corrigir\n";
echo '</pre>';

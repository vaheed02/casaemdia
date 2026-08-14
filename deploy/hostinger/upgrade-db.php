<?php
/**
 * Upgrade automático do banco — Hostinger
 * Upload em public_html/upgrade-db.php e abra no navegador UMA vez.
 * APAGUE depois.
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Casa em Dia — upgrade-db</h1><pre style="white-space:pre-wrap;font:14px/1.45 monospace">';

$root = __DIR__;
$envPath = $root . '/.env';
if (! is_file($envPath)) {
    exit("FALHA: .env não encontrado\n");
}

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
        continue;
    }
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $env[$k] = trim($v, " \t\"'");
}

$host = $env['database.default.hostname'] ?? 'localhost';
$db   = $env['database.default.database'] ?? '';
$user = $env['database.default.username'] ?? '';
$pass = $env['database.default.password'] ?? '';

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    exit('[FALHA] MySQL: ' . $mysqli->connect_error . "\n");
}
echo "[OK] Conectado em {$db}\n";

$columns = static function (mysqli $db, string $table): array {
    $out = [];
    $res = $db->query('SHOW COLUMNS FROM `' . $db->real_escape_string($table) . '`');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $out[] = $row['Field'];
        }
    }

    return $out;
};

$run = static function (mysqli $db, string $sql) use (&$okCount, &$failCount): void {
    if ($db->query($sql)) {
        echo "[OK] {$sql}\n";
        $okCount++;
    } else {
        // Ignora "duplicate column" etc.
        $err = $db->error;
        if (stripos($err, 'Duplicate') !== false || stripos($err, 'already exists') !== false) {
            echo "[SKIP] {$sql} — {$err}\n";
        } else {
            echo "[FALHA] {$sql} — {$err}\n";
            $failCount++;
        }
    }
};

$okCount = 0;
$failCount = 0;

// --- perfis_prestador ---
$pf = $columns($mysqli, 'perfis_prestador');
$needPf = [
    'valor_telhado'    => "ALTER TABLE `perfis_prestador` ADD COLUMN `valor_telhado` DECIMAL(10,2) NOT NULL DEFAULT 0",
    'valor_piscina'    => "ALTER TABLE `perfis_prestador` ADD COLUMN `valor_piscina` DECIMAL(10,2) NOT NULL DEFAULT 0",
    'valor_jardim'     => "ALTER TABLE `perfis_prestador` ADD COLUMN `valor_jardim` DECIMAL(10,2) NOT NULL DEFAULT 0",
    'valor_hidraulico' => "ALTER TABLE `perfis_prestador` ADD COLUMN `valor_hidraulico` DECIMAL(10,2) NOT NULL DEFAULT 0",
    'mp_email'         => "ALTER TABLE `perfis_prestador` ADD COLUMN `mp_email` VARCHAR(180) NULL",
];
foreach ($needPf as $col => $sql) {
    if (! in_array($col, $pf, true)) {
        $run($mysqli, $sql);
    } else {
        echo "[OK] perfis_prestador.{$col} já existe\n";
    }
}
$run($mysqli, "ALTER TABLE `perfis_prestador` MODIFY `tipos_servico` VARCHAR(180) NOT NULL");

// --- agendamentos tipo_servico enum ---
$run($mysqli, "ALTER TABLE `agendamentos` MODIFY `tipo_servico` ENUM('diarista','passeador','telhado','piscinas','jardins','hidraulico') NOT NULL");

// --- pagamentos MP ---
$pg = $columns($mysqli, 'pagamentos');
$needPg = [
    'mp_preference_id' => "ALTER TABLE `pagamentos` ADD COLUMN `mp_preference_id` VARCHAR(80) NULL",
    'mp_payment_id'    => "ALTER TABLE `pagamentos` ADD COLUMN `mp_payment_id` VARCHAR(80) NULL",
    'mp_status'        => "ALTER TABLE `pagamentos` ADD COLUMN `mp_status` VARCHAR(40) NULL",
    'checkout_url'     => "ALTER TABLE `pagamentos` ADD COLUMN `checkout_url` VARCHAR(500) NULL",
    'payout_status'    => "ALTER TABLE `pagamentos` ADD COLUMN `payout_status` ENUM('nao_aplicavel','pendente','processando','pago','falhou') NOT NULL DEFAULT 'nao_aplicavel'",
    'payout_ref'       => "ALTER TABLE `pagamentos` ADD COLUMN `payout_ref` VARCHAR(120) NULL",
    'payout_em'        => "ALTER TABLE `pagamentos` ADD COLUMN `payout_em` DATETIME NULL",
    'payout_nota'      => "ALTER TABLE `pagamentos` ADD COLUMN `payout_nota` TEXT NULL",
    'meta_json'        => "ALTER TABLE `pagamentos` ADD COLUMN `meta_json` TEXT NULL",
];
foreach ($needPg as $col => $sql) {
    if (! in_array($col, $pg, true)) {
        $run($mysqli, $sql);
    } else {
        echo "[OK] pagamentos.{$col} já existe\n";
    }
}

// --- configuracoes ---
$res = $mysqli->query("SHOW TABLES LIKE 'configuracoes'");
if (! $res || $res->num_rows === 0) {
    $run($mysqli, "CREATE TABLE `configuracoes` (
      `chave` VARCHAR(80) NOT NULL,
      `valor` TEXT NULL,
      `updated_at` DATETIME NULL,
      PRIMARY KEY (`chave`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $run($mysqli, "INSERT INTO `configuracoes` (`chave`,`valor`,`updated_at`) VALUES
      ('comissao_percentual','15',NOW()),
      ('mp_auto_payout','0',NOW())");
} else {
    echo "[OK] tabela configuracoes já existe\n";
}

echo "\nResumo: ok/alterações={$okCount} falhas={$failCount}\n";
echo "Se falhas=0, teste de novo: cadastro prestador e salvar perfil.\n";
echo "APAGUE este arquivo upgrade-db.php agora.\n</pre>";

$mysqli->close();

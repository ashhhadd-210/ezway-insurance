<?php
defined('APP') or exit;

const SCHEMA_VERSION = 2;

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $c = cfg('db');
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    if (($c['driver'] ?? 'sqlite') === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int) ($c['port'] ?? 3306), $c['name']);
        $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
        $pdo->exec("SET time_zone = '+00:00'");
    } else {
        $pdo = new PDO('sqlite:' . STORAGE . '/database.sqlite', null, null, $opts);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }
    migrate($pdo);
    return $pdo;
}

function db_is_mysql(): bool
{
    return (cfg('db.driver') ?? 'sqlite') === 'mysql';
}

/** Create tables on first run. A marker file avoids re-checking on every request. */
function migrate(PDO $pdo): void
{
    $marker = STORAGE . '/.schema-' . (db_is_mysql() ? 'mysql-' . md5((string) cfg('db.name')) : 'sqlite') . '-v' . SCHEMA_VERSION;
    if (is_file($marker)) {
        return;
    }

    if (db_is_mysql()) {
        $id = 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        $s = fn (int $n) => "VARCHAR($n)";
        $text = 'MEDIUMTEXT';
        $dt = 'DATETIME';
        $suffix = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    } else {
        $id = 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $s = fn (int $n) => 'TEXT';
        $text = 'TEXT';
        $dt = 'TEXT';
        $suffix = '';
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS quotes (
        id $id,
        ref {$s(16)} NOT NULL UNIQUE,
        service {$s(30)} NOT NULL DEFAULT 'personal_auto',
        lang {$s(2)} NOT NULL DEFAULT 'en',
        status {$s(20)} NOT NULL DEFAULT 'new',
        is_read SMALLINT NOT NULL DEFAULT 0,
        first_name {$s(80)} NOT NULL,
        last_name {$s(80)} NOT NULL,
        email {$s(160)} NOT NULL,
        phone {$s(30)} NOT NULL,
        city {$s(80)},
        state {$s(2)},
        zip {$s(10)},
        drivers_count SMALLINT NOT NULL DEFAULT 0,
        vehicles_count SMALLINT NOT NULL DEFAULT 0,
        search $text,
        data $text NOT NULL,
        notes $text,
        ip_hash {$s(64)},
        user_agent {$s(255)},
        created_at $dt NOT NULL,
        updated_at $dt NOT NULL
    )$suffix");

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id $id,
        is_read SMALLINT NOT NULL DEFAULT 0,
        name {$s(120)} NOT NULL,
        email {$s(160)} NOT NULL,
        phone {$s(30)},
        topic {$s(60)},
        message $text NOT NULL,
        ip_hash {$s(64)},
        created_at $dt NOT NULL
    )$suffix");

    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id $id,
        name {$s(80)} NOT NULL,
        email {$s(160)} NOT NULL UNIQUE,
        password_hash {$s(255)} NOT NULL,
        last_login_at $dt,
        created_at $dt NOT NULL
    )$suffix");

    $pdo->exec("CREATE TABLE IF NOT EXISTS throttle (
        id $id,
        bucket {$s(40)} NOT NULL,
        key_hash {$s(64)} NOT NULL,
        created_at $dt NOT NULL
    )$suffix");

    $pdo->exec("CREATE TABLE IF NOT EXISTS vin_cache (
        vin {$s(17)} NOT NULL PRIMARY KEY,
        data $text NOT NULL,
        created_at $dt NOT NULL
    )$suffix");

    // v2: service and language columns for databases created by v1.
    foreach (["service {$s(30)} NOT NULL DEFAULT 'personal_auto'", "lang {$s(2)} NOT NULL DEFAULT 'en'"] as $col) {
        try {
            $pdo->exec("ALTER TABLE quotes ADD COLUMN $col");
        } catch (PDOException) {
            // Column already exists.
        }
    }

    $indexes = [
        'idx_quotes_status'  => 'quotes (status, created_at)',
        'idx_quotes_created' => 'quotes (created_at)',
        'idx_quotes_service' => 'quotes (service, created_at)',
        'idx_messages_created' => 'messages (created_at)',
        'idx_throttle'       => 'throttle (bucket, key_hash, created_at)',
    ];
    foreach ($indexes as $name => $def) {
        try {
            $pdo->exec(db_is_mysql() ? "CREATE INDEX $name ON $def" : "CREATE INDEX IF NOT EXISTS $name ON $def");
        } catch (PDOException) {
            // Index already exists (MySQL has no IF NOT EXISTS for indexes).
        }
    }

    @file_put_contents($marker, date('c'));
}

function db_one(string $sql, array $params = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    return $row === false ? null : $row;
}

function db_all(string $sql, array $params = []): array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_val(string $sql, array $params = []): mixed
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $v = $st->fetchColumn();
    return $v === false ? null : $v;
}

function db_exec(string $sql, array $params = []): int
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}

<?php
declare(strict_types=1);

define('APP', true);
define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', dirname(__DIR__));
// Hosts with a read-only disk (e.g. Vercel) point this at a writable folder.
define('STORAGE', getenv('APP_STORAGE') ?: PUBLIC_ROOT . '/storage');

if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    exit('This site needs PHP 8.1 or newer. In cPanel, open "MultiPHP Manager" and pick PHP 8.x.');
}

$GLOBALS['__config'] = require APP_ROOT . '/config.php';

date_default_timezone_set($GLOBALS['__config']['timezone'] ?? 'America/New_York');
error_reporting(E_ALL);
ini_set('display_errors', !empty($GLOBALS['__config']['debug']) ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE . '/error.log');

require APP_ROOT . '/lib/helpers.php';
require APP_ROOT . '/lib/db.php';
require APP_ROOT . '/lib/crypto.php';
require APP_ROOT . '/lib/security.php';
require APP_ROOT . '/lib/mailer.php';
require APP_ROOT . '/lib/vin.php';
require APP_ROOT . '/lib/quote.php';

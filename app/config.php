<?php

declare(strict_types=1);

const APP_NAME = 'Calidad de Software';
const BASE_PATH = __DIR__ . '/..';
const STORAGE_PATH = BASE_PATH . '/storage';
const UPLOAD_PATH = BASE_PATH . '/public/uploads';

define('BASE_URL', rtrim((string) getenv('APP_URL'), '/'));
define('DB_HOST', getenv('MYSQL_HOST') ?: 'localhost');
define('DB_PORT', (int) (getenv('MYSQL_PORT') ?: 3306));
define('DB_DATABASE', getenv('MYSQL_DATABASE') ?: 'myweb');
define('DB_USERNAME', getenv('MYSQL_USER') ?: 'admin');
define('DB_PASSWORD', getenv('MYSQL_PASSWORD') ?: 'admin');
define('DB_CHARSET', getenv('MYSQL_CHARSET') ?: 'utf8mb4');

define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@example.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: APP_NAME);

define('TURNSTILE_SITE_KEY', getenv('TURNSTILE_SITE_KEY') ?: '');
define('TURNSTILE_SECRET', getenv('TURNSTILE_SECRET') ?: '');
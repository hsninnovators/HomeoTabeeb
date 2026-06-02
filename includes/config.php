<?php
declare(strict_types=1);

const SITE_NAME = 'Homeo Tabeeb';
const SITE_URL = 'https://doctor.hsninnovators.com';
const OPENROUTER_API_KEY = 'CHANGE_ME_OPENROUTER_API_KEY';
const OPENROUTER_MODEL = 'openai/gpt-4o-mini';
const DB_HOST = 'localhost';
const DB_NAME = 'homeo_tabeeb';
const DB_USER = 'homeo_user';
const DB_PASS = 'change_me';
const DB_CHARSET = 'utf8mb4';
const ADMIN_SESSION_TIMEOUT = 1800;
const APP_ENV = 'production';

ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/php-error.log');
date_default_timezone_set('Asia/Karachi');

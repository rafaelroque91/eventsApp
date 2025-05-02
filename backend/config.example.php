<?php

//redis
define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

// --- API Configuration ---
define('API_BASE_URL', 'https://MYAPI.COM');
define('CLIENT_ID', 'MY_CLIENT_ID');
define('CLIENT_SECRET','SECRET');

// --- API Settings ---
define('TOKEN_CACHE_KEY', 'civicplus_api_token');
define('APP_DEBUG', true);

if (APP_DEBUG == true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

define('API_PAGE_SIZE', 10);
define('ALLOWED_CORS', ['http://localhost:5173']);
?>
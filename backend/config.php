<?php
$redisHost = getenv('REDIS_HOST') ?: '127.0.0.1'; // Default if not set
$redisPort = getenv('REDIS_PORT') ?: 6379;

define('REDIS_HOST', $redisHost);
define('REDIS_PORT', (int)$redisPort);

// --- API Configuration ---
$baseApiUrl = 'https://interview.civicplus.com/AAAAAAAAAAAAAAAA';

// URL base da API externa (sem a barra no final)
define('API_BASE_URL', $baseApiUrl);

define('AUTH_URL', $baseApiUrl . '/api/Auth');

define('CLIENT_ID', 'AAAAAAAAAAAA'); // Your Client ID
define('CLIENT_SECRET', 'AAAAAAAAA'); // Your Client Secret

define('API_ENDPOINT_EVENTS', '/api/Events'); // List and Add events
define('API_ENDPOINT_EVENT_DETAIL_PREFIX', '/api/Events/'); // For specific event (append ID)

define('TOKEN_CACHE_KEY', 'civicplus_api_token');

define('APP_BASE_PATH', __DIR__); // Root of the backend code
define('TEMPLATE_PATH', APP_BASE_PATH . '/templates');
:
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_DEBUG',true);
// In production, set to false

?>
<?php

namespace App\Infrastructure\Http\Middleware;

class CORSMiddleware
{
    public static function setup(): void
    {
        $allowOrigin = null;
        if (in_array($_SERVER['HTTP_ORIGIN'] ?? '', ALLOWED_CORS)) {
            $allowOrigin = $_SERVER['HTTP_ORIGIN'];
        }

        if ($allowOrigin) {
            header("Access-Control-Allow-Origin: " . $allowOrigin);
        }

        //OPTIONS REQUEST, TO RETURN THE REQUIRED HEADERS AND THEN RETURN 204
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if ($allowOrigin) {
                header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, Origin, Accept");
                http_response_code(204);
                exit;
            } else {
                http_response_code(403);
                exit;
            }
        }
    }
}
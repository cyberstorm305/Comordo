<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ob_start();
define('COMORDO_STATIC', true);
require __DIR__ . '/_lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) {
    $in = [];
}

try {
    $result = comordo_handle_subscribe($in, comordo_config(dirname(__DIR__) . '/comordo-config.php'));
} catch (Throwable $e) {
    error_log('comordo subscribe: ' . $e->getMessage());
    $result = ['status' => 500, 'body' => ['ok' => false, 'message' => 'We couldn’t register that email right now. Please try again shortly.']];
}

ob_end_clean();
http_response_code($result['status']);
echo json_encode($result['body'], JSON_UNESCAPED_UNICODE);

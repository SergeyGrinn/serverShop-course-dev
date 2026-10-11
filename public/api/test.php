<?php
require_once '../../src/Helpers/Response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    Response::json(['success' => false, 'message' => 'Request must be GET']);
}

// test 1 - simple response
Response::json([
    'success' => true,
    'message' => 'API is working correctly',
    'test' => 'test_value'
]);

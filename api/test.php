<?php
require_once '../../src/Helpers/Response.php';

// test 1 - simple response
Response::json([
    'success' => true,
    'message' => 'API is working correctly',
    'test' => 'test_value'
]);

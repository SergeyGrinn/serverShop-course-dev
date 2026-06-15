<?php

const BASE_PATH = __DIR__ . '/../';
require_once BASE_PATH . 'src/Config/app.php';
require_once BASE_PATH . 'src/Core/functions.php';

session_start();
session_destroy();
header('Location: ' . BASE_URL . '/index.php');
exit;

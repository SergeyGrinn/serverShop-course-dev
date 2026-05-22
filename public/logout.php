<?php

session_start();
session_destroy();
header('Location: /L/course/public/index.php');
exit;

<?php

require_once base_path('src/Models/Order.php');
require_once base_path('src/Middleware/Admin.php');

class OrderController {
    private $orderModel;

    public function __construct($pdo) {
        Admin::check();
        $this->orderModel = new Order($pdo);
    }

    public function index() {
        $orders = $this->orderModel->getAllAdmin();
        require_once base_path('templates/admin/orders-list.php');
    }
}

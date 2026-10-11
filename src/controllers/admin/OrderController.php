<?php

require_once base_path('src/Models/Order.php');
require_once base_path('src/Middleware/Admin.php');

class OrderController {
    private const STATUS_LABELS = [
        'pending' => 'Awaiting Payment',
        'paid' => 'Paid',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    private const STATUS_ACTIONS = [
        'processing' => ['action' => 'mark_paid', 'label' => 'Mark as Paid'],
        'paid' => ['action' => 'mark_completed', 'label' => 'Mark as Completed'],
    ];
    private const STATUS_TRANSITIONS = [
        'mark_paid' => ['from' => 'processing', 'to' => 'paid'],
        'mark_completed' => ['from' => 'paid', 'to' => 'completed'],
    ];

    private $orderModel;

    public function __construct($pdo) {
        Admin::check();
        $this->orderModel = new Order($pdo);
    }

    public function index() {
        $errors = [];
        $statusFilter = $this->normalizeStatusFilter($_GET['status'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $statusFilter = $this->normalizeStatusFilter($_POST['status_filter'] ?? '');
            $errors = $this->updateFromPost($statusFilter);
        }

        $statusOptions = self::STATUS_LABELS;
        $statusActions = self::STATUS_ACTIONS;
        $orders = $this->orderModel->getAllAdmin();
        require_once base_path('templates/admin/orders-list.php');
    }

    private function normalizeStatusFilter($value): string {
        return is_string($value) && isset(self::STATUS_LABELS[$value]) ? $value : '';
    }

    private function updateFromPost(string $statusFilter): array {
        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!is_string($csrfToken) || !csrf_verify($csrfToken)) {
            return ['Invalid CSRF token'];
        }

        $submission = $this->validateSubmission();
        if (isset($submission['error'])) {
            return [$submission['error']];
        }

        if (!$this->orderModel->transitionStatus(
            $submission['order_id'],
            $submission['from'],
            $submission['to']
        )) {
            return ['Order status changed before this action could be applied'];
        }

        $this->redirectToList($statusFilter);
    }

    private function validateSubmission(): array {
        $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';

        if ($orderId === false || $orderId < 1 || !is_string($action) || !isset(self::STATUS_TRANSITIONS[$action])) {
            return ['error' => 'Invalid order action'];
        }

        $order = $this->orderModel->get($orderId);
        if (!$order) {
            return ['error' => 'Order not found'];
        }

        $transition = self::STATUS_TRANSITIONS[$action];
        if ($order['status'] !== $transition['from']) {
            return ['error' => 'This action is not available for the current order status'];
        }

        return [
            'order_id' => $orderId,
            'from' => $transition['from'],
            'to' => $transition['to'],
        ];
    }

    private function redirectToList(string $statusFilter): void {
        $redirectUrl = BASE_URL . '/admin/orders.php';
        if ($statusFilter !== '') {
            $redirectUrl .= '?status=' . rawurlencode($statusFilter);
        }
        header('Location: ' . $redirectUrl);
        exit;
    }
}

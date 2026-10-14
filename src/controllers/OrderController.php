<?php

namespace App\Controllers;

require_once \base_path('src/Models/Order.php');
require_once \base_path('src/Models/Cart.php');
require_once \base_path('src/Models/User.php');
require_once \base_path('src/Helpers/Response.php');

class OrderController {
	private const STATUS_COLORS = [
		'pending' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'Awaiting Payment'],
		'paid' => ['bg' => '#dff3ee', 'text' => '#12624f', 'label' => 'Paid'],
		'processing' => ['bg' => '#d1ecf1', 'text' => '#0c5460', 'label' => 'Processing'],
		'completed' => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Completed'],
		'cancelled' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Cancelled'],
	];

	private $orderModel;
	private $pdo;

	public function __construct($pdo) {
		$this->pdo = $pdo;
		$this->orderModel = new \Order($pdo);
	}

	public function checkout(): void {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		header('Cache-Control: no-store, private, max-age=0');
		header('Pragma: no-cache');
		header('Expires: 0');
		if (($_SESSION['user_role'] ?? '') === 'admin') {
			header('Location: ' . \BASE_URL . '/admin/orders.php');
			exit;
		}
		if (empty($_SESSION['user_id'])) {
			header('Location: ' . \BASE_URL . '/login.php?redirect=checkout.php');
			exit;
		}

		$checkoutUser = (new \User($this->pdo))->findById($_SESSION['user_id']);
		if (!$checkoutUser) {
			$_SESSION = [];
			session_destroy();
			header('Location: ' . \BASE_URL . '/login.php?redirect=checkout.php');
			exit;
		}
		$_SESSION['email_verified_at'] = $checkoutUser['email_verified_at'];
		if ($checkoutUser['email_verified_at'] === null) {
			header('Location: ' . \BASE_URL . '/index.php');
			exit;
		}

		if (!isset($_SESSION['session_id'])) {
			$_SESSION['session_id'] = session_id();
		}

		$cartModel = new \Cart($this->pdo);
		$cart = $cartModel->getOrCreateCart($_SESSION['session_id']);
		$cartItems = $cartModel->getItems($cart['id']);

		if (empty($cartItems)) {
			header('Location: ' . \BASE_URL . '/index.php');
			exit;
		}

		$checkoutContact = [
			'name' => '',
			'email' => '',
			'phone' => '',
		];
		$checkoutContact = [
			'name' => $checkoutUser['username'] ?? '',
			'email' => $checkoutUser['email'] ?? '',
			'phone' => $checkoutUser['mobile_phone'] ?? '',
		];

		$totalPrice = array_sum(array_column($cartItems, 'total_price'));
		require_once \base_path('templates/header.php');
		require_once \base_path('templates/checkout.php');
		require_once \base_path('templates/footer.php');
	}

	public function myOrders(): void {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (!isset($_SESSION['user_id'])) {
			header('Location: ' . \BASE_URL . '/login.php?redirect=my-orders.php');
			exit;
		}
		if (($_SESSION['user_role'] ?? '') === 'admin') {
			header('Location: ' . \BASE_URL . '/admin/orders.php');
			exit;
		}

		$orders = $this->orderModel->getByUserWithItemCount($_SESSION['user_id']);
		foreach ($orders as &$order) {
			$order['current_status'] = self::STATUS_COLORS[$order['status']] ?? self::STATUS_COLORS['pending'];
		}
		unset($order);

		require_once \base_path('templates/header.php');
		require_once \base_path('templates/my-orders.php');
		require_once \base_path('templates/footer.php');
	}

	public function showOrder($requestedOrderId): void {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$orderId = filter_var($requestedOrderId, FILTER_VALIDATE_INT);
		if ($orderId === false || $orderId < 1) {
			header('Location: ' . \BASE_URL . '/index.php?message=Order%20not%20found');
			exit;
		}

		$order = $this->orderModel->get($orderId);
		if (!$order) {
			header('Location: ' . \BASE_URL . '/index.php?message=Order%20not%20found');
			exit;
		}

		if (!$this->canViewOrder($order)) {
			http_response_code(403);
			header('Location: ' . \BASE_URL . '/index.php?message=Access%20denied');
			exit;
		}

		$currentStatus = self::STATUS_COLORS[$order['status']] ?? self::STATUS_COLORS['pending'];
		require_once \base_path('templates/header.php');
		require_once \base_path('templates/order.php');
		require_once \base_path('templates/footer.php');
	}

	public function details($requestedOrderId): void {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$orderId = filter_var($requestedOrderId, FILTER_VALIDATE_INT);
		if ($orderId === false || $orderId < 1) {
			http_response_code(400);
			exit('Invalid order ID');
		}

		$order = $this->orderModel->get($orderId);
		if (!$order) {
			http_response_code(404);
			exit('Order not found');
		}

		if (!$this->canViewOrder($order)) {
			http_response_code(403);
			exit('Access denied');
		}

		$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
		$currentStatus = self::STATUS_COLORS[$order['status']] ?? self::STATUS_COLORS['pending'];
		$backUrl = $isAdmin ? \BASE_URL . '/admin/orders.php' : \BASE_URL . '/my-orders.php';
		$backLabel = $isAdmin ? 'Back to Order Management' : 'Back to Order History';

		require_once \base_path('templates/order-details.php');
	}

	public function getApi($requestedOrderId): void {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$orderId = filter_var($requestedOrderId, FILTER_VALIDATE_INT);
		if ($orderId === false || $orderId < 1) {
			http_response_code(400);
			\Response::json(['success' => false, 'message' => 'Invalid order ID']);
		}

		$order = $this->orderModel->get($orderId);
		if (!$order) {
			http_response_code(404);
			\Response::json(['success' => false, 'message' => 'Order not found']);
		}

		if (!$this->canViewOrder($order)) {
			http_response_code(403);
			\Response::json(['success' => false, 'message' => 'Access denied']);
		}

		\Response::json(['success' => true, 'order' => $order]);
	}

	private function canViewOrder(array $order): bool {
		if (($_SESSION['user_role'] ?? '') === 'admin') {
			return true;
		}

		if (isset($_SESSION['user_id']) && $order['user_id'] !== null) {
			return (int) $order['user_id'] === (int) $_SESSION['user_id'];
		}

		if ($order['user_id'] === null && !empty($order['session_id'])) {
			$currentSessionId = $_SESSION['session_id'] ?? session_id();
			return hash_equals((string) $order['session_id'], (string) $currentSessionId);
		}

		return false;
	}
}

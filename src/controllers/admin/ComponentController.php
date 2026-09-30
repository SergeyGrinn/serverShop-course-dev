<?php

require_once base_path('src/Models/Component.php');
require_once base_path('src/Middleware/Admin.php');

class ComponentController {
	private const INVALID_CSRF_MESSAGE = 'Invalid CSRF token';
	private const COMPONENTS_URL = '/admin/components.php';

	private $componentModel;

	public function __construct($pdo) {
		Admin::check();
		$this->componentModel = new Component($pdo);
	}

	public function index() {
		$allComponents = $this->componentModel->getAll();
		require_once base_path('templates/admin/components-list.php');
	}

	public function create() {
		$errors = [];

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!$this->hasValidCsrfToken()) {
				$errors[] = self::INVALID_CSRF_MESSAGE;
			} else {
				$name = trim($_POST['name'] ?? '');
				$type = $_POST['type'] ?? '';
				$price = $_POST['price'] ?? '';
				$value = $this->buildComponentValue($type, $errors);

				$this->validateCommonFields($name, $type, $price, $errors);

				if (empty($errors)) {
					$this->componentModel->create($name, $type, $value, $price);
					$this->redirectToList();
				}
			}
		}

		require_once base_path('templates/admin/component-create.php');
	}

	public function edit($id) {
		$component = $this->componentModel->getById($id);
		if (!$component) {
			$this->redirectToList();
		}

		$errors = [];
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!$this->hasValidCsrfToken()) {
				$errors[] = self::INVALID_CSRF_MESSAGE;
			} else {
				$name = trim($_POST['name'] ?? '');
				$type = $_POST['type'] ?? '';
				$value = trim($_POST['value'] ?? '');
				$price = $_POST['price'] ?? '';

				$this->validateCommonFields($name, $type, $price, $errors);
				if ($value === '') {
					$errors[] = 'Component specifications are required';
				}

				if (empty($errors)) {
					$this->componentModel->update($id, $name, $type, $value, $price);
					$this->redirectToList();
				}

				$component = array_merge($component, [
					'name' => $name,
					'type' => $type,
					'value' => $value,
					'price' => $price,
				]);
			}
		}

		require_once base_path('templates/admin/component-edit.php');
	}

	public function delete($id) {
		if (!$this->hasValidCsrfToken()) {
			http_response_code(403);
			exit(self::INVALID_CSRF_MESSAGE);
		}

		$componentId = filter_var($id, FILTER_VALIDATE_INT);
		if ($componentId !== false && $componentId > 0) {
			$this->componentModel->delete($componentId);
		}

		$this->redirectToList();
	}

	private function hasValidCsrfToken(): bool {
		return csrf_verify($_POST['csrf_token'] ?? null);
	}

	private function redirectToList(): void {
		header('Location: ' . BASE_URL . self::COMPONENTS_URL);
		exit;
	}

	private function validateCommonFields(string $name, string $type, $price, array &$errors): void {
		if ($name === '') {
			$errors[] = 'Component name is required';
		}

		if (!in_array($type, ['cpu', 'gpu', 'ram', 'ssd', 'hdd'], true)) {
			$errors[] = 'Component type is invalid';
		}

		if ($price === '' || !is_numeric($price) || (float) $price < 0) {
			$errors[] = 'Price must be a non-negative number';
		}
	}

	private function buildComponentValue(string $type, array &$errors): string {
		$value = '';

		switch ($type) {
			case 'cpu':
				$cores = $_POST['cpu_cores'] ?? '';
				$frequency = $_POST['cpu_frequency'] ?? '';
				if (!is_numeric($cores) || (int) $cores < 1 || !is_numeric($frequency) || (float) $frequency <= 0) {
					$errors[] = 'CPU cores and frequency are required';
				}
				$value = "{$cores} cores, {$frequency} GHz";
				break;
			case 'gpu':
				$vram = $_POST['gpu_vram'] ?? '';
				if (!is_numeric($vram) || (int) $vram < 1) {
					$errors[] = 'GPU VRAM is required';
				}
				$value = "{$vram} GB VRAM";
				break;
			case 'ram':
				$capacity = $_POST['ram_capacity'] ?? '';
				if (!is_numeric($capacity) || (int) $capacity < 1) {
					$errors[] = 'RAM capacity is required';
				}
				$value = "{$capacity} GB";
				break;
			case 'ssd':
			case 'hdd':
				$capacity = $_POST['storage_capacity'] ?? '';
				if (!is_numeric($capacity) || (int) $capacity < 1) {
					$errors[] = 'Storage capacity is required';
				}
				$value = "{$capacity} GB";
				break;
			default:
				$errors[] = 'Component type is invalid';
				break;
		}

		return $value;
	}
}

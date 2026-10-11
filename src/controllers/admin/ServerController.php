<?php

require_once base_path('src/Models/Server.php');
require_once base_path('src/Models/Component.php');
require_once base_path('src/Models/ServerComponent.php');
require_once base_path('src/Middleware/Admin.php');

class ServerController {
    private $serverModel;
    private $componentModel;
    private $serverComponentModel;
    private $pdo;

    public function __construct($pdo) {
        Admin::check();
        $this->pdo = $pdo;
        $this->serverModel = new Server($pdo);
        $this->componentModel = new Component($pdo);
        $this->serverComponentModel = new ServerComponent($pdo);
    }

    public function index() {
        $servers = $this->serverModel->getAllServersAdmin();
        require base_path('templates/admin/servers-list.php');
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = 'emptyimage.png';
            $base_price = $_POST['base_price'] ?? '';

            $errors = [];
            $hardware = $this->getHardwareDefaults($_POST, $errors);

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if ($base_price === '' || !is_numeric($base_price) || (float) $base_price < 0) {
                $errors[] = 'Price must be a non-negative number';
            }

            if (empty($errors) && $this->hasUploadedImage()) {
                $image = $this->storeServerImage($_FILES['image'], $errors) ?? $image;
            }

            if (empty($errors)) {
                $this->serverModel->create([
                    'name' => $name,
                    'description' => $description,
                    'image' => $image,
                    'base_price' => $base_price,
                    'default_ram' => $hardware['default_ram'],
                    'default_storage' => $hardware['default_storage'],
                    'default_cpu_cores' => $hardware['default_cpu_cores'],
                    'default_gpu_vram' => $hardware['default_gpu_vram'],
                ]);
                header('Location: ' . BASE_URL . '/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-create.php');
    }

    public function edit($id) {
        $server = $this->serverModel->getByIdAdmin($id);
        
        if (!$server) {
            header('Location: ' . BASE_URL . '/admin/servers.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $server['image'];
            $base_price = $_POST['base_price'] ?? '';
            $available = $server['available'];

            $errors = [];
            $hardware = $this->getHardwareDefaults($_POST, $errors);

            if (empty($name)) $errors[] = 'Name is required';
            if (empty($description)) $errors[] = 'Description is required';
            if ($base_price === '' || !is_numeric($base_price) || (float) $base_price < 0) {
                $errors[] = 'Price must be a non-negative number';
            }

            if (empty($errors) && $this->hasUploadedImage()) {
                $image = $this->storeServerImage($_FILES['image'], $errors) ?? $image;
            }

            if (empty($errors)) {
                $this->serverModel->update($id, [
                    'name' => $name,
                    'description' => $description,
                    'image' => $image,
                    'base_price' => $base_price,
                    'available' => $available,
                    'default_ram' => $hardware['default_ram'],
                    'default_storage' => $hardware['default_storage'],
                    'default_cpu_cores' => $hardware['default_cpu_cores'],
                    'default_gpu_vram' => $hardware['default_gpu_vram'],
                ]);
                header('Location: ' . BASE_URL . '/admin/servers.php');
                exit;
            }
        }

        require base_path('templates/admin/server-edit.php');
    }

    private function hasUploadedImage(): bool {
        return isset($_FILES['image'])
            && is_array($_FILES['image'])
            && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function storeServerImage(array $upload, array &$errors): ?string {
        $uploadError = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError !== UPLOAD_ERR_OK) {
            $errors[] = in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'PHP rejected the upload before the application could inspect its file type. Increase upload_max_filesize and post_max_size to check this file.'
                : 'Image upload failed';
            return null;
        }

        $temporaryPath = $upload['tmp_name'] ?? '';
        if (!is_string($temporaryPath) || $temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            $errors[] = 'Uploaded file is invalid';
            return null;
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($temporaryPath);

        if (!isset($allowedTypes[$mimeType])) {
            $errors[] = 'Only valid JPEG, PNG, WebP, and sanitized SVG images are allowed';
            return null;
        }

        $fileSize = filesize($temporaryPath);
        $maximumImageSize = 5 * 1024 * 1024;
        if ($fileSize === false || $fileSize < 1 || $fileSize > $maximumImageSize) {
            $errors[] = 'Image must be 5 MB or smaller';
            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
        $destination = base_path('public/assets/images/' . $filename);

        if ($mimeType === 'image/svg+xml') {
            $safeSvg = $this->sanitizeSvg($temporaryPath);
            if ($safeSvg === null) {
                $errors[] = 'SVG is invalid or contains unsupported active content';
                return null;
            }

            $writtenBytes = file_put_contents($destination, $safeSvg, LOCK_EX);
            if ($writtenBytes !== strlen($safeSvg)) {
                if (is_file($destination)) {
                    unlink($destination);
                }
                $errors[] = 'Could not save sanitized SVG image';
                return null;
            }

            return $filename;
        }

        $imageInfo = @getimagesize($temporaryPath);
        if ($imageInfo === false || ($imageInfo['mime'] ?? null) !== $mimeType) {
            $errors[] = 'Image content does not match its detected type';
            return null;
        }
        if ($imageInfo[0] < 1 || $imageInfo[1] < 1 || $imageInfo[0] * $imageInfo[1] > 25000000) {
            $errors[] = 'Image dimensions are too large';
            return null;
        }
        if (!move_uploaded_file($temporaryPath, $destination)) {
            $errors[] = 'Could not save uploaded image';
            return null;
        }

        return $filename;
    }

    private function sanitizeSvg(string $path): ?string {
        $svgNamespace = 'http://www.w3.org/2000/svg';
        $allowedElements = array_fill_keys([
            'svg', 'g', 'defs', 'path', 'rect', 'circle', 'ellipse', 'line',
            'polyline', 'polygon', 'linearGradient', 'radialGradient', 'stop',
            'clipPath', 'mask', 'title', 'desc',
        ], true);
        $allowedAttributes = array_fill_keys([
            'xmlns', 'id', 'viewBox', 'width', 'height', 'preserveAspectRatio',
            'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry',
            'd', 'points', 'fill', 'fill-opacity', 'fill-rule', 'stroke',
            'stroke-width', 'stroke-opacity', 'stroke-linecap', 'stroke-linejoin',
            'stroke-miterlimit', 'opacity', 'transform', 'offset', 'stop-color',
            'stop-opacity', 'gradientUnits', 'gradientTransform', 'clipPathUnits',
            'maskUnits', 'maskContentUnits', 'clip-path', 'mask',
        ], true);

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        $previousLibxmlState = libxml_use_internal_errors(true);
        try {
            $loaded = $document->load(
                $path,
                LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlState);
        }

        $root = $document->documentElement;
        if (!$loaded || $document->doctype !== null || !$root || $root->localName !== 'svg' || $root->namespaceURI !== $svgNamespace) {
            return null;
        }

        $viewBox = preg_split('/[\s,]+/', trim($root->getAttribute('viewBox')));
        if (count($viewBox) !== 4 || !array_reduce($viewBox, static fn($valid, $value) => $valid && is_numeric($value), true)) {
            return null;
        }
        $viewBoxWidth = (float) $viewBox[2];
        $viewBoxHeight = (float) $viewBox[3];
        if ($viewBoxWidth <= 0 || $viewBoxHeight <= 0 || $viewBoxWidth > 8192 || $viewBoxHeight > 8192 || $viewBoxWidth * $viewBoxHeight > 25000000) {
            return null;
        }

        $sanitizeAttributes = static function (DOMElement $element) use ($allowedAttributes, $svgNamespace, $root): void {
            for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
                $attribute = $element->attributes->item($index);
                if (!$attribute) {
                    continue;
                }

                $attributeName = $attribute->nodeName;
                $isRootNamespace = $element === $root
                    && $attributeName === 'xmlns'
                    && $attribute->value === $svgNamespace;
                if ($isRootNamespace) {
                    continue;
                }
                if (($attribute->namespaceURI !== null && $attribute->namespaceURI !== '') || !isset($allowedAttributes[$attributeName])) {
                    $element->removeAttributeNode($attribute);
                    continue;
                }

                $value = trim($attribute->value);
                if (in_array($attributeName, ['fill', 'stroke', 'clip-path', 'mask'], true)
                    && stripos($value, 'url(') !== false
                    && !preg_match('/^url\(\s*#[-_a-zA-Z0-9:.]+\s*\)$/i', $value)) {
                    $element->removeAttributeNode($attribute);
                    continue;
                }
                if ($attributeName === 'id' && !preg_match('/^[A-Za-z_][A-Za-z0-9_.:-]*$/', $value)) {
                    $element->removeAttributeNode($attribute);
                    continue;
                }
                if (in_array($attributeName, ['width', 'height'], true)
                    && (!preg_match('/^\d+(?:\.\d+)?(?:px|pt|pc|mm|cm|in|%)?$/i', $value) || (float) $value > 8192)) {
                    $element->removeAttributeNode($attribute);
                }
            }
        };

        $elementCount = 1;
        $sanitizeChildren = function (DOMNode $parent) use (&$sanitizeChildren, &$elementCount, $allowedElements, $svgNamespace, $sanitizeAttributes): bool {
            for ($child = $parent->firstChild; $child !== null;) {
                $next = $child->nextSibling;
                if ($child instanceof DOMElement) {
                    $elementCount++;
                    if ($elementCount > 5000 || $child->namespaceURI !== $svgNamespace || !isset($allowedElements[$child->localName])) {
                        $parent->removeChild($child);
                        $child = $next;
                        continue;
                    }

                    $sanitizeAttributes($child);
                    if (!$sanitizeChildren($child)) {
                        return false;
                    }
                } elseif ($child instanceof DOMText || $child instanceof DOMCdataSection) {
                    if (!in_array($parent->localName, ['title', 'desc'], true)) {
                        $parent->removeChild($child);
                    }
                } else {
                    $parent->removeChild($child);
                }
                $child = $next;
            }

            return $elementCount <= 5000;
        };

        $sanitizeAttributes($root);
        if (!$sanitizeChildren($root)) {
            return null;
        }

        return $document->saveXML($root) ?: null;
    }

    private function getHardwareDefaults(array $input, array &$errors) {
        $defaults = [
            'default_ram' => null,
            'default_storage' => null,
            'default_cpu_cores' => null,
            'default_gpu_vram' => null,
        ];

        if (!isset($input['has_built_in_hardware'])) {
            return $defaults;
        }

        $fields = [
            'default_ram' => ['ram', 'RAM'],
            'default_storage' => ['storage', 'Storage'],
            'default_cpu_cores' => ['cpu_cores', 'CPU cores'],
            'default_gpu_vram' => ['gpu_vram', 'GPU memory'],
        ];

        $providedCount = 0;
        foreach ($fields as $column => [$field, $label]) {
            $rawValue = $input[$field] ?? '';
            if ($rawValue === '') {
                continue;
            }

            $value = $this->parseHardwareValue($column, $rawValue, $label, $errors);
            if ($value === null) {
                continue;
            }

            $defaults[$column] = $value;
            $providedCount++;
        }

        if ($providedCount === 0 && empty($errors)) {
            $errors[] = 'Enter at least one built-in hardware value';
        }

        return $defaults;
    }

    private function parseHardwareValue(string $column, string $value, string $label, array &$errors) {
        $parsedValue = null;

        if ($column === 'default_ram') {
            $ramValue = trim($value);
            if (is_numeric($ramValue) && (float) $ramValue > 0 && preg_match('/^\d+(?:\.\d{1,3})?$/', $ramValue)) {
                $parsedValue = (float) $ramValue;
            } else {
                $errors[] = $label . ' must be positive and use at most 3 decimal places';
            }
        } elseif (filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0) {
            $parsedValue = (int) $value;
        } else {
            $errors[] = $label . ' must be a positive whole number';
        }

        return $parsedValue;
    }

    public function toggleAvailability($id) {
        $this->serverModel->toggleAvailability($id);
        header('Location: ' . BASE_URL . '/admin/servers.php');
        exit;
    }
    
    public function delete($id) {
        $this->serverModel->delete($id);
        header('Location: ' . BASE_URL . '/admin/servers.php');
        exit;
    }

    public function components($id) {
    $server = $this->serverModel->getByIdAdmin($id);
    
    if (!$server) {
        header('Location: ' . BASE_URL . '/admin/servers.php');
        exit;
    }

    // Creation of a new component
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sub_action']) && $_POST['sub_action'] === 'create_component') {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? '';
        $price = $_POST['price'] ?? 0;
        $value = '';

        $errors = [];
        if ($price === '' || !is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Price must be a non-negative number';
        }

        switch ($type) {
            case 'cpu':
                $value = "{$_POST['cpu_cores']} cores, {$_POST['cpu_frequency']} GHz";
                break;
            case 'gpu':
                $value = "{$_POST['gpu_vram']} GB VRAM";
                break;
            case 'ram':
                $value = "{$_POST['ram_capacity']} GB";
                break;
            case 'ssd':
            case 'hdd':
                $value = "{$_POST['storage_capacity']} GB";
                break;
        }

        if (empty($errors)) {
            $this->componentModel->create($name, $type, $value, $price);
            header('Location: ' . BASE_URL . '/admin/servers.php?action=components&id=' . $id);
            exit;
        }
    }

    // Saving the selected components for the server
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['sub_action'] ?? '') !== 'create_component') {
        $component_ids = $_POST['components'] ?? [];
        $current_components = $this->serverComponentModel->getServerComponents($id);
        
        foreach ($current_components as $comp_id) {
            if (!in_array($comp_id, $component_ids)) {
                $this->serverComponentModel->detachComponent($id, $comp_id);
            }
        }
        
        foreach ($component_ids as $comp_id) {
            if (!in_array($comp_id, $current_components)) {
                $this->serverComponentModel->attachComponent($id, $comp_id);
            }
        }

        header("Location: servers.php?action=components&id={$id}");
        exit;
    }

    $allComponents = $this->componentModel->getAll();
    $serverComponents = $this->serverComponentModel->getServerComponents($id);
    $componentModel = $this->componentModel;
    
    require base_path('templates/admin/server-components.php');
}
}

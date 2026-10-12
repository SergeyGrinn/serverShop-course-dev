<?php

use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    private ?\PDO $pdo = null;
    private ?string $sessionId = null;
    private ?int $serverId = null;
    private ?int $componentId = null;

    protected function setUp(): void
    {
        $dsn = getenv('TEST_DB_DSN');
        if (!$dsn) {
            $this->markTestSkipped('Set TEST_DB_DSN to run the MySQL order integration test.');
        }

        if (!preg_match('/(?:^|;)dbname=([^;]+)/i', $dsn, $matches)
            || strtolower($matches[1]) !== 'server_eshop_test'
        ) {
            self::fail('TEST_DB_DSN must point to the separate server_eshop_test database.');
        }

        $this->pdo = new \PDO(
            $dsn,
            getenv('TEST_DB_USER') ?: 'root',
            getenv('TEST_DB_PASS') ?: '',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        require_once dirname(__DIR__) . '/src/Models/Order.php';

        $this->sessionId = 'phpunit_' . bin2hex(random_bytes(16));
        $statement = $this->pdo->prepare(
            'INSERT INTO servers (name, description, base_price) VALUES (:name, :description, :base_price)'
        );
        $statement->execute([
            ':name' => 'PHPUnit test server',
            ':description' => 'Temporary integration-test fixture',
            ':base_price' => 100.00,
        ]);
        $this->serverId = (int) $this->pdo->lastInsertId();

        $statement = $this->pdo->prepare(
            'INSERT INTO components (name, type, value, price, server_id) VALUES (:name, :type, :value, :price, :server_id)'
        );
        $statement->execute([
            ':name' => 'PHPUnit test component',
            ':type' => 'ram',
            ':value' => '8 GB',
            ':price' => 49.50,
            ':server_id' => $this->serverId,
        ]);
        $this->componentId = (int) $this->pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        if ($this->pdo !== null && $this->sessionId !== null) {
            $statement = $this->pdo->prepare(
                'DELETE oi FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id WHERE o.session_id = :session_id'
            );
            $statement->execute([':session_id' => $this->sessionId]);

            $statement = $this->pdo->prepare('DELETE FROM orders WHERE session_id = :session_id');
            $statement->execute([':session_id' => $this->sessionId]);
        }

        if ($this->pdo !== null && $this->serverId !== null) {
            $statement = $this->pdo->prepare('DELETE FROM servers WHERE id = :id');
            $statement->execute([':id' => $this->serverId]);
        }

        if ($this->pdo !== null && $this->componentId !== null) {
            $statement = $this->pdo->prepare('DELETE FROM components WHERE id = :id');
            $statement->execute([':id' => $this->componentId]);
        }

        parent::tearDown();
    }

    public function testOrderCanBeCreatedReadAndTransitioned(): void
    {
        $order = new Order($this->pdo);
        $orderId = $order->create([
            'user_id' => null,
            'session_id' => $this->sessionId,
            'total_price' => 149.50,
            'name' => 'PHPUnit Test',
            'email' => 'phpunit@example.test',
            'phone' => null,
            'comment' => 'Integration test fixture',
        ], [[
            'server_id' => $this->serverId,
            'component_ids' => json_encode([$this->componentId]),
            'total_price' => 149.50,
        ]]);

        self::assertNotSame('', $orderId);

        $createdOrder = $order->get($orderId);
        self::assertIsArray($createdOrder);
        self::assertSame('pending', $createdOrder['status']);
        self::assertSame('149.50', $createdOrder['total_price']);
        self::assertCount(1, $createdOrder['items']);
        self::assertSame('PHPUnit test server', $createdOrder['items'][0]['server_name']);
        self::assertSame('100.00', $createdOrder['items'][0]['server_base_price']);
        self::assertSame('PHPUnit test component', $createdOrder['items'][0]['components'][0]['name']);

        self::assertTrue($order->transitionStatus($orderId, 'pending', 'paid'));
        self::assertFalse($order->transitionStatus($orderId, 'pending', 'cancelled'));
        self::assertSame('paid', $order->get($orderId)['status']);

        $deleteServer = $this->pdo->prepare('DELETE FROM servers WHERE id = :id');
        $deleteServer->execute([':id' => $this->serverId]);
        $deleteComponent = $this->pdo->prepare('DELETE FROM components WHERE id = :id');
        $deleteComponent->execute([':id' => $this->componentId]);

        $preservedOrder = $order->get($orderId);
        self::assertCount(1, $preservedOrder['items']);
        self::assertSame('PHPUnit test server', $preservedOrder['items'][0]['server_name']);
        self::assertSame('PHPUnit test component', $preservedOrder['items'][0]['components'][0]['name']);
    }
}
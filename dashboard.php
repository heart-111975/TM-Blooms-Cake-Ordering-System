<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Direktang koneksyon sa database para maiwasan ang error sa db.php
$host = 'localhost';
$dbname = 'cake_ordering_system';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// HANDLE UPDATE & DELETE (CRUD Operations)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $orderId = (int)$_POST['order_id'];
        $newStatus = trim($_POST['status']);
        
        $updateStmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $updateStmt->execute(['status' => $newStatus, 'id' => $orderId]);
        header("Location: dashboard.php");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_order') {
        $orderId = (int)$_POST['order_id'];
        
        $deleteStmt = $conn->prepare("DELETE FROM orders WHERE id = :id");
        $deleteStmt->execute(['id' => $orderId]);
        header("Location: dashboard.php");
        exit;
    }
}

$totalOrders = 0;
$pendingOrders = 0;
$confirmedOrders = 0;
$completedOrders = 0;
$orders = [];
$search = trim($_GET['search'] ?? '');
$errorMsg = "";

try {
    $totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
    $confirmedOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Confirmed'")->fetchColumn();
    $completedOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Completed'")->fetchColumn();

    $stmt = $conn->prepare("
        SELECT * FROM orders
        WHERE customer_name LIKE :search
        OR cake_name LIKE :search
        ORDER BY created_at DESC
    ");

    $stmt->execute(['search' => "%$search%"]);
    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    $errorMsg = "Database error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T&M Blooms | Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #fff7f8;
            color: #49383b;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background: #f5dce2;
            position: fixed;
            top: 0;
            left: 0;
            padding: 30px 20px;
        }

        .brand {
            text-align: center;
            font-size: 23px;
            font-weight: bold;
            color: #9b5369;
            margin-bottom: 40px;
        }

        .sidebar a {
            display: block;
            text-decoration: none;
            color: #684653;
            padding: 14px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #e9b9c7;
            color: white;
        }

        .main {
            margin-left: 240px;
            padding: 35px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            color: #9b5369;
            margin-bottom: 8px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .card h3 {
            font-size: 15px;
            color: #927780;
            margin-bottom: 15px;
        }

        .card p {
            font-size: 30px;
            font-weight: bold;
            color: #a65370;
        }

        .orders {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .orders h2 {
            color: #9b5369;
            margin-bottom: 20px;
        }

        .search {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search input {
            width: 280px;
            padding: 11px;
            border: 1px solid #e5c4ce;
            border-radius: 7px;
        }

        .search button {
            padding: 11px 18px;
            border: none;
            background: #c77f98;
            color: white;
            border-radius: 7px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #f0e1e5;
            font-size: 14px;
        }

        th {
            background: #f9e9ed;
            color: #805064;
        }

        .action-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .action-form select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #e5c4ce;
            font-size: 12px;
        }

        .update-btn {
            background: #5b92e5;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .delete-btn {
            background: #d9534f;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #999;
        }

        @media (max-width: 900px) {
            .cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .main {
                padding: 20px;
            }

            .orders {
                overflow-x: auto;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                width: 180px;
            }

            .main {
                margin-left: 180px;
            }

            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="sidebar">
    <div class="brand">
        T&M BLOOMS
    </div>

    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="orders.php">Orders</a>
    <a href="index.php">Customer Website</a>
</div>

<div class="main">

    <div class="header">
        <h1>Dashboard</h1>
        <p>Welcome to T&M Blooms Cake Ordering System!</p>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div style="background: #ffe8e8; color: #a33b3b; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <?= $errorMsg ?>
        </div>
    <?php endif; ?>

    <div class="cards">
        <div class="card">
            <h3>Total Orders</h3>
            <p><?= (int)$totalOrders ?></p>
        </div>

        <div class="card">
            <h3>Pending Orders</h3>
            <p><?= (int)$pendingOrders ?></p>
        </div>

        <div class="card">
            <h3>Confirmed Orders</h3>
            <p><?= (int)$confirmedOrders ?></p>
        </div>

        <div class="card">
            <h3>Completed Orders</h3>
            <p><?= (int)$completedOrders ?></p>
        </div>
    </div>

    <div class="orders">
        <h2>Recent Cake Orders</h2>

        <form method="GET" class="search">
            <input
                type="text"
                name="search"
                placeholder="Search customer or cake..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Cake</th>
                    <th>Size / Flavor</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Pickup Date</th>
                    <th>Status & Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['cake_name']) ?></td>
                            <td><?= htmlspecialchars($order['size'] ?? '') ?></td>
                            <td><?= (int)$order['quantity'] ?></td>
                            <td>₱<?= number_format((float)$order['price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['pickup_date']) ?></td>
                            <td>
                                <!-- UPDATE & DELETE FORMS (CRUD) -->
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <select name="status">
                                            <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Confirmed" <?= $order['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                        <button type="submit" class="update-btn">Update</button>
                                    </form>

                                    <form method="POST" onsubmit="return confirm('Sigurado ka bang gusto mong i-delete ang order na ito?');">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="delete_order">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty">
                            No cake orders found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</div>

</body>
</html>
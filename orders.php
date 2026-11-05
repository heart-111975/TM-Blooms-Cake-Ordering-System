
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'dashboard.php';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int) $_POST['order_id'];
    $status = $_POST['status'];

    $allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

    if (in_array($status, $allowedStatuses, true)) {
        $stmt = $conn->prepare(
            "UPDATE orders SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $orderId]);
    }

    header("Location: orders.php");
    exit;
}

// Get all orders
$stmt = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T&M Blooms | Orders</title>

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

        h1 {
            color: #9b5369;
            margin-bottom: 8px;
        }

        .subtitle {
            margin-bottom: 25px;
        }

        .orders-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
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

        select {
            padding: 8px;
            border: 1px solid #e5c4ce;
            border-radius: 6px;
        }

        button {
            padding: 8px 12px;
            background: #c77f98;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #a65370;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #999;
        }

        @media (max-width: 700px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="sidebar">
    <div class="brand">T&M BLOOMS</div>

    <a href="dashboard.php">Dashboard</a>
    <a href="orders.php" class="active">Orders</a>
    <a href="index.php">Customer Website</a>
</div>

<div class="main">

    <h1>Manage Orders</h1>
    <p class="subtitle">View and update your customers' cake orders.</p>

    <div class="orders-container">

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Cake</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Pickup Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($orders) > 0): ?>

                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['contact_number']) ?></td>
                            <td><?= htmlspecialchars($order['cake_name']) ?></td>
                            <td><?= htmlspecialchars($order['size']) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td>₱<?= number_format((float)$order['price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['pickup_date']) ?></td>

                            <form method="POST">
                                <td>
                                    <select name="status">
                                        <?php
                                        $statuses = [
                                            'Pending',
                                            'Confirmed',
                                            'Completed',
                                            'Cancelled'
                                        ];
                                        foreach ($statuses as $status):
                                        ?>
                                            <option value="<?= $status ?>"
                                                <?= $order['status'] === $status ? 'selected' : '' ?>>
                                                <?= $status ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td>
                                    <input type="hidden" name="order_id"
                                           value="<?= (int)$order['id'] ?>">
                                    <button type="submit" name="update_status">
                                        Update
                                    </button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="10" class="empty">
                            No cake orders available yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</div>

</body>
</html>

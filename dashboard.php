
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'cake_ordering_system';
$username = 'root';
$password = '';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ESCAPE OUTPUT
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// DASHBOARD DATA
$totalOrders = 0;
$pendingOrders = 0;
$confirmedOrders = 0;
$completedOrders = 0;
$orders = [];
$search = trim($_GET['search'] ?? '');
$errorMsg = '';

try {

    // ORDER COUNTS
    $totalOrders = $conn->query(
        "SELECT COUNT(*) FROM orders"
    )->fetchColumn();

    $pendingOrders = $conn->query(
        "SELECT COUNT(*) FROM orders WHERE status = 'Pending'"
    )->fetchColumn();

    $confirmedOrders = $conn->query(
        "SELECT COUNT(*) FROM orders WHERE status = 'Confirmed'"
    )->fetchColumn();

    $completedOrders = $conn->query(
        "SELECT COUNT(*) FROM orders WHERE status = 'Completed'"
    )->fetchColumn();

    // RECENT 5 ORDERS
    // Latest five orders, displayed oldest-first within that group.
    $stmt = $conn->prepare("
        SELECT *
        FROM (
            SELECT *
            FROM orders
            WHERE customer_name LIKE :search1
            OR cake_name LIKE :search2
            ORDER BY created_at DESC, id DESC
            LIMIT 5
        ) AS recent_orders
        ORDER BY created_at ASC, id ASC
    ");

    $keyword = "%{$search}%";

    $stmt->execute([
        'search1' => $keyword,
        'search2' => $keyword
    ]);

    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    $errorMsg = "Unable to retrieve order information.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>T&M Blooms | Admin Dashboard</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #fff8fa;
    color: #553943;
}

/* SIDEBAR */
.sidebar {
    width: 245px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, #f9dfe7, #fcebf0);
    padding: 30px 20px;
    border-right: 1px solid #f2d0da;
    z-index: 100;
}

.brand {
    text-align: center;
    margin-bottom: 45px;
}

.brand-icon {
    width: 75px;
    height: 75px;
    margin: auto auto 12px;
    border-radius: 50%;
    background: #fff;
    color: #d77c99;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    box-shadow: 0 5px 15px rgba(190, 105, 130, .12);
}

.brand h2 {
    font-family: 'Playfair Display', serif;
    color: #974b65;
    font-size: 25px;
}

.brand p {
    font-size: 11px;
    color: #a46b7d;
    margin-top: 5px;
}

.nav-title {
    font-size: 10px;
    font-weight: 700;
    color: #b27b8d;
    letter-spacing: 1.5px;
    margin: 25px 12px 12px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 13px 15px;
    margin-bottom: 8px;
    border-radius: 12px;
    color: #744c5b;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: .3s;
}

.sidebar a i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #df8da7;
    color: white;
    box-shadow: 0 5px 12px rgba(205, 116, 146, .2);
}

/* MAIN CONTENT */
.main {
    margin-left: 245px;
    padding: 30px 35px;
    min-height: 100vh;
}

/* TOP HEADER */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    gap: 15px;
}

.topbar h1 {
    font-size: 26px;
    color: #87465d;
    font-family: 'Playfair Display', serif;
}

.topbar p {
    color: #a7838e;
    font-size: 12px;
    margin-top: 5px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 9px 15px;
    border-radius: 30px;
    box-shadow: 0 3px 12px rgba(120, 70, 85, .06);
    font-size: 12px;
    font-weight: 600;
}

.admin-profile i {
    background: #f5d7e1;
    color: #a95774;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* WELCOME BANNER */
.welcome-banner {
    background: linear-gradient(110deg, #f8dce5, #fcebf0);
    border: 1px solid #f3d3dd;
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
}

.welcome-banner::after {
    content: "♡";
    position: absolute;
    right: 35px;
    top: -35px;
    font-size: 160px;
    color: rgba(255,255,255,.5);
}

.welcome-banner h2 {
    font-family: 'Playfair Display', serif;
    color: #87465d;
    font-size: 28px;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
}

.welcome-banner p {
    color: #895e6c;
    font-size: 13px;
    position: relative;
    z-index: 1;
}

/* SUMMARY CARDS */
.cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}

.card {
    background: white;
    padding: 21px;
    border-radius: 17px;
    border: 1px solid #f4e1e7;
    box-shadow: 0 4px 15px rgba(140, 75, 95, .05);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: .3s;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(140, 75, 95, .10);
}

.card-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
}

.card:nth-child(1) .card-icon {
    background: #fce2eb;
    color: #cf7192;
}

.card:nth-child(2) .card-icon {
    background: #fff0d9;
    color: #d99b45;
}

.card:nth-child(3) .card-icon {
    background: #e5efff;
    color: #6595d8;
}

.card:nth-child(4) .card-icon {
    background: #e3f5e9;
    color: #63a87c;
}

.card h3 {
    font-size: 11px;
    color: #a48a93;
    font-weight: 500;
    margin-bottom: 5px;
}

.card p {
    font-size: 25px;
    font-weight: 700;
    color: #784558;
}

/* RECENT ORDERS SECTION */
.orders {
    background: white;
    padding: 25px;
    border-radius: 18px;
    border: 1px solid #f3e1e6;
    box-shadow: 0 4px 15px rgba(140, 75, 95, .05);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 22px;
}

.section-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 21px;
    color: #87465d;
}

.section-header p {
    color: #ad929b;
    font-size: 11px;
    margin-top: 4px;
}

/* SEARCH */
.search {
    display: flex;
    gap: 9px;
}

.search input {
    width: 250px;
    max-width: 100%;
    padding: 11px 14px;
    border: 1px solid #efd6df;
    background: #fffafb;
    border-radius: 10px;
    outline: none;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
}

.search input:focus {
    border-color: #d9829f;
    box-shadow: 0 0 0 3px rgba(217, 130, 159, .12);
}

.search button {
    padding: 10px 17px;
    border: none;
    border-radius: 10px;
    background: #d77e9b;
    color: white;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    transition: .3s;
}

.search button:hover {
    background: #bd6482;
}

/* VIEW ALL BUTTON */
.view-all {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #bd6683;
    font-size: 11px;
    font-weight: 600;
    background: #fceaf0;
    padding: 10px 15px;
    border-radius: 10px;
    transition: .3s;
}

.view-all:hover {
    background: #f5d4df;
}

/* TABLE */
.table-container {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #f2e3e8;
    border-radius: 12px;
}

table {
    width: 100%;
    min-width: 720px;
    border-collapse: collapse;
}

th {
    background: #fceef2;
    color: #875468;
    font-size: 11px;
    font-weight: 600;
    text-align: left;
    padding: 15px 13px;
    white-space: nowrap;
}

td {
    padding: 14px 13px;
    border-bottom: 1px solid #f5e9ed;
    font-size: 11px;
    color: #70535e;
    white-space: nowrap;
}

tbody tr {
    transition: .2s;
}

tbody tr:hover {
    background: #fff9fb;
}

tbody tr:last-child td {
    border-bottom: none;
}

.order-id {
    color: #bc6d88;
    font-weight: 600;
}

.customer-name {
    font-weight: 600;
    color: #704455;
}

.price {
    font-weight: 700;
    color: #a75270;
}

/* STATUS BADGES */
.status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
}

.status-pending {
    background: #fff1d8;
    color: #bd8532;
}

.status-confirmed {
    background: #e5efff;
    color: #5884c2;
}

.status-completed {
    background: #e2f5e8;
    color: #4b9b68;
}

/* EMPTY STATE */
.empty {
    text-align: center;
    padding: 45px 20px;
    color: #b69ca5;
}

.empty i {
    display: block;
    font-size: 35px;
    color: #e5bdca;
    margin-bottom: 12px;
}

.empty p {
    font-size: 12px;
}

/* ERROR */
.error-message {
    background: #fff0f0;
    border: 1px solid #f2caca;
    color: #a94442;
    padding: 13px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 12px;
}

/* FOOTER */
.footer {
    text-align: center;
    color: #b69ba5;
    font-size: 10px;
    padding: 25px 0 10px;
}

/* RESPONSIVE */
@media (max-width: 1100px) {
    .cards {
        grid-template-columns: repeat(2, 1fr);
    }

    .main {
        padding: 25px;
    }
}

@media (max-width: 700px) {
    .sidebar {
        width: 75px;
        padding: 25px 10px;
    }

    .brand h2,
    .brand p,
    .nav-title,
    .sidebar a span {
        display: none;
    }

    .brand-icon {
        width: 48px;
        height: 48px;
        font-size: 22px;
    }

    .sidebar a {
        justify-content: center;
        padding: 14px 5px;
    }

    .sidebar a i {
        font-size: 18px;
    }

    .main {
        margin-left: 75px;
        padding: 20px 14px;
    }

    .topbar h1 {
        font-size: 22px;
    }

    .welcome-banner {
        padding: 23px 20px;
    }

    .welcome-banner h2 {
        font-size: 22px;
    }

    .cards {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .card {
        padding: 14px 10px;
        gap: 9px;
    }

    .card-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        font-size: 15px;
    }

    .card p {
        font-size: 21px;
    }

    .card h3 {
        font-size: 9px;
    }

    .orders {
        padding: 17px 12px;
    }

    .section-header {
        align-items: flex-start;
    }

    .search {
        width: 100%;
    }

    .search input {
        flex: 1;
        width: 100%;
    }

    .admin-profile span {
        display: none;
    }
}

@media (max-width: 380px) {
    .cards {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="brand">
        <div class="brand-icon">
            <i class="fa-solid fa-cake-candles"></i>
        </div>

        <h2>T&M Blooms</h2>
        <p>Cake Ordering System ♡</p>
    </div>

    <div class="nav-title">MAIN MENU</div>

    <a href="dashboard.php" class="active">
        <i class="fa-solid fa-house"></i>
        <span>Dashboard</span>
    </a>

    <a href="orders.php">
        <i class="fa-solid fa-bag-shopping"></i>
        <span>Manage Orders</span>
    </a>

    <a href="index.php">
        <i class="fa-solid fa-store"></i>
        <span>Customer Website</span>
    </a>

</div>

<!-- MAIN CONTENT -->
<div class="main">

    <!-- HEADER -->
    <div class="topbar">
        <div>
            <h1>Dashboard</h1>
            <p>Manage your bakery orders and activities.</p>
        </div>

        <div class="admin-profile">
            <i class="fa-solid fa-user"></i>
            <span>Administrator</span>
        </div>
    </div>

    <!-- WELCOME BANNER -->
    <div class="welcome-banner">
        <h2>Welcome back, Admin! ♡</h2>
        <p>Here's what's happening at T&M Blooms today.</p>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div class="error-message">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= e($errorMsg) ?>
        </div>
    <?php endif; ?>

    <!-- SUMMARY CARDS -->
    <div class="cards">

        <div class="card">
            <div class="card-icon">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <h3>Total Orders</h3>
                <p><?= (int)$totalOrders ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h3>Pending Orders</h3>
                <p><?= (int)$pendingOrders ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-icon">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div>
                <h3>Confirmed Orders</h3>
                <p><?= (int)$confirmedOrders ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-icon">
                <i class="fa-solid fa-cake-candles"></i>
            </div>
            <div>
                <h3>Completed Orders</h3>
                <p><?= (int)$completedOrders ?></p>
            </div>
        </div>

    </div>

    <!-- RECENT CAKE ORDERS -->
    <div class="orders">

        <div class="section-header">

            <div>
                <h2>Recent Cake Orders</h2>
                <p>Latest customer orders, arranged First Come, First Served.</p>
            </div>

            <a href="orders.php" class="view-all">
                Manage All Orders
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </div>

        <!-- SEARCH -->
        <form method="GET" class="search" style="margin-bottom:20px;">

            <input
                type="text"
                name="search"
                placeholder="Search customer or cake..."
                value="<?= e($search) ?>"
            >

            <button type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
                Search
            </button>

        </form>

        <div class="table-container">

            <table>

                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Cake</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Pickup Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($orders) > 0): ?>

                    <?php foreach ($orders as $order): ?>

                        <?php
                            $status = $order['status'] ?? 'Pending';

                            $statusClass = 'status-pending';

                            if ($status === 'Confirmed') {
                                $statusClass = 'status-confirmed';
                            } elseif ($status === 'Completed') {
                                $statusClass = 'status-completed';
                            }
                        ?>

                        <tr>

                            <td class="order-id">
                                #<?= e($order['id']) ?>
                            </td>

                            <td class="customer-name">
                                <?= e($order['customer_name']) ?>
                            </td>

                            <td>
                                <?= e($order['cake_name']) ?>
                            </td>

                            <td>
                                <?= (int)$order['quantity'] ?>
                            </td>

                            <td class="price">
                                ₱<?= number_format((float)$order['price'], 2) ?>
                            </td>

                            <td>
                                <?= e($order['pickup_date']) ?>
                            </td>

                            <td>
                                <span class="status <?= $statusClass ?>">
                                    <i class="fa-solid fa-circle"
                                    style="font-size:6px;"></i>
                                    <?= e($status) ?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="empty">
                            <i class="fa-solid fa-cake-candles"></i>
                            <p>No cake orders found.</p>
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        © <?= date('Y') ?> T&M Blooms Cake Ordering System. Made with ♡
    </div>

</div>

</body>
</html>

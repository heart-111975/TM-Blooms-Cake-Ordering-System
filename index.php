<?php
date_default_timezone_set('Asia/Manila');

// DATABASE CONNECTION
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
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$success = false;
$error = "";
$orderDetails = [];

$cakes = [
    "Classic Cake" => 350,
    "Chocolate Moist Cake" => 400,
    "Bento Cake" => 250,
    "Cupcakes" => 180,
    "Yema Cake" => 380,
    "Brownies" => 200,
    "Macaroons" => 150,
    "Cookies" => 120
];

// PROCESS ORDER
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer = trim($_POST["customer_name"] ?? "");
    $contact = trim($_POST["contact_number"] ?? "");
    $cake = trim($_POST["cake_name"] ?? "");
    $quantity = (int)($_POST["quantity"] ?? 0);
    $pickup = $_POST["pickup_date"] ?? "";

    // VALIDATION
    if (
        $customer === "" ||
        !preg_match('/^\+639[0-9]{9}$/', $contact) ||
        !array_key_exists($cake, $cakes) ||
        $quantity < 1 ||
        $quantity > 50 ||
        $pickup === ""
    ) {

        $error = "Please complete all required fields and enter a valid Philippine mobile number.";

    } else {

        $pickupTimestamp = strtotime($pickup);
        $today = strtotime(date("Y-m-d"));

        if (
            $pickupTimestamp === false ||
            date("Y-m-d", $pickupTimestamp) !== $pickup ||
            $pickupTimestamp < $today
        ) {

            $error = "Please select a valid pickup date.";

        } elseif (date("N", $pickupTimestamp) == 7) {

            $error = "Cake orders are only accepted from Monday to Saturday. Sunday is unavailable.";

        } else {

            try {

                // COMPUTE TOTAL PRICE
                $basePrice = $cakes[$cake];
                $price = $basePrice * $quantity;

                // INSERT ORDER
                $stmt = $conn->prepare("
                    INSERT INTO orders
                    (
                        customer_name,
                        contact_number,
                        cake_name,
                        size,
                        quantity,
                        price,
                        pickup_date,
                        status
                    )
                    VALUES
                    (
                        :customer,
                        :contact,
                        :cake,
                        :size,
                        :quantity,
                        :price,
                        :pickup,
                        'Pending'
                    )
                ");

                $stmt->execute([
                    ":customer" => $customer,
                    ":contact" => $contact,
                    ":cake" => $cake,
                    ":size" => "N/A",
                    ":quantity" => $quantity,
                    ":price" => $price,
                    ":pickup" => $pickup
                ]);

                $success = true;

                // ORDER DETAILS
                $orderDetails = [
                    "customer" => $customer,
                    "contact" => $contact,
                    "cake" => $cake,
                    "quantity" => $quantity,
                    "price" => $price,
                    "pickup" => $pickup
                ];

            } catch (PDOException $e) {

                $error = "Your order could not be submitted. Please check your database connection.";

            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>T&M Blooms | Online Cake Store</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

/* GENERAL */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

html, body {
    width: 100%;
    min-height: 100%;
    background: #fffafa;
    color: #4a3b40;
}

/* APP LAYOUT */

.app-container {
    display: flex;
    min-height: 100vh;
}

/* SIDEBAR */

.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #fff;
    padding: 25px 20px;
    display: flex;
    flex-direction: column;
    gap: 25px;
    flex-shrink: 0;
    border-right: 1px solid #f0e1e5;
    box-shadow: 2px 0 10px rgba(0,0,0,0.02);
    z-index: 100;
    overflow-y: auto;
}
.sidebar-brand {
    font-size: 22px;
    font-weight: 700;
    color: #d46a84;
    letter-spacing: 0.5px;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sidebar-menu a {
    text-decoration: none;
    color: #6b535b;
    font-weight: 500;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    transition: 0.2s;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background: #fdf2f4;
    color: #d46a84;
}

/* MAIN CONTENT */
.main-content {
    margin-left: 240px;
    width: calc(100% - 240px);
    min-width: 0;
    min-height: 100vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}
/* TOP NAVBAR */

.navbar {
    background: #ffffff;
    padding: 15px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0e1e5;
    position: sticky;
    top: 0;
    z-index: 90;
}

.nav-logo {
    font-weight: 700;
    font-size: 20px;
    color: #d46a84;
}

.nav-links {
    display: flex;
    gap: 25px;
}

.nav-links a {
    text-decoration: none;
    color: #6b535b;
    font-weight: 500;
    font-size: 14px;
    transition: color 0.2s;
}

.nav-links a:hover {
    color: #d46a84;
}

/* HERO SECTION */

.hero {
    width: 100%;
    padding: 60px 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #fff0f3 0%, #fffbfb 100%);
    gap: 30px;
}

.hero-content {
    max-width: 550px;
}

.hero h1 {
    font-size: clamp(32px, 4vw, 48px);
    color: #b84a6b;
    margin-bottom: 15px;
    line-height: 1.2;
    font-weight: 700;
}

.hero h1 span {
    color: #d46a84;
}

.hero p {
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 25px;
    color: #7d636b;
}

.hero-btn {
    display: inline-block;
    background: #d46a84;
    color: white;
    padding: 12px 28px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 4px 15px rgba(212, 106, 132, 0.3);
    transition: 0.3s;
}

.hero-btn:hover {
    background: #b84a6b;
    transform: translateY(-2px);
}

.hero-image-box {
    width: 380px;
    height: 280px;
    background: #fde2e7;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 70px;
    box-shadow: 0 10px 30px rgba(212, 106, 132, 0.15);
    position: relative;
    overflow: hidden;
}

.hero-image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* SECTIONS */

.section {
    width: 100%;
    padding: 50px 5%;
}

.section-title {
    text-align: center;
    margin-bottom: 35px;
}

.section-title h2 {
    color: #b84a6b;
    font-size: clamp(24px, 3vw, 32px);
    margin-bottom: 8px;
    font-weight: 700;
}

.section-title p {
    color: #8c737b;
    font-size: 14px;
}

/* PRODUCTS */

.products {
    width: 100%;
    max-width: 1250px;
    margin: auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.04);
    transition: 0.3s;
    border: 1px solid #f7e6ea;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(212, 106, 132, 0.1);
}

.product-image {
    height: 160px;
    background: #fdf2f4;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
    position: relative;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    padding: 16px;
    text-align: center;
}

.product-info h3 {
    color: #b84a6b;
    font-size: 15px;
    margin-bottom: 4px;
    font-weight: 600;
}

.product-info p {
    color: #8c737b;
    font-size: 12px;
    line-height: 1.4;
    margin-bottom: 10px;
}

.product-price {
    color: #d46a84;
    font-weight: 700;
    font-size: 14px;
}

/* ORDER FORM SECTION */

.order-section {
    background: #fff5f7;
}

.order-container {
    width: 100%;
    max-width: 680px;
    margin: auto;
    background: white;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(212, 106, 132, 0.08);
    border: 1px solid #f2dbe2;
}

.notice {
    background: #fff9e6;
    color: #8a6d15;
    border: 1px solid #ffeeba;
    padding: 15px 17px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 12px;
    line-height: 1.8;
}

.notice strong {
    display: block;
    margin-bottom: 3px;
    font-size: 13px;
}

.form-heading {
    margin-bottom: 23px;
}

.form-heading h3 {
    color: #b84a6b;
    font-size: 19px;
    margin-bottom: 5px;
}

.form-heading p {
    color: #9a8189;
    font-size: 12px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #6b535b;
    font-size: 13px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 13px 15px;
    border: 1px solid #ead5dc;
    border-radius: 11px;
    outline: none;
    background: #fffdfd;
    color: #4a3b40;
    font-size: 13px;
    transition: 0.2s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #d46a84;
    box-shadow: 0 0 0 3px rgba(212, 106, 132, 0.1);
}

.form-group input::placeholder {
    color: #b6a2a8;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

/* CONTACT NUMBER */

#contact_number {
    letter-spacing: 0.5px;
}

/* SUBMIT BUTTON */

.submit-btn {
    width: 100%;
    padding: 15px;
    background: #d46a84;
    color: white;
    border: none;
    border-radius: 11px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 5px 15px rgba(212, 106, 132, 0.22);
}

.submit-btn:hover {
    background: #b84a6b;
    transform: translateY(-2px);
}

.error {
    background: #fdf2f2;
    color: #9c2c2c;
    border: 1px solid #f5c6cb;
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 13px;
    line-height: 1.6;
}

/* ORDER CONFIRMATION */

.confirmation-card {
    background: #ffffff;
    border: 1px solid #f3dce3;
    border-radius: 22px;
    padding: 35px;
    text-align: center;
    max-width: 600px;
    width: 100%;
    margin: 0 auto 25px;
    box-shadow: 0 12px 35px rgba(184, 74, 107, 0.09);
    animation: fadeIn 0.5s ease;
}

.confirmation-icon {
    width: 85px;
    height: 85px;
    background: #e9f8ed;
    color: #38a169;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    margin: 0 auto 20px;
}

.confirmation-card h2 {
    color: #b84a6b;
    font-size: 25px;
    margin-bottom: 10px;
}

.confirmation-card > p {
    color: #8c737b;
    font-size: 13px;
    line-height: 1.8;
    margin-bottom: 20px;
}

.confirmation-status {
    display: inline-block;
    background: #fff4dc;
    color: #a87519;
    padding: 9px 18px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 22px;
}

.order-details {
    background: #fff8fa;
    border: 1px solid #f4e1e7;
    border-radius: 15px;
    padding: 20px;
    text-align: left;
    margin-bottom: 20px;
}

.order-details h3 {
    color: #b84a6b;
    font-size: 16px;
    margin-bottom: 12px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid #f1e5e9;
    font-size: 12px;
}

.detail-row span:first-child {
    color: #8c737b;
}

.detail-row span:last-child {
    color: #4a3b40;
    font-weight: 600;
    text-align: right;
    overflow-wrap: anywhere;
}

.total-row {
    margin-top: 12px;
    padding-top: 15px;
    border-top: 1px dashed #e6bdca;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    color: #b84a6b;
    font-size: 16px;
    font-weight: 700;
}

.confirmation-note {
    background: #fff9e9;
    border-radius: 10px;
    padding: 14px;
    color: #8a6d15;
    font-size: 12px;
    line-height: 1.8;
    text-align: left;
    margin-bottom: 25px;
}

.confirmation-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}

.confirmation-btn {
    display: inline-block;
    padding: 12px 22px;
    background: #d46a84;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: 0.3s;
}

.confirmation-btn:hover {
    background: #b84a6b;
    transform: translateY(-2px);
}

.confirmation-btn.secondary {
    background: white;
    color: #d46a84;
    border: 1px solid #d46a84;
}

.confirmation-btn.secondary:hover {
    background: #fff0f4;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* FOOTER */

footer {
    background: #ffffff;
    text-align: center;
    padding: 25px 15px;
    color: #8c737b;
    font-size: 13px;
    border-top: 1px solid #f0e1e5;
}

/* RESPONSIVE */

@media (max-width: 1024px) {
    .products {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {

    .app-container {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        padding: 15px;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-menu {
        flex-direction: row;
    }

    .sidebar-menu a {
        padding: 9px;
        font-size: 12px;
    }

    .hero {
        flex-direction: column;
        text-align: center;
    }

    .hero-content {
        max-width: 100%;
    }

    .hero-image-box {
        width: 100%;
        max-width: 320px;
        margin: auto;
    }

    .navbar {
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .nav-links {
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .products {
        grid-template-columns: 1fr;
    }

    .order-container {
        padding: 22px 17px;
    }

    .confirmation-card {
        padding: 25px 17px;
    }

    .confirmation-actions {
        flex-direction: column;
    }

    .confirmation-btn {
        width: 100%;
        text-align: center;
    }
}

</style>
</head>

<body>

<div class="app-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="sidebar-brand">T&M BLOOMS</div>

        <nav class="sidebar-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="index.php" class="active">Store / Orders</a>
        </nav>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- TOP NAVBAR -->
        <nav class="navbar">

            <div class="nav-logo">🌸 T&M Blooms</div>

            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#cakes">Cakes</a>
                <a href="#order">Order Now</a>
            </div>

        </nav>

        <!-- HERO SECTION -->
        <section class="hero" id="home">

            <div class="hero-content">

                <h1>
                    Delicious Cakes for
                    <span>Every Occasion</span>
                </h1>

                <p>
                    Handmade with love using the finest ingredients.
                    Order online and get your favorite cakes delivered
                    straight to your doorstep.
                </p>

                <a href="#order" class="hero-btn">
                    Order Your Cake Now
                </a>

            </div>

            <div class="hero-image-box">
                <img src="images/maincake.jpg" alt="Main Cake">
            </div>

        </section>

        <!-- CAKE MENU -->
        <section class="section" id="cakes">

            <div class="section-title">

                <h2>Our Sweet Selection</h2>

                <p>
                    Discover our best-selling homemade treats
                    and beautiful cake designs.
                </p>

            </div>

            <div class="products">

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/classic.jpg" alt="Classic Cake">
                    </div>
                    <div class="product-info">
                        <h3>Classic Cake</h3>
                        <p>Perfect for birthdays and special celebrations.</p>
                        <div class="product-price">Starts at ₱350.00</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/chocolate.jpg" alt="Chocolate Moist Cake">
                    </div>
                    <div class="product-info">
                        <h3>Chocolate Moist Cake</h3>
                        <p>Rich, soft, and chocolatey goodness.</p>
                        <div class="product-price">Starts at ₱400.00</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/bento.jpg" alt="Bento Cake">
                    </div>
                    <div class="product-info">
                        <h3>Bento Cake</h3>
                        <p>Small aesthetic cakes made for simple moments.</p>
                        <div class="product-price">Starts at ₱250.00</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/cupcake.jpg" alt="Cupcake">
                    </div>
                    <div class="product-info">
                        <h3>Cupcakes</h3>
                        <p>Sweet little treats baked fresh for you.</p>
                        <div class="product-price">Starts at ₱180.00</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/yema.jpg" alt="Yema Cake">
                    </div>
                    <div class="product-info">
                        <h3>Yema Cake</h3>
                        <p>A delicious treat with creamy yema frosting.</p>
                        <div class="product-price">Starts at ₱380.00</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/brownie.jpg" alt="Brownies">
                    </div>
                    <div class="product-info">
                        <h3>Brownies</h3>
                        <p>Fudgy and rich chocolate squares.</p>
                        <div class="product-price">Starts at ₱200.00</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/macaroons.jpg" alt="Macaroons">
                    </div>
                    <div class="product-info">
                        <h3>Macaroons</h3>
                        <p>Sweet and milky coconut snacks.</p>
                        <div class="product-price">Starts at ₱150.00</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="images/cookies.jpg" alt="Cookies">
                    </div>
                    <div class="product-info">
                        <h3>Cookies</h3>
                        <p>Sweet snacks to share with family and friends.</p>
                        <div class="product-price">Starts at ₱120.00</div>
                    </div>
                </div>

            </div>

        </section>

        <!-- ORDER FORM SECTION -->
        <section class="section order-section" id="order">

            <div class="section-title">

                <h2><?= $success ? "Order Confirmation" : "Place Your Order" ?></h2>

                <p>
                    <?= $success
                        ? "Your order details are shown below."
                        : "Fill out the form below to request your favorite treats." ?>
                </p>

            </div>

            <div class="order-container">

                <?php if ($success): ?>

                    <div class="confirmation-card">

                        <div class="confirmation-icon">✓</div>

                        <h2>Order Submitted Successfully!</h2>

                        <p>
                            Thank you for choosing T&M Blooms!
                            Your order has been received and is now
                            being reviewed by our shop.
                        </p>

                        <div class="confirmation-status">
                            ● Pending Confirmation
                        </div>

                        <div class="order-details">

                            <h3>Order Summary</h3>

                            <div class="detail-row">
                                <span>Customer Name</span>
                                <span><?= htmlspecialchars($orderDetails['customer']) ?></span>
                            </div>

                            <div class="detail-row">
                                <span>Contact Number</span>
                                <span><?= htmlspecialchars($orderDetails['contact']) ?></span>
                            </div>

                            <div class="detail-row">
                                <span>Product</span>
                                <span><?= htmlspecialchars($orderDetails['cake']) ?></span>
                            </div>

                            <div class="detail-row">
                                <span>Quantity</span>
                                <span><?= htmlspecialchars((string)$orderDetails['quantity']) ?></span>
                            </div>

                            <div class="detail-row">
                                <span>Pickup Date</span>
                                <span>
                                    <?= date("F d, Y", strtotime($orderDetails['pickup'])) ?>
                                </span>
                            </div>

                            <div class="total-row">
                                <span>Total Amount</span>
                                <span>₱<?= number_format($orderDetails['price'], 2) ?></span>
                            </div>

                        </div>

                        <div class="confirmation-note">

                            <strong>Important Reminder</strong><br>

                            Your order is currently pending confirmation.
                            Please wait for the shop administrator to
                            review and approve your order.

                            Thank you for your patience!

                        </div>

                        <div class="confirmation-actions">

                            <a href="index.php" class="confirmation-btn">
                                Back to Store
                            </a>

                            <a href="dashboard.php" class="confirmation-btn secondary">
                                View Dashboard
                            </a>

                        </div>

                    </div>

                <?php else: ?>

                    <div class="notice">

                        <strong>Order Reminder</strong>

                        T&M Blooms accepts cake orders from Monday
                        to Saturday only. Sunday is unavailable.
                        All submitted orders are subject to confirmation
                        and approval by the shop administrator.

                    </div>

                    <?php if ($error): ?>

                        <div class="error">
                            <strong>Unable to Submit Order</strong><br>
                            <?= htmlspecialchars($error) ?>
                        </div>

                    <?php endif; ?>

                    <div class="form-heading">

                        <h3>Customer Information</h3>

                        <p>
                            Please provide your details and order preferences.
                        </p>

                    </div>

                    <form method="POST" action="#order">

                        <div class="form-group">

                            <label>Full Name</label>

                            <input
                                type="text"
                                name="customer_name"
                                placeholder="Enter your full name"
                                maxlength="100"
                                value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>"
                                required
                            >

                        </div>

                        <!-- CONTACT NUMBER WITH AUTOMATIC +639 -->
                        <div class="form-group">

                            <label>Contact Number</label>

                            <input
                                type="tel"
                                name="contact_number"
                                id="contact_number"
                                value="<?= htmlspecialchars($_POST['contact_number'] ?? '+639') ?>"
                                maxlength="13"
                                placeholder="+639XXXXXXXXX"
                                pattern="\+639[0-9]{9}"
                                title="Enter the 9 digits after +639."
                                inputmode="numeric"
                                required
                                onfocus="keepPrefix()"
                                oninput="formatContactNumber(this)"
                            >

                            <small style="display:block; color:#9a8189; margin-top:7px; font-size:11px;">
                                The +639 prefix is already provided. Enter the remaining 9 digits only.
                            </small>

                        </div>

                        <div class="form-group">

                            <label>Select Cake / Treat</label>

                            <select name="cake_name" required>

                                <option value="">-- Choose an item --</option>

                                <?php foreach ($cakes as $cakeName => $cakePrice): ?>

                                    <option
                                        value="<?= htmlspecialchars($cakeName) ?>"
                                        <?= (($_POST['cake_name'] ?? '') === $cakeName) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($cakeName) ?>
                                        (₱<?= number_format($cakePrice, 2) ?>)
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>Quantity</label>

                            <input
                                type="number"
                                name="quantity"
                                min="1"
                                max="50"
                                value="<?= htmlspecialchars($_POST['quantity'] ?? '1') ?>"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label>Preferred Pickup Date</label>

                            <input
                                type="date"
                                name="pickup_date"
                                min="<?= date('Y-m-d') ?>"
                                value="<?= htmlspecialchars($_POST['pickup_date'] ?? '') ?>"
                                required
                            >

                            <small style="display:block; color:#9a8189; margin-top:7px; font-size:11px;">
                                Available pickup days: Monday to Saturday only. Sunday is unavailable.
                            </small>

                        </div>

                        <button type="submit" class="submit-btn">
                            Submit Cake Order
                        </button>

                    </form>

                <?php endif; ?>

            </div>

        </section>

        <!-- FOOTER -->

        <footer>

            <p>
                © <?= date('Y') ?> T&M Blooms.
                All Rights Reserved. Made with love ♡
            </p>

        </footer>

    </main>

</div>

<!-- CONTACT NUMBER SCRIPT -->
<script>

function keepPrefix() {
    const contact = document.getElementById("contact_number");

    if (!contact.value.startsWith("+639")) {
        contact.value = "+639";
    }
}

function formatContactNumber(input) {

    let value = input.value;

    // Keep +639 as the fixed prefix
    if (!value.startsWith("+639")) {
        value = value.replace(/[^0-9]/g, "");

        // Remove the prefix digits if already typed
        if (value.startsWith("639")) {
            value = value.substring(3);
        } else if (value.startsWith("09")) {
            value = value.substring(2);
        } else if (value.startsWith("9")) {
            value = value.substring(1);
        }

        input.value = "+639" + value.substring(0, 9);
        return;
    }

    // Allow only numbers after +639
    let remainingDigits = value.substring(4).replace(/[^0-9]/g, "");

    // Limit to 9 digits
    remainingDigits = remainingDigits.substring(0, 9);

    input.value = "+639" + remainingDigits;
}

</script>

</body>
</html>
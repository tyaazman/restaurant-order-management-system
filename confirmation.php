<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Sup Tulang ZZ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="customer-portal">
    <div class="page-header">
        <h1>Order Confirmed!</h1>
    </div>
    <main class="checkout-wrapper" style="text-align: center; margin-top: 50px;">
        <div class="order-summary" style="padding: 40px; border-radius: 8px; background: #f0e6d2; display: inline-block; max-width: 500px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-left: 5px solid #8B4513;">
            <h2 style="color: #8B4513; margin-top: 0;">Thank you for your order!</h2>
            <p style="font-size: 1.1rem;">Your order ID is: <strong style="color: #A85530;">#<?php echo htmlspecialchars($_GET['order_id'] ?? 'N/A'); ?></strong></p>
            <p style="color: #555;">Your order has been recorded and sent to our staff. You can present your Order ID at the counter or wait for delivery.</p>
            <div style="margin-top: 30px;">
                <a href="index.html" class="add-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 30px;">Back to Menu</a>
            </div>
        </div>
    </main>
</body>
</html>

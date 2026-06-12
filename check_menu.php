<?php
include("config/db.php");

$result = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY category, item_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sup Tulang ZZ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 32px;
            background: #f7f2ea;
            color: #222;
        }
        .brand {
            margin-bottom: 24px;
        }
        .brand h1 {
            margin: 0;
            font-size: 2.25rem;
        }
        .brand p {
            margin: 8px 0 0;
            color: #666;
            font-size: 1rem;
        }
        .menu {
            display: grid;
            gap: 12px;
            max-width: 720px;
        }
        .item {
            background: #fff;
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        .item strong {
            display: block;
            margin-bottom: 4px;
        }
        .category {
            color: #8a5a2b;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="brand">
        <h1>Sup Tulang ZZ</h1>
        <p>Since 1990's • Pasir Gudang</p>
    </div>

    <div class="menu">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="item">
                <div>
                    <strong><?php echo htmlspecialchars($row['item_name']); ?></strong>
                    <span class="category"><?php echo htmlspecialchars($row['category']); ?></span>
                </div>
                <div>RM<?php echo number_format((float)$row['price'], 2); ?></div>
            </div>
        <?php } ?>
    </div>
</body>
</html>
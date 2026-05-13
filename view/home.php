<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
    <h1><?php echo htmlspecialchars($message); ?></h1>

    <?php if (!empty($products)): ?>
        <ul>
            <?php foreach ($products as $p): ?>
                <li><?php echo htmlspecialchars($p['name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No products found yet.</p>
    <?php endif; ?>
</body>
</html>
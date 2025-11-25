<?php
session_start();
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Pedido
$stmt = $mysqli->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    die("Pedido no encontrado");
}

// Items
$sql = "SELECT oi.*, p.name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Detalle pedido <?=$id?></title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1">Panel de administración</span>
        <div class="d-flex">
            <a href="index.php" class="btn btn-outline-light btn-sm me-2">Volver a pedidos</a>
            <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Ver tienda</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <h1 class="h4 mb-3">Pedido #<?= $pedido['id']; ?></h1>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($pedido['email']); ?></p>
            <p class="mb-1"><strong>Nombre envío:</strong> <?= htmlspecialchars($pedido['shipping_name']); ?></p>
            <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($pedido['shipping_address']); ?></p>
            <p class="mb-1"><strong>Ciudad / CP / País:</strong>
                <?= htmlspecialchars($pedido['shipping_city']); ?> /
                <?= htmlspecialchars($pedido['shipping_postalcode']); ?> /
                <?= htmlspecialchars($pedido['shipping_country']); ?>
            </p>
            <p class="mb-1"><strong>Total:</strong> <?= number_format($pedido['total_amount'], 2) ?> €</p>
            <p class="mb-0"><strong>Estado:</strong> <?= htmlspecialchars($pedido['status']); ?></p>
        </div>
    </div>

    <h2 class="h5">Productos del pedido</h2>

    <div class="table-responsive">
        <table class="table table-striped align-middle admin-table">
            <thead>
                <tr>
                    <th>Carta</th>
                    <th>Cantidad</th>
                    <th>Precio unidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['name']); ?></td>
                    <td><?= $it['quantity']; ?></td>
                    <td><?= number_format($it['unit_price'], 2); ?> €</td>
                    <td><?= number_format($it['subtotal'], 2); ?> €</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>

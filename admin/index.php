<?php
session_start();
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$result = $mysqli->query("
    SELECT o.id, o.email, o.total_amount, o.status, o.created_at
    FROM orders o
    ORDER BY o.created_at DESC
");
$pedidos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Admin – Pedidos</title>
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
            <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Ver tienda</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <h1 class="h4 mb-3">Pedidos realizados</h1>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">Todavía no hay pedidos.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Ver</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><?= $p['id']; ?></td>
                        <td><?= htmlspecialchars($p['email']); ?></td>
                        <td><?= number_format($p['total_amount'], 2) ?> €</td>
                        <td><?= htmlspecialchars($p['status']); ?></td>
                        <td><?= $p['created_at']; ?></td>
                        <td>
                            <a href="pedido.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-primary">
                                Detalles
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>


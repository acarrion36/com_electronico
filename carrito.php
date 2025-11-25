<?php
require_once __DIR__ . '/bootstrap.php';

// Acción por GET
$action = $_GET['action'] ?? null;

if ($action === 'add') {
    $id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $qty = isset($_GET['qty']) ? max(1, (int)$_GET['qty']) : 1;
    carrito_add($id, $qty);
    header('Location: carrito.php');
    exit;
}

if ($action === 'remove') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    carrito_remove($id);
    header('Location: carrito.php');
    exit;
}

if ($action === 'clear') {
    carrito_clear();
    header('Location: carrito.php');
    exit;
}

// Actualizar cantidades por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cantidades'])) {
    foreach ($_POST['cantidades'] as $id => $cantidad) {
        carrito_set((int)$id, (int)$cantidad);
    }
    header('Location: carrito.php');
    exit;
}

$items = carrito_get_items($mysqli);
$total = carrito_get_total($mysqli);
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="row mb-3">
    <div class="col-12">
        <h1 class="h3">Carrito de la compra</h1>
    </div>
</div>

<?php if (empty($items)): ?>
    <div class="alert alert-info">
        Tu carrito está vacío. <a href="index.php" class="alert-link">Ir a la tienda</a>
    </div>
<?php else: ?>
    <form method="post" action="carrito.php">
        <div class="table-responsive mb-3">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-end">Precio unidad</th>
                        <th style="width:120px;">Cantidad</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $linea): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($linea['name']); ?></td>
                        <td class="text-end"><?php echo number_format($linea['price'], 2); ?> €</td>
                        <td>
                            <input type="number"
                                   name="cantidades[<?php echo (int)$linea['id']; ?>]"
                                   value="<?php echo (int)$linea['quantity']; ?>"
                                   min="0"
                                   class="form-control form-control-sm">
                        </td>
                        <td class="text-end"><?php echo number_format($linea['subtotal'], 2); ?> €</td>
                        <td class="text-end">
                            <a href="carrito.php?action=remove&id=<?php echo (int)$linea['id']; ?>"
                               class="btn btn-sm btn-outline-danger">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button type="submit" class="btn btn-primary me-2">Actualizar cantidades</button>
                <a href="carrito.php?action=clear" class="btn btn-outline-secondary">
                    Vaciar carrito
                </a>
            </div>
            <div class="fs-5">
                Total: <strong><?php echo number_format($total, 2); ?> €</strong>
            </div>
        </div>
    </form>

    <div class="text-end">
        <a href="checkout.php" class="btn btn-success btn-lg">
            Finalizar compra
        </a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>


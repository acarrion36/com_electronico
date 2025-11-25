<?php
require_once __DIR__ . '/bootstrap.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
    http_response_code(404);
    echo "Producto no encontrado.";
    exit;
}
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="row mb-3">
    <div class="col-12">
        <a href="categoria.php?id=<?php echo (int)$producto['category_id']; ?>"
           class="small text-decoration-none">&larr; Volver a <?php echo htmlspecialchars($producto['category_name']); ?></a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-md-4">
        <?php if (!empty($producto['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($producto['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($producto['name']); ?>"
                 class="img-fluid rounded shadow-sm product-detail-img">
        <?php endif; ?>
    </div>
    <div class="col-12 col-md-8">
        <h1 class="h3"><?php echo htmlspecialchars($producto['name']); ?></h1>
        <p class="text-muted mb-2">Categoría: <?php echo htmlspecialchars($producto['category_name']); ?></p>
        <p class="fs-4 fw-bold mb-3"><?php echo number_format($producto['price'], 2); ?> €</p>

        <?php if (!empty($producto['description'])): ?>
            <p><?php echo nl2br(htmlspecialchars($producto['description'])); ?></p>
        <?php endif; ?>

        <form method="get" action="carrito.php" class="row g-2 align-items-end mt-3">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?php echo (int)$producto['id']; ?>">
            <div class="col-auto">
                <label class="form-label mb-1">Cantidad</label>
                <input type="number" name="qty" value="1" min="1" class="form-control" style="width:80px;">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success">
                    Añadir al carrito
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

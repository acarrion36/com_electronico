<?php
require_once __DIR__ . '/bootstrap.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cargar categoría
$stmt = $mysqli->prepare("SELECT id, name FROM categories WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$categoria = $stmt->get_result()->fetch_assoc();

if (!$categoria) {
    http_response_code(404);
    echo "Categoría no encontrada.";
    exit;
}

// Cargar productos de la categoría (con image_url)
$stmt = $mysqli->prepare(
    "SELECT id, name, price, image_url
     FROM products
     WHERE category_id = ?
     ORDER BY name"
);
$stmt->bind_param('i', $categoria['id']);
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="row mb-3">
    <div class="col-12">
        <h1 class="h3 mb-0"><?php echo htmlspecialchars($categoria['name']); ?></h1>
        <a href="index.php" class="small text-decoration-none">&larr; Volver a categorías</a>
    </div>
</div>

<?php if (empty($productos)): ?>
    <div class="alert alert-info">No hay productos en esta categoría.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($productos as $p): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <article class="card h-100 shadow-sm">
                    <?php if (!empty($p['image_url'])): ?>
                        <a href="producto.php?id=<?php echo (int)$p['id']; ?>">
                            <img src="<?php echo htmlspecialchars($p['image_url']); ?>"
                                 class="card-img-top card-img-product"
                                 alt="<?php echo htmlspecialchars($p['name']); ?>">
                        </a>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h2 class="card-title h6">
                            <a href="producto.php?id=<?php echo (int)$p['id']; ?>"
                               class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($p['name']); ?>
                            </a>
                        </h2>
                        <p class="fw-bold mb-2"><?php echo number_format($p['price'], 2); ?> €</p>
                        <div class="mt-auto">
                            <a class="btn btn-sm btn-success w-100"
                               href="carrito.php?action=add&id=<?php echo (int)$p['id']; ?>">
                                Añadir al carrito
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>



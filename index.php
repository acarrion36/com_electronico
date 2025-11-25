<?php
require_once __DIR__ . '/bootstrap.php';

// Consultar categorías (con image_url)
$result = $mysqli->query("SELECT id, name, image_url FROM categories ORDER BY name");
if (!$result) {
    die("Error consultando categorías: " . $mysqli->error);
}
$categorias = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="row">
    <div class="col-12 mb-3">
        <h1 class="h3">Catálogo por tipo de carta</h1>
        <p class="text-muted">
            Selecciona una categoría para ver las cartas disponibles en la tienda.
        </p>
    </div>
</div>

<?php if (empty($categorias)): ?>
    <div class="alert alert-warning">No hay categorías en la base de datos.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($categorias as $cat): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <article class="card h-100 shadow-sm">
                    <?php if (!empty($cat['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($cat['image_url']); ?>"
                             class="card-img-top card-img-category"
                             alt="<?php echo htmlspecialchars($cat['name']); ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h2 class="card-title h5">
                            <a href="categoria.php?id=<?php echo (int)$cat['id']; ?>"
                               class="stretched-link text-decoration-none text-dark">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        </h2>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>

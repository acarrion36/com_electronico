<?php
require_once __DIR__ . '/bootstrap.php';

$items = carrito_get_items($mysqli);
$total = carrito_get_total($mysqli);

if (empty($items)) {
    echo "El carrito está vacío.";
    exit;
}

// POST: procesar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? 'guest'; // guest / registered

    $email   = trim($_POST['email'] ?? '');
    $name    = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $cp      = trim($_POST['postalcode'] ?? '');
    $country = trim($_POST['country'] ?? 'España');
    $pass    = $_POST['password'] ?? null;

    if ($tipo === 'registered') {
        if ($name === '' || $email === '' || $address === '') {
            die("Faltan datos obligatorios para usuario registrado.");
        }

        $hash = $pass ? password_hash($pass, PASSWORD_BCRYPT) : null;

        $stmt = $mysqli->prepare(
            "INSERT INTO customers (full_name, email, password_hash, is_registered)
             VALUES (?, ?, ?, 1)"
        );
        $stmt->bind_param('sss', $name, $email, $hash);
        $stmt->execute();
        $customerId = $stmt->insert_id;
        $isGuest    = 0;
        $shippingName = $name;
    } else {
        // Invitado
        if ($email === '' || $address === '') {
            die("Faltan datos obligatorios para usuario invitado.");
        }
        $customerId   = null;
        $isGuest      = 1;
        $shippingName = $name !== '' ? $name : 'Invitado';
    }

    // Crear pedido
    $stmt = $mysqli->prepare(
        "INSERT INTO orders
         (customer_id, is_guest, email, shipping_name, shipping_address,
          shipping_city, shipping_postalcode, shipping_country, total_amount, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')"
    );

    $isGuestInt = (int)$isGuest;
    $stmt->bind_param(
        'iissssssd',
        $customerId,
        $isGuestInt,
        $email,
        $shippingName,
        $address,
        $city,
        $cp,
        $country,
        $total
    );
    $stmt->execute();
    $orderId = $stmt->insert_id;

    // Insertar líneas de pedido
    $stmtItem = $mysqli->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
         VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($items as $linea) {
        $oid   = $orderId;
        $pid   = (int)$linea['id'];
        $qty   = (int)$linea['quantity'];
        $price = (float)$linea['price'];
        $sub   = (float)$linea['subtotal'];
        $stmtItem->bind_param('iiidd', $oid, $pid, $qty, $price, $sub);
        $stmtItem->execute();
    }

    carrito_clear();

    include __DIR__ . '/header.php';
    ?>
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Resumen de pedido</h1>
                    <p class="mb-1"><strong>Nº de pedido:</strong> <?php echo $orderId; ?></p>
                    <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($shippingName); ?></p>
                    <p class="mb-3"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                    <p class="mb-3">
                        <strong>Dirección:</strong><br>
                        <?php echo nl2br(htmlspecialchars($address)); ?><br>
                        <?php echo htmlspecialchars($city . ' ' . $cp . ' ' . $country); ?>
                    </p>

                    <h2 class="h5">Productos</h2>
                    <ul class="list-group mb-3">
                        <?php foreach ($items as $linea): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <?php echo (int)$linea['quantity']; ?> x
                                    <?php echo htmlspecialchars($linea['name']); ?>
                                </span>
                                <span><?php echo number_format($linea['subtotal'], 2); ?> €</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <p class="fs-5">
                        Total: <strong><?php echo number_format($total, 2); ?> €</strong>
                    </p>
                    <p class="mb-0">Gracias por tu compra.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

include __DIR__ . '/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <h1 class="h3">Finalizar compra</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 col-lg-6">
        <h2 class="h5">Contenido del carrito</h2>
        <ul class="list-group mb-3">
            <?php foreach ($items as $linea): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <?php echo (int)$linea['quantity']; ?> x
                        <?php echo htmlspecialchars($linea['name']); ?>
                    </span>
                    <span><?php echo number_format($linea['subtotal'], 2); ?> €</span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="fs-5">
            Total: <strong><?php echo number_format($total, 2); ?> €</strong>
        </p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Comprar como invitado</h2>
                <form method="post" action="checkout.php">
                    <input type="hidden" name="tipo" value="guest">
                    <div class="mb-2">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nombre (opcional)</label>
                        <input type="text" name="name" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Dirección de envío *</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="city" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Código postal</label>
                        <input type="text" name="postalcode" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">País</label>
                        <input type="text" name="country" class="form-control" value="España">
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        Confirmar pedido como invitado
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Comprar como usuario registrado</h2>
                <form method="post" action="checkout.php">
                    <input type="hidden" name="tipo" value="registered">
                    <div class="mb-2">
                        <label class="form-label">Nombre completo *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Dirección de envío *</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="city" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Código postal</label>
                        <input type="text" name="postalcode" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">País</label>
                        <input type="text" name="country" class="form-control" value="España">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Confirmar pedido como usuario registrado
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

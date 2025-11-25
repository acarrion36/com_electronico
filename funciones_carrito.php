<?php
// funciones_carrito.php

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = []; // [product_id => cantidad]
}

function carrito_add(int $productId, int $cantidad = 1): void
{
    if ($cantidad < 1) {
        return;
    }
    if (!isset($_SESSION['carrito'][$productId])) {
        $_SESSION['carrito'][$productId] = 0;
    }
    $_SESSION['carrito'][$productId] += $cantidad;
}

function carrito_set(int $productId, int $cantidad): void
{
    if ($cantidad <= 0) {
        unset($_SESSION['carrito'][$productId]);
    } else {
        $_SESSION['carrito'][$productId] = $cantidad;
    }
}

function carrito_remove(int $productId): void
{
    unset($_SESSION['carrito'][$productId]);
}

function carrito_clear(): void
{
    $_SESSION['carrito'] = [];
}

/**
 * Devuelve un array de líneas de carrito con info de producto:
 * [
 *   [ 'id'=>..., 'name'=>..., 'price'=>..., 'quantity'=>..., 'subtotal'=>... ],
 *   ...
 * ]
 */
function carrito_get_items(mysqli $mysqli): array
{
    if (empty($_SESSION['carrito'])) {
        return [];
    }

    $ids = array_keys($_SESSION['carrito']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "SELECT id, name, price FROM products WHERE id IN ($placeholders)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Error en prepare: " . $mysqli->error);
    }

    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $productosPorId = [];
    while ($p = $result->fetch_assoc()) {
        $productosPorId[$p['id']] = $p;
    }

    $lineas = [];
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        if (!isset($productosPorId[$id])) {
            continue;
        }
        $p = $productosPorId[$id];
        $subtotal = $p['price'] * $cantidad;
        $lineas[] = [
            'id'       => $p['id'],
            'name'     => $p['name'],
            'price'    => $p['price'],
            'quantity' => $cantidad,
            'subtotal' => $subtotal,
        ];
    }

    return $lineas;
}

function carrito_get_total(mysqli $mysqli): float
{
    $total = 0.0;
    foreach (carrito_get_items($mysqli) as $linea) {
        $total += $linea['subtotal'];
    }
    return $total;
}

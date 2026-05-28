<?php
$archivo = 'productos.json';

// Leer productos: convierte el JSON del archivo a un array de PHP
function leerProductos($archivo) {
    if (!file_exists($archivo)) return [];
    $json = file_get_contents($archivo);
    return json_decode($json, true);
}

// Guardar productos: convierte el array de PHP a JSON y lo escribe en el archivo
function guardarProductos($productos, $archivo) {
    $json = json_encode($productos, JSON_PRETTY_PRINT);
    file_put_contents($archivo, $json);
}

function siguienteId($productos) {
    if (empty($productos)) return 1;
    return max(array_column($productos, 'id')) + 1;
}

$mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productos = leerProductos($archivo);
    $accion = $_POST['accion'];

    if ($accion === 'agregar') {
        $productos[] = [
            'id'     => siguienteId($productos),
            'nombre' => $_POST['nombre'],
            'marca'  => $_POST['marca'],
            'precio' => (float) $_POST['precio'],
            'stock'  => (int) $_POST['stock'],
            'tipo'   => $_POST['tipo'],
        ];
        guardarProductos($productos, $archivo);
        $mensaje = "Producto agregado correctamente.";

    } elseif ($accion === 'modificar') {
        $id = (int) $_POST['id'];
        foreach ($productos as &$p) {
            if ($p['id'] === $id) {
                $p['nombre'] = $_POST['nombre'];
                $p['marca']  = $_POST['marca'];
                $p['precio'] = (float) $_POST['precio'];
                $p['stock']  = (int) $_POST['stock'];
                $p['tipo']   = $_POST['tipo'];
                break;
            }
        }
        guardarProductos($productos, $archivo);
        $mensaje = "Producto modificado correctamente.";
    }
}

$productos = leerProductos($archivo);

// Si el usuario hace click en "Editar", cargamos ese producto en el formulario
$editando = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    foreach ($productos as $p) {
        if ($p['id'] === $id) { $editando = $p; break; }
    }
}

$tipos = [
    'Computadoras y laptops',
    'Smartphones y tablets',
    'Monitores y pantallas',
    'Teclados',
    'Ratones y trackpads',
    'Audífonos y parlantes',
    'Cámaras y webcams',
    'Almacenamiento (HDD / SSD / USB)',
    'Redes y conectividad',
    'Cables y adaptadores',
    'Cargadores y baterías',
    'Impresoras y escáneres',
    'Componentes internos',
    'Otro',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Productos - Lab 6</title>
</head>
<body>

<h1>Inventario de Productos</h1>
<p>Laboratorio 6 - Sistema de inventario con archivos JSON</p>
<hr>

<?php if ($mensaje): ?>
    <p><b><?= $mensaje ?></b></p>
<?php endif; ?>

<!-- ========== TABLA DE PRODUCTOS ========== -->
<h2>Productos registrados</h2>

<?php if (empty($productos)): ?>
    <p>No hay productos registrados todavía.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Marca</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Tipo</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($productos as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['nombre'] ?></td>
            <td><?= $p['marca'] ?></td>
            <td>$<?= number_format($p['precio'], 2) ?></td>
            <td><?= $p['stock'] ?></td>
            <td><?= $p['tipo'] ?></td>
            <td><a href="?editar=<?= $p['id'] ?>">Editar</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<hr>

<!-- ========== FORMULARIO ========== -->
<?php if ($editando): ?>
    <!-- Formulario para MODIFICAR -->
    <h2>Modificar producto</h2>
    <form method="POST">
        <input type="hidden" name="accion" value="modificar">
        <input type="hidden" name="id" value="<?= $editando['id'] ?>">

        <p>ID: <?= $editando['id'] ?> (no se puede cambiar)</p>

        <label>Nombre: <input type="text" name="nombre" value="<?= $editando['nombre'] ?>" required></label><br><br>
        <label>Marca: <input type="text" name="marca" value="<?= $editando['marca'] ?>" required></label><br><br>
        <label>Precio: <input type="number" name="precio" step="0.01" value="<?= $editando['precio'] ?>" required></label><br><br>
        <label>Stock: <input type="number" name="stock" value="<?= $editando['stock'] ?>" required></label><br><br>
        <label>Tipo:
            <select name="tipo">
                <?php foreach ($tipos as $t): ?>
                <option value="<?= $t ?>" <?= $editando['tipo'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Guardar cambios</button>
        <a href="index.php">Cancelar</a>
    </form>

<?php else: ?>
    <!-- Formulario para AGREGAR -->
    <h2>Agregar nuevo producto</h2>
    <form method="POST">
        <input type="hidden" name="accion" value="agregar">

        <label>Nombre: <input type="text" name="nombre" required></label><br><br>
        <label>Marca: <input type="text" name="marca" required></label><br><br>
        <label>Precio: <input type="number" name="precio" step="0.01" required></label><br><br>
        <label>Stock: <input type="number" name="stock" required></label><br><br>
        <label>Tipo:
            <select name="tipo">
                <?php foreach ($tipos as $t): ?>
                <option value="<?= $t ?>"><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Agregar producto</button>
    </form>
<?php endif; ?>

</body>
</html>

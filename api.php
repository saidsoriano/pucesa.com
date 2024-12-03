<?php
// Configuración de la base de datos
$host = '127.0.0.1'; // Dirección del servidor (localhost para XAMPP)
$user = 'root'; // Usuario por defecto en XAMPP
$password = ''; // Contraseña vacía por defecto en XAMPP
$database = 'sql10749287'; // Nombre de la base de datos creado en phpMyAdmin

// Conexión a la base de datos
$conn = new mysqli($host, $user, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die(json_encode(['message' => 'Error de conexión a la base de datos']));
}

// Obtener los datos enviados desde el cliente
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['correo']) && isset($data['puntos'])) {
    $correo = $data['correo'];
    $puntos = $data['puntos'];

    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id, puntos FROM persona WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Si el correo existe, actualiza los puntos
        $row = $result->fetch_assoc();
        $newPuntos = $row['puntos'] + $puntos;

        $updateStmt = $conn->prepare("UPDATE persona SET puntos = ? WHERE id = ?");
        $updateStmt->bind_param("di", $newPuntos, $row['id']);
        $updateStmt->execute();

        echo json_encode(['message' => "Puntos actualizados. Total: $newPuntos"]);
    } else {
        // Si no existe, inserta un nuevo registro
        $insertStmt = $conn->prepare("INSERT INTO persona (correo, puntos) VALUES (?, ?)");
        $insertStmt->bind_param("sd", $correo, $puntos);
        $insertStmt->execute();

        echo json_encode(['message' => "Nuevo registro creado con $puntos puntos"]);
    }
} else {
    echo json_encode(['message' => 'Datos incompletos']);
}

$conn->close();
?>

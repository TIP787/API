<?php
$servername = "192.168.1.34";
$username = "tomas";
$password = "S3gura@2024";
$dbname = "base_biblioteca";
//Listo
// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(array("error" => "Connection failed: " . $conn->connect_error)));
}

// Configura el tipo de contenido para la respuesta en JSON
header('Content-Type: application/json');

// Configuración inicial para manejar la URI y el ID
$uri = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim($uri, '/'));
$id_from_uri = end($segments);

// Obtén el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// Procesa la solicitud según el método HTTP
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
             // Obtener un libro específico por ID
             $id = intval($_GET['id']);
             $sql = "SELECT * FROM TABLAINVENTARIOS WHERE id = ?";
             $stmt = $conn->prepare($sql);
             $stmt->bind_param("i", $id);
             $stmt->execute();
             $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $Libros = $result->fetch_assoc();
                echo json_encode(array($Libros)); // Envolver en un array
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Libro no encontrado."));
            }

            $response = array();
if ($result->num_rows > 0) {
    $response['existe'] = true;
} else {
    $response['existe'] = false;
}

            $stmt->close();
        } else {
            // Obtener todos los Libros
            $sql = "SELECT * FROM TABLAINVENTARIOS";
            $result = $conn->query($sql);
            $Libros = array();
            while ($row = $result->fetch_assoc()) {
                $Libros[] = $row;
            }
            echo json_encode($Libros); // Devolver datos en formato de array
        }
        break;

    case 'POST':
         // Crear un nuevo libro
        $data = file_get_contents("php://input");

        // Verifica si los datos recibidos son JSON
        $decoded_data = json_decode($data, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Datos JSON válidos
            $nombre = $decoded_data['NOMBRE'];
            $codigo = $decoded_data['CODIGO'];
            $categoria = $decoded_data['CATEGORIA'];
            $ubicacion = $decoded_data['UBICACION'];

            $sql = "INSERT INTO TABLAINVENTARIOS (NOMBRE, CODIGO, CATEGORIA, UBICACION) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $nombre, $codigo, $categoria, $ubicacion);

            if ($stmt->execute()) {
                echo json_encode(array("success" => "Libro creado correctamente"));
            } else {
                echo json_encode(array("error" => "Error al crear el libro: " . $stmt->error));
            }
            $stmt->close();
        } else {
            // Datos no JSON (tratados como cadena)
            echo json_encode(["error" => "Datos JSON malformados"]);
        }
        break;


    case 'PUT':
       // Extraer el ID de la URL
       $uri = $_SERVER['REQUEST_URI'];
       $segments = explode('/', trim($uri, '/'));
       $id_from_uri = end($segments);
       $id = intval($id_from_uri);
       
       
        // Leer los datos JSON enviados en la solicitud
        $data = file_get_contents("php://input");
        $decoded_data = json_decode($data, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Validar que el ID es válido
            if ($id && $id > 0) {
            $nombre = $decoded_data['NOMBRE'] ?? null;
            $codigo = $decoded_data['CODIGO'] ?? null;
            $categoria = $decoded_data['CATEGORIA'] ?? null;
            $ubicacion = $decoded_data['UBICACION'] ?? null;

            // Verificar que los campos obligatorios están presentes
            if ($nombre && $codigo && $categoria && $ubicacion) {
                // Construir la consulta SQL dinámicamente según los campos presentes
            $sql = "UPDATE TABLAINVENTARIOS SET NOMBRE = ?, CODIGO = ?, CATEGORIA = ?, UBICACION = ?";
            $params = [$nombre, $codigo, $categoria, $ubicacion];
            $types = "ssss";

            $sql .= " WHERE id = ?";
                            $params[] = $id;
                            $types .= "i";
            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param($types, ...$params);

                            if ($stmt->execute()) {
                                echo json_encode(["message" => "INVENTARIO actualizado exitosamente."]);
                            } else {
                                echo json_encode(["message" => "Error al actualizar INVENTARIO.", "error" => $stmt->error]);
                            }
                            $stmt->close();
                        } else {
                            echo json_encode(["error" => "Datos incompletos para actualizar el INVENTARIO"]);
                        }
                    } else {
                        echo json_encode(["error" => "ID no proporcionado o inválido"]);
                    }
                } else {
                    echo json_encode(["error" => "Datos JSON malformados"]);
                }
                break;

                case 'DELETE':
                    // Extraer el ID de la URL
                    $uri = $_SERVER['REQUEST_URI'];
                    $segments = explode('/', trim($uri, '/'));
                    $id_from_uri = end($segments);
                    $id = intval($id_from_uri);
            
                    if ($id && $id > 0) {
                        $sql = "DELETE FROM TABLAINVENTARIOS WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
            
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(array("success" => "INVENTARIO eliminado correctamente"));
                            } else {
                                echo json_encode(array("error" => "El INVENTARIO con el ID especificado no existe"));
                            }
                        } else {
                            echo json_encode(array("error" => "Error al eliminar el INVENTARIO: " . $stmt->error));
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(array("error" => "ID no proporcionado o inválido"));
                    }
                    break;
            }
            
            $conn->close();
            ?>
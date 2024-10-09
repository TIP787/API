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
            // Obtener un socio específico por ID
            $id = intval($_GET['id']);
            $sql = "SELECT * FROM TABLASOCIOS WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $socios = $result->fetch_assoc();
                echo json_encode(array($socios)); // Envolver en un array
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Socio no encontrado."));
            }

            $response = array();
if ($result->num_rows > 0) {
    $response['existe'] = true;
} else {
    $response['existe'] = false;
}

            $stmt->close();
        } else {
            // Obtener todos los socios
            $sql = "SELECT * FROM TABLASOCIOS";
            $result = $conn->query($sql);
            $socios = array();
            while ($row = $result->fetch_assoc()) {
                $socios[] = $row;
            }
            echo json_encode($socios); // Devolver datos en formato de array
        }
        break;

        case 'POST':
            $data = file_get_contents("php://input");
            $decoded_data = json_decode($data, true);
        
            if (json_last_error() === JSON_ERROR_NONE) {
                $nombre = $decoded_data['Nombre'];
                $dni = $decoded_data['DNI'];
                $direccion = $decoded_data['Direccion'];
                $telefono = $decoded_data['Telefono'];
                $correo = $decoded_data['Correo'];
                $fecha = $decoded_data['Fecha'];
        
                $sql = "INSERT INTO TABLASOCIOS (Nombre, DNI, Direccion, Telefono, Correo, Fecha) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $nombre, $dni, $direccion, $telefono, $correo, $fecha);
        
                if ($stmt->execute()) {
                    echo json_encode(array("success" => "Socio creado correctamente"));
                } else {
                    echo json_encode(array("error" => "Error al crear el socio: " . $stmt->error));
                }
                $stmt->close();
            } else {
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
                        $nombre = $decoded_data['Nombre'] ?? null;
                        $dni = $decoded_data['DNI'] ?? null;
                        $direccion = $decoded_data['Direccion'] ?? null;
                        $telefono = $decoded_data['Telefono'] ?? null;
                        $correo = $decoded_data['Correo'] ?? null;
                        $fecha = $decoded_data['Fecha'] ?? null;
            
                        // Verificar que los campos obligatorios están presentes
                        if ($nombre && $dni && $direccion && $telefono && $correo) {
                            // Construir la consulta SQL dinámicamente según los campos presentes
                            $sql = "UPDATE TABLASOCIOS SET Nombre = ?, DNI = ?, Direccion = ?, Telefono = ?, Correo = ?";
                            $params = [$nombre, $dni, $direccion, $telefono, $correo];
                            $types = "sssss";
            
                            // Incluir la fecha solo si está presente
                            if ($fecha) {
                                $sql .= ", Fecha = ?";
                                $params[] = $fecha;
                                $types .= "s";
                            }
            
                            $sql .= " WHERE id = ?";
                            $params[] = $id;
                            $types .= "i";
            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param($types, ...$params);
            
                            if ($stmt->execute()) {
                                echo json_encode(["message" => "Socio actualizado exitosamente."]);
                            } else {
                                echo json_encode(["message" => "Error al actualizar socio.", "error" => $stmt->error]);
                            }
                            $stmt->close();
                        } else {
                            echo json_encode(["error" => "Datos incompletos para actualizar el socio"]);
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
                        $sql = "DELETE FROM TABLASOCIOS WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(array("success" => "Socio eliminado correctamente"));
                            } else {
                                echo json_encode(array("error" => "El socio con el ID especificado no existe"));
                            }
                        } else {
                            echo json_encode(array("error" => "Error al eliminar el socio: " . $stmt->error));
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(array("error" => "ID no proporcionado o inválido"));
                    }
                    break;
    }
    
    $conn->close();
    ?>
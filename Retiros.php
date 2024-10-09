        <?php
        $servername = "192.168.1.34";
        $username = "tomas";
        $password = "S3gura@2024";
        $dbname = "base_biblioteca";
        
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
                     // Obtener un retiro específico por ID
                     $id = intval($_GET['id']);
                     $sql = "SELECT * FROM TABLARETIROS WHERE id = ?";
                     $stmt = $conn->prepare($sql);
                     $stmt->bind_param("i", $id);
                     $stmt->execute();
                     $result = $stmt->get_result();
        
                    if ($result->num_rows > 0) {
                        $Retiros = $result->fetch_assoc();
                        echo json_encode(array($Retiros)); // Envolver en un array
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Retiro no encontrado."));
                    }
                    $stmt->close();
                } else {
                    // Obtener todos los retiros
                    $sql = "SELECT * FROM TABLARETIROS";
                    $result = $conn->query($sql);
                    $Retiros = array();
                    while ($row = $result->fetch_assoc()) {
                        $Retiros[] = $row;
                    }
                    echo json_encode($Retiros);// Devolver datos en formato de array
                }
                break;
        
            case 'POST':
                 // Crear un nuevo retiro
                $data = file_get_contents("php://input");
        
                // Verifica si los datos recibidos son JSON
                $decoded_data = json_decode($data, true);
        
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Datos JSON válidos
                    
                     $fecha = $decoded_data['FECHA'];
                     $codigo = $decoded_data['CODIGO'];
                     $libro = $decoded_data['LIBRO'];
                     $nombre = $decoded_data['NOMBRE'];
                     $telefono = $decoded_data['TELEFONO'];
        
                     $sql = "INSERT INTO TABLARETIROS (FECHA, CODIGO, LIBRO, NOMBRE, TELEFONO) VALUES (?, ?, ?, ?, ?)";
                     $stmt = $conn->prepare($sql);
                     $stmt->bind_param("sssss", $fecha, $codigo, $libro, $nombre, $telefono);

                    if ($stmt->execute()) {
                        echo json_encode(array("success" => "Retiro creado correctamente"));
                    } else {
                        echo json_encode(array("error" => "Error al crear el retiro: " . $stmt->error));
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

        // Actualizar un retiro existente
        $data = file_get_contents("php://input");
        $decoded_data = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // Validar que el ID es válido
            if ($id && $id > 0) {
            $fecha = $decoded_data['FECHA'] ?? null;
            $codigo = $decoded_data['CODIGO'] ?? null;
            $libro = $decoded_data['LIBRO'] ?? null;
            $nombre = $decoded_data['NOMBRE'] ?? null;
            $telefono = $decoded_data['TELEFONO'] ?? null;

            // Verificar que los campos obligatorios están presentes
            if ($fecha && $codigo && $libro && $nombre && $telefono) {
            $sql = "UPDATE TABLARETIROS SET FECHA = ?, CODIGO = ?, LIBRO = ?, NOMBRE = ?, TELEFONO = ?";
            $params = [$fecha, $codigo, $libro, $nombre, $telefono];
            $types = "sssss";

            $sql .= " WHERE id = ?";
                            $params[] = $id;
                            $types .= "i";

            $stmt = $conn->prepare($sql);
                            $stmt->bind_param($types, ...$params);
            
                            if ($stmt->execute()) {
                                echo json_encode(["message" => "RETIRO actualizado exitosamente."]);
                            } else {
                                echo json_encode(["message" => "Error al actualizar RETIOR.", "error" => $stmt->error]);
                            }
                            $stmt->close();
                        } else {
                            echo json_encode(["error" => "Datos incompletos para actualizar el RETIRO"]);
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
                        $sql = "DELETE FROM TABLARETIROS WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(array("success" => "retiro eliminado correctamente"));
                            } else {
                                echo json_encode(array("error" => "El retiro con el ID especificado no existe"));
                            }
                        } else {
                            echo json_encode(array("error" => "Error al eliminar el retiro: " . $stmt->error));
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(array("error" => "ID no proporcionado o inválido"));
                    }
                    break;
}

$conn->close();
?>

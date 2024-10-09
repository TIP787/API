            <?php
            $servername = "192.168.1.34";
            $username = "tomas";
            $password = "S3gura@2024";
            $dbname = "base_biblioteca";
            //listo
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
                        // Obtener una categoría específica por ID
                         $id = intval($_GET['id']);
                         $sql = "SELECT * FROM TABLACATEGORIAS WHERE id = ?";
                         $stmt = $conn->prepare($sql);
                           $stmt->bind_param("i", $id);
                         $stmt->execute();
                         $result = $stmt->get_result();
                         
                        if ($result->num_rows > 0) {
                            $categoria = $result->fetch_assoc();
                            echo json_encode(array($categoria)); // Envolver en un array
                        } else {
                            http_response_code(404);
                            echo json_encode(array("message" => "Categoria no encontrado."));
                        }
                        $stmt->close();
                    } else {
                        // Obtener todas los Categorias
                         $sql = "SELECT * FROM TABLACATEGORIAS";
                          $result = $conn->query($sql);
                         $categoria = array();
                         while ($row = $result->fetch_assoc()) {
                             $categoria[] = $row;
            }
            echo json_encode($categoria); // Devolver datos en formato de array
                    }
                    break;
            
                case 'POST':
                    // Crear una nueva categoria
                    $data = file_get_contents("php://input");
            
                    // Verifica si los datos recibidos son JSON
                    $decoded_data = json_decode($data, true);
            
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Datos JSON válidos
                        $categoria = $decoded_data['CATEGORIA'];
        
                         $sql = "INSERT INTO TABLACATEGORIAS (CATEGORIA) VALUES (?)";
                         $stmt = $conn->prepare($sql);
                         $stmt->bind_param("s", $categoria);
            
                        if ($stmt->execute()) {
                            echo json_encode(array("success" => "Categoria creado correctamente"));
                        } else {
                            echo json_encode(array("error" => "Error al crear el categoria: " . $stmt->error));
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
                    
                        // Actualizar una categoría existente
                        $data = file_get_contents("php://input");
                        $decoded_data = json_decode($data, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Validar que el ID es válido
                            if ($id && $id > 0) {
                                $categoria = $decoded_data['CATEGORIA'] ?? null;
                    
                                // Verificar que los campos obligatorios están presentes
                                if ($categoria) {
                                    $sql = "UPDATE TABLACATEGORIAS SET CATEGORIA = ?";
                                    $params = [$categoria];
                                    $types = "s";
                    
                                    $sql .= " WHERE id = ?";
                                    $params[] = $id;
                                    $types .= "i";
                    
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param($types, ...$params);
                    
                                    if ($stmt->execute()) {
                                        echo json_encode(["message" => "Categoria actualizado exitosamente."]);
                                    } else {
                                        echo json_encode(["message" => "Error al actualizar categoria.", "error" => $stmt->error]);
                                    }
                                    $stmt->close();
                                } else {
                                    echo json_encode(["error" => "Datos incompletos para actualizar el categoria"]);
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
                        $sql = "DELETE FROM TABLACATEGORIAS WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows > 0) {
                                echo json_encode(array("success" => "CATEGORIA eliminado correctamente"));
                            } else {
                                echo json_encode(array("error" => "El CATEGORIA con el ID especificado no existe"));
                            }
                        } else {
                            echo json_encode(array("error" => "Error al eliminar el categoria: " . $stmt->error));
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(array("error" => "ID no proporcionado o inválido"));
                    }
                    break;
}

$conn->close();
?>

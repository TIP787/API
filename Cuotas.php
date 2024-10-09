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
                    // Obtener un Pago específico por ID
                    $id = intval($_GET['id']);
                    $sql = "SELECT * FROM TABLACUOTAS WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    if ($result->num_rows > 0) {
                        $pagos = $result->fetch_assoc();
                        echo json_encode(array($pagos)); // Envolver en un array
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Pago no encontrado."));
                    }
                    $stmt->close();
                } else {
                    // Obtener todos los Pagos
                    $sql = "SELECT * FROM TABLACUOTAS";
                    $result = $conn->query($sql);
                    $pagos = array();
                    while ($row = $result->fetch_assoc()) {
                        $pagos[] = $row;
                    }
                    echo json_encode($pagos); // Devolver datos en formato de array
                }
                break;
        
            case 'POST':
                // Crear un nuevo Pago
                $data = file_get_contents("php://input");
        
                // Verifica si los datos recibidos son JSON
                $decoded_data = json_decode($data, true);
        
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Datos JSON válidos
                    $nombre = $decoded_data['NOMBRE'];
                     $fecha = $decoded_data['FECHA'];
                     $monto = $decoded_data['MONTO'];
           
                     $sql = "INSERT INTO TABLACUOTAS (NOMBRE, FECHA, MONTO) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                     $stmt->bind_param("sss", $nombre, $fecha, $monto);
        
                    if ($stmt->execute()) {
                        echo json_encode(array("success" => "Pago creado correctamente"));
                    } else {
                        echo json_encode(array("error" => "Error al crear el pago: " . $stmt->error));
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

        // Actualizar un pago existente
        $data = file_get_contents("php://input");
        $decoded_data = json_decode($data, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Validar que el ID es válido
            if ($id && $id > 0) {
            $nombre = $decoded_data['NOMBRE'] ?? null;
            $fecha = $decoded_data['FECHA'] ?? null;
            $monto = $decoded_data['MONTO'] ?? null;

             // Verificar que los campos obligatorios están presentes
             if ($nombre && $fecha && $monto) {
            $sql = "UPDATE TABLACUOTAS SET NOMBRE = ?, FECHA = ?, MONTO = ?";
            $params = [$nombre, $fecha, $monto];
            $types = "sss";

            $sql .= " WHERE id = ?";
                            $params[] = $id;
                            $types .= "i";

                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                                echo json_encode(["message" => "Pago actualizado exitosamente."]);
                            } else {
                                echo json_encode(["message" => "Error al actualizar pago.", "error" => $stmt->error]);
                            }
                            $stmt->close();
                        } else {
                            echo json_encode(["error" => "Datos incompletos para actualizar el pago"]);
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
            $sql = "DELETE FROM TABLACUOTAS WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(array("success" => "Pago eliminado correctamente"));
                } else {
                    echo json_encode(array("error" => "El pago con el ID especificado no existe"));
                }
            } else {
                echo json_encode(array("error" => "Error al eliminar el pago: " . $stmt->error));
            }
            $stmt->close();
        } else {
            echo json_encode(array("error" => "ID no proporcionado o inválido"));
        }
        break;
}

$conn->close();
?>

    
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
                    // Obtener un Gasto específico por ID
                    $id = intval($_GET['id']);
                    $sql = "SELECT * FROM TABLAGASTOS WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
        
                    if ($result->num_rows > 0) {
                        $gastos = $result->fetch_assoc();
                        echo json_encode(array($gastos)); // Envolver en un array
                    } else {
                        http_response_code(404);
                        echo json_encode(array("message" => "Gasto no encontrado."));
                    }
                    $stmt->close();
                } else {
                    // Obtener todos los Gastos
                     $sql = "SELECT * FROM TABLAGASTOS";
                     $result = $conn->query($sql);
                     $gastos = array();
                     while ($row = $result->fetch_assoc()) {
                      $gastos[] = $row;
                 }
                echo json_encode($gastos); // Devolver datos en formato de array
                }
                break;
        
            case 'POST':
                // Crear un nuevo socio
                $data = file_get_contents("php://input");
        
                // Verifica si los datos recibidos son JSON
                $decoded_data = json_decode($data, true);
        
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Datos JSON válidos
                     $motivo = $decoded_data['MOTIVO'];
                      $monto = $decoded_data['MONTO'];
                     $observaciones = $decoded_data['OBSERVACIONES'];
                     $fecha = $decoded_data['FECHA'];
           
                     $sql = "INSERT INTO TABLAGASTOS (MOTIVO, MONTO, OBSERVACIONES, FECHA) VALUES (?, ?, ?, ?)";
                     $stmt = $conn->prepare($sql);
                     $stmt->bind_param("ssss", $motivo, $monto, $observaciones, $fecha);
        
                    if ($stmt->execute()) {
                        echo json_encode(array("success" => "Gasto creado correctamente"));
                    } else {
                        echo json_encode(array("error" => "Error al crear el gasto: " . $stmt->error));
                    }
                    $stmt->close();
                } else {
                    // Datos no JSON (tratados como cadena)
                    echo json_encode(array('data' => array($data)));
                }
                break;
        

    case 'PUT':
          // Extraer el ID de la URL
          $uri = $_SERVER['REQUEST_URI'];
          $segments = explode('/', trim($uri, '/'));
          $id_from_uri = end($segments);
         $id = intval($id_from_uri);


        // Actualizar un gasto existente
        $data = file_get_contents("php://input");
        $decoded_data = json_decode($data, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Validar que el ID es válido
            if ($id && $id > 0) {
            $motivo = $decoded_data['MOTIVO']?? null;
            $monto = $decoded_data['MONTO']?? null;
            $observaciones = $decoded_data['OBSERVACIONES']?? null;
            $fecha = $decoded_data['FECHA']?? null;

            // Verificar que los campos obligatorios están presentes
            if ($motivo&& $monto && $observaciones && $fecha) {
                // Construir la consulta SQL dinámicamente según los campos presentes
            $sql = "UPDATE TABLAGASTOS SET MOTIVO = ?, MONTO = ?, OBSERVACIONES = ?, FECHA = ?";
            $params = [$motivo, $monto, $observaciones, $fecha];
            $types = "ssss";

            $sql .= " WHERE id = ?";
                            $params[] = $id;
                            $types .= "i";
            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param($types, ...$params);

                            if ($stmt->execute()) {
                                echo json_encode(["message" => "GASTO actualizado exitosamente."]);
                            } else {
                                echo json_encode(["message" => "Error al actualizar GASTO.", "error" => $stmt->error]);
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
            $sql = "DELETE FROM TABLAGASTOS WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
        // Eliminar un gasto
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(array("success" => "GASTO eliminado correctamente"));
            } else {
                echo json_encode(array("error" => "El GASTO con el ID especificado no existe"));
            }
        } else {
            echo json_encode(array("error" => "Error al eliminar el GASTO: " . $stmt->error));
        }
        $stmt->close();
    } else {
        echo json_encode(array("error" => "ID no proporcionado o inválido"));
    }
    break;

}

$conn->close();
?>

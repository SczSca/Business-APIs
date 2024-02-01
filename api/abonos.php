<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Content-type: application/json; charset=UTF-8');

$Serverpath = $_SERVER['DOCUMENT_ROOT'];
$headers=apache_request_headers();
require_once($Serverpath."/config.php");

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $correo = isset($_GET['correo']) ? $_GET['correo'] : NULL;
        $id_ticket = isset($_GET['idt']) ? $_GET['idt'] : NULL;
        
        $dataResponse = array();

        if($correo && isset($id_ticket)){
            $condicional = $id_ticket == 0 ? "correo LIKE '%".$correo."%'" : "correo LIKE '%".$correo."%' AND fk_ticket = ".$id_ticket;
            $sqlString = "SELECT * FROM vw_abonos_ticket WHERE ".$condicional;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $c = 0;
                while($r = mysqli_fetch_array($resultado)){
                    $temp[$c] = array('id_abono' => $r['id_abono'], 'fk_ticket' => $r['fk_ticket'], 'fecha' => $r['fecha'], 'abono' => $r['abono'], 'fk_cliente' => $r['fk_cliente'], 'correo' => $r['correo'], 'tel' => $r['telefono']);
                    $c++;
                }
                $dataResponse = array_merge($dataResponse, $temp);
                
                http_response_code(200);
                echo json_encode(array(
                    "error"=>false,
                    "statusCode"=>200,
                    "message"=>"OK",
                    "data"=>$dataResponse
                ));
            }
        }else{
            http_response_code(400);
            echo json_encode(array(
                "error" => true,
                "statusCode"=>400,
                "message"=>"Faltan datos"
            ));
        }
        
        
    break;
    
    case 'POST':
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $id_abono = isset($data->id_abono) ? $data->id_abono : NULL;
        $abono = isset($data->abono) ? $data->abono : NULL;
        $fk_ticket = isset($data->fk_ticket) ? $data->fk_ticket : NULL;

        $operacion = isset($data->operacion) ? $data->operacion : NULL;

        switch ($operacion) {
            
            case 'POST':
                if (isset($fk_ticket) && isset($abono) ) {
                    // Todas las variables están definidas y puedes continuar con tu lógica aquí
                    $sqlString = "INSERT INTO abonos(fk_ticket, abono) VALUES(".$fk_ticket.", ".$abono.")";
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_affected_rows($conn);
                    if($num > 0){
                        http_response_code(201);
                        echo json_encode(array(
                          "error"=>false,
                          "statusCode"=>201,
                          "message"=>"Registro guardado"
                        ));
                        mysqli_free_result($resultado);
                    }else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"No se guardo ningún registro"
                        ));
                    }
                    
                } else {
                    http_response_code(400);
                    echo json_encode(array(
                      "error" => true,
                      "statusCode"=>400,
                      "message"=>"Faltan datos"
                    ));
                }
            break;

            case 'PUT':
                if (isset($fk_ticket) && isset($abono) && isset($id_abono)) {
                    $sqlString = "UPDATE abonos SET fk_ticket = ".$fk_ticket.", abono = ".$abono." WHERE id_abono = ".$id_abono;
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_affected_rows($conn);
                    if ($num > 0) {
                        http_response_code(200);
                        echo json_encode(array(
                            "error"=> false,
                            "statusCode"=> 200,
                            "message"=> "Registro actualizado"
                        ));
                    }else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"No se actualizó ningún registro"
                        ));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array(
                      "error" => true,
                      "statusCode"=>400,
                      "message"=>"Faltan datos"
                    ));
                }
            break;

            case 'PATCH':
                http_response_code(405);
                echo json_encode(array(
                    "error" => true,
                    "statusCode"=>405,
                    "Error" => "Metodo No permitido"
                ));
            break;
            
            case 'DELETE':
                if(isset($id_abono)){
                    $sqlString = "DELETE FROM abonos WHERE id_abono = ".$id_abono;
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_affected_rows($conn);
                    if ($num > 0) {
                        http_response_code(200);
                        echo json_encode(array(
                            "error"=> false,
                            "statusCode"=> 200,
                            "message"=> "abono borrado"
                        ));
                    }else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"No se borró el abono"
                        ));
                    }
                } else{
                    http_response_code(400);
                    echo json_encode(array(
                      "error" => true,
                      "statusCode"=>400,
                      "message"=>"Faltan datos"
                    ));
                }
            break;

            default:
                http_response_code(405);
                echo json_encode(array(
                    "error" => true,
                    "statusCode"=>405,
                    "Error" => "Metodo No permitido"
                ));
            break;
        }
        
        
        
    break;
    
    case 'PUT':
        if (isset($fk_ticket) && isset($abono) && isset($id_abono)) {
            $sqlString = "UPDATE abonos SET fk_ticket = ".$fk_ticket.", abono = ".$abono." WHERE id_abono = ".$id_abono;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_affected_rows($conn);
            if ($num > 0) {
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "Registro actualizado"
                ));
            }else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se actualizó ningún registro"
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
              "error" => true,
              "statusCode"=>400,
              "message"=>"Faltan datos"
            ));
        }
    break;

    case 'PATCH':
        http_response_code(405);
        echo json_encode(array(
            "error" => true,
            "statusCode"=>405,
            "Error" => "Metodo No permitido"
        ));
    break;
    
    case 'DELETE':
        if(isset($id_abono)){
            $sqlString = "DELETE FROM abonos WHERE id_abono = ".$id_abono;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_affected_rows($conn);
            if ($num > 0) {
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "cliente borrado"
                ));
            }else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se borró el cliente"
                ));
            }
        } else{
            http_response_code(400);
            echo json_encode(array(
              "error" => true,
              "statusCode"=>400,
              "message"=>"Faltan datos"
            ));
        }
    break;

    default:
        http_response_code(405);
        echo json_encode(array(
          "error" => true,
          "statusCode"=>405,
          "Error" => "Metodo No permitido"
        ));
    break;
    
}
mysqli_close($conn);
?>
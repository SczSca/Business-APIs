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

        $id_cliente = isset($_GET['idc']) ? $_GET['idc'] : NULL;
        
        $temp;
        $dataResponse = array();
        $tickets = array();
        $detallesTicket = array();

        if($id_cliente){
            $condicional = "fk_cliente = ".$id_cliente;
            $sqlString = "SELECT * FROM tickets_temp WHERE ".$condicional;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $r = mysqli_fetch_array($resultado);
                $temp[0] = array('id_ticket' => $r['id_ticket'], 'fecha' => $r['fecha'], 'detallesTicket' => '' , 'fk_cliente' => $r['fk_cliente']);
                $tickets = array_merge($tickets, $temp);
                $condicional = "fk_ticket = ".$tickets[0]['id_ticket'];
                $sqlString = "SELECT * FROM vw_detTickets_productos_temp WHERE ".$condicional;
                $resultado = mysqli_query($conn, $sqlString);
                $num = mysqli_num_rows($resultado); 
                if($num > 0){
                    $c = 0;
                    $temp = array();
                    while( $r = mysqli_fetch_array($resultado)){
                        $temp[$c] = array('id_detallesTicket' => $r['id_detallesTicket'], 'id_ticket' => $r['fk_ticket'],'fk_producto' => $r['fk_producto'], 'nombre' => $r['nombre'], 'precio' => $r['precio'], 'cantidad' => $r['cantidad'], 'url_img' => $r['url_img']);
                        $c++;
                    }
                    $tickets[0]['detallesTicket'] = $temp;

                }
                $dataResponse = $tickets;
                $tickets = $temp = null;

                
            }
            http_response_code(200);
                echo json_encode(array(
                    "error"=>false,
                    "statusCode"=>200,
                    "message"=>"OK",
                    "data"=>$dataResponse
                ));
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
        $id_ticket = isset($data->id_ticket) ? $data->id_ticket : NULL;
        $fk_cliente = isset($data->fk_cliente) ? $data->fk_cliente : NULL;
        $fk_producto = isset($data->fk_producto) ? $data->fk_producto : NULL;
        $cantidad = isset($data->cantidad) ? $data->cantidad : NULL;

        $operacion = isset($data->operacion) ? $data->operacion : NULL;

        switch ($operacion) {
            
            case 'POST':
                if ($fk_cliente && $cantidad && $fk_producto) {
                    // Todas las variables están definidas y puedes continuar con tu lógica aquí
                    $sqlString = "SELECT id_ticket FROM tickets_temp WHERE fk_cliente = ".$fk_cliente;
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_num_rows($resultado);
                    if ($num){
                        $r = mysqli_fetch_array($resultado);
                        $id_ticket = $r['id_ticket'];
                        //func que retorna un http response 201 o 400
                        insert_detallesTicket($conn, $id_ticket, $fk_producto, $cantidad); 

                    }else{
                        $sqlString = "INSERT INTO tickets_temp(fk_cliente) VALUES(".$fk_cliente.")";
                        $resultado = mysqli_query($conn, $sqlString);
                        $num = mysqli_insert_id($conn);
                        if ($num){
                            //func que retorna un http response 201 o 400
                            insert_detallesTicket($conn, $num, $fk_producto, $cantidad);

                        }else{
                            http_response_code(400);
                            echo json_encode(array(
                            "error"=>true,
                            "statusCode"=>400,
                            "message"=>"Problemas al crear ticket"
                            ));
                        }
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
                http_response_code(405);
                echo json_encode(array(
                    "error" => true,
                    "statusCode"=>405,
                    "Error" => "Metodo No permitido"
                ));
            break;

            case 'PATCH':
                if($cantidad && $fk_producto && $id_ticket){
                    $condicional = "fk_ticket = ".$id_ticket." AND fk_producto = ".$fk_producto;
                    $sqlString = "UPDATE detalles_ticket_temp SET cantidad = ".$cantidad." WHERE ".$condicional;
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_affected_rows($conn);
                    if ($num){
                        http_response_code(200);
                        echo json_encode(array(
                            "error"=> false,
                            "statusCode"=> 200,
                            "message"=> "cantidad actualizada"
                        ));
                    } else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"No se pudo realizar el cambio de la cantidad"
                        ));
                    }
                }
            break;
            
            case 'DELETE':
                if($id_ticket && $fk_producto){
                    $condicional = "fk_ticket = ".$id_ticket." AND fk_producto = ".$fk_producto;
                    $sqlString = "DELETE FROM detalles_ticket_temp WHERE ".$condicional;
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_affected_rows($conn);
                    if ($num > 0) {
                        http_response_code(200);
                        echo json_encode(array(
                            "error"=> false,
                            "statusCode"=> 200,
                            "message"=> "producto borrado del ticket"
                        ));
                    }else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"No se borró el producto del ticket"
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
        http_response_code(405);
        echo json_encode(array(
            "error" => true,
            "statusCode"=>405,
            "Error" => "Metodo No permitido"
        ));
    break;

    case 'PATCH':
        if($cantidad && $fk_producto && $id_ticket){
            $condicional = "fk_ticket = ".$id_ticket." AND fk_producto = ".$fk_producto;
            $sqlString = "UPDATE detalles_ticket_temp SET cantidad = ".$cantidad." WHERE ".$condicional;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_affected_rows($conn);
            if ($num){
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "cantidad actualizada"
                ));
            } else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se pudo realizar el cambio de la cantidad"
                ));
            }
        }
    break;
    
    case 'DELETE':
        if($id_ticket && $fk_producto){
            $condicional = "fk_ticket = ".$id_ticket." AND fk_producto = ".$fk_producto;
            $sqlString = "DELETE FROM detalles_ticket_temp WHERE ".$condicional;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_affected_rows($conn);
            if ($num > 0) {
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "producto borrado del ticket"
                ));
            }else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se borró el producto del ticket"
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
function insert_detallesTicket($conn, $id_ticket, $fk_producto, $cantidad){
    $sqlString = "INSERT INTO detalles_ticket_temp(fk_ticket, fk_producto, cantidad) VALUES(".$id_ticket.", ".$fk_producto.", ".$cantidad.")";
    $resultado = mysqli_query($conn, $sqlString);
    $num = mysqli_affected_rows($conn);
    if ($num){
        http_response_code(201);
        echo json_encode(array(
            "error"=>false,
            "statusCode"=>201,
            "message"=>"producto guardado con éxito"
        ));
    }else{
        http_response_code(400);
        echo json_encode(array(
            "error"=>true,
            "statusCode"=>400,
            "message"=>"problemas al guardar el producto"
        ));
    }
}
?>
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
        $id_tienda = isset($_GET['idt']) ? $_GET['idt'] : NULL;
        
        $temp;
        $dataResponse = array();
        $tickets = array();
        $detallesTicket = array();
        $types = "";
        $params = array();

        if(isset($id_cliente) && isset($id_tienda)){
            $types = "i";
            $params[] = $id_tienda;

            $sqlString = "SELECT id_ticket, fecha, total, credito, nombre_completo, estado FROM vw_ticket_cliente WHERE fk_tienda = ?";
            if( $id_cliente > 0 ){
                $sqlString .= " AND id_cliente = ?";
                $types .= "i";
                $params[] = $id_cliente;
            }
            // $condicional_cliente = $id_cliente == 0 ? "" : " AND fk_cliente = ?";
            //$condicional = $id_tienda == 0 ? $condicional_cliente : $condicional_cliente." AND fk_tienda = ".$id_tienda;
            

            $sqlPreparado = mysqli_prepare($conn, $sqlString);
            $bind_params = array_merge(array($sqlPreparado, $types), $params);
            call_user_func_array('mysqli_stmt_bind_param', $bind_params);
            // mysqli_stmt_bind_param( $sqlPreparado, $types, $id_tienda, $id_cliente );
            // $resultado = mysqli_query($conn, $sqlString);
            if( mysqli_stmt_execute($sqlPreparado) ){
                $resultado = mysqli_stmt_get_result($sqlPreparado);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $c = 0;
                while($r = mysqli_fetch_array($resultado)){
                    $temp[$c] = array('id_ticket' => $r['id_ticket'], 'fecha' => $r['fecha'], 'total' => $r['total'], 'credito' => $r['credito'], 'nombre_completo' => $r['nombre_completo'], 'estado' => $r['estado']);
                    $c++;
                }
                $tickets = array_merge($tickets, $temp);
                
                $ticketLength = count($tickets);
                for($i = 0; $i < $ticketLength; $i++){
                    $c = 0;
                    $temp = array();
                    
                    $condicional = "fk_ticket = ".$tickets[$i]["id_ticket"];
                    $sqlString = "SELECT * FROM vw_detTickets_productos WHERE ".$condicional;
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_num_rows($resultado);
                    while( $r = mysqli_fetch_array($resultado)){
                        $temp[$c] = array('id_detallesTicket' => $r['id_detallesTicket'], 'id_ticket' => $r['fk_ticket'],'fk_producto' => $r['fk_producto'], 'nombre' => $r['nombre'], 'precio' => $r['precio'], 'cantidad' => $r['cantidad'], 'url_img' => $r['url_img']);
                        $c++;
                    }
                    $tickets[ $i ]['detallesTicket'] = $temp;

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
        $id_ticket = isset($data->id_ticket) ? $data->id_ticket : NULL;
        $fk_tienda = isset($data->fk_tienda) ? $data->fk_tienda : NULL;
        $fk_cliente = isset($data->fk_cliente) ? $data->fk_cliente : NULL;
        $fk_producto = isset($data->fk_producto) ? $data->fk_producto : NULL;
        $cantidad = isset($data->cantidad) ? $data->cantidad : NULL;

        $operacion = isset($data->operacion) ? $data->operacion : NULL;

        switch ($operacion) {
            
            case 'POST':
                if ($fk_cliente && $id_ticket && $fk_tienda ) {
                    // Todas las variables están definidas y puedes continuar con tu lógica aquí
                    $dataResponse = "Se realizó correctamente";
                    // $sqlString = "CALL TraspasarCarrito(".$fk_cliente.", ".$id_ticket.", ".$fk_tienda.")";
                    $sqlString = "CALL TraspasarCarrito( ? , ? , ? )";
                    
                    $sqlPreparado = mysqli_prepare( $conn, $sqlString );
                    mysqli_stmt_bind_param( $sqlPreparado, "iii", $fk_cliente, $id_ticket, $fk_tienda );

                    // $resultado = mysqli_query($conn, $sqlString);
                    if( mysqli_stmt_execute($sqlPreparado) ){
                    $num = mysqli_num_rows($resultado);
                    if($num){
                        $r = mysqli_fetch_array($resultado);
                        $dataResponse = $r['@text'];
                        
                    }
                    http_response_code(200);
                    echo json_encode(array(
                        "error"=> false,
                        "statusCode"=> 200,
                        "message"=> $dataResponse
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
                http_response_code(405);
                echo json_encode(array(
                "error" => true,
                "statusCode"=>405,
                "Error" => "Metodo No permitido"
                ));
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
                if(isset($id_ticket)){
                    // $sqlString = "DELETE FROM tickets WHERE id_ticket = ".$id_ticket;
                    $sqlString = "DELETE FROM tickets WHERE id_ticket = ?";
                    $sqlPreparado = mysqli_prepare( $conn, $sqlString );
                    mysqli_stmt_bind_param( $sqlPreparado, "i", $id_ticket);
                    // $resultado = mysqli_query($conn, $sqlString);
                    if( mysqli_stmt_execute($sqlPreparado) ){
                    $num = mysqli_affected_rows($conn);
                    if ($num > 0) {
                        http_response_code(200);
                        echo json_encode(array(
                            "error"=> false,
                            "statusCode"=> 200,
                            "message"=> "ticket borrado"
                        ));
                    }else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"No se borró el ticket"
                        ));
                    }
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
        http_response_code(405);
        echo json_encode(array(
            "error" => true,
            "statusCode"=>405,
            "Error" => "Metodo No permitido"
        ));
    break;
    
    case 'DELETE':
        if(isset($id_ticket)){
            // $sqlString = "DELETE FROM tickets WHERE id_ticket = ".$id_ticket;
            $sqlString = "DELETE FROM tickets WHERE id_ticket = ?";
            $sqlPreparado = mysqli_prepare( $conn, $sqlString );
            mysqli_stmt_bind_param( $sqlPreparado, "i", $id_ticket);
            // $resultado = mysqli_query($conn, $sqlString);
            if( mysqli_stmt_execute($sqlPreparado) ){
            $num = mysqli_affected_rows($conn);
            if ($num > 0) {
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "ticket borrado"
                ));
            }else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se borró el ticket"
                ));
            }
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
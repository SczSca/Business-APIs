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
        $id_tienda = isset($_GET['idt']) ? $_GET['idt'] : NULL;
        $fecha_inicio = isset($_GET['finicio']) ? $_GET['finicio'] : NULL;
        $fecha_final = isset($_GET['ffinal']) ? $_GET['ffinal'] : NULL;
        
        $dataResponse = array();
        
        $temp;
        $tickets_abonos = array();
        $venta_total = 0;
        $abono_total = 0;
        $costo_total = 0;
        $costo_cubierto_abono = array();
        $abono_anterior_total = 0;
        $abono_actual = 0;
        $abonos_otrosPlazos = 0;
        $abonos_plazo = array();
        $abonos_anteriores = array();

        $tickets_pagados = array();
        $tickets_sin_pago = array();
        $detallesTicket = array();
        $debug = 0;

        if($fecha_inicio && $fecha_final && $id_tienda){
            $fecha_inicio = new DateTimeImmutable($fecha_inicio);
            $fecha_final = new DateTimeImmutable($fecha_final);
            //$fecha_inicio->setTime(0,0,0);
            //$fecha_final->setTime(0,0,0);
            $fInicio = $fecha_inicio->format('Y-m-d H:i:s');
            $fFinal = $fecha_final->format('Y-m-d H:i:s');
            $condicional = "fk_tienda = ".$id_tienda." AND fecha BETWEEN '".$fInicio."' AND '".$fFinal."' ";
            $sqlString = "SELECT * FROM vw_abonos_ticket WHERE ".$condicional." ORDER BY fk_ticket";
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num){
                $debug = 1;
                $ii = 1;
                $c = 1;
                $r = mysqli_fetch_array($resultado);
                // $condicional_fkTickets = "fk_ticket";
                $condicional_tickets = $r['fk_ticket'];
                $temp[0] = array('id_abono' => $r['id_abono'], 'fecha' => $r['fecha'], 'fk_ticket' => $r['fk_ticket'], 'abono' => $r['abono']);
                $tickets_abonos[0] = [$temp[0]['fk_ticket'],$r['abono']];
                $abono_actual += $r['abono'];
                while($r = mysqli_fetch_array($resultado)){
                    $temp[$c] = array('id_abono' => $r['id_abono'], 'fecha' => $r['fecha'], 'fk_ticket' => $r['fk_ticket'], 'abono' => $r['abono']);
                    if($tickets_abonos[$ii-1][0] == $temp[$c]['fk_ticket']){ 
                        $tickets_abonos[$ii-1][1] += $r['abono'];
                        $abono_actual += $r['abono'];
                    }else {
                        $condicional_tickets .= ", ".$r['fk_ticket'];
                        $tickets_abonos[$ii] = [$temp[$c]['fk_ticket'],$r['abono']];
                        $abono_actual += $tickets_abonos[$ii][1];
                        $ii++;
                    }
                    $c++;
                }
                $debug = $tickets_abonos;
                mysqli_free_result($resultado);
                $sqlString = "CALL AbonosOtrosPlazos('".$fInicio."', '".$fFinal."', '(".$condicional_tickets.")',".$id_tienda.")";
                $debug = $sqlString;
                $resultado = mysqli_query($conn,$sqlString);
                if($resultado){
                    $c = 0;
                    $temp = array();
                    while($r = mysqli_fetch_array($resultado)){
                        $temp[$c] = array('fk_ticket' => $r['fk_ticket'], 'abono' => $r['abono']);
                        $abonos_otrosPlazos += $r['abono'];
                    }
                }
                
                //ERROR AQUI
                mysqli_free_result($resultado);
                mysqli_next_result($conn);
                $c= 0;
                $temp = array();
                $condicional = "id_ticket NOT IN (".$condicional_tickets.") AND fk_tienda = ".$id_tienda;
                $sqlString = "SELECT * FROM tickets WHERE ".$condicional;
                $resultado = mysqli_query($conn,$sqlString);
                $num = mysqli_num_rows($resultado);
                //posible error, prueba sin necesidad de un if
                while($r = mysqli_fetch_array($resultado)){
                    $temp[$c] = array('id_ticket' => $r['id_ticket'], 'fecha' => $r['fecha'], 'fk_cliente' => $r['fk_cliente'], 'total' => $r['total'], 'credito' => $r['credito'], 'estado' => $r['estado']);
                    $venta_total += $r['total'];
                    $abono_total += $r['credito']; //probablemente innecesario ya que retorna tickets sin abonos
                    $c++;
                }
                $tickets_sin_pago = $temp;
                
                mysqli_free_result($resultado);
                mysqli_next_result($conn);
                // $condicional_fkTickets .= $condicional;
                $condicional_idTickets = "id_ticket IN (".$condicional_tickets.")";
                // $condicional_idTickets = "id_ticket = ".$tickets_abonos[0][0];
                // $longitud_idTickets = count($tickets_abonos);
                // for($i = 1; $i < $longitud_idTickets; $i++){
                //     $condicional_idTickets .= " OR id_ticket = ".$tickets_abonos[$i][0];
                // }
                $sqlString = "CALL TicketsConjuntoDeIds('".$condicional_idTickets."', ".$id_tienda.")";
                $resultado = mysqli_query($conn,$sqlString);
                $num = mysqli_num_rows($resultado);

                if($num){
                    $c = 0;
                    $temp = array();
                    while($r = mysqli_fetch_array($resultado)){
                        $temp[$c] = array('id_ticket' => $r['id_ticket'], 'fecha' => $r['fecha'], 'fk_cliente' => $r['fk_cliente'], 'total' => $r['total'], 'credito' => $r['credito'], 'abonos_plazo' => $tickets_abonos[$c][1],'estado' => $r['estado']);
                        $venta_total += $r['total'];
                        $abono_total += $r['credito'];
                        $abonos_anteriores[$c] = $r['credito'] - $tickets_abonos[$c][1];
                        $abono_anterior_total += $abonos_anteriores[$c];
                        $c++;
                    }
                    $tickets_pagados = $temp;
                    
                    
            
            
                }
                $condicional = "'".$fInicio."', '".$fFinal."', ".$id_tienda;
                mysqli_free_result($resultado);
                mysqli_next_result($conn);
                $sqlString = "CALL ReporteVentasProductos(".$condicional.")";
                $debug = $sqlString;
                $resultado = mysqli_query($conn, $sqlString);
                if($resultado){
                    $c = 0;
                    $temp = array();
                    while($r = mysqli_fetch_array($resultado)){
                        $temp[$c] = array('id_producto' => $r['id_producto'], 'nombre' => $r['nombre'], 'precio_costo' => $r['precio_costo'], 'precio_venta' => $r['precio_venta'], 'cantidad_total' => $r['cantidad_total'], 'costo_total' => $r['costo_total'],'venta_total' => $r['venta_total'], 'ganancia' => $r['ganancia'], 'ganancia_porc' => $r['ganancia_porc']);

                        $costo_total += $r['costo_total'];
                        $c++;
                    }

                    $costo_total = $abono_total - $abono_actual - $abonos_otrosPlazos - $costo_total;
                    $costo_total = $costo_total > 0 ? 0 : $costo_total;
                    $ganancia = round($abono_actual + $costo_total,2);
                    $detallesTicket = $temp;
                    $dataResponse['datos_generales'] = array('costo_total' => $costo_total, 'venta_total' => $venta_total, 'abono_total' =>$abono_total,'abono_actual' => $abono_actual, 'ganancia' => $ganancia);
                    $dataResponse['tickets_pagados'] = $tickets_pagados;
                    $dataResponse['tickets_sin_pago'] = $tickets_sin_pago;
                    $dataResponse['ventas_productos'] = $detallesTicket;
                    $costo_total = null;
                    //$debug = [$abonos_anteriores, $abono_anterior_total];
                }
                $venta_total = $abono_total = $temp = null;
                mysqli_free_result($resultado);
                mysqli_next_result($conn);
            }
            http_response_code(200);
            echo json_encode(array(
                "error"=>false,
                "statusCode"=>200,
                "message"=>"OK",
                "data"=>$dataResponse,
                "debug" => $debug
            ));
            $dataResponse = null;
    
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

        $operacion = isset($data->operacion) ? $data->operacion : NULL;

        switch ($operacion) {
            
            case 'POST':
                http_response_code(405);
                echo json_encode(array(
                    "error" => true,
                    "statusCode"=>405,
                    "Error" => "Metodo No permitido"
                ));
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
                http_response_code(405);
                echo json_encode(array(
                "error" => true,
                "statusCode"=>405,
                "Error" => "Metodo No permitido"
                ));
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
        http_response_code(405);
        echo json_encode(array(
          "error" => true,
          "statusCode"=>405,
          "Error" => "Metodo No permitido"
        ));
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
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
        $fk_tienda = isset($_GET['idt']) ? $_GET['idt'] : NULL;
        $id_cliente = isset($_GET['idc']) ? $_GET['idc'] : NULL;
        
        $dataResponse = array();
        $status = true;

        if($id_cliente && $fk_tienda){
            $sqlString = "SELECT id_cliente, nombres, apellidos, domicilio, correo, telefono, foto FROM clientes WHERE fk_tienda = ".$fk_tienda." AND id_cliente = '".$id_cliente."'";
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $r = mysqli_fetch_array($resultado);
                $dataResponse[0] = array('id_cliente' => $r['id_cliente'], 'nombres' => $r['nombres'], 'apellidos' => $r['apellidos'], 'domicilio' => $r['domicilio'], 'correo' => $r['correo'], 'tel' => $r['telefono'], 'foto_url' => $r['foto']);
            }
        }else if($fk_tienda){
            $sqlString = "SELECT id_cliente, nombres, apellidos, domicilio, correo, telefono, foto FROM clientes WHERE fk_tienda = ".$fk_tienda;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $c = 0;
                while($r = mysqli_fetch_array($resultado)){
                    $temp[$c] = array('id_cliente' => $r['id_cliente'], 'nombres' => $r['nombres'], 'apellidos' => $r['apellidos'], 'domicilio' => $r['domicilio'], 'correo' => $r['correo'], 'tel' => $r['telefono'], 'foto_url' => $r['foto']);
                    $c++;
                }
                $dataResponse = array_merge($dataResponse, $temp);
            }
        }else $status = false;

        if($status){
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
                "error"=>true,
                "statusCode"=>400,
                "message"=>"Faltan datos"
            )); 
        }
        
    break;
    
    case 'POST':
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $id_cliente = isset($data->id_cliente) ? $data->id_cliente : NULL;
        $fk_tienda = isset($data->fk_tienda) ? $data->fk_tienda : NULL;
        $nombres = isset($data->nombres) ? $data->nombres : NULL;
        $apellidos = isset($data->apellidos) ? $data->apellidos : NULL;
        $domicilio = isset($data->domicilio) ? $data->domicilio : NULL;
        $correo = isset($data->correo) ? $data->correo : NULL;
        $telefono = isset($data->tel) ? $data->tel : NULL;
        $foto = isset($data->foto_url) ? $data->foto_url : NULL;

        $operacion = isset($data->operacion) ? $data->operacion : NULL;

        switch ($operacion) {
            
            case 'POST':
                if (isset($nombres) && isset($apellidos)  && isset($domicilio) && isset($correo) && isset($telefono) && isset($fk_tienda)) {
                    // Todas las variables están definidas y puedes continuar con tu lógica aquí
                    $sqlString = "INSERT INTO clientes(nombres, apellidos, domicilio, correo, telefono, foto, fk_tienda) VALUES('".$nombres."', '".$apellidos."', '".$domicilio."', '".$correo."', '".$telefono."', '".$foto."', ".$fk_tienda.")";
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
                if (isset($id_cliente) && isset($nombres) && isset($apellidos) && isset($domicilio) && isset($correo) && isset($telefono)) {
                    $sqlString = "UPDATE clientes SET nombres = '".$nombres."', apellidos = '".$apellidos."', domicilio = '".$domicilio."', correo = '".$correo."', telefono = '".$telefono."' WHERE id_cliente = ".$id_cliente;
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
                if(isset($id_cliente) && isset($foto)){
                    $sqlString = "UPDATE clientes SET foto = '".$foto."' WHERE id_cliente = ".$id_cliente;
                    $resultado = mysqli_query($conn, $sqlString);
                    $num = mysqli_affected_rows($conn);
                    if ($num > 0) {
                        http_response_code(200);
                        echo json_encode(array(
                            "error"=> false,
                            "statusCode"=> 200,
                            "message"=> "foto actualizada"
                        ));
                    }else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"No se actualizó la foto"
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
            
            case 'DELETE':
                if(isset($id_cliente)){
                    $sqlString = "DELETE FROM clientes WHERE id_cliente = ".$id_cliente;
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

        }
        
        
        
    break;
    
    case 'PUT':
        if (isset($id_cliente) && isset($nombres) && isset($apellidos) && isset($domicilio) ) {
            $sqlString = "UPDATE clientes SET nombres = '".$nombres."', apellidos = '".$apellidos."', domicilio = '".$domicilio."' WHERE id_cliente = ".$id_cliente;
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
        if(isset($id_cliente) && isset($correo) && isset($telefono)){
            $sqlString = "UPDATE clientes SET correo = '".$correo."', telefono = '".$telefono."' WHERE id_cliente = ".$id_cliente;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_affected_rows($conn);
            if ($num > 0) {
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "datos de contacto actualizados"
                ));
            }else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se actualizó ninguno de los datos de contacto"
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
    
    case 'DELETE':
        if(isset($id_cliente)){
            $sqlString = "DELETE FROM clientes WHERE id_cliente = ".$id_cliente;
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
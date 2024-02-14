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
        
        $dataResponse = array();

        if($id_tienda){
            $sqlString = "SELECT id_tienda, nombre, owner_name, owner_lastNames, logo FROM tiendas WHERE id_tienda = ?";

            $sqlPreparado = mysqli_prepare($conn, $sqlString);
            mysqli_stmt_bind_param( $sqlPreparado, 'i', $id_tienda);

            if( mysqli_stmt_execute($sqlPreparado) ){
                $resultado = mysqli_stmt_get_result($sqlPreparado);
                $num = mysqli_num_rows($resultado);
                if($num > 0){
                    $r = mysqli_fetch_array($resultado);
                    $dataResponse[0] = array('id_tienda' => $r['id_tienda'], 'nombre' => $r['nombre'], 'nombre_jefe' => $r['owner_name'], 'apellidos_jefe' => $r['owner_lastNames'], 'logo_url' => $r['logo']);
                }
            }
        }else{
            $sqlString = "SELECT id_tienda, nombre, owner_name, owner_lastNames, logo FROM tiendas";
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $c = 0;
                while($r = mysqli_fetch_array($resultado)){
                    $temp[] = array('id_tienda' => $r['id_tienda'], 'nombre' => $r['nombre'], 'nombre_jefe' => $r['owner_name'], 'apellidos_jefe' => $r['owner_lastNames'], 'logo_url' => $r['logo']);
                }
                $dataResponse = $temp;
            }
        }
        http_response_code(200);
        echo json_encode(array(
            "error"=>false,
            "statusCode"=>200,
            "message"=>"OK",
            "data"=>$dataResponse
        ));
        
    break;
    
    case 'POST':
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $id_tienda = isset($data->id_tienda) ? $data->id_tienda : NULL;
        $nombre = isset($data->nombre) ? $data->nombre : NULL;
        $correo = isset($data->correo) ? $data->correo : NULL;
        $ownerName = isset($data->owner_name) ? $data->owner_name : NULL;
        $ownerLastNames = isset($data->owner_lastNames) ? $data->owner_lastNames : NULL;
        $logo = isset($data->logo_url) ? $data->logo_url : NULL;
        $password = isset($data->password) ? $data->password : NULL;

        $operacion = isset($data->operacion) ? $data->operacion : NULL;

        switch ($operacion) {
            
            case 'POST':
                if (isset($nombre) && isset($correo) && isset($ownerName) && isset($ownerLastNames) && isset($password)) {
                    // Todas las variables están definidas y puedes continuar con tu lógica aquí
                    // $sqlString = "INSERT INTO tiendas(nombre,correo,owner_name,owner_lastNames,logo,password) VALUES('".$nombre."', '".$correo."', '".$ownerName."', '".$ownerLastNames."', '".$logo."', '".$password."')";
                    $sqlString = "INSERT INTO tiendas(nombre,correo,owner_name,owner_lastNames,logo,password) VALUES(?, ?, ?, ?, ?, ?)";

                    $sqlPreparado = mysqli_prepare($conn, $sqlString);
                    mysqli_stmt_bind_param( $sqlPreparado, "ssssss", $nombre, $correo, $ownerName, $ownerLastNames, $logo, $password );

                    if( mysqli_stmt_execute($sqlPreparado) ){
                        $resultado = mysqli_stmt_get_result($sqlPreparado);
                    
                        $num = mysqli_affected_rows($conn);
                        if($num > 0){
                            http_response_code(201);
                            echo json_encode(array(
                                "error"=>false,
                                "statusCode"=>201,
                                "message"=>"Registro guardado"
                                ));
                            
                            mysqli_stmt_close($sqlPreparado);
                        }else{
                            http_response_code(204);
                            echo json_encode(array(
                                "error"=>true,
                                "statusCode"=>204,
                                "message"=>"No se guardo ningún registro"
                            ));
                        }
                    }else{

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
                if (isset($nombre) && isset($ownerName) && isset($ownerLastNames) ) {
                    // $sqlString = "UPDATE tiendas SET nombre = '".$nombre."', owner_name = '".$ownerName."', owner_lastNames = '".$ownerLastNames."', logo = '".$logo."' WHERE id_tienda = ".$id_tienda;
                    $sqlString = "UPDATE tiendas SET nombre = ?, owner_name = ?, owner_lastNames = ?, logo = ? WHERE id_tienda = ?";

                    $sqlPreparado = mysqli_prepare($conn, $sqlString);
                    mysqli_stmt_bind_param( $sqlPreparado, 'ssssi', $nombre, $ownerName, $ownerLastNames, $logo, $id_tienda );

                    // $resultado = mysqli_query($conn, $sqlString);
                    if( mysqli_stmt_execute($sqlPreparado) ){
                        $resultado = mysqli_stmt_get_result($sqlPreparado);
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
                    } else{
                        http_response_code(400);
                        echo json_encode(array(
                            "error"=>true,
                            "statusCode"=>400,
                            "message"=>"Error en consulta"
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
                if(isset($id_tienda) && isset($correo) && isset($password)){
                    // $sqlString = "UPDATE tiendas SET correo = '".$correo."', password = '".$password."' WHERE id_tienda = ".$id_tienda;
                    $sqlString = "UPDATE tiendas SET correo = ?, password = ? WHERE id_tienda = ?";

                    $sqlPreparado = mysqli_prepare($conn, $sqlString);
                    mysqli_stmt_bind_param( $sqlPreparado, 'ssi', $correo, $password, $id_tienda );

                    if( mysqli_stmt_execute($sqlPreparado) ){
                        $resultado = mysqli_stmt_get_result($sqlPreparado);

                        $num = mysqli_affected_rows($conn);
                        if ($num > 0) {
                            http_response_code(200);
                            echo json_encode(array(
                                "error"=> false,
                                "statusCode"=> 200,
                                "message"=> "credenciales actualizadas"
                            ));
                        }else{
                            http_response_code(400);
                            echo json_encode(array(
                            "error"=>true,
                            "statusCode"=>400,
                            "message"=>"No se actualizó ninguna credencial"
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
            
            case 'DELETE':
                if(isset($id_tienda)){
                    // $sqlString = "DELETE FROM tiendas WHERE id_tienda = ".$id_tienda;

                    $sqlString = "DELETE FROM tiendas WHERE id_tienda = ?";

                    $sqlPreparado = mysqli_prepare($conn, $sqlString);
                    mysqli_stmt_bind_param( $sqlPreparado, 'i', $id_tienda);

                    // $resultado = mysqli_query($conn, $sqlString);

                    if( mysqli_stmt_execute($sqlPreparado) ){
                        $num = mysqli_affected_rows($conn);
                        if ($num > 0) {
                            http_response_code(200);
                            echo json_encode(array(
                                "error"=> false,
                                "statusCode"=> 200,
                                "message"=> "tienda borrada"
                            ));
                        }else{
                            http_response_code(400);
                            echo json_encode(array(
                                "error"=>true,
                                "statusCode"=>400,
                                "message"=>"No se borró la tienda"
                            ));
                        }
                    }else{
                        http_response_code(400);
                        echo json_encode(array(
                          "error"=>true,
                          "statusCode"=>400,
                          "message"=>"Error en la consulta"
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
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $nombre = isset($data->nombre) ? $data->nombre : NULL;
        $ownerName = isset($data->owner_name) ? $data->owner_name : NULL;
        $ownerLastNames = isset($data->owner_lastNames) ? $data->owner_lastNames : NULL;
        $logo = isset($data->logo_url) ? $data->logo_url : NULL;

        if (isset($nombre) && isset($ownerName) && isset($ownerLastNames) ) {
            // $sqlString = "UPDATE tiendas SET nombre = '".$nombre."', owner_name = '".$ownerName."', owner_lastNames = '".$ownerLastNames."', logo = '".$logo."' WHERE id_tienda = ".$id_tienda;
            $sqlString = "UPDATE tiendas SET nombre = ?, owner_name = ?, owner_lastNames = ?, logo = ? WHERE id_tienda = ?";

            $sqlPreparado = mysqli_prepare($conn, $sqlString);
            mysqli_stmt_bind_param( $sqlPreparado, 'ssssi', $nombre, $ownerName, $ownerLastNames, $logo, $id_tienda );

            // $resultado = mysqli_query($conn, $sqlString);
            if( mysqli_stmt_execute($sqlPreparado) ){
                $resultado = mysqli_stmt_get_result($sqlPreparado);
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
            } else{
                http_response_code(400);
                echo json_encode(array(
                    "error"=>true,
                    "statusCode"=>400,
                    "message"=>"Error en consulta"
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
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $id_tienda = isset($data->id_tienda) ? $data->id_tienda : NULL;
        $correo = isset($data->correo) ? $data->correo : NULL;
        $password = isset($data->password) ? $data->password : NULL;

        if(isset($id_tienda) && isset($correo) && isset($password)){
            $sqlString = "UPDATE tiendas SET correo = '".$correo."', password = '".$password."' WHERE id_tienda = ".$id_tienda;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_affected_rows($conn);
            if ($num > 0) {
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "credenciales actualizadas"
                ));
            }else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se actualizó ninguna credencial"
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
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $id_tienda = isset($data->id_tienda) ? $data->id_tienda : NULL;

        if(isset($id_tienda)){
            $sqlString = "DELETE FROM tiendas WHERE id_tienda = ".$id_tienda;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_affected_rows($conn);
            if ($num > 0) {
                http_response_code(200);
                echo json_encode(array(
                    "error"=> false,
                    "statusCode"=> 200,
                    "message"=> "tienda borrada"
                ));
            }else{
                http_response_code(400);
                echo json_encode(array(
                  "error"=>true,
                  "statusCode"=>400,
                  "message"=>"No se borró la tienda"
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
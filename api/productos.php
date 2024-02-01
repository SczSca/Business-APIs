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
        $fk_tienda = isset($_GET['fkt']) ? $_GET['fkt'] : 0;
        
        $dataResponse = array();

        if($fk_tienda > 0){
            $sqlString = "SELECT id_producto, fk_tienda, nombre, descripcion, cantidad, precio_costo, precio_venta, url_img FROM productos WHERE fk_tienda = ".$fk_tienda;
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $c = 0;
                while($r = mysqli_fetch_array($resultado)){
                    $temp[$c] = array('id_producto' => $r['id_producto'],'fk_tienda' => $r['fk_tienda'], 'nombre' => $r['nombre'], 'descripcion' => $r['descripcion'], 'cantidad' => $r['cantidad'], 'precio_costo' => $r['precio_costo'], 'precio_venta' => $r['precio_venta'], 'url_img' => $r['url_img']);
                    $c++;
                }
                $dataResponse = array_merge($dataResponse, $temp);
            }
        }else{
            $sqlString = "SELECT id_producto, fk_tienda, nombre, descripcion, cantidad, precio_costo, precio_venta, url_img FROM productos";
            $resultado = mysqli_query($conn, $sqlString);
            $num = mysqli_num_rows($resultado);
            if($num > 0){
                $c = 0;
                while($r = mysqli_fetch_array($resultado)){
                    $temp[$c] = array('id_producto' => $r['id_producto'],'fk_tienda' => $r['fk_tienda'], 'nombre' => $r['nombre'], 'descripcion' => $r['descripcion'], 'cantidad' => $r['cantidad'], 'precio_costo' => $r['precio_costo'], 'precio_venta' => $r['precio_venta'], 'url_img' => $r['url_img']);
                    $c++;
                }
                $dataResponse = array_merge($dataResponse, $temp);
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
        $id_producto = isset($data->id_producto) ? $data->id_producto : NULL;
        $fk_tienda = isset($data->fk_tienda) ? $data->fk_tienda : NULL;
        $nombre = isset($data->nombre) ? $data->nombre : NULL;
        $descripcion = isset($data->descripcion) ? $data->descripcion : NULL;
        $cantidad = isset($data->cantidad) ? $data->cantidad : NULL;
        $precio_costo = isset($data->precio_costo) ? $data->precio_costo : NULL;
        $precio_venta = isset($data->precio_venta) ? $data->precio_venta : NULL;
        $foto = isset($data->url_img) ? $data->url_img : NULL;

        $operacion = isset($data->operacion) ? $data->operacion : NULL;

        switch ($operacion) {
            
            case 'POST':
                if (isset($fk_tienda) && isset($nombre) && isset($descripcion)  && isset($cantidad) && isset($precio_costo) && isset($precio_venta) && isset($foto)) {
                    // Todas las variables están definidas y puedes continuar con tu lógica aquí
                    $sqlString = "INSERT INTO productos(fk_tienda, nombre, descripcion, cantidad, precio_costo, precio_venta, url_img) VALUES(".$fk_tienda.", '".$nombre."', '".$descripcion."', '".$cantidad."', '".$precio_costo."', '".$precio_venta."', '".$foto."')";
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
                if (isset($id_producto) && isset($nombre) && isset($descripcion) && isset($cantidad) && isset($precio_costo) && isset($precio_venta)) {
                    $sqlString = "UPDATE productos SET nombre = '".$nombre."', descripcion = '".$descripcion."', cantidad = '".$cantidad."', precio_costo = '".$precio_costo."', precio_venta = '".$precio_venta."' WHERE id_producto = ".$id_producto;
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
                if(isset($id_producto) && isset($foto)){
                    $sqlString = "UPDATE productos SET url_img = '".$foto."' WHERE id_producto = ".$id_producto;
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
                if(isset($id_producto)){
                    $sqlString = "DELETE FROM productos WHERE id_producto = ".$id_producto;
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
        
        
        
    break;
    
    case 'PUT':
        if (isset($id_producto) && isset($nombre) && isset($descripcion) && isset($cantidad) && isset($precio_costo) && isset($precio_venta)) {
            $sqlString = "UPDATE productos SET nombre = '".$nombre."', descripcion = '".$descripcion."', cantidad = '".$cantidad."', precio_costo = '".$precio_costo."', precio_venta = '".$precio_venta."' WHERE id_producto = ".$id_producto;
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
        if(isset($id_producto) && isset($foto)){
            $sqlString = "UPDATE productos SET foto = '".$foto."' WHERE id_producto = ".$id_producto;
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
        if(isset($id_producto)){
            $sqlString = "DELETE FROM productos WHERE id_producto = ".$id_producto;
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
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
    case 'POST':
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $correo = isset($data->correo) ? $data->correo : NULL;
        $password = isset($data->password) ? $data->password : NULL;
        
        if (isset($correo) && isset($password)) {
            // Todas las variables están definidas y puedes continuar con tu lógica aquí
            $sqlString = "SELECT id_tienda FROM tiendas WHERE correo = ? AND password = ?";
            $sqlPreparado = mysqli_prepare($conn, $sqlString);
            mysqli_stmt_bind_param( $sqlPreparado, 'ss', $correo, $password);

            if( mysqli_stmt_execute($sqlPreparado) ){
                $resultado = mysqli_stmt_get_result($sqlPreparado);
                $num = mysqli_num_rows($resultado);
                if($num > 0){
                    $r = mysqli_fetch_assoc($resultado);
                    
                    http_response_code(200);
                    echo json_encode(array(
                    "error"=>false,
                    "statusCode"=>200,
                    "message"=>"Login con éxito",
                    "id" => $r['id_tienda']
                    ));

                    mysqli_stmt_close($sqlPreparado);
                }else{
                    http_response_code(400);
                    echo json_encode(array(
                    "error"=>true,
                    "statusCode"=>400,
                    "message"=>"Datos incorrectos"
                    ));
                }
            }else{
                http_response_code(400);
                echo json_encode( array(
                    "error" => true
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
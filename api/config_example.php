<?php
  $host_name = 'The_hostname';
  $database = 'TheDb';
  $user_name = 'YourUsername';
  $password = 'YourPassword';

  $conn = new mysqli($host_name, $user_name, $password, $database);

  if ($conn->connect_error) {
    die('<p>Error al conectar con servidor MySQL: '. $conn->connect_error .'</p>');
  } else {
    echo '<p>Se ha establecido la conexión al servidor MySQL con éxito.</p>';
  }
?>
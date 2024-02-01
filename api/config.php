<?php
  $host_name = 'db5014713788.hosting-data.io';
  $database = 'dbs12226561';
  $user_name = 'dbu1285687';
  $password = '1pr0gr4mac!0nM0v1l#';

  $conn = new mysqli($host_name, $user_name, $password, $database);

  if ($conn->connect_error) {
    die('<p>Error al conectar con servidor MySQL: '. $conn->connect_error .'</p>');
  } else {
    echo '<p>Se ha establecido la conexión al servidor MySQL con éxito.</p>';
  }
?>
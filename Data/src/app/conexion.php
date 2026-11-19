<?php
use Psr\Container\ContainerInterface;

$container->set('base_datos',function(ContainerInterface $c){
    $conf = $c->get('config_bd');

    $opc = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ];

    $dsn = "mysql:host={$conf->host};dbname={$conf->database};charset={$conf->charset}";

    try {
        $conexion = new PDO($dsn,$conf->username, $conf->password,$opc);
    } catch (PDOException $e) {
        die("Error de conexion " . $e->getMessage());
    }

    return $conexion;
});
<?php
$container->set('config_bd',function(){
    // La contraseña se toma del secret de Docker (db_app_password.txt) para que
    // siempre coincida con la del usuario de la app en MySQL. Si no existe, se usa DB_PASSW del .env.
    $secret = '/run/secrets/db_app_password';
    $password = is_readable($secret) ? trim(file_get_contents($secret)) : $_ENV['DB_PASSW'];

    return(object)[
        'host' => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USER'],
        'password' => $password,
        'database' => $_ENV['DB_NAME'],
        'charset' => 'utf8mb4'
    ];
});

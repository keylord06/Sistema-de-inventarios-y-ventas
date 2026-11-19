<?php
namespace App\controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("API de datos - Inventario y Ventas");
    return $response;
});

$app->group('/api', function (RouteCollectorProxy $api) {

    $api->group('/auth', function (RouteCollectorProxy $endpoint) {
        $endpoint->post('/credentials', Auth::class . ':credentials');
        $endpoint->put('/tkRef/{id:[0-9]+}', Auth::class . ':updateTokenRef');
    });

    $api->group('/categoria', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('[/{id:[0-9]+}]', Categoria::class . ':read');
        $endpoint->post('', Categoria::class . ':create');
        $endpoint->put('/{id:[0-9]+}', Categoria::class . ':update');
        $endpoint->delete('/{id:[0-9]+}', Categoria::class . ':delete');
    });

    $api->group('/proveedor', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('[/{id:[0-9]+}]', Proveedor::class . ':read');
        $endpoint->get('/filter/{offset}/{limit}', Proveedor::class . ':filter');
        $endpoint->post('', Proveedor::class . ':create');
        $endpoint->put('/{id:[0-9]+}', Proveedor::class . ':update');
        $endpoint->delete('/{id:[0-9]+}', Proveedor::class . ':delete');
    });

    $api->group('/cliente', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('[/{id:[0-9]+}]', Cliente::class . ':read');
        $endpoint->get('/filter/{offset}/{limit}', Cliente::class . ':filter');
        $endpoint->post('', Cliente::class . ':create');
        $endpoint->put('/{id:[0-9]+}', Cliente::class . ':update');
        $endpoint->delete('/{id:[0-9]+}', Cliente::class . ':delete');
    });

    $api->group('/producto', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('[/{id:[0-9]+}]', Producto::class . ':read');
        $endpoint->get('/filter/{offset}/{limit}', Producto::class . ':filter');
        $endpoint->get('/stock-bajo', Producto::class . ':stockBajo');
        $endpoint->post('', Producto::class . ':create');
        $endpoint->post('/{id:[0-9]+}/entrada', Producto::class . ':entrada');
        $endpoint->put('/{id:[0-9]+}', Producto::class . ':update');
        $endpoint->delete('/{id:[0-9]+}', Producto::class . ':delete');
    });

    $api->group('/venta', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('[/{id:[0-9]+}]', Venta::class . ':read');
        $endpoint->get('/filter/{offset}/{limit}', Venta::class . ':filter');
        $endpoint->get('/cliente/{id:[0-9]+}', Venta::class . ':readCliente');
        $endpoint->post('', Venta::class . ':create');
        $endpoint->put('/{id:[0-9]+}/anular', Venta::class . ':anular');
    });
});

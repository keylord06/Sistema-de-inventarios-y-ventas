<?php
namespace App\controllers;

use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $api) {
  $api->group('/auth', function (RouteCollectorProxy $endpoint) {
    $endpoint->post('/login', Auth::class . ':login');
    $endpoint->delete('/loggout/{id}', Auth::class . ':loggout');
    $endpoint->post('/refresh-session/{id}', Auth::class . ':refreshSession');
  });

  // Solo Admin (rol_id 1) puede crear/editar/borrar el catálogo (categoria, proveedor, producto).
  $api->group('/categoria', function (RouteCollectorProxy $endpoint) {
    $endpoint->get('[/{id}]', Categoria::class . ':read');
    $endpoint->post('', Categoria::class . ':create')->add(soloRoles([1]));
    $endpoint->put('/{id}', Categoria::class . ':update')->add(soloRoles([1]));
    $endpoint->delete('/{id}', Categoria::class . ':delete')->add(soloRoles([1]));
  });

  $api->group('/proveedor', function (RouteCollectorProxy $endpoint) {
    $endpoint->get('/filter/{offset}/{limit}', Proveedor::class . ':filter');
    $endpoint->get('[/{id}]', Proveedor::class . ':read');
    $endpoint->post('', Proveedor::class . ':create')->add(soloRoles([1]));
    $endpoint->put('/{id}', Proveedor::class . ':update')->add(soloRoles([1]));
    $endpoint->delete('/{id}', Proveedor::class . ':delete')->add(soloRoles([1]));
  });

  // Cliente no es catálogo: cualquier rol autenticado puede gestionarlo (lo necesita para vender).
  $api->group('/cliente', function (RouteCollectorProxy $endpoint) {
    $endpoint->get('/filter/{offset}/{limit}', Cliente::class . ':filter');
    $endpoint->get('[/{id}]', Cliente::class . ':read');
    $endpoint->post('', Cliente::class . ':create');
    $endpoint->put('/{id}', Cliente::class . ':update');
    $endpoint->delete('/{id}', Cliente::class . ':delete');
  });

  $api->group('/producto', function (RouteCollectorProxy $endpoint) {
    $endpoint->get('/filter/{offset}/{limit}', Producto::class . ':filter');
    $endpoint->get('/stock-bajo', Producto::class . ':stockBajo');
    $endpoint->get('[/{id}]', Producto::class . ':read');
    $endpoint->post('', Producto::class . ':create')->add(soloRoles([1]));
    $endpoint->post('/{id}/entrada', Producto::class . ':entrada')->add(soloRoles([1]));
    $endpoint->put('/{id}', Producto::class . ':update')->add(soloRoles([1]));
    $endpoint->delete('/{id}', Producto::class . ':delete')->add(soloRoles([1]));
  });

  // Venta: cualquier rol autenticado puede vender; solo Admin puede anular una venta ya hecha.
  $api->group('/venta', function (RouteCollectorProxy $endpoint) {
    $endpoint->get('/filter/{offset}/{limit}', Venta::class . ':filter');
    $endpoint->get('/cliente/{id}', Venta::class . ':readCliente');
    $endpoint->get('[/{id}]', Venta::class . ':read');
    $endpoint->post('', Venta::class . ':create');
    $endpoint->put('/{id}/anular', Venta::class . ':anular')->add(soloRoles([1]));
  });
});

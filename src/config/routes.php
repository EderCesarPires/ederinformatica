<?php
/** @var App\Core\Router $router */

use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\DashboardController;
use App\Controllers\OrderController;
use App\Controllers\ServiceController;
use App\Controllers\TaskController;

// Autenticação (públicas)
$router->get('/',          [AuthController::class, 'loginForm']);
$router->get('/login',     [AuthController::class, 'loginForm']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/register',  [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout',   [AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Tarefas do usuário
$router->post('/tasks',               [TaskController::class, 'store']);
$router->post('/tasks/{id}/toggle',   [TaskController::class, 'toggle']);
$router->post('/tasks/{id}/delete',   [TaskController::class, 'destroy']);

// Serviços (CRUD)
$router->get('/services',             [ServiceController::class, 'index']);
$router->get('/services/create',      [ServiceController::class, 'create']);
$router->post('/services',            [ServiceController::class, 'store']);
$router->get('/services/{id}/edit',   [ServiceController::class, 'edit']);
$router->post('/services/{id}',       [ServiceController::class, 'update']);
$router->post('/services/{id}/delete',[ServiceController::class, 'destroy']);

// Clientes (CRUD)
$router->get('/clients',              [ClientController::class, 'index']);
$router->get('/clients/create',       [ClientController::class, 'create']);
$router->post('/clients',             [ClientController::class, 'store']);
$router->get('/clients/{id}/edit',    [ClientController::class, 'edit']);
$router->post('/clients/{id}',        [ClientController::class, 'update']);
$router->post('/clients/{id}/delete', [ClientController::class, 'destroy']);

// Ordens de Serviço (CRUD)
$router->get('/orders',               [OrderController::class, 'index']);
$router->get('/orders/create',        [OrderController::class, 'create']);
$router->post('/orders',              [OrderController::class, 'store']);
$router->get('/orders/{id}',          [OrderController::class, 'show']);
$router->get('/orders/{id}/edit',     [OrderController::class, 'edit']);
$router->post('/orders/{id}',         [OrderController::class, 'update']);
$router->post('/orders/{id}/delete',  [OrderController::class, 'destroy']);

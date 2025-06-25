<?php
//php -S localhost:8000 -t public
//npm run serve

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\db;
use App\Middleware\JwtMiddleware;
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Services\ProductService;

require __DIR__ . '/../vendor/autoload.php';

// Initialize Slim app
$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// CORS Middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Max-Age', '86400');
});

// Global OPTIONS route to handle preflight requests
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Max-Age', '86400')
        ->withStatus(204);
});

// JWT Middleware setup
$secretKey = "my-secret-key"; // Use env variable in production
$unprotectedRoutes = ['/api/auth/register', '/api/auth/login'];
$jwtMiddleware = new JwtMiddleware($secretKey, $unprotectedRoutes);

// --- Unprotected Routes (Register & Login) ---
$app->post('/api/auth/register', function (Request $request, Response $response) use ($secretKey) {
    $registrationData = json_decode($request->getBody()->getContents(), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($registrationData)) {
        $errorBody = json_encode(['error' => 'Invalid JSON data provided.']);
        $response->getBody()->write($errorBody);
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    $database = new db();
    $authController = new AuthController($database, $secretKey);
    $result = $authController->register($registrationData);
    $response->getBody()->write(json_encode($result));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

$app->post('/api/auth/login', function (Request $request, Response $response) use ($secretKey) {
    $credentials = json_decode($request->getBody()->getContents(), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($credentials)) {
        $errorBody = json_encode(['error' => 'Invalid JSON data provided for login.']);
        $response->getBody()->write($errorBody);
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    $database = new db();
    $authController = new AuthController($database, $secretKey);
    $result = $authController->login($credentials);
    $response->getBody()->write(json_encode($result));
    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});

// --- Protected Routes (JWT required) ---
// Example: Product routes (add your own as needed)
$app->group('/api/products', function ($group) {
    $group->get('', [ProductController::class, 'getAll']);
    $group->post('', [ProductController::class, 'create']);
    $group->put('/{id}', [ProductController::class, 'update']);
    $group->delete('/{id}', [ProductController::class, 'delete']);
})->add($jwtMiddleware);

$app->run();
<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class JwtMiddleware implements MiddlewareInterface
{
    private $secretKey;
    private $unprotectedRoutes;

    public function __construct($secretKey, $unprotectedRoutes = [])
    {
        $this->secretKey = $secretKey;
        $this->unprotectedRoutes = $unprotectedRoutes;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $path = $request->getUri()->getPath();
        if (in_array($path, $this->unprotectedRoutes)) {
            return $handler->handle($request);
        }

        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Missing or invalid Authorization header']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $jwt = $matches[1];
        try {
            $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($this->secretKey, 'HS256'));
            // Optionally, you can add the decoded user to the request attributes
            $request = $request->withAttribute('user', $decoded);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Invalid token: ' . $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
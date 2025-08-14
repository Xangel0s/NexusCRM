<?php
namespace App;

class Router {
    private array $routes = [];
    
    public function get(string $path, array $handler): void {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post(string $path, array $handler): void {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function dispatch(): void {
        // Registrar rutas para preview de vendedor
        $this->get('/backdata/seller/preview', [\App\Controllers\BackdataController::class, 'sellerPreview']);
    // Registrar rutas para desarchivar base
        $this->post('/backdata/base/unarchive', [\App\Controllers\BackdataController::class, 'baseUnarchive']);
    // Eliminar base
    $this->post('/backdata/base/delete', [\App\Controllers\BackdataController::class, 'baseDelete']);
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Eliminar trailing slash excepto para la ruta raíz
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        
        if (isset($this->routes[$httpMethod][$path])) {
            [$class, $method] = $this->routes[$httpMethod][$path];
            $controller = new $class();
            $controller->$method();
            return;
        }
        
        // Ruta no encontrada
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
    }
}
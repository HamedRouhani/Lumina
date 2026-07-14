<?php
// app/core/Router.php
class Router
{
    private $routes = [];
    private $notFoundCallback = null;
    
    public function get($pattern, $callback, $middlewares = [])
    {
        $this->addRoute('GET', $pattern, $callback, $middlewares);
    }
    
    public function post($pattern, $callback, $middlewares = [])
    {
        $this->addRoute('POST', $pattern, $callback, $middlewares);
    }
    
    public function put($pattern, $callback, $middlewares = [])
    {
        $this->addRoute('PUT', $pattern, $callback, $middlewares);
    }
    
    public function delete($pattern, $callback, $middlewares = [])
    {
        $this->addRoute('DELETE', $pattern, $callback, $middlewares);
    }
    
    public function patch($pattern, $callback, $middlewares = [])
    {
        $this->addRoute('PATCH', $pattern, $callback, $middlewares);
    }
    
    private function addRoute($method, $pattern, $callback, $middlewares)
    {
        // تبدیل pattern به regex با پشتیبانی از پارامترها
        $pattern = ltrim($pattern, '/');
        
        // جایگزینی پارامترهای {id} با regex
        $regex = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function($matches) {
            return '([^/]+)';
        }, $pattern);
        
        $regex = '#^' . $regex . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // پشتیبانی از PUT/DELETE از طریق _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        // حذف base path /mylumina/ از ابتدای URI
        $basePath = 'mylumina';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = trim($uri, '/');
        
        // اگر URI خالی است، به صفحه اصلی برو
        if ($uri === '') {
            $uri = '';
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            
            if (preg_match($route['regex'], $uri, $matches)) {
                array_shift($matches);
                
                // اجرای middlewareها
                $canProceed = $this->runMiddlewares($route['middlewares']);
                if (!$canProceed) {
                    return;
                }
                
                // فراخوانی کنترلر
                return $this->callCallback($route['callback'], $matches);
            }
        }
        
        // صفحه 404
        $this->notFound();
    }
    
    private function runMiddlewares($middlewares)
    {
        foreach ($middlewares as $middleware) {
            if (strpos($middleware, 'role:') === 0) {
                $roles = explode(',', substr($middleware, 5));
                if (!RoleMiddleware::check($roles)) {
                    return false;
                }
            } elseif ($middleware === 'auth') {
                if (!AuthMiddleware::check()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    private function callCallback($callback, $params = [])
    {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controllerName, $method) = explode('@', $callback);
            $controllerClass = $controllerName;
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $method)) {
                    return call_user_func_array([$controller, $method], $params);
                }
            }
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
        
        $this->notFound();
    }
    
    private function notFound()
    {
        http_response_code(404);
        
        // بررسی درخواست AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'API not found']);
        } else {
            echo "<h1>404 - صفحه یافت نشد</h1>";
            echo "<p>آدرس مورد نظر وجود ندارد.</p>";
        }
        exit;
    }
}
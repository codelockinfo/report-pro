<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $params = [];

    public function get($route, $controller)
    {
        $this->addRoute('GET', $route, $controller);
    }

    public function post($route, $controller)
    {
        $this->addRoute('POST', $route, $controller);
    }

    private function addRoute($method, $route, $controller)
    {
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $route);
        $route = '#^' . $route . '$#';
        $this->routes[$method][$route] = $controller;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_GET['url'] ?? '/';
        
        // Ensure URL starts with / for route matching
        if (!empty($url) && $url[0] !== '/') {
            $url = '/' . $url;
        }
        
        $url = rtrim($url, '/');
        $url = $url ?: '/';

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $controller) {
                if (preg_match($route, $url, $matches)) {
                    $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    list($controllerName, $method) = explode('@', $controller);
                    $controllerClass = "App\\Controllers\\{$controllerName}";
                    
                    if (class_exists($controllerClass)) {
                        try {
                            $controllerInstance = new $controllerClass();
                            if (method_exists($controllerInstance, $method)) {
                                return call_user_func_array([$controllerInstance, $method], $this->params);
                            }
                        } catch (\Exception $e) {
                            // Handle database and other exceptions
                            if (strpos($e->getMessage(), 'Database connection') !== false) {
                                // Database error - let controller handle it
                                throw $e;
                            }
                            http_response_code(500);
                            echo "Error: " . htmlspecialchars($e->getMessage());
                            return;
                        }
                    }
                }
            }
        }

        http_response_code(404);
        echo "404 - Page not found";
    }

    public function getParams()
    {
        return $this->params;
    }
}


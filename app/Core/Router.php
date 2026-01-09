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
        $url = rtrim($url, '/');
        $url = $url ?: '/';

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $controller) {
                if (preg_match($route, $url, $matches)) {
                    $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    list($controllerName, $method) = explode('@', $controller);
                    $controllerClass = "App\\Controllers\\{$controllerName}";
                    
                    if (class_exists($controllerClass)) {
                        $controllerInstance = new $controllerClass();
                        if (method_exists($controllerInstance, $method)) {
                            return call_user_func_array([$controllerInstance, $method], $this->params);
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


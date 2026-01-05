<?php

namespace App\Engine;

use ArgumentCountError;

class Router{
    private array $routes = [
        "GET" => [],
        "POST"=> [],
        "PUT" => [],
    ];

    /**
     * Registers a get request
     * @param string $uri - endpoint for the route
     * @param callable|array $callback - either a function or a [class, method] array to run when the route is requested
     * @return void
     */
    public function get(string $uri, callable|array $callback){
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $uri);
        $pattern = "#^$pattern$#";

        $this->routes["GET"][$pattern] = $callback;
    }

    /**
     * Registers a post request
     * @param string $uri - endpoint for the route
     * @param callable|array $callback - either a function or a [class, method] array to run when the route is requested
     * @return void
     */
    public function post(string $uri, callable|array $callback){
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $uri);
        $pattern = "#^$pattern$#";

        $this->routes["POST"][$pattern] = $callback;
    }

    /**
     * Handles the capture of an incoming request, verifies whether it's registered or not and runs its corresponding callback
     * 
     * @throws ArgumentCountError - when the request's params don't match up to the route's params.
     * @return void
     */
    public function resolve(){
        $method = $_SERVER["REQUEST_METHOD"];
        $uri   = $_SERVER["REQUEST_URI"];

        // trying to extract the actual route (callback, pattern and urlMatches) from the registered $routes
        $route = $this->getUriCorrespondingRoute($method, $uri);

        if($route === null){
            http_response_code(404);
            echo "404 not found.";

            return;
        }

        // trying to extract any params from the request, in case of a get request the dynamic params from the uri are returned
        // in case of a post request the $_POST array values are returned. (no support for json payload yet).
        $params = $this->extractRequestParams($route, $method);

        // running the actual function/method that handles the request.
        try{
            $this->runCallback($route["callback"], $params);
        }catch(ArgumentCountError $e){
            http_response_code(500); 
            echo "missing params";
        }
    }

    /**
     * Given a $uri and a $method (POST, GET), returns the first matching registered route else null.
     * @param string $method - the request's method
     * @param string $uri - the request's uri
     * @return array{callback: mixed, matches: array|null, pattern: mixed|null}|null
     */
    private function getUriCorrespondingRoute(string $method, string $uri){
        foreach($this->routes[$method] as $pattern => $callback){
            if(preg_match($pattern, $uri, $matches)){
                return [
                    "pattern" => $pattern,
                    "callback" => $callback,
                    "matches" => $matches,
                ];
            }
        }

        return null;
    }

    /**
     * Dynamically calls the route's callable (either calls the function or the controller's method) passing with it the params from the URI.
     * @param callable|array $callback - can either be an array of the format [ControllerClass, Method] or a function.
     * @param array $params - array of parameters to be passed to the callback.
     * @return void
     */
    private function runCallback(callable|array $callback, array $params){
        if(is_callable($callback)){
            call_user_func($callback, ...$params);
        }

        if(is_array($callback)){
            $class = $callback[0];
            $method = $callback[1];

            $controller = new $class();
            $controller->$method(...$params);
        }
    }

    /**
     * Since GET and POST requests have different ways to access their params, this method returns the params of each method depending on the
     * current request's method.
     * @param mixed $route
     * @param mixed $method
     * @return array
     */
    private function extractRequestParams($route, $method){
        if($method == "POST"){
            $params = array_values($_POST);
        }else{
            $params = $this->extractParamsFromUrl($route["matches"]);
        }

        return $params;
    }

    /**
     * After finding a matching route, extract the dynamic params of that route as a plain array
     * @param array $matches - the matched params
     * @return array
     */
    private function extractParamsFromUrl(array $matches)
    {
        $params = array_filter(
            $matches,
            fn($key) => !is_numeric($key),
            ARRAY_FILTER_USE_KEY
        );

        return array_values($params);
    }
}
<?php

namespace EasePHP\Handlers;

abstract class RequestHandler {
    
    protected $controllers = array();
    
    /**
     * 
     * @var array
     */
    protected $request;
                
    /**
     * 
     * @var \Bandu\Controllers\RESTful\Controller
     */
    protected $controller;
    
    public function __construct(array $request) {
        $this->request = $request;
        $this->controllers = $this->loadControllers();
        $this->init();
    }
    
    protected function init() {
        if (!array_key_exists('resource', $this->request)) {
            throw new \Exception("Bad Request: No Handler Requested", 401);
        }
        $this->loadController($this->sanitize($this->request['resource']));
    }
    
    abstract protected function loadControllers();
    
    public function handleRequest() {
        return $this->controller->handleRequest();
    }
    
    public function getResponse() {
        return "Request Successful: ".$this->handler;
    }
    
    /**
     * 
     * @param string $resource
     * @throws \Exception
     */
    protected function loadController($resource) {
        if (!array_key_exists($resource, $this->controllers)) {
            throw new \Exception("Resource Not Found: $resource", 404);
        }
        $controllerClass = $this->controllers[$resource];
        $this->controller = new $controllerClass($this->request);
        return $this;
    }
    
    private function sanitize($string) {
        return trim(strtoupper($string));
    }
    
}

<?php

namespace EasePHP\Controllers\RESTful;

use Bandu\Objects\Struct;

abstract class Controller {
    
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const OPTIONS = "OPTIONS";
    
    /**
     * 
     * @var array
     */
    protected $request;
        
    public function __construct(array $request) {
        $this->request = $request;
    }
    
    public final function handleRequest() {
        switch(strtoupper($_SERVER['REQUEST_METHOD'])) {
            case self::GET:
                return $this->handleGet();
            case self::POST:
                return $this->handlePost();
            case self::PUT:
                return $this->handlePut();
            case self::DELETE:
                return $this->handleDelete();
            case self::OPTIONS:
                $method = $this->determineRequestMethod();
                return "Inbound Request: $method";
            default:
                throw new Exception('Bad Request: Unknown Request Method');
        }
    }
    
    abstract public function handleGet();
    
    abstract public function handlePost();
    
    abstract public function handlePut();
    
    abstract public function handleDelete();
    
    abstract protected function getResourceManager();
    
    protected function determineRequestMethod() {
        if (array_key_exists("HTTP_ACCESS_CONTROL_REQUEST_METHOD", $_SERVER)) {
            return strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']);
        } else {
            throw new \Exception('Bad Request: Unknown Request Method');
        }
    }
    
    protected function getRequestData() {
        $payload = file_get_contents('php://input');
        if (!strlen($payload)) {
            throw new \Exception('No Request Data');
        }
        if (!($properties = json_decode($payload, true))) {
            throw new \Exception('Invalid Request Data');
        }
        return $properties;
    }
    
    protected function getResourceFromRequest(Struct $resource) {
        $properties = array();
        foreach (array_keys($resource->getProperties()) as $property) {
            if(array_key_exists($property, $this->request)) {
                $properties[$property] = $this->request[$property];
            }
        }
        $resource->setProperties($properties);
        $this->getResourceManager()->retrieve($resource);
        return $resource;
    }
    
}
<?php

namespace EasePHP\Controllers\RESTful;

use Bandu\Objects\Struct;

abstract class Controller {
    
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    
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
                break;
            case self::POST:
                return $this->handlePost();
                break;
            case self::PUT:
                return $this->handlePut();
                break;
            case self::DELETE:
                return $this->handleDelete();
                break;
            default:
                throw new Exception('Bad Request: Unknown Request Method');
                break;
        }
    }
    
    abstract public function handleGet();
    
    abstract public function handlePost();
    
    abstract public function handlePut();
    
    abstract public function handleDelete();
    
    abstract protected function getResourceManager();
    
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
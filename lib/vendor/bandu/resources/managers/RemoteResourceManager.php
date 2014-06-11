<?php

namespace Bandu\Resources\Managers;

use Bandu\Objects\Resource;

use Bandu\Requests\RESTfulRequest;

abstract class RemoteResourceManager extends RESTfulRequest {
    
    protected $requestQueryParams = array();
    
    public function __construct() {
        $this->setBaseDomain($this->getBaseDomain());
        $this->requestQueryParams['resource'] = $this->getResource();
    }
    
    abstract protected function getBaseDomain();
    
    abstract protected function getResource();
    
    public function create(Resource &$resource) {
        $this->setRequestMethod(self::POST);
        $this->setPayload(json_encode($resource->getProperties(), true));
        $response = $this->send();
        if ($this->requestWasSuccessful()) {
            $resource->setProperties($response);
        } else {
            
        }
    }
    
    public function retrieve(Resource &$resource) {
        $this->setRequestMethod(self::GET);
        $this->requestQueryParams+= $resource->getProperties();
        $response = $this->send();
        if ($this->requestWasSuccessful()) {
            $resource->setProperties($response);
        } else {
            
        }
    }
    
    public function update(Resource &$resource) {
        $this->setRequestMethod(self::PUT);
        $this->requestQueryParams['id'] = $resource->getId();
        $this->setPayload(json_encode($resource->getProperties(), true));
        $response = $this->send();
        if ($this->requestWasSuccessful()) {
            $resource->setProperties($response);
        } else {
            
        }
    }
    
    public function delete(Resource &$resource) {
        $this->setRequestMethod(self::DELETE);
        $this->requestQueryParams['id'] = $resource->getId();
        $response = $this->send();
        if ($this->requestWasSuccessful()) {
            $resource->setProperties($response);
        } else {
            
        }
    }
    
    public function send() {
        $this->prepareRequestHeaders();
        $this->buildRequestURL();
        parent::send();
        return json_decode($this->getResponse(), true);
    }
    
    protected function prepareRequestHeaders() {
        return $this;
    }
    
    protected function buildRequestURL() {
        $this->setRequestQuery(http_build_query($this->requestQueryParams));
    }
        
}
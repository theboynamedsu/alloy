<?php

namespace Bandu\Objects;

use Bandu\Objects\Struct;

abstract class Resource extends Struct {
    
    /**
     * 
     * @var array
     */
    protected $required;
    
    protected function init() {
        parent::init();
        
        $this->internal[]= 'required';

        $this->required = array(
            'create' => $this->getRequiredCreateProperties(),
            'update' => $this->getRequiredUpdateProperties(),
        );
    }
    
    abstract protected function getRequiredCreateProperties();
    abstract protected function getRequiredUpdateProperties();
    
    public function isValid($method) {
        foreach ($this->required[$method] as $property) {
            $getter = 'get'.ucfirst($property);
            if (is_null($this->$getter())) {
                throw new \Exception("Missing Required Argument: $property");
            }
        }
        return true;
    }

}
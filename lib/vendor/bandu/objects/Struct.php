<?php

namespace Bandu\Objects;

abstract class Struct {

    protected $immutable;
    protected $internal;

    public function __construct(array $properties = null) {
        $this->init();
        if (!is_null($properties)) {
            $this->setProperties($properties);
        }
    }

    protected function init() {
        $this->immutable = array ();
        $this->internal = array(
    		'immutable',
    		'internal',
        );
    }

    public function getProperties() {
        $exposedProperties = array();
        foreach (get_object_vars($this) as $key => $value) {
            if (!in_array($key, $this->internal)) {
                $exposedProperties[$key] = $value;
            }
        }
        return $exposedProperties;
    }

    public function setProperties(array $properties) {
        foreach ($properties as $property => $value) {
            $setter = 'set'.ucfirst($property);
            $this->$setter($value);
        }
    }

    public function __get($property) {
        if (!property_exists($this, $property)) {
            throw new \Exception("Unknown Property: $property");
        }
        $getter = 'get'.ucfirst($property);
        return $this->$getter;
    }

    public function __set($property, $value) {
        if (!property_exists($this, $property)) {
            throw new \Exception("Unknown Property: $property");
        }
        if (in_array ($property, $this->immutable)) {
            throw new \Exception("Property cannot be modified");
        }
        $setter = 'set'.ucfirst($property);
        $this->$setter($value);
    }

    public function __call($name, $arguments) {
        switch ($name) {
            case strpos($name, "get") === 0:
                $property = lcfirst(substr($name, 3));
                if (property_exists($this, $property)) {
                    return $this->$property;
                }
                break;
            case strpos($name, "set") === 0:
                $property = lcfirst(substr($name, 3));
                if (property_exists($this, $property)) {
                    if (in_array ($property, $this->immutable)) {
                        throw new \Exception("Property cannot be modified: $property");
                    }
                    $this->$property = $arguments[0];
                }
                break;
            default:
                throw new \Exception("Undefined Method: $name");
                break;
        }
        return $this;
    }
    
    public function render($format = 'JSON') {
        switch ($format) {
            case 'XML':
                return $this->renderXML();
                break;
            case 'JSON':
            default:
                return $this->renderJSON();
                break;
        }
    }
    
    protected function renderXML() {
        throw new \Exception('Format not currently supported');
    }
    
    protected function renderJSON() {
        return json_encode($this->getProperties(), JSON_FORCE_OBJECT);
    }

}

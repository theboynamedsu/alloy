<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bandu
 * Date: 05/09/2013
 * Time: 18:18
 * To change this template use File | Settings | File Templates.
 */

namespace Bandu\Orm;


abstract class ORMComponent {

    protected $name;

    public function __construct($name, array $properties) {
        $this->init();
        $this->name = $name;
        $this->setProperties($properties);
    }

    abstract protected function init();

    abstract protected function getPropertyNames();

    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    protected function setProperties(array $properties) {
        foreach ($this->getPropertyNames() as $key) {
            $setter = 'set'.ucfirst($key);
            if (array_key_exists($key, $properties)) {
                $this->$setter($properties[$key]);
            } else {
                $this->$setter(null);
            }
        }
        return $this;
    }

    protected function isArrayOfStrings(array $array) {
        foreach ($array as $item) {
            if (!is_string($item)) {
                return false;
            }
        }
        return true;
    }

}
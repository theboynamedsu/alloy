<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bandu
 * Date: 05/09/2013
 * Time: 17:37
 * To change this template use File | Settings | File Templates.
 */

namespace Bandu\Orm;


class Property extends ORMComponent {

    protected $name;

    protected $field;
    protected $rules;
    protected $callback;

    protected function init() {
        $this->rules = array();
        $this->callback = array();
    }

    protected function getPropertyNames() {
        return array(
            'field',
            'rules',
            'callback',
        );
    }

    protected function setField($field) {
        if (is_null($field) || !strlen(trim($field))) {
            throw new \Exception("No Field Provided");
        }
        $this->field = $field;
    }

    protected function setRules(array $rules) {
        $this->rules = $rules;
    }

    protected function setCallback(array $callback) {
        if (!$this->isArrayOfStrings($callback)) {
            throw new \Exception('Callbacks must be strings');
        }
        $this->callback = $callback;
    }

}
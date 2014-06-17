<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bandu
 * Date: 05/09/2013
 * Time: 18:15
 * To change this template use File | Settings | File Templates.
 */

namespace Bandu\Orm;


class Association extends ORMComponent {

    protected $name;

    protected $table;
    protected $fields;
    protected $filter;
    protected $searchOptions;
    protected $callback;

    protected $query;
    protected $args;

    protected function init() {
        $this->fields = array();
        $this->filter = array();
        $this->searchOptions = array();
        $this->callback = array();
        $this->args = array();
    }

    public function getPropertyNames() {
        return array(
            'table',
            'fields',
            'filter',
            'searchOptions',
            'callback',
        );
    }

    protected function setTable($table) {
        if (is_null($table) || !strlen(trim($table))) {
            throw new \Exception("No Table Provided");
        }
        $this->table = $table;
    }

    protected function setFields($fields) {
        if (!$this->isArrayOfStrings($fields)) {
            throw new \Exception('Fields must be strings');
        }
        $this->fields = $fields;
    }

    protected function setFilter(array $filter) {
        if (!$this->isArrayOfStrings($filter)) {
            throw new \Exception('Filters must be strings');
        }
        $this->filter = $filter;
    }

    protected function setSearchOptions(array $searchOptions = array()) {
        $this->searchOptions = $searchOptions;
    }

    protected function setCallback(array $callback) {
        if (!$this->isArrayOfStrings($callback)) {
            throw new \Exception('Callbacks must be strings');
        }
        $this->callback = $callback;
    }

    protected function setQuery($query) {
        $this->query = $query;
    }

    protected function setArgs(array $args) {
        if (!$this->isArrayOfStrings($args)) {
            throw new \Exception('Args must be strings');
        }
        $this->args = $args;
    }


}
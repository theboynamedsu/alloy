<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Bandu
 * Date: 05/09/2013
 * Time: 23:03
 * To change this template use File | Settings | File Templates.
 */

namespace Bandu\Orm;


class Collection extends ORMComponent {

    protected $manager;
    protected $filter;

    protected function init() {
        $this->filter = array();
    }

    protected function getPropertyNames() {
        return array(
            'manager',
            'filter',
        );
    }

    protected function setManager($manager) {
        if (is_null($manager) || !strlen(trim($manager))) {
            throw new \Exception('No Resource Manager Provided');
        }
        $this->manager = $manager;
    }

    protected function setFilter($filter) {
        if (!$this->isArrayOfStrings($filter)) {
            throw new \Exception('filters must be string');
        }
        $this->filter = $filter;
    }

}
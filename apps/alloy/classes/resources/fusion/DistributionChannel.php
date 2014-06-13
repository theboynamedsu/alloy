<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Resources\Fusion;

/**
 * Description of DistributionChannel
 *
 * @author Bandu
 */
class DistributionChannel extends \Bandu\Objects\Resource {
    
    protected $id;
    protected $name;
    protected $type;
    protected $status;
    
    protected function getRequiredCreateProperties() {
        $properties = array_keys($this->getProperties());
        unset($properties['id']);
        return array_keys($properties);
    }

    protected function getRequiredUpdateProperties() {
        return array_keys($this->getProperties());
    }
    
}


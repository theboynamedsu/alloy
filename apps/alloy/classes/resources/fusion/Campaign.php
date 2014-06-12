<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Resources\Fusion;

/**
 * Description of Campaign
 *
 * @author Bandu
 */
class Campaign extends \Bandu\Objects\Resource {
    
    protected $id;
    protected $name;
    protected $description;
    protected $status;
    protected $dateCreated;
    protected $createdBy;
    protected $lastUpdated;
    protected $updatedBy;
    
    protected $distributionChannels;
    protected $settings;
    
    protected function getRequiredCreateProperties() {
        return array_keys($this->getProperties());
    }

    protected function getRequiredUpdateProperties() {
        return array_keys($this->getProperties());
    }    
}



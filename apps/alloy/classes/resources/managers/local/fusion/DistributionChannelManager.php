<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Resources\Managers\Local\Fusion;

/**
 * Description of DistributionChannel
 *
 * @author Bandu
 */
class DistributionChannelsManager extends \Bandu\Resources\Managers\DbResourceManager {
    
    protected function getAssociations() {
        return array();
    }

    protected function getCollections() {
        return array();
    }

    protected function getDefaults() {
        return array(
            'table' => 'DistributionChannels',
            'filter' => array(
                'id',
            ),
        );
    }

    protected function getProperties() {
        return array(
            'id' => array(
                'field' => 'id',
                'rules' => array(
                    'READ_ONLY',
                ),
                'callback' => array(),
            ),
            'name' => array(
                'field' => 'name',
                'rules' => array(),
                'callback' => array(),
            ),
            'type' => array(
                'field' => 'type',
                'rules' => array(),
                'callback' => array(),
            ),
            'status' => array(
                'field' => 'status',
                'rules' => array(),
                'callback' => array(),
            ),
        );
    }
    
}


<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Resources\Managers\Local\Fusion;

/**
 * Description of CampaignsManager
 *
 * @author Bandu
 */
class CampaignsManager extends \Bandu\Orm\LocalResourceManager {

    protected function getAssociations() {
        return array(
            'distributionChannels' => array(
                'table' => 'Campaigns__DistributionChannels',
                'fields' => array(
                    'channelId',
                    'dataKey',
                    'dataValue',
                ),
                'filter' => array(
                    'id' => 'campaignId',
                ),
                'callback' => array(),
            ),
            'settings' => array(
                'table' => 'Campaigns__Settings',
                'fields' => array(
                    'dataKey',
                    'dataValue',
                ),
                'filter' => array(
                    'id' => 'campaignId',
                ),
                'callback' => array(),
            ),
        );
    }

    protected function getCollections() {
        return array();
    }

    protected function getDefaults() {
        return array(
            'table' => 'Campaigns',
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
            'description' => array(
                'field' => 'description',
                'rules' => array(),
                'callback' => array(),
            ),
            'status' => array(
                'field' => 'status',
                'rules' => array(),
                'callback' => array(),
            ),
            'dateCreated' => array(
                'field' => 'dateCreated',
                'rules' => array(),
                'callback' => array(
                    'CREATE_ONLY',
                ),
            ),
            'createdBy' => array(
                'field' => 'createdBy',
                'rules' => array(),
                'callback' => array(),
            ),
            'lastUpdated' => array(
                'field' => 'lastUpdated',
                'rules' => array(),
                'callback' => array(),
            ),
            'updatedBy' => array(
                'field' => 'UpdatedBy',
                'rules' => array(),
                'callback' => array(),
            ),
        );
    }

}


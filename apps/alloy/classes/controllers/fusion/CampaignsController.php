<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controllers\Fusion;

/**
 * Description of CampaignsController
 *
 * @author Bandu
 */
class CampaignsController extends \EasePHP\Controllers\RESTful\Controller {
    
    /**
     * 
     * @return \Resources\Managers\Local\Fusion\CampaignsManager
     */
    protected function getResourceManager() {
        $db = new \Bandu\Database\MySQLWrapper(array(
                'server' => 'localhost',
                'user' => 'root',
                'password' => '911DKPrince!',
                'db' => 'test'
        ));
        return new \Resources\Managers\Local\Fusion\CampaignsManager($db);
    }

    public function handleDelete() {
        throw new Exception("Method not supported for this resource");
    }

    public function handleGet() {
        $results = $this->getResourceManager()->find($this->request);
        if (!count($results)) {
            return \json_encode(array());
        }
        $formattedResults = array();
        foreach ($results as $result) {
            $formattedResults[] = $result->getProperties();
        }
        return \json_encode($formattedResults);
    }

    public function handlePost() {
        throw new Exception("Method not supported for this resource");
    }

    public function handlePut() {
        throw new Exception("Method not supported for this resource");
    }
}

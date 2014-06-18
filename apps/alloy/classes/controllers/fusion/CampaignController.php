<?php

namespace Controllers\Fusion;

class CampaignController extends \EasePHP\Controllers\RESTful\Controller {
    
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
    
    public function handleGet() {
        if (!array_key_exists('id', $this->request)) {
            throw new \Exception("Please provide an ID for the requested resource");
        }
        $campaign = new \Resources\Fusion\Campaign(array(
            'id' => $this->request['id'],
        ));
        $this->getResourceManager()->retrieve($campaign);
        return $campaign->render('JSON');
    }
    
    public function handlePost() {
        if (!array_key_exists('id', $this->request)) {
            throw new \Exception("Please provide an ID for the requested resource");
        }
        $properties = $this->getRequestData();
        $campaign = new \Resources\Fusion\Campaign($properties);
        $this->getResourceManager()->create($campaign);
        return $campaign->render('JSON');
    }
    
    public function handlePut() {
        
    }
    
    public function handleDelete() {
        
    }
    
}

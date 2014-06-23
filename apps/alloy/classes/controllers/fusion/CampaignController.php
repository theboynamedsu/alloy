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
        $campaign = $this->getResourceBy(array(
            'id' => $this->request['id'],
        ));
        return $campaign->render('JSON');
    }
    
    public function handlePost() {
        $properties = $this->getRequestData();
        $campaign = new \Resources\Fusion\Campaign($properties);
        $this->getResourceManager()->create($campaign);
        return $campaign->render('JSON');
    }
    
    public function handlePut() {
        if (!array_key_exists('id', $this->request)) {
            throw new \Exception("Please provide an ID for the requested resource");
        }
        $campaign = $this->getResourceBy(array(
            'id' => $this->request['id'],
        ));
        $campaign->setProperties($this->getRequestData());
        $this->getResourceManager()->update($campaign);
        return $campaign->render('JSON');        
    }
    
    public function handleDelete() {
        if (!array_key_exists('id', $this->request)) {
            throw new \Exception("Please provide an ID for the requested resource");
        }
        $campaign = $this->getResourceBy(array(
            'id' => $this->request['id'],
        ));
        $this->getResourceManager()->delete($campaign);
        return "";
    }
    
    /**
     * 
     * @param array $criteria
     * @return \Resources\Fusion\Campaign
     * @throws Exception
     */
    protected function getResourceBy(array $criteria) {
        $results = $this->getResourceManager()->find($criteria);
        if (count($results != 1)) {
            throw new \Exception("Resource not found", 404);
        }
        return $results[0];
    }
    
}

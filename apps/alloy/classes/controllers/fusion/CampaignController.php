<?php

namespace Controllers\Fusion;

class CampaignController extends \EasePHP\Controllers\RESTful\Controller {
    
    protected function getResourceManager() {
        $db = new \Bandu\Database\MySQLWrapper(array(
                'server' => 'localhost',
                'user' => 'root',
                'password' => 'p4$$word!',
                'db' => 'fusion'
        ));
        return new \Resources\Managers\Local\Fusion\CampaignsManager($db);
    }
    
    public function handleGet() {
        $campaign = new \Resources\Fusion\Campaign(array(
            'id' => 1,
        ));
        $settings = array(
            'startDate' => array(
                '_lt' => time(),
            ),
        );
        print_r($this->getResourceManager()->find(array('settings' => $settings)));
        $this->getResourceManager()->retrieve($campaign);
        return $campaign->render('JSON');
    }
    
    public function handlePost() {
        
    }
    
    public function handlePut() {
        
    }
    
    public function handleDelete() {
        
    }
    
}
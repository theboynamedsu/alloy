<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controllers\Fusion;

/**
 * Description of DistributionChannelsController
 *
 * @author Bandu
 */
class DistributionChannelController extends \EasePHP\Controllers\RESTful\Controller {
    
    protected function getResourceManager() {
        $db = new \Bandu\Database\MySQLWrapper(array(
                'server' => 'localhost',
                'user' => 'root',
                'password' => 'p4$$word!',
                'db' => 'fusion'
        ));
        return new \Resources\Managers\Local\Fusion\DistributionChannelsManager($db);
    }

    public function handleDelete() {
        throw new \Exception("Method not supported");
    }

    public function handleGet() {
        $channel = new \Resources\Fusion\DistributionChannel($this->getRequestData());
        $this->getResourceManager()->find($channels);
    }

    public function handlePost() {
        throw new \Exception("Method not supported");
    }

    public function handlePut() {
        throw new \Exception("Method not supported");
    }
}


<?php

namespace EasePHP\Controllers\RESTful;

use EasePHP\Controllers\RESTful\Controller;

abstract class WebController extends Controller {
    
    public function handlePut() {
        throw new \Exception('Method Not Supported');
    }
    
    public function handleDelete() {
        throw new \Exception('Method Not Supported');
    }
    
}

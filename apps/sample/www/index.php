<?php

$system = parse_ini_file("../../../config/system-dev.ini", true);
$app = parse_ini_file("../config/sample-dev.ini", true);

require_once $system['paths']['root'].'/lib/vendor/bandu/database/MySQLWrapper.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/objects/Struct.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/objects/Resource.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/ORMComponent.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/Property.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/Association.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/Collection.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/resources/managers/LocalResourceManager.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/requests/RESTfulRequest.php';
require_once $system['paths']['root'].'/lib/vendor/easephp/handlers/RequestHandler.php';
require_once $system['paths']['root'].'/lib/vendor/easephp/controllers/restful/Controller.php';

$requestHandler = new EasePHP\Handlers\RequestHandler($app['controllers'], array('resource' => 'sample'));
echo $requestHandler->handleRequest();

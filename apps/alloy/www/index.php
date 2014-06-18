<?php

if (!isset($_SERVER) || !array_key_exists('env', $_SERVER)) {
    $_SERVER['env'] = 'live';
}

$system = parse_ini_file(sprintf("../../../config/system-%s.ini", $_SERVER['env']), true);
$app = parse_ini_file(sprintf("../config/sample-%s.ini", $_SERVER['env']), true);

require_once $system['paths']['root'].'/lib/vendor/bandu/database/MySQLWrapper.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/objects/Struct.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/objects/Resource.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/ORMComponent.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/Property.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/Association.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/orm/Collection.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/resources/managers/DbResourceManager.php';
require_once $system['paths']['root'].'/lib/vendor/bandu/requests/RESTfulRequest.php';
require_once $system['paths']['root'].'/lib/vendor/easephp/handlers/RequestHandler.php';
require_once $system['paths']['root'].'/lib/vendor/easephp/controllers/restful/Controller.php';

require_once $system['paths']['apps'].DIRECTORY_SEPARATOR.$app['paths']['controllers'].'/fusion/CampaignController.php';
require_once $system['paths']['apps'].DIRECTORY_SEPARATOR.$app['paths']['controllers'].'/fusion/CampaignsController.php';
require_once $system['paths']['apps'].DIRECTORY_SEPARATOR.$app['paths']['classes'].'/resources/fusion/Campaign.php';
require_once $system['paths']['apps'].DIRECTORY_SEPARATOR.$app['paths']['classes'].'/resources/managers/local/fusion/CampaignsManager.php';

$requestHandler = new EasePHP\Handlers\RequestHandler($app['controllers'], $_GET);
header('Content-Type: application/json');
echo $requestHandler->handleRequest();

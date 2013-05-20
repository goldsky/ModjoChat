<?php

header("Content-Type: application/json");
usleep(rand(0, 500000));
$range = '';
if (isset($_SERVER["HTTP_RANGE"])) {
    $range = $_SERVER["HTTP_RANGE"];
} elseif (isset($_SERVER["HTTP_X_RANGE"])) {
    $range = $_SERVER["HTTP_X_RANGE"];
}

$configs = array();

if ($range !== '') {
    preg_match('/(\d+)(\d+)*/', $_SERVER["HTTP_RANGE"], $matches);

    $start = intval($matches[1]);
    $end = intval($matches[2]);
    if ($end > 20) {
        $end = 20;
    }
} else {
    $start = 0;
    $end = 20;
}

foreach ($_GET as $k => $v) {
    if ($k === 'action')
        continue;
    $scriptProperties[$k] = $v;
}
$scriptProperties['start'] = $start !== 0 ?
        intval($start) :
        (isset($scriptProperties['start']) ? intval($scriptProperties['start']) : 0);
$scriptProperties['limit'] = $end !== 0 ?
        intval($end) - intval($start) :
        (isset($scriptProperties['count']) ? intval($scriptProperties['count']) : 20);

if (!empty($scriptProperties['sort'])) {
    $matches = array();
    preg_match('/^-{1}/', $scriptProperties['sort'], $matches);
    $scriptProperties['sort'] = ltrim($scriptProperties['sort'], '-+ ');
    $scriptProperties['dir'] = isset($matches[0]) && $matches[0] === '-' ? 'DESC' : 'ASC';
} else {
    $scriptProperties['sort'] = 'id';
    $scriptProperties['dir'] = 'ASC';
}

$defaultChatCorePath = $modx->getOption('core_path') . 'components/modjochat/';
$chatCorePath = $modx->getOption('modjochat.core_path', null, $defaultChatCorePath);
$chat = $modx->getService('modjochat', 'ModjoChat', $chatCorePath . 'models/');
if (!($chat instanceof ModjoChat)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '!($chat instanceof modjoChat)');
    return '';
}

$scriptProperties['processorsPath'] = $chat->configs['processorsPath'];

require $modx->modjochat->configs['basePath'] . 'vendors/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
if (!$app)
    return false;

$app->get("/", function() use($app, $modx, $scriptProperties) {
            $response = $modx->runProcessor('web/messages/getlist', $scriptProperties, array(
                'processors_path' => $scriptProperties['processorsPath']
                    ));
            if (!isset($response->response)) {
                $error = $response->getMessage();
                return $error;
            }
            $responseJson = $response->getResponse();
            $responseArray = json_decode($responseJson, TRUE);

            $messages = array();
            foreach ($responseArray['results'] as $v) {
                $v['action'] = '';
                $messages[] = $v;
            }

            $total = $responseArray['total'];
            //$app->response()->header('Content-Range', 'item ' . $configs['start'] . '-' . $configs['limit'] . '/' . $total);
            header('Content-Range: item ' . $scriptProperties['start'] . '-' . $scriptProperties['limit'] . '/' . $total);
            echo json_encode($messages);
        });

$app->get('/messages', function () {
            $test = array('This is a GET route');
            echo json_encode($test);
        });

$app->run();
return;
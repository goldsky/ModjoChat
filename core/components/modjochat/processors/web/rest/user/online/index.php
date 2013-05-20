<?php

header("Content-Type: application/json");
usleep(rand(0, 500000));
$range = '';
if (isset($_SERVER["HTTP_RANGE"])) {
    $range = $_SERVER["HTTP_RANGE"];
} elseif (isset($_SERVER["HTTP_X_RANGE"])) {
    $range = $_SERVER["HTTP_X_RANGE"];
}

if ($range !== '') {
    preg_match('/(\d+)-(\d+)*/', $range, $matches);

    $start = intval($matches[1]);
    $end = intval($matches[2]);
//    if ($end == '' || $end == null) {
//        $end = 21;
//    }
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
        (isset($scriptProperties['count']) ? intval($scriptProperties['count']) : 0);

if (!empty($scriptProperties['sort'])) {
    $matches = array();
    preg_match('/^-{1}/', $scriptProperties['sort'], $matches);
    $scriptProperties['sort'] = ltrim($scriptProperties['sort'], '-+ ');
    $scriptProperties['dir'] = isset($matches[0]) && $matches[0] === '-' ? 'DESC' : 'ASC';
} else {
    $scriptProperties['sort'] = 'user_name';
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

$_SERVER['QUERY_STRING'] = str_replace('&amp;', '&', $_SERVER['QUERY_STRING']);
require $modx->modjochat->configs['basePath'] . 'vendors/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/', function () use($modx, $app, $scriptProperties) {
            $response = $modx->runProcessor('web/users/online.getlist', $scriptProperties, array(
                'processors_path' => $scriptProperties['processorsPath']
            ));
            if (!isset($response->response)) {
                $error = $response->getMessage();
                return $error;
            }
            $responseJson = $response->getResponse();
            $responseArray = json_decode($responseJson, TRUE);

//$modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
//$modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
//$modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $responseArray ' . print_r($responseArray,1));

            $users = array();
            foreach ($responseArray['results'] as $v) {
                $v['action'] = '';
                $users[] = $v;
            }

//$modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $users ' . print_r($users,1));

            $total = $responseArray['total'];
            if ($scriptProperties['limit'] > $total ||
                    $scriptProperties['limit'] === 0) {
                $scriptProperties['limit'] = $total;
            }
            $app->response()->header('Content-Range', 'item ' . $scriptProperties['start'] . '-' . $scriptProperties['limit'] . '/' . $total);
            //header('Content-Range: item ' . $scriptProperties['start'] . '-' . $scriptProperties['limit'] . '/' . $total);
            echo json_encode($users);
        });

// POST route
$app->post('/', function () use($modx, $app, $scriptProperties) {
            $modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $_POST ' . print_r($_POST, 1));
            $modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $scriptProperties ' . print_r($scriptProperties, 1));

            echo json_encode(array());
        });

// PUT route
$app->put('/put', function () use($modx, $app, $scriptProperties) {
            $modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $_POST ' . print_r($_POST, 1));
            $modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $scriptProperties ' . print_r($scriptProperties, 1));

            echo json_encode(array('This is a PUT route'));
        });

// DELETE route
$app->delete('/delete', function () use($modx, $app, $scriptProperties) {
            $modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $_GET ' . print_r($_GET, 1));

            echo json_encode(array('This is a DELETE route'));
        });

//$modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
//$modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
//$modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $_SERVER[\'REQUEST_METHOD\'] ' . $_SERVER['REQUEST_METHOD']);
//$modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $_REQUEST ' . print_r($_REQUEST, 1));

$app->run();
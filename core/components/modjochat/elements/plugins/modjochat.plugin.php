<?php
// OnWebPageInit
// OnWebLogin
// OnWebLogout

$defaultChatCorePath = $modx->getOption('core_path') . 'components/modjochat/';
$chatCorePath = $modx->getOption('modjochat.core_path', null, $defaultChatCorePath);
$chat = $modx->getService('modjochat', 'ModjoChat', $chatCorePath . 'models/', $scriptProperties);
if (!($chat instanceof ModjoChat)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Plugin is !($chat instanceof modjoChat)');
    return '';
}

$userId = $modx->user->get('id');
$ip = $chat->getClientIp();
if ($userId !== 0) {
    $userName = $modx->user->get('username');
} elseif (isset($_COOKIE['modjochat_username']) &&
        strlen($_COOKIE['modjochat_username']) > 1) {
    $userName = $_COOKIE['modjochat_username'];
} else {
    return;
}

$processorsPath = $chat->configs['processorsPath'];

$eventName = $modx->event->name;
switch ($eventName) {
    case 'OnWebPageInit':
        break;
    case 'OnWebLogin':
        break;
    case 'OnWebLogout':
        // cron job
        $response = $modx->runProcessor('web/users/online.remove', array(
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_name' => $userName
                ), array(
            'processors_path' => $processorsPath
        ));
        if (!isset($response->response)) {
            $error = $response->getMessage();
            $modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
            $modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': $error ' . $error);
            return;
        }
//        $responseArray = $response->getResponse();
        setcookie('modjochat_starttime', '', time() - 3600);
        break;
}

return;
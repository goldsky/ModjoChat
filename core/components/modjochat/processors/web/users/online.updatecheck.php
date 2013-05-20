<?php

$defaultChatCorePath = $modx->getOption('core_path') . 'components/modjochat/';
$chatCorePath = $modx->getOption('modjochat.core_path', null, $defaultChatCorePath);
$chat = $modx->getService('modjochat', 'ModjoChat', $chatCorePath . 'models/');
if (!($chat instanceof ModjoChat)) {
    $modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
    $modx->log(modX::LOG_LEVEL_ERROR, '!($chat instanceof modjoChat)');
    return '';
}

$userId = $modx->user->get('id');
$ip = $chat->getClientIp();
if ($userId !== 0) {
    $userName = $modx->user->get('username');
} elseif (isset($scriptProperties['user_name']) &&
        strlen($scriptProperties['user_name']) > 1) {
    $userName = $scriptProperties['user_name'];
} else {
    return;
}

$processorsPath = $chat->configs['processorsPath'];

$params = array(
    'user_id' => $userId,
    'ip_address' => $ip,
    'user_name' => $userName,
    'channel_id' => $scriptProperties['channel_id']
);

$response = $modx->runProcessor('web/users/online.get', $params, array(
    'processors_path' => $processorsPath
        ));
if (!isset($response->response)) {
    $error = $response->getMessage();
    return $error;
}
$responseArray = $response->getResponse();
$isExisted = $responseArray['success'];
unset($response, $responseArray);

if ($isExisted) {
    $response = $modx->runProcessor('web/users/online.update', $params, array(
        'processors_path' => $processorsPath
    ));
    if (!isset($response->response)) {
        $error = $response->getMessage();
        return $error;
    }
    $responseArray = $response->getResponse();
} else {
    $response = $modx->runProcessor('web/users/online.create', $params, array(
        'processors_path' => $processorsPath
    ));
    if (!isset($response->response)) {
        $error = $response->getMessage();
        return $error;
    }
    $responseArray = $response->getResponse();
}

$output = json_encode($responseArray);
return $output;
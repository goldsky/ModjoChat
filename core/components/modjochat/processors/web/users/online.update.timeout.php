<?php

$defaultChatCorePath = $modx->getOption('core_path') . 'components/modjochat/';
$chatCorePath = $modx->getOption('modjochat.core_path', null, $defaultChatCorePath);
$chat = $modx->getService('modjochat', 'ModjoChat', $chatCorePath . 'models/');
if (!($chat instanceof ModjoChat)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '!($chat instanceof modjoChat)');
    return '';
}

$props = array();
$props['user_id'] = $modx->user->get('id');
if ($props['user_id'] !== 0) {
    $props['user_name'] = $modx->user->get('username');
} elseif (isset($_COOKIE['modjochat_username']) &&
        strlen($_COOKIE['modjochat_username']) > 1) {
    $props['user_name'] = $_COOKIE['modjochat_username'];
}

$props['ip_address'] = $chat->getClientIp();

$settingTimeOut = $chat->getSetting('user.timeout');
$timeout = time() + $settingTimeOut;
$props['timeout'] = $timeout;

$c = $modx->newQuery('OnlineUsers');
$c->where(array(
    'user_name' => $props['user_name']
));

$collection = $modx->getCollection('OnlineUsers', $c);

$output = array(
    'success' => array(),
    'message' => array(),
    'total' => array(),
    'errors' => array(),
    'object' => array(),
);
foreach ($collection as $item) {
    $props['channel_id'] = $item->get('channel_id');
    
    $response = $chat->updateUserTimeout($props);
    
    $output['success'] = array_merge($output['success'], array($response['success']));
    $output['message'] = array_merge($output['message'], array($response['message']));
    $output['total'] = array_merge($output['total'], array($response['total']));
    $output['errors'] = array_merge($output['errors'], array($response['errors']));
    $output['object'] = array_merge($output['object'], array($response['object']));
}

return $output;
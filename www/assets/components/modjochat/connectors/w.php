<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * Ajax Connector
 *
 * @package modjochat
 */
$validActions = array(
    'web/settings/getlist',
    'web/settings/get',
    'web/channels/getlist',
    'web/users/get',
    'web/users/online.get',
    'web/users/online.create',
    'web/users/online.getrestlist',
    'web/users/online.remove',
    'web/users/online.updatecheck',
    'web/rest/user/online/index',
    'web/messages/create',
//    'web/messages/getrestlist',
    'web/messages/getlist',
    'web/actions/getlist',
    'web/rest/message/index',
    'web/users/online.update.timeout',
);
if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $validActions)) {
    @session_cache_limiter('public');
    define('MODX_REQP', false);
}

define('MODX_API_MODE', true);
// this goes to the www.domain.name/index.php
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

$modjochatCorePath = $modx->getOption('modjochat.core_path', null, $modx->getOption('core_path') . 'components/modjochat/');
require_once $modjochatCorePath . 'models/modjochat.class.php';
$modx->modjochat = new ModjoChat($modx);
if (!($modx->modjochat instanceof ModjoChat)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '!($chat instanceof modjoChat)');
    die();
}

$modx->lexicon->load('modjochat:web');

if (in_array($_REQUEST['action'], $validActions)) {
    $version = $modx->getVersionData();
    if (version_compare($version['full_version'], '2.1.1-pl') >= 0) {
        if ($modx->user->hasSessionContext($modx->context->get('key'))) {
            $_SERVER['HTTP_MODAUTH'] = $_SESSION["modx.{$modx->context->get('key')}.user.token"];
        } else {
            $_SESSION["modx.{$modx->context->get('key')}.user.token"] = 0;
            $_SERVER['HTTP_MODAUTH'] = 0;
        }
    } else {
        $_SERVER['HTTP_MODAUTH'] = $modx->site_id;
    }
    $_REQUEST['HTTP_MODAUTH'] = $_SERVER['HTTP_MODAUTH'];
}

// try this
// echo $modx->user->get('id');

/* handle request */
$connectorRequestClass = $modx->getOption('modConnectorRequest.class',null,'modConnectorRequest');
$modx->config['modRequest.class'] = $connectorRequestClass;
$path = $modx->getOption('processorsPath', $modx->modjochat->configs, $modjochatCorePath . 'processors/');
$modx->getRequest();
$modx->request->sanitizeRequest();
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));
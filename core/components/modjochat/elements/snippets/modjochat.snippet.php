<?php

$phs = array();
$phs['domId'] = $modx->getOption('domId', $scriptProperties, 'modjochat-wrapper');
$tpl = $modx->getOption('tpl', $scriptProperties, '<div id="' . $phs['domId'] . '"></div>');
$cssFile = $modx->getOption('cssFile', $scriptProperties, 'assets/components/modjochat/css/modjochat.css');
$jsFile = $modx->getOption('jsFile', $scriptProperties, 'assets/components/modjochat/js/modjochat.js');
$dojoJsFile = $modx->getOption('dojoJsFile', $scriptProperties, 'assets/vendors/dtk/dojo/dojo.js');
$connectionType = $modx->getOption('modjochat.connection_type', $scriptProperties, 'ajax');
$ajaxTimer = $modx->getOption('modjochat.ajax_timer', $scriptProperties, 3000);
$dojoTheme = $modx->getOption('modjochat.dojo_theme', $scriptProperties, 'claro');

$requireAuth = $modx->getOption('requireAuth', $scriptProperties, false);
if ($requireAuth && !$modx->user->isAuthenticated())
    return;

$allowGuestLogin = $modx->getOption('allowGuestLogin', $scriptProperties, true);

$defaultChatCorePath = $modx->getOption('core_path') . 'components/modjochat/';
$chatCorePath = $modx->getOption('modjochat.core_path', null, $defaultChatCorePath);
$chat = $modx->getService('modjochat', 'ModjoChat', $chatCorePath . 'models/', $scriptProperties);
if (!($chat instanceof ModjoChat)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '!($chat instanceof modjoChat)');
    return '';
}

$chat->setConfigs($scriptProperties);
$chat->updateUserTimeout(array('channel_id' => 0));
$chat->removeTimeoutUsers();

if (!empty($cssFile)) {
    $modx->regClientCSS($cssFile);
}

if (!empty($dojoJsFile)) {
    $modx->regClientCSS($dojoJsFile . '/../../dijit/themes/' . $dojoTheme . '/' . $dojoTheme . '.css');
//    $modx->regClientCSS($dojoJsFile . '/../../gridx/resources/claro/Gridx.css');
    $modx->regClientCSS($dojoJsFile . '/../../dgrid/css/skins/' . $dojoTheme . '.css');
    $modjochatVars = array(
        'domHolders' => array(
            'wrapper' => $phs['domId']
        ),
        'conn' => $chat->configs['connectorUrl'] . 'w.php',
        'imageLoader' => $chat->configs['assetsUrl'] . 'img/ajax-loader.gif',
        'version' => $chat->getVersion(),
        'connectionType' => $connectionType,
        'dojoTheme' => $dojoTheme
    );
    if ($allowGuestLogin) {
        $modjochatVars = array_merge($modjochatVars, array(
            'allowGuestLogin' => $allowGuestLogin
        ));
    }
    if ($connectionType === 'ajax') {
        $modjochatVars = array_merge($modjochatVars, array(
            'ajaxTimer' => $ajaxTimer
        ));
    }
    $startupScript = 'var modjochat = ' . json_encode($modjochatVars) . ';';
    $modx->regClientStartupHTMLBlock('
    <script type="text/javascript">
        ' . $startupScript . '
        dojoConfig = {
            async: true
        };
    </script>
');
    $modx->regClientStartupScript($dojoJsFile);
}

if (!empty($jsFile)) {
    $modx->regClientScript($jsFile);
}

if ($modx->user->isAuthenticated()) {
    if (!isset($_COOKIE['modjochat_starttime'])) {
        setcookie('modjochat_starttime', time(), time() + 60 * 60 * 24 * 1);
    }
}

$output = $chat->parseTpl($tpl, $phs);
return $output;
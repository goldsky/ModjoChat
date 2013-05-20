<?php

class ModjoChat {

    const ModjoVersion = '1.0.0-dev';

    public $modx;
    public $configs;

    /**
     * constructor
     * @param   modX    $modx
     * @param   array   $configs    parameters
     */
    public function __construct(modX $modx, $configs = array()) {
        $this->modx = &$modx;
        $configs = is_array($configs) ? $configs : array();
        $basePath = $this->modx->getOption('modjochat.core_path'
                , $configs
                , $this->modx->getOption('core_path') . 'components/modjochat/'
        );
        $assetsUrl = $this->modx->getOption('modjochat.assets_url'
                , $configs
                , $this->modx->getOption('assets_url') . 'components/modjochat/'
        );

        $this->configs = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelsPath' => $basePath . 'models/',
            'processorsPath' => $basePath . 'processors/',
            'chunksPath' => $basePath . 'elements/chunks/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'connectors/'
                ), $configs);

        $this->modx->lexicon->load('modjochat:default');
        $this->modx->addPackage('modjochat', $this->configs['modelsPath'], 'modx_modjochat_');
//        $this->modx->setDebug(true);
    }

    public function getVersion() {
        return self::ModjoVersion;
    }

    /**
     * Set class configuration exclusively for multiple snippet calls
     * @param   array   $configs    snippet's parameters
     */
    public function setConfigs(array $configs = array()) {
        $this->configs = array_merge($this->configs, $configs);
    }

    /**
     * Define individual config for the class
     * @param   string  $key    array's key
     * @param   string  $val    array's value
     */
    public function setConfig($key, $val) {
        $this->configs[$key] = $val;
    }

    /**
     * Parsing template
     * @param   string  $tpl    @BINDINGs options
     * @param   array   $phs    placeholders
     * @return  string  parsed output
     * @link    http://forums.modx.com/thread/74071/help-with-getchunk-and-modx-speed-please?page=2#dis-post-413789
     */
    public function parseTpl($tpl, array $phs = array()) {
        $output = '';
        if (preg_match('/^(@CODE|@INLINE)/i', $tpl)) {
            $tplString = preg_replace('/^(@CODE|@INLINE)/i', '', $tpl);
            // tricks @CODE: / @INLINE:
            $tplString = ltrim($tplString, ':');
            $tplString = trim($tplString);
            $output = $this->parseTplCode($tplString, $phs);
        } elseif (preg_match('/^@FILE/i', $tpl)) {
            $tplFile = preg_replace('/^@FILE/i', '', $tpl);
            // tricks @FILE:
            $tplFile = ltrim($tplFile, ':');
            $tplFile = trim($tplFile);
            $tplFile = $this->replacePropPhs($tplFile);
            try {
                $output = $this->parseTplFile($tplFile, $phs);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
        // ignore @CHUNK / @CHUNK: / empty @BINDING
        else {
            $tplChunk = preg_replace('/^@CHUNK/i', '', $tpl);
            // tricks @CHUNK:
            $tplChunk = ltrim($tpl, ':');
            $tplChunk = trim($tpl);

            $chunk = $this->modx->getObject('modChunk', array('name' => $tplChunk), true);
            if (empty($chunk)) {
                // try to use @splittingred's fallback
                $f = $this->configs['chunksPath'] . strtolower($tplChunk) . '.chunk.tpl';
                try {
                    $output = $this->parseTplFile($f, $phs);
                } catch (Exception $e) {
                    $output = $e->getMessage();
                    return 'Chunk: ' . $tplChunk . ' is not found, neither the file ' . $output;
                }
            } else {
                $output = $this->modx->getChunk($tpl, $phs);
            }
        }

        return $output;
    }

    /**
     * Parsing inline template code
     * @param   string  $code   HTML with tags
     * @param   array   $phs    placeholders
     * @return  string  parsed output
     */
    public function parseTplCode($code, array $phs = array()) {
        $chunk = $this->modx->newObject('modChunk');
        $chunk->setContent($code);
        $chunk->setCacheable(false);
        $phs = $this->replacePropPhs($phs);
        return $chunk->process($phs);
    }

    /**
     * Parsing file based template
     * @param   string  $file   file path
     * @param   array   $phs    placeholders
     * @return  string  parsed output
     * @throws  Exception if file is not found
     */
    public function parseTplFile($file, array $phs = array()) {
        if (!file_exists($file)) {
            throw new Exception('File: ' . $file . ' is not found.');
        }
        $o = file_get_contents($file);
        $chunk = $this->modx->newObject('modChunk');

        // just to create a name for the modChunk object.
        $name = strtolower(basename($file));
        $name = rtrim($name, '.tpl');
        $name = rtrim($name, '.chunk');
        $chunk->set('name', $name);

        $chunk->setCacheable(false);
        $chunk->setContent($o);
        $output = $chunk->process($phs);

        return $output;
    }

    /**
     * Replace the property's placeholders
     * @param   string|array    $subject    Property
     * @return  array           The replaced results
     */
    public function replacePropPhs($subject) {
        $pattern = array(
            '/\{core_path\}/',
            '/\{base_path\}/',
            '/\{assets_url\}/',
            '/\{filemanager_path\}/',
            '/\[\[\+\+core_path\]\]/',
            '/\[\[\+\+base_path\]\]/'
        );
        $replacement = array(
            $this->modx->getOption('core_path'),
            $this->modx->getOption('base_path'),
            $this->modx->getOption('assets_url'),
            $this->modx->getOption('filemanager_path'),
            $this->modx->getOption('core_path'),
            $this->modx->getOption('base_path')
        );
        if (is_array($subject)) {
            $parsedString = array();
            foreach ($subject as $k => $s) {
                if (is_array($s)) {
                    $s = $this->replacePropPhs($s);
                }
                $parsedString[$k] = preg_replace($pattern, $replacement, $s);
            }
            return $parsedString;
        } else {
            return preg_replace($pattern, $replacement, $subject);
        }
    }

    public function getContents() {
        $contents = array(
            'domId' => $this->configs['domId']
        );
        return $contents;
    }

    public function getClientIp() {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function getSetting($settingKey) {
        $setting = $this->modx->getObject('Settings', array(
            'key' => $settingKey
        ));

        return $setting->get('value');
    }

    public function removeTimeoutUsers() {
        $response = $this->modx->runProcessor('web/users/online.remove.timeout', array(), array(
            'processors_path' => $this->configs['processorsPath']
        ));
        if (!isset($response->response)) {
            $error = $response->getMessage();
            return $error;
        }
        $responseArray = $response->getResponse();

        return json_decode($responseArray, 1);
    }

    public function updateUserTimeout($props = array()) {
        $userId = isset($props['user_id']) ? $props['user_id'] : $this->modx->user->get('id');
        $props['user_id'] = (int) $userId;
        if (!isset($props['user_name'])) {
            if ($props['user_id'] !== 0) {
                $props['user_name'] = $this->modx->user->get('username');
            } elseif (isset($_COOKIE['modjochat_username']) &&
                    strlen($_COOKIE['modjochat_username']) > 1) {
                $props['user_name'] = $_COOKIE['modjochat_username'];
            } else {
                return;
            }
        }

        $props['ip_address'] = $this->getClientIp();
        $settingTimeOut = $this->getSetting('user.timeout');
        $props['timeout'] = time() + $settingTimeOut;

        $processorsPath = $this->configs['processorsPath'];
        $props['channel_id'] = isset($props['channel_id']) ? $props['channel_id'] : 0;
        // check existed names
        $response = $this->modx->runProcessor('web/users/online.getlist', array(
            'user_id' => $props['user_id'],
            'ip_address' => $props['ip_address'],
            'user_name' => $props['user_name'],
            'channel_id' => $props['channel_id'],
            'timeout' => 0 // trick to get everything regardless the timeout
                ), array(
            'processors_path' => $processorsPath
        ));
        if (!isset($response->response)) {
            $error = $response->getMessage();
            return $error;
        }
        $responseArray = $response->getResponse();
        $responseArray = json_decode($responseArray, 1);
        $isExisted = $responseArray['total'] > 0;
        unset($response, $responseArray);

        $params = array(
            'user_id' => $props['user_id'],
            'user_name' => $props['user_name'],
            'ip_address' => $props['ip_address'],
            'channel_id' => $props['channel_id'],
        );
            
        if (!empty($isExisted)) {
            $response = $this->modx->runProcessor('web/users/online.update', $params, array(
                'processors_path' => $processorsPath
            ));
            if (!isset($response->response)) {
                $error = $response->getMessage();
                return $error;
            }
            $responseArray = $response->getResponse();
        } else {
            $response = $this->modx->runProcessor('web/users/online.create', $params, array(
                'processors_path' => $processorsPath
            ));
            if (!isset($response->response)) {
                $error = $response->getMessage();
                return $error;
            }
            $responseArray = $response->getResponse();
        }

        return $responseArray;
    }

    public function escapeString($string) {
        return $string;
    }

    public function unescapeString($string) {
        return $string;
    }

    /**
     * Convert under_score type array's keys to camelCase type array's keys
     * @param   array   $array          array to convert
     * @param   array   $arrayHolder    parent array holder for recursive array
     * @return  array   camelCase array
     */
    public function camelCaseKeys($array, $arrayHolder = array()) {
        $camelCaseArray = !empty($arrayHolder) ? $arrayHolder : array();
        foreach ($array as $key => $val) {
            $newKey = @explode('_', $key);
            array_walk($newKey, create_function('&$v', '$v = ucwords($v);'));
            $newKey = @implode('', $newKey);
            $newKey{0} = strtolower($newKey{0});
            if (!is_array($val)) {
                $camelCaseArray[$newKey] = $val;
            } else {
                $camelCaseArray[$newKey] = $this->camelCaseKeys($val, $camelCaseArray[$newKey]);
            }
        }
        return $camelCaseArray;
    }

    /**
     * Convert camelCase type array's keys to under_score+lowercase type array's keys
     * @param   array   $array          array to convert
     * @param   array   $arrayHolder    parent array holder for recursive array
     * @return  array   under_score array
     */
    public function underscoreKeys($array, $arrayHolder = array()) {
        $underscoreArray = !empty($arrayHolder) ? $arrayHolder : array();
        foreach ($array as $key => $val) {
            $newKey = preg_replace('/[A-Z]/', '_$0', $key);
            $newKey = strtolower($newKey);
            $newKey = ltrim($newKey, '_');
            if (!is_array($val)) {
                $underscoreArray[$newKey] = $val;
            } else {
                $underscoreArray[$newKey] = $this->underscoreKeys($val, $underscoreArray[$newKey]);
            }
        }
        return $underscoreArray;
    }

    /**
     * Convert camelCase type array's values to under_score+lowercase type array's values
     * @param   mixed   $mixed          array|string to convert
     * @param   array   $arrayHolder    parent array holder for recursive array
     * @return  mixed   under_score array|string
     */
    public function underscoreValues($mixed, $arrayHolder = array()) {
        $underscoreArray = !empty($arrayHolder) ? $arrayHolder : array();
        if (!is_array($mixed)) {
            $newVal = preg_replace('/[A-Z]/', '_$0', $mixed);
            $newVal = strtolower($newVal);
            $newVal = ltrim($newVal, '_');
            return $newVal;
        } else {
            foreach ($mixed as $key => $val) {
                $underscoreArray[$key] = $this->underscoreValues($val, $underscoreArray[$key]);
            }
            return $underscoreArray;
        }
    }

}
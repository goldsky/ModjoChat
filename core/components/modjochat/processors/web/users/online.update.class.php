<?php

class WebUsersOnlineUpdateProcessor extends modObjectUpdateProcessor {

    public $classKey = 'OnlineUsers';
    public $languageTopics = array('context');
    public $objectType = 'modjochat.WebUsersOnlineUpdate';
    public $primaryKeyField = 'user_name';

    public function initialize() {
        $primaryKey = $this->getProperty($this->primaryKeyField, false);
        if (empty($primaryKey))
            return $this->modx->lexicon($this->objectType . '_err_ns');

        $props = $this->getProperties();
        $params = array(
            'user_id' => $props['user_id'],
            'ip_address' => $props['ip_address'],
            'user_name' => $props['user_name'],
            'channel_id' => $props['channel_id']
        );
        
        $this->object = $this->modx->getObject($this->classKey, $params);

        if (empty($this->object)) {
            return $this->modx->lexicon($this->objectType . '_err_nfs', $params);
        }

        if ($this->checkSavePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('save')) {
            return $this->modx->lexicon('access_denied');
        }

        $settingTimeOut = $this->modx->modjochat->getSetting('user.timeout');
        $timeOut = time() + $settingTimeOut;
        $this->setProperty('timeout', $timeOut);

        return true;
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        $props = $this->getProperties();
        
        $params = array(
            'user_id' => intval($props['user_id']),
            'ip_address' => $props['ip_address'],
            'user_name' => $props['user_name'],
            'channel_id' => $props['channel_id'],
            'timeout' => $props['timeout']
        );
        
        $this->object->fromArray($params, '', TRUE); // <== IMPORTANT for multiple Primary Keys!
        
        /* run object validation */
        if (!$this->object->validate()) {
            /** @var modValidator $validator */
            $validator = $this->object->getValidator();
            if ($validator->hasMessages()) {
                foreach ($validator->getMessages() as $message) {
                    $this->addFieldError($message['field'],$this->modx->lexicon($message['message']));
                }
            }
        }
        
        if ($this->saveObject() === false) {
            return $this->failure($this->modx->lexicon($this->objectType.'_err_save'));
        }
        
        return $this->cleanup();
    }

    /**
     * Log the removal manager action
     * @return void
     */
    public function logManagerAction() {
        
    }

}

return 'WebUsersOnlineUpdateProcessor';


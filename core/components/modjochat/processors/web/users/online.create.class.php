<?php

class WebUsersOnlineCreateProcessor extends modObjectCreateProcessor {

    public $objectType = 'object.WebUsersOnlineCreate';
    public $classKey = 'OnlineUsers';
    public $primaryKeyField = 'user_name';
    public $checkListPermission = false;
    public $languageTopics = array();

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize() {
        $props = $this->getProperties();
        
        if (empty($props['user_id'])) {
            $userId = $this->modx->user->get('id');
            $this->setProperty('user_id', $userId);
        }
        if (empty($props['ip_address'])) {
            $userIp = $this->modx->modjochat->getClientIp();
            $this->setProperty('ip_address', $userIp);
        }
        if (empty($props['timeout'])) {
            $settingTimeOut = $this->modx->modjochat->getSetting('user.timeout');
            $timeOut = time() + $settingTimeOut;
            $this->setProperty('timeout', $timeOut);
        }

        $this->object = $this->modx->newObject($this->classKey);
        return true;
    }

    /**
     * Process the Object create processor
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        $props = $this->getProperties();
        $this->object->fromArray($props, '', true); // <== IMPORTANT for multiple Primary Keys!

        /* save element */
        if ($this->object->save() === false) {
            $this->modx->error->checkValidation($this->object);
            return $this->failure($this->modx->lexicon($this->objectType . '_err_save'));
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

return 'WebUsersOnlineCreateProcessor';
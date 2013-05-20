<?php

class WebUsersOnlineGetProcessor extends modObjectGetProcessor {

    public $classKey = 'OnlineUsers';
    public $primaryKeyField = 'user_name';
    public $languageTopics = array();
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebUsersOnlineGet';

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize() {
        $primaryKey = $this->getProperty($this->primaryKeyField, false);
        if (empty($primaryKey)) {
            return $this->modx->lexicon($this->objectType . '_err_ns');
        }

        $timeOut = time();
//        $ip = $this->getProperty('ip_address');
//        $userId = $this->getProperty('user_id');
        $userName = $this->getProperty('user_name');
        $this->object = $this->modx->getObject($this->classKey, array(
//            'user_id' => $userId,
//            'ip_address' => $ip,
            'user_name' => $userName,
            'timeout:>=' => $timeOut
                ));

        if (empty($this->object)) {
            return $this->modx->lexicon($this->objectType . '_err_nfs', array(
//                        'user_id' => $userId,
//                        'ip_address' => $ip,
                        'user_name' => $userName
                    ));
        }

        if ($this->checkViewPermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('view')) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }

}

return 'WebUsersOnlineGetProcessor';
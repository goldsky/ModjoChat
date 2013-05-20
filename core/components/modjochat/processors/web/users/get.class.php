<?php

class WebUsersGetProcessor extends modObjectGetProcessor {

    public $classKey = 'modUser';
    public $primaryKeyField = 'id';
    public $languageTopics = array();
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebUsersGet';

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize() {
        $props = $this->getProperties();
        if (isset($props['userId']) && strlen($props['userId']) > 0) {
            $primaryKey = $this->getProperty($this->primaryKeyField, false);
            if (empty($primaryKey))
                return $this->modx->lexicon($this->objectType . '_err_ns');
            $this->object = $this->modx->getObject($this->classKey, $primaryKey);
            if (empty($this->object))
                return $this->modx->lexicon($this->objectType . '_err_nfs', array($this->primaryKeyField => $primaryKey));
        } elseif (isset($props['userName']) && strlen($props['userName']) > 0) {
            $this->object = $this->modx->getObject($this->classKey, array('username' => $props['userName']));
            if (empty($this->object))
                return $this->modx->lexicon($this->objectType . '_err_nfs', array('username' => $props['userName']));
        } else {
            return $this->modx->lexicon($this->objectType . '_err_nfs');
        }

        if ($this->checkViewPermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('view')) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }

    /**
     * Return the response
     * @return array
     */
    public function cleanup() {
        $output = $this->object->toArray();

        $filteredOutput = array(
            'id' => $output['id'],
            'username' => $output['username']
        );

        return $this->success('', $filteredOutput);
    }

}

return 'WebUsersGetProcessor';
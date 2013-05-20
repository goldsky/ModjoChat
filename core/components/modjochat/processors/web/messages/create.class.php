<?php

class WebMessagesCreateProcessor extends modObjectCreateProcessor {

    public $classKey = 'Messages';
    public $languageTopics = array();
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebMessagesCreateProcessor';

    /**
     * Process the Object create processor
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        $props = $this->getProperties();
        $props['timestamp'] = time();
        $props['user_id'] = $this->modx->user->get('id');
        if ($props['user_id'] === 0) {
            if (empty($props['user_name'])) {
                // PHP 5.4's bug : empty(string) = true!
                if (isset($_COOKIE['modjochat_username']) &&
                        strlen($_COOKIE['modjochat_username']) > 1) {
                    $props['user_name'] = $_COOKIE['modjochat_username'];
                }
            }
        }
        $props['ip_address'] = $this->modx->modjochat->getClientIp();
        $this->object->fromArray($props);

        /* run object validation */
        if (!$this->object->validate()) {
            /** @var modValidator $validator */
            $validator = $this->object->getValidator();
            if ($validator->hasMessages()) {
                foreach ($validator->getMessages() as $message) {
                    $this->addFieldError($message['field'], $this->modx->lexicon($message['message']));
                }
            }
        }

        /* save element */
        if ($this->object->save() == false) {
            $this->modx->error->checkValidation($this->object);
            return $this->failure($this->modx->lexicon($this->objectType . '_err_save'));
        }

        $this->modx->modjochat->updateUserTimeout($props);

        return $this->cleanup();
    }

}

return 'WebMessagesCreateProcessor';
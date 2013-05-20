<?php

class WebSettingsGetProcessor extends modObjectGetProcessor {
    public $classKey = 'Settings';
    public $primaryKeyField = 'key';
    public $languageTopics = array();
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebSettingsGet';

}

return 'WebSettingsGetProcessor';
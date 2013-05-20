<?php

class WebSettingsGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'Settings';
    public $languageTopics = array();
    public $defaultSortField = 'key';
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebSettingsGetList';

}
return 'WebSettingsGetListProcessor';
<?php

class WebActionsGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'Actions';
    public $languageTopics = array();
    public $defaultSortField = 'id';
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebActionsGetList';

    /**
     * Can be used to adjust the query prior to the COUNT statement
     *
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $this->setProperty('limit', 0);
        $channelId = $this->getProperty('channel');
        $startTime = isset($_COOKIE['modjochat_starttime']) ? $_COOKIE['modjochat_starttime'] : time();
        $c->where(array(
            'channel_id' => $channelId,
            'timestamp:>=' => $startTime
        ));
        $lastAction= $this->getProperty('lastAction');
        if ($lastAction > 0) {
            $c->where(array(
                'id:>' => $lastAction
            ));
        }
        return $c;
    }

}

return 'WebActionsGetListProcessor';
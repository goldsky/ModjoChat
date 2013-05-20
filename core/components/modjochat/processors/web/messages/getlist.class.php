<?php

class WebMessagesGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'Messages';
    public $languageTopics = array();
    public $defaultSortField = 'id';
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebMessagesGetList';

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
        $lastId = $this->getProperty('lastId');
        if ($lastId > 0) {
            $c->where(array(
                'id:>' => $lastId
            ));
        }
        return $c;
    }

}

return 'WebMessagesGetListProcessor';
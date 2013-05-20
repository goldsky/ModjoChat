<?php

class WebUsersOnlineRemoveTimeoutProcessor extends modObjectGetListProcessor {

    public $classKey = 'OnlineUsers';
    public $languageTopics = array();
    public $defaultSortField = 'user_name';
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebUsersOnlineRemoveTimeout';

    /**
     * Get the data of the query
     * @return array
     */
    public function getData() {
        $data = array();

        /* query for chunks */
        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->modx->getCount($this->classKey, $c);
        $c = $this->prepareQueryAfterCount($c);

        $sortClassKey = $this->getSortClassKey();
        $sortKey = $this->modx->getSelectColumns($sortClassKey, $this->getProperty('sortAlias', $sortClassKey), '', array($this->getProperty('sort')));
        if (empty($sortKey))
            $sortKey = $this->getProperty('sort');
        $c->sortby($sortKey, $this->getProperty('dir'));

        $collection = $this->modx->getCollection($this->classKey, $c);
        if ($collection) {
            $removeCollection = $this->modx->removeCollection($this->classKey, $c);
            if ($removeCollection == false) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, __FILE__ . ' ');
                $this->modx->log(modX::LOG_LEVEL_ERROR, __METHOD__ . ' ');
                $this->modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': error to remove timeout users from OnlineUsers');
            }
        }
        $data['results'] = $collection;
        return $data;
    }

    /**
     * Can be used to adjust the query prior to the COUNT statement
     *
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $now = time();
        $c->where(array(
            'timeout:<' => $now
        ));
        return $c;
    }

}

return 'WebUsersOnlineRemoveTimeoutProcessor';
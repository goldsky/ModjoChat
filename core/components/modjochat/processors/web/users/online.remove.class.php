<?php

class WebUsersOnlineRemoveProcessor extends modObjectGetListProcessor {

    public $classKey = 'OnlineUsers';
    public $languageTopics = array();
    public $defaultSortField = 'user_name';
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebUsersOnlineRemove';

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
                $this->modx->log(modX::LOG_LEVEL_ERROR, __LINE__ . ': error to remove users from OnlineUsers');
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
        /**
         * dojo can set cookie for guest
         */
        $userName = $this->modx->user->get('username');
        if ($userName == '(anonymous)') {
            // PHP 5.4's bug : empty(string) = true!
            if (isset($_COOKIE['modjochat_username']) &&
                    strlen($_COOKIE['modjochat_username']) > 1) {
                $userName = $_COOKIE['modjochat_username'];
            }
        }
        $c->where(array(
            'user_name' => $userName
        ));
        return $c;
    }

}

return 'WebUsersOnlineRemoveProcessor';
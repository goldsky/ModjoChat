<?php

class WebUsersOnlineGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'OnlineUsers';
    public $languageTopics = array();
    public $defaultSortField = 'user_name';
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebUsersOnlineGetList';

    /**
     * Get the data of the query
     * @return array
     */
    public function getData() {
        $data = array();
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));
        
        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->modx->getCount($this->classKey,$c);
        $c = $this->prepareQueryAfterCount($c);

        $sortClassKey = $this->getSortClassKey();
        $sortKey = $this->modx->getSelectColumns($sortClassKey,$this->getProperty('sortAlias',$sortClassKey),'',array($this->getProperty('sort')));
        if (empty($sortKey)) $sortKey = $this->getProperty('sort');
        $c->sortby($sortKey,$this->getProperty('dir'));
        if ($limit > 0) {
            $c->limit($limit,$start);
        }

        $data['results'] = $this->modx->getCollection($this->classKey,$c);
        return $data;
    }

    /**
     * Can be used to adjust the query prior to the COUNT statement
     *
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $userId = $this->getProperty('user_id');
        if (!empty($userId)) {
            $c->where(array(
                'user_id' => $userId
            ));
        }
        $ipAddress = $this->getProperty('ip_address');
        if (!empty($ipAddress)) {
            $c->where(array(
                'ip_address' => $ipAddress
            ));
        }
        $userName = $this->getProperty('user_name');
        if (!empty($userName)) {
            $c->where(array(
                'user_name' => $userName
            ));
        }
        $channelId = $this->getProperty('channel_id');
        if (!empty($channelId)) {
            $c->where(array(
                'channel_id' => $channelId
            ));
        }
        /**
         * this part is tricky!
         * timeout => 0 to ignore this filter
         */
        $timeOut = $this->getProperty('timeout');
        if ($timeOut !== 0) {
            $now = time();
            $c->where(array(
                'timeout:>=' => $now
            ));
        }
        return $c;
    }

    /**
     * Can be used to insert a row after iteration
     * @param array $list
     * @return array
     */
    public function beforeIteration(array $list) {
        $newList = array();
        foreach ($list as $k => $v) {
            if ($v['user_id'] === 0) {
                // PHP 5.4's bug : empty(string) = true!
                if (isset($_COOKIE['modjochat_username']) &&
                        strlen($_COOKIE['modjochat_username']) > 1) {
                    $v['username'] = $_COOKIE['modjochat_username'];
                    $newList[] = $v;
                } else {
                    continue;
                }
            } else {
                $userObj = $this->modx->getObject('modUser', $v['user_id']);
                $userProfile = $userObj->get('Profile');
                $userArray = array_merge($v, $userProfile);
                $newList[] = $userArray;
            }
        }
        
        return $newList;
    }

    /**
     * Can be used to insert a row after iteration
     * @param array $list
     * @return array
     */
    public function afterIteration(array $list) {
        foreach($list as $k => $v) {
            $list[$k] = $this->modx->modjochat->camelCaseKeys($v);
        }
        return $list;
    }

}

return 'WebUsersOnlineGetListProcessor';
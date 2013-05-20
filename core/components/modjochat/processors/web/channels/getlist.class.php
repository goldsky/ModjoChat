<?php

class WebChannelsGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'Channels';
    public $languageTopics = array();
    public $defaultSortField = 'id';
    public $checkListPermission = false;
    public $objectType = 'modjochat.WebChannelsGetList';

    /**
     * Get the data of the query
     * @return array
     */
    public function getData() {
        $data = array();
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));

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
        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        $data['results'] = $this->modx->getCollection($this->classKey, $c);
        return $data;
    }

    /**
     * Can be used to insert a row after iteration
     * @param array $list
     * @return array
     */
    public function afterIteration(array $list) {
        $userId = $this->modx->user->get('id');
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
        } else {
            if (isset($_COOKIE['modjochat_username'])) {
                $userName = $_COOKIE['modjochat_username'];
            }
        }
        $newList = array();
        foreach ($list as $k => $v) {
            if ($v['is_guest_allowed'] !== 1 && $userId === 0) {
                continue;
            }
            $list[$k] = $this->modx->modjochat->camelCaseKeys($v);
            $list[$k]['userId'] = $userId;
            $list[$k]['userName'] = $userName;
            $list[$k]['userIp'] = $this->modx->modjochat->getClientIp();
            $newList[] = $list[$k];
        }

        return $newList;
    }

}

return 'WebChannelsGetListProcessor';
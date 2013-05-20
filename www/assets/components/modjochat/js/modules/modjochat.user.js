define([
    "dojo/_base/declare",
    "dojo/_base/lang",
    
    "dojo/request",
    "dojo/cookie",
    "dojo/when",
    "dojo/promise/all",
    "dojo/aspect",
    
    "dojo/data/ObjectStore",
    "dojo/store/JsonRest",
    "dojo/store/Memory",
    "dojo/store/Observable",
    "dojo/store/Cache",
    
    "dijit/registry",
    "dijit/tree/ForestStoreModel",
    "dijit/Tree",
    "dojox/timing"
], function(
        declare, lang,
        request, cookie, when, all, aspect,
        ObjectStore, JsonRest, Memory, Observable, Cache,
        registry, ForestStoreModel, Tree,
        timing
        ) {
    return declare(null, {
        store: null,
        channelObj: {},
        configs: {},
        constructor: function(/*Object*/kwArgs) {
            lang.mixin(this, kwArgs);
        },
        usersContainer: function() {
            var _this = this;
            var check = registry.byId('userTree_' + _this.channelObj.id);
            if (check)
                check.destroyRecursive();

            var dataStore = new ObjectStore({
//                objectStore: this.store
                objectStore: this.storeMemory
            });

            var treeModel = new ForestStoreModel({
                store: dataStore,
                query: {
                    channel_id: _this.channelObj.id
                },
                labelAttr: 'userName'
            });

            var usersTreeObj = new Tree({
                id: 'userTree_' + _this.channelObj.id,
                model: treeModel,
                showRoot: false,
                persist: false,
                onClick: function(item) {
                    console.log(item);
                },
                /* prepare for the context menu widget below */
                onMouseDown: function(e) {
                    if (e.altKey) {
                        return;
                    }
                    if (e.button === 2) { // right-click
                        var treeNode = registry.getEnclosingWidget(e.target);
                        if (!treeNode) {
                            return;
                        }
                        this.set('selectedItem', treeNode.item);
                        console.log('treeNode', treeNode);
                    }
                }
            });

//            document.body.onclick = function(e) {
//                _this.updateUserTimeout();
//            };

            return usersTreeObj;
        },
        storeMaster: function() {
            var _this = this;

            var restStore = JsonRest({
                target: _this.configs.conn,
                sortParam: 'sort',
                sort: [{
                        attribute: "user_name",
                        descending: false
                    }],
                idProperty: 'user_name',
                preventCache: true,
                query: function(query, options) {
                    query = query || {};
                    query.action = 'web/rest/user/online/index';
                    query.channel_id = query.channel_id ? query.channel_id : _this.channelObj.id;
                    query.status = query.status ? query.status : 'online';
                    options = options || {};
                    options.start = options.start ? options.start : 0;
                    options.count = options.count ? options.count : 25;
                    return this.inherited(arguments);
                }
            });
            return restStore;

        },
        storeMemory: Observable(Memory()),
        initCache: function() {
            this.store = new Cache(this.storeMaster(), this.storeMemory);
        },
        init: function() {
            var _this = this;
            var query = this.store.query();
            when(query, lang.hitch(this, function() {
                var memoryQuery = this.storeMemory.query({
                    channel_id: _this.channelObj.id
                });
                console.log('memoryQuery', memoryQuery);
                memoryQuery.observe(function(object, removedFrom, insertedInto) {
                    console.log('observed: object, removedFrom, insertedInto =>', object, removedFrom, insertedInto);
                    if (insertedInto === -1) {
                        console.log('removed object', object, 'from index', removedFrom);
                    }
                    else if (removedFrom === -1) {
                        console.log('added object', object, 'to index', insertedInto);
                    }
                    else {
                        console.log('updated object', object, 'with index', removedFrom, insertedInto);
                    }
                });
                
            }));
        },
        getData: function() {
            this.initCache();
        },
        timer: function() {
            var _this = this,
                    t = new timing.Timer(_this.configs.ajaxTimer),
//                    t = new timing.Timer(5000),
                    inSecond = t.interval / 1000;

            this.init();
            t.onStart = function() {
                console.info("User starting timer");
            };
            t.onTick = function() {
                console.info(inSecond + " seconds lapsed users list");
                _this.getData();
            };
            t.start();
        },
        getOnlineUsers: function() {
            this.getData();

            var startTime = cookie('modjochat_starttime');
            if (!!startTime) {
                if (this.configs.connectionType === 'ajax') {
                    this.timer();
                }
            }

            var usersContainer = this.usersContainer();
            return usersContainer;
        },
        checkUsername: function(/*Object*/inputObj) {
            var _this = this,
                    userName = inputObj.getValue();

            var checkOnline = request(this.configs.conn, {
                query: {
                    action: 'web/users/online.get',
                    user_name: userName
                },
                preventCache: true,
                handleAs: "json"
            });
            var checkMember = request(this.configs.conn, {
                query: {
                    action: 'web/users/get',
                    user_name: userName
                },
                preventCache: true,
                handleAs: "json"
            });

            all({
                checkOnline: checkOnline,
                checkMember: checkMember
            }).then(function(results) {

                if (results.checkOnline.success === true) {
                    inputObj.setValue('');
                    alert('Username ' + userName + ' is online. Please choose another username.');
                    throw new Error('Pick another username!');
                }

                if (results.checkMember.success === true) {
                    if (checkMember.data.object && checkMember.data.object.username === userName) {
                        inputObj.setValue('');
                        alert('Username ' + userName + ' has been taken. Please choose another username.');
                        return;
                    }
                } else {
                    inputObj.setValue('');
                    if (checkMember.message !== 'Access denied.') {
                        var cookieValue = cookie('modjochat_username');
                        if (cookieValue === userName) {
                            alert('Username ' + userName + ' has been taken. Please choose another username.');
                            return;
                        } else {
                            request.post(_this.configs.conn, {
                                query: {
                                    action: 'web/users/online.updatecheck',
                                    user_name: userName,
                                    channel_id: _this.channelObj.id
                                },
                                preventCache: true,
                                handleAs: "json"
                            }).then(
                                    function(res) {
                                        console.log(res);
                                        if (res.success) {
                                            cookie("modjochat_username", userName, {
                                                expires: 1 // 1 day
                                            });
                                            var timestamp = Math.round((new Date()).getTime() / 1000);
                                            cookie("modjochat_starttime", timestamp, {
                                                expires: 1 // 1 day
                                            });
                                            document.location.reload(true);
                                        }
                                    },
                                    function(error) {
                                        console.log("An error occurred: " + error);
                                    });
                        }
                    }
                }
            });
        },
        removeOnlineUser: function() {
            var _this = this;
            var removeOnlineUser = request.post(_this.configs.conn, {
                query: {
                    action: 'web/users/online.remove',
                    channel_id: _this.channelObj.id
                },
                preventCache: true,
                handleAs: "json"
            }).then(function(res) {
                if (res.total > 0) {
                    cookie("modjochat_username", null, {
                        expires: 0
                    });
                    cookie("modjochat_starttime", null, {
                        expires: 0
                    });
                    document.location.reload(true);
                } else {
                    console.log('res', res);
                }
            });
            return removeOnlineUser;
        },
        updateUserTimeout: function() {
            var _this = this;
            request.post(_this.configs.conn, {
                query: {
                    action: 'web/users/online.update.timeout',
                    channel_id: _this.channelObj.id
                },
                preventCache: true,
                handleAs: "json"
            });
        }
    });
});
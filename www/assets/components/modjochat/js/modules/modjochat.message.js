/**
 * @link http://en.wikipedia.org/wiki/List_of_Internet_Relay_Chat_commands IRC commands
 */

define([
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/_base/array",
    "dojo/when",
    
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-geometry",
    "dojo/dom-style",
    
    "dojo/request",
    "dojo/promise/all",
    "dijit/registry",
    "dojo/cookie",
    
    "dojo/store/JsonRest",
    "dojo/store/Observable",
    "dojo/store/Cache",
    "dojo/store/Memory",
    
    "dgrid/OnDemandList",
    "dgrid/extensions/DijitRegistry",
    "put-selector/put",
    "dojox/timing",
    "dojox/socket"
], function(
        declare, lang, array, when,
        dom, domConstruct, domGeom, domStyle,
        request, all, registry, cookie,
        JsonRest, Observable, Cache, Memory,
        List, DijitRegistry, put,
        timing, Socket
        ) {
    return declare(null, {
        channelObj: {},
        configs: {},
        constructor: function(/*Object*/kwArgs) {
            lang.mixin(this, kwArgs);
        },
        insertRow: function(/*Object*/object, /*Object*/options) {
            var date = new Date(object.timestamp * 1000);
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var seconds = date.getSeconds();
            var formattedTime = hours + ':' + minutes + ':' + seconds;

            var parent = put("div");
            var userInfo = put("div.modjochatChatInfo");
            put(userInfo, "span.modjochatChatUser", object.user_name);
            put(userInfo, "span.modjochatChatTime", formattedTime);
            put(parent, userInfo);
            put(parent, "div.modjochatChatText", object.text);

            return parent;
        },
        submitAjaxChat: function(/*Object*/textarea) {
            var _this = this;
            request.post(this.configs.conn + '?action=web/messages/create', {
                data: {
                    text: textarea.getValue(),
                    user_id: _this.channelObj.userId,
                    user_name: _this.channelObj.userName,
                    channel_id: _this.channelObj.id
                }
            });
        },
        submitChat: function(/*Object*/textarea) {
            if (this.configs.connectionType === 'socket') {
                this.submitSocketChat(textarea);
            } else {
                this.submitAjaxChat(textarea);
            }
        },
        msgContainer: function() {
            var _this = this;

            var msgContainer = new (declare([List, DijitRegistry]))({
                id: 'chattalks_' + _this.channelObj.id,
                region: "center", // for chatpanel- BorderContainer
                renderRow: function(object, options) {
                    return _this.insertRow(object, options);
                }
                ,
                keepScrollPosition: true
            });

            var startTime = cookie('modjochat_starttime');
            if (!!startTime) {
                if (_this.configs.connectionType === 'ajax') {
                    _this.timer();
                }
            }
            return msgContainer;
        },
        last: {
            id: 0,
            action: 0
        },
        getData: function() {
            var _this = this,
                    chatTalks = registry.byId('chattalks_' + _this.channelObj.id),
                    chatUsers = registry.byId('chatusers_' + _this.channelObj.id);

            var msgList = request(_this.configs.conn, {
                query: {
                    action: "web/messages/getlist",
                    channel: _this.channelObj.id,
                    lastId: _this.last.id
                },
                preventCache: true,
                handleAs: "json"
            });
            var actionList = request(_this.configs.conn, {
                query: {
                    action: "web/actions/getlist",
                    channel: _this.channelObj.id,
                    lastAction: _this.last.action
                },
                preventCache: true,
                handleAs: "json"
            });
            all({
                msgList: msgList,
                actionList: actionList
            }).then(function(results) {
                console.log('results', results);
                if (results.msgList.total > 0) {
                    array.forEach(results.msgList.results, function(item, itemIdx) {
                        if (item['id'] > _this.last.id) {
                            _this.last.id = item['id'];
                        }
                    });
                    chatTalks.renderArray(results.msgList.results);
                    _this.fixScroll();
                }
            });
        },
        timer: function() {
            var _this = this,
                    t = new timing.Timer(_this.configs.ajaxTimer),
//                    t = new timing.Timer(5000),
                    inSecond = t.interval / 1000;

//            _this.getData();
//            t.onStart = function() {
//                console.info("Starting timer");
//            };
            t.onTick = function() {
//                console.info(inSecond + " seconds lapsed");
                _this.getData();
            };
            t.start();
        },
        fixScroll: function() {
            console.log('@todo: fix scrolling');
            var _this = this;
            var chatTalks = registry.byId('chattalks_' + _this.channelObj.id);
            var chatTalksDom = dom.byId('chattalks_' + _this.channelObj.id);
            var includeScroll = true;
            var output = domGeom.position(chatTalksDom, includeScroll);
//            chatTalks.scrollTo({x: 0, y: output.h});

//            var scrollPosition = chatTalks.getScrollPosition();
//            console.log('scrollPosition', scrollPosition);
//            var yCoord = scrollPosition.y + 100;
//            chatTalks.scrollTo({x: 0, y: 0});
        }
    });
});
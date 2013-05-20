define([
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/_base/array",
    "dojo/dom-construct",
    "dojo/request",
    "dijit/registry",
    "dijit/layout/BorderContainer",
    "dijit/layout/TabContainer",
    "dijit/layout/ContentPane",
    "dojo/keys",
    "dojo/_base/event",
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/form/Button",
    "assets/components/modjochat/js/modules/modjochat.user.js",
    "assets/components/modjochat/js/modules/modjochat.message.js"
], function(
        declare, lang, array,
        domConstruct,
        request, registry,
        BorderContainer, TabContainer, ContentPane,
        keys, event, ValidationTextBox, Textarea, Button,
        ModjochatUser, ModjochatMessage
        ) {

    return declare(null, {
        configs: {},
        constructor: function(/*Object*/kwArgs) {
            lang.mixin(this, kwArgs);
        },
        getSettings: function() {
            var settings = request(this.configs.conn, {
                query: {
                    action: 'web/settings/getlist'
                },
                preventCache: true,
                handleAs: "json"
            });
            return settings;
        },
        getSetting: function(key) {
            var setting = request(this.configs.conn, {
                query: {
                    action: 'web/settings/get',
                    key: key
                },
                preventCache: true,
                handleAs: "json"
            });
            return setting;
        },
        getChannels: function() {
            var _this = this;
            var check = registry.byId(this.configs.domHolders.wrapper);
            if (check)
                check.destroyRecursive(true);
            domConstruct.empty(this.configs.domHolders.wrapper);
            domConstruct.place(_this.imageLoader(), _this.configs.domHolders.wrapper);

            var channels = request(this.configs.conn, {
                query: {
                    action: 'web/channels/getlist',
                    limit: 0
                },
                preventCache: true,
                handleAs: "json"
            });

            channels.response.then(function(response) {
                if ((response.data.total - 0) > 0) {
                    var chatBox = new BorderContainer({
                        'class': 'modjochatChatBox',
                        design: 'headline'
                    });
                    var channelTab = new TabContainer({
                        region: "center",
                        'class': "centerPanel"
                    });
                    array.forEach(response.data.results, function(result, resultIdx) {
                        channelTab.addChild(_this.parseChannelTab(result));
                    });
                    chatBox.addChild(channelTab);
                    domConstruct.empty(_this.configs.domHolders.wrapper);
                    chatBox.placeAt(_this.configs.domHolders.wrapper);
                    var c = domConstruct.create('div', {
                        innerHTML: 'modjoChat, ' + _this.configs.version + ' &copy 2013 <a href="//www.virtudraft.com" target="_blank">virtudraft</a>' +
                                ' &amp; <a href="//www.icwebdesign.co.uk" target="_blank">icwebdesign</a>',
                        'class': 'modjochatFootNote'
                    });
                    domConstruct.place(c, _this.configs.domHolders.wrapper);
                    chatBox.startup();
                }
            });
        },
        imageLoader: function() {
            var imgLdr = domConstruct.create('div', {
                innerHTML: '<img src="' + this.configs.imageLoader + '" />',
                style: {
                    textAlign: 'center'
                }
            });
            return imgLdr;
        },
        parseChannelTab: function(/*Object*/channelObj) {
            var check = registry.byId('chatpanel_' + channelObj.id);
            if (check)
                check.destroyRecursive();

            var channel = new ContentPane({
                title: channelObj.id,
                id: 'chatpanel_' + channelObj.id
            });

            var chatRoom = new BorderContainer({
                design: 'sidebar'
            });

            var message = new ModjochatMessage({
                channelObj: channelObj,
                configs: this.configs
            });
            var chatTalks = message.msgContainer();
            chatRoom.addChild(chatTalks);

            var chatUsers = new ContentPane({
                id: 'chatusers_' + channelObj.id,
                region: "right",
                style: "width: 14em;"
            });

            var user = new ModjochatUser({
                channelObj: channelObj,
                configs: this.configs
            });
            
            user.updateUserTimeout();
            var onlineUsers = user.getOnlineUsers();
            chatUsers.addChild(onlineUsers);

            chatRoom.addChild(chatUsers);
            var chatInput = this.parseInputContainer(channelObj);
            chatRoom.addChild(chatInput);

            channel.addChild(chatRoom);
            return channel;
        },
        parseInputContainer: function(/*Object*/channelObj) {
            var _this = this;
            var chatInput = new ContentPane({
                region: 'bottom',
                style: 'margin: 0; padding: 0; border: none;'
            });
            if (channelObj.userId !== 0 ||
                    (channelObj.isGuestAllowed === 1 && (channelObj.userName !== '(anonymous)'))
                    ) {
                chatInput.addChild(new Textarea({
                    name: channelObj.title + "_chatinput",
                    onKeyDown: function(e) {
                        if (e.keyCode === keys.ENTER) {
                            event.stop(e);
                            var message = new ModjochatMessage({
                                channelObj: channelObj,
                                configs: _this.configs
                            });
                            message.submitChat(this);
                            this.setValue('');
                        }
                    }
                }));
                if (_this.configs.allowGuestLogin &&
                        channelObj.userId === 0 &&
                        channelObj.userName) {
                    var bottomRow = new BorderContainer({
                        style: 'min-height: 43px;'
                    });
                    bottomRow.addChild(new ContentPane({
                        content: 'smiley',
                        region: 'center',
                        style: 'margin: 0; padding: 0; border: none;'
                    }));
                    bottomRow.addChild(new Button({
                        label: 'Guest logout',
                        region: 'right',
                        style: 'margin: 0; padding: 0; border: none;',
                        onClick: function() {
                            var user = new ModjochatUser({
                                channelObj: channelObj,
                                configs: _this.configs
                            });
                            user.removeOnlineUser();
                        }
                    }));
                    chatInput.addChild(bottomRow);
                } else {
                    chatInput.addChild(new ContentPane({
                        content: 'smiley',
                        style: 'margin: 0; padding: 0; border: none;'
                    }));
                }
            } else if (_this.configs.allowGuestLogin) {
                var user = new ModjochatUser({
                    channelObj: channelObj,
                    configs: _this.configs
                });
                var usernameTB = new ValidationTextBox({
                    placeholder: 'Select a username',
                    style: 'display: inline-block; vertical-align: middle;',
                    'class': 'modjochatInputInner',
                    onKeyDown: function(e) {
                        if (this.isValid()) {
                            if (e.keyCode === keys.ENTER) {
                                user.checkUsername(usernameTB);
                            }
                        }
                    },
                    regExp: '[\\w]+',
                    invalidMessage: 'Invalid Non-Space Text.'
                });
                chatInput.addChild(usernameTB);
                chatInput.addChild(new Button({
                    label: 'Guest login',
                    style: 'margin-left: 20px;',
                    onClick: function() {
                        var username = usernameTB.getValue();
                        if (!username) {
                            return;
                        }
                        user.checkUsername(channelObj, usernameTB);
                    }
                }));
                chatInput.addChild(new ContentPane({
                    content: 'IP: ' + channelObj.userIp,
                    style: 'display: inline-block; vertical-align: bottom;'
                }));
            }
            return chatInput;
        },
        parseAdminTab: function() {

        }
    });
});

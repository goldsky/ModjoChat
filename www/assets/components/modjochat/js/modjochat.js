require([
    "dojo/_base/window",
    "dojo/dom-class",
    "assets/components/modjochat/js/modules/modjochat.channel.js",
    "dojo/domReady!"
    ], function(
        win,
        domClass,
        ModjochatChannel
        ){

        domClass.add(win.body(), modjochat.dojoTheme);

        // override
        modjochat = new ModjochatChannel({configs: modjochat});
        modjochat.getChannels();

    });
App({
    globalData: {
        userInfo: null,
        appInfo: null,
        token: null,
        session: null
    },
    queryData: {},
    CACHE: {},
    FLAGS: {},
    OPTIONS: {},
    iCMS: function() {
        let iCMS  = require('./iCMS/core/iCMS.js');
        let utils = require('./iCMS/core/iUtils.js');
        return utils.extend(true,{},iCMS);
    },
    onLaunch: function(options) {
    },
    onShow: function(options) {
        this.OPTIONS = options;
        this.queryData = options.query;
    },
    onHide: function() {},
    onError: function(msg) {
        console.log('onError:', msg)
    }
})

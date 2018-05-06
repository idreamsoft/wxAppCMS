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
    ONESELF:false,
    wxAppCMS: function() {
        let a = require('./iCMS/core/iCMS.js');
        a.$globalData = this.globalData;
        a.$queryData  = this.queryData;
        return a.utils.extend(true,{},a);
    },
    onLaunch: function(options) {
        // console.log(options.path);
    },
    onShow: function(options) {
        // console.log('==================onShow=======================');
        var $URI = this.getURI(options.path,options.query);
        // console.log('URI:',$URI);

        if(this.FLAGS['path']==$URI){
            this.ONESELF = true;
        }else{
            this.ONESELF = false;
            this.CACHE = {};
        }
        // console.log('ONESELF:',this.ONESELF);

        this.queryData = options.query;
    },
    onHide: function() {
        var $page = getCurrentPages();
        this.FLAGS['path'] = this.getURI($page[0].route,$page[0].options);
        // console.log('==================onHide=======================');
        // console.log(this.FLAGS['path']);
        // console.log("\n\n\n\n\n");
    },
    onError: function(msg) {
        console.log('onError:', msg)
    },
    getURI: function(path,query) {
        if(query){
            let iUrl = require('./iCMS/core/iUrl.js');
            path+='?'+iUrl.encode(query);
        }
        return path;
    }

})

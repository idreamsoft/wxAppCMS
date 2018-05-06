(function () {
    var URL = window.UMEDITOR_HOME_URL
    window.UMEDITOR_CONFIG = {
        UMEDITOR_HOME_URL : URL
        ,imageUrl:window.iCMS.CONFIG.API + '?app=user&do=uploadimage'
        ,imageFieldName:"upfile"
        ,toolbar:[
            'undo redo | bold italic underline strikethrough | image '
        ]
        ,lang:"zh-cn"
        ,langPath:URL +"lang/"
        ,textarea:'body'
        ,initialContent:''
        ,initialFrameWidth:"100%"
        ,initialFrameHeight:500
        ,focus:false
        ,zIndex:1000
        ,format:true
        ,formatCallback:function function_name (a) {
            var utils = window.iCMS.run('utils');
            return utils.format(a);
        }
    };
})();

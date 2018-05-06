(function () {
    var URL = window.UMEDITOR_HOME_URL
    window.UMEDITOR_CONFIG = {
        UMEDITOR_HOME_URL : URL
        ,imageUrl:window.iCMS.CONFIG.API + '?app=user&do=uploadimage'
        ,imageFieldName:"upfile"
        ,toolbar:[
            'preview | undo redo | bold italic underline strikethrough | superscript subscript | forecolor backcolor |',
            'insertorderedlist insertunorderedlist | paragraph | fontsize' ,
            '| justifyleft justifycenter justifyright justifyjustify |',
            'link unlink | image video',
            '| cleardoc fullscreen'
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

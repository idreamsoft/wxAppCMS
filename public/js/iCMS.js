/**
 * 开发版本的文件导入
 */
(function (){
    var paths  = [
            'icms.js',
            'plugin.js',
            'timer.js',
            'poshytip.js',
            'config.js',
            'cookie.js',
            'api.js',
            'artdialog.js',
            'ui.dialog.js',
            'ui.js',
            'utils.js',
            'user.js',
            'passport.js',
            'common.js',
            'comment.js',
            'former.js',
            'init.js',
        ],
        baseURL = '/public/js/_src/';
        if(window.location.href.indexOf('/public/')!="-1"){
            baseURL = './js/_src/';
        }
    for (var i=0,pi;pi = paths[i++];) {
        document.write('<script type="text/javascript" src="'+ baseURL + pi +'"></script>');
    }
})();

(function($) {
    window.iCMS = {
        config:{
            API: '/public/api.php',
            PUBLIC: '/',
            COOKIE: 'iCMS_',
            AUTH:'USER_AUTH',
            DIALOG:[]
        },
        UI:{
            $dialog:{},
            success: function(msg,callback) {
                return iCMS.alert(msg,true,callback);
            },
            alert: function(msg,ok,callback) {
                return iCMS.alert(msg,ok,callback);
            },
            dialog: function(options,callback) {
                return iCMS.dialog(options,callback);
            }
        },
        FORMER:{
            select: function(el, v) {
                var va = v.split(',');
                $("#"+el).val(va).trigger("chosen:updated");
            },
            checked: function(el,v){
                if(v){
                    var va = v.split(',');
                    $.each(va, function(i,val){
                        $(el+'[value="'+val+'"]').prop("checked", true);
                    })
                }else{
                    // $(el).prop("checked",true);
                }
                if($.uniform){
                    $.uniform.update(el);
                }
            }
        },
        init: function(options) {
            this.config = $.extend(this.config,options);
        },
        api: function(app, _do) {
            return iCMS.config.API + '?app=' + app + (_do || '');
        },
        multiple: function(a) {
            var $this = $(a),
            $parent   = $this.parent(),
            param     = iCMS.param($this),
            _param    = iCMS.param($parent);
            return $.extend(param,_param);
        },
        param: function(a,_param) {
            if(_param){
                a.attr('data-param',iCMS.json2str(_param));
                return;
            }
            var param = a.attr('data-param') || false;
            if (!param) return {};
            return $.parseJSON(param);
        },
        tip: function(el,title,placement) {
            placement = placement||el.attr('data-placement');
            var container = el.attr('data-container');
            if(container){
                $(container).html('');
            }
            el.tooltip('destroy');
            el.tooltip({
              html: true,container:container||false,
              placement: placement||'right',
              trigger: 'manual',
              title:title
            }).tooltip('show');
        },
        success: function(msg,callback,t) {
            return window.top.iCMS.alert(msg,true,callback,t);
        },
        alert: function(msg,ok,callback,t) {
            var opts = ok ? {
                label: 'success',
                icon: 'check'
            } : {
                label: 'warning',
                icon: 'warning'
            }
            opts.id      = 'iPHP-DIALOG-ALERT';
            opts.skin    = 'iCMS_dialog_alert'
            opts.content = msg;
            opts.height  = 150;
            opts.modal   = true;
            opts.time    = t||3000;
            return window.top.iCMS.dialog(opts,callback);
        },
        dialog: function(options,callback) {
            var defaults = {
                id:'iCMS-DIALOG',
                title:'iCMS - 提示信息',
                width:'auto',height:'auto',
                className:'iCMS_UI_DIALOG',
                backdropBackground: '#333',
                backdropOpacity: 0.5,
                fixed: true,
                autofocus: false,
                quickClose: true,
                modal: true,
                time: null,
                label:'success',icon: 'check',api:false,elemBack:'beforeremove'
            },
            timeOutID = null,
            opts = $.extend(defaults,iCMS.config.DIALOG,options);

            if(opts.follow){
                opts.fixed = false;
                opts.modal = false;
                opts.skin  = 'iCMS_tooltip_popup'
                opts.className = 'ui-popup';
                opts.backdropOpacity = 0;
            }
            var content = opts.content;
            //console.log(typeof content);
            if (content instanceof jQuery){
                opts.content = content;
            }else if (typeof content === "string") {
                opts.content = __msg(content);
            }
            opts.onclose = function(){
                __callback('close');
            };
            opts.onbeforeremove = function(){
                __callback('beforeremove');
            };
            opts.onremove = function(){
                __callback('remove');
            };
            var d = window.dialog(opts);

            if(opts.modal){
                d.showModal();
                // $(d.backdrop).addClass("ui-popup-overlay").click(function(){
                //     d.close().remove();
                // })
            }else{
                d.show(opts.follow);
                if(opts.follow){
                    //$(d.backdrop).remove();
                    // $("body").bind("click",function(){
                    //     d.close().remove();
                    // })
                }
                //$(d.backdrop).css("opacity","0");
            }
            if(opts.time){
                timeOutID = window.setTimeout(function(){
                    d.destroy();
                },opts.time);
            }
            d.destroy = function (){
                d.close().remove();
            }

            function __callback(type){
                window.clearTimeout(timeOutID);
                if (typeof(callback) === "function") {
                    callback(type,d);
                }
            }
            function __msg(content){
                return '<table class=\"ui-dialog-table\" align=\"center\"><tr><td valign=\"middle\">'
                +'<div class=\"iPHP-msg\">'
                +'<span class=\"label label-' + opts.label + '\">'
                +'<i class=\"fa fa-' + opts.icon + '\"></i> '
                + content
                + '</span></div>'
                +'</td></tr></table>';
            }
            iCMS.UI.$dialog = d;
            return d;
        },
        setcookie: function(cookieName, cookieValue, seconds, path, domain, secure) {
            var expires = new Date();
            expires.setTime(expires.getTime() + seconds);
            cookieName = this.config.COOKIE + '_' + cookieName;
            document.cookie = escape(cookieName) + '=' + escape(cookieValue) + (expires ? '; expires=' + expires.toGMTString() : '') + (path ? '; path=' + path : '/') + (domain ? '; domain=' + domain : '') + (secure ? '; secure' : '');
        },
        getcookie: function(name) {
            name = this.config.COOKIE + '_' + name;
            var cookie_start = document.cookie.indexOf(name);
            var cookie_end = document.cookie.indexOf(";", cookie_start);
            return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
        },
        random: function(len,ischar) {
            len = len || 16;
            var chars = "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ";
            if(ischar){
                var chars = "abcdefhjmnpqrstuvwxyz";
            }
            var code = '';
            for (i = 0; i < len; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length))
            }
            return code;
        },

        json2str:function(o){
            var arr = [];
            var fmt = function(s) {
                if (typeof s == 'object' && s != null) return iCMS.json2str(s);
                return /^(string|number)$/.test(typeof s) ? '"' + s + '"' : s;
            }
            for (var i in o)
                 arr.push('"' + i + '":'+ fmt(o[i]));
            return '{' + arr.join(',') + '}';
        },
        format:function(content,ubb) {
            content = content.replace(/\/"/g, '"')
                .replace(/\\\&quot;/g, "")
                .replace(/\r/g, "")
                .replace(/on(\w+)="[^"]+"/ig, "")
                .replace(/<script[^>]*?>(.*?)<\/script>/ig, "")
                .replace(/<style[^>]*?>(.*?)<\/style>/ig, "")
                .replace(/style=[" ]?([^"]+)[" ]/ig, "")
                .replace(/<a[^>]+href=[" ]?([^"]+)[" ]?[^>]*>(.*?)<\/a>/ig, "[url=$1]$2[/url]")
                .replace(/<img[^>]+src=[" ]?([^"]+)[" ]?[^>]*>/ig, "[img]$1[/img]")
                .replace(/<embed/g, "\n<embed")
                .replace(/<embed[^>]+class="edui-faked-video"[^"].+src=[" ]?([^"]+)[" ]+width=[" ]?([^"]\d+)[" ]+height=[" ]?([^"]\d+)[" ]?[^>]*>/ig, "[embed video=$2,$3]$1[/embed]")
                .replace(/<embed[^>]+class="edui-faked-music"[^"].+src=[" ]?([^"]+)[" ]+width=[" ]?([^"]\d+)[" ]+height=[" ]?([^"]\d+)[" ]?[^>]*>/ig, "[embed music=$2,$3]$1[/embed]")
                .replace(/<video[^>]*?width=[" ]?([^"]\d+)[" ]+height=[" ]?([^"]\d+)[" ]+src=[" ]?([^"]+)[" ]+?[^>]*>*<source src=[" ]?([^"]+)[" ]+type=[" ]?([^"]+)[" ]\/>*<\/video>/img, "[video=$1,$2 type=\"$5\"]$3[/video]")
                .replace(/<h([1-6])[^>]*>(.*?)<\/h([1-6])>/ig, "[h$1]$2[/h$1]")
                .replace(/<b[^>]*>(.*?)<\/b>/ig, "[b]$1[/b]")
                .replace(/<strong[^>]*>(.*?)<\/strong>/ig, "[b]$1[/b]")
                .replace(/<p[^>]*?>/g, "\n\n")
                .replace(/<br[^>]*?>/g, "\n")
                .replace(/<[^>]*?>/g, "");
                // console.log(content);
            if(ubb){
                content = content.replace(/\n+/g, "[iCMS.N]");
                content = this.n2p(content,ubb);
                return content;
            }
            content = content.replace(/\[url=([^\]]+)\]\n(\[img\]\1\[\/img\])\n\[\/url\]/g, "$2")
                .replace(/\[img\](.*?)\[\/img\]/ig, '<p><img src="$1" /></p>')
                .replace(/\[b\](.*?)\[\/b\]/ig, '<b>$1</b>')
                .replace(/\[h([1-6])\](.*?)\[\/h([1-6])\]/ig, '<h$1>$2</h$1>')
                .replace(/\[url=([^\]|#]+)\](.*?)\[\/url\]/g, '$2')
                .replace(/\[url=([^\]]+)\](.*?)\[\/url\]/g, '<a target="_blank" href="$1">$2</a>')
               .replace(/\n+/g, "[iCMS.N]");

            content = this.n2p(content);
            content = content.replace(/#--iCMS.PageBreak--#/g, "<!---->#--iCMS.PageBreak--#")
                .replace(/<p>\s*<p>/g, '<p>')
                .replace(/<\/p>\s*<\/p>/g, '</p>')
                .replace(/<p>\s*<\/p>/g, '')
                .replace(/\[video=(\d+),(\d+)\stype="(.+?)"\](.*?)\[\/video\]/ig, '<video class="edui-upload-video  vjs-default-skin  video-js" controls="" preload="none" width="$1" height="$2" src="$4" data-setup="{}">'+'<source src="$4" type="$3"/>'+'</video>')
                .replace(/\[embed\svideo=(\d+),(\d+)\](.*?)\[\/embed\]/ig, '<embed type="application/x-shockwave-flash" class="edui-faked-video" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true"/>')
                .replace(/\[embed\smusic=(\d+),(\d+)\](.*?)\[\/embed\]/ig, '<embed type="application/x-shockwave-flash" class="edui-faked-music" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true" align="none"/>')
                .replace(/<p><br\/><\/p>/g, '');
            return content;
        },
        n2p:function(cc,ubb) {
            var c = '',s = cc.split("[iCMS.N]");
            for (var i = 0; i < s.length; i++) {
                while (s[i].substr(0, 1) == " " || s[i].substr(0, 1) == "　") {
                    s[i] = s[i].substr(1, s[i].length);
                }
                if (s[i].length > 0){
                    if(ubb){
                        c += s[i] + "\n";
                    }else{
                        c += "<p>" + s[i] + "</p>";
                    }
                }
            }
            return c;
        }
    };
})(jQuery);


function pad(num, n) {
    num = num.toString();
    return Array(n > num.length ? (n - ('' + num).length + 1) : 0).join(0) + num;
}

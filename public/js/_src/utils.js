iCMS.define("utils",{
        addcss: function(url, id) {
            url = iCMS.CONFIG.PUBLIC+'/'+url;
            var s = document.createElement("link"), h = document.getElementsByTagName("head")[0];
            s.id = id;
            s.href = url;
            s.type = "text/css";
            s.rel = "stylesheet";
            h.insertBefore(s, h.firstChild);
        },
        addjs: function(name, id) {
            url = iCMS.CONFIG.PUBLIC+'/'+name+'.js';
            var s = document.createElement("script"), h = document.getElementsByTagName("head")[0];
            s.id = id;
            s.src = url;
            h.insertBefore(s, h.firstChild);
        },
        format:function (content,ubb) {
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
                .replace(/<embed[^>]+class="edui-faked-video"[^"].+src=[" ]?([^"]+)[" ]+width=[" ]?([^"]\d+)[" ]+height=[" ]?([^"]\d+)[" ]?[^>]*>/ig, "[video=$2,$3]$1[/video]")
                .replace(/<embed[^>]+class="edui-faked-music"[^"].+src=[" ]?([^"]+)[" ]+width=[" ]?([^"]\d+)[" ]+height=[" ]?([^"]\d+)[" ]?[^>]*>/ig, "[music=$2,$3]$1[/music]")
                .replace(/<b[^>]*>(.*?)<\/b>/ig, "[b]$1[/b]")
                .replace(/<strong[^>]*>(.*?)<\/strong>/ig, "[b]$1[/b]")
                .replace(/<p[^>]*?>/g, "\n\n")
                .replace(/<br[^>]*?>/g, "\n")
                .replace(/<li[^>]*?>/g, "\n")
                .replace(/<[^>]*?>/g, "");

            function n2p(cc,ubb) {
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
            if(ubb){
                content = content.replace(/\n+/g, "[iCMS.N]");
                content = n2p(content,ubb);
                return content;
            }
            content = content.replace(/\[url=([^\]]+)\]\n(\[img\]\1\[\/img\])\n\[\/url\]/g, "$2")
                .replace(/\[img\](.*?)\[\/img\]/ig, '<p><img src="$1" /></p>')
                .replace(/\[b\](.*?)\[\/b\]/ig, '<b>$1</b>')
                .replace(/\[url=([^\]|#]+)\](.*?)\[\/url\]/g, '$2')
                .replace(/\[url=([^\]]+)\](.*?)\[\/url\]/g, '<a target="_blank" href="$1">$2</a>')
               .replace(/\n+/g, "[iCMS.N]");
            content = n2p(content);
            content = content.replace(/#--iCMS.PageBreak--#/g, "<!---->#--iCMS.PageBreak--#")
                .replace(/<p>\s*<p>/g, '<p>')
                .replace(/<\/p>\s*<\/p>/g, '</p>')
                .replace(/<p>\s*<\/p>/g, '')
                .replace(/\[video=(\d+),(\d+)\](.*?)\[\/video\]/ig, '<embed type="application/x-shockwave-flash" class="edui-faked-video" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true"/>')
                .replace(/\[music=(\d+),(\d+)\](.*?)\[\/music\]/ig, '<embed type="application/x-shockwave-flash" class="edui-faked-music" pluginspage="http://www.macromedia.com/go/getflashplayer" src="$3" width="$1" height="$2" wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true" align="none"/>')
                .replace(/<p><br\/><\/p>/g, '');
            return content;
        },
        random: function(len) {
            len = len || 16;
            var chars = "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ",
                code = '';
            for (i = 0; i < len; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length))
            }
            return code;
        },
        callback: function(ret, SUCCESS, FAIL, me, param) {
            var success = SUCCESS || me.SUCCESS
            var fail = FAIL || me.FAIL
            if (ret.code) {
                this.__callback(success,ret,param);
            } else {
                this.__callback(fail,ret,param);
            }
        },
        __callback: function(func,ret,param) {
            if (typeof(func) === "function") {
                func(ret,param);
            } else {
                var msg = ret;
                if (typeof(ret) === "object") {
                    msg = ret.msg || 'error';
                }
                var UI = iCMS.require("ui");
                UI.alert(msg);
            }
        }
});


iCMS.define("user", function(require) {
    var utils = iCMS.require("utils"),
        API = iCMS.require("api"),
        UI = iCMS.require("ui");

    $User = {
        INBOX_URL: iCMS.CONFIG.API + '?app=user&do=manage&pg=inbox',
        widget: {
            loading: '<div class="tip_info">' +
                '<img src="' + iCMS.CONFIG.PUBLIC + '/ui/loading.gif">' +
                '<span> 用户信息加载中……</span>' +
                '</div>'
        }
    };
    return $.extend($User, {
        NOAVATAR: function(img) {
            img.src = iCMS.CONFIG.PUBLIC + '/ui/avatar.gif';
        },
        NOCOVER: function(img, type) {
            var name = 'coverpic';
            if (type == "m") {
                // name = 'm_coverpic';
                name = 'coverpic';
            }
            img.src = iCMS.CONFIG.PUBLIC + '/ui/' + name + '.jpg';
        },
        STATUS: function($param, SUCCESS, FAIL) {
            var me = this;
            $.get(API.url('user', '&do=data'), $param, function(ret) {
                if (ret.code) {
                    $User.data = ret;
                }
                utils.callback(ret, SUCCESS, FAIL, me);
            }, 'json');
        },
        AUTH: function() {
            var cookie = iCMS.require("cookie");
            return cookie.get(iCMS.CONFIG.AUTH) ? true : false;
        },
        CHECK: {
            LOGIN: function() {
                var auth = $User.AUTH();
                if (!auth) {
                    return $User.LOGIN();
                } else {
                    return true;
                }
            }
        },
        UHOME: function(uid) {
            return iCMS.CONFIG.UHOME.replace('{uid}', uid);
        },
        LOGIN: function() {
            window.location.href = API.url('user', "&do=login");
        },
        LOGOUT: function($param, SUCCESS, FAIL) {
            var me = this;
            $.get(API.url('user', "&do=logout"), $param, function(ret) {
                utils.callback(ret, SUCCESS, FAIL, me);
            }, 'json');
        },
        FOLLOW: function(a, SUCCESS, FAIL) {
            var me = this;
            if (!this.CHECK.LOGIN()) return;

            var data = $(a).attr('i').replace('follow:', '').split(":");
            var $param = { 'uid': data[0], 'follow': data[1], 'action': 'follow' }
            $.post(API.url('user'), $param, function(ret) {
                utils.callback(ret, SUCCESS, FAIL, me, $param);
            }, 'json');
        },
        UCARD: function(doc) {
            $("[i^='ucard']",(doc||document)).poshytip({
                idName: 'iCMS-UCARD',
                className: 'iCMS_UI_TOOLTIP',
                alignTo: 'target',
                alignX: 'center',
                liveEvents: true,
                offsetX: 0,
                offsetY: 5,
                fade: false,
                slide: false,
                content: function(update) {
                    var uid = $(this).attr('i').replace('ucard:', '');
                    if (uid) {
                        $.get(API.url('user', "&do=ucard"), { 'uid': uid }, update);
                    }
                    return $User.widget.loading;
                }
            });
        },
        PM: function(a) {
            var me = this;
            if (!this.CHECK.LOGIN()) return;

            var $this = $(a),
                box = document.getElementById("iCMS-PM-DIALOG"),
                dialog = UI.dialog({
                    title: '发送私信',
                    quickClose: false,
                    width: "auto",
                    height: "auto",
                    content: box
                }),
                iv = iCMS.$v(a, 'pm')
            param = { 'uid': iv[0], 'name': iv[1] }
            content = $("[name='content']", box).val('');

            if (iv[2]) {
                param.mid = iv[2];
            }
            $(".pm_warnmsg", box).hide();
            $('.pm_uname', box).text(param.name);

            if ($User.INBOX_URL) {
                $('.pm_inbox', box).attr("href", $User.INBOX_URL);
            } else {
                $('.pm_inbox', box).hide();
            }

            $('.cancel', box).click(function(event) {
                event.preventDefault();
                dialog.remove();
            });
            $('[name="send"]', box).click(function(event) {
                event.preventDefault();
                param.content = content.val();
                if (!param.content) {
                    content.focus();
                    $(".pm_warnmsg", box).show();
                    return false;
                }
                param.action = 'pm';
                $.post(API.url('message'), param, function(c) {
                    dialog.remove();
                    UI.alert(c.msg, c.code);
                }, 'json');
            });
        },
        REPORT: function(a) {
            var me = this;
            if (!this.CHECK.LOGIN()) return;

            var $this = $(a),
                _title = $this.attr('title') || '为什么举报这个评论?',
                box = document.getElementById("iCMS-REPORT-DIALOG"),
                dialog = UI.dialog({
                    title: _title,
                    content: box,
                    quickClose: false,
                    width: "auto",
                    height: "auto"
                });

            $("li", box).click(function(event) {
                event.preventDefault();
                $("li", box).removeClass('checked');
                $(this).addClass('checked');
                //$("[name='reason']",box).prop("checked",false);
                $("[name='reason']", this).prop("checked", true);
            });
            $('.cancel', box).click(function(event) {
                event.preventDefault();
                dialog.remove();
            });
            $('[name="ok"]', box).click(function(event) {
                event.preventDefault();
                var data = API.param($this),
                    content = $("[name='content']", box);
                data.reason = $("[name='reason']:checked", box).val();
                if (!data.reason) {
                    UI.alert("请选择举报的原因");
                    return false;
                }
                if (data.reason == "0") {
                    data.content = content.val();
                    if (!data.content) {
                        UI.alert("请填写举报的原因");
                        return false;
                    }
                }
                data.action = 'report';
                $.post(API.url('user'), data, function(c) {
                    content.val('');
                    $("li", box).removeClass('checked');
                    $("[name='reason']", box).removeAttr('checked');
                    UI.alert(c.msg, c.code);
                    if (c.code) {
                        dialog.remove();
                    }
                }, 'json');
            });
        },

    });
});

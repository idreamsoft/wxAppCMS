iCMS.define("passport", function(require) {
    var API = iCMS.require("api");
    return {
        SUCCESS:{},
        FAIL:{},
        __post: function(param) {
            var me = this;
            $.post(API.url('user'), param, function(ret) {
                me.__callback(ret);
            }, 'json');
        },
        __callback: function(ret, SUCCESS, FAIL) {
            var utils = iCMS.require("utils");
            utils.callback(ret, SUCCESS, FAIL, this);
        },
        LOGIN: function(param) {
            param = $.extend(param, {
                'action': 'login'
            });
            this.__post(param);
        },
        REGISTER: function(param) {
            param = $.extend(param, {
                'action': 'register'
            });
            this.__post(param);
        },
        FINDPWD: function(param) {
            param = $.extend(param, {
                'action': 'findpwd'
            });
            this.__post(param);
        },
        CHECK: function(param, SUCCESS, FAIL) {
            var me = this;
            $.get(API.url('user', "&do=check"), param, function(ret) {
                me.__callback(ret, SUCCESS, FAIL);
            }, 'json');
        }
    };
});

iCMS.define("api",{
        url: function(app, _do) {
            return iCMS.CONFIG.API + '?app=' + app + (_do || '');
        },
        param: function(a) {
            var $a = $(a),$p = $a.parent();
            var _a = this.get_param($a),_b = this.get_param($p);
            return $.extend(_b,_a);
        },
        // params: function(a) {
        //     var $this = $(a),
        //         $parent = $this.parent();
        //     return $.extend(this.param($this), this.param($parent));
        // },
        get_param: function(a, param) {

            var json2str = function (o) {
                var arr = [];
                var fmt = function(s) {
                    if (typeof s == 'object' && s != null) return json2str(s);
                    return /^(string|number)$/.test(typeof s) ? '"' + s + '"' : s;
                }
                for (var i in o)
                    arr.push('"' + i + '":' + fmt(o[i]));
                return '{' + arr.join(',') + '}';
            };

            if (param) {
                a.attr('data-param', json2str(param));
                return;
            }
            var param = a.attr('data-param') || false;
            if (!param) return {};
            return $.parseJSON(param);
        }
});

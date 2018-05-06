iCMS.define("comment", function() {
    var API = iCMS.require("api"),
        UI = iCMS.require("ui"),
        USER = iCMS.require("user"),
        utils = iCMS.require("utils"),

        $COMMENT = {
            seccode: iCMS.CONFIG.COMMENT.seccode,
            page_no: {},
            page_total: {},
            options: {
                more: '<a href="javascript:;" class="load-more" i="comment_load"><span class="text">显示全部评论</a>',
                spinner: $('<div class="commentApp-spinner">正在加载，请稍等...... <i class="spinner-lightgray"></i></div>'),
                up_label: 'comment_up_label',
                up_num: 'comment_up_num',
                list: 'comment_list',
                form: 'comment_form',
            },
            cache: {}
        };

    return $.extend($COMMENT, {
        setopt: function(options) {
            $COMMENT.options = $.extend($COMMENT.options, options);
            return this;
        },
        template: function(name, func) {
            if ($COMMENT.cache[name]) {
                if (typeof(func) === "function") {
                    return func($COMMENT.cache[name]);
                } else {
                    return $COMMENT.cache[name];
                }
            }
            $.get(API.url('comment'), '&do=widget&name=' + name,
                function(tpl) {
                    $COMMENT.cache[name] = tpl;
                    if (typeof(func) === "function") {
                        func(tpl)
                    }
                }
            )
        },
        miniForm: function(callback) {
            $COMMENT.template('mini.form', function(f) {
                var $f = $(f);
                $f.on('focus', '[i="comment_content"]', function(event) {
                    event.preventDefault();
                    $f.addClass('expanded');
                }).on('click', '[i="comment_cancel"]', function(event) {
                    event.preventDefault();
                    $f.removeClass('expanded');
                    $('[i="comment_content"]', $f).val('');
                });
                if (callback) {
                    utils.__callback(callback, $f);
                }
            });
        },
        like: function(a, SUCCESS, FAIL) {
            if (!USER.CHECK.LOGIN()) return;

            var me = this,
                $this = $(a),
                opt = $COMMENT.options,
                param = API.param($this);

            me.SUCCESS = function(ret, p) {
                var c = $this.parent();
                var num = iCMS.$(opt.up_num, c).text();
                num = parseInt(num) + 1;
                iCMS.$(opt.up_label, c).show();
                iCMS.$(opt.up_num, c).text(num);
            };

            param["do"] = 'like';
            $.get(API.url('comment'), param, function(ret) {
                utils.callback(ret, SUCCESS, FAIL, me);
            }, 'json');
        },
        reply: function(a) {
            if (!USER.CHECK.LOGIN()) return;

            var me = this,
                item = $(a).parent().parent(),
                caf = $('.commentApp-form', item);
            if (caf.length > 0) {
                caf.remove();
                return false;
            }

            $('.commentApp-form', '.commentApp-list').remove();
            $('.commentApp-form').removeClass('expanded');

            var param = $(a).param();

            $COMMENT.miniForm(function($f) {
                $f.on('click', '[i="comment_cancel"]', function(event) {
                        event.preventDefault();
                        iCMS.$('comment_content', $f)
                            .data('param', param)
                            .focus()
                            .val("");
                        $f.remove();
                    })
                    //回复评论事件绑定
                    .on('click', '[i="comment_add"]', function(event) {
                        event.preventDefault();
                        $COMMENT.options.list = item.parent();
                        $COMMENT.add(param, $f,null,'after');
                    })
                    .addClass('expanded');

                $(a).parent().after($f);
            });
        },
        add: function(param, container, SUCCESS,listype) {
            //设置表单容器
            $COMMENT.options.form = container;
            //提交表单
            $COMMENT.post(param,
                function(ret) {
                    if (typeof(SUCCESS) === 'function') {
                        SUCCESS(ret);
                    }
                    //加载评论列表模板
                    $COMMENT.template('item');
                    $(".commentApp-item.empty").remove();
                    $COMMENT.getlist(param.iid, ret.forward, (listype||'append'));
                },
                function(ret) {
                    UI.alert(ret.msg);
                }
            );
        },
        post: function(param, SUCCESS, FAIL, container) {
            if (!USER.CHECK.LOGIN()) return;

            var me = this;
            var opt = $COMMENT.options;
            container = container || iCMS.$(opt.form);

            if ($COMMENT.seccode == "1") {
                var comment_seccode = iCMS.$('comment_seccode', container);
                param.seccode = comment_seccode.val();
                if (!param.seccode) {
                    comment_seccode.focus();
                    return false;
                }
            }

            var comment_content = iCMS.$('comment_content', container);
            param.content = comment_content.val();
            if (!param.content) {
                comment_content.focus();
                return false;
            }
            var refresh = function(ret) {
                if (ret.forward != 'seccode') {
                    comment_content.val('');
                }
                if (typeof(comment_seccode) !== "undefined") {
                    comment_seccode.val('');
                    UI.seccode();
                }
            }

            var _param = comment_content.data('param');

            if (typeof(_param) !== "undefined") {
                param = $.extend(param, _param);
            }

            param.action = 'add';
            $.post(API.url('comment'), param, function(ret) {
                refresh(ret);
                utils.callback(ret, SUCCESS, FAIL, me);
            }, 'json');
        },
        page: function(pn, a, func) {
            var $this = $(a),
                p = $this.parent(),
                pp = p.parent(),
                query = p.attr('data-query'),
                url = iCMS.CONFIG.API + '?' + query + '&pn=' + pn;

            $.get(url, function(ret) {
                utils.__callback(func, ret);
            });
        },
        getlist: function(iid, id, type, container) {
            if (!id) {
                $COMMENT.page_no[iid]++;
                if ($COMMENT.page_total[iid]) {
                    if ($COMMENT.page_no[iid] > $COMMENT.page_total[iid]) {
                        return false;
                    }
                }
            }
            var opt = $COMMENT.options;
            container = container || iCMS.$(opt.list);

            $.get(API.url('comment'), {
                    'do': 'json',
                    'iid': iid,
                    'id': (id || 0),
                    'by': 'ASC',
                    page: $COMMENT.page_no[iid]
                },
                function(json) {
                    $COMMENT.options.spinner.remove();

                    if (!json) {
                        return false;
                    }
                    if (!id) {
                        $COMMENT.page_total[iid] = json[0].page.total;
                    }

                    $.each(json, function(i, data) {
                        var item = $.parseTmpl($COMMENT.cache['item'], data);
                        USER.UCARD(item);
                        if (type == "after") {
                            container.after(item);
                        } else if (type == "before") {
                            container.before(item);
                        } else {
                            container.append(item);
                        }
                    });
                    if (!id) {
                        iCMS.$("comment_load").remove();
                        if ($COMMENT.page_no[iid] < $COMMENT.page_total[iid]) {
                            container.after(opt.more);
                        }
                    }
                }, 'json');
        },
        create: function(a) {

            var $this = $(a),
                p = $this.parent(),
                pp = p.parent(),
                param = API.param(p),
                wrap = $('.commentApp-wrap', pp);
            if (wrap.length > 0) {
                wrap.remove();
                return false;
            }

            $('.commentApp-wrap').remove();

            var $spike = $('<i class="ui-icon comment-spike-icon commentApp-bubble"></i>'),
                $wrap = $('<div class="commentApp-wrap">'),
                $list = $('<div class="commentApp-list">'),
                iid = param['iid'];

            var pos = $this.position();
            var _isMobile = 'createTouch' in document && !('onmousemove' in document) ||
                /(iPhone|iPad|iPod)/i.test(navigator.userAgent);

            $spike.css({ 'left': _isMobile ? event.offsetX : pos.left, 'display': 'inline' });

            $wrap.html($COMMENT.options.spinner);
            $wrap.append($spike, $list);

            $COMMENT.options.list = $list;

            $COMMENT.miniForm(function($f) {
                $f.addClass('commentApp-wrap-ft');
                $wrap.append($f);

                //提交评论
                $f.on('click', '[i="comment_add"]', function(event) {
                    event.preventDefault();
                    $COMMENT.add(param, $f,
                        function(ret) {
                            var count = parseInt(iCMS.$('comment_num', $this).text());
                            iCMS.$('comment_num', $this).text(count + 1);
                        }
                    );
                });
            });

            p.after($wrap);

            //加载评论列表模板
            $COMMENT.template('item', function(tmpl) {
                //加载评论
                $COMMENT.page_no[iid] = 0;
                $COMMENT.page_total[iid] = 0;
                $COMMENT.getlist(iid);
            });
            //加载更多
            $wrap.on('click', '[i="comment_load"]', function(event) {
                event.preventDefault();
                $list.append($COMMENT.options.spinner);
                $COMMENT.getlist(iid);
            });
        }
    });
});

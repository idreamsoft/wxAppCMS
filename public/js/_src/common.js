iCMS.define("common", function() {
    var API = iCMS.require("api"),utils = iCMS.require("utils"),USER = iCMS.require("user"),UI = iCMS.require("ui");
    return {
        __post: function(param,uri,SUCCESS,FAIL) {
            var me = this;
            $.post(API.url(uri), param, function(ret) {
                utils.callback(ret, SUCCESS, FAIL,me,param);
            }, 'json');
        },
        vote: function(a, SUCCESS, FAIL) {
            // if (!USER.CHECK.LOGIN()) return;

            var vars = iCMS.$v(a,'vote');
            var param = API.param(a);
            param = $.extend(param, {
                'type': vars[1],
                'action': 'vote'
            });
            this.__post(param,vars[0],SUCCESS,FAIL);
        },
        favorite: function(a,callback) {
            if (!USER.CHECK.LOGIN()) return;

            var me    = this;
            var $this = $(a);
            var $box  = $("#iCMS-FAVORITE-TPL"),tpl = $box.html();
            var $wrap = document.getElementById("iCMS-FAVORITE-DIALOG");
            $wrap.innerHTML = $wrap.innerHTML.replace('data-src', 'src');

            var dialog = UI.dialog({
                title: $box.data("title")||'添加到收藏夹',content:$wrap,
                quickClose: false,width:"auto",height:"auto"
            });

            $('.cancel', $wrap).click(function(event) {
                event.preventDefault();
                dialog.remove();
            });
            $('.create,.cancel_create', $wrap).click(function(event) {
                event.preventDefault();
                if($(this).hasClass('create')){
                    dialog.title($box.data("create-title")||"创建新收藏夹");
                }else{
                    dialog.title($box.data("title")||"添加到收藏夹");
                }
                $(".favorite_create",$wrap).toggle();
                $(".favorite_list",$wrap).toggle();
            });

            $('[name="create"]', $wrap).click(function(event){
                event.preventDefault();
                var data = {
                    'action':'create',
                    'title':$('[name="title"]',$wrap).val(),
                    'description':$('[name="description"]',$wrap).val(),
                    'mode':$('[name="mode"]:checked',$wrap).val()
                }
                if(data.title==""){
                    $('[name="title"]',$wrap).focus();
                    $('.favorite_create_error',$wrap).text('请填写标题').show();
                    return false;
                }
                $.post(API.url('favorite'), data, function(c) {
                    if(c.code){
                        var item = $.parseTmpl(tpl,{
                            'id':c.forward,'title':data.title,
                            'count':0,'follow':0,'favorite':false
                        });
                        $(".favorite_list_content",$wrap).append(item);
                        $('[name="title"]',$wrap).val('');
                        $('[name="description"]',$wrap).val('');
                        $(".favorite_create",$wrap).toggle();
                        $(".favorite_list",$wrap).toggle();
                        dialog.reset();
                    }else{
                        $('.favorite_create_error',$wrap).text(c.msg).show();
                    }
                }, 'json');
            });

            $.post(API.url('favorite',"&do=list"),API.param(a),function(json) {
                var item ='';
                $.each(json, function(i,val){
                    item+=$.parseTmpl(tpl,val);
                });
                $(".favorite_list_content",$wrap).html(item);
                dialog.reset();
            },'json');

            $($wrap).on("click", '.favo-list-item-link', function(event) {
                //console.log(this);
                var $this = $(this),
                data = API.param(a),
                num  = parseInt($('.num',$this).text());
                data.fid    = $this.attr('data-fid');
                if($this.hasClass('active')){
                    data.action = 'delete';
                }else{
                    data.action = 'add';
                }
                $.post(API.url('favorite'),data,function(c) {
                    if(c.code){
                        if($this.hasClass('active')){
                            $('.num',$this).text(num-1);
                            $this.removeClass('active');
                        }else{
                            $('.num',$this).text(num+1);
                            $this.addClass('active');
                        }
                    }else{
                        UI.alert(c.msg);
                    }
                },'json');
            });
        },
    };
});

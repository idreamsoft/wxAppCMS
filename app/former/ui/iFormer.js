var iFormer = {
    ui:{
        class:'iFormer-ui',
        fbox:null
    },
    FieldType:{
        'number':['BIGINT','INT','MEDIUMINT','SMALLINT','TINYINT']
    },
    editor_callback:{
        validate:function(e, p) {
          $(".v_selected").removeClass('v_selected');
          $("option:selected","#iFormer-validate").each(function(){
            var value_el = $("#iFormer-validate-"+this.value);
            if(value_el.length > 0 ) {
              value_el.show().addClass('v_selected');
            }
          });
          $("[id^='iFormer-validate-']").each(function(index, el) {
            if(!$(this).hasClass('v_selected')){
              $(this).hide();
              $("input",this).val('');
            }
          });
        }
    },
    sort_option: function(e, v) {
        var option = e.find('option[value="' + v + '"]').clone();
        option.attr('selected', 'selected');
        return option;
    },
    sort_value: function(a,e,p) {
        var s_name = a.id.replace('iFormer-',''),select = $("#sort-"+s_name);
        if(p['selected']){
          select.append(iFormer.sort_option($(a),p['selected']));
        }
        if(p['deselected']){
          select.find('option[value="' + p['deselected'] + '"]').remove();
        }
        if(typeof(iFormer.editor_callback[s_name])==='function'){
          iFormer.editor_callback[s_name](e, p);
        }
    },
    sort_select: function($fbox) {
        $('.chosen-select[multiple="multiple"]',$fbox).each(function(index, select) {
            var name = this.id.replace('iFormer-', '');
            $("#sort-"+name, $fbox).html('');
            $(this).on('change', function(e, p) {
                iFormer.sort_value(this,e,p);
            });
        });
    },
    widget: function(t) {
        var element = {
            input: '<input/>',
            textarea: '<textarea/>',
            btngroup: function (text,$type) {
                var btngroup = iFormer.widget('div').addClass('btn-group btn-group-vertical');
                if($type){
                    btngroup.append('<a class="btn"><i class="fa fa-'+$type+'"></i> '+text+'</a>');
                }else{
                    btngroup.append('<a class="btn dropdown-toggle"><span class="caret"></span> '+text+'</a>');
                }
                return btngroup;
            }
        };
        if(typeof(element[t])==="function"){
            return element[t];
        }
        if(element[t]){
          return $(element[t]);
        }else{
          return $('<'+t+'/>');
        }
    },
    /**
     * 生成HTML
     * @param  {[type]} helper [表单]
     * @param  {[type]} obj    [生成表单数组]
     * @param  {[type]} data   [fields字符串]
     * @param  {[type]} origin [原字段名]
     * @param  {[type]} readonly [只读]
     * @return {[type]}        [description]
     */
    render: function(helper,obj,data,origin,readonly) {
        var me = this;
        var $container = this.widget('div').addClass(this.ui.class);

        if (obj['type'] == 'br') {
            data = 'UI:BR';
            $container.addClass('pagebreak');
            $container.dblclick(function(event) {
                $container.remove();
            });
        }else{
            var $div  = this.widget('div').addClass('input-prepend'),
                $label= this.widget('span').addClass('add-on'),
                $help = this.widget('span').addClass('help-inline');
                // $comment = this.widget('span').addClass('help-inline');


            var $elem = this.widget('input'),
            elem_type = 'text';
            // elem_class = obj['class'];
            var obj_type = obj['type'],type_addons;
            if(obj_type.indexOf(':')!="-1"){
                var typeArray = obj_type.split(':');
                obj_type  = typeArray[0];
                type_addons = typeArray[1];
            }
            switch (obj_type) {
                case 'tpldir':
                case 'tplfile':
                    var div_after = function () {
                        var btngroup = iFormer.widget('btngroup');
                        $div.addClass('input-append');
                        $div.append(btngroup('选择','search'));
                    }
                break;
                case 'multi_image':
                case 'multi_file':
                    $elem = this.widget('textarea');
                    elem_type = null;
                case 'text_prop':
                case 'file':
                case 'image':
                    var eText = {
                        prop:'选择属性',
                        image:'图片上传',
                        multi_image:'多图上传',
                        file:'文件上传',
                        multi_file:'批量上传',
                    }
                    if(obj_type!='prop'){
                        obj['class'] = obj['class']||"span6";
                    }
                    var div_after = function () {
                        var btngroup = iFormer.widget('btngroup');
                        $div.addClass('input-append');
                        $div.append(btngroup(eText[obj_type]));
                    }

                break;
                case 'seccode':
                    $elem.addClass('seccode').attr('maxlength',"4");
                    obj['validate'] = ["empty"];
                    var div_after = function () {
                        var span = iFormer.widget('span').addClass('add-on');
                        span.append(
                            '<img src="'+iCMS.config.API+'?do=seccode" alt="验证码" class="seccode-img r3">'+
                            '<a href="javascript:;" class="seccode-text">换一张</a>'
                        );
                        $div.addClass('input-append');
                        $div.append(span);
                    }
                break;
                case 'multitext':
                    obj['class'] = obj['class']||"span12";
                case 'textarea':
                    $elem = this.widget('textarea');
                    obj['class'] = obj['class']||"span6";
                    $elem.css('height','300px');
                    elem_type = null;
                break;
                case 'markdown':
                case 'editor':
                    $elem = this.widget('textarea').hide();
                    var div_after = function () {
                        var img = iFormer.widget('img');
                        img.prop('src', './app/former/ui/img/'+obj_type+'.png');
                        $div.append(img);
                    }
                    elem_type = null;
                break;

                case 'PRIMARY':
                case 'user_category':
                case 'userid':
                case 'union':
                case 'hidden':
                    var div_after = function () {
                        var $span;
                        if(obj_type=="PRIMARY"){
                            $span = iFormer.widget('span').addClass('label label-important').text('主键 自增ID');
                        }
                        me.hidden($elem,$div,$span);
                    }
                case 'text':
                    if(obj['len']=="5120"){
                        obj['class'] = obj['class']||'span12';
                    }
                break;
                case 'switch':
                case 'radio':
                case 'checkbox':
                    obj['class'] = obj['class']||obj_type;
                    elem_type = obj_type;
                    if(obj_type=='switch'){
                        obj['class'] = 'radio';
                        elem_type = 'radio';
                    }
                    if(obj_type=='checkbox'){
                        obj['multiple'] = true;
                    }
                    //改变$div内容
                    var _div = function () {
                        var parent = iFormer.widget('span').addClass('add-on');
                        var field_option = function () {
                            var optionText = obj['option'].replace(/(\n)+|(\r\n)+/g, "");
                            optionArray = optionText.split(";");
                            $.each(optionArray, function(index, val) {
                                if(val){
                                    var ov = val.split("=");
                                    var aa = me.widget('input').attr('type', elem_type);
                                    parent.append(ov[0],aa,' ');
                                }
                            });
                        };
                        if(obj['option']){
                            field_option();
                            $elem.hide();
                        }
                        parent.append($elem);
                        $div.addClass('input-append');
                        $div.append($label,parent);
                    }
                break;
                case 'multi_prop':
                case 'multi_category':
                case 'multiple':
                case 'prop':
                case 'category':
                case 'select':
                    $elem = this.widget('select');
                    var div_after = function () {
                        var eText = {
                            prop:'属性',
                            multi_prop:'属性',
                            category:'栏目',
                            multi_category:'栏目',
                        }
                        if(eText[obj_type]){
                            var $span = iFormer.widget('span').addClass('label label-info label-tip').text(eText[obj_type]);
                            $('.add-on:first',$div).append($span);
                        }
                    }
                    if(obj_type.indexOf("multi")!='-1'){
                        obj['multiple'] = true;
                        $elem.attr('multiple',true);
                    }
                    obj['class'] = obj['class']||'span6 chosen-select';
                    var field_option = function () {
                        // console.log(obj['option']);
                        var optionText = obj['option'].replace(/(\n)+|(\r\n)+/g, "");
                        optionArray = optionText.split(";");
                        $.each(optionArray, function(index, val) {
                            if(val){
                                var ov = val.split("=");
                                $elem.append('<option value="'+ov[1]+'">'+ov[0]+'</option>');
                            }
                        });
                    };
                    if(obj['option']){
                        field_option();
                    }
                    // console.log(obj);
                    elem_type = null;
                break;
                case 'number':
                    if(obj['len']=="1"){
                        obj['class'] = obj['class']||'span3';
                    }
                    if(obj['len']=="10"){
                        // obj['class'] = obj['class']||'span3';
                    }
                break;
                case 'currency':
                case 'percentage':
                    //追加$div内容
                    var div_after = function () {
                        var label2 = iFormer.widget('span').addClass('add-on');
                        label2.append(obj['label-after']);
                        $div.append(label2).addClass('input-append');
                    }
                break;
                case 'decimal':
                break;
            }
            if(type_addons=='hidden'){
                var div_after = function () {
                    me.hidden($elem,$div);
                }
            }
            obj['class'] = obj['class']||'span3';

            //整数类型 默认无符号
            if($.inArray(obj['field'], iFormer.FieldType['number'])>0){
                if(typeof(obj['unsigned'])=="undefined"){
                    obj['unsigned'] = '1'
                }
            }
            /**
             * 生成器字段样式展现
             */
            $elem.attr({
                'id': '_field_'+obj['id'],
                'name': '_field_'+obj['name'],
                'class': obj['class'],
                'value': obj['default']|| '',
            });

            if(elem_type){
                $elem.attr({'type': elem_type,});
            }

            $label.text(obj['label']);
            $help.text(obj['help']);
            // $comment.text(obj['comment']);

            if(typeof(_div)==="function"){
                _div();
            }else{
                $div.append($label,$elem);
            }

            if(typeof(div_after)==="function"){
                div_after();
            }

            $container.append($div,$help);
            if(readonly){
                $container.addClass('iFormer-base-field');
            }else{
                iFormer.action_btn($container,$div);
            }
        }

        if(origin){
            var $origin = this.widget('input').prop({
                'type':'hidden',
                'name':'origin['+origin+']',
                'value':obj['id']
            });
            $container.append($origin);
        }

        data = data||this.url_encode(obj);

        iFormer.fields(data,$container);

        $(':checkbox,:radio',$container).uniform();


        // $container.dblclick(function(event) {
        //     iFormer.edit($container);
        // });

        return $container;
    },
    /**
     * 字段数据
     * @param  {[type]} obj  [数组]
     * @param  {[type]} data [字符串]
     * @param  {[type]} $container
     * @return {[type]}      [description]
     */
    fields:function(data,$container) {
        var $fields = this.widget('input').prop({'type':'hidden','name':'fields[]'});
        // if(data){
            $fields.val(data);
        // }else{
        //     $fields.val(this.url_encode(obj));
        // }
        $container.append($fields);
    },
    /**
     * 字段 编辑/删除 按钮
     * @param  {[type]} $container [description]
     * @param  {[type]} $div [description]
     * @return {[type]}            [description]
     */
    action_btn:function($container,$div) {
        var $action    = this.widget('span').addClass('action'),
            $edit      = this.widget('a').addClass('fa fa-edit'),
            $del       = this.widget('a').addClass('fa fa-trash-o');

        $action.append($edit,$del);

        $edit.click(function(event) {
            iFormer.edit($container);
        }).attr('href','javascript:;');

        $del.click(function(event) {
            $container.remove();
        }).attr('href','javascript:;');

        $div.after($action);
        // return $action;
    },
    hidden:function ($elem,$div,$span) {
        var parent = iFormer.widget('span').addClass('add-on');
        var info = iFormer.widget('span').addClass('label label-info').text('隐藏字段');
        if($span){
            parent.append($span);
        }
        parent.append(info);
        $elem.hide();
        $div.append(parent);
        $div.addClass('input-append');
    },
    url_encode:function(param, key) {
      if(param==null) return '';

      var query = [],t = typeof (param);
      if (t == 'string' || t == 'number' || t == 'boolean') {
        query.push(key + '=' + encodeURIComponent(param));
      } else {
        for (var i in param) {
          var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
          var q = this.url_encode(param[i], k);
          if(q!=='') query.push(q);
        }
      }
      return query.join('&');
    },
    url_decode: function(query) {
        var args = [],pairs = query.split("&");
        for (var i = 0; i < pairs.length; i++) {
            var pos = pairs[i].indexOf('=');
            if (pos == -1) continue;
            var argname = pairs[i].substring(0, pos);
            argname = argname.replace(/\+/g, '%20');
            argname = decodeURIComponent(argname);
            var value = pairs[i].substring(pos + 1);
            value = value.replace(/\+/g, '%20');
            value = decodeURIComponent(value);

            if(argname.indexOf('[')!=-1){
              argname = argname.replace(/\[\d+\]/g, "[]");
              argname = argname.replace('[]', '');
              if(!args[argname]){
                args[argname] = [];
              }
              args[argname].push(value);
            }else{
              args[argname] = value;
            }
        };
        return args; // Return the object
    },
    callback: function(func,ret,param) {
        if (typeof(func) === "function") {
            func(ret,param);
        } else {
            var msg = ret;
            if (typeof(ret) === "object") {
                msg = ret.msg || 'error';
            }
            var UI = require("ui");
            UI.alert(msg);
        }
    },
    /**
     * 重置表单
     * @param  {[type]} a [description]
     * @return {[type]}   [description]
     */
    freset: function(a) {
        //form嵌套下出错
        document.getElementById("iFormer-field-form").reset();
        $(".chosen-select", $(a)).chosen("destroy").find('option').removeAttr('selected')
        $.uniform.restore('.uniform');
    },
    /**
     * 编辑字段
     * @param  {[type]} $container [description]
     * @return {[type]}            [description]
     */
    edit: function($container) {
        // $container.dblclick(function(event) {
            // window.event.preventDefault();
            var me = $(this),
            data   = $("[name='fields[]']",$container).val(),
            origin  = $("[name^='origin']",$container).val(),
            obj    = iFormer.url_decode(data);
            // console.log(obj);
            iFormer.edit_dialog(obj,
                function(param,qt) {
                    var render = iFormer.render($container,param,qt,origin);
                    $container.replaceWith(render);
                }
            );
        // });
    },

    /**
     * 字段编辑框
     * @param  {[type]}   obj      [description]
     * @param  {Function} callback [description]
     * @return {[type]}            [description]
     */
    edit_dialog: function(obj, callback) {
        var me = this,_id = obj['id'];
        var fbox = document.getElementById("iFormer-field-editor");
        var $fbox = $(fbox);
        $(".chosen-select",$fbox).chosen(chosen_config);
        $('.uniform',$fbox).uniform();

        iFormer.sort_select($fbox);

        for(var name in obj) {

            // if(i=='func'){
            //     continue;
            // }
            if(name=='javascript'){
                obj[name] = obj[name].replace(/\\(['|"])/g,"$1");
            }
            var ifn = $("#iFormer-"+name, $fbox);
            // console.log(i,obj[name],typeof(obj[name]));
            // if(typeof(obj[name])==='object'){
            if(ifn.hasClass('chosen-select')){
                // ifn.trigger("chosen:updated");
                // 多选排序
                // console.log(ifn);
                if(ifn.attr('multiple')){
                    ifn.setSelectionOrder(obj[name], true);
                }else{
                    ifn.val(obj[name]).trigger("chosen:updated");
                }
                if ($("#sort-"+name, $fbox).length > 0 ) {
                    $.each(obj[name], function(ii, v) {
                        $("#sort-"+name, $fbox).append(iFormer.sort_option(ifn,v));
                    });
                }
            }else{
                if (ifn.length > 0 ) {
                    ifn.val(obj[name]);
                }
            }
        }


        //整数类型 显示unsigned
        if($.inArray(obj['field'], iFormer.FieldType['number'])>0){
            $('.unsigned-wrap', $fbox).show();
            $('[name="unsigned"][value="'+obj['unsigned']+'"]', $fbox).prop("checked", true);
            $.uniform.update('[name="unsigned"]');
        }

        if(obj['validate']){
            $.each(obj['validate'], function(i, v) {
                if ($("#iFormer-validate-"+v).length > 0 ) {
                    $("#iFormer-validate-"+v).show();
                    if($.isArray(obj[v])){
                        $.each(obj[v], function(index, val) {
                            $('[name="'+v+'['+index+']"]').val(val);
                        });
                    }else{
                        if(v=='defined'){
                            obj[v] = obj[v].replace(/\\(['|"])/g,"$1");
                        }
                        $('[name="'+v+'"]').val(obj[v]);
                    }

                }
            });
        }

        $("#iFormer-label-after-wrap", $fbox).hide();

        if(obj['label-after']){
            $("#iFormer-label-after-wrap", $fbox).show();
        }
        if(obj['type']=='radio'||obj['type']=='checkbox'||obj['type']=='select'||obj['type']=='multiple'){
            $("#iFormer-option-wrap", $fbox).show();
            $("[name='option']").removeAttr('disabled');
        }else{
            $("#iFormer-option-wrap", $fbox).hide();
            $("[name='option']").attr("disabled",true);
        }

        return iCMS.dialog({
            id: 'apps-field-dialog',
            title: 'iCMS - 表单字段设置',
            content: fbox,
            okValue: '确定',
            ok: function() {
                //更新字段展现
                var data = $.extend(obj,{
                    'label': $("#iFormer-label", $fbox).val(),
                    'type': $("#iFormer-type", $fbox).val(),
                    'name': $("#iFormer-name", $fbox).val(),
                    'class': $("#iFormer-class", $fbox).val(),
                    'comment': $("#iFormer-comment", $fbox).val(),
                    'option': $("#iFormer-option", $fbox).val(),
                    'help': $("#iFormer-help", $fbox).val(),
                    'label-after': $("#iFormer-label-after", $fbox).val(),
                    'default': $("#iFormer-default", $fbox).val()
                });


                if(!data.label){
                  iCMS.alert("请填写字段名称!");
                  return false;
                }
                if(!data.name){
                  iCMS.alert("请填写字段名!");
                  return false;
                }
                if(data['id']!= data['name']){
                    data['id'] = data['name'];
                    $("#iFormer-id", $fbox).val(data['id']);
                }
                var $apptype = $('[name="apptype"]').val();
                if($apptype=="2"){
                    var dname = $('[name="_field_'+data.name+'"]','.iFormer-layout').not('[name="_field_'+_id+'"]');
                }else{
                    var dname = $('td[field="'+data.name+'"]','.app-table-list').not('td[field="'+_id+'"]');
                }

                if(dname.length){
                    iCMS.alert("该字段名已经存在,请重新填写");
                    return false;
                }

                $('td[field="'+_id+'"]').attr('field', data.name).text(data.name);

                //更新 fields[]
                param = $("form", $fbox).serialize();
                param = param.replace(/\+/g, '%20');
                callback(data,param);
                me.freset(fbox);
                return true;
            },
            cancelValue: '取消',
            cancel: function() {
                me.freset(fbox);
                return true;
            }
        });
    }
};


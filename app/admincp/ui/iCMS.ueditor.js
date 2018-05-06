(function() {
    var URL = window.iCMS.config.UI+'/ueditor/';
    window.UEDITOR_CONFIG = {
        UEDITOR_HOME_URL: URL
        ,iCMS_PUBLIC_URL:window.iCMS.config.PUBLIC
        ,catchRemoteImageEnable:window.catchRemoteImageEnable||false //远程图片本地化
        ,serverUrl: window.iCMS.config.API + '?app=editor'
        ,toolbars: [
        [
            'fullscreen', 'source', 'print', 'preview', 'cleardoc', 'insertcode', '|',
            'pasteplain', 'selectall', 'undo', 'redo', 'searchreplace', '|',
            'insertorderedlist', 'insertunorderedlist', '|',
            'unlink', 'link', '|',
            'simpleupload','insertimage', 'music', 'insertvideo', 'attachment', 'scrawl', 'wordimage', 'map', '|',
            'date', 'time', '|',
            'horizontal', 'spechars', 'blockquote', 'highlightcode', '|',
            'formatmatch', 'removeformat', 'autotypeset', '|',
            'template', 'pagebreak', '|','drafts'
        ], [
            'paragraph', 'fontfamily', 'fontsize', '|',
            'bold', 'italic', 'underline', 'strikethrough',
            'superscript', 'subscript', 'touppercase', 'tolowercase', '|',
            'forecolor', 'backcolor', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'rowspacingbottom', 'rowspacingtop', 'lineheight', '|',
            'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'help'
            ]
        ]

    ,imageManagerEnable:true //图片在线管理,默认开启 iCMS
    ,textarea:'body' // 提交表单时，服务器获取编辑器提交内容的所用的参数，多实例时可以给容器name属性，会将name给定的值最为每个实例的键值，不用每次实例化的时候都设置这个值

    ,initialContent:''    //初始化编辑器的内容,也可以通过textarea/script给值，看官网例子

    //,autoClearinitialContent:true //是否自动清除编辑器初始内容，注意：如果focus属性设置为true,这个也为真，那么编辑器一上来就会触发导致初始化的内容看不到了
    //,focus:false //初始化时，是否让编辑器获得焦点true或false

    ,initialFrameWidth:"100%"  //初始化编辑器宽度,默认1000
    ,initialFrameHeight:520  //初始化编辑器高度,默认320

    //启用自动保存
    ,enableAutoSave: true
    //自动保存间隔时间， 单位ms
    ,saveInterval: 500

    //,imagePopup:true      //图片操作的浮层开关，默认打开

    //,autoSyncData:true //自动同步编辑器要提交的数据

    //粘贴只保留标签，去除标签所有属性
    //,retainOnlyLabelPasted: false

    //,allHtmlEnabled:false //提交到后台的数据是否包含整个html字符串

    //打开右键菜单功能
    //,enableContextMenu: true
    //右键菜单的内容，可以参考plugins/contextmenu.js里边的默认菜单的例子，label留空支持国际化，否则以此配置为准
    //,contextMenu:[
    //    {
    //        label:'',       //显示的名称
    //        cmdName:'selectall',//执行的command命令，当点击这个右键菜单时
    //        //exec可选，有了exec就会在点击时执行这个function，优先级高于cmdName
    //        exec:function () {
    //            //this是当前编辑器的实例
    //            //this.ui._dialogs['inserttableDialog'].open();
    //        }
    //    }
    //]
    //
    //快捷菜单
    //,shortcutMenu:["fontfamily", "fontsize", "bold", "italic", "underline", "forecolor", "backcolor", "insertorderedlist", "insertunorderedlist"]

    //,themePath:URL +"themes/"
    //wordCount
    ,wordCount:true          //是否开启字数统计
    ,maximumWords:500000       //允许的最大字符数
    //removeFormat
    //清除格式时可以删除的标签和属性
    //removeForamtTags标签
    //,removeFormatTags:'b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var'
    //removeFormatAttributes属性
    ,removeFormatAttributes:'class,style,lang,width,height,align,hspace,valign'

    //pageBreakTag
    //分页标识符,默认是_ueditor_page_break_tag_
    ,pageBreakTag:'#--iCMS.PageBreak--#'

    //autotypeset
    //自动排版参数
    ,autotypeset: {
       mergeEmptyline: true,           //合并空行
       removeClass: true,              //去掉冗余的class
       removeEmptyline: false,         //去掉空行
       // textAlign:"left",               //段落的排版方式，可以是 left,right,center,justify 去掉这个属性表示不执行排版
       // imageBlockLine: 'center',       //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
       pasteFilter: true,             //根据规则过滤没事粘贴进来的内容
       clearFontSize: true,           //去掉所有的内嵌字号，使用编辑器默认的字号
       clearFontFamily: true,         //去掉所有的内嵌字体，使用编辑器默认的字体
       removeEmptyNode: true,         // 去掉空节点
       //可以去掉的标签
       removeTagNames: {div:1},
       indent: false,                  // 行首缩进
       indentValue : '2em',            //行首缩进的大小
       bdc2sb: false,
       tobdc: false
    }

    //sourceEditor
    //源码的查看方式,codemirror 是代码高亮，textarea是文本框,默认是codemirror
    //注意默认codemirror只能在ie8+和非ie中使用
    ,sourceEditor:"codemirror"
    //如果sourceEditor是codemirror，还用配置一下两个参数
    //codeMirrorJsUrl js加载的路径，默认是 URL + "third-party/codemirror/codemirror.js"
    //codeMirrorCssUrl css加载的路径，默认是 URL + "third-party/codemirror/codemirror.css"
    //编辑器初始化完成后是否进入源码模式，默认为否。
    //,sourceEditorFirst:false
    };

    iCMS.editor = {
        eid:'ueditor',
        container:[],
        get:function(eid) {
            if(eid) this.eid = eid;
            var ed  = this.container[this.eid]||this.create();
            return ed;
        },
        create:function(eid,config) {
            if(eid) this.eid = eid;
            var ed = UE.getEditor(this.eid,config);
            this.container[this.eid] = ed;
            return ed;
        },
        destroy:function(eid) {
            eid = eid||this.eid;
            setTimeout(function(){
                UE.delEditor(eid);
            },200);
            this.container[eid] = null;
        },
        insPageBreak:function (argument) {
            var ed = this.get();
            ed.execCommand('pagebreak');
            ed.focus();
        },
        delPageBreakflag:function() {
            var ed = this.get(), html = ed.getContent();
            html = html.replace(/#--iCMS.PageBreak--#/g, '');
            ed.setContent(html);
            ed.focus();
        },
        cleanup:function() {
            if($.isEmptyObject(this.container)){
                iCMS.UI.alert("没找到可用编辑器");
            }else{
                var ed = this.get(), html = ed.getContent();
                html = iCMS.format(html);
                ed.setContent(html);
                ed.focus();
            }
        }
    };

})();

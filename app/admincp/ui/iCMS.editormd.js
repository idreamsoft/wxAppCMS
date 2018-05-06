(function() {
    window.MD_CONFIG = {
            width: "100%",
            height: 740,
            path : window.iCMS.config.UI+'/editor.md/lib/',
            // theme : "dark",
            // previewTheme : "dark",
            // editorTheme : "pastel-on-dark",
            // markdown : md,
            codeFold : true,
            //syncScrolling : false,
            saveHTMLToTextarea : true,    // 保存 HTML 到 Textarea
            searchReplace : true,
            //watch : false,                // 关闭实时预览
            htmlDecode : "style,script,iframe|on*",            // 开启 HTML 标签解析，为了安全性，默认不开启
            //toolbar  : false,             //关闭工具栏
            //previewCodeHighlight : false, // 关闭预览 HTML 的代码块高亮，默认开启
            taskList : true,
            tocm : true,         // Using [TOCM]
            tex : true,                   // 开启科学公式TeX语言支持，默认关闭
            flowChart : true,             // 开启流程图支持，默认关闭
            sequenceDiagram : true,       // 开启时序/序列图支持，默认关闭,
            //dialogLockScreen : false,   // 设置弹出层对话框不锁屏，全局通用，默认为true
            //dialogShowMask : false,     // 设置弹出层对话框显示透明遮罩层，全局通用，默认为true
            //dialogDraggable : false,    // 设置弹出层对话框不可拖动，全局通用，默认为true
            //dialogMaskOpacity : 0.4,    // 设置透明遮罩层的透明度，全局通用，默认值为0.1
            //dialogMaskBgColor : "#000", // 设置透明遮罩层的背景颜色，全局通用，默认为#fff
            imageUpload : true,
            imageFormats : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
            imageUploadURL : window.iCMS.config.API + '?app=editor&do=md_uploadimage',
            onload : function() {
                // console.log('onload', this);
                //this.fullscreen();
                //this.unwatch();
                //this.watch().fullscreen();

                //this.setMarkdown("#PHP");
                //this.width("100%");
                //this.height(480);
                //this.resize("100%", 640);
            }
    };
    editormd.katexURL  = {
        css : "//cdn.bootcss.com/KaTeX/0.7.1/katex.min",
        js  : "//cdn.bootcss.com/KaTeX/0.7.1/katex.min"
    };
    iCMS.editormd = {
        eid:'meditor',
        container:[],
        get:function(eid) {
            if(eid) this.eid = eid;
            var ed  = this.container[this.eid]||this.create();
            return ed;
        },
        create:function(eid) {
            if(eid) this.eid = eid;
            var ed  = editormd(this.eid, window.MD_CONFIG);
            this.container[this.eid] = ed;
            return ed;
        },
        destroy:function(eid) {
            eid = eid||this.eid;
            setTimeout(function(){
                this.get(eid).editor.remove();
            },200);
            this.container[eid] = null;
        },
        insPageBreak:function (argument) {
        var ed = this.get();
            ed.insertValue("\n#--iCMS.PageBreak--#\n");
            ed.focus();
        },
        delPageBreakflag:function() {
            var ed = this.get(), html = ed.getMarkdown();
            html = html.replace(/#--iCMS\.PageBreak--#/g, '');
            ed.setMarkdown(html);
            ed.focus();
        },
        cleanup:function(eid) {
            // if(eid){
            //     var ed = editormd('iCMS-editor-'+eid, MD_CONFIG)
            // }else{
            //     var ed = this.container[this.id];
            // }
            // var html = iCMS.format(ed.getMarkdown());
            // ed.setMarkdown(html);
            // //ed.execCommand("autoTypeset");
            // ed.focus();
        },
    };
})();


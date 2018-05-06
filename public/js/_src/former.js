iCMS.define("former",{
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
});

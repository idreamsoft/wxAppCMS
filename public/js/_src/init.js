window.iCMS = $.extend(window.iCMS,{
    API:iCMS.require("api"),
    init: function(options) {
        var config      = iCMS.require("config");
        iCMS.CONFIG     = $.extend(config,options);
        iCMS.UI         = iCMS.require("ui");
        iCMS.FORMER     = iCMS.require("former");
        iCMS.dialog     = iCMS.UI.dialog;
        iCMS.alert      = iCMS.UI.alert;
    }
});

(function($) {
    $.fn.param = function() {
        return window.iCMS.API.param(this);
    }
})(jQuery);

// function $i(i,doc) {
//     return window.iCMS.$(i,doc);
// }

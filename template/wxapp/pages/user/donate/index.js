let $APP = getApp();
let $iCMS = $APP.iCMS();

$iCMS.addData({
    tabs: ["我发出的赞赏", "我接到的赞赏"],
    activeIndex: 0,
    sliderOffset: 0,
    sliderLeft: 0,
    sliderWidth: 25,
    donate: [],
    receive: [],
});
$iCMS.load = function(options) {
    var that = this;
    wx.getSystemInfo({
        success: function(res) {
            var psw = 100 / that.data.tabs.length;
            var width = res.windowWidth * (psw / 100);
            that.setData({
                sliderWidth: psw,
                sliderLeft: (res.windowWidth / that.data.tabs.length - width) / 2,
                sliderOffset: res.windowWidth / that.data.tabs.length * that.data.activeIndex            });
        }
    });
}
$iCMS.main = function() {
    this.getList(0);
}
$iCMS.getList = function(type) {
    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'index', { 'tpl': 'donate.my', type: type }
    )
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        that.setData({
            result: res.result
        });
    });
}

$iCMS.tabClick = function(e) {
    this.setData({
        sliderOffset: e.currentTarget.offsetLeft,
        activeIndex: e.currentTarget.id
    });
    this.getList(e.currentTarget.id);
}
$iCMS.run();

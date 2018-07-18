let $iCMS = getApp().iCMS();

$iCMS.addData({
    subTitle: '最新资讯',
    inputShowed: false,
    inputVal: null,
    tag_list: []
});

$iCMS.showInput = function() {
    this.setData({
        inputShowed: true
    });
}
$iCMS.hideInput = function() {
    this.setData({
        inputVal: "",
        inputShowed: false
    });
}
$iCMS.clearInput = function() {
    this.setData({
        inputVal: ""
    });
}
$iCMS.searchAction = function(e) {
    this.setData({
        inputVal: e.detail.value
    });
    wx.navigateTo({
        url: '../search/search?q=' + e.detail.value
    })
}

$iCMS.main = function(options) {
    wx.setNavigationBarTitle({
        title: '搜索 - ' + this.$globalData.appInfo.name
    });
    let that = this;
    let $url = this.iURL.make(
        'index', { tpl: 'search.index' }
    );
    this.GET($url).then(res => {
        that.page_loading(false, true);
        that.setData({
            tag_list: res.tag_list
        });
    });
}
$iCMS.run();

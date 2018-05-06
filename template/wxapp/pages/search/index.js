let $wxAppCMS = getApp().wxAppCMS();

$wxAppCMS.addData({
    subTitle: '最新资讯',
    inputShowed: false,
    inputVal: null,
    tag_list: []
});

$wxAppCMS.showInput = function() {
    this.setData({
        inputShowed: true
    });
}
$wxAppCMS.hideInput = function() {
    this.setData({
        inputVal: "",
        inputShowed: false
    });
}
$wxAppCMS.clearInput = function() {
    this.setData({
        inputVal: ""
    });
}
$wxAppCMS.searchAction = function(e) {
    this.setData({
        inputVal: e.detail.value
    });
    wx.navigateTo({
        url: '../search/search?q=' + e.detail.value
    })
}

$wxAppCMS.main = function(options) {
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

    this.setData({
        APP: this.$globalData.appInfo
    });
}
$wxAppCMS.run();

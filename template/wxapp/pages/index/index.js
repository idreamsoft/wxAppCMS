let $wxAppCMS = getApp().wxAppCMS();

$wxAppCMS.addData({
    subTitle: '最新资讯',
    article_list: [],
    banner: []
});

$wxAppCMS.getList = function() {
    if (this.data.page_last) return;
    if(this.ONESELF) return;

    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'index', { page: this.data.page_no }
    )
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        if (res.banner) {
            that.setData({
                banner: res.banner
            });
        }

        that.setData({
            article_list: that.data.article_list.concat(res.article_list),
            page_last: res.PAGE ? res.PAGE.LAST : false
        });
    });
};
$wxAppCMS.onShareAppMessage = function(res) {
    if (res.from === 'button') {
        // 来自页面内转发按钮
        console.log(res.target)
    }
    return {
        title: this.$globalData.appInfo.title + ' - ' + this.$globalData.appInfo.name,
        path: '/pages/index/index?uid=' + this.$globalData.session.userid,
        success: function(res) {
            // 转发成功
        },
        fail: function(res) {
            // 转发失败
        }
    }
}

$wxAppCMS.main = function() {
    let that = this;
    wx.getSystemInfo({
        success(res) {
            that.setData({
                scrollHeight: res.windowHeight
            });
        }
    })
    wx.setNavigationBarTitle({
        title: this.$globalData.appInfo.name
    });
    this.getList();
}
$wxAppCMS.show = function() {
}

$wxAppCMS.run();

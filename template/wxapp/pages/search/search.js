let $iCMS = getApp().iCMS();

$iCMS.addData({
    subTitle: '最新资讯',
    q: null,
    search: [],
    article_list: [],
    banner: []
});

$iCMS.onShareAppMessage = function(res) {
    var that = this;
    if (res.from === 'button') {
        // 来自页面内转发按钮
        console.log(res.target)
    }
    let session = this.$globalData.session;
    return {
        title: session.nickname + '@你，我找了关于'+this.data.subTitle+'的文章！',
        path: '/pages/search/search?q=' + this.data.q + '&uid=' + session.uid + '&from=share',
        success: function(res) {},
        fail: function(res) {}
    }
}

$iCMS.getList = function() {
    if (this.data.page_last) return;
    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'search', { q: this.data.q, page: this.data.page_no }
    );
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        wx.setNavigationBarTitle({
            title: res.search.title + ' - ' + that.$globalData.appInfo.name
        });
        that.setData({
            subTitle: res.search.title,
            search: res.search,
            article_list: that.data.article_list.concat(res.article_list),
            banner: res.banner,
            page_last: res.PAGE ? res.PAGE.LAST : false
        });
    });

};

$iCMS.main = function(options) {
    var that = this;
    wx.getSystemInfo({
        success(res) {
            that.setData({
                scrollHeight: res.windowHeight
            });
        }
    });
    this.data.q = options.q;
    this.getList();
}

$iCMS.run();

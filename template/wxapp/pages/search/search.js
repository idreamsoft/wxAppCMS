let $wxAppCMS = getApp().wxAppCMS();

$wxAppCMS.addData({
    subTitle: '最新资讯',
    q: null,
    search: [],
    article_list: [],
    banner: []
});

$wxAppCMS.getList = function() {
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

$wxAppCMS.main = function(options) {
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

$wxAppCMS.run();

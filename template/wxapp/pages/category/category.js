let $wxAppCMS = getApp().wxAppCMS();

$wxAppCMS.addData({
    cid: 0,
    subTitle: '最新资讯',
    category: [],
    article_list: [],
    banner: []
});

$wxAppCMS.getList = function() {
    if (this.data.pageLast) return;

    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'category', {
            tpl: 'category.list',
            cid: this.data.cid,
            page: this.data.pageNum
        }
    );
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        wx.setNavigationBarTitle({
            title: res.category.name + ' - ' + that.$globalData.appInfo.name
        });
        that.setData({
            subTitle: res.category.name,
            category: res.category,
            article_list: that.data.article_list.concat(res.article_list),
            banner: res.banner,
            pageLast: res.PAGE ? res.PAGE.LAST : false
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
    this.data.cid = options.cid;
    this.getList();
}

$wxAppCMS.run();

let $iCMS = getApp().iCMS();

$iCMS.addData({
    cid: 0,
    subTitle: '最新资讯',
    category: [],
    article_list: [],
    banner: []
});

$iCMS.getList = function() {
    if (this.data.page_last) return;

    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'category', {
            tpl: 'category.list',
            cid: this.data.cid,
            page: this.data.page_no
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
    this.data.cid = options.cid;
    this.getList();
}

$iCMS.run();

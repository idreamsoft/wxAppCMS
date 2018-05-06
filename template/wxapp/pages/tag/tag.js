let $wxAppCMS = getApp().wxAppCMS();

$wxAppCMS.addData({
    subTitle: '最新资讯',
    tid: 0,
    tag: [],
    article_list: [],
    banner: []
});

$wxAppCMS.data.tid = null;

$wxAppCMS.getList = function() {
    if (this.data.pageLast) return;

    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'tag', {
            id: this.data.tid,
            page: this.data.pageNum
        }
    );
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        wx.setNavigationBarTitle({
            title: res.tag.name + ' - ' + that.$globalData.appInfo.name
        });
        that.setData({
            subTitle: res.tag.name,
            tag: res.tag,
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
    this.data.tid = options.id;
    this.getList();
}

$wxAppCMS.run();

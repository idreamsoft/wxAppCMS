let $iCMS = getApp().iCMS();

$iCMS.addData({
    subTitle: '最新资讯',
    tid: 0,
    tag: [],
    article_list: [],
    banner: []
});

$iCMS.data.tid = null;

$iCMS.getList = function() {
    if (this.data.page_last) return;

    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'tag', {
            id: this.data.tid,
            page: this.data.page_no
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
        });
        if (res.banner) {
            that.setData({
                banner: res.banner
            });
        }
        if(this.data.page_no>1){
            that.setData({
                article_list: that.data.article_list.concat(res.article_list)
            });
        }else{
            that.setData({
                article_list: res.article_list,
                page_last: res.PAGE ? res.PAGE.LAST : false
            });
        }
        if(that.data.page_no>=res.PAGE.TOTAL){
            that.setData({
                page_last:true
            });
            return;
        }
    });

};
$iCMS.onShareAppMessage = function(res) {
    var that = this;
    if (res.from === 'button') {
        // 来自页面内转发按钮
        console.log(res.target)
    }
    let session = this.$globalData.session;
    return {
        title: session.nickname + '@你，我找了关于'+this.data.subTitle+'的文章！',
        path: '/pages/tag/tag?id=' + this.data.tid + '&uid=' + session.uid + '&from=share',
        success: function(res) {},
        fail: function(res) {}
    }
}

$iCMS.main = function(options) {
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

$iCMS.run();

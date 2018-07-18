let $iCMS = getApp().iCMS();

$iCMS.addData({
    article_list: [],
    banner: []
});

$iCMS.getList = function() {
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
        if (res.tag_list) {
            that.setData({
                tag_list: res.tag_list
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
    if (res.from === 'button') {
        // 来自页面内转发按钮
        console.log(res.target)
    }
    let title = this.$globalData.appInfo.name;
    if(that.metaData['share:index:title']){
        title = that.metaData['share:index:title']+ ' - ' + this.$globalData.appInfo.name
    }
    return {
        title: title,
        path: '/pages/index/index?uid=' + this.sessionData.userid,
        success: function(res) {
            // 转发成功
        },
        fail: function(res) {
            // 转发失败
        }
    }
}

$iCMS.main = function() {
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

$iCMS.run();

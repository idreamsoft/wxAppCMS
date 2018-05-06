let $wxAppCMS = getApp().wxAppCMS();

$wxAppCMS.addData({
    userInfo: {},
});

$wxAppCMS.main = function() {
    this.page_loading(false, true);
    this.setData({
        APP: this.$globalData.appInfo,
        userInfo: this.$globalData.userInfo
    });
}

$wxAppCMS.run();

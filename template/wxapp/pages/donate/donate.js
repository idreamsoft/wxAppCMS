let $APP = getApp();
let $wxAppCMS = $APP.wxAppCMS();

$wxAppCMS.addData({
    moneys: [1, 2, 4, 6, 8, 10],
    article: {}
});

$wxAppCMS.payTap = function(e) {
    var that = this;
    let $param = this.get_dataset(e);
    let $article = $APP.CACHE['article'];

    $param['avatar_url'] = this.$globalData.userInfo.avatarUrl;
    $param['iid'] = $article.id;
    $param['appid'] = '1';
    $param['title'] = $article.title;
    $param['uid'] = $article.user.uid;
    $param['name'] = $article.user.name;
    $param['avatar'] = $article.user.avatar;

    let $url = this.iURL.make('donate', { do: 'wxapp' });

    this.POST($url, $param).then(res => {
        that.payment(res.result).then(res => {
            wx.showModal({
                content: '非常感谢您的支持!',
                showCancel: false,
                success: function(res) {
                    if (res.confirm) {
                        wx.navigateBack({
                            delta: 2
                        })
                    }
                }
            });
        });
    }).catch(ret => {
        that.alert(ret.msg);
    });

}

$wxAppCMS.main = function(options) {
    this.page_loading(false, true);
    let $appInfo = this.$globalData.appInfo;

    if ($appInfo.meta['donate']) {
        this.setData({
            moneys: $appInfo.meta['donate']['value'].split(','),
        });
    }

    let $article = $APP.CACHE['article'];

    wx.setNavigationBarTitle({
        title: '赞赏' + $article.user.name
    });
    this.setData({
        article: $article,
    });

}

$wxAppCMS.run();

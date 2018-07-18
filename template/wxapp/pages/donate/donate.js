let $iCMS = getApp().iCMS();

$iCMS.addData({
    moneys: [1, 2, 4, 6, 8, 10],
    article: {}
});

$iCMS.payTap = function(e) {
    let that = this;
    let $APP = getApp();
    let $param = this.get_dataset(e);
    let $article = $APP.CACHE['article'];

    $param['avatar_url'] = this.userData.avatarUrl;
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

$iCMS.main = function(options) {
    this.page_loading(false, true);

    if (this.metaData['donate']) {
        this.setData({
            moneys: this.metaData['donate'].split(','),
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

$iCMS.run();

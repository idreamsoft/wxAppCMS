let $APP = getApp();
let $wxAppCMS = $APP.wxAppCMS();
let WxParse = require('../../wxParse/wxParse.js');

$wxAppCMS.addData({
    article: [],
    category: [],
    article_list: [],
    banner: []
});

$wxAppCMS.getData = function($id) {
    let that = this;
    let $url = this.iURL.make(
        'article', { tpl: 'article', id: $id }
    )
    this.data_loading('show');
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        wx.setNavigationBarTitle({
            title: res.article.title + ' - ' + that.$globalData.appInfo.name
        });

        if (res.article.user.avatar == "about:blank") {
            res.article.user.avatar = "/images/avatar.gif";
        }

        WxParse.wxParse('body', (res.article.markdown ? 'md' : 'html'), res.article.body, that, 5);

        that.setData({
            article: res.article,
            category: res.category,
            article_list: res.article_list,
            article_prev: res.article_prev,
            article_next: res.article_next
        });
        $APP.CACHE['article'] = {
            id: $id,
            title: res.article.title,
            user: {
                uid: res.article.user.uid,
                avatar: res.article.user.avatar,
                name: res.article.user.name
            }
        }
    });
};

$wxAppCMS.onShareAppMessage = function(res) {
    if (res.from === 'button') {
        // 来自页面内转发按钮
        console.log(res.target)
    }
    return {
        title: this.data.article.title,
        path: '/pages/article/article?id=' + this.data.article.id,
        success: function(res) {
            // 转发成功
        },
        fail: function(res) {
            // 转发失败
        }
    }
}

$wxAppCMS.upTap = function(e) {
    let that = this;
    let $iid = e.currentTarget.id,
        $avg_k = 'a_v_g_' + $iid;
    let $avg_v = wx.getStorageSync($avg_k) || 0;
    let $now = Date.now();

    if ($now - $avg_v < 86400) {
        that.alert('您已经点过赞了');
        return;
    }
    let $url = this.iURL.make('article');
    let $param = {
        "action": "vote",
        "type": "good",
        "iid": $iid
    }
    this.POST($url, $param).then(res => {
        wx.setStorageSync($avg_k, $now);
        ++that.data.article.good;
        that.setData({
            article: that.data.article
        });
    });

}

$wxAppCMS.favoriteTap = function(e) {
    // console.log(this.$globalData);

    let that = this;
    let $iid = e.currentTarget.id;
    let $param = this.utils.extend({
        fid: "0",
        uid: this.$globalData.session.userid,
        action: "add"
    }, that.data.article.param);

    let $url = this.iURL.make('favorite');

    this.POST($url, $param).then(res => {
        if (res.code) {
            ++that.data.article.favorite;
            that.setData({
                article: that.data.article
            });
        }
    }).catch(err => {
        that.alert(err.msg);
    });

}
$wxAppCMS.main = function(options) {
    this.setData({
        APP: this.$globalData.appInfo
    });
    this.getData(options.id);
}

$wxAppCMS.run();

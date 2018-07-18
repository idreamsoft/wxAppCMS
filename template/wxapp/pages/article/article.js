let $iCMS = getApp().iCMS();
let WxParse = require('../../wxParse/wxParse.js');

$iCMS.addData({
    article: [],
    category: [],
    article_list: [],
    banner: []
});

$iCMS.getData = function($id) {
    let that = this;
    let $url = this.iURL.make(
        'article', { tpl: 'article', id: $id }
    )
    this.data_loading('show');
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        that.title = res.article.title;

        wx.setNavigationBarTitle({
            title: res.article.title
        });
        that.utils.metaData(res.article.meta,res.article);
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

        let $APP = getApp();

        $APP.CACHE['article'] = res.article
    });
};

$iCMS.onShareAppMessage = function(res) {
    if (res.from === 'button') {
        // 来自页面内转发按钮
        console.log(res.target)
    }

    let $session = this.$globalData.session;
    let $data    = res.target.dataset;

    let $stitle   = this.metaData['share_title']||'推荐';
    let $title    = this.data.article.metaData['share_title']||$session.nickname + '给你'+$stitle+'了一篇不错的文章，打开看看吧！';
    let $imageUrl = this.data.article.metaData['share_imageUrl']||this.data.article.pic.url;
    let $path     = this.data.article.metaData['share_path']||'/pages/article/article?id=' + this.data.article.id + '&uid=' + $session.uid + '&from=share';

    if ($data['title'])     $title    = $data['title'];
    if ($data['path'])      $path     = $data['path'];
    if ($data['imageUrl'])  $imageUrl = $data['imageUrl'];

    $title = $title.replace('{name}', $session.nickname);
    $title = $title.replace('{title}', this.data.article.title);
    $path  = $path.replace('{id}', this.data.article.id);
    $path  = $path.replace('{uid}', $session.uid);

    let $share = {
        title:$title,path:$path,imageUrl:$imageUrl,
        success: function(res) {
            // 转发成功
        },
        fail: function(res) {
            // 转发失败
        }
    }
}

$iCMS.upTap = function(e) {
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

$iCMS.favoriteTap = function(e) {
    // console.log(this.$globalData);

    let that = this;
    let $iid = e.currentTarget.id;
    let $param = this.utils.extend({
        fid: "0",
        uid: that.sessionData.userid,
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
$iCMS.main = function(options) {
    this.iid = options.id;
    this.getData(this.iid);
}

$iCMS.run();

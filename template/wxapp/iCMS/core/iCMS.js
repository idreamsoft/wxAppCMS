var config = require('../../config.js');

var utils = require('iUtils.js');
var iHttp = require('iHttp.js');
var iUser = require('iUser.js');
var iUrl = require('iUrl.js');
var iUI = require('iUI.js');
var $APP = getApp();
var iCMS = utils.extend(true, iUI, {
    data: {
        appInfo: {},
        TABS: {},
        scrollHeight: 0,
        userInfo: null,
        page_no: 1,
        page_last: false,
        page_hidden: true,
        data_loading: true,
        page_loading: false
    },
    $init:false,
    $globalData:{},
    CONFIG: config,
    utils: utils,
    iURL: iUrl,
    iHttp: iHttp,
    GET: iHttp.GET,
    POST: iHttp.POST,
    UPLOAD: iHttp.UPLOAD,
    DOWNLOAD: iHttp.DOWNLOAD,
    log: function(...args) {
        console.log(args);
    },

    init: function() {
        let that = this;
        return new Promise(function(resolve, reject) {
            //获取用户的登录信息
            iUser.checkLogin().then(res => {
                console.log('login cache');

                that.$globalData.userInfo = wx.getStorageSync('userInfo');
                that.$globalData.appInfo = wx.getStorageSync('appInfo');
                that.$globalData.token = wx.getStorageSync('token');
                that.$globalData.session = wx.getStorageSync('session');
                resolve(that.$globalData);
                that.getAppInfo(false);
            }).catch(err => {
                console.log(err, 'login cache timeout');

                iUser.login().then((res) => {
                    wx.setStorageSync('userInfo', res.userInfo);
                    wx.setStorageSync('appInfo', res.appInfo);
                    wx.setStorageSync('session', res.session);
                    wx.setStorageSync('token', res.token);
                    wx.setStorageSync('nonce', res.nonce);

                    that.$globalData.userInfo = res.userInfo;
                    that.$globalData.appInfo = res.appInfo;
                    that.$globalData.token = res.token;
                    that.$globalData.session = res.session;
                    resolve(res);
                    that.getAppInfo(true);
                }).catch(err => {
                    console.log(err, 'login fail');
                });
            });
        });
    },
    getAppInfo: function($get) {

        var $now = Date.parse(new Date());
        var $time = this.$globalData.appInfo.timestamp * 1000;
        var $ctime = this.$globalData.appInfo.cachetime * 1000;
        var $cache = $now - $time < $ctime;

        // if ($get || $cache || this.ONESELF) return;

        let that = this;
        let $url = this.iURL.make(
            'wxapp', { 'do': 'appinfo' }
        );
        this.GET($url).then(res => {
            wx.setStorageSync('appInfo', res);
            that.$globalData.appInfo = res;
        });
    },
    payment: function(json) {
        return new Promise(function(resolve, reject) {
            wx.requestPayment({
                'timeStamp': json.timeStamp,
                'nonceStr': json.nonceStr,
                'package': json.package,
                'signType': 'MD5',
                'paySign': json.paySign,
                'success': function(res) {
                    if (res.errMsg == 'requestPayment:ok') {
                        if (typeof(resolve) === "function") {
                            resolve(res);
                        }
                    }
                },
                'fail': function(res) {
                    if (res.errMsg == 'requestPayment:fail cancel') {
                        wx.showToast({
                            title: '取消支付',
                            icon: 'none',
                            duration: 1500
                        });
                    }
                    if (res.errMsg == 'requestPayment:fail') {

                    }
                    if (typeof(reject) === "function") {
                        reject(res);
                    }
                    // console.log(res);
                    // iHttp.fail_reject(res, reject, 'requestPayment');
                }
            });
        });
    },
    success: function(title, duration) {
        wx.showToast({
            title: title,
            icon: 'success',
            duration: duration || 1500
        });
    },
    alert: function(content, callback, title) {
        if(content){
            wx.showModal({
                title: title || '系统提示',
                showCancel: false,
                content: content,
                success: function(res) {
                    if (typeof(callback) === "function") {
                        callback(res);
                    }
                }
            })
        }
    },
    tabsTap: function(e) {
        var data = iUI.get_dataset(e);
        console.log(data);

        if (data['id']) {
            this.setData({
                'TABS.active': data['id']
            });
        }
    },
    copyTap: function(e) {
        var get = iUI.get_dataset(e);
        var data = get['copy'];
        if(data){
            wx.setClipboardData({
              data: data,
              success: function(res) {
                // wx.getClipboardData({
                //   success: function(res) {
                //     console.log(res.data) // data
                //   }
                // })
              }
            })
        }
    },
    dataTap: function(e) {
        var data = iUI.get_dataset(e);

        if (data['tabbar']) {
            let url = data['tabbar'];
            if (url.indexOf("/pages/") == -1) {
                url = '/pages/' + url;
            }
            console.log(url);

            wx.switchTab({
                url: url
            });
        } else if (data['url'] || data['path']) {
            let url = data['url'] || data['path'];
            if (!url) {
                this.alert("dataTap error");
            }
            if (url.indexOf("/pages/") == -1) {
                url = '/pages/' + url;
            }
            wx.navigateTo({ url: url });
        } else if (data['uri']) {
            var uri = data['uri'];
            delete data['uri'];
            var query = iUrl.encode(data);
            wx.navigateTo({
                url: '/pages/' + uri + '?' + query
            })
        } else if (data['msg']) {
            this.alert(data['msg'], null, data['title'])
        }
    },
    addData: function(data) {
        this.data = utils.extend(true, data, this.data);
    },
    gotoIndex: function() {
        wx.switchTab({
            url: '/pages/index/index'
        });
    },
    saveFormIds: function() {
        let formIds = wx.getStorageSync('formIds');
        if(formIds){
            let that = this;
            let $url = this.iURL.make(
                'wxapp', { do: 'saveFormIds'}
            );
            this.POST($url,formIds).then(res => {
                wx.setStorageSync('formIds', []);
            }).catch(ret => {
            });
        }
    },
    pageMain: function() {
        let that = this;
        if (typeof(this.main) === 'function') {
            console.log('--- pageMain ---');
            this.$init = true;
            this.init().then(res => {
                that.sessionData = that.$globalData.session || {}
                that.userData = that.$globalData.userInfo || {}
                that.setData({
                    session: that.$globalData.session,
                    appInfo: that.$globalData.appInfo,
                    userInfo: that.$globalData.userInfo
                });
                utils.metaData(that.$globalData.appInfo.meta,that);
                if (that.hideUI) {
                    that.setData({hideUI: that.hideUI});
                }
                if (that.metaData) {
                    that.setData({metaData: that.metaData});
                }
                that.saveFormIds();
                that.main(that.options);
            }).catch(err => {
                console.log('pageMain init error', err);
            });
        }
    },
    getList: function() {},
    refresh: function() {},
    loadMore: function() {
        ++this.data.page_no;
        this.getList();
    },
    getPage:function() {
        let $pages = getCurrentPages();
        return $pages[($pages.length - 1)];
    },
    getUri:function() {
        let $page = this.getPage();
        return iUrl.path_query($page.route,$page.options);
    },
    // onShareAppMessage: function() {
    //     var that = this;
    //     if (res.from === 'button') {
    //         // 来自页面内转发按钮
    //         console.log(res.target)
    //     }

    //     let session = this.$globalData.session;
    //     return {
    //         title: session.nickname + '给你' + that.data.shareTitle + '了一张好看的头像，打开看看吧！',
    //         path: '/pages/open/index?id=' + this.$globalData.avatarId + '&uid=' + session.uid + '&from=share',
    //         imageUrl: that.data.poster,
    //         success: function(res) {
    //             // 转发成功
    //             wx.redirectTo({
    //                 url: '/pages/index/index'
    //             });
    //         },
    //         fail: function(res) {
    //             // 转发失败
    //         }
    //     }
    // },
    onPullDownRefresh: function() {
        wx.stopPullDownRefresh()
    },
    onUnload: function() {
        console.log('--- onUnload ---');
        this.saveFormIds();
        $APP.FLAGS['uri'] = null;
        if (typeof(this.unload) === 'function') {
            this.unload();
        }
    },
    onHide: function() {
        console.log('--- onHide ---');
        this.saveFormIds();
        $APP.FLAGS['uri']=this.getUri();
        console.log($APP.FLAGS);
        if (typeof(this.hide) === 'function') {
            this.hide();
        }
    },
    onLoad: function(options) {
        let that = this;
        $APP.FLAGS['uri'] = null;
        this.setData({
            'CONFIG':config
        });
        // wx.getSystemInfo({
        //     success: function(res) {
        //       that.$globalData.pixelRatio = res.pixelRatio;
        //       that.$globalData.windowWidth = res.windowWidth;
        //       that.$globalData.windowHeight = res.windowHeight;
        //     }
        // })
        console.log('--- onLoad ---');
        this.options = options;
        if (typeof(this.load) === 'function') {
            this.load(options);
        }
    },
    onShow: function() {
        console.log('--- onShow ---');
        let $uri = this.getUri();
        console.log($APP.FLAGS['uri'],$uri);
        //同个页面不重载
        if($APP.FLAGS['uri']!=$uri){
            this.pageMain('onShow');
        }
        if (typeof(this.show) === 'function') {
            this.show(this.options);
        }
    },
    run: function() {
        Page(this);
    }
});
// iCMS.UI.data_loading = iUI.data_loading.call(this);

module.exports = iCMS;

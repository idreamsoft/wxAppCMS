var config = require('../../config.js');

var utils = require('iUtils.js');
var iHttp = require('iHttp.js');
var iUser = require('iUser.js');
var iUrl = require('iUrl.js');
var iUI = require('iUI.js');

var iCMS = utils.extend(true, iUI, {
    data: {
        APP: {},
        scrollHeight: 0,
        userInfo: null,
        page_no: 1,
        page_last: false,
        page_hidden: true,
        data_loading: true,
        page_loading: false
    },
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
                    if(res.errMsg=='requestPayment:ok'){
                        if (typeof(resolve) === "function") {
                            resolve(res);
                        }
                    }
                },
                'fail': function(res) {
                    if(res.errMsg=='requestPayment:fail cancel'){
                        wx.showToast({
                          title: '取消支付',
                          icon: 'none',
                          duration: 1500
                        });
                    }
                    if(res.errMsg=='requestPayment:fail'){

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
    },
    dataTap: function(e) {
        var data = iUI.get_dataset(e);
        if (data['url'] || data['path']) {
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
    getList: function() {},
    refresh: function() {},
    loadMore: function() {
        ++this.data.page_no;
        this.getList();
    },
    onPullDownRefresh: function() {
        wx.stopPullDownRefresh()
    },
    onLoad: function(options) {
        let that = this;
        this.options = options;
        if (typeof(this.load) === 'function') {
            this.load(options);
        }
        this.init().then(res => {
            that.main(options);
        }).catch(err => {
            console.log('init error',err);
        });
    },
    onShow: function() {
        let that = this;
        // this.init().then(res => {
        //     that.main(that.options);
        //     if (typeof(that.show) === 'function') {
        //         that.show();
        //     }
        // });
        // let that = this;
        if (typeof(this.show) === 'function') {
            this.show(this.options);
        }

        // this.init().then(res => {
        // });
    },
    run: function() {
        Page(this);
    }
});
// iCMS.UI.data_loading = iUI.data_loading.call(this);

module.exports = iCMS;

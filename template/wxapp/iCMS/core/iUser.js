var config = require('../../config.js');
var iHttp = require('iHttp.js');
var iUrl = require('iUrl.js');

/**
 * 检查微信会话是否过期
 */
function wx_checkSession() {
    return new Promise(function(resolve, reject) {
        wx.checkSession({
            success: function() {
                resolve(true);
            },
            fail: function() {
                reject(false);
            }
        })
    });
}
/**
 * 调用微信登录
 */
function wx_login() {
    return new Promise(function(resolve, reject) {
        wx.login({
            success: function(res) {
                if (res.code) {
                    //登录远程服务器
                    // console.log(res)
                    resolve(res);
                } else {
                    reject(res);
                }
            },
            fail: function(err) {
                reject(err);
            }
        });
    });
}

function wx_getUserInfo() {
    return new Promise(function(resolve, reject) {
        wx.getUserInfo({
            withCredentials: true,
            success: function(res) {
                // console.log(res)
                resolve(res);
            },
            fail: function(err) {
                reject(err);
            }
        })
    });
}

/**
 * 判断用户是否登录
 */
function checkLogin() {
    return new Promise(function(resolve, reject) {
        var now = Date.parse(new Date());
        if (wx.getStorageSync('session') &&
            wx.getStorageSync('token') &&
            wx.getStorageSync('nonce')
            )
        {
            wx_checkSession().then(() => {
                resolve(true);
            }).catch(() => {
                reject(false);
            });
        } else {
            reject(false);
        }
    });
}

function login() {
    let $LOGIN_CODE = null;
    return new Promise(function(resolve, reject) {
        return wx_login().then(res => {
            $LOGIN_CODE = res.code;
            return wx_getUserInfo();
        }).then(data => {
            data.LOGIN_CODE = $LOGIN_CODE;
            //登录远程服务器
            iHttp.POST(
                iUrl.make('wxapp', 'auth'), data
            ).then(res => {
                if (res.code) {
                    //存储用户信息
                    res.userInfo = data.userInfo;
                     // if (iCMS.NONCE) url += '&_nonce=' + iCMS.NONCE;
                    resolve(res);
                } else {
                    reject(res);
                }
            }).catch((err) => {
                reject(err);
            });
        }).catch((err) => {
            wx.showModal({
                content: '为了保证您能正常使用' + config.TITLE + ',请允许获取您的公开信息(头像、昵称)',
                success: function(res) {
                    if (res.confirm) {
                        wx.openSetting({
                            success: (res) => {

                            }
                        });
                    } else if (res.cancel) {
                        login();
                    }
                }
            })
            if (typeof(reject) === "function") {
                reject(err);
            }
        })
    });
}

module.exports = {
    wx_checkSession,
    wx_login,
    wx_getUserInfo,
    checkLogin,
    login
}

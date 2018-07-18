function get_header($header) {
    let $token = wx.getStorageSync('token');
    if ($token) $header['AUTHORIZATION'] = $token;
    return $header;
}

function show_modal(title,content,callback) {
    wx.showModal({
        title: title,
        showCancel: false,
        content: content,
        success: function(res) {
            if (typeof(callback) === "function") {
                callback(res);
            }
        }
    })
}

function fail_reject(err, reject, method) {
    console.log(err, method + " failed");
    if (typeof(reject) === "function") {
        reject(err);
    }
}

function success_resolve(res,resolve,reject,method) {
    if (res.statusCode == 200) {
        if (res.data.code == "0") {
            console.log('data.code = 0');
            if(res.data.showMsg){
                show_modal('系统出错',res.data.msg+'请截图发给客服,谢谢');
            }
            fail_reject(res.data, reject, method);
        } else if (res.data.code == "-98") {
            if(res.data.forward) wx.redirectTo({url: res.data.forward});
            fail_reject(res.data, reject, method);
        } else if (res.data.code == "-99") {
            var reLaunch_count = wx.getStorageSync('reLaunch_count')||0;
            if(reLaunch_count<5){
                wx.clearStorageSync();
                wx.setStorageSync('reLaunch_count', ++reLaunch_count);
                wx.reLaunch({
                  url: '/pages/index/index?error='+res.data.forward
                })
            }
            fail_reject(res.data, reject, method);
        } else {
            resolve(res.data);
        }
    } else {
        fail_reject(res.data, reject, method);
    }
}

function GET($url, $data = {}) {
    return new Promise(function(resolve, reject) {
        let method = 'GET';
        wx.request({
            url: $url,
            data: $data,
            method: method,
            header: get_header({ 'content-type': 'application/json' }),
            success: function(res) {
                success_resolve(res,resolve,reject,method);
            },
            fail: function(err) {
                fail_reject(err, reject, method);
            }
        });
    });
}

function POST($url, $data = {}) {
    return new Promise(function(resolve, reject) {
        let method = 'POST';
        wx.request({
            url: $url,
            data: $data,
            method: method,
            header: get_header({ 'content-type': 'application/x-www-form-urlencoded' }),
            success: function(res) {
                success_resolve(res,resolve,reject,method);
            },
            fail: function(err) {
                fail_reject(err, reject, method);
            }
        });
    });
}
var iUrl = require('iUrl.js');
var uploadUrl = iUrl.make(
    'wxapp', { do: 'upload' }
);
var _uploadTask = null;
var _downloadTask = null;

function uploadTask($func) {
    _uploadTask = $func;
}

function downloadTask($func) {
    _downloadTask = $func;
}

function UPLOAD($filePath, $url, $data = {}) {
    return new Promise(function(resolve, reject) {
        let method = 'UPLOAD';
        const task = wx.uploadFile({
            url: $url || uploadUrl,
            filePath: $filePath,
            name: 'upfile',
            formData: $data || {},
            header: get_header({ 'content-type': 'multipart/form-data' }),
            success: function(res) {
                success_resolve(res,resolve,reject,method);
            },
            fail: function(err) {
                fail_reject(err, reject, method);
            }
        })

        if (_uploadTask) {
            task.onProgressUpdate((res) => {
                _uploadTask(res, $filePath);
            })
        }
    });
}

function DOWNLOAD($url, $data = {}) {
    return new Promise(function(resolve, reject) {
        let method = 'DOWNLOAD';
        const task = wx.downloadFile({
            url: $url,
            header: get_header({}),
            success: function(res) {
                success_resolve(res,resolve,reject,method);
            },
            fail: function(err) {
                fail_reject(err, reject, method);
            }
        })
        if (_downloadTask) {
            task.onProgressUpdate((res) => {
                _downloadTask(res);
            })
        }
    });
}
module.exports = {
    GET,
    POST,
    UPLOAD,
    uploadTask,
    uploadUrl,
    DOWNLOAD,
    downloadTask,
    success_resolve,
    fail_reject,
    get_header
}

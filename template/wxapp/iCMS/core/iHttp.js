function get_header($header) {
    let $token = wx.getStorageSync('token');
    if ($token) $header['AUTHORIZATION'] = $token;
    return $header;
}

function fail_reject(err, reject, method) {
    if (typeof(reject) === "function") {
        reject(err);
    }
    console.log(method + " failed")
}

function showModal(title,content,callback) {
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
function success_resolve(res, resolve, method) {
    if (res.statusCode == 200) {
        if (res.data.code == "0") {
            console.log('data.code = 0');
            console.log(res.data);
            showModal('系统出错',res.data.msg+'请截图发给客服,谢谢');
        } else if (res.data.code == "-99") {
            wx.clearStorage();
            wx.reLaunch({
              url: '/pages/index/index?error='+res.data.error
            })
            console.log(res.data);
        } else {
            resolve(res.data);
        }
    } else {
        console.log(method + ' success data error');
        console.log(res.data);
        // if (typeof(reject) === "function") {
        //     reject(res.data);
        // }
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
                success_resolve(res, resolve, method);
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
                success_resolve(res, resolve, method);
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
                success_resolve(res, resolve, method);
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
            success: function(res) {
                success_resolve(res, resolve, method);
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
    fail_reject
}

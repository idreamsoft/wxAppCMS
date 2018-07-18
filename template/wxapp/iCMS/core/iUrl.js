let config = require('../../config.js');

function make(app, query) {
    var url = config.HOST + "wxapp.php?app=" + app;
    if (config.wxAppID) url += '&wxAppId=' + config.wxAppID;
    if (config.VERSION) url += '&wxAppVersion=' + config.VERSION;
    if (typeof query === 'object') {
        url += '&' + encode(query);
    } else if (query) {
        url += '&' + query;
    }

    let nonce = wx.getStorageSync('nonce');
    if (nonce) url += '&_nonce=' + nonce;

    return url;
}
function path_query(path,query) {
    let qs = encode(query);
    if(qs){
        path+='?'+qs;
    }
    return path;
}
function encode(param, key) {
    if (param == null) return '';
    if (typeof param =='undefined') return '';

    var query = [],
        t = typeof(param);
    if (t == 'string' || t == 'number' || t == 'boolean') {
        query.push(key + '=' + encodeURIComponent(param));
    } else {
        for (var i in param) {
            var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
            var q = encode(param[i], k);
            if (q !== '') query.push(q);
        }
    }
    return query.join('&');
}

function decode(query) {
    var args = [],
        pairs = query.split("&");
    for (var i = 0; i < pairs.length; i++) {
        var pos = pairs[i].indexOf('=');
        if (pos == -1) continue;
        var argname = pairs[i].substring(0, pos);
        argname = argname.replace(/\+/g, '%20');
        argname = decodeURIComponent(argname);
        var value = pairs[i].substring(pos + 1);
        value = value.replace(/\+/g, '%20');
        value = decodeURIComponent(value);

        if (argname.indexOf('[') != -1) {
            argname = argname.replace(/\[\d+\]/g, "[]");
            argname = argname.replace('[]', '');
            if (!args[argname]) {
                args[argname] = [];
            }
            args[argname].push(value);
        } else {
            args[argname] = value;
        }
    };
    return args; // Return the object
}

module.exports = {
    make,
    encode,
    decode,
    path_query
}

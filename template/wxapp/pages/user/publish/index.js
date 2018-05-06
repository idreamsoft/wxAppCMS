let $APP = getApp();
let $wxAppCMS = $APP.wxAppCMS();

$wxAppCMS.addData({
    userInfo: {},
    counter: 0,
    maxlength: 10000,
    category_list: [],
    topCategory: [],
    subCategory: [],
    ms_index: 0,
    cid: 0,
    placeholder: false,
    textarea: '',
    progress: {},
    mediaCount: 0
});
$wxAppCMS.bodyData = [];


$wxAppCMS.getCategory = function() {
    this.data_loading('show');
    let that = this;
    let $url = this.iURL.make(
        'index', { tpl: 'publish.category' }
    );
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        let top = res.topCategory;
        top.unshift({ cid: "0", name: "请选择栏目", child: "1" });

        let sub = res.subCategory;
        sub.unshift({"rootid": "0","child": [{ "cid": "0", "name": "选择栏目"}]});

        wx.setStorageSync('category_list',{top,sub});

        that.setData({
            category_list: [top, sub[0]['child']]
        });
    });
};
$wxAppCMS.getSubCategoryById = function(idx) {
    let category_list = wx.getStorageSync('category_list');
    let rootid = category_list.top[idx].cid;
    let tempObj = [];
    let subCategory = category_list.sub;
    for (let i = 0; i < subCategory.length; i++) {
        if (subCategory[i].rootid == rootid) {
            tempObj = subCategory[i].child;
            break;
        }
    }
    return tempObj;
}
$wxAppCMS.bindPickerChange = function(e) {
    // console.log(e.detail);
    if (e.detail.column == "0") {
        let category_list = wx.getStorageSync('category_list');
        let subCategory   = this.getSubCategoryById(e.detail.value);

        this.setData({
            placeholder: true,
            cid: subCategory[0]['cid'],
            category_list: [category_list.top, subCategory]
        })
    }

}
$wxAppCMS.catchDelImage = function(e) {
    let $data = this.get_dataset(e);
    let rootid = $data['rootid'];
    let key = $data['key'];
    this.bodyData[rootid]['content'].splice(key, 1);
    if (this.bodyData[rootid]['content'].length == 0) {
        this.bodyData.splice(rootid, 1);
    }
    this.data.mediaCount--;
    this.setData({
        bodyData: this.bodyData
    });
}
$wxAppCMS.bindDelBody = function(e) {
    let $data = this.get_dataset(e);
    let index = $data['index'];
    this.bodyData.splice(index, 1);
    if ($data['type'] != 'text') {
        this.data.mediaCount -= $data['len'];
    }
    this.setData({
        bodyData: this.bodyData
    });
}
$wxAppCMS.setBody = function(content, type) {
    let that = this;
    type = type || 'text';
    let $bodyId = wx.getStorageSync('bodyId');

    if (type == 'text') {
        let $data = this.bodyData[$bodyId];
        if ($data && $data['type'] != 'text') {
            $bodyId++;
        }
    } else {
        let sss = 9 - this.data.mediaCount;
        if (sss < 0) {
            this.alert("单次最多只能上传9张图片/视频");
            return;
        } else {
            let ss = sss - content.length;
            if (ss < 0) {
                this.alert("单次最多只能上传9张图片/视频");
                content = content.slice(0, sss);
            }
        }
        this.data.mediaCount += content.length;
        $bodyId++;
        this.setData({
            textarea: ''
        });
    }
    let uuid = this.utils.uuid();
    this.bodyData[$bodyId] = {
        uuid,
        type,
        content
    };

    this.setData({
        bodyData: this.bodyData
    });
    if (type != 'text') {
        this.data.mediaCount = 0;
        this.bodyData.forEach(function(data, index) {
            if (data['type'] != 'text') {
                that.data.mediaCount += data['content'].length;
            }
        });
    }
    console.log('$bodyId++', $bodyId);
    wx.setStorageSync('bodyId', $bodyId);
    wx.setStorageSync('bodyData', this.bodyData);
}

$wxAppCMS.bindBodyBlur = function(e) {
    let text = e.detail.value;
    if (text) this.setBody(text);
}
$wxAppCMS.textTap = function(e) {
    let bodyId = wx.getStorageSync('bodyId');
    bodyId++;
    wx.setStorageSync('bodyId', bodyId);
    this.setData({
        textarea: ''
    });
}
$wxAppCMS.bindBodyInput = function(e) {
    let text = e.detail.value;
    this.setData({
        counter: text.length
    });
}

function chooseImage(index) {
    let that = this;
    let source = ['album', 'camera'];
    wx.chooseImage({
        count: 9, // 默认9
        sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
        sourceType: source[index] || ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
        success: function(res) {
            that.setBody(res.tempFilePaths, 'image');
        }
    })
}

function chooseVideo() {
    let that = this;
    wx.chooseVideo({
        sourceType: ['album', 'camera'],
        maxDuration: 60,
        camera: 'back',
        success: function(res) {
            that.setBody([res.tempFilePath], 'video');
        }
    })
}
$wxAppCMS.mediaTap = function(e) {
    $APP.ONESELF = true;
    let that = this;
    let chooseMap = [chooseImage, chooseImage, chooseVideo];
    wx.showActionSheet({
        itemList: ['拍照', '选择照片', '选择视频'],
        success: function(res) {
            let index = res.tapIndex;
            chooseMap[index].call(that, index);
        },
        fail: function(res) {
            console.log(res.errMsg)
        }
    });
}
$wxAppCMS.formSubmit = function(e) {
    let that = this;
    let $param = e.detail.value;

    console.log($param);

    if (!$param['title']) {
        that.alert("请填写文章标题");
        return '';
    }
    if (!$param['cid'] || $param['cid'] == "0") {
        that.alert("请选择文章栏目");
        return;
    }

    if (!this.bodyData.length) {
        that.alert("请填写文章内容");
        return;
    }

    this.iHttp.uploadTask(function(res, path) {
        let pp = {}
        pp[path] = res.progress;
        let progress = that.utils.extend(true, that.data.progress, pp);
        that.setData({ 'progress': progress });
    });
    // bodyData(this.bodyData).then().then(upload)
    var uploads = [];
    that.bodyData.forEach(function(data, index) {
        if (data['type'] != 'text') {
            data['content'].forEach(function(path, i) {
                console.log(path);
                if (path.indexOf('//tmp') !== -1 ||
                    path.indexOf('wxfile://') !== -1 ||
                    path.indexOf('tmp_') !== -1
                    )
                {
                    let u = that.UPLOAD(path);
                    uploads.push(u);
                }
            });
        }
    });
    if (uploads.length > 0) {
        wx.showToast({
            title: '图片/视频上传中',
            icon: 'loading',
            mask: true,
            duration: 30000
        })
        // 同时执行所有上传，并在它们都完成后执行then: // 获得一个Array: ['P1', 'P2']
        Promise.all(uploads).then(function(results) {
            let $urls = [];
            results.forEach(function(ret, index) {
                let obj = JSON.parse(ret);
                if (obj.code) {
                    let $md5 = that.utils.get_file_md5(obj.original);
                    $urls[$md5] = obj.url
                }
            });
            that.bodyData.forEach(function(data, index) {
                if (data['type'] != 'text') {
                    data['content'].forEach(function(path, i) {
                        let $md5 = that.utils.get_file_md5(path);
                        let $url = $urls[$md5];
                        if ($url) {
                            that.bodyData[index]['content'][i] = $urls[$md5];
                        }
                    });
                }
            });
            wx.hideToast();
            // console.log($urls);
            // console.log(that.bodyData);
            publish_param();
        }).catch(ret => {
            console.log(ret);
        });
    } else {
        publish_param();
    }

    function publish_param() {
        // let $body = [];
        // that.bodyData.forEach(function(data,index){
        //     if(data['type']=='text'){
        //         if(data['content']) $body[index]=data['content']+"\n";
        //     }else{
        //         $body[index] = [];
        //         data['content'].forEach(function(path,i){
        //             if(path.indexOf('//tmp')===-1){
        //                 $body[index][i]="![]("+path+")\n";
        //             }
        //         });
        //     }
        // });
        $param['body'] = JSON.stringify(that.bodyData);
        publish_post($param);
    }

    function publish_post($param) {
        wx.showToast({
            title: '提交中,请稍候',
            icon: 'loading',
            duration: 3000
        })
        let $url = that.iURL.make(
            'wxapp', { do: 'publish' }
        );
        that.POST($url, $param).then(res => {
            wx.hideToast();
            wx.showModal({
                content: res.msg,
                showCancel: false,
                success: function(res) {
                    if (res.confirm) {
                        wx.navigateBack({
                            delta: 2
                        })
                    }
                }
            });
        }).catch(ret => {
            that.alert(ret.msg);
        });
    }
}
$wxAppCMS.formReset = function() {
    console.log('form发生了reset事件')
}
$wxAppCMS.load = function() {
    wx.setStorageSync('bodyId', 0);
    wx.setStorageSync('bodyData', []);
}
$wxAppCMS.main = function() {
    // this.page_loading(false, true);;
    this.setData({
        APP: this.$globalData.appInfo,
    });

    this.getCategory();
}
$wxAppCMS.run();

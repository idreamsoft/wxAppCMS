let $APP = getApp();
let $iCMS = $APP.iCMS();

$iCMS.main = function() {
    this.getList(0);
}
$iCMS.getList = function() {
    this.data_loading('show');

    let that = this;
    let $url = this.iURL.make(
        'index', { 'tpl': 'article.my' }
    )
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        that.setData({
            result: res.result
        });
    });
}

$iCMS.delTap = function(e) {
    let $data = this.get_dataset(e);

    let that = this;
    let $url = this.iURL.make('user');
    let $param = {
        'id': $data['id'],
        'act': 'delete',
        'pg': 'article',
        'action': 'manage'
    }

    this.POST($url, $param).then(res => {
        wx.showToast({
            title: '成功',
            icon: 'success',
            duration: 2000
        });
        let result = that.data.result;
        var newRet = []
        result.forEach(function(value, index) {
            if (value['id'] == $data['id']) {
                // console.log(value['id'],index);
                delete that.data.result[index];
            } else {
                newRet.push(value);
            }
        });
        that.setData({
            result: newRet
        });
    });
}
$iCMS.run();

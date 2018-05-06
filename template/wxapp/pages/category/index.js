let $wxAppCMS = getApp().wxAppCMS();

$wxAppCMS.addData({
    cid: 0,
    article_list_loading: false,
    tag_list: [],
    category_list: [],
    article_list: [],
    banner: []
});

$wxAppCMS.getData = function() {
    this.data_loading('hide');
    let that = this;
    let $url = this.iURL.make(
        'index', { tpl: 'category.index' }
    );
    this.GET($url).then(res => {
        that.data_loading('hide');
        that.page_loading(false, true);

        that.setData({
            tag_list: res.tag_list,
            category_list: res.category_list,
            cid: res.category_list[0].cid
        });
        that.getList(res.category_list[0].cid);
    });

};
$wxAppCMS.getList = function($cid) {
    if (this.data.pageLast) return;

    $cid = $cid || this.data.cid;

    let that = this;
    let $url = this.iURL.make(
        'category', {
            tpl: 'category.list',
            cid: $cid,
            page: this.data.pageNum
        }
    );
    this.setData({
        article_list_loading: false
    });
    this.GET($url).then(res => {
        that.setData({
            article_list_loading: true,
            article_list: res.article_list
        });
    });

};

$wxAppCMS.tabClick = function(e) {
    this.setData({
        cid: e.currentTarget.id
    });
    this.getList();
}
$wxAppCMS.main = function() {
    this.getData();
}
$wxAppCMS.run();

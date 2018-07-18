let $iCMS = getApp().iCMS();

$iCMS.addData({
    cid: 0,
    article_list_loading: false,
    tag_list: [],
    category_list: [],
    article_list: [],
    banner: []
});

$iCMS.getData = function() {
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
$iCMS.getList = function($cid) {
    if (this.data.page_last) return;

    $cid = $cid || this.data.cid;

    let that = this;
    let $url = this.iURL.make(
        'category', {
            tpl: 'category.list',
            cid: $cid,
            page: this.data.page_no
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

$iCMS.tabClick = function(e) {
    this.setData({
        cid: e.currentTarget.id
    });
    this.getList();
}
$iCMS.main = function() {
    this.getData();
}
$iCMS.run();

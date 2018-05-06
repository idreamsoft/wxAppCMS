function data_loading(a = 'show') {
    this.setData({
        data_loading: (a == 'show' ? false : true)
    });
}

function page_loading(a, b) {
    this.setData({
        page_hidden: a,
        page_loading: b,
    });
}

function get_dataset(e) {
    return e.currentTarget.dataset;
}
module.exports = {
    data_loading,
    page_loading,
    get_dataset
}

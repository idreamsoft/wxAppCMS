Component({
    properties: {},
    methods: {
        collect: function (e) {
            let formId = e.detail.formId;
            let formIds = wx.getStorageSync('formIds')||[];
            formIds.push(formId);
            wx.setStorageSync('formIds', formIds);
            console.log(formIds);

          // var detail = e.detail // detail对象，提供给事件监听函数
          // var option = {} // 触发事件的选项
          // console.log(e);
          // this.triggerEvent('icmsEvent', detail, option);
        }
    }
})

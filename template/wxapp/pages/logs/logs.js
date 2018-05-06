//logs.js
var utils = require('../../utils/utils.js')
Page({
  data: {
    logs: []
  },
  onLoad: function () {
    this.setData({
      logs: (wx.getStorageSync('logs') || []).map(function (log) {
        return utils.formatTime(new Date(log))
      })
    })
  }
})

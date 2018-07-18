let $iCMS = getApp().iCMS();

$iCMS.addData({
    canIUse: wx.canIUse('button.open-type.getUserInfo')
});

$iCMS.load = function() {
  this.setData({
    'CONFIG':this.CONFIG
  });
}

$iCMS.formSubmit =function(e) {
  //console.log(e.detail);
  if(e.detail.formId){
    wx.setStorageSync('userLoginFormId', e.detail.formId);
    return;
  }
  if(e.detail.userInfo){
    wx.navigateBack({delta:2});
  }else{
    this.alert('为了保证您能正常使用' + this.CONFIG.TITLE + ',请允许获取您的公开信息(头像、昵称)')
  }
}
$iCMS.run();

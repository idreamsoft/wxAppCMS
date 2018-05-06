(function(factory) {
    if (!window.jQuery) {
        alert('jQuery is required.')
    }

    jQuery(function() {
        factory.call(null, jQuery);
    });
})(function($) {
    // -----------------------------------------------------
    // ------------ START ----------------------------------
    // -----------------------------------------------------

    // ---------------------------------
    // ---------  Uploader -------------
    // ---------------------------------
    var Uploader = (function() {

        // -------setting-------
        // 如果使用原始大小，超大的图片可能会出现 Croper UI 卡顿，所以这里建议先缩小后再crop.
        var FRAME_WIDTH = 1600;


        var _ = WebUploader;
        var Uploader = _.Uploader;
        var uploaderContainer = $('.uploader-container');
        var uploader, file;

        if (!Uploader.support()) {
            alert('Web Uploader 不支持您的浏览器！');
            throw new Error('WebUploader does not support the browser you are using.');
        }

        // hook,
        // 在文件开始上传前进行裁剪。
        // Uploader.register({
        //     'before-send-file': 'cropImage'
        // }, {
        //     cropImage: function(file) {
        //         var data = file._cropData,
        //             image, deferred;

        //         file = this.request('get-file', file);
        //         deferred = _.Deferred();

        //         image = new _.Lib.Image();

        //         deferred.always(function() {
        //             image.destroy();
        //             image = null;
        //         });
        //         image.once('error', deferred.reject);
        //         image.once('load', function() {
        //             image.crop(data.x, data.y, data.width, data.height, data.scale);
        //         });

        //         image.once('complete', function() {
        //             var blob, size;

        //             // 移动端 UC / qq 浏览器的无图模式下
        //             // ctx.getImageData 处理大图的时候会报 Exception
        //             // INDEX_SIZE_ERR: DOM Exception 1
        //             try {
        //                 blob = image.getAsBlob();
        //                 size = file.size;
        //                 file.source = blob;
        //                 file.size = blob.size;

        //                 file.trigger('resize', blob.size, size);

        //                 deferred.resolve();
        //             } catch (e) {
        //                 console.log(e);
        //                 // 出错了直接继续，让其上传原始图片
        //                 deferred.resolve();
        //             }
        //         });

        //         file._info && image.info(file._info);
        //         file._meta && image.meta(file._meta);
        //         image.loadFromBlob(file.source);
        //         return deferred.promise();
        //     }
        // });

        return {
            init: function(selectCb) {
                uploader = new Uploader({
                    pick: {
                        id: '#filePicker',
                        multiple: false
                    },
                    fileVal: 'upfile',
                    formData: {
                        'action': 'profile',
                        'pg': 'avatar'
                    },

                    // 设置用什么方式去生成缩略图。
                    thumb: {
                        quality: 70,

                        // 不允许放大
                        allowMagnify: false,

                        // 是否采用裁剪模式。如果采用这样可以避免空白内容。
                        crop: false
                    },

                    // 禁掉分块传输，默认是开起的。
                    chunked: false,

                    // 禁掉上传前压缩功能，因为会手动裁剪。
                    compress: false,
                    runtimeOrder :'html5',
                    fileSingleSizeLimit: 2 * 1024 * 1024,
                    server: SERVER_UPLOADER_URL,
                    swf: '../webuploader/Uploader.swf',
                    // fileNumLimit: 1,
                    onError: function() {
                        var args = [].slice.call(arguments, 0);
                        alert(args.join('\n'));
                    }
                });
                uploader.reset();

                uploader.on('fileQueued', function(_file) {
                    file = _file;

                    uploader.makeThumb(file, function(error, src) {

                        if (error) {
                            alert('不能预览');
                            return;
                        }

                        selectCb(src);

                    }, FRAME_WIDTH, 1); // 注意这里的 height 值是 1，被当成了 100% 使用。
                });
            },

            crop: function(data) {

                var scale = Croper.getImageSize().width / file._info.width;
                data.scale = scale;




            },

            upload: function() {
                uploader.upload();
            }
        }
    })();

    // ---------------------------------
    // ---------  Crpper ---------------
    // ---------------------------------
    var Croper = (function() {
        var container = $('.cropper-wraper');
        var $image = container.find('.img-container img');
        var isBase64Supported, callback;

        var options = {
            viewMode: 3,
            preview: '.img-preview',
            aspectRatio: 1 / 1,
            dragMode: "move",
            // responsive: false,
            // zoomOnWheel: false,
            minCropBoxWidth: 300,
            minCropBoxHeight: 300,
            //zoomable: false,
            scalable: false,
            // rotatable: false,
            // movable: false,
            // // minContainerWidth: $("body").width(),
            // autoCropArea: false,
            // cropBoxResizable:false,
            ready: function(e) {
                $(".cropper-preview").show();
                $(".cropper-info").show();
                var d = $(this).cropper('getImageData');
                $("#oimg-size").text('原图尺寸:' + Math.round(d.naturalWidth) + ' x ' + Math.round(d.naturalHeight));
                // $("#img-size").text('缩略图尺寸:' + Math.round(d.width) + ' x ' + Math.round(d.width));
                // $("#ocrop-size").text('需要尺寸:150 x 150');
                // var w = 300 / d.aspectRatio
                var CropBoxData = $(this).cropper('CropBoxData');
                CropBoxData.width = 300;
                CropBoxData.height = 300;
                // $(".cropper-crop-box").css({
                //     width: '300',
                //     height: '300'
                // });
                $(this).cropper("setCropBoxData", CropBoxData);
                $(this).cropper("setCanvasData", {
                    top: 0,
                    left: 0,
                });
                // $(this).cropper('zoom', 1);

                // console.log('getContainerData',$(this).cropper('getContainerData'));
                // console.log('getImageData',$(this).cropper('getImageData'));
                // console.log('getCropBoxData',$(this).cropper('getCropBoxData'));
            },
            // cropstart: function(e) {
            //     $(this).cropper("setCropBoxData",{width:150,height:150});
            // },
            crop: function(e) {
                // console.log('getCropBoxData',$(this).cropper('getCropBoxData'));
                // var cbd = $(this).cropper('getCropBoxData');
                // console.log(cbd);
                // $("#crop-size").text('剪裁尺寸:' + Math.round(cbd.width) + ' x ' + Math.round(cbd.height) + ' 位置:' + Math.round(e.x) + ':' + Math.round(e.y));
            }
        };

        $image.cropper(options);

        function srcWrap(src, cb) {

            // we need to check this at the first time.
            if (typeof isBase64Supported === 'undefined') {
                (function() {
                    var data = new Image();
                    var support = true;
                    data.onload = data.onerror = function() {
                        if (this.width != 1 || this.height != 1) {
                            support = false;
                        }
                    }
                    data.src = src;
                    isBase64Supported = support;
                })();
            }

            if (isBase64Supported) {
                cb(src);
            } else {
                // otherwise we need server support.
                // convert base64 to a file.
                $.ajax(SERVER_PREVIEW_URL, {
                    method: 'POST',
                    data: src,
                    dataType: 'json'
                }).done(function(response) {
                    if (response.result) {
                        cb(response.result);
                    } else {
                        alert("预览出错");
                    }
                });
            }
        }

        var btn = $('.upload-btn');
        btn.on('click', function() {
            var data = $image.cropper("getData");
            // if (callback) {
            //     callback(data);
            // } else {
            $image.cropper('getCroppedCanvas').toBlob(function(blob) {
                var formData = new FormData();
                formData.append('upfile', blob);
                formData.append('action', 'profile');
                formData.append('pg', 'avatar');

                $.ajax(SERVER_UPLOADER_URL, {
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(c) {
                          if(c.code){
                            $image.cropper("replace", c.forward+'?'+ Math.random());
                            $image.cropper("reset");
                            $(".upload-btn").hide();
                          }else{
                            iCMS.alert('上传失败!请重请上传');
                          }
                    },
                    error: function() {
                        alert('Upload error');
                    }
                });
            });
            // }
            return false;
        });

        return {
            setSource: function(src) {

                // 处理 base64 不支持的情况。
                // 一般出现在 ie6-ie8
                srcWrap(src, function(src) {
                    $image.cropper("replace", src);
                });

                container.removeClass('webuploader-element-invisible');

                return this;
            },

            getImageSize: function() {
                var img = $image.get(0);
                return {
                    width: img.naturalWidth,
                    height: img.naturalHeight
                }
            },

            setCallback: function(cb) {
                callback = cb;
                return this;
            },

            disable: function() {
                $image.cropper("disable");
                return this;
            },

            enable: function() {
                $image.cropper("enable");
                return this;
            }
        }

    })();

    // ------------------------------
    // -----------logic--------------
    // ------------------------------
    // var container = $('.uploader-container');

    Uploader.init(function(src) {
        Croper.setSource(src);
        $(".upload-btn").show();

        // 隐藏选择按钮。
        // container.addClass('webuploader-element-invisible');
        // 当用户选择上传的时候，开始上传。
        // Croper.setCallback(function(data) {
        //     Uploader.crop(data);
        //     Uploader.upload();
        // });
    });



    // -----------------------------------------------------
    // ------------ END ------------------------------------
    // -----------------------------------------------------
});

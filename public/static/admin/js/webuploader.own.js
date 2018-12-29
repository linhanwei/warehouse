$(function(){
    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
        }
        return "";
    }
    var lang = getCookie('language');
    // 默认语言
    if (lang === "") {
        lang = 'zh-cn';
    }

    var local_messages = {
        'zh-cn': {
            wait_upload: "等待上传",
            upload_file_size_tip: "文件大小不能超过",
            uploading: "上传中",
            uploaded: "上传完成",
            upload_error: "上传出错",
        },
    };
    var lang_messages = local_messages[lang];

    var $btn = $('.uploader-btn');

    uploader = new Array();
    $('.uploader-group').each(function(index){

        var tmp_path   = $(this).data("dir");
        var randname   = $(this).data("randname");
        var multiple   = $(this).data("multiple");
        var extensions = $(this).data("extensions");
        var mimeTypes  = $(this).data("mimetypes");
        var auto = $(this).data("auto");  //是否自动上传
        var len = $(this).data("len");    //是否单独上传
        var size = $(this).data("size")*1024*1024;//文件大小限制 ,配置单位M;
        var status = $(this).data("status");
        if( $(this).data("size")==undefined){
            size=1000000000000000;
        }
        if (tmp_path == undefined) {
            tmp_path = 'tmp';
        };
        if (randname == undefined) {
            randname = '1';
        };
        if (multiple == undefined) {
            multiple = true;
        };
        if (extensions == undefined) {
            //extensions = 'gif,jpg,jpeg,bmp,png,zip,pdf,doc,xls';
            extensions = 'gif,jpg,jpeg,bmp,png,pdf,doc,docx,txt,xls,xlsx,ppt,pptx';
        };
        if (mimeTypes == undefined) {
            //mimeTypes = 'image/*,application/zip,application/pdf,application/msword,application/vnd.ms-excel';
            mimeTypes = 'image/*,text/*'
                //word
                +',application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                //excel
                +',application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                //ppt
                +',application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation'
                +',application/pdf'
        };

        var filePicker=$(this).find('.uploader-picker');//上传按钮实例
        uploader[index] = WebUploader.create({
            resize: false,                          // 不压缩image
            swf: STATIC_URL+'/static/admin/webuploader/uploader.swf', // swf文件路径
            server: BD_UPLOADS_URL,    // 文件接收服务端。
            //pick: '.btn-dark',
            // 选择文件的按钮。可选
            pick:{
                id:filePicker,                      // 选择文件的按钮。可选
                multiple: multiple,                 // 默认为true，就是可以多选
            },
            duplicate: true,
            chunked: true,                          // 是否要分片处理大文件上传
            chunkSize: 1*1024*1024,                 // 分片上传，每片2M，默认是5M
            auto: auto,                            // 选择文件后是否自动上传
            chunkRetry : 2,                         // 如果某个分片由于网络问题出错，允许自动重传次数
            runtimeOrder: 'html5,flash',
            accept: {
                title: 'Images',
                extensions: extensions,
                mimeTypes: mimeTypes
            },
            formData: {
                //'token'     :index ,
                'randname'  : randname,             // 是否随机生成文件名
                'tmp_path'  : tmp_path,             // 上传文件目录
            }
        });

        uploader[index].on( 'fileQueued', function( file ) {

            var m = size/1024/1024;
            if(file.size<size){
                if($(".uploader-picker").eq(index).data("type")=="image"){ //判断是否是图片
                    addFile( file,uploader[index]);
                }else{
                    $(".uploader-picker").eq(index).siblings(".uploader-list").append( '<div id="' + file.id + '" class="item other-item">' +
                        '<h4 class="info">' + file.name + '<i class="fa fa-close close-btn" style="color:red"></i></h4> ' +
                        '<p class="state">'+lang_messages.wait_upload+'...</p>' +
                        '</div>' );
                }
            }else{
                parent.layer.msg(lang_messages.upload_file_size_tip+m+"M");
                return false;
            }
            $(".uploader-picker").eq(index).closest("div").removeClass("has-error").find("span.Validform_checktip").remove();
            $(".uploader-picker").eq(index).closest("div").siblings("label").css("color","");
        });
        // 当有img添加进来时执行，负责view的创建
        function addFile( file,now_uploader) {
            now_uploader.makeThumb(file, function (error, src) {
                $(".uploader-picker").eq(index).siblings(".uploader-list").append( '<div id="' + file.id + '" class="item img-item pull-left" style="margin-bottom:10px;margin-right:10px;">' +
                    '<img style="width:100px;height:100px;" src="'+src+'">' +
                    '<i class="fa fa-close close-btn"></i>'+
                    '<p class="state">'+lang_messages.wait_upload+'...</p>' +
                    '</div>' );
            });
        }
        //加入队列前，判断文件格式，不合适的排除
        uploader[index].on('beforeFileQueued', function (file) {
            file.guid = WebUploader.Base.guid();
        });
        //文件分块上传前触发，加参数，文件的订单编号加在这儿
        uploader[index].on('uploadBeforeSend', function (object, data, headers) {
            data.guid = object.file.guid;
        });
        // 文件上传过程中创建进度条实时显示。
        uploader[index].on( 'uploadProgress', function( file, percentage ) {

            var $li = $( '#'+file.id),
                $percent = $li.find('.progress .progress-bar');
            // 避免重复创建
            if ( !$percent.length ) {
                $percent = $('<div class="progress progress-striped active">' +
                    '<div class="progress-bar" role="progressbar" style="width: 0%">' +
                    '</div>' +
                    '</div>').appendTo( $li ).find('.progress-bar');
            }

            $li.find('p.state').text(lang_messages.uploading);

            $percent.css( 'width', percentage * 100 + '%' );
        });
        // 文件上传成功
        uploader[index].on( 'uploadSuccess', function( file,response ) {

            var obj = JSON.parse(response._raw);
            if(obj.error)
            {
                $( '#'+file.id ).find('p.state').html('<span style="color: red;">'+obj.error.message+'</span>');
                return false;
            }
            var name =  $( '#'+file.id ).parents(".uploader-list").siblings("a").data("file"),
                val = obj.result.filename,
                src = obj.result.filelink;
            var str = '<input type="hidden" name="'+name+'[]" value="'+val+'" class="hid-filename">'
            $( '#'+file.id ).append(str);
            $( '#'+file.id).find("img").attr("src",src);
            $( '#'+file.id ).find('p.state').text(lang_messages.uploaded);
            if(len==1){
                $( '#'+file.id ).parents(".uploader-list").siblings("a").addClass("hide");
                //hideBtnFn();
                if(status=="edit"){
                    $( '#'+file.id ).siblings("div").remove();
                }
            }

            $( '#'+file.id ).parents(".form-group").removeClass("has-error").find("span.help-block").remove();
            return false
        });
        // 文件上传失败，显示上传出错
        uploader[index].on( 'uploadError', function( file ) {
            $( '#'+file.id ).find('p.state').text(lang_messages.upload_error);
        });
        // 完全上传完
        uploader[index].on( 'uploadComplete', function( file ) {
            $( '#'+file.id ).find('.progress').fadeOut();
        })

    });
    //每个上传button加data-id
    $(".uploader-btn").each(function(index){
        $(this).attr("data-id",index);
    })
    $btn.on('click', function () {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        $(this).siblings(".uploader-list").addClass("AF");
        var i = $(this).data("id");
        uploader[i].upload();
        //if (state === 'ready') {
        //    uploader.upload();
        //} else if (state === 'paused') {
        //    uploader.upload();
        //} else if (state === 'uploading') {
        //    uploader.stop();
        //}
    });

    $("body").on("click",".close-btn",function(){  //点击删除按钮
        var val = $(this).parents(".item").find("input[type=hidden]").val();
        var len = $(this).parents(".uploader-group").data("len");
        var item = $(this).parents(".item");
        var str = $(this).parents(".item").find(".state").html();
        if(len==1){
            $(this).parents(".uploader-list").siblings("a").css("display","inline-block").removeClass("hide");
        }

        $.getJSON(BD_DEL_IMAGE_URL, {"filename":val},function(result){});

        item.remove();

    })
});

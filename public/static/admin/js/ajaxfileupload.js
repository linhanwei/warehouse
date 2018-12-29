// JavaScript Document
jQuery.extend({

    createUploadIframe: function(id, uri)
    {
        //create frame
        var frameId = 'jUploadFrame' + id;

        if(window.ActiveXObject) {
            var io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');
            if(typeof uri== 'boolean'){
                io.src = 'javascript:false';
            }
            else if(typeof uri== 'string'){
                io.src = uri;
            }
        }
        else {
            var io = document.createElement('iframe');
            io.id = frameId;
            io.name = frameId;
        }
        io.style.position = 'absolute';
        io.style.top = '-1000px';
        io.style.left = '-1000px';

        document.body.appendChild(io);

        return io;   
    },
    createUploadForm: function(id, fileElementId)
    {
        //create form 
        var formId = 'jUploadForm' + id;
        var fileId = 'jUploadFile' + id;
        var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>'); 
        var oldElement = jQuery('#' + fileElementId);
        var newElement = jQuery(oldElement).clone();
        jQuery(oldElement).attr('id', fileId);
        jQuery(oldElement).before(newElement);
        jQuery(oldElement).appendTo(form);
        //set attributes
        jQuery(form).css('position', 'absolute');
        jQuery(form).css('top', '-1200px');
        jQuery(form).css('left', '-1200px');
        jQuery(form).appendTo('body');  
        return form;
    },

    ajaxFileUpload: function(s) {
        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout  
        s = jQuery.extend({}, jQuery.ajaxSettings, s);
        var id = s.fileElementId;        
        var form = jQuery.createUploadForm(id, s.fileElementId);
        var io = jQuery.createUploadIframe(id, s.secureuri);
        var frameId = 'jUploadFrame' + id;
        var formId = 'jUploadForm' + id;  

        if( s.global && ! jQuery.active++ )
        {
            // Watch for a new set of requests
            jQuery.event.trigger( "ajaxStart" );
        }            
        var requestDone = false;
        // Create the request object
        var xml = {};   
        if( s.global )
        {
            jQuery.event.trigger("ajaxSend", [xml, s]);
        }            

        var uploadCallback = function(isTimeout)
        {  
            // Wait for a response to come back 
            var io = document.getElementById(frameId);
            try 
            {    
                if(io.contentWindow)
                {
                    xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
                    xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;

                }else if(io.contentDocument)
                {
                    xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
                    xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
                }      
            }catch(e)
            {
                jQuery.handleError(s, xml, null, e);
            }
            if( xml || isTimeout == "timeout") 
            {    
                requestDone = true;
                var status;
                try {
                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if( status != "error" )
                    {
                        // process the data (runs the xml through httpData regardless of callback)
                        var data = jQuery.uploadHttpData( xml, s.dataType );                        
                        if( s.success )
                        {
                            // ifa local callback was specified, fire it and pass it the data
                            s.success( data, status );
                        };                 
                        if( s.global )
                        {
                            // Fire the global callback
                            jQuery.event.trigger( "ajaxSuccess", [xml, s] );
                        };                            
                    } else
                    {
                        jQuery.handleError(s, xml, status);
                    }

                } catch(e) 
                {
                    status = "error";
                    //                    jQuery.handleError(s, xml, status, e);
                };                
                if( s.global )
                {
                    // The request was completed
                    jQuery.event.trigger( "ajaxComplete", [xml, s] );
                };


                // Handle the global AJAX counter
                if(s.global && ! --jQuery.active)
                {
                    jQuery.event.trigger("ajaxStop");
                };
                if(s.complete)
                {
                    s.complete(xml, status);
                } ;                 

                jQuery(io).unbind();

                setTimeout(function()
                    { try 
                        {
                            jQuery(io).remove();
                            jQuery(form).remove(); 

                        } catch(e) 
                        {
                            jQuery.handleError(s, xml, null, e);
                        }         

                    }, 100);

                xml = null;

            };
        }
        // Timeout checker
        if( s.timeout > 0 ) 
        {
            setTimeout(function(){

                if( !requestDone )
                {
                    // Check to see ifthe request is still happening
                    uploadCallback( "timeout" );
                }

            }, s.timeout);
        }
        try 
        {
            var form = jQuery('#' + formId);
            jQuery(form).attr('action', s.url);
            jQuery(form).attr('method', 'POST');
            jQuery(form).attr('target', frameId);
            if(form.encoding)
            {
                form.encoding = 'multipart/form-data';    
            }
            else
            {    
                form.enctype = 'multipart/form-data';
            }   
            jQuery(form).submit();

        } catch(e) 
        {   
            jQuery.handleError(s, xml, null, e);
        }
        if(window.attachEvent){
            document.getElementById(frameId).attachEvent('onload', uploadCallback);
        }
        else{
            document.getElementById(frameId).addEventListener('load', uploadCallback, false);
        }   
        return {abort: function () {}}; 

    },

    uploadHttpData: function( r, type ) {
        var data = !type;
        data = type == "xml" || data ? r.responseXML : r.responseText;
        // ifthe type is "script", eval it in global context
        if( type == "script" )
        {
            jQuery.globalEval( data );
        }

        // Get the JavaScript object, ifJSON is used.
        if( type == "json" )
        {
            eval( "data = " + data );
        }

        // evaluate scripts within html
        if( type == "html" )
        {
            jQuery("<div>").html(data).evalScripts();
        }

        return data;
    }
});

/**
 *  单张图片用法：
<input type="hidden" name='image' id="image_img" class="text" value='' readonly='readonly' />
<span class="image_img_display"><img src="static/images/nopic.png" id="image_img_display" width="100" height="100" onClick="document.getElementById('image_img_upload').click()" /></span>
<input type="file" name="image_img_upload" id="image_img_upload" onchange="image_upload('image_img');" style="display:none;" />

<input type="hidden" name='image' id="image_img" class="text" value='' readonly='readonly' />
<span class="image_img_display"><img src="<{if empty($v.image)}>static/images/nopic.png<{else}><{$URL_UPLOADS}>/image/<{$v.image}><{/if}>" id="image_img_display" width="100" height="100" onClick="document.getElementById('image_img_upload').click()" /></span>
<input type="file" name="image_img_upload" id="image_img_upload" onchange="image_upload('image_img');" style="display:none;" />
*/

/*function image_upload(this){


    var form_filename = obj_id+'_upload';
    $.ajaxFileUpload({
        type: "post",
        fileElementId:form_filename,
        data: {},
        url: "",
        dataType: 'json',
        success: function (data) {
            if(data.error == 0){
                $("#"+obj_id).val(data.filename);
                //$('#'+obj_id+'_display').attr("src",data.tmpurl + data.filename);
                $('.'+obj_id+'_display img').attr("src",data.tmpurl + data.filename);
                $('.'+obj_id+'_display').css("display","block");
            }else{
                alert(data.tips);
            }
        }
    });
}*/

/**
 * 批量上传图片: 
<tr>
    <th>图片：</th>
    <td class="image_td">
        <div></div>
        <div class="addPic">
            <input type="hidden" name="images[]" value="" readonly="readonly" />
            <span>
                <img src="static/images/nopic.png" width="100" height="100" onclick="image_click(this)" />
            </span>
            <button class="sub" onclick="image_delete(this)">删除</button>
        </div>
        <a onclick="create_span_note()">+</a>
        <input type="file" name="image_upload" id="image_upload" onchange="image_upload_batch();" style="display:none;" />
    </td>
</tr>
 */
function create_span_note() {
    var str = ' <div class="addPic"><input type="hidden" name="images[]" value="" readonly="readonly" /><span><img src="static/images/nopic.png" width="100" height="100" onclick="image_click(this)" /></span><button class="sub" onclick="image_delete(this)">删除</button></div>';
    $(".image_td").find("div:last").after(str);
    //$(obj).parent().find("div:last").after(str);
}
function image_delete(obj) {
    $(obj).parent().remove();
}

// span 对象
var span_obj;
function image_click(obj){
    // 把对象存起来
    span_obj = $(obj).parent().parent();
    $('#image_upload').click();
}
function image_upload_batch(){
    var form_filename = 'image_upload';
    $.ajaxFileUpload({
        type: "post",
        fileElementId:form_filename,
        data: {},
        url: "?ct=upload&ac=image&form_filename="+form_filename,
        dataType: 'json',
        success: function (data) {
            if(data.error == 0){
                $(span_obj).find('img').attr("src",data.tmpurl + data.filename);
                $(span_obj).find('input').val(data.filename);
            }else{
                alert(data.tips);
            }
        }
    });
}

$(function(){
    $(document).on({ //阻止冒泡
        dragleave:function(e){    //拖离
            e.preventDefault();
        },
        drop:function(e){  //拖后放
            e.preventDefault();
        },
        dragenter:function(e){    //拖进
            e.preventDefault();
        },
        dragover:function(e){    //拖来拖去
            e.preventDefault();
        }
    });

    // 触发单图片上传
    $("input.single-img-updata").change(function(){
        var id = $(this).parents(".add-img-wrap").attr("id")
        var file = this.files[0];
        updateImgSingle(file,id);//调用单图上传
    });

    // 触发多图片上传
    $("input.more-img-updata").on("change",function(){
        var id = $(this).parents(".add-img-wrap").attr("id")
        var file = this.files[0];
        updateImgMore(file,id);//调用多图上传
    })

    //dropMoreFn('drop_area_more');
    //dropMoreFn('drop_area_more2');
    //多图上传，删除单个
    $("body").on("click",".pre-img-wrap i",function(){
        $(this).parents(".pre-img-wrap").remove();
    })
})
//多图上传拖动
function dropMoreFn(id){
    var boxMore = document.getElementById(id);
    boxMore.addEventListener("drop",function(e){
        var fileList = e.dataTransfer.files[0];
        e.preventDefault();
        updateImgMore(fileList,id);//调用单图上传
    },false);
    dropSingleFn("drop_area");
}
//单图上传拖动
function dropSingleFn(id){
    var boxMore = document.getElementById(id);
    boxMore.addEventListener("drop",function(e){
        var fileList = e.dataTransfer.files[0];
        e.preventDefault();
        updateImgSingle(fileList,id);//调用单图上传
    },false);
}
//多图上传方法
function updateImgMore(file,id){
    if (window.FileReader) {
        var reader = new FileReader();
        reader.readAsDataURL(file);
        //监听文件读取结束后事件
        reader.onloadend = function (e) {
            //var img = '<div class="pull-left pre-img-wrap"><img src="'+e.target.result+'" alt="" class=""><i class="fa fa-close"></i><div>'
            var url = "?ct=upload&ac=redactor&type=html5";
            $.post(url, {
                data:e.target.result,
            }, function(data, status) {
                var img = '<div class="pull-left pre-img-wrap"><input type="hidden" name="'+id+'[]" value="'+data.filename+'"/><img src="'+data.filelink+'" alt="" class=""/><i class="fa fa-close"></i><div>'
                $("#"+id).siblings(".pro-wrap").append(img);
                $(".more-img-updata").val("");
                $(".more-img-updata").siblings("span").remove();

            });
        };
    }
}
//单图上传方法
function updateImgSingle(file,id){
    if (window.FileReader) {
        var reader = new FileReader();
        reader.readAsDataURL(file);
        //监听文件读取结束后事件
        reader.onloadend = function (e) {
            //$(".img").attr("src",e.target.result);    //e.target.result就是最后的路径地址
            var url = "?ct=upload&ac=redactor&type=html5";
            $.post(url, {
                data:e.target.result,
            }, function(data, status) {
                $("#"+id).children("img.img").attr("src", data.filelink);
                $("#"+id).children("input.img").val(data.filename);
            });
        };
    }
}
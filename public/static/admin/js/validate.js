
function checkone(msg){
    $.validator.addMethod("checkOne", function(value,element) {
        if(value.length==0){
            if($(element).parents(".form-group").siblings(".checkonewrap").find(".checkone").val().length==0){
                return false;
            }else{
                $(element).parents(".form-group").siblings(".checkonewrap").removeClass("has-error");
                $(element).parents(".form-group").siblings(".checkonewrap").find("span").remove();
                return true;

            }
        }else{
            $(element).parents(".form-group").siblings(".checkonewrap").removeClass("has-error");
            $(element).parents(".form-group").siblings(".checkonewrap").find("span").remove();
            return true;

        }
    }, "<i class='fa fa-times-circle'></i>  "+ msg);
}
function checkone2(msg){
    $.validator.addMethod("checkOne", function(value,element) {
        if(value.length==0){
            var k = 0;
            $(element).parents(".widget-content").find(".checkonewrap").find(".checkone").each(function(){
                if($(this).val()!=0){
                    k++;
                }
            })
            if(k!=0){
                return true;
            }else{

                return false;

            }

        }else{
            $(element).parents(".widget-content").find(".checkonewrap").removeClass("has-error");
            $(element).parents(".widget-content").find(".checkonewrap").find("span").remove();
            return true;

        }
    },"<i class='fa fa-times-circle'></i>  "+ msg);
}
$(function(){
    var e = "<i class='fa fa-times-circle'></i> ";
    $.validator.addMethod("alnum", function(value,element) {
        var lv = 0;
        if (value.match(/[A-Z]/g)) {
            lv++;
        }
        if (value.match(/[a-z]/g)) {
            lv++;
        }
        if (value.match(/[0-9]/g)) {
            lv++;
        }
        if (lv < 3) {
            return false;
        } else {
            return true;
        }
    }, e + ' 只能包括英文字母和数字');
    $.validator.addMethod("selectRe", function(value,element) {
        console.log(value)
        if (value ==0) {
            return false;
        } else {
            return true;
        }
    }, e +' 必选字段');
    $.validator.addMethod("numReg", function(value,element) {
        if(value.length != 0)
        {
            var reg = /^[0-9a-zA-Z_]{1,}$/;
            if(!value.match(reg)){
                return false;
            }else{
                return true;
            }
        }else
        {
            return true;
        }

    }, e +' 只能包含大小写字母及数字和下划线');

    //网上追逃验证
    $.validator.addMethod("wantedReg", function(value,element) {
        var is_grasped = $("input[name='is_grasped']:checked").val();
        var want = $("input[name='wanted']:checked").val();
        console.log(is_grasped)
        if (is_grasped ==1) {
            if(want>=0){
                $(element).parents(".form-group").find("label").css("color","#555");
                $(element).parents(".form-group").siblings(".form-group").removeClass("has-error").find("span").remove();
                return true;
            }else{
                return false;
            }
        } else {
            $(element).parents(".form-group").siblings(".form-group").removeClass("has-error").find("span").remove();
            return true;
        }
    }, e +' 必选项');

    //强制措施
    $.validator.addMethod("measuresReg", function(value,element) {
        var is_grasped = $("input[name='is_grasped']:checked").val();
        var measures = $("input[name='measures']:checked").val();
        if (is_grasped ==1) {
            if(measures>=0){

                return true;
            }else{
                $(element).parents("form-group").find("span").css("color","#a94442");
                return false;
            }
        } else {
            //$(element).parents("form-group").siblings(".form-group").removeClass("has-success").find("span").remove();
            return true;
        }
    }, e +' 必选项');
    // 已撤网,撤网日期：
    $.validator.addMethod("dateReg", function(value,element) {
        var is_grasped = $("input[name='is_grasped']:checked").val();
        var state = $(element).siblings(".radio").find("input").val();
        var want = $("input[name='wanted']:checked").val();
        if(want==state){
            if(value.length==0){
                $(element).addClass("input-error");
                $(element).parents(".form-group").find("label").css("color","#555");
                $(element).parents(".radio-wrap").addClass("err");
                return false;
            }else{
                $(element).removeClass("input-error");
                $(element).parents(".radio-wrap").removeClass("err")
                return true
            }
        }else{
            if(want==undefined && is_grasped!= 0){
                $(element).parents(".form-group").find("label").css("color","#a94442");
            }
            return true;
        }

    }, e + ' 必填项');

    // 已撤网,撤网日期：
    // $.validator.addMethod("fileReg", function(value,element) {
    //     var len = $(element).parents(".uploader-group").find(".item").length;
    //     var error = '<span id="file-error" class="help-block m-b-none" style="margin-left:200px;">请上传附件</span>';
    //     if(len==0){
    //         $(element).parents(".uploader-group").append(error);
    //         return false;
    //
    //     }else{
    //         $(element).parents(".uploader-group").find("span.help-block").remove();
    //         return true;
    //     }
    //
    // }, '请上传附件');






})
$.validator.setDefaults({
    highlight: function(e) {
        $(e).closest(".form-group").removeClass("has-success").addClass("has-error");
        if($(e).parent("div").hasClass("inner-form")){
            $(e).closest("div").removeClass("has-success").addClass("has-error");
        }


    },

    success: function(e) {
        e.closest(".form-group").removeClass("has-error").addClass("has-success");
        if($(e).parent("div").hasClass("inner-form")){
            e.closest("div").removeClass("has-error").addClass("has-success");
        }
        $(e).remove();
        console.log(2)
    },
    errorElement: "span",
    errorPlacement: function(e, r) {
        e.appendTo(r.is(":radio") || r.is(":checkbox") ? r.parent().parent().parent() : r.parent());

    },
    errorClass: "help-block m-b-none",
    validClass: "help-block m-b-none"
});

$().ready(function() {
    $("#validateForm").validate();
    var e = "<i class='fa fa-times-circle'></i> ";
    $("#userValidateForm").validate({
        rules: {
            username:{
                required: !0,
                minlength: 2
            },
            password:{
                minlength:6,
                alnum:true
            }

        }
    })
});


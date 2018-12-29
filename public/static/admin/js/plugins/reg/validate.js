$(function() {
    $.validator.addMethod("alnum", function (value, element) {
        var lv = 0;
        if (value.match(/[a-z]/g)) {
            lv++;
        }
        if (value.match(/[0-9]/g)) {
            lv++;
        }
        if (value.match(/(.[^a-z0-9])/g)) {
            lv++;
        }
        if (lv < 3) {
            return false;
        } else {
            return true;
        }
    }, '密码强度不够，必须包含大小写字母以及数字');
    $.validator.addMethod("imgLen", function (value, element) {
        var len = $(element).parents(".add-img-wrap").siblings(".pro-wrap").find(".pre-img-wrap").length;
        if(len != 0){
            return true;
        }else{
            return false;
        }
    }, '请上传证件照');
})
$.validator.setDefaults({
    highlight: function(e) {
        $(e).closest(".form-item").removeClass("has-success").addClass("has-error");
    },
    success: function(e) {
        e.closest(".form-item").removeClass("has-error").addClass("has-success");
    },
    errorElement: "span",
    errorPlacement: function(e, r) {
        e.appendTo(r.is(":radio") || r.is(":checkbox") ? r.parent().parent().parent() : r.parent());

    },
    errorClass: "help-block m-b-none",
    validClass: "help-block m-b-none"
}),
    $().ready(function() {
        $(".conpany-form").validate({
            rules: {
                password: {
                    minlength: 6,
                    alnum: true
                },
                image_img_upload:{
                    imgLen:true
                }
            }

        });
    });


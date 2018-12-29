$(function(){
    //验证------------------------------------------start-------
    var checkoneFn = function(gets,obj,curform,regxp){    //多选一自定义方法
        /*参数gets是获取到的表单元素值，
          obj为当前表单元素，
          curform为当前验证的表单，
          regxp为内置的一些正则表达式的引用。*/
        var flag = 0;
        var len = curform.find(".checkone").length;
        curform.find(".checkone").each(function(){
            if($(this).val().length == 0){
                flag++
            }
        });
        if(flag==len){
            return false;
        }else{
            $(".checkone").siblings("span").remove();
            $(".checkone").parent("div").removeClass("has-error").addClass("has-success");
            $(".checkone").parents(".form-group").find(".control-label").css("color","#1ab394");
            return true;

        }
    }
    var fileLen = function(gets,obj,curform,regxp){
        var len = $(obj).siblings(".uploader-list").find(".item").length;
        console.log(len)
        if(len>0){
            return true;
        }else{
            return false;
        }
    }
    $(".form-horizontal").Validform({
        tiptype:function(msg,o,cssctl){
            if(o.type==3){
                $(o.obj).addClass().parent("div").addClass("has-error");
                var str = '<span class="Validform_checktip Validform_wrong"><i class="fa fa-times-circle"></i> '+ msg+'</span>';
                $(o.obj).closest("div").children(".Validform_checktip").remove();
                $(o.obj).closest("div").append(str);
                $(o.obj).parents("div.form-group").find(".control-label").css("color","#ed5565");

            }else{
                $(o.obj).parents("div.form-group").find(".control-label").css("color","#1ab394");
                $(o.obj).closest("div").removeClass("has-error").addClass("has-success");
                if($(o.obj).attr("sucmsg")){
                    $(o.obj).closest("div").children(".Validform_checktip").addClass("Validform_right").html('<i class="fa fa-check-circle-o"></i> '+$(o.obj).attr("sucmsg"));
                }else{
                    $(o.obj).closest("div").children(".Validform_checktip").remove();
                }

            }
        },
        showAllError:true,
        datatype:{    //自定义方法
            "checkone":checkoneFn,    //自定义调用fileLen
            "fileLen":fileLen
        }
    });
    //tipsmsg 属性
    $("form").find("[tipsmsg]").each(function(){
        var str = '<span class="Validform_checktip"><i class="fa fa-info-circle"></i> '+ $(this).attr('tipsmsg')+'</span>';
        $(this).closest("div").append(str);
    })

    //验证------------------------------------------end-------
})
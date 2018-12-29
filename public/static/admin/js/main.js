var str = "Adf";
var qs = (function(a) {
    if (a == '') return {};
    var b = {};
    for (var i = 0; i < a.length; ++i)
    {
        var p=a[i].split('=');
        if (p.length != 2) continue;
        b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, ' '));
    }
    return b;
})(window.location.search.substr(1).split('&'));

function pageFormSubmit() {
    qs['page_no'] = $('.page_no').val();
    qs['page_size'] = $('.page_size').val();
    location.href = '?'+$.param(qs);
}

$("body").on("click", ".parent", function () {
    if (this.checked) {
        $(this).parents(".table").find(".child").each(function (i) {
            $(this).prop("checked",true);
        });

        $(this).parents(".widget-box").find(".child").each(function (i) {
            $(this).prop("checked",true);
        });
    } else {
        $(this).parents("table").find(".child").each(function (i) {
            $(this).prop("checked",false);
        });
        $(this).parents(".widget-box").find(".child").each(function (i) {
            $(this).prop("checked",false);
        });
    }
})
function selectFn(){ //select2多选

    $("body").find("select[data-plugin=select2]").each(function(index){
        var url = $(this).data("url");
        var len = $(this).data("minimum-input-length");
        if(len==undefined && url){
            len=1;
        }else{
            len = 0;
        }
        var _this = $(this);
        if($(this).data("url")){               //判断是不是ajax
            $(this).select2({
                ajax: {
                    url:url,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                        };
                    },
                    processResults: function (data,params) {
                        return {
                            results: data.results,//itemList
                        };
                    },
                    results: function (data, page) { return data; },
                    cache: true
                },
                minimumInputLength:len,
                placeholder: "select a option"
            })
        }else{
            $(this).select2({
                minimumInputLength:len,
                placeholder: "select a option"
            });
        }
    })
    //验证
    $("select[data-plugin=select2]").change(function(){
            console.log($(this).val())
            var obj = $(this).val();
            var re = $(this).attr("required");//获取是否要验证
            if(re){
                if(obj!=null){
                    if(obj.length>0){
                        $(this).parents(".form-group").removeClass("has-error").find("span.help-block").remove();
                    }
                }else{
                    $(this).parents(".form-group").removeClass("has-error").find("span.help-block").remove();
                    $(this).parents(".form-group").addClass("has-error").children("div").append('<span  class="help-block m-b-none"><i class="fa fa-times-circle"></i>  必填字段</span>');
                }
            }
        $(this).siblings("input[type=hidden]").val(obj);
    })
    //select2 选择排序问题
    $(".select2").on("select2:select",function(evt){
        var element=evt.params.data.element;
        var $element=$(element);
        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
    });
}
function tokenFn(){
    $('input[data-plugin=tokenfield]').each(function(){
        var option = $(this).attr("data-max-option");
        var tokenArr=[];
        var str=$(this).val();
        var result=str.split(",");
        var re = $(this).attr("required");
        if(str!=""){
            for(var i=0;i<result.length;i++){
                tokenArr.push(result[i])
            }
        }
        if(option){
            $(this).parents(".form-group").addClass("option-wrap");
        }
        $(this).tokenfield()
            .on('tokenfield:createdtoken', function (e) {
                var val = e.attrs.value;
                tokenArr.push(val);
                 $(this).parents(".tokenfield ").siblings("input[type=hidden]").val(tokenArr)
                if(re){
                    if(tokenArr.length>0){
                        $(this).parents(".form-group").removeClass("has-error").find("span.help-block").remove();
                    }else{
                        $(this).parents(".form-group").removeClass("has-error").find("span.help-block").remove();
                        $(this).parents(".form-group").addClass("has-error").children("div").append('<span  class="help-block m-b-none"><i class="fa fa-times-circle"></i>  必填字段</span>')
                    }
                }
                console.log($(this).parents(".tokenfield ").siblings("input[type=hidden]").val())
            })
            .on('tokenfield:removedtoken', function (e) {
                var val = e.attrs.value;
                var ind = tokenArr.indexOf(val);
                tokenArr.splice(ind,1);
                $(this).parents(".tokenfield ").siblings("input[type=hidden]").val(tokenArr);

                if(re){
                    if(tokenArr.length>0){
                        $(this).parents(".form-group").removeClass("has-error").find("span.help-block").remove();
                    }else{
                        $(this).parents(".form-group").removeClass("has-error").find("span.help-block").remove();
                        $(this).parents(".form-group").addClass("has-error").children("div").append('<span  class="help-block m-b-none"><i class="fa fa-times-circle"></i>  必填字段</span>')
                    }
                }
                console.log($(this).parents(".tokenfield ").siblings("input[type=hidden]").val())
            });
    })
}
var plt = {
    confirmAction:function(e){
        var title = e.target.getAttribute('data-title'),
            href =e.target.getAttribute('data-href'),
            content = e.target.getAttribute('data-content');
        if (content == '' || content == null) {
            content = '确认进行该操作吗?';
        }
        parent.layer.confirm(content,{icon: 3, title:title},
            function(index){
                window.location.href=href;
                parent.layer.close(index);
            },function(index){

            });
    },
    subform:function(e,cl){
        var title = e.target.getAttribute('data-title'),
            href =e.target.getAttribute('data-href');

        $("[data-href='"+href+"']").parents("form").attr("action",href);
        var valArr = new Array;
        var k=0;
        $("."+cl).each(function(i){
            if(this.checked)
            {
                valArr[k] = $(this).val();
                k++;
            }
        });
        if(cl==undefined || valArr.length>0){
            parent.layer.confirm(str,{icon: 3, title:title},
                function(index){
                    $("[data-href='"+href+"']").parents("form").submit();
                    parent.layer.close(index);
                },function(index){

                });
        }else{
            parent.layer.alert(str,{ shadeClose: true});
            return;
        }
    },
    //弹出用户基本信息
    openIframe: function (e) {
        var title = e.target.getAttribute('data-title'),
            href =e.target.getAttribute('data-href');
        parent. layer.open({
            type: 2,
            title: decodeURI(title) + title,
            shade: 0.3,
            maxmin: true,
            shadeClose: true,
            area: ['600px', '500px'],
            content: href
        });

    },
    getDateEndMonth:function(id){//日期控件，截至月份
        //日期控件
        $("#"+id).datetimepicker({
            format: 'yyyy-mm',
            language: 'zh-CN',
            pickDate: true,
            pickTime: false,
            minView: 'year',
            startView:3,
            autoclose: true
        });
        function getNowDate(id){ //默认获取当前日期
            var today = new Date();
            var nowdate = (today.getFullYear()) + "-" + (today.getMonth() + 1) + "-" + today.getDate();
            //对日期格式进行处理
            var date = new Date(nowdate);
            var mon = date.getMonth() + 1;
            var mydate = date.getFullYear() + "-" + (mon < 10 ? "0" + mon : mon);
            $("#"+id).attr("placeholder",mydate)
        }
    }
}

function subform(url){
    $("#myform").attr('action', url);
    $("#myform").submit();
}

$(function(){
    $(document).on("click",function(e){
        if(parent.closeDrop){
            parent.closeDrop(e);
        }
    });
    selectFn();  //多选调用
    tokenFn();   //

    $("body").on("click","#content",function(){  //屏幕小于768时调用父级
        if($("body",parent.document).width()<768){
            $("body",parent.document).addClass("mini-navbar");
            $(".navbar-minimalize i",parent.document).removeClass("fa-long-arrow-left ").addClass("fa-bars")
        }

    })

    $(".open-new-href").on("click",function(){
        var subUrl = $(this).data("href");
        var url = subUrl.substring(0,subUrl.lastIndexOf("?"))
        parent.parentLink(url,subUrl);
    })
})


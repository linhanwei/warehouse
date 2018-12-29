/**
 * Created by cloud on 2017/9/7.
 */
$(document).ready(function () {
    plt.check("trCheckALl","trCheck")//全选
    //点击遮住层隐藏
    $(".modal-pup").on("click",function(){
        plt.rightPupHide();
    })
    //下级部门排序
    var sortFn = function() {
        var updateOutput = function (e) {
            var list = e.length ? e : $(e.target), output = list.data("output");
            if (window.JSON) {
                output.val(window.JSON.stringify(list.nestable("serialize")))
            } else {
                output.val("浏览器不支持")
            }
        };
        $("#nestable").nestable({group: 1}).on("change", updateOutput);
        updateOutput($("#nestable").data("output", $("#nestable-output")));
    }

    $(".sort").on("click",function(){
        $(".dd-handle").css("background","rgb(253, 253, 253)");
        $(".fa-arrows-alt").show();
        $(".handle").addClass("dd-handle")
        sortFn();
        $(".config").show();
    })






    //添加人员  ------------------------------------start
    var selectArr = [];//定义一个对象
    $("body").on("click", "#checkAll" , function () {  //全选
        if (this.checked) {
            selectArr = [];
            $(".check" ).each(function (i) {
                this.checked = true;
                var img = $(this).siblings("img").attr("src");
                var name = $(this).siblings("span").html();
                selectArr.push({
                    img:img,
                    name:name
                })
            });
        } else {
            $(".check" ).each(function (i) {
                this.checked = false;
                selectArr = []
            });
        }
        var str = "";
        for(var i=0;i<selectArr.length;i++){
            str+= '<li><img src="'+selectArr[i].img+'" alt=""> <span>'+selectArr[i].name+'</span> <i class="fa fa-close"></i></li>'
        }
        $("#c_modal .modal-body-r ul").html(str);
    })

    $("body").on("click", ".check" , function (){
        var img = $(this).siblings("img").attr("src");
        var name = $(this).siblings("span").html();
        var obj = {
            img:img,
            name:name
        }
        if(this.checked){
            if(JSON.stringify(selectArr).indexOf(name)==-1){
                selectArr.push(obj)
            }
        }else{
            for(var i=0;i<selectArr.length;i++){
                if(name==selectArr[i].name){
                    selectArr.splice(i,1)
                }
            }
        }
        $("#checkAll").attr("checked",false);
        var str = "";
        for(var i=0;i<selectArr.length;i++){
            str+= '<li><img src="'+selectArr[i].img+'" alt=""> <span>'+selectArr[i].name+'</span> <i class="fa fa-close"></i></li>'
        }
        $("#c_modal .modal-body-r ul").html(str);
    })

    $("body").on("click","#c_modal .fa-close",function(){  //删除事件
        var img = $(this).siblings("img").attr("src");
        var name = $(this).siblings("span").html();
        $(this).parents("li").remove();
        for(var i=0;i<selectArr.length;i++){
            if(name==selectArr[i].name){
                selectArr.splice(i,1)
            }
        }
        $(".select-con").find("label span").each(function(i){
            var o = $(this).html();
            $("#checkAll").attr("checked",false)
            if(o==name){
                $(".select-con label input.check").eq(i).attr("checked",false);
            }
        })
    })
    $("body").on("click",".selectBtn",function(){
        var val = "";
        $("#c_modal .modal-body-r ul").find("li span").each(function(i){
            val += $(this).html() + ",";
        })
        $("#add-user").val(val.substring(0,val.length-1));
    })
    //添加人员  ------------------------------------end


    //选部门  ------------------------------------start
    var dpSelectArr = [];//定义一个对象
    $("body").on("click", "#dp-checkAll" , function () {  //全选
        if (this.checked) {
            dpSelectArr = [];
            $(".dp-check" ).each(function (i) {
                this.checked = true;
                var name = $(this).siblings("span").html();
                dpSelectArr.push({
                    name:name
                })
            });
        } else {
            $(".dp-check" ).each(function (i) {
                this.checked = false;
                dpSelectArr = []
            });
        }
        var str = "";
        for(var i=0;i<dpSelectArr.length;i++){
            str+= '<li> <i class="fa fa-sitemap"></i> <span>'+dpSelectArr[i].name+'</span> <i class="fa fa-close"></i></li>'
        }
        $("#department_modal .modal-body-r ul").html(str);
    })

    $("body").on("click", ".dp-check" , function (){ //单选事件
        var name = $(this).siblings("span").html();
        var obj = {
            name:name
        }
        if(this.checked){
            if(JSON.stringify(dpSelectArr).indexOf(name)==-1){
                dpSelectArr.push(obj)
            }
        }else{
            for(var i=0;i<dpSelectArr.length;i++){
                if(name==dpSelectArr[i].name){
                    dpSelectArr.splice(i,1)
                }
            }
        }
        $("#dp-checkAll").attr("checked",false);
        var str = "";
        for(var i=0;i<dpSelectArr.length;i++){
            str+= '<li> <i class="fa fa-sitemap"></i> <span>'+dpSelectArr[i].name+'</span> <i class="fa fa-close"></i></li>'
        }
        $("#department_modal .modal-body-r ul").html(str);
    })

    $("#department_modal .modal-body-r ul").on("click"," .fa-close",function(){  //删除事件
        var name = $(this).siblings("span").html();
        $(this).parents("li").remove();
        for(var i=0;i<dpSelectArr.length;i++){
            if(name==dpSelectArr[i].name){
                dpSelectArr.splice(i,1)
            }
        }
        $("#department_modal .select-con").find("label span").each(function(i){
            var o = $(this).html();
            $("#dp-checkAll").attr("checked",false)
            if(o==name){
                $("#department_modal .select-con label input.dp-check").eq(i).attr("checked",false);
            }
        })
    })

    $("body").on("click",".dpselectBtn",function(){ //点击确定按钮
        var val = "";
        $("#department_modal .modal-body-r ul").find("li span").each(function(i){
            val += $(this).html() + ",";
        })
        $("#department").val(val.substring(0,val.length-1));
    })
    //选部门  ------------------------------------end


    //选择主管  ------------------------------------start
    $("body").on("click","#director_modal ul li",function(){
        var str = "<li>"+$(this).html()+"</li>";
        $("#director_modal .modal-body-r ul").html(str);
    })
    $("body").on("click",".directorBtn",function(){ //点击确定按钮
        var val = $("#director_modal").find(".modal-body-r ul li span").text();

        $("#director").val(val);
    })

    //选择主管  ------------------------------------end



    //角色管理交互
    $("body").on("click",".arrow",function(){
        if($(this).hasClass("fa-caret-down")){
            $(this).addClass("fa-caret-left").removeClass("fa-caret-down");
            $(this).parent("p").siblings("ul").slideUp();
        }else{
            $(this).addClass("fa-caret-down").removeClass("fa-caret-left");
            $(this).parent("p").siblings("ul").slideDown();
        }
    })
    $("body").on("click",".role-second li",function(){
        $(".role-second li").removeClass("active");
        $(this).addClass("active");
    })


    $("body").on("click",".td-checkall",function(){
        if (this.checked) {
            $(this).parents(".table-right-item").find("input").each(function (i) {
                this.checked = true;
            });
        } else {
            $(this).parents(".table-right-item").find("input").each(function (i) {
                this.checked = false;
            });
        }
    })
    $("body").on("click",".td-checkall-left",function(){
        if (this.checked) {
            $(this).parents("tr").find("input").each(function (i) {
                this.checked = true;
            });
        } else {
            $(this).parents("tr").find("input").each(function (i) {
                this.checked = false;
            });
        }
    })

    //右滑出btn取消事件
    function hidePup(){
        $(".modal-pup").hide();
        $(".modal-con").animate({right:-1000});
    }
    $("body").on("click",".pup-btn-group .cancel-btn",function(){
        hidePup();
    })

});
$(function(){

    //默认获取当前日期
    function getNoWDateFn(id){
        var today = new Date();
        var nowdate = (today.getFullYear()) + "-" + (today.getMonth() + 1) + "-" + today.getDate();
        //对日期格式进行处理
        var date = new Date(nowdate);
        var mon = date.getMonth() + 1;
        var day = date.getDate();
        var mydate = date.getFullYear() + "-" + (mon < 10 ? "0" + mon : mon) + "-" + (day < 10 ? "0" + day : day);
        document.getElementById(id).value = mydate;
    }
    //日期控件
    $("#startDate,#endDate").datetimepicker({
        format: 'yyyy-mm-dd',
        language: 'zh-CN',
        pickDate: true,
        pickTime: false,
        minView: 'month',
        todayBtn: true,
        todayHighlight: true
    });
    getNoWDateFn("startDate");
    getNoWDateFn("endDate");

})
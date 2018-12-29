//列表样式控制脚本
var body = (document.compatMode && document.compatMode.toLowerCase() == "css1compat") ? document.documentElement : document.body;
function ResizeTable() {
    var sideWidth = document.getElementById("side-menu").clientWidth ;
    if (document.getElementById('content-main') != null) {
        var tableDiv = document.getElementById('content-main');
        var warpDiv = document.getElementById("page-wrapper");
        tableDiv.style.height = '' + Math.max((body.clientHeight - tableDiv.offsetTop), 0) + "px";

       /* if(body.clientWidth<970){
            tableDiv.style.width = '' + Math.max((body.clientWidth - 70), 0) + "px";
            warpDiv.style.width = '' + Math.max((body.clientWidth - 70), 0) + "px";
        }else{
            warpDiv.style.width = '' + Math.max((body.clientWidth - 220), 0) + "px";
            tableDiv.style.width = '' + Math.max((body.clientWidth - 220), 0) + "px";
        }*/
    }
}
$(function(){
    $(window).resize(function () {
        ResizeTable()
    });

})

window.onload = function () {

    ResizeTable();
    window.onresize = ResizeTable;

    /*全屏显示*/
    var flag = true;

    document.getElementById("screen-btn").onclick=function(){

        var elem = document.getElementById("wrapper");
        if(flag){
            requestFullScreen(elem);
            flag = false
        }else{
            exitFull();
            flag = true
        }

    };

    function requestFullScreen(element) {
        var requestMethod = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullScreen;
        if (requestMethod) {
            requestMethod.call(element);
        } else if (typeof window.ActiveXObject !== "undefined") {
            var wscript = new ActiveXObject("WScript.Shell");
            if (wscript !== null) {
                wscript.SendKeys("{F11}");
            }
        }
    }
    function exitFull() {
        // 判断各种浏览器，找到正确的方法
        var exitMethod = document.exitFullscreen || //W3C
            document.mozCancelFullScreen || //Chrome等
            document.webkitExitFullscreen || //FireFox
            document.webkitExitFullscreen; //IE11
        if (exitMethod) {
            exitMethod.call(document);
        }
        else if (typeof window.ActiveXObject !== "undefined") {//for Internet Explorer
            var wscript = new ActiveXObject("WScript.Shell");
            if (wscript !== null) {
                wscript.SendKeys("{F11}");
            }
        }
    }
    /*全屏显示*/
}
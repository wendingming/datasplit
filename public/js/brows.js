
var StaticUrl = '\/\/bk.st.styleweb.com.cn\/';
var sUserAgent= navigator.userAgent.toLowerCase();
var bIsIpad= sUserAgent.match(/ipad/i) == "ipad";
var bIsIphoneOs= sUserAgent.match(/iphone os/i) == "iphone os";
var bIsMidp= sUserAgent.match(/midp/i) == "midp";
var bIsUc7= sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
var bIsUc= sUserAgent.match(/ucweb/i) == "ucweb";
var bIsAndroid= sUserAgent.match(/android/i) == "android";
var bIsCE= sUserAgent.match(/windows ce/i) == "windows ce";
var bIsWM= sUserAgent.match(/windows mobile/i) == "windows mobile";

var browser=navigator.appName;
var b_version=navigator.appVersion;
var version=b_version.split(";");
var _vm = {};
var trim_Version= version[1] ? version[1].replace(/[ ]/g,"") : "";
var isIe = {
    ie6:browser=="Microsoft Internet Explorer" && trim_Version=="MSIE6.0",
    ie7:browser=="Microsoft Internet Explorer" && trim_Version=="MSIE7.0",
    ie8:browser=="Microsoft Internet Explorer" && trim_Version=="MSIE8.0",
    ie9:browser=="Microsoft Internet Explorer" && trim_Version=="MSIE9.0"
}

function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}

var version = {'css':'201891217542','js' :'201882815759'};


function setCookie(c_name,value,expiredays)
{
    var exdate=new Date()
    exdate.setDate(exdate.getDate()+expiredays)
    document.cookie=c_name+ "=" +escape(value)+";path=/"+((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}


setCookie("time_offset", -new Date().getTimezoneOffset()/60);
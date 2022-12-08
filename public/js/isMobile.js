<!--
var mobilephone = (/android|blackberry|iphone|ipad|ipod|iemobile|opera|webos mini/i.test(navigator.userAgent.toLowerCase()));

if (mobilephone && $.cookie("mobileweb") == null) {
    if (confirm(MSGChgMobileWeb)) {
        $.cookie("mobileweb", 1, { expires: 7, path:'/', domain: generalDomain });
        location.reload();
    } else {
        $.cookie("mobileweb", 0, { expires: 7, path:'/', domain: generalDomain });
    }
}
//-->
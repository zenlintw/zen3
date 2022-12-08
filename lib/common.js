    /**
     * 一些常用的函式
     * @author ShenTing Lin
     * @since 2003-01-09
     * @version $Id: common.js,v 1.1 2009-06-25 09:26:48 edi Exp $
     **/

    /**
     * checkbox 的選取動作
     * @pram string objName 指定在哪個物件上，不指定則預設在 document 上
     * @pram integer actType 選取的動作
     *     1. 0: 全部取消
     *     2. 1: 全部選取
     *     3. 2: 反向選取
     * @return integer 錯誤編號
     *     1. 0: 沒有錯誤
     *     2. 1: 找不到指定的物件
     *     3. 2: 找不到任何的 input 物件
     * @access public
     *
     * 其它說明：新增一項屬性 exclude：在 input 中若有設定這項屬性，則程式會忽略該物件
     * 例如：
     *     以下的例子，本程式將會忽略該物件
     *
     *         <input type="checkbox" exclude> or
     *         <input type="checkbox" exclude="true"> (建議)
     *
     *     而以下的例子，該物件會依指定的動作而有所動作
     *
     *         <input type="checkbox"> (建議) or
     *         <input type="checkbox" exclude="false">
     **/
    function select_func(objName, actType) {
        var obj = null, nodes = null, attr = null;
        var cnt = 0;
        var isSel = false;

        if (typeof(actType) == "boolean") {
            actType = actType ? 1 : 0;
        }
        obj = (objName == "") ? document : document.getElementById(objName);
        if ((typeof(obj) != "object") || (obj == null)) return 1;
        nodes = obj.getElementsByTagName("input");
        if ((nodes == null) || (nodes.length <= 0)) return 2;
        cnt = nodes.length;
        if (parseInt(actType) < 2) {
            // 全選或全消 (select all or cancel select)
            isSel = (actType > 0) ? true : false;
            for (var i = 0; i < cnt; i++) {
                if (nodes[i].type == "checkbox") {
                    attr = nodes[i].getAttribute("exclude");
                    if ((attr == null) || (attr == "false"))
                        nodes[i].checked = isSel;
                }
            }
        } else {
            // 反向選取 (inverse select)
            for (var i = 0; i < cnt; i++) {
                if (nodes[i].type == "checkbox") {
                    attr = nodes[i].getAttribute("exclude");
                    if ((attr == null) || (attr == "false"))
                        nodes[i].checked = !nodes[i].checked;
                }
            }
        }
        return 0;
    }

////////////////////////////////////////////////////////////////////////////////
    // 檢查瀏覽器 (check browser)
    if ((typeof(isIE) == "undefined") || (typeof(isMz) == "undefined")) {
        var isIE = false, isMZ = false, BVER = '';
        chkBrowser();
    }

    /**
     * 偵測瀏覽器版本
     */
    function chkBrowser() {

        var explorer = window.navigator.userAgent,
        compare = function(s) { return (explorer.indexOf(s) >= 0); },
        ie11 = (function() { return ("ActiveXObject" in window) })();

        if (compare("MSIE") || ie11) { BVER = RegExp.$1;isIE = true; }
        else if (compare("Firefox") && !ie11) { isMZ = true; }
        else if (compare("Chrome") && !ie11) { isMZ = true; }
        else if (compare("Opera") && !ie11) { isMZ = true; }
        else if (compare("Safari") && !ie11) { isMZ = true; }
        else if (compare("Edge") && !ie11) { isMZ = true; }
//        if (navigator.userAgent.search(/ MSIE (\d+\.\d+)/) != -1)
//        {
//            BVER = RegExp.$1;isIE = true;
//        }
//        else if (navigator.userAgent.indexOf('Gecko') != -1)
//        {
//            isMZ = true;
//        }
    }


    /**
     * 給 IE 呼叫用的 (for IE)
     **/
    function showDialogIE(uri, modal, argument, opCenter, opTop, opLeft, opWidth, opHeight, opExtra) {
        var strFace = "";
        var aryFace = new Array();
        var ieDialog = null;

        if (opCenter) {
            aryFace[aryFace.length] = "center=yes";
        } else {
            aryFace[aryFace.length] = "center=no";
            aryFace[aryFace.length] = "dialogTop="  + opTop;
            aryFace[aryFace.length] = "dialogLeft=" + opLeft;
        }

        aryFace[aryFace.length] = "help=no";
        aryFace[aryFace.length] = "dialogWidth="  + opWidth;
        aryFace[aryFace.length] = "dialogHeight=" + opHeight;
        aryFace[aryFace.length] = opExtra;
        strFace = aryFace.toString();
        strFace = strFace.replace(/,/ig, ';', strFace);
        strFace = strFace.replace(/ /ig, '', strFace);
        strFace = strFace.replace(/;;/ig, ';', strFace);

        if (modal) {
            ieDialog = window.showModalDialog(uri, argument, strFace);
        } else {
            ieDialog = window.showModelessDialog(uri, argument, strFace);
        }
        return ieDialog;
    }

    /**
     * 給 Mozilla 呼叫用的 (for Mozilla)
     **/
    var mzDialog    = null;
    var mzDialogCnt = Math.ceil(Math.random() * 100000);
    function showDialogMZ(uri, modal, argument, opCenter, opTop, opLeft, opWidth, opHeight, opExtra) {
        var strFace = "";
        var aryFace = new Array();
        var oT = 0, oL = 0;

        if (opCenter) {
            oT = parseInt(parseInt(window.outerHeight) / 2) - parseInt(parseInt(opHeight) / 2);
            oL = parseInt(parseInt(window.outerWidth) / 2) - parseInt(parseInt(opWidth) / 2);
            oT = oT + parseInt(window.screenY);
            oL = oL + parseInt(window.screenX);
            aryFace[aryFace.length] = "top="  + oT + "px";
            aryFace[aryFace.length] = "left=" + oL + "px";
        } else {
            aryFace[aryFace.length] = "top="  + opTop;
            aryFace[aryFace.length] = "left=" + opLeft;
        }

        aryFace[aryFace.length] = "toolbar=0";
        aryFace[aryFace.length] = "menubar=0";
        aryFace[aryFace.length] = "dependent=1";
        aryFace[aryFace.length] = "dialog=1";
        aryFace[aryFace.length] = (modal) ? "modal=1" : "modal=0";
        aryFace[aryFace.length] = "width="  + opWidth;
        aryFace[aryFace.length] = "height=" + opHeight;
        aryFace[aryFace.length] = opExtra;

        strFace = aryFace.toString();
        strFace = strFace.replace(/;/ig, ",", strFace);
        strFace = strFace.replace(/ /ig, "", strFace);
        strFace = strFace.replace(/,,/ig, ",", strFace);

        mzDialog = window.open(uri, "mzDialog" + mzDialogCnt, strFace);
        if (typeof mzDialog === "undefined") {
            // need fix popup block
            alert('Please disable popup blocker.');
            return false;
        }
        mzDialog.dialogArguments = argument;
        if (modal) {
            if ((mzDialog != null) && !mzDialog.closed) {
                window.onfocus = function () {mzDialog.focus();};
            }
            return mzDialog.returnValue;
        } else {
            window.onfocus = function () {};
            mzDialogCnt = Math.ceil(Math.random() * 100000);
            return mzDialog;
        }
    }

    /**
     * 共通呼叫介面
     *
     *     在這一部分 IE 還有一個介面參數 help，這會在標題列上顯示一個問號。
     *     但是，由於 Mozilla 沒有支援，所以直接將它設為關閉，其實這也是沒有多大作用
     *
     * @param string  uri      : 要開啟的網址
     * @param boolean modal    : 開啟的視窗類型
     *     true  : modal
     *     false : modeless
     * @param any     argument : 要傳遞給 dialog 的資料
     * @param boolean opCenter : 是否置中
     *     true  : 置中
     *     false : 自行指定
     * @param string  opTop    : 視窗在桌面上的頂端位置
     * @param string  opLeft   : 視窗在桌面上的左端位置
     * @param string  opWidth  : 視窗的寬度
     * @param string  opHeight : 視窗的高度
     * @param string  opExtra  : 其他屬性
     *     status     = 1, 0 (1: 顯示, 0: 隱藏)
     *     resizeable = 1, 0 (1: 可  , 0: 不可)
     * @return
     **/
    function showDialog(uri, modal, argument, opCenter, opTop, opLeft, opWidth, opHeight, opExtra) {
        var oW = 0, oH = 0;
        // 建立預設的長寬 (default width and height)
        oW = parseInt(opWidth);
        oW = (isNaN(oW) || (oW == 0)) ? "200px" : oW + "px";
        oH = parseInt(opHeight);
        oH = (isNaN(oH) || (oH == 0)) ? "200px" : oH + "px";
        if (isIE) return showDialogIE(uri, modal, argument, opCenter, opTop, opLeft, opWidth, opHeight, opExtra);
        if (isMZ) return showDialogMZ(uri, modal, argument, opCenter, opTop, opLeft, opWidth, opHeight, opExtra);
    }

    /**
     * 以window.open方式開啟視窗
     * @param url         欲開啟之url
     * @param winname   開啟window name
     * @param w            寬度
     * @param h            高度
     */
    function OpenNamedWin(url,winname, w, h) {
        var wL = (screen.width-w)/2;
        var wT = (screen.height-h)/2;
        _Win = window.open(url,winname,"width="+w+",height="+h+",resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,scrollbars=no,resizable=1");
        _Win.moveTo(wL, wT);    // 將視窗居中 (center)
    }

    /**
      * 將 html 的 tag 轉成 普通文字 顯示
      * @param string str 要轉換的字串
      * @return string 已轉換好的字串
      **/
    function htmlspecialchars(str) {
        if (typeof(str) == 'undefined') return false;

        return str.replace(/&/ig, '&amp;').replace(/</ig, '&lt;').replace(/>/ig, '&gt;').replace(/'/ig, '&#039;').replace(/"/ig, '&quot;');
    }

    /**
      * 將 普通文字 轉成 html 的 tag 顯示
      * @param string str 要轉換的字串
      * @return string 已轉換好的字串
      **/
    function un_htmlspecialchars(str) {
        if (typeof(str) == 'undefined') return false;

        return str.replace(/&amp;/ig, '&').replace(/&lt;/ig, '<').replace(/&gt;/ig, '>').replace(/&#039;/ig, '\'').replace(/&quot;/ig, '"');
    }

    /**
     * 取得節點的值
     * @param object node : 要從哪個節點中取值
     * @param string tag  : 節點中的哪個 tag
     * @return string result : 取得的值。若無法取值，則回傳空字串
     **/
    function getNodeValue(node, tag) {
        var childs = null;

        if ((typeof(node) != "object") || (node == null))
            return "";
        childs = node.getElementsByTagName(tag);
        if ((childs == null) || (childs.length <= 0)) return "";
        if (childs[0].hasChildNodes()) {
            return childs[0].firstChild.data;
        } else {
            return "";
        }
    }

    /**
     * 去掉字串前後空白
     * @param string val 要去掉空白的字串
     * @return 前後去掉空白後的字串
     */
    function trim(val) {
          return val.replace(/^\s+|\s+$/g, "");
    }

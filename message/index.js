    /**
     * index.php include JavaScript
     *
     * 建立日期：2003/05/01
     * @author  ShenTing Lin
     * @version $Id: index.js,v 1.1 2010/02/24 02:40:17 saly Exp $
     * @copyright 2003 SUNNET
     **/

    /**
     * write new message
     * @param
     * @return
     **/
    function post(){
        remove_unload();
        location.replace('write.php');
    }

    /**
     * read message
     * @param integer val : message serial
     * @return
     **/
    function read(val){
        var obj = document.getElementById("readFm");
        if ((typeof(obj) != "object") || (obj == null)) return '';
        remove_unload();
        obj.serial.value = val;
        obj.submit();
    }

    /**
     * delete selected message
     * @param
     * @return
     **/
    function del() {
        var nodes = null, attr = null, obj = null;
        var cnt = 0;

        obj = document.getElementById("mainFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        nodes = document.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("type");
            if ((nodes[i].name != "ck") && (attr == "checkbox") && (nodes[i].checked)) {
                if ((folder_id == "sys_trash") || (folder_id == "sys_notebook_trash")) {
                    if (!confirm(MSG_CONFIRM_DEL)) return false;
                } else {
                    alert(MSG_DEL_ALERT);
                }
                remove_unload();
                obj.action = "del.php";
                obj.submit();
                return false;
            }
        }
        alert(MSG_SEL_DEL);
    }

    /**
     * move message to another folder
     **/
    function mv() {
        var nodes = null, attr = null, obj = null, aobj = null;
        var msgFolder = "";

        obj = document.getElementById("mainFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.action = "move.php";

        nodes = document.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("type");
            if ((attr == "checkbox") && (nodes[i].checked)) {
                aobj = getTarget();
                if ((aobj == null) || (typeof(aobj.getSelFolder) != "function")) return false;
                msgFolder = aobj.getSelFolder();
                if (msgFolder == "") {
                    alert(MSG_SEL_TARGET);
                    return false;
                }
                if (msgFolder == folder_id) {
                    alert(MSG_SAME_FOLDER);
                    return false
                }
                remove_unload();
                obj.folder_id.value = msgFolder;
                obj.submit();
                return false;
            }
        }
        alert(MSG_SEL_MOVE);
    }

    /**
     * change page
     * @param integer n : action type or page number
     * @return
     **/
    function go_page(n){
        var obj = document.getElementById("actFm");
        if ((typeof(obj) != "object") || (obj == null)) return '';
        switch(n){
            case -1:	// 第一頁
                obj.page.value = 1;
                break;
            case -2:	// 前一頁
                obj.page.value = parseInt(obj.page.value) - 1;
                if (parseInt(obj.page.value) == 0) obj.page.value = 1;
                break;
            case -3:	// 後一頁
                obj.page.value = parseInt(obj.page.value) + 1;
                break;
            case -4:	// 最末頁
                obj.page.value = parseInt(total_page);
                break;
            default:	// 指定某頁
                obj.page.value = parseInt(n);
                break;
        }
        remove_unload();
        obj.submit();
    }

    function trim(val) {
        var re = /\s/g;
        val = val.replace(re, '');
        return val;
    }

    function sortBy(val){
        var ta = new Array('',
            'sender',
            'subject',
            'send_time',
            'priority'
        );
        var re = /asc/ig;

        var obj = document.getElementById("actFm");
        if ((typeof(obj) != "object") || (obj == null)) return '';

        if (trim(obj.sortby.value) == ta[val]) {
            obj.order.value = (re.test(obj.order.value)) ? 'desc' : 'asc';
        }
        obj.sortby.value = ta[val];
        remove_unload();
        obj.submit();
    }

    function remove_unload() {
        window.onunload = function () {};
        return "";
    }

    function winColse() {
        var obj = null;
        obj = getTarget();
        if (obj != null) obj.location.replace("about:blank");
    }

    function getTarget() {
        var obj = null;
        switch (this.name) {
            case "s_main": obj = parent.s_catalog; break;
            case "c_main": obj = parent.c_catalog; break;
            case "main"  : obj = parent.catalog;   break;
            case "s_catalog": obj = parent.s_main; break;
            case "c_catalog": obj = parent.c_main; break;
            case "catalog"  : obj = parent.main;   break;
        }
        return obj;
    }

    window.onload = function () {
        var obj = null, obj1 = null, obj2 = null;
        var re = /\/message\/msg_folder\.php$/;

        obj = getTarget();
        if ((typeof(obj) == "object") && (obj != null) && (obj.location.href.match(re) == null)) {
            obj.location.replace("msg_folder.php");
        }
        obj.location.replace("msg_folder.php");
        obj1 = document.getElementById("tb1");
        obj2 = document.getElementById("tb2");
        if ((obj1 != null) && (obj2 != null))
        obj2.innerHTML = obj1.innerHTML;
    
        if (detectIE() === 13) {
            $('.title-bar, .content .subject td').css('border-radius', '0 0 0 0');
        }
    };

    window.onunload = winColse;

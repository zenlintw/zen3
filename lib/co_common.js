// 檢驗是否為特殊 HTML 字元
var html_chars = new Array(
			"'".charCodeAt(0),"&#039;",
			'"'.charCodeAt(0),"&quot;",
			"<".charCodeAt(0),"&lt;",
			">".charCodeAt(0),"&gt;");
document.write('<script type="text/javascript" src="/lib/kphp.js"></script>');

function LengthHtmlChar(c) {
		for(n=0;n<html_chars.length;n+=2) {
			if(c==html_chars[n])
				return html_chars[n+1].length;
		}
		return 1;
	}
    // 判斷(中)文字長度
	function getTxtLength(v) {
		return v.length;
	}

/**
     *驗證啟用日與關閉日是否合法
     */
    function checkDateSetting(open, close){
        var startArray = open.split("-");
        var endArray = close.split("-");
        var star = new Date(startArray[0], startArray[1], startArray[2]);
        var end = new Date(endArray[0], endArray[1], endArray[2]);

        if(end < star){
            return false;
        } else {
            return true;
        }
    }
    /**
     * 驗證正整數
     * @param string str
     * @return boolean
     */
	function isInteger(str){
		var regu = /^[-]{0,1}[0-9]{1,}$/;
		return regu.test(str);
	}
	/**
	 *驗證URL
	 */
    function IsURL(str_url){
       var strRegex = "^((https|http)?://)"
       + "?(([0-9a-zA-Z_!~*'().&=+$%-]+: )?[0-9a-zA-Z_!~*'().&=+$%-]+@)?"
       + "(([0-9]{1,3}\.){3}[0-9]{1,3}"
       + "|"
       + "([0-9a-zA-Z_!~*'()-]+\.)*"
       + "([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\."
       + "[a-z]{2,6})"
       + "(:[0-9]{1,4})?"
       + "((/?)|"
       + "(/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+/?)$";
       var re=new RegExp(strRegex);
       if (re.test(str_url)){
          return true;
       }else{
          return false;
       }
    }

    /**
	 * 切換全選或全消的 checkbox
	 **/
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
//		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
//		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
	}
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
	/**
	 * 全選控制
	 **/
	function selfunc() {
		var obj  = document.getElementById("ck");
		select_func('', obj.checked);
	}

	/**
	 * 交換節點
	 * @param object node1 : 節點
	 * @param object node2 : 節點
	 **/
	function swapNode(node1, node2) {
		var pnode1 = null, pnode2 = null, tnode1 = null, tnode2 = null;
		var attr1 = null, attr2 = null;

		if ((typeof(node1) != "object") || (node1 == null)
			|| (typeof(node2) != "object") || (node2 == null))
		{
			return false;
		}
		if (isIE && (BVER == "5.0")) {
			var style1 = node1.className;
			var style2 = node2.className;
			node1.swapNode(node2);
			node1.className = style2;
			node2.className = style1;
		} else {
			pnode1 = node1.parentNode;
			pnode2 = node2.parentNode;
			tnode1 = node1.cloneNode(true);
			tnode2 = node2.cloneNode(true);
			tnode1.className = node2.className;
			tnode2.className = node1.className;
			pnode1.replaceChild(tnode2, node1);
			pnode2.replaceChild(tnode1, node2);
		}
		return true;
	}


	/**
	 * 排序
	 * @param integer val :
	 *     0 : 向上
	 *     1 : 向下
	 * @return
	 **/
	function permute(val, num_toolbar) {
		var node1 = null, node2 = null;
		var pnode = null;
		var nid = new Array();
		var idx = 0;
		var topBound= num_toolbar;
		nid = getCkVal();

		if (nid.length <= 0) {
			alert(MSG_PERMUTE_SELECT);
			return false;
		}
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		nid = new Array();
		if (val == 0) {
			for (var i = 0; i < nodes.length; i++) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;

					if (node1.rowIndex == topBound) {
						alert(MSG_CAN_NOT_UP);
						return false;
					}
					nid[nid.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex - 1]);
				}
				idx = i;
			}
		} else {
			for (var i = nodes.length - 1; i >= 0 ; i--) {
				if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
				if (nodes[i].checked) {
					node1 = nodes[i].parentNode.parentNode;
					if (node1.rowIndex == node1.parentNode.rows.length - 2) {
						alert(MSG_CAN_NOT_DOWN);
						return false;
					}
					nid[nid.length] = idx;
					swapNode(node1, node1.parentNode.rows[node1.rowIndex + 1]);
				}
				idx = i;
			}
		}


		for (var i = 0; i < nid.length; i++) {
			nodes[nid[i]].checked = true;
		}

	}
	/**
	 * 儲存順序
	 **/
	function savePermute(fm, nid, type) {
		var obj = null;
		var nodes = document.getElementsByTagName("input");
		var ary = new Array();
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			ary[ary.length] = nodes[i].value;
		}
		obj = document.getElementById(nid);
		obj.value = ary.toString();
		resWin = window.open("about:blank", "resWin", "width=300,height=200,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbar=0,resizable=1");
		obj = document.getElementById(fm);
		obj.action="/lib/permute_save.php?type="+type;
		obj.submit();
	}
	/**
	 * 取得勾選的議題編號
	 * @return array nid
	 **/
	function getCkVal() {
		var nid = new Array();
		var nodes = document.getElementsByTagName("input");

		if ((nodes == null) || (nodes.length <= 0)) return nid;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) {
				nid[nid.length] = nodes[i].value;
			}
		}
		return nid;
	}

    /**
     *秀日曆的函數(checkbox)
     */
	function showDateInput(objName, state)
	{
		var obj = document.getElementById(objName);

		if (obj != null) {
			obj.style.display = (state==1) ? "" : "none";
		}
	}
	/**
	 *秀日曆的函數
	 */
	function Calendar_setup(ifd, fmt, btn, shtime)
	{
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			weekNumbers : false,
			step        : 1
		});
	}

   /**
	 *日期預設為選取(顯示)
	 */
	function initDate(){
	    document.getElementById('span_open_date').style.display='';
	    document.getElementById('span_close_date').style.display='';
	}

	/**
     * 顯示錯誤訊息框
     **/
	function displayErrorDialog(name){
		var obj             = document.getElementById(name);

		obj.style.left      = document.body.scrollLeft  + 360;
		obj.style.top       = document.body.scrollTop  + 140;
		obj.style.display   = '';
	}

	/**
	 *隱藏錯誤訊息框
	 */
	function hiddenDialog(name){
		document.getElementById(name).style.display = 'none';
	}
	/**
	 * 刪除確認
	 **/
	function rmConfirm(fmName, obj){

		var rmContent='';
		var ck = document.getElementsByTagName("input");

        for (var i=0;i<ck.length;i++) {
            if (ck[i].type=='checkbox' && ck[i].checked) {
                if (ck[i].value) {
                    rmContent += kphp.htmlspecialchars_decode(document.getElementById(obj+'_'+ck[i].value).innerHTML)+'\r\n';
                }
            }
        }
        if (rmContent=='') {
            rmContent=MSG_RM_CONFIRM;
            alert(rmContent);
        } else {
            rmContent=MSG_CONFIRM_ALERT+"\r\n"+rmContent;
            if (confirm(rmContent)){
                rmData(fmName);
            }
        }
        
	}

	/**
	 * 刪除確認
	 **/
	function rmConfirm1(fmName, obj){
		var rmContent='';
		var ck = document.getElementsByTagName("input");

        for (var i=0;i<ck.length;i++) {
            if (ck[i].type=='checkbox' && ck[i].checked) {
                if (ck[i].value) {
                    rmContent += document.getElementById(obj+'_'+ck[i].value).innerHTML+'\r\n';
                }
            }
        }
        if (rmContent=='') {
            rmContent=MSG_RM_CONFIRM;
            alert(rmContent);
        } else {
            rmContent=MSG_CONFIRM_ALERT;
            if (confirm(rmContent)){
                rmData(fmName);
            }
        }
	}
	/**
	 * 提交刪除資料
	 **/
	function rmData(fm){
	    var obj =document.getElementById(fm);
	    obj.action='';
	    obj.target='';
	    obj.act.value='rm';
	    obj.submit();
	}

	function postReturn(fm, url, msgtp){
	    var obj =document.getElementById(fm);
	    obj.action=url;
	    if (msgtp!='' && typeof(msgtp)!='undefinded') {
	        obj.msgtp.value=msgtp;
	    }
	    obj.submit();
	}

    function goBoard(board_id){
	    window.location='/forum/co_thread_manage.php?board_id='+board_id;
	}

	function goCourseBoard(board_id, course_id){
	    window.location='/forum/co_thread_manage.php?board_id='+board_id+"&course_id="+course_id;
	}
    function bytesToSize(bytes) {
        if(bytes == 0) return '0 Byte';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
    }

// 快速鍵
document.onkeyup = function (e) {
        var evnt = (typeof e != "undefined") ? e : event;
        if (evnt.altKey) {
                switch (evnt.keyCode) {
                        // ALT + U = 推薦分享區
                        case 85:
                                $("#up").click();
								setTimeout(function() { $("#up").focus(); }, 0);
                                break;
                        case 76: // L 左方導覽區塊
                                $("#login").click();
								setTimeout(function() { $("#left").focus(); }, 0);
                                break;
						case 89: // Y 上方訂閱電子報區塊
                                 $("#epaper").click();
								setTimeout(function() { $("#epaper").focus(); }, 0);
                                break;
						case 67: // C 中央內容區塊
                                 $("#content").click();
								setTimeout(function() { $("#content").focus(); }, 0);
                                break;

						case 73: // I 下方資訊區塊
                                 $("#footer").click();
								setTimeout(function() { $("#footer").focus(); }, 0);
                                break;
						case 66: // B 回首頁按鈕
                                 $("#home").submit();
                                break;


                }
                return;
        }
}
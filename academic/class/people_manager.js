    /**
    * $Id: people_manager.js,v 1.1 2010/02/24 02:38:15 saly Exp $
    */
    var xmlDoc = null, xmlHttp = null, xmlVars = null;
    ////////////////////////////////////////////////////////////////////////////
    /**
     * 將 XML 的節點以一般的文字呈現
     **/
    function xml(node) {
        if (node == null) return "";

        if (isIE) return node.xml;
        if (isMZ) return (new XMLSerializer()).serializeToString(node);

        return "";
    }

// //////////////////////////////////////////////////////////////////////////
    /**
    * 將 ,  轉成 <br> 顯示
    **/
    function DotconvtBr(str) {
        var re = /</ig;
        var val = str;
        val = val.replace(/,/ig, "<br>");
        return val;
    }
// //////////////////////////////////////////////////////////////////////////
    function getGPNode(gid) {
        var nodes = null, attr = null;
        if ((gid == "root") || (gid == 0)) return xmlDoc.documentElement;

        nodes = xmlDoc.getElementsByTagName("classes");

        if ((nodes == null) || (nodes.length <= 0)) return null;
        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("id");

            if ((attr != null) && (parseInt(attr) == parseInt(gid))) {

                return nodes[i];
            }
        }
        return null;
    }
// //////////////////////////////////////////////////////////////////////////
        /**
     * 變更群組底下的班級
     **/
    function doFunc(val) {
        var obj = null, obj2 = null, node1 = null, node2 = null, nodes1 = null, nodes2 = null;
        var cnt1 = 0, cnt2 = 0, idx = 0;

        var idx1 = new Array(), idx2 = new Array();

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars2) != "object") || (xmlVars2 == null)) xmlVars2 = XmlDocument.create();

        switch (parseInt(val)) {
            case 1 :   //  附屬 (attach)
                idx2 = getCheckPerson();

                if (idx2.length == 0) {
                    alert(MSG_SRC_COURSE);
                    return false;
                }

                idx1 = parent.catalog.searchPoint();
                if (idx1.length == 0) {
                    alert(MSG_TARGET);
                    return false;
                }

                txt = "<manifest><classes>"+ idx1 + "</classes><student>" + idx2 + "</student></manifest>";

                xmlVars2.loadXML(txt);

                xmlHttp.open("POST", "attach_people.php", false);
                xmlHttp.send(xmlVars2);

                xmlVars2.loadXML(xmlHttp.responseText);
                node = xmlVars2.selectSingleNode('//result');

                if (node.hasChildNodes()) {
                    var tmp = node.firstChild.nodeValue;
                    msg = tmp;
                }

                alert(msg);

                selFunc(false);  // 取消已勾選的選項 (cancel checkbox)

                break;

            case 2 :   //  寄信 (send mail)
                var obj = document.getElementById("mailFm");

                if ((typeof(obj) != "object") || (obj == null)) return false;

                //  班級 (class)
                idx1 = parent.catalog.searchPoint();

                //  人員 (people)
                idx2 = getCheckPerson();

                if ((idx2.length == 0) && (idx1.length == 0) ) {
                    alert(MSG_MAIL);
                    return false;
                }

                if (idx1.length > 0) {
                    obj.class_id.value = idx1;
                }

                if (idx2.length > 0) {
                    obj.send_user.value = idx2;
                }

                obj.submit();
                break;

            case 3 :   //  調動 (transfer)
                var obj = document.getElementById("modify_belong");

                if ((typeof(obj) != "object") || (obj == null)) return false;

                //  原屬於 那一個 班級 (original class)
                obj2 = document.getElementById("CGroup");
                if (obj2 != null) obj.old_class.value = obj2.value;

                //  人員 (people)
                idx2 = getCheckPerson();

                if (idx2.length == 0) {
                    alert(MSG_SRC_COURSE);
                    return false;
                }

                obj.student.value = idx2;

                //  更新後班級 (update class)
                idx1 = parent.catalog.searchPoint();
                if (idx1.length == 0) {
                    alert(MSG_TARGET);
                    return false;
                }

                obj.new_class.value = idx1;

                obj.submit();
                break;
            case 4 :   //  移出  (刪除 delete)
                idx2 = getCheckPerson();

                if (idx2.length == 0) {
                    alert(MSG_SRC_COURSE);
                    return false;
                }
                if (confirm(MSG_DEL)){

                    //  原屬於 那一個 班級 (original)
                    obj2 = document.getElementById("CGroup");
                    if (obj2 != null) class_id = obj2.value;

                    txt = "<manifest><classes>"+ class_id + "</classes><student>" + idx2 + "</student></manifest>";

                    xmlVars2.loadXML(txt);

                    xmlHttp.open("POST", "remove_people.php", false);
                    xmlHttp.send(xmlVars2);

                    xmlVars2.loadXML(xmlHttp.responseText);

                    node = xmlVars2.selectSingleNode('//result');

                    if (node.hasChildNodes()) {
                        var tmp = node.firstChild.nodeValue;
                        msg = tmp;
                    }

                    alert(msg);

                    // 重新查詢 班級的資料 begin

                    loadCS(class_id,true);

                    cancelChk();  // 取消已勾選的選項 (cancel checkbox)

                    // 重新查詢 班級的資料 end
                }
                break;
            case 5 : //  變換身份 (switch)
                /* 檢查是否有勾選人員 */
                idx1 = getCheckPerson();

                if (idx1.length == 0) {
                    alert(MSG_SRC_COURSE2);
                    return false;
                }

                actionLayer("divSettings", true);

                break;
        }
    }
////////////////////////////////////////////////////////////////////////////

    /**
     * 載入整個 班級 或 部門 群組的 XML
     **/
    function loadGP() {

        var obj = null;
        var txt = "";
        var xmlHttp = null;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlDoc)  != "object") || (xmlDoc  == null)) xmlDoc  = XmlDocument.create();

        txt  = "<manifest>"
        txt += "<ticket></ticket>";
        txt +="</manifest>";
        if (!xmlDoc.loadXML(txt)) {
            xmlDoc = null;
            return false;
        }

        xmlHttp.open("POST", "class_group_get.php", false);

        xmlHttp.send(xmlDoc);

        if (!xmlDoc.loadXML(xmlHttp.responseText)) {
            xmlDoc.loadXML(txt);
        }

        if ((typeof(top.catalog) == "object")
            && (typeof(top.catalog.showGroup) == "function")) {
            top.catalog.showGroup(xmlDoc);
        }
    }

// //////////////////////////////////////////////////////////////////////////
    /**
     * 清除表格中的資料
     **/
    function cleanTableData() {
        var obj = null;
        var idx = 0;

        obj = document.getElementById("ClassList");
        if (obj == null) return false;

        if (obj.rows.length <= 6) return false;
        idx = parseInt(obj.rows.length)-2;
        for (var i = idx; i > 4; i--) {
            obj.deleteRow(i);
        }
    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 建立下拉選單
     **/
    function buildPageList() {
        var cnt = 0;
        var txt = "";
        var obj = null, node = null;

        node = xmlVars.documentElement;

        if ((typeof(node) == "object") && (node != null) && node.hasChildNodes()) {
            obj = xmlVars.selectSingleNode('//total_row/text()');
            cnt = obj.nodeValue;
        }

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // 算出共有幾頁 (total page)
        pageNum = parseInt(cnt / listNum);
        if ((cnt % listNum) > 0) pageNum++;

        // 取代 MSG_page_exceed 中有 %TOTAL_PAGE% 的 字 (replace)
        MSG_page_exceed = MSG_page_exceed_org.replace('%TOTAL_PAGE%',pageNum);

        // 目前要換到第幾頁
        page_scope();
    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 將 標題的 hyperlink 清空
     **/
    function clear_title_hyperlink(){
        var obj = null;
        var temp_class = '';

        obj = document.getElementById('title34');
        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(1);" >' + msg_title34 + '</a>';

        obj = document.getElementById('title33');
        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(2);" >' + msg_title33 + '</a>';

        obj = document.getElementById('title55');
        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(3);" >' + msg_title55 + '</a>';

        // 位在那個節點 (CGroup)
        obj = document.getElementById("CGroup");
        if (obj != null) temp_class = obj.value;

        obj = document.getElementById('hi_status');
        if (temp_class == 1000000){
            obj.title = msg_belong_class;
            obj.align = 'center';
            obj.innerHTML = msg_belong_class;
        }else{
            obj.title = msg_title59;
            obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(4);" >' + msg_title59 + '</a>';
        }

    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 建立成員清單
     **/
    function buildperson() {
        var obj = null, node = null, nodes = null, childs = null, attr = null;
        var i = 0, cnt = 0, idx = 0;
        var col = "cssTrEvn";
        var icon = '<img src="/theme/' + theme + '/academic/icon_folder.gif" width="16" height="16" border="0" alt="' + msg02 + '" title="' + msg02 + '">';
        var ary = new Array("&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;","&nbsp;","&nbsp;","&nbsp;");
        var checkbx = "";
        var obj1 = null,obj2 = null;
        var class_id = '';
        var searial_no = 0;
        var temp_belong_class = '';

        // 位在那個節點 (CGroup)
        obj1 = document.getElementById("CGroup");
        class_id = obj1.value;

        /*
         * 判斷是否有用到查詢按鈕
         * 1 => 有用到查詢 , '' => 沒有用到查詢按鈕
         */
        var check_query_btn = '';
        obj = document.getElementById("query_btn");

        if (obj != null)
            check_query_btn  = obj.value;

        obj = document.getElementById("ClassList");
        if (obj == null) return false;

        cleanTableData();   // 清除表格的資料 (clean table)

        /**
         *   Mozilla & IE 的不一樣的地方
         *   Mozilla 的 <manifest>    </manifest> =>  多出空白行
         *   IE 的 <manifest></manifest> =>  不會
         **/

        node = xmlVars.documentElement;

        if ((typeof(node) != "object") || (node == null) || (node.childNodes.length == 1)) {
            if (check_query_btn  == 1) {
                alert(NO_KEYWORD);
            }
            return false;
        }

        nodes = node.childNodes;

        i = 1;
        cnt = nodes.length;
        // if (cnt > nodes.length) cnt = nodes.length;

        for (k = i; k < cnt; i++) {
            if (nodes[i].nodeType != 1) continue;
            if (nodes[i].nodeName != "class") continue;

            idx = parseInt(obj.rows.length) - 1;
            obj.insertRow(idx);
            obj.rows[idx].className = col;
            attr = getNodeValue(nodes[i], "username");
            checkbx = "";

            ary[0] = '<input type="checkbox" MyAttr="' + k + '" value="' + attr + '" onclick="selClass(this)" ' + checkbx + '>';
            // ary[1] = '<div style="width: 100px; overflow: hidden;" title="' + attr + '"><a href="javascript:void(null)" onclick="showDetail(\'' + attr + "'" + '); return false;">' + attr + '</a></div>';
            // ary[2] = '<div style="width: 100px; overflow: hidden;" title="' + htmlspecialchars(getNodeValue(nodes[i], "realname")) + '"><a href="javascript:void(null)" onclick="showDetail(\'' + attr + "'" + '); return false;">' + getNodeValue(nodes[i], "realname") + '</a></div>';
            /*Chrome*/
            ary[1] = '<div style="" title="' + attr + '"><a href="javascript:void(null)" onclick="showDetail(\'' + attr + "'" + '); return false;">' + attr + '</a></div>';
            ary[2] = '<div style="" title="' + htmlspecialchars(getNodeValue(nodes[i], "realname")) + '"><a href="javascript:void(null)" onclick="showDetail(\'' + attr + "'" + '); return false;">' + getNodeValue(nodes[i], "realname") + '</a></div>';
            ary[3] = getNodeValue(nodes[i], "gender");
            if (class_id == 1000000){
                temp_belong_class = '';
                temp_belong_class = getNodeValue(nodes[i], "role");
                if (temp_belong_class.length > 0){
                    ary[4] = '';
                    ary[4] = '<img id="imgsrc'+attr+'" title="'+msg_close_explode+'" src="/theme/'+theme+'/academic/minus.gif" width="9" height="15" align="absmiddle" onclick="class_explode(\''+attr+'\',0);">'+
                             '<br><span id="rec'+attr+'" style="" title="'+temp_belong_class+'">'+DotconvtBr(temp_belong_class)+'</span>';
                }else{
                    ary[4] = '';
                }
            }else{
                ary[4] = getNodeValue(nodes[i], "role");
            }
            ary[5] = '<a href="javascript:void(null)" onclick="showDetail(\'' + attr + '\'); return false;" class="cssAnchor">' + icon + '</a>';
            ary[6] = '<a href="javascript:void(null)" onclick="takeCourse(\'' + attr + '\'); return false;" class="cssAnchor">' + icon + '</a>';
            ary[7] = '<a href="javascript:void(null)" onclick="learnResult(\'' + attr + '\'); return false;" class="cssAnchor">' + icon + '</a>';

            for (var j = 7; j >= 0; j--){
                obj.rows[idx].insertCell(0);
                obj.rows[idx].cells[0].noWrap = true;
                if (j == 3){
                    obj.rows[idx].cells[0].innerHTML = '<img src="' +ary[j]+'" border="0" align="absmiddle">';
                }else {
                    obj.rows[idx].cells[0].innerHTML = ary[j];
                }
            }
            obj.rows[idx].cells[0].align = "center";
            obj.rows[idx].cells[3].align = "center";
            obj.rows[idx].cells[5].align = "center";
            obj.rows[idx].cells[6].align = "center";
            obj.rows[idx].cells[7].align = "center";
            col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
            k++;
        }

        // 是否要顯示 下拉選單的身份 (hidden select status)
        obj1 = document.getElementById("status_tr");

        if (class_id == 1000000) {
            obj1.style.display = "none";
        }else{
            obj1.style.display = "";
        }
    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 載入群組中所有的班級 XML
     **/
    function loadCS(val, reDisplay) {

        // 取消已勾選的選項 (cancel checkbox)
        cancelChk();

        var obj = null, node= null, nodes = null,obj2 = null;
        var txt = "",txt2 = "";

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        // sort field
        obj = document.getElementById("sby1");
        obj.value = '1';

        //  asc or desc
        obj = document.getElementById("oby1");
        obj.value = 'desc';

        // sort keyword
        var sort_key = ' username asc';

        //  clear title hyperlink
        clear_title_hyperlink();

        obj = document.getElementById("CGroup");
        if (obj != null) obj.value = val;

        /**
         * 如果是在 全校的根節點 那麼
         *  調動 、 移出 及 變換身份 的按鈕
         * 不能使用 begin
        **/

        /**
        *  調動 (transfer)
        */
        obj2 = document.getElementById("moveBtn1");

        if (parseInt(val) == 1000000){
            obj2.disabled = true;
        }else{
            obj2.disabled = false;
        }


        /**
        * 移出 (remove)
        */

        obj2 = document.getElementById("delBtn11");

        if (parseInt(val) == 1000000){
            obj2.disabled = true;
        }else{
            obj2.disabled = false;
        }

        /**
         * 變換身份 (switch)
         */
        obj2 = document.getElementById("status1Btn");

        if (parseInt(val) == 1000000){
            obj2.disabled = true;
        }else{
            obj2.disabled = false;
        }
        /**
         * 如果是在 全校的根節點 那麼
         *  調動 、 移出 及 變換身份 的按鈕
         * 不能使用 end
        **/

        txt = "";

        //  查詢目前所在的位置 (begin)
        txt = "<manifest><classes_id>" + val + "</classes_id></manifest>";


        xmlVars.loadXML(txt);

        if (val != 1000000) {
            node = getGPNode(val);
            if (node != null) {
                xmlVars.documentElement.appendChild(node.cloneNode(true));
                // 顯示目前所在的課程群組位置 (show location)
                txt = "";
                while ((node != null) && (node.tagName != "manifest")) {
                    txt = "&nbsp;>&nbsp;" + htmlspecialchars(getCaption(node)) + txt;
                    node = node.parentNode;
                }
            }
        } else {
            txt = "";
        }

        obj = document.getElementById("gpName2");
        if (obj != null) obj.innerHTML = txt;

        var obj2 = document.getElementById("actFm");
        obj2.class_id.value = val;

        //  查詢目前所在的位置 (end)

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;


        txt2 = "<manifest><ticket></ticket><classes_id>" + val + "</classes_id>"+
              "<page_serial>1</page_serial><page_num>"+ listNum + "</page_num>"+
              "<sby1>"+sort_key+"</sby1></manifest>";

        xmlVars.loadXML(txt2);

        xmlHttp.open("POST", "class_get.php", false);
        xmlHttp.send(xmlVars);
        xmlVars.loadXML(xmlHttp.responseText);
        nodes = xmlVars.getElementsByTagName("ticket");

        if ((nodes != null) && (nodes.length > 0)) {
            if (nodes[0].hasChildNodes())
                ticket = nodes[0].firstChild.nodeValue;
            else
                ticket = "";
            for (i = nodes.length - 1; i >= 0; i--) {
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }

          if (reDisplay) {
            closeDetail();
            groupIdx = val;
            pageIdx = 1;
            // 目前正在第幾頁
            var obj_page = document.getElementById("where_page");
            obj_page.value = 1;
            buildPageList();   // Create page list
            clear_title_hyperlink();    //  clear title hyperlink
            goPage(1);
        }
    }
// //////////////////////////////////////////////////////////////////////////
    function queryClass() {

        var obj = null, node= null, nodes = null;
        var txt = "",txt2 = "",txt3 = "",txt4 = "",txt5 = "";

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        // sort field
        obj = document.getElementById("sby1");
        obj.value = '1';

        //  asc or desc
        obj = document.getElementById("oby1");
        obj.value = 'asc';

        // sort field
        obj = document.getElementById("sby1");
        obj.value = '1';

        //  asc or desc
        obj = document.getElementById("oby1");
        obj.value = 'asc';

        // 身份 (status)
        obj = document.getElementById("status");
        if (obj != null) txt5 = obj.value;

        // 搜尋 (searchkey)
        obj = document.getElementById("searchkey");
        if (obj != null) txt2 = obj.value;

        // 關鍵字 (keyword)
        obj = document.getElementById("keyword");
        if (obj != null) txt3 = htmlspecialchars(obj.value);

        // 位在那個節點 (CGroup)
        obj = document.getElementById("CGroup");
        if (obj != null) txt4 = obj.value;

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // sort begin
        obj = document.getElementById("sby1");
        var sort_val = obj.value;
        obj = document.getElementById("oby1");
        var sort_order = obj.value;

        var sort_key = '';
        if (sort_order == ''){

            if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name asc,last_name asc';
            }else{
                sort_key = stud_sort[sort_val] + ' asc';
            }
        }else if (sort_order == 'asc'){

             if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name desc,last_name desc';
             }else{
                sort_key = stud_sort[sort_val] + ' desc';
             }
        }else if (sort_order == 'desc'){

             if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name asc,last_name asc';
             }else{
                sort_key = stud_sort[sort_val] + ' asc';
             }
        }
        // sort end

        txt = "<manifest>"+
              "<gpName>"+ txt4 + "</gpName>"+
              "<status>" + txt5 + "</status>"+
              "<searchkey>"+ txt2 +"</searchkey>"+
              "<keyword>"+ txt3 +"</keyword>"+
              "<page_serial>1</page_serial>"+
              "<page_num>"+ listNum + "</page_num>"+
              "<sby1>"+sort_key+"</sby1>"+
              "</manifest>";

        xmlVars.loadXML(txt);

        xmlHttp.open("POST", "people_query.php", false);
        xmlHttp.send(xmlVars);
        xmlVars.loadXML(xmlHttp.responseText);
        
        /*
        *  如果有使用過查的按鈕
        *  要將  query_btn 的值 設為 1
        *  主要是 判斷排序的來源
        *  1. 未使用查詢   2. 已使用查詢按鈕
        */
        obj = document.getElementById("query_btn");
        obj.value = 1;

        nodes = xmlVars.getElementsByTagName("ticket");
        if ((nodes != null) && (nodes.length > 0)) {
            if (nodes[0].hasChildNodes())
                ticket = nodes[0].firstChild.nodeValue;
            else
                ticket = "";
            for (i = nodes.length - 1; i >= 0; i--) {
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }

        closeDetail();
        groupIdx = txt4;
        pageIdx = 1;
        buildPageList();   // Create page list
        clear_title_hyperlink();    //  clear title hyperlink
        goPage(1);

    }
////////////////////////////////////////////////////////////////////////////

    /**
     * 同步功能種類
     * @param obj : 下拉選單物件
     * @param val : 功能
     **/
    function syncValue(obj, val) {
        if ((typeof(obj) != "object") || (obj == null)) return false;
        for (var i = 0; i < obj.length; i++) {
            if (val == obj.options[i].value) {
                obj.selectedIndex = i;
                return true;
            }
        }
        return false;
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 換頁
     * @pram val
     *     >0 : 第幾頁
     *      0 : 全部
     *     -1 : 首頁
     *     -2 : 上頁
     *     -3 : 下頁
     *     -4 : 末頁
     **/
    function goPage(val) {
        var obj = null,obj2 = null;
        var btn11 = null, btn12 = null, btn13 = null, btn14 = null;
        var btn21 = null, btn22 = null, btn23 = null, btn24 = null;
        var aryBtn = new Array("firstBtn", "prevBtn", "nextBtn", "lastBtn");
        var aryAct = new Array(false, false, false, false);
        var cnt = 0;
        var status = "", txt = "";

        if (parseInt(val) < 0) {
            switch (parseInt(val)) {
                case -1 : val = 1; break;
                case -2 :
                    val = pageIdx - 1;
                    if (val <= 0) val = 1;
                    break;
                case -3 :
                    val = parseInt(pageIdx) + 1;
                    if (val >= pageNum) val = pageNum;
                    break;
                case -4 : val = pageNum; break;
                default : val = 1;
            }
        }
        // 換頁清單的同步 (switch page)

        // 目前要換到第幾頁
        var obj_page = document.getElementById("where_page");
        obj_page.value = val;
        page_scope();

        // 判斷是否有用到查詢按鈕
        obj = document.getElementById("query_btn");
        var query_btn = obj.value;

        if (query_btn == ''){
            page_chage(val);
        }else{
            page_chage2(val);
        }

        obj = document.getElementById("selBtn1");
        res = syncValue(obj, val);
        if (!res) val = 0;

        if (parseInt(val) == 1) {
            aryAct[0] = true; aryAct[1] = true;
        }
        if (parseInt(val) == parseInt(pageNum)) {
            aryAct[2] = true; aryAct[3] = true;
        }
        if (parseInt(val) == 0) {
            aryAct = new Array(true, true, true, true);
        }

        for (var j = 0; j < 4; j++) {
            obj = document.getElementById(aryBtn[j] + "1");
            if (obj != null) obj.disabled = aryAct[j];
        }

        pageIdx = val;

        // 翻頁 checkbox default false
        Page_CancelChk();

        buildperson();

        obj = document.getElementById("toolbar1");
        if ((typeof(obj) == "object") && (obj != null)) txt = obj.innerHTML;

        obj = document.getElementById("toolbar2");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt;
        $('#toolbar2 #selBtn1').val($('#toolbar1 #selBtn1').val());
        $('#toolbar2 #page_num').val($('#toolbar1 #page_num').val());
        $('#toolbar2 #input_page').val($('#toolbar1 #input_page').val());
        
        obj = document.getElementById("sby1");
        sort_field = obj.value;
        var temp = '';

        //  if sort_field begin
        if (sort_field != ''){

            switch (parseInt(sort_field)){
                case 1:
                    temp = msg_title34;
                    obj2 = document.getElementById('title34');
                    break;
                case 2:
                    temp = msg_title33;
                    obj2 = document.getElementById('title33');
                    break;
                case 3:
                    temp = msg_title55;
                    obj2 = document.getElementById('title55');
                    break;
                case 4:
                    temp = msg_title59;
                    obj2 = document.getElementById('hi_status');
                    break;
                case 5:
                    temp = msg_title35;
                    obj2 = document.getElementById('title35');
                    break;
            }

            obj = document.getElementById("oby1");

            if (obj.value == 'asc'){
                temp += icon_up;
            }else if (obj.value == 'desc'){
                temp += icon_dn;
            }

            obj2.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(' + parseInt(sort_field) + ');" >' + temp + '</a>';

        }
        //  if sort_field end
    }
// //////////////////////////////////////////////////////////////////////////
    /*
     * 換頁重新 在抓 sql 資料 for goPage(val) method 使用
     */
     function page_chage(go_page){
         var obj = null, node= null, nodes = null,obj2 = null;
        var txt = "",txt2 = "",class_group_id = '';

        // 取消已勾選的選項 (cancel checkbox)
        cancelChk();

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        // sort begin
        obj = document.getElementById("sby1");
        var sort_val = obj.value;
        obj = document.getElementById("oby1");
        var sort_order = obj.value;

        var sort_key = '';
        if (sort_order == ''){

            if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name asc,last_name asc';
            }else{
                sort_key = stud_sort[sort_val] + ' asc';
            }
        }else if (sort_order == 'asc'){

             if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name desc,last_name desc';
             }else{
                sort_key = stud_sort[sort_val] + ' desc';
             }
        }else if (sort_order == 'desc'){

             if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name asc,last_name asc';
             }else{
                sort_key = stud_sort[sort_val] + ' asc';
             }
        }
        // sort end

        obj = document.getElementById("CGroup");

        if (obj != null) class_group_id = parseInt(obj.value);

        /**
         * 如果是在 全校的根節點 那麼 調動 、 移出 及 變換身份 的按鈕 不能使用 begin
        **/

        /**
        *  調動 (transfer)
        */
        obj2 = document.getElementById("moveBtn1");
        if (class_group_id == 1000000){
            obj2.disabled = true;
        }else{
            obj2.disabled = false;
        }

        /**
        * 移出 (remove)
        */

        obj2 = document.getElementById("delBtn11");

        if (class_group_id == 1000000){
            obj2.disabled = true;
        }else{
            obj2.disabled = false;
        }

        /**
         * 變換身份 (switch)
         */
        obj2 = document.getElementById("status1Btn");

        if (class_group_id == 1000000){
            obj2.disabled = true;
        }else{
            obj2.disabled = false;
        }
        /**
         * 如果是在 全校的根節點 那麼
         *  調動 、 移出 及 變換身份 的按鈕
         * 不能使用 end
        **/

        //  查詢目前所在的位置 (begin)
        if (class_group_id == 1000000) {
            txt = "";
        } else {
            node = getGPNode(class_group_id);
            if (node != null) {
                xmlVars.documentElement.appendChild(node.cloneNode(true));
                // 顯示目前所在的課程群組位置 (show location)
                txt = "";
                while ((node != null) && (node.tagName != "manifest")) {
                    txt = "&nbsp;>&nbsp;" + htmlspecialchars(getCaption(node)) + txt;
                    node = node.parentNode;
                }
            }

        }

        obj = document.getElementById("gpName2");
        if (obj != null) obj.innerHTML = txt;

        var obj2 = document.getElementById("actFm");
        obj2.class_id.value = class_group_id;

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        //  查詢目前所在的位置 (end)

        txt2 = "<manifest><ticket></ticket><classes_id>" + class_group_id + "</classes_id>"+
              "<page_serial>"+ go_page + "</page_serial><page_num>"+ listNum + "</page_num>"+
              "<sby1>"+sort_key+"</sby1></manifest>";

        xmlVars.loadXML(txt2);
        xmlHttp.open("POST", "class_get.php", false);
        xmlHttp.send(xmlVars);

        xmlVars.loadXML(xmlHttp.responseText);
    
        nodes = xmlVars.getElementsByTagName("ticket");

        if ((nodes != null) && (nodes.length > 0)) {
            if (nodes[0].hasChildNodes())
                ticket = nodes[0].firstChild.nodeValue;
            else
                ticket = "";
            for (i = nodes.length - 1; i >= 0; i--) {
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }

     }
// //////////////////////////////////////////////////////////////////////////
    /*
     * 換頁重新 在抓 sql 資料 for goPage2(val) method 使用
     */
    function page_chage2(go_page){
         var obj = null, node= null, nodes = null;
        var txt = "",txt2 = "",txt3 = "",txt4 = "",txt5 = "";

        var obj = null, node= null, nodes = null;
        var txt = "",txt2 = "",txt3 = "",txt4 = "",txt5 = "";

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        // sort begin
        obj = document.getElementById("sby1");
        var sort_val = obj.value;
        obj = document.getElementById("oby1");
        var sort_order = obj.value;

        var sort_key = '';
        if (sort_order == ''){

            if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name asc,last_name asc';
            }else{
                sort_key = stud_sort[sort_val] + ' asc';
            }
        }else if (sort_order == 'asc'){

             if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name desc,last_name desc';
             }else{
                sort_key = stud_sort[sort_val] + ' desc';
             }
        }else if (sort_order == 'desc'){

             if (stud_sort[sort_val] == 'first_name,last_name'){
                sort_key = 'first_name asc,last_name asc';
             }else{
                sort_key = stud_sort[sort_val] + ' asc';
             }
        }
        // sort end

        // 身份 (status)
        obj = document.getElementById("status");
        if (obj != null) txt5 = obj.value;

        // 搜尋 (searchkey)
        obj = document.getElementById("searchkey");
        if (obj != null) txt2 = obj.value;

        // 關鍵字 (keyword)
        obj = document.getElementById("keyword");
        if (obj != null) txt3 = htmlspecialchars(obj.value);

        // alert('txt3 1096='+txt3);

        // 位在那個節點 (CGroup)
        obj = document.getElementById("CGroup");
        if (obj != null) txt4 = obj.value;

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        txt = "<manifest><gpName>"+ txt4 + "</gpName>"+
              "<status>" + txt5 + "</status>"+
              "<searchkey>"+ txt2 +"</searchkey>"+
              "<keyword>"+ txt3 +"</keyword>"+
              "<page_serial>"+go_page+"</page_serial>"+
              "<page_num>"+ listNum + "</page_num>"+
              "<sby1>"+sort_key+"</sby1>"+
              "</manifest>";

        xmlVars.loadXML(txt);

        xmlHttp.open("POST", "people_query.php", false);
        xmlHttp.send(xmlVars);
        xmlVars.loadXML(xmlHttp.responseText);

        nodes = xmlVars.getElementsByTagName("ticket");
        if ((nodes != null) && (nodes.length > 0)) {
            if (nodes[0].hasChildNodes())
                ticket = nodes[0].firstChild.nodeValue;
            else
                ticket = "";
            for (i = nodes.length - 1; i >= 0; i--) {
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }

    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 取得勾選的班級 (可刪除)
     **/
    function getChecked() {
        var nodes = null;
        var cnt = 0;
        var idx = new Array();

        nodes = document.getElementsByTagName("input");
        cnt = nodes.length;
        for (var i = 0; i < cnt; i++) {
            if ((nodes[i].type == "checkbox") && nodes[i].checked) {
                idx[idx.length] = parseInt(nodes[i].getAttribute("MyAttr"));
            }
        }
        return idx;
    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 取得勾選的人員
     **/
    function getCheckPerson() {
        var nodes = null, attr = null;
        var cnt = 0;
        var idx = new Array();

        nodes = document.getElementsByTagName("input");

        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");

            if ((nodes[i].type == "checkbox") && (nodes[i].value != '')) {

                if (nodes[i].checked){
                    idx[idx.length] = nodes[i].value;
                }
            }
        }
        return idx;
    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 取得本頁面勾選的班級
     **/
    function getPageCheckClass() {
        var nodes = null, attr = null;
        var cnt = 0;
        var idx = new Array();

        nodes = document.getElementsByTagName("input");
        cnt = nodes.length;
        for (var i = 0; i < cnt; i++) {
            if ((nodes[i].type == "checkbox") && nodes[i].checked) {
                idx[idx.length] = nodes[i].getAttribute("MyAttr");
            }
        }
        return idx;
    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 同步全選或全消的按鈕與 checkbox
     * @version 1.0
     **/
    var nowSel = false;
    function sel_button_func() {

        var obj  = document.getElementById("ckbox");
        var btn1 = document.getElementById("btnSel1");

        if ((obj == null) || (btn1 == null)) return false;
        nowSel = !nowSel;

        obj.checked = nowSel;
        btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

        nodes = document.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
          attr = nodes[i].getAttribute("exclude");
          if ((nodes[i].type == "checkbox") && (attr == null)){
              nodes[i].checked = nowSel;
          }
      }

        obj = document.getElementById("toolbar1");
        if ((typeof(obj) == "object") && (obj != null)) txt = obj.innerHTML;

        obj = document.getElementById("toolbar2");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt;
        $('#toolbar2 #selBtn1').val($('#toolbar1 #selBtn1').val());
        $('#toolbar2 #page_num').val($('#toolbar1 #page_num').val());
        $('#toolbar2 #input_page').val($('#toolbar1 #input_page').val());
    }

// //////////////////////////////////////////////////////////////////////////

    /**
     * 全選或全消
     **/
    function selFunc(actType) {

        nodes = document.getElementsByTagName("input");
        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (attr == null)){

                nodes[i].checked = actType;

                selClass(nodes[i]);
            }
        }

        var btn1 = document.getElementById("btnSel1");
          nowSel = actType;
        /*
         * 全選
         */
        if (actType){
            btn1.value = MSG_SELECT_CANCEL;
        }else{
            btn1.value = MSG_SELECT_ALL;
        }

        obj = document.getElementById("toolbar1");
        if ((typeof(obj) == "object") && (obj != null)) txt = obj.innerHTML;

        obj = document.getElementById("toolbar2");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt;
        $('#toolbar2 #selBtn1').val($('#toolbar1 #selBtn1').val());
        $('#toolbar2 #page_num').val($('#toolbar1 #page_num').val());
        $('#toolbar2 #input_page').val($('#toolbar1 #input_page').val());

    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 單獨點選一門班級
     **/
    function selClass(obj) {
        var nodes = null, attr = null;
        var isSel = "false";
        var cnt = 0;

        if ((typeof(obj) != "object") || (obj == null)) return false;

        nodes = document.getElementsByTagName("input");
        for (var i = 0, m = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (attr == null)) {
                m++;

                if (nodes[i].checked){
                    cnt++;
                }
            }
        }

        nowSel = (m == cnt);
        document.getElementById("ckbox").checked = nowSel;

        /* button 顯示 全選 或 全消 begin */
        var btn1 = document.getElementById("btnSel1");
        btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

        obj = document.getElementById("toolbar1");
        if ((typeof(obj) == "object") && (obj != null)) txt = obj.innerHTML;

        obj = document.getElementById("toolbar2");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt;
        $('#toolbar2 #selBtn1').val($('#toolbar1 #selBtn1').val());
        $('#toolbar2 #page_num').val($('#toolbar1 #page_num').val());
        $('#toolbar2 #input_page').val($('#toolbar1 #input_page').val());
    }

// //////////////////////////////////////////////////////////////////////////
    /*
     * 取消已勾選的選項
     */
    function cancelChk() {
        var nodes = null, attr = null;
        var isSel = "false";
        var cnt = 0;

        nodes = document.getElementsByTagName("input");
        for (var i = 0, m = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (attr == null)) {
                nodes[i].checked = false;
            }
        }
        // m = (m > 0) ? m - 1 : 0;

        document.getElementById("ckbox").checked = false;

        // 關鍵字 (keyword)
        obj = document.getElementById("keyword");
        if (obj != null) obj.value = MSG_keyword;

        // 是否有用過查詢的按鈕
        obj = document.getElementById("query_btn");
        obj.value = '';

        // 全選 or 全消的 button
        var btn1 = document.getElementById("btnSel1");
        btn1.value = MSG_SELECT_ALL;

        obj = document.getElementById("toolbar1");
        if ((typeof(obj) == "object") && (obj != null)) txt = obj.innerHTML;

        obj = document.getElementById("toolbar2");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt;
        $('#toolbar2 #selBtn1').val($('#toolbar1 #selBtn1').val());
        $('#toolbar2 #page_num').val($('#toolbar1 #page_num').val());
        $('#toolbar2 #input_page').val($('#toolbar1 #input_page').val());
        
        /* 同步全選或全消的按鈕與 checkbox */
        nowSel = false;
    }
////////////////////////////////////////////////////////////////////////////
    /*
     * 翻頁 取消已勾選的選項
     */
    function Page_CancelChk() {
        var nodes = null, attr = null;
        var isSel = "false";
        var cnt = 0;

        nodes = document.getElementsByTagName("input");
        for (var i = 0, m = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (attr == null)) {
                nodes[i].checked = false;
            }
        }
        // m = (m > 0) ? m - 1 : 0;

        document.getElementById("ckbox").checked = false;

        // 全選 or 全消的 button
        var btn1 = document.getElementById("btnSel1");
        btn1.value = MSG_SELECT_ALL;

        obj = document.getElementById("toolbar1");
        if ((typeof(obj) == "object") && (obj != null)) txt = obj.innerHTML;

        obj = document.getElementById("toolbar2");
        if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt;
        $('#toolbar2 #selBtn1').val($('#toolbar1 #selBtn1').val());
        $('#toolbar2 #page_num').val($('#toolbar1 #page_num').val());
        $('#toolbar2 #input_page').val($('#toolbar1 #input_page').val());

    }
////////////////////////////////////////////////////////////////////////////
    function rePoint(ary) {
        var nodes = null;
        var cnt = 0, idx = 0;
        if ((typeof(ary) != "object") || (ary == null)) {
            return false;
        }

        nodes = document.getElementsByTagName("input");
        if (nodes == null) return false;
        cnt = nodes.length;
        for (var i = 0, j = 0; i < cnt; i++) {
            if (nodes[i].type == "checkbox") {
                if (nodes[i].getAttribute("MyAttr") == ary[j]) {
                    nodes[i].checked = true;
                    j++;
                }
            }
        }
    }
// //////////////////////////////////////////////////////////////////////////
    /**
     * 顯示 基本資料
     **/
    function showDetail(val) {
        var obj = document.getElementById("actFm");

        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.user.value = val;
        obj.msgtp.value = 1;

        obj.submit();

    }
////////////////////////////////////////////////////////////////////////////
    function closeDetail() {
        var obj = null;
        obj = document.getElementById("DetailTable");
        if (obj != null) obj.style.display = "none";
        obj = document.getElementById("ListTable");
        if (obj != null) obj.style.display = "block";

    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 顯示 修課記錄
     **/
    function takeCourse(val){
        var obj = document.getElementById("actFm");

        if ((typeof(obj) != "object") || (obj == null)) return false;

        obj.user.value = val;
        obj.msgtp.value = 2;
        obj.submit();
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 顯示 學習成果
     **/
    function learnResult(val){
        var obj = document.getElementById("actFm");

        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.user.value = val;
        obj.msgtp.value = 3;
        obj.submit();
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 顯示或隱藏對話框
     * state:
     *     true : 顯示
     *     false: 隱藏
    **/
    function actionLayer(objName, state) {
        var obj = document.getElementById(objName);

        layerAction(objName, state);
        obj.style.top = "20px";
        obj.style.left = "200px";

    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 變換身份
    **/
    function change_status() {

        var idx1 = new Array();
        var role = 0;
        var val = 0;
        var page_serial = 0;

        /**
         * 檢查是否有勾選人員
        **/
        idx1 = getCheckPerson();

        if (idx1.length == 0) {
            alert(MSG_SRC_COURSE2);
            return false;
        }

        var obj = document.getElementById("fmSetting");

        var role_len = obj.role.length;

        for (var i = 0 ; i < role_len ; i++) {
          if (obj.role[i].checked) {
            role = obj.role[i].value;
            break;
          }
        }
        if (role == 0) 
        {
            alert(MSG_choose_role);
            return false;
        }
        obj = document.getElementById("CGroup");
        if (obj != null) val = obj.value;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars2) != "object") || (xmlVars2 == null)) xmlVars2 = XmlDocument.create();

        txt = "<manifest><class>"+ val +"</class><student>"+ idx1 + "</student><role>" + role + "</role></manifest>";

        xmlVars2.loadXML(txt);

        xmlHttp.open("POST", "switch_status.php", false);
        xmlHttp.send(xmlVars2);

        xmlVars2.loadXML(xmlHttp.responseText);

        node = xmlVars2.selectSingleNode('//result');

        if (node.hasChildNodes()) {
            var tmp = node.firstChild.nodeValue;
            msg = tmp;
        }

        alert(msg);

        /**
         * 重新查詢 班級的資料 begin
        */

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // 目前正在第幾頁 (page serial no)
        obj = document.getElementById("selBtn1");
        if (obj != null) page_serial = obj.value;

        // sort field
        var sort_key = 1;
        var sort_str = '';
        obj = document.getElementById("sby1");
        if (obj != null){
            sort_key = obj.value;
        }

        //  asc or desc
        obj = document.getElementById("oby1");
        if (obj.value == ''){
            obj.value = 'asc';

            if (stud_sort[sort_key] == 'first_name,last_name'){
                sort_str = 'first_name asc,last_name asc';
            }else{
                sort_str = stud_sort[sort_key] + ' asc';
            }
        }else if (obj.value == 'asc'){

             if (stud_sort[sort_key] == 'first_name,last_name'){
                sort_str = 'first_name asc,last_name asc';
             }else{
                sort_str = stud_sort[sort_key] + ' asc';
             }
        }else if (obj.value == 'desc'){
             if (stud_sort[sort_key] == 'first_name,last_name'){
                sort_str = 'first_name desc,last_name desc';
             }else{
                sort_str = stud_sort[sort_key] + ' desc';
             }
        }

        txt = "<manifest><ticket></ticket><classes_id>" + val + "</classes_id>"+
             "<page_serial>"+ page_serial +"</page_serial>"+
             "<page_num>"+ listNum + "</page_num>"+
             "<sby1>"+ sort_str + "</sby1>"+
             "</manifest>";

        xmlVars.loadXML(txt);
        xmlHttp.open("POST", "class_get.php", false);
        xmlHttp.send(xmlVars);

        xmlVars.loadXML(xmlHttp.responseText);

        nodes = xmlVars.getElementsByTagName("ticket");

        if ((nodes != null) && (nodes.length > 0)) {
            if (nodes[0].hasChildNodes())
                ticket = nodes[0].firstChild.nodeValue;
            else
                ticket = "";
            for (i = nodes.length - 1; i >= 0; i--) {
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }

        closeDetail();
        groupIdx = val;
        pageIdx = 1;
        buildPageList();   // Create page list
        goPage(1);

        // 重新查詢 班級的資料 (end)

        actionLayer("divSettings", false);
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 個人基本資料 &　修課記錄　&　學習成果　
    **/
    function chgHistory(val) {
        var obj = document.getElementById("actFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.msgtp.value = val;

        obj.submit();
    }
////////////////////////////////////////////////////////////////////////////
    /**
    * 回 人員列表
    **/
    function go_list() {
        window.location.replace("people_manager.php");
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 排序
     **/
    function chgPageSort(val) {

        var obj = null, obj2 = null, node= null, nodes = null;
        var txt = "",txt2 = "",txt3 = "";
        var txt4 = "",txt5 = "",txt6 = "";

        //  clear title hyperlink
        clear_title_hyperlink();

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        // 位在那個節點 (CGroup)
        obj = document.getElementById("CGroup");
        if (obj != null) txt2 = obj.value;

        // sort field
        obj = document.getElementById("sby1");
        if (obj != null){
            obj.value = val;
        }

        //  asc or desc
        obj = document.getElementById("oby1");
        if (obj.value == ''){
            obj.value = 'asc';

            if (stud_sort[val] == 'first_name,last_name'){
                txt3 = 'first_name asc,last_name asc';
            }else{
                txt3 = stud_sort[val] + ' asc';
            }
        }else if (obj.value == 'asc'){
             obj.value = 'desc';
             if (stud_sort[val] == 'first_name,last_name'){
                txt3 = 'first_name desc,last_name desc';
             }else{
                txt3 = stud_sort[val] + ' desc';
             }
        }else if (obj.value == 'desc'){
             obj.value = 'asc';
             if (stud_sort[val] == 'first_name,last_name'){
                txt3 = 'first_name asc,last_name asc';
             }else{
                txt3 = stud_sort[val] + ' asc';
             }
        }
        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // 目前第幾頁
        obj = document.getElementById("selBtn1");
        if (obj != null) page_serial = obj.value;


        /* 如果有使用過查的按鈕  要將  query_btn 的值 設為 1
        *  主要是 判斷排序的來源
        *  1. 未使用查詢   2. 已使用查詢按鈕
        */
        obj = document.getElementById("query_btn");
        var query_btn = obj.value;

        var file_name = '';

        if (query_btn == ''){
            txt = "<manifest><classes_id>"+ txt2 + "</classes_id>"+
                  "<page_serial>1</page_serial>"+
                  "<page_num>"+listNum+"</page_num>"+
                  "<sby1>" + txt3 + "</sby1></manifest>";

            file_name = 'class_get.php';

        }else{
            // 身份 (status)
            obj = document.getElementById("status");
            if (obj != null) txt4 = obj.value;

            // 搜尋 (searchkey)
            obj = document.getElementById("searchkey");
            if (obj != null) txt5 = obj.value;

            // 關鍵字 (keyword)
            obj = document.getElementById("keyword");
            if (obj != null) txt6 = obj.value;

            txt = "<manifest>"+
                  "<gpName>"+ txt2 + "</gpName>"+
                  "<query_btn>" + query_btn + "</query_btn>"+
                  "<status>" + txt4 + "</status>"+
                  "<searchkey>"+ txt5 +"</searchkey>"+
                  "<keyword>"+ txt6 +"</keyword>"+
                  "<page_serial>1</page_serial>"+
                  "<page_num>"+listNum+"</page_num>"+
                  "<sby1>" + txt3 + "</sby1>"+
                  "</manifest>";

            file_name = 'people_query.php';

        }
        xmlVars.loadXML(txt);
        xmlHttp.open("POST", file_name, false);
        xmlHttp.send(xmlVars);
        xmlVars.loadXML(xmlHttp.responseText);

        nodes = xmlVars.getElementsByTagName("ticket");
        if ((nodes != null) && (nodes.length > 0)) {
            if (nodes[0].hasChildNodes())
                ticket = nodes[0].firstChild.nodeValue;
            else
                ticket = "";
            for (i = nodes.length - 1; i >= 0; i--) {
                nodes[i].parentNode.removeChild(nodes[i]);
            }
        }

        closeDetail();
        groupIdx = txt2;
        pageIdx = 1;
        buildPageList();   // Create page list

        goPage(1);

    }
////////////////////////////////////////////////////////////////////////////
    /*
     * 一頁顯示幾筆
     */
    function Page_Row(row){
        $("select[name='page_num']").val(row);
        
        var no_group_id = 100000;
        var group_id = 0;

        // 位在那個班級 (Class_id)
        obj = document.getElementById("CGroup");
        group_id = obj.value;

        if (group_id.length == 0) {
            group_id = no_group_id;
        }

        loadCS(group_id,true);
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 將 授課列表 的 內容 展開 / 收攏
     * user_idx: 帳號
     * type: 是要展開 (1) 或 收攏 (0)
     **/
    function class_explode(user_idx,img_type){
        var obj = document.getElementById("rec"+user_idx);
        /*
         *展開 收攏的 小圖片
        */
        var obj2 = document.getElementById("imgsrc"+user_idx);
        if (img_type == 1) {  //  open
            obj.style.display = "block";
            obj2.src='/theme/' + theme + '/academic/minus.gif';
            obj2.title=msg_close_explode;
            obj2.onclick = function () {
                class_explode(user_idx, 0);
            };
        }else if (img_type == 0){
            obj.style.display = "none";
            obj2.src ='/theme/' + theme + '/academic/plus.gif';
            obj2.title=msg_explode;
            obj2.onclick = function () {
                class_explode(user_idx, 1);
            };
        }
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 下拉選單 頁數的範圍
     **/
    function page_scope(){
        var obj_page = document.getElementById("where_page");
        var where_page = parseInt(obj_page.value);
        if(where_page == 0)
            where_page = 1;
        var txt = '';
        var page_limit = 10;
        var lb = where_page - page_limit;
        var ub = where_page + page_limit;
        var z = Math.min(pageNum-1,ub);

        txt = '<option value="' + 1 + '">' + 1 + '</option>';   // 第一頁一定要有

        // 從第一頁到目前頁的前 10 頁，之間的每隔 10 頁
        for (var i=page_limit; i<lb; i+=page_limit)
            txt += '<option value="' + i + '">' + i + '</option>';

        // 目前頁的前後 10 頁
        for (var i=Math.max(2,lb); i<=z; i++)
            txt += '<option value="' + i + '">' + i + '</option>';

        // 從目前頁的後 10 頁到最後一頁，之間的每隔 10 頁
        for (var i=Math.ceil(ub/page_limit)*page_limit; i<pageNum; i+=page_limit)
            txt += '<option value="' + i + '">' + i + '</option>';

        txt += '<option value="' + pageNum + '">' + pageNum + '</option>';  // 最後一頁一定要有

        for (var i = 1; i <= 2; i++) {
            obj = document.getElementById("spanSel" + i);
            if (obj == null) continue;
            obj.innerHTML = '<select name="selBtn' + i + '" id="selBtn' + i + '" course="box02" onchange="goPage(this.value)">' + txt + '</select>';
        }
    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 手動輸入到 第幾頁數的範圍
     **/
    function go_page_btn(btn){
        var obj_page = btn.previousSibling.previousSibling;
        var page_val = obj_page.value;
        if (page_val.search(/^[1-9]\d*$/) == -1)
        {
            alert(MSG_page_range_error);
            obj_page.focus();
            return  false;
        }

        page_val = parseInt(page_val);
        if(page_val > pageNum){
            alert(MSG_page_exceed);
            obj_page.focus();
            return false;
        }

        document.getElementById('input_page').value = page_val;
        goPage(page_val);
    }
////////////////////////////////////////////////////////////////////////////
    function init(evnt) {
        var obj = null;
        // 開啟工具列視窗 (window)
        top.catalog.location.replace("class_group_tree.php?a="+class_id);

        if ((typeof(isXmlExtras) == "undefined") || !isXmlExtras) {
            alert("Can not find need lib.");
            return false;
        }
        xmlHttp = XmlHttp.create();
        xmlVars = XmlDocument.create();
        chkBrowser();
    }

    window.onload = init;

    window.onunload = function () {
        top.catalog.location.href = "about:blank";
    };

    window.onerror = function () {
        return true;
    };


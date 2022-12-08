/*
 *$Id: view_grade.js,v 1.1 2010/02/24 02:38:15 saly Exp $
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

////////////////////////////////////////////////////////////////////////////

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

    //    alert('xmlDoc.xml ( people_manager.js 69 )='+xmlDoc.xml);

        if ((typeof(top.catalog) == "object")
            && (typeof(top.catalog.showGroup) == "function")) {
            top.catalog.showGroup(xmlDoc);
        }
    }
////////////////////////////////////////////////////////////////////////////
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

    /**
     * 建立下拉選單
     **/
    function buildPageList() {
        var cnt = 0;
        var txt = "";
        var obj = null, node = null;

        node = xmlVars.documentElement;

        if ((typeof(node) == "object") && (node != null) && node.hasChildNodes()) {
            cnt = node.selectSingleNode('//total_row').text;
        }

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // 算出共有幾頁 (total page)
        pageNum = Math.max(1, Math.ceil(cnt / listNum));

        // txt  = '<option value="0" >' + msg07 + '</option>';

        // 取代 MSG_page_exceed 中有 %TOTAL_PAGE% 的 字 (replace)
        MSG_page_exceed = MSG_page_exceed_org.replace('%TOTAL_PAGE%',pageNum);
        
        page_scope();
    }

    /**
     * 建立成員清單
     **/
    function buildperson() {
        // 位在那個節點 (CGroup)
        obj = document.getElementById("CGroup");
        class_id = obj.value;

        var obj = null, node = null, nodes = null, childs = null, attr = null;
        var i = 0, cnt = 0, idx = 0;
        var col = "cssTrEvn";
        var icon = '<img src="/theme/' + theme + '/academic/icon_folder.gif" width="16" height="16" border="0" alt="' + msg02 + '" title="' + msg02 + '">';
        var ary = new Array("&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;","&nbsp;","&nbsp;","&nbsp;");
        var checkbx = "";
        var obj1 = null;

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

        cleanTableData();   // 清除表格的資料 (clearn table)

        node = xmlVars.documentElement;

        if ((typeof(node) != "object") || (node == null) ||  (node.childNodes.length == 1)) {
            if (check_query_btn  != '') {
                alert(NO_KEYWORD);
            }
            return false;
        }

        nodes = node.childNodes;

        i = 1;
        cnt = nodes.length;

        for (k = i, m = 0; k < cnt; i++) {
            if (nodes[i].nodeType != 1) continue;
            if (nodes[i].nodeName != "class") continue;

            idx = parseInt(obj.rows.length) - 1;
            obj.insertRow(idx);
            obj.rows[idx].className = col;

            attr = getNodeValue(nodes[i], "username");

        //    checkbx = (nodes[i].getAttribute("checked") == "true") ? "checked" : "";
            checkbx = "";

            ary[0] = '<input type="checkbox" MyAttr="' + k + '" value="' + attr + '" onclick="selClass(this)" ' + checkbx + '>';
            // ary[1] = '<div style="width: 50px; overflow: hidden;" title="' + attr + '"><a href="javascript:void(null)" onclick="detail_grade(\'' + attr + '\',' + class_id + '); return false;">' + attr + '</a></div>';
            // ary[2] = '<div style="width: 100px; overflow: hidden;" title="' + htmlspecialchars(getNodeValue(nodes[i], "realname")) + '"><a href="javascript:void(null)" onclick="showDetail(\'' + attr + "'" + '); return false;">' + getNodeValue(nodes[i], "realname") + '</a></div>';
            /*Chrome*/
            ary[1] = '<div style="" title="' + attr + '"><a href="javascript:;" class="cssAnchor" onclick="detail_grade(\'' + attr + '\',' + class_id + '); return false;">' + attr + '</a></div>';
            ary[2] = '<div style="" title="' + htmlspecialchars(getNodeValue(nodes[i], "realname")) + '"><a href="javascript:;" class="cssAnchor" onclick="showDetail(\'' + attr + "'" + '); return false;">' + getNodeValue(nodes[i], "realname") + '</a></div>';
            ary[3] = getNodeValue(nodes[i], "total_course");
            ary[4] = getNodeValue(nodes[i], "G60");
            ary[5] = getNodeValue(nodes[i], "L60");
            ary[6] = getNodeValue(nodes[i], "total_avge");
            ary[7] = '<a href="javascript:void(null)" onclick="detail_grade(\'' + attr + '\',' + class_id + '); return false;" class="cssAnchor">' + icon + '</a>';

            for (var j = 7; j >= 0; j--) {
                obj.rows[idx].insertCell(0);
                obj.rows[idx].cells[0].noWrap = true;
                obj.rows[idx].cells[0].innerHTML = ary[j];

            }

            obj.rows[idx].cells[0].align = "center";
            obj.rows[idx].cells[7].align = "center";

            col = (col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
            k++;
        }
    }



    /**
     * 將群組中的班級做一個同步
     **/
    function syncclass() {
        var childs1 = null, childs2 = null, node = null, childs = null;
        var cnt = 0;

        if (groupIdx <= 1000000) return false;
        node = getGPNode(groupIdx);
        // 先移除原本的班級 (remove)
        if (node.hasChildNodes()) {
            childs = node.childNodes;
            cnt = childs.length;
            for (var i = (cnt - 1); i >= 0; i--) {
                if ((childs[i].nodeType == 1) && (childs[i].nodeName == "classes")) {
                    childs[i].parentNode.removeChild(childs[i]);
                }
            }
        }

        // 更新群組中的班級 (update)
        childs = xmlVars.documentElement.childNodes;
        cnt = childs.length;
        for (var i = 0; i < cnt; i++) {
            if ((childs[i].nodeType == 1) && (childs[i].nodeName == "class")) {
                node.appendChild(childs[i].cloneNode(true));
            }
        }
    }

    /**
     * 載入群組中所有的班級 XML
     **/
    function loadCS(val, reDisplay) {
        var obj = null, node= null, nodes = null,obj2 = null;
        var txt = "",txt2 = "";
        var page_serial = 1;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        obj = document.getElementById("CGroup");
        if (obj != null) obj.value = val;

        // sort field
        obj = document.getElementById("sby1");
        obj.value = '1';

        //  asc or desc
        obj = document.getElementById("oby1");
        obj.value = 'asc';

        //  clear title hyperlink
        clear_title_hyperlink();

        // clear query keyword
        cancel_btn();

        // clear checkbox
        cancelChk();

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

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // 目前正在第幾頁 (page serial no)
        // obj = document.getElementById("selBtn1");
        // if (obj != null) page_serial = obj.value;

        // sort field
        var sort_key = 1;
        var sort_str = '';

        obj = document.getElementById("sby1");
        if (obj != null) sort_key = obj.value;

        //  asc or desc
        obj = document.getElementById("oby1");

        if (obj.value == ''){
            obj.value = 'asc';

            sort_str = stud_sort[sort_key] + ' asc';
        }else if (obj.value == 'asc'){
             sort_str = stud_sort[sort_key] + ' asc';

        }else if (obj.value == 'desc'){
             sort_str = stud_sort[sort_key] + ' desc';
        }

        //  查詢目前所在的位置 (end)


        txt2 = "<manifest><ticket></ticket><classes_id>" + val + "</classes_id>"+
               "<page_serial>"+ page_serial + "</page_serial><page_num>"+ listNum + "</page_num>"+
               "<sby1>"+sort_str+"</sby1></manifest>";

        xmlVars.loadXML(txt2);

        xmlHttp.open("POST", "class_get_grade.php", false);
        xmlHttp.send(xmlVars);

        xmlVars.loadXML(xmlHttp.responseText);

        // alert('xmlHttp.responseText(people_manager.js 378)='+xmlHttp.responseText);
        // alert('t/f 379='+xmlVars.loadXML(xmlHttp.responseText));

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
            goPage(1);
        }

    }

    function queryClass() {

        var obj = null, node= null, nodes = null;
        var txt = "",txt1 = "",txt2 = "",txt3 = "",txt4 = "",txt5 = "",txt6="";
        var txt7 = "",txt8 = "",txt9="";
        var sdate = "",edate = "";
        var page_serial = 1;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        // 位在那個節點 (CGroup)
        obj = document.getElementById("CGroup");
        if (obj != null) txt1 = obj.value;

        // 搜尋 (searchkey)
        obj = document.getElementById("searchkey");
        if (obj != null) txt2 = obj.value;

        // 關鍵字 (keyword)
        obj = document.getElementById("keyword");
        if (obj != null) txt3 = htmlspecialchars(obj.value);


        // 修課期間 (period)
        obj = document.getElementById("sdate");
        if (obj != null) txt4 = obj.value;
        /*
        var temp = checkDate(txt4);
        if (temp > 0){
            switch (temp){
                case 1:
                    alert(msg1);
                    break;
                case 2:
                    alert(msg2);
                    break;
                case 3:
                    alert(msg4);
                    break;
                case 4:
                    alert(msg5);
                    break;
                case 5:
                    alert(msg6);
                    break;
                case 6:
                    alert(msg3);
                    break;
            }
            return false;
        }
        */
        if (txt4 != ''){
            sdate = txt4+' 00:00:00';
        }

        obj = document.getElementById("edate");
        if (obj != null) txt5 = obj.value;
        /*
        var temp = checkDate(txt5);
        if (temp > 0){
            switch (temp){
                case 1:
                    alert(msg1);
                    break;
                case 2:
                    alert(msg2);
                    break;
                case 3:
                    alert(msg4);
                    break;
                case 4:
                    alert(msg5);
                    break;
                case 5:
                    alert(msg6);
                    break;
                case 6:
                    alert(msg3);
                    break;
            }
            return false;
        }
        */
        if (txt5 != ''){
            edate = txt5+' 23:59:59';
        }

        //  詳細分數 (detail)
        var obj = document.getElementById("actFm");
        obj.sdate.value=sdate;
        obj.edate.value=edate;

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // sort field
        obj = document.getElementById("sby1");
        var sort_key = 1;
        if (obj != null)
            sort_key = obj.value;

        //  asc or desc
        obj = document.getElementById("oby1");

        if (obj.value == ''){
            obj.value = 'asc';

            sort_str = stud_sort[sort_key] + ' asc';

        }else if (obj.value == 'asc'){
             sort_str = stud_sort[sort_key] + ' asc';

        }else if (obj.value == 'desc'){
             sort_str = stud_sort[sort_key] + ' desc';
        }

        txt = "<manifest><classes_id>"+ txt1 + "</classes_id>"+
              "<searchkey>"+ txt2 +"</searchkey>"+
              "<keyword>"+ txt3 +"</keyword>"+
              "<sdate>"+sdate + "</sdate>"+
              "<edate>"+edate+"</edate>"+
              "<page_serial>"+page_serial+"</page_serial>"+
              "<page_num>"+ listNum + "</page_num>"+
              "<sby1>"+sort_str+"</sby1>"+
              "</manifest>";

        xmlVars.loadXML(txt);

        xmlHttp.open("POST", "people_query_grade.php", false);
        xmlHttp.send(xmlVars);
        xmlVars.loadXML(xmlHttp.responseText);

        // alert('txt 482='+txt);
        // alert('xmlHttp.responseText 483='+xmlHttp.responseText);
        // alert('xmlVars.loadXML(xmlHttp.responseText) 484='+xmlVars.loadXML(xmlHttp.responseText));

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

        /*
        *  如果有使用過查的按鈕
        *  要將  query_btn 的值 設為 1
        *  主要是 判斷排序的來源
        *  1. 未使用查詢   2. 已使用查詢按鈕
        */
        obj = document.getElementById("query_btn");
        obj.value = 1;

        closeDetail();
        groupIdx = txt4;
        pageIdx = 1;
        buildPageList();   // Create page list
        goPage(1);

    }

////////////////////////////////////////////////////////////////////////////
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

        var obj = null;
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
        // 換頁清單的同步 (switch)

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
                    temp = msg_title132;
                    obj2 = document.getElementById('title132');
                    break;
                case 3:
                    temp = msg_title124;
                    obj2 = document.getElementById('title124')
                    break;
                case 4:
                    temp = msg_title125;
                    obj2 = document.getElementById('title125')
                    break;
                case 5:
                    temp = msg_title115;
                    obj2 = document.getElementById('title115')
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
////////////////////////////////////////////////////////////////////////////
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

        //  asc or desc
        obj = document.getElementById("oby1");

        if (sort_order == ''){
            obj.value = 'asc';
            sort_key = stud_sort[sort_val] + ' asc';

        }else if (sort_order == 'asc'){
             sort_key = stud_sort[sort_val] + ' asc';

        }else if (sort_order == 'desc'){
             sort_key = stud_sort[sort_val] + ' desc';
        }

        // sort end

        obj = document.getElementById("CGroup");

        if (obj != null) class_group_id = parseInt(obj.value);

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

        // alert('txt2 800='+txt2);

        xmlVars.loadXML(txt2);

        // alert('xmlVars.loadXML(txt2) 1180='+xmlVars.loadXML(txt2));

        xmlHttp.open("POST", "class_get_grade.php", false);
        xmlHttp.send(xmlVars);

        xmlVars.loadXML(xmlHttp.responseText);

        //  alert('xmlHttp.responseText(people_manager.js 1808)='+xmlHttp.responseText);
        // alert('xmlVars.xml 1809='+xmlVars.xml);
           //  alert('t/f 1810='+xmlVars.loadXML(xmlHttp.responseText));

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
     * 換頁重新 在抓 sql 資料 for goPage(val) method 使用
     */
     function page_chage2(go_page){
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

        //  asc or desc
        obj = document.getElementById("oby1");

        if (sort_order == ''){
            obj.value = 'asc';
            sort_key = stud_sort[sort_val] + ' asc';

        }else if (sort_order == 'asc'){
             sort_key = stud_sort[sort_val] + ' asc';

        }else if (sort_order == 'desc'){
             sort_key = stud_sort[sort_val] + ' desc';
        }

        // sort end

        obj = document.getElementById("CGroup");

        if (obj != null) class_group_id = parseInt(obj.value);

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

        // 搜尋 (searchkey)
        var searchkey = '';
        obj = document.getElementById("searchkey");
        if (obj != null) searchkey = obj.value;

        // 關鍵字 (keyword)
        var key_word = '';
        obj = document.getElementById("keyword");
        if (obj != null) key_word = htmlspecialchars(obj.value);


        // 修課期間 (period)
        var sdate = '',sdate1 = '';
        obj = document.getElementById("sdate");
        if (obj != null) sdate1 = obj.value;
        /*
        var temp = checkDate(sdate1);
        if (temp > 0){
            switch (temp){
                case 1:
                    alert(msg1);
                    break;
                case 2:
                    alert(msg2);
                    break;
                case 3:
                    alert(msg4);
                    break;
                case 4:
                    alert(msg5);
                    break;
                case 5:
                    alert(msg6);
                    break;
                case 6:
                    alert(msg3);
                    break;
            }
            return false;
        }
        */
        if (sdate1 != ''){
            sdate = sdate1+' 00:00:00';
        }

        var edate = '',edate1 = '';
        obj = document.getElementById("edate");
        if (obj != null) edate1 = obj.value;
        /*
        var temp = checkDate(edate1);
        if (temp > 0){
            switch (temp){
                case 1:
                    alert(msg1);
                    break;
                case 2:
                    alert(msg2);
                    break;
                case 3:
                    alert(msg4);
                    break;
                case 4:
                    alert(msg5);
                    break;
                case 5:
                    alert(msg6);
                    break;
                case 6:
                    alert(msg3);
                    break;
            }
            return false;
        }
        */
        if (edate1 != ''){
            edate = edate1+' 23:59:59';
        }

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        //  查詢目前所在的位置 (end)

        txt2 = "<manifest><ticket></ticket>"+
               "<classes_id>" + class_group_id + "</classes_id>"+
               "<searchkey>"+ searchkey +"</searchkey>"+
               "<keyword>"+ key_word +"</keyword>"+
               "<sdate>"+sdate + "</sdate>"+
               "<edate>"+edate+"</edate>"+
               "<page_serial>"+ go_page + "</page_serial>"+
               "<page_num>"+ listNum + "</page_num>"+
              "<sby1>"+sort_key+"</sby1></manifest>";

        // alert('txt2 1024='+txt2);

        xmlVars.loadXML(txt2);

        // alert('xmlVars.loadXML(txt2) 1180='+xmlVars.loadXML(txt2));

        xmlHttp.open("POST", "people_query_grade.php", false);
        xmlHttp.send(xmlVars);

        xmlVars.loadXML(xmlHttp.responseText);

        // alert('xmlHttp.responseText(people_manager.js 1035)='+xmlHttp.responseText);
        // alert('xmlVars.xml 1036='+xmlVars.xml);
           // alert('t/f 1037='+xmlVars.loadXML(xmlHttp.responseText));

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
////////////////////////////////////////////////////////////////////////////
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
////////////////////////////////////////////////////////////////////////////
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
////////////////////////////////////////////////////////////////////////////
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
////////////////////////////////////////////////////////////////////////////
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

        // alert('m 1157='+m+'->cnt='+cnt);
        nowSel = (m == cnt);
        document.getElementById("ckbox").checked = nowSel;

        /*
         * button 顯示 全選 或 全消 begin
         */
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
////////////////////////////////////////////////////////////////////////////
    /**
     * 全選或全消
     **/
    function selFunc(actType) {
        var nodes = document.getElementsByTagName("input");

        for (var i = 0; i < nodes.length; i++) {
            attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (nodes[i].value != '')){
                // alert('nodes[i].value 1189='+nodes[i].value);

                nodes[i].checked = actType;
                selClass(nodes[i]);
            }
        }
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
            if ((nodes[i].type == "checkbox") && (attr == null)) {
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
    function editSysbar(val) {
        var obj = document.getElementById("sysbarFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.csid.value = parseInt(val);
        obj.submit();
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
            case 1 :   //  寄信 (send mail)
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

                break;
        }
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
     * 顯示或隱藏對話框
     * state:
     *     true : 顯示
     *     false: 隱藏
    **/
    function actionLayer(objName, state) {
        var obj = document.getElementById(objName);
        obj.style.left = "200px";
        layerAction(objName, state);
    }
////////////////////////////////////////////////////////////////////////////
    /**
     *  詳細分數
     **/
    function detail_grade(username,class_id) {

        var obj = document.getElementById("actFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;

        obj.user.value = username;
        obj.class_id.value = class_id;

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
     * 將 標題的 hyperlink 清空
     **/
    function clear_title_hyperlink(){
        var obj = null;
        var temp_class = '';

        obj = document.getElementById('title34');

        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(1);" >' + msg_title34 + '</a>';

        obj = document.getElementById('title132');

        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(2);" >' + msg_title132 + '</a>';

        obj = document.getElementById('title124');

        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(3);" >' + msg_title124 + '</a>';

        obj = document.getElementById('title125');

        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(4);" >' + msg_title125 + '</a>';

        obj = document.getElementById('title115');

        obj.innerHTML = '<a class="cssAnchor" href="javascript:chgPageSort(5);" >' + msg_title115 + '</a>';


    }
////////////////////////////////////////////////////////////////////////////
    /**
     * 排序
     **/
    function chgPageSort(val) {

        var obj = null, obj2 = null, node= null, nodes = null;
        var txt = "",txt2 = "",txt3 = "",txt4 = "";
        var txt5 = "", txt6 = "";
        var txt7 = "",txt8 = "",txt9 = "";
        var txt10 = "",txt11 = "",txt12 = "";
        var sdate = "",edate = "";

        //  clear title hyperlink
        clear_title_hyperlink();

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

        // 位在那個節點 (CGroup)
        obj = document.getElementById("CGroup");
        if (obj != null) txt2 = obj.value;

        // 每頁顯示幾筆 (listNum)
        obj = document.getElementById("page_num");
        if (obj != null) listNum = obj.value;

        // sort field
        obj = document.getElementById("sby1");
        if (obj != null){
            obj.value = val;
        }

        //  asc or desc
        obj = document.getElementById("oby1");
        if (obj.value == ''){
            txt4 = 'asc';
            obj.value = 'asc';
            txt3 = stud_sort[val] + ' asc';
        }else if (obj.value == 'asc'){
             txt4 = 'desc';
             obj.value = 'desc';
             txt3 = stud_sort[val] + ' desc';
        }else if (obj.value == 'desc'){
             txt4 = 'asc';
             obj.value = 'asc';
             txt3 = stud_sort[val] + ' asc';
        }

        /*
        *  如果有使用過查的按鈕
        *  要將  query_btn 的值 設為 1
        *  主要是 判斷排序的來源
        *  1. 未使用查詢   2. 已使用查詢按鈕
        */
        var obj = document.getElementById("query_btn");
        var query_btn = obj.value;
        var file_name = '';

        if (query_btn == ''){

            txt = "<manifest><ticket></ticket><classes_id>" + txt2 + "</classes_id>"+
                   "<page_serial>1</page_serial><page_num>"+ listNum + "</page_num>"+
                   "<sby1>"+txt3+"</sby1></manifest>";

            file_name = 'class_get_grade.php';
        }else{
            // 搜尋 (searchkey)
            obj = document.getElementById("searchkey");
            if (obj != null) txt5 = obj.value;

            // 關鍵字 (keyword)
            obj = document.getElementById("keyword");
            if (obj != null) txt6 = htmlspecialchars(obj.value);

            // 修課期間 (period)
            obj = document.getElementById("sdate");
            if (obj != null) txt7 = obj.value;

            if (txt7 != ''){
                sdate = txt7+' 00:00:00';
            }

            obj = document.getElementById("edate");
            if (obj != null) txt8 = obj.value;

            if (txt8 != ''){
                edate = txt8+' 23:59:59';
            }

            txt = "<manifest>"+
                  "<classes_id>"+ txt2 + "</classes_id>"+
                    "<searchkey>"+ txt5 +"</searchkey>"+
                  "<keyword>"+ txt6 +"</keyword>"+
                  "<sdate>"+sdate + "</sdate>"+
                  "<edate>"+edate+"</edate>"+
                  "<page_serial>1</page_serial>"+
                  "<page_num>"+ listNum + "</page_num>"+
                  "<sby1>"+txt3+"</sby1>"+
                  "</manifest>";

            // alert('txt 1446='+txt);

            file_name = 'people_query_grade.php';
        }

        xmlVars.loadXML(txt);

        xmlHttp.open("POST", file_name, false);
        xmlHttp.send(xmlVars);
        xmlVars.loadXML(xmlHttp.responseText);

        // alert('xmlVars.loadXML(xmlHttp.responseText) 1455='+xmlVars.loadXML(xmlHttp.responseText));

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
    /**
     * 取消查詢
    **/
    function cancel_btn(){
        var obj = document.getElementById("keyword");
        obj.value = msg_keyword;

        var obj = document.getElementById("sdate");
        obj.value = '';

        var obj = document.getElementById("edate");
        obj.value = '';

        var obj = document.getElementById("query_btn");
        obj.value = '';

    }
////////////////////////////////////////////////////////////////////////////
    /*
     * 去掉空格
     */
    function trim(str){
         if(str.charAt(0) == " "){
              str = str.slice(1);
              str = Trim(str);
         }
         return str;
    }

/*
////////////////////////////////////////////////////////////////////////////

     // 是否為潤年
     function isLeapYear(year){
         if((year%4==0&&year%100!=0)||(year%400==0)) {
             return true;
         }
         return false;
    }

    // 檢查日期是否合法
    function checkDate(val){
        var datetime;
        var year,month,day;
        var gone,gtwo;
        var date_reg = /^[0-9]{4,}\-[0-9]{2}\-[0-9]{2}$/;

        var datetime=trim(val);

        if(datetime.length > 0){

            if (datetime.search(date_reg) == -1){
                   return 1;
               }

              var temp = datetime.split('-');

            var year = parseInt(temp[0]);
            var month = parseInt(temp[1].replace(/^0/ig,''));
            var day = parseInt(temp[2]);

            if (year == 0){
                return 1;
            }
            if (day == 0){
                return 1;
            }
            if(month<1||month>12) {
                   return 2;
            }

            if(month==2){
                if(isLeapYear(year)&&day>29){
                       return 3;
                 }
                 if(!isLeapYear(year)&&day>28){
                       return 4;
                 }
             }

            if((month==1||month==3||month==5||month==7||month==8||month==10||month==12)&&(day>31)){
                   return 6;
            }

            if((month==4||month==6||month==9||month==11)&&(day>30)){
                return 5;
               }

        }
    }
////////////////////////////////////////////////////////////////////////////
*/
    /*
     * 一頁顯示幾筆
     */
    function Page_Row(row){
        $("select[name='page_num']").val(row);

        var no_group_id = 1000000;
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



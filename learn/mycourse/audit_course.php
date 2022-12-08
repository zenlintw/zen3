<?php
    /**
     * 功能名稱 個人區→我的課程→全校課程→課程：可旁聽
     * @since   2016/05/26
     * @author  Jeff Wang
      * @version $Id: audit_course.php,v 1.1 2010/02/24 02:39:08 saly Exp $
     * @copyright Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/audit_course.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

    if ((strlen($_GET['cour_id']) == 8) && is_numeric($_GET['cour_id'])){
        $cour_str = 'var course_id=' . $_GET['cour_id'] . ';';
        $course_id = $_GET['cour_id'];
    }else{
        $cour_str = '';
    }

    $create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Add_Audit' . $sysSession->username);
    $nEnv = $sysSession->env == 'teach' ? 2 : 1;
$js = <<< BOF
    function add_audit(){
        {$cour_str}
        var xmlVars = null, xmlHttp = null, xmlDocs = null;
        var obj = null, nodes = null, node = null,msg_node = null;
        var ticket = "{$create}";
        var msg = '',state_code = 1;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();

        if (typeof(course_id) != 'undefined'){
            txt  = "<manifest>";
            txt += "<ticket>" + ticket + "</ticket>";
            txt += "<course_id>" + parseInt(course_id) + "</course_id>";
            txt += "</manifest>";

            res = xmlVars.loadXML(txt);
            if (!res) {
                alert("{$MSG['msg_system_error'][$sysSession->lang]}");
                return false;
            }
            xmlHttp.open("POST", "add_audit.php", false);
            xmlHttp.send(xmlVars);

            if (!xmlVars.loadXML(xmlHttp.responseText)) {
                alert("{$MSG['msg_system_error'][$sysSession->lang]}");
                return false;
            }

            msg_node = xmlVars.selectSingleNode('//state_msg');

            if (msg_node.hasChildNodes()) {
                msg = msg_node.firstChild.nodeValue;
            }

            node = xmlVars.selectSingleNode('//state_code');

            if (node.hasChildNodes()) {
                state_code = parseInt(node.firstChild.nodeValue);
                
                switch (state_code){
                    case 0:
                        alert(msg);
                        parent.location.href = "/learn/mycourse/index.php";
                        break;
                    case 1:
                        alert(msg);
                        parent.$.fancybox.close();
                        break;
                }

            } else {
                msg = "{$MSG['add_fail2'][$sysSession->lang]}";
                alert(msg);
                parent.$.fancybox.close();
            }
        }
    }

BOF;
    
    $smarty->assign('inlineJS', $js);
    // get course name
    if ($course_id != ''){
        list($cour_name) = dbGetStSr('WM_term_course','caption','course_id=' . $course_id, ADODB_FETCH_NUM);
        $cour_lang = unserialize($cour_name);
        $show_msg = $MSG['audit_statement'][$sysSession->lang];
        $show_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$show_msg);
    }
    $smarty->assign('show_msg', $show_msg);
    
    // output
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/mycourse/audit.tpl');
    $smarty->display('common/tiny_footer.tpl');

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
    require_once(sysDocumentRoot . '/lang/enploy_course.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');

    /**
     * 取得最高優先權的審核規則編號
     * @param array $ary   : 規則的編號
     * @param array $rules : 所有的規則
     * @return string $rid : 編號
     **/
    function getfwid($ary, $rules) {
        $rid = 0;
        $mut = -1;
        if (!is_array($ary)) return $rid;
        foreach ($ary as $idx) {
            $tmp = $rules[$idx][0];
            if (($mut < 0) || ($tmp < $mut)) {
                $rid = $idx;
                $mut = $tmp;
            }
        }
        return $rid;
    }
    
    if ((strlen($_GET['cour_id']) == 8) && is_numeric($_GET['cour_id'])){
        $cour_str = 'var course_id=' . $_GET['cour_id'] . ';';
        $course_id = intval($_GET['cour_id']);
    }else{
        die();
    }

    if ($sysSession->username=='guest') {
        header('HTTP/1.1 403 Forbidden');
    exit;
    }

    $create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Add_Audit' . $sysSession->username);
    $nEnv = $sysSession->env == 'teach' ? 2 : 1;
$js = <<< BOF
    function add_student(){
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
            xmlHttp.open("POST", "add_student.php", false);
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
        
    $enRules  = array();
    $enMaps   = array();
    // 先取出所有規則
    $RS = dbGetStMr('WM_review_syscont', '`flow_serial`, `content`, `permute`', "`kind`='course' order by `permute`", ADODB_FETCH_ASSOC);
    while (!$RS->EOF) {
        $enRules[$RS->fields['flow_serial']] = array($RS->fields['permute'], $RS->fields['content']);
        $RS->MoveNext();
    }
    
    // 取出所有的對應關係
    if (count($enMaps) <= 0) {
        $RS = dbGetStMr('WM_review_sysidx', '`discren_id`, `flow_serial`', '1 order by `discren_id`', ADODB_FETCH_ASSOC);
        while (!$RS->EOF) {
            $enMaps[$RS->fields['discren_id']][] = $RS->fields['flow_serial'];
            $RS->MoveNext();
        }
    }
    
    // 開始查詢規則
    $res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
    $res .= '<manifest><result>1</result></manifest>';
    $now  = time();
    // 取得所選課程的正式生與旁聽生數量限制(一次取得，不必放在迴圈中每門課來兩次 SQL query)
    $student_amount_limits = dbGetAssoc('WM_term_major',
        'course_id, sum(if(role&' . $sysRoles['student'] . ', 1, 0)) , sum(if(role&' . $sysRoles['auditor'] . ', 1, 0))',
        sprintf('course_id = %d group by course_id', $course_id),
        ADODB_FETCH_NUM);
    // 檢查有無審核規則 (Begin)
    $rid = 0;
    // 課程的規則 (Begin)
    if (isset($enMaps[$course_id])) {
        $ary = $enMaps[$course_id];
        $rid = getfwid($ary, $enRules);
    }
    
    // 課程的規則 (End)
    // 檢查有無審核規則 (End)
    $smarty->assign('reviewRuleId', $rid);
    
    $smarty->assign('inlineJS', $js);
    // get course name
    if ($course_id != ''){
        list($cour_name) = dbGetStSr('WM_term_course','caption','course_id=' . $course_id, ADODB_FETCH_NUM);
        $cour_lang = unserialize($cour_name);
        $show_msg = ($rid <= 1)?$MSG['audit_statement'][$sysSession->lang]:$MSG['audit_statement_review'][$sysSession->lang];
        $show_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$show_msg);
    }
    $smarty->assign('show_msg', $show_msg);
    
    $smarty->assign('course_id', $course_id);
    
    
    // output
    $smarty->display('common/tiny_header.tpl');
    $smarty->display('learn/mycourse/enploy.tpl');
    $smarty->display('common/tiny_footer.tpl');

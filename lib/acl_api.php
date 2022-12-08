<?php
    /**
     * $Id: acl_api.php,v 1.1 2010/02/24 02:39:32 saly Exp $
     */

    // 系統執行範圍
    $sysScopes = array( 1 => 'learn',
                        2 => 'teach',
                        4 => 'direct',
                        8 => 'academic');

    // 系統權限
    $sysPermissions =array(   1 => 'enable',
                              2 => 'visible',
                              4 => 'readable',
                              8 => 'writable',
                             16 => 'modifiable',
                             32 => 'uploadable',
                             64 => 'removable',
                            128 => 'manageable',
                            256 => 'assignable');
    // 程式執行環境
    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    // 禁止單獨使用
    defined('sysDocumentRoot') or exit;
    // 因為有對話框，所以要引用介面 API
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lang/acl_api.php');
    require_once(sysDocumentRoot . '/lang/academic_access.php');
    require_once(sysDocumentRoot . '/lang/sysbar_config.php');
        
        // 是否是行動裝置
        $isMobile = isMobileBrowser() ? '1' : '0';

    /**
     * 取得所有學校
     *
     * @access  public  公用 API
     * @return  array   傳回 學校 ID => 學校名稱 的陣列
     */
    function aclGetAllSchool(){
        return dbGetAssoc('WM_school', 'distinct school_id, school_name', '1');
    }

    /**
     * 取得某校所有課程及教材
     *
     * @access  public  公用 API
     * @return  array   傳回課程與教材的 ID => 名稱 陣列
     */
    function aclGetAllDepartment(){
        global $sysConn, $sysSession, $MSG;

        $ret = array('a' => $MSG['course'][$sysSession->lang]);
        $rs = dbGetStMr('WM_term_course', 'course_id, caption', 'kind="course" and status != 9', ADODB_FETCH_ASSOC);
        if ($rs) while($fields = $rs->FetchRow()){
            if (strpos($fields['caption'], 'a:') === 0)
                $titles = unserialize($fields['caption']);
            else
                $titles[$sysSession->lang] = $fields['caption'];
            $ret[$fields['course_id']] = sprintf('&nbsp;&nbsp;|_ (%s) %s', $fields['course_id'], htmlspecialchars(mb_substr($titles[$sysSession->lang], 0, 50, 'UTF-8')));
        }

        $ret['b'] = ''; $ret['c'] = $MSG['material'][$sysSession->lang];
        $rs = dbGetStMr('WM_content', 'content_id, caption', '1', ADODB_FETCH_ASSOC);
        if ($rs) while($fields = $rs->FetchRow()){
            if (strpos($fields['caption'], 'a:') === 0)
                $titles = unserialize($fields['caption']);
            else
                $titles[$sysSession->lang] = $fields['caption'];
            $ret[$fields['content_id']] = sprintf('&nbsp;&nbsp;|_ (%s) %s', $fields['content_id'], htmlspecialchars(mb_substr($titles[$sysSession->lang], 0, 50, 'UTF-8')));
        }
        return $ret;
    }

    /**
     * 取得某課程或某教材之所有元素 (討論板、討論室、作業、測驗、問卷)
     *
     * @access  public  公用 API
     * @param   integer $department_id  課程 ID (course_id) 或 班級 ID (class_id)
     * @param   bool    $returnHTML     傳回 HTML 碼，或是只傳回陣列
     * @param   integer $selected       ID值。決定 <select> 中的哪個 <option> 要 selected
     * @return  string|array            若 $returnHTML=true 傳回 <select> HTML 碼；否則傳回 ID => 名稱 陣列
     */
    function aclGetAllElement($department_id, $returnHTML=false, $selected=null){
        global $sysConn, $sysSession, $MSG;

        $department_id = intval($department_id);
        $index = 97;
        // 取討論板
        $rets = array(chr($index++) => $MSG['forum'][$sysSession->lang]);
        $rs = dbGetStMr('WM_bbs_boards', 'board_id, bname', 'owner_id=' . $department_id, ADODB_FETCH_ASSOC);
        if ($rs) while($fields = $rs->FetchRow()){
            $titles = unserialize($fields['bname']);
            $rets[$fields['board_id']] = sprintf('&nbsp;&nbsp;|_ (%s) %s ', $fields['board_id'], htmlspecialchars(mb_substr($titles[$sysSession->lang], 0, 50, 'UTF-8')));
        }

        // 取討論室
        $rets[chr($index++)] = ''; $rets[chr($index++)] = $MSG['chatroom'][$sysSession->lang];
        $rs = dbGetStMr('WM_chat_setting', 'rid, title', sprintf('owner RegExp "^%s$|^%s_"', $department_id, $department_id) , ADODB_FETCH_ASSOC);
        if ($rs) while($fields = $rs->FetchRow()){
            $titles = unserialize($fields['title']);
            $rets[$fields['rid']] = sprintf('&nbsp;&nbsp;|_ (%s) %s ', $fields['rid'], htmlspecialchars(mb_substr($titles[$sysSession->lang], 0, 50, 'UTF-8')));
        }

        // 取作業
        if (strlen($department_id) == 8){
            foreach(array('homework'      => $MSG['homework'][$sysSession->lang],
                          'exam'          => $MSG['exam'][$sysSession->lang],
                          'questionnaire' => $MSG['questionnaire'][$sysSession->lang]) as $k => $v){

                $rets[chr($index++)] = ''; $rets[chr($index++)] = $v;
                $rs = dbGetStMr('WM_qti_' . $k . '_test', 'exam_id, title', 'course_id=' . $department_id, ADODB_FETCH_ASSOC);
                if ($rs) while($fields = $rs->FetchRow()){
                    $titles = unserialize($fields['title']);
                    $rets[$fields['exam_id']] = sprintf('&nbsp;&nbsp;|_ (%04d) %s ', $fields['exam_id'], htmlspecialchars(mb_substr($titles[$sysSession->lang], 0, 50, 'UTF-8')));
                }
            }
        }
        // 自定功能

        if ($returnHTML){
            ob_start();
            showXHTML_input('select', 'element_id', $rets, $selected);
            // $ret = iconv('Big5', 'UTF-8', ob_get_contents());
            $ret = ob_get_contents();
            ob_end_clean();
            return $ret;
        }
        else
            return $rets;
    }

    /**
     * 將權限範圍字串，轉為 bitmap 陣列
     *
     * @access  public  公用 API
     * @param   string  $scope  以逗點隔開的權限範圍字串
     * @return  array   傳回 Bitmap 陣列
     */
    function aclScope2Array($scope){
        $s = array_flip($GLOBALS['sysScopes']);
        $ret = array();
        foreach(explode(',', $scope) as $item) $ret[] = $s[$item];
        return $ret;
    }

    /**
     * 將權限字串轉為 bitmap 陣列
     *
     * @access  public  公用 API
     * @param   string  $perm   以逗點隔開的權限描述字串
     * @return  array   傳回權限 Bitmap 陣列
     */
    function aclPermission2Array($perm){
        $s = array_flip($GLOBALS['sysPermissions']);
        $ret = array();
        foreach(explode(',', $perm) as $item) $ret[] = $s[$item];
        return $ret;
    }

    /**
     * 將權限字串轉換為 BitMap
     *
     * @access  public  公用 API
     * @param   string  $perm   以逗點隔開的權限字串
     * @return  integer 傳回權限 Bitmap 整數值
     */
    function aclPermission2Bitmap($perm){
        return array_sum(aclPermission2Array($perm));
    }

    /**
     * 將 BitMap 轉換為權限字串
     *
     * @access  public  公用 API
     * @param   integer $bitmap 權限 bitmap 整數值
     * @return  string  以逗點隔開的權限字串
     */
    function aclBitmap2Permission($bitmap){

        $ret = '';
        foreach($GLOBALS['sysPermissions'] as $k => $v)
        {
            if ($bitmap & $k) $ret .= "$v,";
        }
        return substr($ret, 0, -1); // 去掉最後的逗號
    }

    /**
     * 將角色 Bitmap 轉為字串
     *
     * @access  public
     * @param   integer $bitmap 角色 bitmap 整數值
     * @return  string  傳回以逗點隔開的角色描述字串
     */
    function aclBitmap2Roles($bitmap){
        $ret = ''; $b = intval($bitmap);

        foreach(array_slice($GLOBALS['sysRoles'], 0, -1) as $k => $v){
            if ($v & $b) $ret .= "$k,";
        }
        return substr($ret, 0, -1); // 去掉最後的逗號
    }


    /**
     * 產生 ACL 控制選單
     *
     * @access  public
     * @param   hash    $func           功能 RecordSet。
     * @param   string  $actionObject   作用物件名稱。也許是測驗名稱或作業名稱。如果不是空字串則表示是修改 ACL 內容。
     * @param   hash    $acl            ACL RecordSet。若為 NULL 表示要新建 ACL
     * @param   integer $roles          角色 bitmap 整數值
     * @param   string  $extra_member   其它成員帳號，每個帳號以換行隔開
     * @param   bool    $dragable       此對話框是否可拖曳
     * @return  void
     */
    function aclGenerateAclControlPanel($func, $actionObject='', $acl=NULL, $roles=0, $extra_member='', $dragable=false){
        global $sysSession, $sysRoles, $MSG;
        static $panelSerial=0;

        if (!is_null($acl)){
            $perm_bitmaps = aclPermission2Bitmap($acl['permission']);
        }

        $panelSerial++;
        if ($panelSerial == 1){
            showXHTML_script('inline', "");
        }

      $ary = array(array((is_null($acl)?$MSG['create'][$sysSession->lang]:$MSG['modify'][$sysSession->lang]) . ' ACL', 'tabsSet', ''));
      showXHTML_tabFrame_B($ary, 1, 'acl_save_form', 'acl_table_element', 'action="access_acl1.php" method="POST" style="display: ' . ($dragable ? 'none' : 'inline') . '"', $dragable);

        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="box01"');

          showXHTML_tr_B('class="bg03 font01"');
            showXHTML_td('', $MSG['binding function'][$sysSession->lang]);
            showXHTML_td_B('colspan="3"');
              echo '(', $func['function_id'], ') ', $func['caption'];
              showXHTML_input('hidden', 'function_id', $func['function_id']);
            showXHTML_td_E();
          showXHTML_tr_E();

          showXHTML_tr_B('class="bg04 font01"');
            showXHTML_td('', $MSG['apply to'][$sysSession->lang]);
          if ($actionObject){
            showXHTML_td_B('colspan="4"'); echo $actionObject;
              showXHTML_input('hidden', 'department_id', $acl['unit_id']);
              showXHTML_input('hidden', 'element_id',    $acl['instance']);
            showXHTML_td_E();
          }
          else{
            showXHTML_td_B('colspan="3"');
              echo "<span id=\"departmentSelect\">\n";
              showXHTML_input('select', 'department_id', aclGetAllDepartment(), $acl['unit_id'], 'onchange="fetchElement(this.value, null)"'); echo "<br>\n";
              echo "</span><span id=\"elementSelect\">\n";
              showXHTML_input('select', 'element_id', array(), '', '');
              echo "</span>\n";
            showXHTML_td_E();
          }
          showXHTML_tr_E();

          if (strpos($acl['caption'], 'a:') === 0)
            $captions = unserialize($acl['caption']);
          else
            $captions[$sysSession->lang] = $acl['caption'];
          
          showXHTML_tr_B('class="bg03 font01"');
            showXHTML_td('rowspan="5"', 'ACL ' . $MSG['caption'][$sysSession->lang]);
            showXHTML_td_B('colspan="2"');
              showXHTML_input('text', 'acl_caption[Big5]',   $captions['Big5'], '', 'size="40" maxlength="64" class="box02"');
            showXHTML_td_E();
            showXHTML_td('rowspan="5"', $MSG['acl caption'][$sysSession->lang]);
          showXHTML_tr_E();
          foreach(array('GB2312', 'en', 'EUC-JP', 'user_define') as $charset){
            $cls = $cls == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
            showXHTML_tr_B($cls);
              showXHTML_td_B('colspan="2"');
                showXHTML_input('text', "acl_caption[$charset]", $captions[$charset], '', 'size="40" maxlength="64" class="box02"');
              showXHTML_td_E();
            showXHTML_tr_E();
          }

          showXHTML_tr_B('class="bg04 font01"');
            showXHTML_td('rowspan="9"', 'ACL ' . $MSG['permission'][$sysSession->lang]);
            showXHTML_td_B('colspan="2"');
              showXHTML_input('checkbox', 'permission[]',   1, '', ((!isset($acl)||($perm_bitmaps&1))?'checked':'')); echo $MSG['enable'][$sysSession->lang];
            showXHTML_td_E();
            showXHTML_td('rowspan="9"', $MSG['acl permission'][$sysSession->lang]);
          showXHTML_tr_E();
          $perm_bitmap = 2;
          foreach(array('visible','readable','writable','modifiable','uploadable','removable','manageable','assignable') as $perm){
            showXHTML_tr_B($cls);
                showXHTML_td_B('colspan="2"' . ' style="display: none"'); // 暫時隱藏其它權限
                  if ($perm_bitmap > 64)
                    showXHTML_input('checkbox', "permission[]", $perm_bitmap, '', (($perm_bitmaps&$perm_bitmap)?'checked':''));
                  else
                    showXHTML_input('checkbox', "permission[]", $perm_bitmap, '', ((!isset($acl)||($perm_bitmaps&$perm_bitmap))?'checked':''));
                  echo $MSG[$perm][$sysSession->lang];
                showXHTML_td_E();
            showXHTML_tr_E();
            $perm_bitmap <<= 1;
            $cls = $cls == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"' ;
          }

          showXHTML_tr_B('class="bg03 font01"');
            showXHTML_td('rowspan="13"', 'ACL ' . $MSG['member'][$sysSession->lang]);
            showXHTML_td_B();
              showXHTML_input('checkbox', 'role[]',   1, '', ($roles&$sysRoles['guest']?'checked':''));
              echo $MSG['guest'][$sysSession->lang];
            showXHTML_td_E();
            showXHTML_td_B('rowspan="13"');
              echo $MSG['specific user'][$sysSession->lang], '<br>';
              showXHTML_input('textarea', 'extra_member', $extra_member, '', 'rows="20" cols="20"');
            showXHTML_td_E();
            showXHTML_td('rowspan="13"', $MSG['specific user tips'][$sysSession->lang]);
          showXHTML_tr_E();

          $role_subset = $sysRoles; array_shift($role_subset); array_pop($role_subset);
          foreach($role_subset as $k => $v){
            $cls = $cls == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
            // 暫時把「學長」「家長」「長官」三個身份隱藏
            showXHTML_tr_B($cls . (in_array($k, array('senior','paterfamilias','superintendent')) ? ' style="display: none"' : ''));
              showXHTML_td_B();
                showXHTML_input('checkbox', 'role[]', $v, '', ($roles&$sysRoles[$k]?'checked':''));
                echo $MSG['bar_role_' . $k][$sysSession->lang];
              showXHTML_td_E();
            showXHTML_tr_E();
          }

          showXHTML_tr_B('class="bg04 font01"');
            showXHTML_td_B('colspan="4" align="center"');
              showXHTML_input('submit', '', (isset($acl) ? $MSG['enforce modifying'][$sysSession->lang] : $MSG['enforce creating'][$sysSession->lang]), '', 'class="cssBtn"');
              showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="history.back();"');
              showXHTML_input('hidden', 'attribute', substr(strstr($_SERVER['QUERY_STRING'], '&'), 1));
              if (isset($acl)) showXHTML_input('hidden', 'acl_id', $acl['acl_id']);
            showXHTML_td_E();
          showXHTML_tr_E();

        showXHTML_table_E();
      showXHTML_tabFrame_E();

    }

    /**
     * 產生 ACL 控制選單。本函式用在各個程式中，產生新增、修改 ACL 的對話框。其中包含控制 Javascript 程式。
     * ※ 請自行在程式前，以 acl_lists 為 js 陣列名，產生一二維字串陣列，第一維為功能索引 (每個所引代表不同 function)，
     *    第二維為 ACL 列表字串 (每個元素表示一個 ACL)，字串內容以 \b (0x08) 隔開，分別是：
     *    ACL_ID (\f) ACL 名稱 (\f) permission_bitmap (\f) roles_bitmap (\f) extra_member
     *    另外，ACL 名稱以 \t (Tab 0x09) 隔開五種語系，extra_member 亦以 \t 隔開各個帳號。
     *
     * ※ 網頁中，要顯示 ACL 列表的地方 (如 <td>, <span>, <div>) 請設 ID="aclDisplayPanel_陣列索引"
     *    並在 window.onload 事件中，執行 generate_list(索引值); 這個 js API
     *
     * @access  public  公用 API
     * @return  void
     */
    function aclGenerateAclControlPanel2(){
        if (!class_exists('Multi_lang') )
            include_once(sysDocumentRoot . '/lib/multi_lang.php');

        global $sysSession, $sysRoles, $topDir, $MSG, $sysConn, $ADODB_FETCH_MODE;
        static $panelSerial=0;

        $QTI_which = defined('QTI_which') ? QTI_which : '';
        if (defined('XMLAPI') && XMLAPI && defined('API_QTI_which')) {
            $QTI_which = API_QTI_which;
        }

        if (($lc = array_search($sysSession->lang, array('Big5','GB2312','en','EUC-JP','user_define'))) === FALSE) $lc = 0;

        showXHTML_CSS('inline', "
#assignTeamPanel, #assignTeamPanel ul {list-style-type: none;}
        ");
                global $isMobile;
        $acl_scr = <<< EOB

var acl_index = 0;              // function 索引。
var lang_charset = $lc;         // 語系索引
var acl_hidden_flags = false;   // 是否要隱藏對話框的旗標
var isMobile = '{$isMobile}';
if (typeof(acl_lists) == 'undefined') var acl_lists = new Array();  // 如果不是修改功能的話，就定義 acl_lists 為新陣列

// 如果沒宣告 displayDialogWindow() 這個 function 就自己宣告
if (typeof(displayDialogWindow) != 'function'){

    /**
     * 顯示對話框
     *
     * @param   string  objName 對話框的 ID
     * @return void
     */
    function displayDialogWindow(objName){
        var obj = document.getElementById(objName);
        // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
                if (isMobile === '1') {
                    obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 860;
                } else {
                    obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;
                }
        // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
                if (isMobile === '1') {
                    obj.style.top   = document.body.scrollTop  + 240;
                } else {
                    obj.style.top   = document.body.scrollTop  + 30;
                }
        obj.style.display='';
        // [lst] 隱藏不在本頁的下拉選單 (需要 dragLayer.js) (Begin)
        if (typeof(getHideObjList) == 'function') {
            dragObj = obj;
            getHideObjList();
            hideShowCovered();
        }
        // [lst] 隱藏不在本頁的下拉選單 (需要 dragLayer.js) (End)
    }
}

/**
 * 加入或修改一個新 ACL
 *
 * @param   string  list_id ACL 名稱。如果省略表示新建一個 ACL，如果有的話則修改內容
 * @return  void
 */
function add_list(){
                        
    /* 如果對象是選分組 */
    var leader_flag = true;

   if ($("select[name='assignTeam']:visible").size() === 1) {
        /* 檢查有勾選的分組有沒有設定組長 */
        $.each($('#assignTeamPanel').find("li[style^='display: inline']").find("input[name='groups[]']:checked"), function(key, value) {
            if ($(value).parent().find('span').data('leader') === 'N') {
                leader_flag = false;
                return;
            }
        });
                
        /* 移除個人對象 */
        $("input[name='role[]']").prop('checked', false);
        $('#extra_member').val('');
    }

    /* 同儕互評檢查有勾選的分組有沒有設定組長，如果沒有設定組長，則提出警訊 */
    if ('{$QTI_which}' === 'peer' && leader_flag === false) {
        alert('{$MSG['alarm_no_leader'][$sysSession->lang]}');
        return;
    }
                        
    var listForm = document.getElementById('acl_save_form');
    if (listForm == null) return;
    list_id = listForm.acl_id.value.replace(/^\s+|\s+$/g, '');

    var captions = new Object();
        captions['Big5']        = 'unnamed';
        captions['GB2312']      = 'unnamed';
        captions['en']          = 'unnamed';
        captions['EUC-JP']      = 'unnamed';
        captions['user_define'] = 'unnamed';
    var permission  = 0;
    var role        = 0;
    var extra_mambers = '';
    var register = false;

    var inputNodes = listForm.getElementsByTagName('input');
    var verify_flag = false;
    var verify_flag_i;
    for(var i=0; i<inputNodes.length; i++){
        switch(inputNodes[i].type){
            case 'text':
                if (inputNodes[i].name.search(/^acl_caption\[(.+)\]$/) === 0){
                    captions[RegExp.$1] = inputNodes[i].value;
                    if (inputNodes[i].value !== 'undefined' && inputNodes[i].value !== '' && inputNodes[i].value.length <=254) {
                        verify_flag = true;
                    } else {
                        if (typeof(verify_flag_i) == 'undefined') {
                            verify_flag_i = i;
                        }
                    }
                }
                break;
            case 'checkbox':
                if (inputNodes[i].name == 'permission[]' && inputNodes[i].checked) permission |= parseInt(inputNodes[i].value);
                else if (inputNodes[i].name == 'role[]' && inputNodes[i].checked && inputNodes[i].value != '!guest') role |= parseInt(inputNodes[i].value);
                else if (inputNodes[i].name == 'role[]' && inputNodes[i].checked && inputNodes[i].value == '!guest') register = true;
                break;
        }
    }

    // 判斷名稱是否正確填寫
    if (verify_flag === false) {
        inputNodes[verify_flag_i].focus();
        inputNodes[verify_flag_i].select();
        alert('{$MSG['lnguage_hint'][$sysSession->lang]}');
        return;
    }

        
    if (listForm.assignTarget.value == 'Group')
    {
            extra_mambers = '';
            var groups = document.getElementById('assignTeamID' + listForm.assignTeam.value).getElementsByTagName('input');
        
            var group_cnt = 0;
            for (var i=0; i<groups.length; i++)
            {
                    if (groups[i].checked) {
                        extra_mambers += groups[i].id.replace('assignTeamID', '@').replace('GroupID', '.') + '\\t';
                        group_cnt++;
                    }
            }
            extra_mambers = extra_mambers.replace(/\s+$/, '');

            // 判斷有沒有選角色或個別帳號
            if (('{$QTI_which}' === 'homework' && group_cnt === 0) || ('{$QTI_which}' === 'peer' && group_cnt <= 1)) {
                alert('{$MSG[$QTI_which . '_group_of_required'][$sysSession->lang]}');
                return;
            }     
    }
    else
    {
            if (listForm.extra_member.value != '') extra_mambers = listForm.extra_member.value.split(/\s+/).join('\\t');
            if (register) extra_mambers = extra_mambers == '' ? '!guest' : extra_mambers + '\\t!guest';

            // 判斷有沒有選角色或個別帳號
            if (role === 0 && extra_mambers === '') {
                alert('{$MSG['list_of_required'][$sysSession->lang]}');
                return;
            }
    }

    if (list_id == ''){ // add new
        if (typeof(acl_lists[acl_index]) == 'undefined') acl_lists[acl_index] = new Array();
        acl_lists[acl_index][acl_lists[acl_index].length] = '*new*'               + '\b' +
                                                            captions['Big5']        + '\\t' +
                                                            captions['GB2312']      + '\\t' +
                                                            captions['en']          + '\\t' +
                                                            captions['EUC-JP']      + '\\t' +
                                                            captions['user_define'] + '\b' +
                                                            permission              + '\b' +
                                                            role                    + '\b' +
                                                            extra_mambers;
    }
    else{                                // modify
        list_id = parseInt(list_id);
        var elements = acl_lists[acl_index][list_id].split('\b');
        acl_lists[acl_index][list_id] = elements[0]       + '\b' +
                                  captions['Big5']        + '\\t' +
                                  captions['GB2312']      + '\\t' +
                                  captions['en']          + '\\t' +
                                  captions['EUC-JP']      + '\\t' +
                                  captions['user_define'] + '\b' +
                                  permission              + '\b' +
                                  role                    + '\b' +
                                  extra_mambers;
    }

    hide_acl_dialog();
}

/**
 * 刪除一個 ACL
 *
 * @param   integer x       acl_index 值，表示哪個 function
 * @param   integer list_id 欲刪除的 ACL_ID
 * @return  void
function del_list(x, list_id){
    acl_index = x;
    var o;
    for(var i=0; i<acl_lists[acl_index].length; i++){
        o = acl_lists[acl_index][i].split('\b');
        if (o[0] == list_id) {
            delete(acl_lists[acl_index][i]);
            generate_list(acl_index);
            return;
        }
    }
}
 */
function del_list(x, list_id){
    if(typeof(acl_lists[x][list_id] != 'undefined')) {
        // delete(acl_lists[x][list_id]);
        acl_lists[x].splice(list_id,1);
        acl_index = x;
        generate_list(x);
    }

    // 判斷是否全刪除
    if ($('#aclDisplayPanel_0') !== null && $('#aclDisplayPanel_0 table tr').length === 0) {
        $('#aclDisplayPanel_0').text('{$MSG['default_student'][$sysSession->lang]}');
    }
}

/**
 * 顯現新增 ACL 的對話框
 *
 * @param   integer x       acl_index 值，表示哪個 function
 * @return  void
 */
function init_add_list(x){
    if (typeof(acl_lists[0]) != 'undefined')
        for (var i=0; i<acl_lists[0].length; i++)
        {
            if (acl_lists[0][i].search(/@[0-9]+\.[0-9]+$/) > -1)
            {
                alert('{$MSG['Just one grouping time only'][$sysSession->lang]}');
                return;
            }
        }

    acl_index = x;
    var listForm = document.getElementById('acl_save_form');
    var t = listForm.getElementsByTagName('table')[0];
    listForm.reset();

    if ('{$QTI_which}' == 'homework')
        assignIndividual(t.rows[t.rows.length-2], 5);
    displayDialogWindow('acl_table_element');
}

/**
 * 顯現修改 ACL 的對話框
 *
 * @param   integer x       acl_index 值，表示哪個 function
 * @param   integer acl_id  欲修改的 acl_id
 * @return void
 */
function init_modify_list(x, acl_id){
    acl_index = x;
    var listForm = document.getElementById('acl_save_form');
    var inputNodes = listForm.getElementsByTagName('input');
    var elements = acl_lists[acl_index][acl_id].split('\b');
    var captions = elements[1].split('\\t');
    var permission = parseInt(elements[2]);
    var role = parseInt(elements[3]);
    var k = 0;
    var aid;
    var elems;
    var t = listForm.getElementsByTagName('table')[0];
    
    /*
    todo: 應該要修正有KEY與VALUE，目前看似用順序，以後會有錯誤風險
    if (window.console) {console.log('acl_lists' , acl_lists);}
    if (window.console) {console.log('elements' , elements);}
    if (window.console) {console.log('captions' , captions);}
    */
    
    var register = false;    // 判斷是否有!guest (已註冊者)
    if ((pos = elements[4].indexOf('!guest')) != -1) {
        register = true;
        elements[4] = elements[4].replace('\\n!guest', '');
        elements[4] = elements[4].replace('!guest\\n', '');
        elements[4] = elements[4].replace('\\t!guest', '');
        elements[4] = elements[4].replace('!guest\\t', '');
        elements[4] = elements[4].replace('!guest', '');
    }

    // 清除內容
    listForm.reset();
    for (var j=t.rows.length-2; j>=2; j--)
    {
        elems = t.rows[j].getElementsByTagName('input');
        for (var i=elems.length-1; i>=0; i--)
            if (elems[i].type == 'checkbox')
                elems[i].checked = false;
            else if (elems[i].type == 'text')
                elems[i].value == '';
        elems = t.rows[j].getElementsByTagName('textarea');
        for (var i=elems.length-1; i>=0; i--)
            elems[i].value == '';
    }

    listForm.acl_id.value = acl_id;

    // 設定caption與permission
    for(var i=0; i<inputNodes.length; i++){
        switch(inputNodes[i].type){
            // 測驗對象的多語系數值
            case 'text':
                if (inputNodes[i].name.search(/^acl_caption\[(.+)\]$/) === 0){
                    inputNodes[i].value = captions[k++];
                }
                break;
            case 'checkbox':
                if (inputNodes[i].name == 'permission[]' && (permission & parseInt(inputNodes[i].value))) inputNodes[i].checked = true;
                else if (inputNodes[i].name == 'role[]'  && inputNodes[i].value != '!guest' && (role & parseInt(inputNodes[i].value))) inputNodes[i].checked = true;
                else if (inputNodes[i].name == 'role[]'  && inputNodes[i].value == '!guest') inputNodes[i].checked = register;
            break;
        }
    }

    if (elements[4].search(/^@[0-9]+\.[0-9]+/) == -1)
    {
        if ('{$QTI_which}' == 'homework')
            assignIndividual(t.rows[t.rows.length-2], 5);

        listForm.extra_member.value = replace_all(elements[4], '\\t', '\\n');
    }
    else
    {
        assignGroup(t.rows[2], 5);
        var re = /@([0-9]+)\.([0-9]+)/g, arr;
        var gs = document.getElementById('assignTeamPanel').getElementsByTagName('input');
        for (var i=0; i<gs.length; i++)
            if (gs[i].name == 'groups[]') gs[i].checked = false;

        while ((arr = re.exec(elements[4])) != null)
        {
            listForm.assignTeam.value = RegExp.$1;
            changeTeam('assignTeamID' + RegExp.$1);
            if ((aid = document.getElementById('assignTeamID' + RegExp.$1 + 'GroupID' + RegExp.$2)) != null) aid.checked = true;
        }
    }
    displayDialogWindow('acl_table_element');
}

    /**
     * 取代字串中所有符合搜尋的字串
     */
    function replace_all(str, find, replace) {
        while (str.indexOf(find) != -1)
            str = str.replace(find, replace);
        return str;
    }
/**
 * 將 acl_lists[display_idx] 這個功能的 ACL 列在網頁上
 *
 * @param   integer display_idx 哪個 function 索引值
 * @return void
 */
function generate_list(display_idx){
    var IH = '<table border="0" cellpadding="1" cellspacing="0" style="border-collapse: collapse" class="font01">';
    var elements, captions;
    if (typeof(acl_lists[display_idx]) != 'undefined') {
        for(var i=0; i<acl_lists[display_idx].length; i++){
            elements = acl_lists[display_idx][i].split('\b');
            captions = elements[1].split('\\t');
            IH += '<tr><td width="40" nowrap>' + elements[0] + '</td><td width="100" nowrap>' + captions[lang_charset] +
                  '</td><td nowrap><a href="javascript:;" onclick="acl_hidden_flags=true; init_modify_list(' +
                  display_idx + ',' + i + '); return false;" title="property"><img src="/theme/default/teach/icon_property.gif" align="absmiddle" border="0"></a>&nbsp;<a href="javascript:;" onclick="acl_hidden_flags=true; del_list(' +
                  display_idx + ',' + i + '); return false;" title="delete"  ><img src="/theme/default/teach/icon_delete.gif"   align="absmiddle" border="0"></a></td></tr>';
        }
    }
    IH += '</table>';
    document.getElementById('aclDisplayPanel_' + display_idx).innerHTML = IH;
}

/**
 * 隱藏對話框
 *
 * @param   string  dialog_id   欲隱藏的區塊 ID。若省略則預設是 ACL 對話框
 * @return void
 */
function hide_acl_dialog(dialog_id){
    var obj = null;
    if (typeof(dialog_id) == 'undefined')
        obj = document.getElementById('acl_table_element');
    else
        obj = document.getElementById(dialog_id);
    obj.style.display='none';

    // [lst] 隱藏不在本頁的下拉選單 (需要 dragLayer.js) (Begin)
    if (typeof(getHideObjList) == 'function') {
        dragObj = obj;
        getHideObjList();
        hideShowCovered();
    }
    // [lst] 隱藏不在本頁的下拉選單 (需要 dragLayer.js) (End)
}

function sel_account() {
/*
    var listForm = document.getElementById('acl_save_form');
    if (listForm == null) return;
    if (listForm.extra_member.value != '') members = listForm.extra_member.value.split(/\s+/).join(',');
    else members = '';
    window.open('/lib/sel_account.php?members=' + members, '', 'top=0,left=0,width=560,toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0');
*/
    window.open('/lib/sel_account.php', '', 'top=0,left=0,width=560,toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0');
}

/**
 * 切換為指派給群組
 *
 * @param   dom_element tr        個人設定 <tr> 所在列
 * @param   int            rowSpan 個人設定佔了幾個 row
 */
function assignGroup(tr, rowSpan)
{
    document.getElementById('acl_save_form').assignTarget.value = 'Group';
    var i = tr.rowIndex;
    var t = tr.parentNode.parentNode;
    for(var j=i; j<i+rowSpan; j++)
        t.rows[j].style.display = 'none';
    t.rows[i+rowSpan].style.display = '';
                
    return false;
}

/**
 * 切換為指派給個人
 *
 * @param   dom_element tr        群組設定 <tr> 所在列
 * @param   int            rowSpan 個人設定佔了幾個 row
 */
function assignIndividual(tr, rowSpan)
{
    document.getElementById('acl_save_form').assignTarget.value = 'Individual';
    var i = tr.rowIndex;
    var t = tr.parentNode.parentNode;
    tr.style.display = 'none';
    for(var j=i-1; j>=i-rowSpan; j--)
        t.rows[j].style.display = '';
    return false;
}

/**
 * 切換分組次所含的群組
 *
 * @param   string      id      分組次ID
 */
function changeTeam(id)
{
    var uls = document.getElementById('assignTeamPanel').getElementsByTagName('li');
    for(var i=0; i<uls.length; i++)
        if (uls[i].id.indexOf('assignTeamID') === 0)
        {
            uls[i].style.display = (uls[i].id == id) ? 'inline' : 'none';
        }
}
EOB;
        showXHTML_script('inline', $acl_scr);

      $ary = array(array(  defined('QTI_which') ? $MSG['acl_'.QTI_which][$sysSession->lang] :(is_null($acl)?$MSG['msg_creaACL'][$sysSession->lang]:$MSG['msg_modiACL'][$sysSession->lang])  , 'tabsSet', ''));
      showXHTML_tabFrame_B($ary, 1, 'acl_save_form', 'acl_table_element', 'action="access_acl1.php" method="POST" style="display: inline"', true);

        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; border-width: 2px;" class="bg04 box01"');

            // 語系 (Begin)
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            $arr_names = array( 'Big5'            =>    'acl_caption[Big5]',
                                'GB2312'        =>    'acl_caption[GB2312]',
                                'en'            =>    'acl_caption[en]',
                                'EUC-JP'        =>    'acl_caption[EUC-JP]',
                                'user_define'   =>    'acl_caption[user_define]'
                                );
            showXHTML_tr_B($col);
                showXHTML_td('align="right" valign="center"', $MSG['acl_title1'][$sysSession->lang]);
                showXHTML_td_B('colspan="2"');
                    $multi_lang = new Multi_lang(false, $captions, $col); // 多語系輸入框
//                    echo '<pre>';
//                    var_dump('$captions', $captions);
//                    echo '</pre>';
                    $multi_lang->show(true, $arr_names);
                    // 測驗對象多語系數值請看lib\acl_api.php->function init_modify_list(，數值是用JS塞回畫面，這邊數值正確也沒用
                showXHTML_td_E();
                showXHTML_td('align="right" valign="center"', $MSG['acl_title_help'][$sysSession->lang]);
            showXHTML_tr_E();

          showXHTML_tr_B('class="bg04 font01" style="display: none"');  // 暫時隱藏權限
            showXHTML_td('rowspan="1" width="80"', $MSG['acl_access1'][$sysSession->lang]);
            showXHTML_td_B('colspan="2" width="270"');
              showXHTML_input('checkbox', 'permission[]',   1, '', 'checked'); echo $MSG['acl_enable'][$sysSession->lang];
            showXHTML_td_E();
            showXHTML_td('rowspan="1" width="120"', $MSG['acl_access_help'][$sysSession->lang]);
          showXHTML_tr_E();
/*
          $perm_bitmap = 2;
          foreach(array('visible','readable','writable','modifiable','uploadable','removable','manageable','assignable') as $perm){
            showXHTML_tr_B($cls);
                showXHTML_td_B('colspan="2" width="270"');
                  showXHTML_input('checkbox', "permission[]", $perm_bitmap); echo $MSG[$perm][$sysSession->lang];
                showXHTML_td_E();
            showXHTML_tr_E();
            $perm_bitmap <<= 1;
            $cls = $cls == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"' ;
          }
*/
          // 不同環境下允許設定acl成員不同
          $role_subset = array();
          if ($topDir == 'teach')
            $hidden_roles = array('guest', 'senior', 'paterfamilias', 'superintendent', 'director', 'manager', 'administrator', 'root', 'class_instructor');
          else if ($topDir == 'academic') {
            $role_subset['register'] = '!guest';
            $hidden_roles = array('guest', 'senior', 'paterfamilias', 'superintendent', 'root');
          }
          else if ($topDir == 'direct')
              $hidden_roles = array('guest', 'senior', 'paterfamilias', 'superintendent', 'director', 'manager', 'administrator', 'root', 'instructor');
          else
            $hidden_roles = array('senior','paterfamilias','superintendent');

          $role_subset = array_merge($role_subset, $sysRoles); array_pop($role_subset);

          $isFirst = true;
          $cls = 'class="bg03 font01"';
          $rowSpan = count($role_subset) - count($hidden_roles);
          foreach($role_subset as $k => $v) {
            if (in_array($k, $hidden_roles)) continue;
            $cls = $cls == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
            // Chrome 考生名單列高不會平均分配，因此寫死指定列高，冠雄說ok
            showXHTML_tr_B($cls . " height='60'");
                if ($isFirst) showXHTML_td('rowspan="'.$rowSpan.'" width="80"', defined('QTI_which') ? ( ((QTI_which == 'homework' || QTI_which == 'peer') && hasGrouped()) ? ($MSG['acl_member_' . QTI_which][$sysSession->lang] . '<br>[<a href="javascript:;" onclick="return assignGroup(this.parentNode.parentNode, ' . $rowSpan . ');" class="cssAnchor">' . $MSG['assign to group'][$sysSession->lang] . '</a>]') : $MSG['acl_member_' . QTI_which][$sysSession->lang]) : $MSG['acl_member'][$sysSession->lang] );
                showXHTML_td_B();
                    showXHTML_input('checkbox', 'role[]', $v);
                    echo $MSG['bar_role_' . ($topDir == 'teach' ? $k . '_cour' : $k)][$sysSession->lang];
                showXHTML_td_E();
                if ($isFirst) {
                    showXHTML_td_B('rowspan="'.$rowSpan.'"');
                        showXHTML_input('button', '', $MSG['acl_account'][$sysSession->lang], '', 'onclick="sel_account();"');
                        echo '<br />';
                                                if ($isMobile === '1') {
                                                    showXHTML_input('textarea', 'extra_member', '', '', 'id="extra_member" rows="10" cols="20" readonly');
                                                } else {
                                                    showXHTML_input('textarea', 'extra_member', '', '', 'id="extra_member" rows="20" cols="20" readonly');
                                                }
                    showXHTML_td_E();
                    showXHTML_td('rowspan="'.$rowSpan.'" width="120"', defined('QTI_which') ? $MSG['acl_member_help_' . QTI_which][$sysSession->lang] : $MSG['acl_member_help'][$sysSession->lang]);
                }
            showXHTML_tr_E();
            $isFirst = false;
          }

          if (in_array(basename($_SERVER['PHP_SELF']), array('exam_create.php','exam_modify.php')) && (QTI_which == 'homework' || QTI_which == 'peer'))
          {
          showXHTML_tr_B($cls . ' style="display: none"');
            showXHTML_td('', $MSG['assigned groups'][$sysSession->lang] . '<br>[<a href="javascript:;" onclick="return assignIndividual(this.parentNode.parentNode, ' . $rowSpan . ');" class="cssAnchor">' . $MSG['assign to individual'][$sysSession->lang] . '</a>]');
            showXHTML_td_B('colspan="2"');

              $sqls = 'SELECT S.team_id, S.team_name, G.group_id, G.caption, G.captain ' .
                      'FROM WM_student_separate AS S ' .
                      'LEFT JOIN WM_student_group AS G ON S.course_id = G.course_id ' .
                      'AND S.team_id = G.team_id ' .
                      'WHERE S.course_id=' . $sysSession->course_id .
                      ' ORDER BY S.permute, S.team_id, G.permute, G.group_id';

              $keep = $ADODB_FETCH_MODE;
              $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
              $rs = $sysConn->Execute($sqls);
              $teams = array(); $groups = array();
              if ($rs)
              {
                while ($fields = $rs->FetchRow())
                {
                    if (empty($cur_team)) $cur_team = $fields[0];
                    $teams[$fields[0]] = htmlspecialchars(fetchTitle($fields[1]), ENT_NOQUOTES);
                                        
                                        // 是否有設定組長
                                        $captainNote = ' <span data-leader="N" style="color: red; font-weight: bold;">(' . $MSG['no_leader'][$sysSession->lang] . ')</span>';
                                        if (empty($fields[4]) === false) {
                                            $captainNote = '<span data-leader="Y"></span>';
                                        } 
                                        
                    if ($fields[2])
                        $groups[$fields[0]][$fields[2]] = htmlspecialchars(fetchTitle($fields[3]), ENT_NOQUOTES) . $captainNote;
                    else
                        $groups[$fields[0]] = array();
                }
                showXHTML_input('select', 'assignTeam', $teams, '', 'onchange="changeTeam(\'assignTeamID\' + this.value);"');
                echo '<ul class="font01" style="margin: 0;" id="assignTeamPanel">';
                foreach ($teams as $team_id => $team_title)
                {
                    echo '<li id="assignTeamID' . $team_id . '" style="display: ' . ($team_id == $cur_team ? 'inline' : 'none') . '"><ul style="margin-left: 1em">';
                    foreach ($groups[$team_id] as $group_id => $group_title)
                    {
                        echo '<li>';
                        showXHTML_input('checkbox', 'groups[]', $group_id, '', 'id="assignTeamID' . $team_id . 'GroupID' . $group_id . '" checked');
                        echo '<label for="assignTeamID', $team_id, 'GroupID', $group_id, '">', $group_title, '</label></li>';
                    }
                    echo '</ul></li>';
                }
                echo '</ul>';
              }
              $ADODB_FETCH_MODE = $keep;

            showXHTML_td_E();
            showXHTML_td('', $MSG['assign to groups tips'][$sysSession->lang]);
          showXHTML_tr_E();
          }

          $cls = $cls == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"' ;
          showXHTML_tr_B($cls);
            showXHTML_td_B('colspan="4" align="center"');
              showXHTML_input('button', '', $MSG['apply'][$sysSession->lang] , '', 'class="cssBtn" onclick="add_list(); generate_list(acl_index);"');
              showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="hide_acl_dialog();"');
              showXHTML_input('hidden', 'acl_id', '');
              showXHTML_input('hidden', 'assignTarget', 'Individual');
            showXHTML_td_E();
          showXHTML_tr_E();

        showXHTML_table_E();
      showXHTML_tabFrame_E();

    }

    /**
     * 由 課程/教材 與 單元 取得 acl_id 陣列
     *
     * @access  public
     * @param   integer $function_id    功能編號
     * @param   integer $dep_id         課程 ID 或 班級 ID
     * @param   integer $instance       衍生單元代號 (三合一編號或看板代號)
     * @return  array   傳回與此功能關聯的陣列
     */
    function aclGetAclIdByInstance($function_id, $dep_id, $instance=0){

        $GLOBALS['sysConn']->Execute('use ' . sysDBschool);
        $ret = $GLOBALS['sysConn']->GetCol('select acl_id from WM_acl_list where ' . sprintf('function_id=%d and unit_id=%d and instance=%d', $function_id, $dep_id, $instance));
        return $ret;
    }


    /**
     * 取得 ACL 中的成員
     *
     * @param    int        $acl_id        ACL 編號
     * @param    int        $dep_id        課程或班級編號，若不為零，則會展開 @群組，若 = 0 則不展開
     */
    function aclGetMembersByAcl($acl_id, $dep_id=0)
    {
        $GLOBALS['sysConn']->Execute('use ' . sysDBschool);
        $ret = $GLOBALS['sysConn']->GetCol('select member from WM_acl_member where acl_id=' . $acl_id);
        $result = array();
        if (is_array($ret) && count($ret))
        {
            foreach($ret as $man)
            {
                if ($dep_id != 0 && $man[0] === '#' && isset($GLOBALS['sysRoles'][substr($man, 1)]))
                {
                    if ($dep_id > 10000000)    // course
                    {
                        $people = $GLOBALS['sysConn']->GetCol('select username from WM_term_major where course_id=' . $dep_id . ' and role&' . intval($GLOBALS['sysRoles'][substr($man, 1)]));
                    }
                    else                    // class
                    {
                        $people = $GLOBALS['sysConn']->GetCol('select username from WM_class_member where class_id=' . $dep_id . ' and role&' . intval($GLOBALS['sysRoles'][substr($man, 1)]));
                    }
                    if (is_array($people) && count($people)) $result = array_merge($result, $people);
                }
                else if ($dep_id > 10000000 && $man[0] == '@') { // 群組作業
                    list($team_id, $group_id) = explode('.', substr($man, 1), 2);
                    if ($team_id && $group_id) {
                        $people = $GLOBALS['sysConn']->GetCol('select username from WM_student_div where course_id=' . $dep_id . ' and group_id = ' . $group_id . ' and team_id = ' . $team_id);
                        if (is_array($people) && count($people)) $result = array_merge($result, $people);
                    }
                }
                else
                    $result[] = $man;
            }
            $result = array_unique($result);
        }

        return $result;
    }

    /**
     * 取得某功能的關聯 ACL，成要顯示在 js 的陣列
     *
     * @param   integer $function_id    功能編號
     * @param   integer $dep_id         課程 ID 或 班級 ID
     * @param   integer $instance       衍生單元代號 (三合一編號或看板代號)
     * @return  array   傳回與此功能關聯的 js 陣列
     */
    function aclGetAclArrayByInstance($function_id, $dep_id, $instance=0){
        global $sysConn, $sysRoles;

        $ret = array();
        $rs = dbGetStMr('WM_acl_list', '*', sprintf('function_id=%u and unit_id=%u and instance=%u', $function_id, $dep_id, $instance), ADODB_FETCH_ASSOC);
        // die(sprintf('select * from WM_acl_list where function_id=%d and unit_id=%d and instance=%d', $function_id, $dep_id, $instance));
        if ($rs) while($fields = $rs->FetchRow()){
            $members = dbGetStMr('WM_acl_member', 'member', 'acl_id=' . $fields['acl_id'], ADODB_FETCH_ASSOC);
            if ($members){
                $role = 0;
                $extra_member = '';
                while($member = $members->FetchRow()){
                    if (strpos($member['member'], '#') === 0){
                        $role |= $sysRoles[substr($member['member'], 1)];
                    }
                    else{
                        $extra_member .= $member['member'] . '\\t';
                    }
                }
                $t = unserialize($fields['caption']);
//                echo '<pre>';
//                var_dump($t);
//                echo '</pre>';
                
//                $sortt = array(
//                    'Big5' => $t['Big5'],
//                    'GB2312' => $t['GB2312'],
//                    'en' => $t['en'],
//                    'EUC-JP' => $t['EUC-JP'],
//                    'user_define' => $t['user_define']
//                );
                
//                echo '<pre>';
//                var_dump($sortt);
//                echo '</pre>';

                if (!is_array($t)) $t = array($fields['caption']);

                $ret[] = sprintf('"%d\\b%s\\b%d\\b%d\\b%s"',
                                 $fields['acl_id'],
                                 addslashes(implode(chr(9), $t)),
                                 aclPermission2Bitmap($fields['permission']),
                                 $role,
                                 preg_replace('/\\\t$/', '', $extra_member)
                                );
                
//                echo '<pre>';
//                var_dump($ret);
//                echo '</pre>';
//                die();
            }
        }
        return $ret;
    }

    /**
     * 取得某問卷是否是開放型問卷
     *
     * @param   integer $function_id    功能編號
     * @param   integer $dep_id         課程 ID 或 班級 ID 或 學校 ID
     * @param   integer $instance       衍生單元代號 (三合一編號或看板代號)
     * @return  bool                    傳回是否
     */
    function aclCheckWhetherForGuestQuest($dep_id, $instance=0){
        return dbGetOne('WM_acl_list as L,WM_acl_member as M',
                        'count(*)',
                        'L.function_id=1800300200 ' .
                        'and L.unit_id=' . intval($dep_id) .
                        ' and L.instance=' . intval($instance) .
                        ' and L.acl_id=M.acl_id ' .
                        'and M.member="guest"');
    }

    /**
     * 取得某課程(班級、學校)哪些問卷是開放型問卷
     *
     * @param   integer $function_id    功能編號
     * @param   integer $dep_id         課程 ID 或 班級 ID 或 學校 ID
     * @return  array                   傳回問卷ID陣列
     */
    function aclGetForGuestQuest($dep_id){
        return dbGetCol('WM_qti_questionnaire_test as Q,WM_acl_list as L,WM_acl_member as M',
                        'Q.exam_id',
                        'Q.course_id=' . intval($dep_id) .
                        ' and L.function_id=1800300200 ' .
                        'and L.unit_id=Q.course_id ' .
                        'and L.instance=Q.exam_id ' .
                        'and L.acl_id=M.acl_id ' .
                        'and M.member="guest"');
    }

    /**
     * 驗證許可
     *
     * @access public
     * @param   integer $function_id    功能編號
     * @param   integer $require_perm   要求的權限
     * @param   integer $dep_id         課程 ID 或 班級 ID，若省略則取目前 session 值
     * @param   integer $instance       衍生單元。若省略表示功能本身
     * @param   string  $user           要判斷的帳號。若省略則判段自己
     * @return  bool    若 true 則具存取權；若 false 則不具
     */
    function aclVerifyPermission($function_id, $require_perm, $dep_id=NULL, $instance=0, $user=NULL){
        global $sysConn, $sysSession, $sysRoles, $MSG;

        list($null, $curr_scope, $null) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
        /* #55990 (B) Mooc 取得學習環境權限 By spring  */
        if ($curr_scope === 'mooc') {
            if (preg_match('/^\/mooc\/user\/exam_/', $_SERVER['PHP_SELF'])) {
                $curr_scope =  'teach';
            } else {
                $curr_scope =  'learn';
            }
        }
        /* #55990 (E) */
        else if (defined('XMLAPI') && XMLAPI && defined('QTI_env')) {
            $curr_scope = QTI_env;
        }

        if (is_null($dep_id))
        {
            $dep_id = ($curr_scope == 'direct') ? $sysSession->class_id : $sysSession->course_id;
        }
        if (is_null($user)) $user = $sysSession->username;

        // 檢查環境是否允許
        list($canAccessScpoe) = dbGetStSr('WM_acl_function', sprintf('(scope & %u)', array_sum(aclScope2Array($curr_scope))), 'function_id=' . intval($function_id), ADODB_FETCH_NUM);
        if (!$canAccessScpoe)
        {
            $GLOBALS['accessErrorMessage'] = $MSG['incorrect environment'][$sysSession->lang];
            return false;
        }

        // 檢查是否有 ACL
        $sqls = 'SELECT L.permission,M.member ' .
                'FROM WM_acl_list as L LEFT JOIN WM_acl_member as M ' .
                'ON L.acl_id=M.acl_id ' .
                sprintf('WHERE L.function_id=%u and unit_id=%u and instance=%u ', $function_id, $dep_id, $instance) ;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $rs = $sysConn->Execute($sqls);
        if ($rs)
        {
            if ($rs->RecordCount() == 0) // 如果並沒有設定 ACL 就交由程式自行檢驗
            {
                $GLOBALS['accessErrorMessage'] = $MSG['use default'][$sysSession->lang];
                return 'WM2';
            }

            $acls = array();
            while($fields = $rs->FetchRow())
            {
                if (aclPermission2Bitmap($fields['permission']) & $require_perm)    // 保留符合權限的 ACL
                {
                    if ($fields['member'] == $user)                                     // 直接指定某帳號
                    {
                        $GLOBALS['accessErrorMessage'] = $MSG['allow'][$sysSession->lang];
                        return true;
                    }
                    elseif(strpos($fields['member'], '#') === 0 || strpos($fields['member'], '!') === 0)  // 指定給特別身份
                    {
                        $acls[] = $fields['member'];
                    }
                    elseif(preg_match('/^@([0-9]+)\.([0-9]+)$/', $fields['member'], $regs)) // 指定給小組
                    {
                        $isMember = $sysConn->GetOne("select count(*) from WM_student_div where course_id=$dep_id and group_id={$regs[2]} and team_id={$regs[1]} and username='{$user}'");
                        if ($isMember)
                        {
                            $GLOBALS['accessErrorMessage'] = $MSG['allow'][$sysSession->lang];
                            return true;
                        }
                    }
                }
            }

            if (count($acls) == 0) // 如果沒有任何符合權限的 ACL 存在
            {
                $GLOBALS['accessErrorMessage'] = $MSG['deny'][$sysSession->lang];
                return false;
            }

            foreach($acls as $group)
            {
                $role = $sysRoles[substr($group,1)];
                switch($group)
                {
                    case '#guest'          :
                        if ($user == 'guest') return true;
                        break;
                    case '!guest'    :
                        if ($user != 'guest') return true;
                        break;
                    case '#senior'         :
                    case '#paterfamilias'  :
                    case '#superintendent' :
                    case '#auditor'        :
                    case '#student'        :
                    case '#assistant'      :
                    case '#instructor'     :
                    case '#class_instructor':
                    case '#teacher'        :
                    case '#director'       :
                        if (strlen($dep_id) > 5 ? aclCheckRole($user, $role, $dep_id) : aclCheckRole($user, $role)) return true;
                        break;
                    case '#manager'        :
                    case '#administrator'  :
                        if (strlen($dep_id) == 5 ? aclCheckRole($user, $role, $dep_id) : aclCheckRole($user, $role)) return true;
                        break;
                    case '#root'           :
                        if (aclCheckRole($user, $role)) return true;
                        break;
                    case '#all'            :
                        return true;
                        break;
                }
            }

            // user 不存在任何具權限的身份
            $GLOBALS['accessErrorMessage'] = $MSG['deny nobody'][$sysSession->lang];
            return false;
        }
        else
        {
            $GLOBALS['accessErrorMessage'] = $MSG['ACL query failure'][$sysSession->lang];
            return false;
        }

        // 檢查

        return false;
    }

    /**
    * 顯示權限不足之訊息與 log
    */
    function aclPermissionDeny(){
        global $MSG;
        die($MSG['deny'][$sysSession->lang]);
    }

    /**
     * 檢查身份
     *
     * 檢查某人是否有 10000001 這門課的 教師/助教 權限：
     * $isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['assistant'], 10000001);
     * 又
     * 如果 $id=0, 即只檢查此人是否為 $role 所示的身份，不管哪一學校、班、課
     * $isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['assistant']);
     *
     * @param  string  $username  帳號
     * @param  integer $cid       schoo_id 或 course_id 或 class_id (會依長度判斷為何)
     * @param  integer $role      身份 (定義在 db_initialize.php 之 $sysRoles) 可同時檢查多身份
     * @return bool               true=具有身份；false=不具身份或參數錯誤
     */
    function aclCheckRole($username, $role, $cid=0, $getLevel=false)
    {
        global $sysConn, $sysSession, $sysRoles;

        $username = preg_replace('/[^\w.-]+/', '', $username);
        $role     = intval($role);
        $cid      = intval($cid);

        if (empty($username) || empty($role)) return false;

        $master_table = false;
        switch (strlen(strval($cid)))
        {
            case 5:     // 學校管理者
                $sqls = 'select ' . ($getLevel ? 'M.level' : 'count(*)') . ' from WM_manager as M inner join WM_school as C on M.school_id=C.school_id and C.school_host not like "[delete]%" where M.username="' . $username . '" and M.school_id=' . $cid . ' and M.level&' . $role;
                $master_table = true;
                break;
            case 7:     // 班級
                $sqls = 'select ' . ($getLevel ? 'M.role' : 'count(*)') . ' from WM_class_member as M inner join WM_class_main as C on M.class_id=C.class_id and C.status != 9 where M.class_id=' . $cid . ' and M.username="' . $username . '" and M.role&' . $role;
                break;
            case 8:     // 課程
                $sqls = 'select ' . ($getLevel ? 'M.role' : 'count(*)') . ' from WM_term_major as M inner join WM_term_course as C on M.course_id=C.course_id and C.status != 9 where M.username="' . $username . '" and M.course_id=' . $cid . ' and M.role&' . $role;
                break;
            default:    // 只判斷身份
                if ($role & $sysRoles['student'])
                {
                    if ($role & ($sysRoles['director'] | $sysRoles['superintendent'] | $sysRoles['paterfamilias'] | $sysRoles['class_instructor']))
                    {
                        $sqls = 'select count(*) from WM_class_member as M inner join WM_class_main as C on M.class_id=C.class_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role;
                    }
                    else
                    {
                        $sqls = 'select count(*) from WM_term_major as M inner join WM_term_course as C on M.course_id=C.course_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role;
                    }
                }
                elseif ($role & $sysRoles['assistant'])
                {
                    if ($role & ($sysRoles['director'] | $sysRoles['superintendent'] | $sysRoles['paterfamilias']))
                    {
                        $sqls = 'select sum(a.cnt) from (select count(*) cnt from WM_class_member as M inner join WM_class_main as C on M.class_id=C.class_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role.') a';
                        //$sqls = 'select sum(a.cnt) from (select count(*) cnt from WM_class_member as M inner join WM_class_main as C on M.class_id=C.class_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role.
                        //' union all ' .
                        //'select count(*) cnt from WM_term_major as M inner join WM_term_course as C on M.course_id=C.course_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role . ') a';
                    }
                    else
                    {
                        $sqls = 'select count(*) from WM_term_major as M inner join WM_term_course as C on M.course_id=C.course_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role;
                    }
                }
                elseif ($role & ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['auditor']))
                {
                    $sqls = 'select count(*) from WM_term_major as M inner join WM_term_course as C on M.course_id=C.course_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role;
                }
                elseif ($role & ($sysRoles['director'] | $sysRoles['superintendent'] | $sysRoles['paterfamilias'] | $sysRoles['student'] |$sysRoles['class_instructor']))
                {
                    if ($role & $sysRoles['class_instructor'])  $role = $role ^ $sysRoles['class_instructor'] | $sysRoles['assistant'];
                    $sqls = 'select count(*) from WM_class_member as M inner join WM_class_main as C on M.class_id=C.class_id and C.status != 9 where M.username="' . $username . '" and M.role&' . $role;
                }
                elseif ($role & ($sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager']))
                {
                    $sqls = 'select count(*) from WM_manager as M inner join WM_school as C on M.school_id=C.school_id and C.school_host not like "[delete]%" where M.username="' . $username . '" and M.level&' . $role;
                    $master_table = true;
                }
                else
                    return false;
                break;
        }
        if ($master_table) chkSchoolId('WM_manager'); else chkSchoolId('WM_term_major');
        return $sysConn->GetOne($sqls);
    }

    /**
        取得acl_function caption
    */
    function getACLFunctionCaption($id)
    {
        if ($id == 0) return '';
        $rtns = $id;
        list($caption) = dbGetStSr('WM_acl_function','caption',"function_id='{$id}'", ADODB_FETCH_NUM);
        if (!empty($caption))
        {
            $rtns = iconv('BIG5','UTF-8',$caption);
        }
        return $rtns;
    }

    /**
     * 加密函數
     * 使用時機:目前使用於WM轉老師資料到LCMS認證用
     *
     * @param string $data 經過 json_encode的陣列，內可含帳密等基本資料，RD自行決定
     * @return string 加密後的字串
     */
    // $data: json_encode()後的字串
    function _3desEncode($data)
    {
        $DESKEY = 'LoGiN!@#$%SUNNET';
        
        // 加密
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $endata = base64_encode(mcrypt_encrypt(MCRYPT_3DES, $DESKEY, $data, MCRYPT_MODE_ECB, $iv));

        return trim($endata);
    }

    /**
     * 解密函數
     * 使用時機:目前使用於LCMS透過WM登入
     *
     * @param string $data 經過 _3desEncode加密後的字串
     * @return string 解密後的JSON字串
     */
    function _3desDecode($encode_string)
    {
        $DESKEY = 'LoGiN!@#$%SUNNET';

        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $dedata = mcrypt_decrypt(MCRYPT_3DES, $DESKEY, base64_decode($encode_string), MCRYPT_MODE_ECB, $iv);

        return trim($dedata);
    }
    
    // 增加答案LOG與EMAIL通知機制
    // 以追蹤QTI 答案XML格式錯誤的可能原因
    // 只記錄讀出後PARSE失敗的
    function log_qti_answer($QTI_which, $sid, $cid, $examId, $username, $xmlstr, $escapeXmlstr) {
        global $sysConn;
        $where = sprintf('exam_id=%d and examinee="%s" and time_id=1', $examId, $username);
        
        $ErrorXmlstr = $sysConn->Getone('select content from WM_qti_' . $QTI_which . '_result where ' . $where);
//        $ErrorXmlstr .= '123';
    //    echo '<pre>';
    //    var_dump($ErrorXmlstr);
    //    var_dump(htmlentities($ErrorXmlstr));
    //    echo '</pre>';
        $dom = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $ErrorXmlstr));
    //    echo '<pre>';
    //    var_dump($dom);
    //    echo '</pre>';
        // 若PARSE失敗
        if ($dom === null) {
            
            // 寫入LOG
            $logPath = sysDocumentRoot . "/base/{$sid}/system/log/qti/A/";
            /* 檔名格式：課程編號 QTI類型 測驗編號 學生帳號 建立時間 */
            $fileName= $cid . '_' . $QTI_which . '_' . htmlspecialchars($examId) . '_' . $username . '_' . date('YmdHis', time()) . '.txt';
            if (!is_dir($logPath) && strlen($logPath)>0) {
                mkdir($logPath, 0777, true);
            }
            $fp = fopen($logPath . $fileName, "w");
    //        fputs($fp, $ErrorXmlstr);
            fputs($fp, 'original:' . PHP_EOL . $xmlstr . PHP_EOL . PHP_EOL . 'after $sysConn->qstr():' . PHP_EOL . $escapeXmlstr . PHP_EOL . PHP_EOL . 'database:' . PHP_EOL . $ErrorXmlstr);
            fclose($fp);
            
            // 發信通知
            global $sysSession;
            list($school_name, $school_mail) = dbGetStSr('WM_school', 'school_name,school_mail', "school_id={$sysSession->school_id} and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);
            if (empty($school_mail)){
                $school_mail =     'webmaster@'. $_SERVER['HTTP_HOST'];
            }
            
            require_once(sysDocumentRoot . '/message/collect.php');// 發信通知用
            $mail       = buildMail('', 'QTI答案XML格式錯誤通知', 'Log檔案路徑：<a target="qti_answer_log" href="' . 'http://' . $_SERVER['HTTP_HOST'] . "/base/{$sid}/system/log/qti/A/" . $fileName . '">http://' . $_SERVER['HTTP_HOST'] . "/base/{$sid}/system/log/qti/A/" . $fileName . '</a>', 'html', '', '', '', '', false);
            $mail->from = mailEncFrom($school_name, $school_mail);
            $mail->to   = trim('ming@sun.net.tw, cch@sun.net.tw, puyuan@sun.net.tw');
            $mail->send();
        }
    }
?>

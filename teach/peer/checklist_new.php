<?php
   /**************************************************************************************************
    *                                                                                                 *
    *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
    *                                                                                                 *
    *		Programmer: cch
    *       SA        : saly                                                                         *
    *		Creation  : 2014/5/21                                                                      *
    *		work for  : 新增評量表
    *		work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9                                          *
    *                                                                                                 *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/teacher_settutor.php');
    require_once(sysDocumentRoot . '/lang/peer_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '172100100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    $js = <<< BOF
    var saveMsg = "{$MSG['title06'][$sysSession->lang]}";
    var nameMsg_empty = "{$MSG['title12'][$sysSession->lang]}";
    var nameMsg_limit = "{$MSG['checklist_error_lengthlimit'][$sysSession->lang]}";
    var levelMsg_empty = "{$MSG['title01'][$sysSession->lang]}";
    var levelMsg_morethanprev = "{$MSG['level_error_morethanprevious'][$sysSession->lang]}";
    var levelMsg_limit = "{$MSG['title05'][$sysSession->lang]}";
    var levelMsg_integer = "{$MSG['title08'][$sysSession->lang]}";
    var highScoreMsg_limit = "{$MSG['title04'][$sysSession->lang]}";
    var lNameMsg_empty = "{$MSG['need_level_name'][$sysSession->lang]}";
    var lNameMsg_limit = "{$MSG['levelname_error_lengthlimit'][$sysSession->lang]}";
    var pNameMsg_empty = "{$MSG['title02'][$sysSession->lang]}";
    var pNameMsg_limit = "{$MSG['pointname_error_lengthlimit'][$sysSession->lang]}";
    var pNoteMsg_empty = "{$MSG['title03'][$sysSession->lang]}";
BOF;
    // 開始呈現 HTML
    showXHTML_head_B($MSG['add_checklist'][$sysSession->lang], '8');
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn_mooc/peer.css");
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', "/theme/default/bootstrap/js/bootstrap-tooltip.js");
    showXHTML_script('inline', $js);
    showXHTML_script('include', '/teach/peer/checklist.js');
    showXHTML_head_E();
    showXHTML_body_B();

    echo
    '<div style="width: 1100px; margin: auto auto;">
        <ul class="bar" id="peer-page-title">
            <li class="left">
                <strong><span>' . $MSG['add_checklist'][$sysSession->lang] . '</span></strong>
            </li>
        </ul>
        <div class="navbar-form"></div>
        <div class="box box-padding-t-1 box-padding-lr-3 box-padding-b-3">
            <table border="0" width="1014" cellspacing="0" cellpadding="0"  id="ListTable" class="margin-top-15">
                <tr>
                    <td valign="top" id="CGroup" >
                        <form method="post" action="checklist_save.php" style="display:inline;" onsubmit="return checkData()" name="actForm" id="actForm">';
                                // 評量表設定
                                echo '<div class="margin-bottom-15">
                                    <div>
                                        <div class="rating-require strong-note">' . $MSG['required'][$sysSession->lang] . '</div>
                                    </div>
                                    <div class="rating bottom-radius bkcolor-white" style="position: relative;">
                                        <div class="rating-text">
                                            <div class="div-border">
                                                <div class="title top-radius bkcolor-orange">' . '評量表設定' . '</div>
                                                <div>
                                                    <table class="rating-table">
                                                        <tbody>'.
                                                            // 名稱
                                                            '<tr>
                                                                <th><span class="strong-note">*</span>' . $MSG['exam_name'][$sysSession->lang] . '</th>
                                                                <td>
                                                                    <div style="width:95%; padding:5px 0;" class="left">
                                                                        <input type="text" name="checklist_name" id="checklist_name" value="' . trim(stripslashes($_POST['checklist_name'])).'" style="size:45; width:220;  font-size: 14px; height:28px;">
                                                                    </div>
                                                                </td>
                                                            </tr>'.
                                                            // 評量表
                                                            '<tr style="vertical-align: top;">
                                                                <th><span class="strong-note">*</span>' . '評量表' . '</th>
                                                                <td style="text-align:center;">
                                                                    <div class="all-radius bkcolor-palegray" style="width:95%; padding:5px;">
                                                                        <div class="score-text">
                                                                            <div>
                                                                                <table class="point-note-table">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th style="vertical-align: middle; height:28px; line-height: 1.6em;"><div style="height: 100%; padding:4px 0;">&nbsp;</div></th>';
                                                                                            // 取預設級距標題
                                                                                            $sql = 'select value, value_name from WM_div_master where type_id = \'eva_level\' and lang_code = \'' . $sysSession->lang . '\' order by show_order';
                                                                                            $rsLevel = $sysConn->Execute($sql);
                                                                                            // 取預設級距的預設值
                                                                                            $sql = 'select value_name from WM_div_master where type_id = \'eva_level_value\' and lang_code = \'' . $sysSession->lang . '\' order by show_order';
                                                                                            $rsLevelValue = $sysConn->Execute($sql);
                                                                                            $level = array();

                                                                                            if ($rsLevel->RecordCount() > 0) {
                                                                                                $level_i = 0;
                                                                                                while ($rs1 = $rsLevel->FetchRow()){
                                                                                                    $levelValue= (trim(stripslashes($_POST['level']))) ? trim(stripslashes($_POST['level'])) : $rsLevelValue->fields['value_name'];
                                                                                                    $levelNameValue = (trim(stripslashes($_POST['levelName']))) ? trim(stripslashes($_POST['levelName'])) : $rs1['value_name'];
                                                                                                    echo '<th style="vertical-align: middle; height:28px; line-height: 1.6em;">
                                                                                                        <div style="width: 100%; height: 100%;">
                                                                                                            <input type="text" name="levelName[]" id="levelName[]" value="'.$levelNameValue.'"  style="width:65%; height:28px;  font-size: 14px; font-weight:bold; vertical-align: text-top;">&nbsp;';
                                                                                                            if ($level_i === 0) {
                                                                                                                echo '<input type="text" name="level[]" id="level[]" value="'.$levelValue.'" style="width: 20%; height:28px;  font-size: 14px; font-weight:bold; vertical-align: text-top;" readonly onkeyup="return rtnInt(this,value)">';
                                                                                                                $level_i++;
                                                                                                            } else {
                                                                                                                echo '<input type="text" name="level[]" id="level[]" value="'.$levelValue.'" style="width: 20%; height:28px;  font-size: 14px; font-weight:bold; vertical-align: text-top;" onkeyup="return rtnInt(this,value)">';
                                                                                                            }
                                                                                                        echo '</div>
                                                                                                    </th>';
                                                                                                    $level[$rs1['value']] = $rs1['value_name'];
                                                                                                    $rsLevelValue->MoveNext();
                                                                                                }
                                                                                            }
                                                                                            // 取級距名稱
                                                                                            /* foreach ($level as $key => $val) {
                                                                                                echo '<th><div>'.$val.'</div></th>';
                                                                                            } */
                                                                                        echo '</tr>
                                                                                    </thead>
                                                                                    <tbody>';
                                                                                        // 取預設指標
                                                                                        $sql = 'select value, value_name from WM_div_master where type_id = \'eva_point\'
                                                                                            and lang_code = \'' . $sysSession->lang . '\' order by show_order';
                                                                                        $rsPoint = $sysConn->Execute($sql);

                                                                                        if ($rsPoint->RecordCount() > 0) {
                                                                                            // 取級距X指標的說明
                                                                                            $sql = 'select value, value_name from WM_div_master where type_id = \'eva_point_note\'
                                                                                                and lang_code = \'' . $sysSession->lang . '\' order by show_order';
                                                                                            $rsPointNote = $sysConn->Execute($sql);

                                                                                            $pointNote = array();
                                                                                            if ($rsPointNote->RecordCount() > 0) {
                                                                                                while ($rs2 = $rsPointNote->FetchRow()){
                                                                                                    $pointNote[$rs2['value']] = $rs2['value_name'];
                                                                                                }
                                                                                            }
                                                                                            // 取級距內容
                                                                                            $i = 1;
                                                                                            while ($rs1 = $rsPoint->FetchRow()) {
                                                                                                echo '<tr>';
                                                                                                    // 名稱
                                                                                                    $pointNameValue = (trim(stripslashes($_POST['point_name']))) ? trim(stripslashes($_POST['point_name'])) : $rs1['value_name'];
                                                                                                    echo '<th class="span2" style="padding:0.5em 0.2em; height: auto;">
                                                                                                        <textarea name="point_name[]" id="point_name[]" value="" style="width: 90%; height: 50px; font-size: 16px; font-weight:bold;">'. $pointNameValue . '</textarea>
                                                                                                    </th>';
                                                                                                    // 指標X級距的說明
                                                                                                    foreach ($level as $key => $val) {
                                                                                                        $pointNoteValue = (trim(stripslashes($_POST['point_note_' . $rs1['value'] . '_' . $key]))) ? trim(stripslashes($_POST['username'])) : $pointNote[$rs1['value'] . '-' . $key];
                                                                                                        echo '<td style="padding:0.5em 0.2em;  height: auto;">
                                                                                                            <textarea name="point_note_' . $rs1['value'] . '_' . $key .'" id="point_note_' . $rs1['value'] . '_' . $key . '" data-id="' . $rs1['value'] . '_' . $key . '" style="width: 90%; height: 50px; font-size: 16px;">'. $pointNoteValue .'</textarea>
                                                                                                        </td>';
                                                                                                    }
                                                                                                echo '</tr>';
                                                                                                $i++;
                                                                                            }
                                                                                        }
                                                                                    echo '</tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <div class="actions">';
                                                        $ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
                                                        echo '<input type="hidden", name="ticket" id="ticket" value="'.$ticket.'">
                                                        <input type="hidden" name="enable" id="enable" value="1">
                                                        <button type="button" id="btn_submit" class="btn btn-warning" onclick="save();">'. $MSG['store'][$sysSession->lang] . '</button>
                                                        <button type="button" class="btn" onclick="tempSave();">'. $MSG['th_disable'][$sysSession->lang] . '</button>
                                                        <button type="button" class="btn" onclick="location.replace(\'checklist_list.php\');">'. $MSG['reset'][$sysSession->lang] . '</button>
                                                    </div>
                                                    <div class="clear-both"></div>
                                                    <div class="margin-bottom-15">&nbsp;</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="margin-bottom--15"></div>
                                    </div>
                                </div>
                        </form>';
                    echo '</td>
                </tr>
            </table>
        </div>
    </div>';
    showXHTML_body_E();
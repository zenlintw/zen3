<?php
   /**************************************************************************************************
    *                                                                                                 *
    *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
    *                                                                                                 *
    *		Programmer: cch
    *       SA        : saly                                                                         *
    *		Creation  : 2014/5/21                                                                      *
    *		work for  : 新增或修改評量表
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
    showXHTML_head_B($MSG['edit_checklist'][$sysSession->lang], '8');
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
        $data = array();
        // 修改模式：取相關資料
        $sql = 'select eva_id, caption, enable from WM_evaluation where eva_id = ' . intval($_POST['evaid']);
        $RS = $sysConn->Execute($sql);

        if ($RS->RecordCount() > 0) {
            $RS1 = $RS->FetchRow();
            $data['caption'] = $RS1['caption'];
            $data['enable']  = $RS1['enable'];
        }

        // 取級距
        $sql = 'select level_id, caption, score from WM_evaluation_level where eva_id = ' . intval($_POST['evaid']) . ' order by permute';
        $RS = $sysConn->Execute($sql);

        if ($RS->RecordCount() > 0) {
            while ($RS1 = $RS->FetchRow()){
                $data['level'][$RS1['level_id']]['caption'] = $RS1['caption'];
                $data['level'][$RS1['level_id']]['score'] = $RS1['score'];
            }
        }

        // 取已被用來評分的數量與作業名稱
        $sql = 'select distinct t.title, c.caption from WM_qti_peer_result_eva e, WM_qti_peer_test t, WM_term_course c
            where t.course_id = c.course_id and e.exam_id = t.exam_id and e.eva_id = \'' . intval($_POST['evaid']) . '\'
            order by c.caption, t.title';
        $RS = $sysConn->Execute($sql);
        $rating_count  = $RS ? $RS->RecordCount() : 0;
        $tmpRating = array();
        if ($rating_count > 0) {
            while ($RS1 = $RS->FetchRow()) {
                $caption = (strpos($RS1['caption'], 'a:') === 0) ?
                         unserialize($RS1['caption']):
                         array('Big5'		    => $RS1['caption'],
                                'GB2312'	    => $RS1['caption'],
                                'en'		    => $RS1['caption'],
                                'EUC-JP'	    => $RS1['caption'],
                                'user_define'	=> $RS1['caption']
                         );
                $title = (strpos($RS1['title'], 'a:') === 0) ?
                         unserialize($RS1['title']):
                         array('Big5'		    => $RS1['title'],
                                'GB2312'	    => $RS1['title'],
                                'en'		    => $RS1['title'],
                                'EUC-JP'	    => $RS1['title'],
                                'user_define'	=> $RS1['title']
                         );
                // 同門課放一起
                $tmpRating[$caption[$sysSession->lang]][] = $title[$sysSession->lang];
            }
            $rating = array();
            foreach ($tmpRating as $key => $val) {
                // 組成字串進入陣列
                $rating[] = '[' . $key . ']-' . implode('/', $val);
            }
        }

        // 取指標
        $sql = 'select point_id, caption from WM_evaluation_point where eva_id = ' . intval($_POST['evaid']) . ' order by permute';
        $RS = $sysConn->Execute($sql);

        if ($RS->RecordCount() > 0) {
            while ($RS1 = $RS->FetchRow()){
                $data['point'][$RS1['point_id']]['caption'] = $RS1['caption'];
            }
        }

        // 取指標X級距
        $sql = 'select point_id, level_id, note from WM_evaluation_point_note where point_id in (select point_id from WM_evaluation_point where eva_id = ' . intval($_POST['evaid']) . ') order by point_id, level_id';
        $RS = $sysConn->Execute($sql);

        if ($RS->RecordCount() > 0) {
            while ($RS1 = $RS->FetchRow()){
                $data['point_note'][$RS1['point_id']][$RS1['level_id']] = $RS1['note'];
            }
        }

        echo
        '<div style="width: 1100px; margin: auto auto;">
            <ul class="bar" id="peer-page-title">
                <li class="left">
                    <strong><span>' . $MSG['edit_checklist'][$sysSession->lang] . '</span></strong>
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
                                                                        <div style="width:95%; padding:5px; 0" class="left">
                                                                            <input type="text" name="checklist_name" id="checklist_name" value="' .trim(stripslashes($data['caption'])).'" style="size:45; width:220; font-size: 14px; height:28px;" class="cssInput">
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
                                                                                            <tr>';
                                                                                                // 取級距名稱、級距
                                                                                                $level = array();
                                                                                                if (count($data['level']) >= 1) {
                                                                                                    echo '<th style="vertical-align: middle; height:28px; line-height: 1.6em;"><div style="height: 100%; padding:4px 0;">&nbsp;</div></th>';
                                                                                                    $level_i = 0;
                                                                                                    foreach ($data['level'] as $key => $val) {
                                                                                                        $levelValue= (trim(stripslashes($_POST['level']))) ? trim(stripslashes($_POST['level'])) : $val['score'];
                                                                                                        $levelNameValue = (trim(stripslashes($_POST['levelName']))) ? trim(stripslashes($_POST['levelName'])) : $val['caption'];
                                                                                                        echo '<th style="vertical-align: middle; height:28px; line-height: 1.6em;">
                                                                                                            <div style="width: 100%; height: 100%; padding:4px 0 4px 5px; text-align: left;">
                                                                                                                <input type="text" name="levelName[' . $key . ']" id="levelName[' . $key . ']" value="'.$levelNameValue.'"  style="width:65%; height:28px;  font-size: 14px; font-weight:bold;">&nbsp;';
                                                                                                                if ($rating_count >= 1) {
                                                                                                                    echo $levelValue . '<input type="hidden" name="level[' . $key . ']" id="level[' . $key . ']" value="' . $levelValue . '">';
                                                                                                                } else if ($level_i === 0) {
                                                                                                                    echo $levelValue . '<input type="hidden" name="level[' . $key . ']" id="level[' . $key . ']" value="' . $levelValue . '">';
                                                                                                                    $level_i++;
                                                                                                                } else {
                                                                                                                    echo '<input type="text" name="level[' . $key . ']" id="level[' . $key . ']" value="'.$levelValue.'" style="width: 20%; height:28px;  font-size: 14px; font-weight:bold;" onkeyup="return rtnInt(this,value)">';
                                                                                                                }
                                                                                                            echo '</div>
                                                                                                        </th>';
                                                                                                        $level[$key] = $val['caption'];
                                                                                                    }
                                                                                                } else {
                                                                                                        echo $MSG['redo'][$sysSession->lang];
                                                                                                }
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
                                                                                                if ($data['point'] >= 1) {
                                                                                                    foreach ($data['point'] as $key => $val) {
                                                                                                        echo '<tr>';
                                                                                                            // 名稱
                                                                                                            $pointNameValue =  (trim(stripslashes($_POST['point_name']))) ? trim(stripslashes($_POST['point_name'])) : $val['caption'];
                                                                                                            echo '<th class="span2" style="padding:0.5em 0.2em; height: auto;">
                                                                                                                <textarea name="point_name[' . $key . ']" id="point_name[' . $key . ']"  data-id="' . $key . '" value="" style="width: 90%; height: 50px; font-size: 16px; font-weight:bold;">'. $pointNameValue . '</textarea>
                                                                                                            </th>';
                                                                                                            // 指標X級距的說明
                                                                                                            foreach ($data['level'] as $kkey => $vval) {
                                                                                                                $pointNoteValue = (trim(stripslashes($_POST['point_note_' . $key . '_' . $kkey]))) ? trim(stripslashes($_POST['username'])) : $data['point_note'][$key][$kkey];
                                                                                                                echo '<td style="padding:0.5em 0.2em;  height: auto;">
                                                                                                                    <textarea name="point_note[' . $key . '_' . $kkey .']" id="point_note_' . $key . '_' . $kkey . '" data-id="' . $key . '_' . $kkey . '" style="width: 90%; height: 50px; font-size: 16px;">'. $pointNoteValue .'</textarea>
                                                                                                                </td>';
                                                                                                            }
                                                                                                        echo '</tr>';
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        echo '</tbody>
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>';
                                                                // 已有人使用評量表
                                                                if ($rating_count >= 1) {
                                                                    echo '<tr style="height:0;">
                                                                        <th></th>
                                                                        <td>';
                                                                            echo '<span>' . $MSG['title07'][$sysSession->lang] . '</span><br/>';
                                                                            echo '<span style="color: red;">' . implode(', ', $rating) . '</span><br/>';
                                                                            echo '<div style="height: 0.5em;">&nbsp;</div>
                                                                        </td>
                                                                    </tr>';
                                                                }
                                                            echo '</tbody>
                                                        </table>
                                                        <div class="actions">';
                                                            $ticket = md5($sysSession->ticket . 'Edit' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
                                                            echo '<input type="hidden", name="ticket" id="ticket" value="'.$ticket.'">
                                                            <input type="hidden" name="enable" id="enable" value="'.$data['enable'].'">
                                                            <input type="hidden" name="eva_id" id="eva_id" value="'.intval($_POST['evaid']).'">
                                                            <button type="button" id="btn_submit" class="btn btn-warning" onclick="save();">'. $MSG['store'][$sysSession->lang] . '</button>';
                                                            if ($data['enable'] === '0') {
                                                                echo '<button type="button" class="btn" onclick="tempSave();">'. $MSG['th_disable'][$sysSession->lang] . '</button>';
                                                            }
                                                            echo '<button type="button" class="btn" onclick="location.replace(\'checklist_list.php\');">'. $MSG['reset'][$sysSession->lang] . '</button>
                                                        </div>
                                                        <div class="clear-both"></div>
                                                        <div class="margin-bottom-15">&nbsp;</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="margin-bottom--15"></div>
                                        </div>
                                    </div>
                            </form>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';

    showXHTML_body_E();
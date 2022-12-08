<?php
   /**************************************************************************************************
    *                                                                                                 *
    *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                              *
    *                                                                                                 *
    *		Programmer: cch
    *       SA        : saly                                                                         *
    *		Creation  : 2014/5/21                                                                      *
    *		work for  : 新增、修改、刪除 評量表                                                     *
    *		work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9
    *                           *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/teacher_settutor.php');
    require_once(sysDocumentRoot . '/lang/peer_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/character_class.php');
    require_once(sysDocumentRoot . '/message/collect.php'); // 發信
    require_once(sysDocumentRoot . '/lib/Hongu.php');// 使用HONGU VALIDATOR

    $actType = '';
    $title   = '';
    $action  = '';

    $mask_role = ($sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher'] | $sysRoles['student'] | $sysRoles['auditor']|$sysRoles['senior']|$sysRoles['paterfamilias']);

   // 新增老師 教授 課程 資料
    $ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType = 'Create';
        $ticket2 = '';
        $action  = 'checklist_new.php';
        $title   =  $MSG['add_teacher'][$sysSession->lang];
    }

    // 修改 老師 教授 課程 資料
    $ticket = md5($sysSession->ticket . 'Edit' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType = 'Edit';
        $action  = 'checklist_modify.php';
        $ticket2 = $_POST['ticket2'];
        $title   = $MSG['title2'][$sysSession->lang];
    }

   // 刪除老師 教授 課程 資料
    $ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Delete' . $sysSession->username);
    if (trim($_POST['ticket']) == $ticket) {
        $actType = 'Delete';
        $ticket2 = '';
        $action  = 'checklist_list.php';
        $title   = $MSG['del_teacher'][$sysSession->lang];
    }

   if (empty($actType)) die($MSG['illege_access'][$sysSession->lang]);

   if ($actType == 'Create' || $actType == 'Edit')
      $sysSession->cur_func = '172100400';
   else
      $sysSession->cur_func = '172100500';
   $sysSession->restore();
   if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
   }

    $self_level = aclCheckRole($sysSession->username, ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']), $sysSession->course_id, true) &
                  ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
   if (($self_level = array_search($self_level, $sysRoles)) === false) die('no teacher permission.');

    // 判斷級距分數
    if ($actType == 'Create' || $actType == 'Edit') {
        $messages = _formLevelValidation();
        if (count($messages) >= 1) {
            $errMsg = array();
            for ($i = 0, $size = count($messages); $i < $size; $i++) {
                $errMsg[] = '<div>' . $MSG['no'][$sysSession->lang] . substr($messages[$i]['id'], 2, 1) . $MSG['range_note1'][$sysSession->lang] . $MSG['range_note2'][$sysSession->lang] . ': ' . $messages[$i]['message'] . '</div>';
            }

            showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
            showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");

            echo '<div class="container esn-container">
                <div class="panel block-center">
                    <form class="well form-horizontal message-pull-center" action="' . $action . '" method="post" name="form1">
                        <fieldset>
                            <div class="input block-center">
                                <div class="row">&nbsp;</div>
                                <div class="control-group">
                                    <div class="message">
                                        <div id="message">
                                            <div>' . implode($errMsg, '') . '</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">&nbsp;</div>
                                <div class="control-group">
                                    <div class="controls">
                                        <div class="lcms-left">
                                            <input type="hidden" value="' . $_POST['ticket'] . '" name="ticket">
                                            <input type="hidden" value="' . $_POST['eva_id'] . '" name="evaid">
                                            <a href="javascript:;" onclick="form1.submit();" class="btn btn-primary aNormal margin-right-10 btn-blue">'.$MSG['back'][$sysSession->lang].'</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>';

            die();
        }
    }

    /**
     * 表單級距驗證
     */
    function _formLevelValidation()
    {
        global $MSG, $sysSession;
        $hongu = new Hongu();
        $rule = new Hongu_Validate_Rule();

        $rules['level'] = array(
             $rule->MAKE_RULE('HalfNumber', null, "{$MSG['arabic_numerals'][$sysSession->lang]}"),
             $rule->MAKE_RULE('IntRangeEqualerThan', array('mis' => 1, 'max' => 100), $MSG['arabic_numerals_range'][$sysSession->lang] . '1 ~ 100'),
             $rule->MAKE_RULE('Required', null, $MSG['required_note'][$sysSession->lang]),
             $rule->MAKE_RULE('XssAttack', null, $MSG['xss_attacks'][$sysSession->lang])
         );
       $params = $_POST;
       $valid = $hongu->getValidator();

       return $valid->check($params, $rules);
    }

    $all_users   = $sysConn->GetCol('select username from WM_user_account');			// 本校所有帳號

    $a = array($sysRoles['teacher']    => "{$MSG['teacher'][$sysSession->lang]}",
               $sysRoles['instructor'] => "{$MSG['instructor'][$sysSession->lang]}",
               $sysRoles['assistant']  => "{$MSG['assistant'][$sysSession->lang]}"
               );

    $current_tas = $sysConn->GetCol("select username from WM_term_major where course_id={$sysSession->course_id} and role&" . ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']));
    $now = date('Y-m-d H:i:s');

    if ($actType == 'Create') {
        // 寫入評量表主表
        dbNew(
            'WM_evaluation',
            "caption,
             enable,
             creator,
             operator,
             create_time,
             upd_time",
            "'{$_POST['checklist_name']}',
             {$_POST['enable']},
             '{$sysSession->username}',
             '{$sysSession->username}',
             '{$now}',
             '{$now}'"
        );
        $sysConn->Affected_Rows();
        $instance = $sysConn->Insert_ID();

        // 寫入指標
        $pointId = array();
        foreach ($_POST['point_name'] as $key => $val) {
            $seq = $key + 1;
            $val = strip_tags($val); // 去除HTML TAG
            dbNew(
                'WM_evaluation_point',
                "eva_id,
                 caption,
                 permute",
                "'{$instance}',
                 '{$val}',
                 '{$seq}'"
            );
            $pointId[$seq] = $sysConn->Insert_ID();
        }

        // 寫入級距
        $levelId = array();
        foreach ($_POST['levelName'] as $key => $val) {
            $seq = $key + 1;
            dbNew(
                'WM_evaluation_level',
                "eva_id,
                 caption,
                 score,
                 permute",
                "'{$instance}',
                 '{$val}',
                 '{$_POST['level'][$key]}',
                 '{$seq}'"
            );
            $levelId[$seq] = $sysConn->Insert_ID();
        }

        // 寫入說明
        foreach ($pointId as $key => $val) {
            foreach ($levelId as $kkey => $vval) {
                $note = strip_tags($_POST['point_note_' . $key . '_' . $kkey]); // 去除HTML TAG
                dbNew(
                    'WM_evaluation_point_note',
                    "point_id,
                     level_id,
                     note",
                    "'{$val}',
                     '{$vval}',
                     '{$note}'"
                );
            }
        }
    } else if ($actType == 'Edit') {
        // 評量表主表
        dbSet('WM_evaluation',"caption = '{$_POST['checklist_name']}', enable = {$_POST['enable']}, operator = '{$sysSession->username}', upd_time = '{$now}'", "eva_id = {$_POST['eva_id']}");
        foreach ($_POST['levelName'] as $key => $val) {
            dbSet('WM_evaluation_level',"caption = '{$val}'", "eva_id = {$_POST['eva_id']} and level_id = {$key}");
        }
        // 級距
        foreach ($_POST['level'] as $key => $val) {
            dbSet('WM_evaluation_level',"score = '{$val}'", "eva_id = {$_POST['eva_id']} and level_id = {$key}");
        }
        // 指標
        foreach ($_POST['point_name'] as $key => $val) {
            $val = strip_tags($val); // 去除HTML TAG
            dbSet('WM_evaluation_point',"caption = '{$val}'", "eva_id = {$_POST['eva_id']} and point_id = {$key}");
        }
        // 級距X指標 說明
        foreach ($_POST['point_note'] as $key => $val) {
            $kkey = explode('_', $key);
            $val = strip_tags($val); // 去除HTML TAG
            dbSet('WM_evaluation_point_note',"note = '{$val}'", "level_id = {$kkey[1]} and point_id = {$kkey[0]}");
        }
    // 刪除
    } elseif ($actType == 'Delete') {
        if (count($_POST['ckEvaid']) >= 1) {
            foreach ($_POST['ckEvaid'] as $val) {
                // 發信通知有引用到此評量表的建立者與更新者
                $sql = 'select c.caption, t.title, t.creator, t.operator from WM_qti_peer_test t, WM_term_course c where t.course_id = c.course_id and t.assess_way = \'' . $val . '\'';
                $rsCreator = $sysConn->Execute($sql);

                if ($rsCreator->RecordCount() > 0) {

                    // 取評量表名稱
                    $sql = 'select caption, creator from WM_evaluation where eva_id = \'' . $val . '\'';
                    $rsEvaluation = $sysConn->Execute($sql);

                    $evaluation = array();
                    if ($rsEvaluation->RecordCount() > 0) {
                        while ($rs1 = $rsEvaluation->FetchRow()){
                            $evaluation = $rs1['caption'];
                            $user = getUserDetailData($rs1['creator']);
                            $eCreator = $user['realname'];
                        }
                    }

                    // 取組相關資訊
                    $homework = array();
                    while ($rs1 = $rsCreator->FetchRow()) {
                        // 取課程名稱
                        $caption = (strpos($rs1['caption'], 'a:') === 0) ?
                                 unserialize($rs1['caption']):
                                 array('Big5'		    => $rs1['caption'],
                                        'GB2312'	    => $rs1['caption'],
                                        'en'		    => $rs1['caption'],
                                        'EUC-JP'	    => $rs1['caption'],
                                        'user_define'	=> $rs1['caption']
                                 );
                        // 取作業名稱
                        $title = (strpos($rs1['title'], 'a:') === 0) ?
                                 unserialize($rs1['title']):
                                 array('Big5'		    => $rs1['title'],
                                        'GB2312'	    => $rs1['title'],
                                        'en'		    => $rs1['title'],
                                        'EUC-JP'	    => $rs1['title'],
                                        'user_define'	=> $rs1['title']
                                 );
                        $homework[$caption[$sysSession->lang] . '|' . $title[$sysSession->lang]] = array();
                        $homework[$caption[$sysSession->lang] . '|' . $title[$sysSession->lang]][] = $rs1['creator'];
                        $homework[$caption[$sysSession->lang] . '|' . $title[$sysSession->lang]][] = $rs1['operator'];
                    }

                    // 取收件者
                    $tmpCreator = array();
                    foreach ($homework as $homeworkVal) {
                        foreach ($homeworkVal as $vval) {
                            $tmpCreator[] = $vval;
                        }
                    }
                    $creator = array_unique($tmpCreator);

                    // 取收件者
                    foreach ($creator as $creatorVal) {
                        // 取信箱
                        $user = getUserDetailData($creatorVal);

                        // 組課程字串
                        $homeworks = '';
                        foreach ($homework as $key => $vval) {
                            if (in_array($creatorVal, $vval) === true) {
                                $name = explode('|', $key);
                                $homeworks .= $name[0] . ' - ' . $name[1] .'<br>';
                            }
                        }

                        // 設定主旨內文
                        $tmpBody = $MSG['checklist_del_mail'][$sysSession->lang];
                        $subject = '作業的評分方式異動通知';

                        $body = strtr($tmpBody,
                              array(
                                '%SCHOOL_NAME%' =>  $sysSession->school_name,
                                '%EVALUATION%'  =>  $evaluation,
                                '%ECREATOR%'    =>  $eCreator,
                                '%HOMEWORKS%'   =>  $homeworks,
                                )
                             );

                        list($school_name, $school_mail) = dbGetStSr(
                            'WM_school',
                            'school_name, school_mail',
                            "school_id = {$sysSession->school_id} and school_host = '{$_SERVER['HTTP_HOST']}'",
                            ADODB_FETCH_NUM
                        );

                        if (empty($school_mail)) {
                            $school_mail = 	'webmaster@'. $_SERVER['HTTP_HOST'];
                        }

                        // 每次進入都必須重新宣告一個新的 mail 類別
                        $mail = buildMail('', $subject, $body, 'html', '', '', '', '', false);
                        $mail->from = mailEncFrom($school_name, $school_mail);
                        $mail->body = $body;
                        $mail->to = trim($user['email']);
                        $mail->send();

                        // 將有引用到此評量表的同儕互評-評分方式，改為開放給分0
                        dbSet('WM_qti_peer_test',"assess_way = 0", "assess_way = {$val}");
                    }
                }

                // 取級距字串
                $sql = 'select level_id from WM_evaluation_level where eva_id = \'' . $val . '\'';
                $rsLevel = $sysConn->Execute($sql);

                $tmpLevel = array();
                if ($rsLevel->RecordCount() > 0) {
                    while ($rs1 = $rsLevel->FetchRow()){
                        $tmpLevel[] = "'" . $rs1['level_id'] . "'";
                    }
                }

                // 取指標字串
                $sql = 'select point_id from WM_evaluation_point where eva_id = \'' . $val . '\'';
                $rsPoint = $sysConn->Execute($sql);

                $tmpPoint = array();
                if ($rsPoint->RecordCount() > 0) {
                    while ($rs1 = $rsPoint->FetchRow()){
                        $tmpPoint[] = "'" . $rs1['point_id'] . "'";
                    }
                }
                $level = array();
                $level = implode(',', $tmpLevel);

                $point = array();
                $point = implode(',', $tmpPoint);

                // 執行刪除
                if (count($level) >= 1) {
                    dbDel('WM_evaluation_point_note', "level_id in ({$level})");
                }
                if (count($point) >= 1) {
                    dbDel('WM_evaluation_point_note', "point_id in ({$point})");
                }
                if ($_POST['eva_id'] !== '') {
                    dbDel('WM_evaluation_point', "eva_id = {$val}");
                    dbDel('WM_evaluation_level', "eva_id = {$val}");
                    dbDel('WM_evaluation', "eva_id = {$val}");
                }
            }
        }
    }

    header('Location: checklist_list.php');
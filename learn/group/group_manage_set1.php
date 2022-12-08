<?php
    /**************************************************************************************************
     *                                                                                                *
     *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
     *                                                                                                *
     *      Programmer: Wiseguy Liang                                                                 *
     *      Creation  : 2003/08/10                                                                    *
     *      work for  :                                                                               *
     *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '2000100300';
    $sysSession->restore();
    if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    if ($_POST['chief'])
    {
        $res = checkUsername($_POST['chief']);
        if ($res != 2)
        {
            die('Access Deny !');
        }
    }
    $_POST['group_id'] = intval($_POST['group_id']);
    $_POST['team_id']  = intval($_POST['team_id']);

    switch($_SERVER['argv'][0]){
        case '1':
            if (ereg('^[0-9]+$', $_POST['team_id'])){
                $tean_name = array('Big5'        => stripslashes($_POST['team_name_Big5']),
                                   'GB2312'      => stripslashes($_POST['team_name_GB2312']),
                                   'en'          => stripslashes($_POST['team_name_en']),
                                   'EUC-JP'      => stripslashes($_POST['team_name_jp']),
                                   'user_define' => stripslashes($_POST['team_name_ud'])
                                  );

                dbSet('WM_student_group',
                      "caption='" . addslashes(serialize($tean_name)) . "',captain='".$_POST['chief']."'",
                      "course_id={$sysSession->course_id} and group_id={$_POST['group_id']} and team_id={$_POST['team_id']}");
                if ($sysConn->ErrorNo() > 0) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
            }
        break;
        case '2':
            if (ereg('^[0-9]+$', $_POST['team_id'])){
                $forum_name = array('Big5'        => stripslashes($_POST['team_forum_Big5']),
                                    'GB2312'      => stripslashes($_POST['team_forum_GB2312']),
                                    'en'          => stripslashes($_POST['team_forum_en']),
                                    'EUC-JP'      => stripslashes($_POST['team_forum_jp']),
                                    'user_define' => stripslashes($_POST['team_forum_ud'])
                                  );
                $helps       = strip_scr($_POST['help']);
                $sw[]        = (trim($_POST['mailfollow']) == 'yes') ? 'mailfollow' : '';
                $switch      = implode(',' , $sw);
                $withattach  = (empty($_POST['withattach'])?'no':trim($_POST['withattach']));
                $sort        = trim($_POST['defsort']);
                $vpost_value = (intval($_POST['vpost1']) | intval($_POST['vpost2'])) & 3;

                // 代換學習路徑節點的 <title> (先取得原 title)
                $old_title = $sysConn->GetOne('select bname from WM_bbs_boards where board_id=' . $_POST['board_id']);
                
                dbSet('WM_bbs_boards',
                      "bname='" . addslashes(serialize($forum_name)) . "',title='$helps',switch='$switch',with_attach='$withattach',vpost=$vpost_value,default_order='$sort'",
                      "board_id={$_POST['board_id']}");
                if ($sysConn->ErrorNo() > 0) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
                
                // 代換學習路徑節點的 <title> begin
                if (($new_title = serialize($forum_name)) != $old_title)
                {
                    $manifest = new SyncImsmanifestTitle(); // 本類別定義於 db_initialize.php
                    $manifest->replaceTitleForImsmanifest(6, $_POST['board_id'], $manifest->convToNodeTitle($forum_name));
                    $manifest->restoreImsmanifest();
                }
                // 代換學習路徑節點的 <title> end

            }
        break;
        case '3':
            if (ereg('^[0-9]+$', $_POST['team_id'])){
                $chat_name = array('Big5'        => stripslashes($_POST['team_rname_Big5']),
                                   'GB2312'      => stripslashes($_POST['team_rname_gb']),
                                   'en'          => stripslashes($_POST['team_rname_en']),
                                   'EUC-JP'      => stripslashes($_POST['team_rname_jp']),
                                   'user_define' => stripslashes($_POST['team_rname_ud'])
                                  );
                $exitAct = trim($_POST['host_exit']);
                $jump = isset($_POST['host_change']) ? 'allow' : 'deny';
                
                // 代換學習路徑節點的 <title> (先取得原 title)
                $old_title = $sysConn->GetOne('select title from WM_chat_setting where rid="' . $_POST['chat_id'] . '"');
                
                dbSet('WM_chat_setting',"title='" . addslashes(serialize($chat_name)) . "',exit_action='$exitAct',jump='$jump'","rid='{$_POST['chat_id']}'");
                if ($sysConn->ErrorNo() > 0) die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
                
                // 代換學習路徑節點的 <title> begin
                if (($new_title = serialize($chat_name)) != $old_title)
                {
                    $manifest = new SyncImsmanifestTitle(); // 本類別定義於 db_initialize.php
                    $manifest->replaceTitleForImsmanifest(7, $_POST['chat_id'], $manifest->convToNodeTitle($chat_name));
                    $manifest->restoreImsmanifest();
                }
                // 代換學習路徑節點的 <title> end
            }
        break;
    }
    header('Location: group_list.php?tid=' . $_POST['team_id']);
?>

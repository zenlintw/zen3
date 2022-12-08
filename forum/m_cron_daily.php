<?php
   /**************************************************************************************************
    *
    *		Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
    *
    *		Programmer: cch
    *       SA        : saly
    *		Creation  : 2014/12/08
    *		work for  : 討論區每日排程
    *		work on   : Apache 1.3.41, MySQL 5.1.59 , PHP 4.4.9
    *
    **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    global $sysConn;

    echo '<pre>';

    // 先判斷 WM_bbs_push有沒有資料
    $rsPush = dbGetStMr(
        'WM_bbs_push',
        '`push`',
        sprintf("`type`='%s'", 'b'),
        ADODB_FETCH_ASSOC
    );
    echo '<div>WM_bbs_push 資料筆數:' . $rsPush->RecordCount() . '</div>';

    // 如果沒有進行大筆匯入
    if ($rsPush->RecordCount() === 0) {
        echo 'WM_bbs_push 大筆匯入';

        $sqls = 'REPLACE INTO WM_bbs_push (TYPE, site, board_id, node)
            SELECT \'b\', site, board_id, node
            FROM WM_bbs_posts
            WHERE board_id >= 1';
        $sysConn->Execute($sqls);

        $sqls = 'REPLACE INTO WM_bbs_push (TYPE, site, board_id, node, push)
            SELECT TYPE,
                   site,
                   board_id,
                   node,
                   count(score) push
            FROM WM_bbs_ranking
            WHERE board_id >=1
              AND TYPE = \'b\'
            GROUP BY TYPE,
                     site,
                     board_id,
                     node';
        $sysConn->Execute($sqls);

    // 如果有進行最近一個月的資料匯入
    } else {
        echo '<div>WM_bbs_push 最近一個月的資料匯入</div>';

        $sqls = 'REPLACE INTO WM_bbs_push (TYPE, site, board_id, node)
            SELECT \'b\', site, board_id, node
            FROM WM_bbs_posts
            WHERE board_id >= 1
              AND (UNIX_TIMESTAMP(NOW())- UNIX_TIMESTAMP(pt))/60/60/24 <= 30';
        $sysConn->Execute($sqls);

        $sqls = 'REPLACE INTO WM_bbs_push (type, site, board_id, node, push)
            SELECT type,
                   site,
                   board_id,
                   node,
                   count(score) push
            FROM WM_bbs_ranking
            WHERE board_id >=1
              AND TYPE = \'b\'
              AND (type, site, board_id, node) in
                (SELECT type, site, board_id, node from WM_bbs_ranking
                WHERE (UNIX_TIMESTAMP(NOW())- UNIX_TIMESTAMP(r_time))/60/60/24 <= 30)
            GROUP BY TYPE,
                     site,
                     board_id,
                     node';
        $sysConn->Execute($sqls);
    }

    $rsPush = dbGetStMr(
        'WM_bbs_push',
        '`push`',
        sprintf("`type`='%s'", 'b'),
        ADODB_FETCH_ASSOC
    );

    echo '<div>WM_bbs_push 資料筆數:' . $rsPush->RecordCount() . '</div>';

    echo '</pre>';
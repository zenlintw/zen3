<?php
    /**
     * 取得修課進度
     *
     * @param integer $cid: 課程代號
     * @param string $content: 課程學習路徑
     * @param string $user: 使用者帳號
     * @return integer 修課進度
     */
    function getProgress($cid, $content, $user)
    {
        if ($xmldoc = @domxml_open_mem($content)) {
            $ctx = xpath_new_context($xmldoc);

            // 儲存 resource 供 item 取得
            $resourcePath = "//manifest/resources/resource";
            $resourceNodes = $ctx->xpath_eval($resourcePath);

            foreach ($resourceNodes->nodeset as $value) {
                $allResourceNodes[$value->get_attribute("identifier")] = $value;
            }

            // NOTICE: mapping 時須注意，APP 與 PC 都需要子節點的已讀狀態，item 前統一都加兩個 /，故移除 descendant 判斷
            $nodes = $ctx->xpath_eval('//manifest/organizations/organization//item[@identifier and @identifierref]');

            $items = array();
            if (count($nodes->nodeset)) {
                foreach ($nodes->nodeset as $node) {
                    $href = $allResourceNodes[$node->get_attribute('identifierref')];
                    // 可用 + 不隱藏 + 有連結 才會計入
                    if (($node->get_attribute('disabled') != 'true' || $node->get_attribute('disabled') == '') &&
                        (($node->get_attribute('isvisible') === '')||(strtolower($node->get_attribute('isvisible')) === 'true')) &&
                        (($href !== '') && ($href !== 'about:blank'))) {
                        $items[] = $node->get_attribute('identifier');
                    }
                }
            } else {
                return array('progress' => 0, 'recordReading' => array());
            }
        } else {
            return array('progress' => 0, 'recordReading' => array());
        }

        $allNode = count($items);

        $table = 'WM_record_reading';
        $fields = 'activity_id';
        $where = 'course_id="' . $cid . '" and username="' . mysql_real_escape_string($user) . '" '.
            'and activity_id in("'.implode('","', $items).'") group by activity_id';
        $rs = dbGetStMr($table, $fields, $where);

        // 把閱讀過的節點記錄下來，在my-course-path-info.class.php裡面判斷已讀未讀
        $recordReading = array();
        if ($rs) {
            while ($row = $rs->FetchRow()) {
                $recordReading[] = $row['activity_id'];
            }
        }

        $readCount = $rs ? $rs->RowCount() : 0;
        if ($readCount && $allNode) {
            return array('progress' => round(($readCount / $allNode) * 100), 'recordReading' => $recordReading);
        } else {
            return array('progress' => 0, 'recordReading' => array());
        }
    }

    function getScoolCourseProgress($sid,$cid, $content, $user)
    {
        if ($xmldoc = @domxml_open_mem($content)) {
            $ctx = xpath_new_context($xmldoc);
            $nodes = $ctx->xpath_eval('//item[@identifier and @identifierref]');
            $items = array();
            if (count($nodes->nodeset)) {
                foreach ($nodes->nodeset as $node) {
                    $items[] = $node->get_attribute('identifier');
                }
            } else {
                return array('progress' => 0, 'recordReading' => array());
            }
        } else {
            return array('progress' => 0, 'recordReading' => array());
        }

        $allnode = count($items);

        $table = sysDBprefix.$sid.'.WM_record_reading';
        $fields = 'activity_id';
        $where = 'course_id="' . $cid . '" and username="' . $user . '" '.
            'and activity_id in("'.implode('","', $items).'") group by activity_id';
        $rs = dbGetStMr($table, $fields, $where);

        // 把閱讀過的節點記錄下來，在my-course-path-info.class.php裡面判斷已讀未讀
        $recordReading = array();
        if ($rs) {
            while ($row = $rs->FetchRow()) {
                $recordReading[] = $row['activity_id'];
            }
        }

        $readCount = $rs ? $rs->RowCount() : 0;
        if ($readCount && $allnode) {
            return array('progress' => round(($readCount / $allnode) * 100), 'recordReading' => $recordReading);
        } else {
            return array('progress' => 0, 'recordReading' => array());
        }
    }

    /**
     * 取得指定的課程處於那些平台群組中
     * @param integer $csid : 課程編號
     * @return array $res : 父群組列表，為一個陣列
     **/
    function getSchoolCourseParents($sid, $cid, $enc=FALSE) {
        global $sysSession,$sysConn;
        $gp = array();
        $csid = checkCourseID($cid);
        if ($csid === false) return $gp;
        $RS = dbGetStMr(''.sysDBname.'.CO_all_group', '`parent`', "`child`={$cid} AND school={$sid} ", ADODB_FETCH_ASSOC);
        if ($RS) {
            while (!$RS->EOF) {
                $pid = ($enc) ? sysEncode($RS->fields['parent']) : $RS->fields['parent'];
                $ary = dbGetStSr(sysDBname.'.CO_all_course', '*', "`course_id`={$RS->fields['parent']} AND school={$sid}", ADODB_FETCH_ASSOC);
                if (($ary['kind'] == 'group') && (intval($ary['status']) < 9)) {
                    $t = getCaption($ary['caption']);
                    $gp[$pid][] = $t[$sysSession->lang];
                    $child=$RS->fields['parent'];
                    while($row=dbGetStSr(''.sysDBname.'.CO_all_group', '`parent`', "`child`={$child} AND school={$sid} ", ADODB_FETCH_ASSOC)){
                        $ary = dbGetStSr(sysDBname.'.CO_all_course', '*', "`course_id`={$row['parent']} AND school={$sid}", ADODB_FETCH_ASSOC);
                        if (($ary['kind'] == 'group') && (intval($ary['status']) < 9)) {
                            $t = getCaption($ary['caption']);
                            $gp[$pid][] = $t[$sysSession->lang];
                        }
                        $child=$row['parent'];
                    }
                }
                $RS->MoveNext();
            }
        }
        return $gp;
    }
    
    /**
    * 取得分解後的資料
    * @param string  $val  : 取得分解後的資料，且是尚未 unserialize 的
    * @return array $data
    **/
   function get_course_data($val) 
   {
        $data = unserialize($val);

        if (is_array($data)) 
        {
            foreach ($data as $key => $val) 
            {
                $data[$key] = htmlspecialchars($val);
            }
        } 

        return $data;
   }

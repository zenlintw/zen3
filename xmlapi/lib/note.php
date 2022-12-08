<?php
    require_once(sysDocumentRoot . '/lang/app_note.php');

    /**
     * 取得所有筆記本與建置json結構
     *
     * @param string $xpath 要搜尋的節點路徑
     * @param string $xmlXpathContext 學習路徑的xpath_new_context
     * @param string $syncTime 要取哪一個時間過後的筆記
     * @param string $username 帳號
     * @param string $extra 需求來源
     * @return array notebook 的 json
     **/
    function getNotebook($xpath, $xmlXpathContext, $syncTime = 0, $username = '', $extra = '')
    {
        // 尋找"我的筆記本"的旗標
        $findRootNotebook = true;
        $notebooks = array();

        $trashNotebooks = getTrashNotebooks($xpath, $xmlXpathContext);

        for ($i = 0; $i < 2; $i++) {
            if ($findRootNotebook === true) {
                // 只搜尋"我的筆記本"這一階
                $xpath = $xpath . '[@id="sys_notebook"]';
                $findRootNotebook = false;
            } else {
                // 搜尋我的筆記本"底下"的所有筆記本
                $xpath = $xpath . '//folder';
            }

            $xpathResult = $xmlXpathContext->xpath_eval($xpath);
            if (!empty($xpathResult)) {
                $j = $i;
                foreach ($xpathResult->nodeset as $singleNotebook) {
                    if (in_array($singleNotebook->get_attribute('id'), $trashNotebooks)) {
                        // 如果是回收桶及其底下的筆記本皆不需要撈出來
                        continue;
                    }
                    // 重設參數
                    unset($thisNotebook);

                    if ($username !== '') {
                        // 取得筆記本裡面的筆記
                        $notes = getNotes($singleNotebook->get_attribute('id'), $syncTime, $username);

                        // 如果有筆記才要回傳
                        if (count($notes) > 0 || $extra === 'all') {
                            // 筆記本ID
                            $thisNotebook['folder_id'] = $singleNotebook->get_attribute('id');
                            // 筆記本名稱
                            $childXpath = $xpath . '[@id="' . $thisNotebook['folder_id'] . '"]';
                            $bookNamePath = $childXpath . '/title/big5/text()';
                            $thisNotebook['folder_title'] = htmlspecialchars_decode(getTitle($xmlXpathContext->xpath_eval($bookNamePath)));
                            // 筆記本底下的筆記
                            $thisNotebook['notes'] = $notes;
                            // 筆記數量
                            $thisNotebook['note_count'] = count($thisNotebook['notes']);

                            $notebooks[$j] = $thisNotebook;
                        }
                    } else {
                        $thisNotebook['folder_id'] = $singleNotebook->get_attribute('id');
                        // 筆記本名稱
                        $childXpath = $xpath . '[@id="' . $thisNotebook['folder_id'] . '"]';
                        $bookNamePath = $childXpath . '/title/big5/text()';
                        $thisNotebook['folder_title'] = htmlspecialchars_decode(getTitle($xmlXpathContext->xpath_eval($bookNamePath)));

                        $notebooks[$j] = $thisNotebook;
                    }
                    $j++;
                }
            }
        }

        return $notebooks;
    }
    /**
     * 取得節點的名稱
     *
     * @param string $node: 節點路徑
     * @param string $attribute: 預設content
     * @return string 節點名稱
     **/
    function getTitle($node, $attribute='content')
    {
        foreach ($node->nodeset as $content) {
            $title = $content->{$attribute};
        }
        return $title;
    }

    /**
     * 取得回收桶與其底下的筆記本id
     *
     * @param string $xpath 要搜尋的節點路徑
     * @param string $xmlXpathContext 學習路徑的xpath_new_context
     *
     * @return array 筆記本id
     **/
    function getTrashNotebooks ($xpath, $xmlXpathContext)
    {
        $trashNotebooks = array('sys_notebook_trash');

        $xpath = $xpath . '[@id="sys_notebook_trash"]//folder';;
        $xpathResult = $xmlXpathContext->xpath_eval($xpath);

        if (!empty($xpathResult)) {
            foreach ($xpathResult->nodeset as $singleNotebook) {
                $trashNotebooks[] = $singleNotebook->get_attribute('id');
            }
        }

        return $trashNotebooks;
    }

    /**
     * 取得筆記本底下的筆記
     *
     * @param string $folderId 筆記本ID
     * @param string $syncTime 要取哪一個時間過後的筆記
     * @param string $username 帳號
     * @return array 筆記
     **/
    function getNotes($folderId, $syncTime, $username)
    {
        global $MSG;
        $defaultDiffTime = 28800;
        $notes = array();
        // 處理前置夾檔路徑
        $attachPath = sprintf('%s/user/%1s/%1s/%s/', WM_SERVER_HOST, substr($username, 0, 1), substr($username, 1,1), $username);
        $mysqlUseFolderId = mysql_real_escape_string($folderId);
        $mysqlUseUsername = mysql_real_escape_string($username);

        $fields = '`msg_serial`, `subject`, `content`, `submit_time`, `receive_time`, `attachment`';
        $where = "`folder_id` = '{$mysqlUseFolderId}' AND
                  `receiver` = '{$mysqlUseUsername}' ";
        if ($syncTime !== 0) {
            $where = $where . 'AND (' . "`receive_time` > '{$syncTime}' OR `receive_time` IS NULL) ";
        }
        $where = $where . 'ORDER BY `receive_time` DESC';
        $rsNotes = dbGetStMr('WM_msg_message', $fields, $where);
        if ($rsNotes) {
            while (!$rsNotes->EOF) {
                // 重設參數
                unset($note);

                // 筆記ID
                $note['note_id'] = intval($rsNotes->fields['msg_serial']);
                // 筆記標題
                $note['note_title'] = $rsNotes->fields['subject'];
                // 筆記內容
                $note['note_content'] = $rsNotes->fields['content'];
                // 筆記時間
                if (is_null($rsNotes->fields['receive_time'])) {
                    $note_time = $rsNotes->fields['submit_time'];
                } else {
                    $note_time = $rsNotes->fields['receive_time'];
                }
                $note['note_time'] = datetimeToSeconds($note_time) - $defaultDiffTime;
                $note['leaf'] = true;

                if (!empty($rsNotes->fields['attachment'])) {
                    // 若有夾檔
                    $note['note_attachments'] = makeAttachments($rsNotes->fields['attachment'], $attachPath);
                } else {
                    // 沒有夾檔
                    $note['note_attachments'] = array();
                }

                $notes[] = $note;

                $rsNotes->MoveNext();
            }
        }
        return $notes;
    }

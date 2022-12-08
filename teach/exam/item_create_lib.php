<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/quota.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');

if (!function_exists('htmlspecialchars_decode')) {
    
    function htmlspecialchars_decode($string, $style = ENT_COMPAT)
    {
        $translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $style));
        if ($style === ENT_QUOTES) {
            $translation['&#039;'] = '\'';
        }
        return strtr($string, $translation);
    }
    
}

/**
 * 把欲插入 XML 節點的資料做 escape
 */
function escape_xml_content($str)
{
    return htmlspecialchars(mb_convert_encoding(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', trim(stripslashes($str))), 'UTF-8', 'UTF-8'));
}

class itemMaintain
{
        var
            $topDir   = '',
            $courseId = '',
            $isModify = false,
            $itemData = array(),
            $ident    = '',
            $attach   = array(),
            $xmlstr   = '',
            $langIdx  = 0,
            $_QTI_which= '';
    
    /**
     * 初始化
     */
    function itemMaintain()
    {
        global $sysSession;
        
        // 設定路徑
        if (!defined('QTI_env')) {
            list($foo, $this->topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
        } else {
            $this->topDir = QTI_env;
        }
        $this->courseId = ($this->topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
        $this->isModify = (strpos($_SERVER['PHP_SELF'], '/item_modify1.php') !== false);
    }
    
    /**
     * 檢查 ACL，並產生 ident
     *
     * @return boolean  true: 通過檢查, false: 未通過檢查
     */
    function checkACL($origin, $ident, $ticket)
    {
        global $sysSession;
        
        $funcid = '';
        $this->_QTI_which = (defined('XMLAPI') && XMLAPI) ? API_QTI_which : QTI_which;
        switch ($this->_QTI_which) {
            case 'exam':
                $funcid = '1600100';
                break;
            case 'homework':
                $funcid = '1700100';
                break;
            case 'questionnaire':
                $funcid = '1800100';
                break;
        }
        
        if ($this->isModify) {
            $sysSession->cur_func = $funcid . '300';
            
            // 判斷 ticket 是否正確 (開始)
            $newticket = md5($origin . $ident . sysTicketSeed . $this->courseId . $_COOKIE['idx']);
            if ($newticket != $ticket) {
                wmSysLog($sysSession->cur_func, $this->courseId, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
                return false;
            }
            // 判斷 ticket 是否正確 (結束)
            $this->ident = $ident;
        } else {
            $sysSession->cur_func = $funcid . '200';
            
            $t           = explode('00 ', microtime());
            $this->ident = sprintf('WM_ITEM1_%s_%u_%s_%06u', sysSiteUID, $this->courseId, $t[1], substr($t[0], 2));
        }
        
        $sysSession->restore();
        if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
            
        }
        
        return true;
    }
    
    function saveFiles()
    {
        global $sysSession;
        
        $savePath = '';
        // 先將附檔清空
        $this->attach = array();

        if ($this->topDir == 'academic') {
                $savePath = sprintf(
                        sysDocumentRoot . '/base/%05d/%s/Q/%s/',
                        $sysSession->school_id, $this->_QTI_which, $this->ident
                );
        } else {
                $savePath = sprintf(
                        sysDocumentRoot . '/base/%05d/course/%08d/%s/Q/%s/',
                        $sysSession->school_id, $sysSession->course_id, $this->_QTI_which, $this->ident
                );
        }
        
        // 處理刪除夾檔 begin
        if ($this->isModify) {
            list($at) = dbGetStSr('WM_qti_' . $this->_QTI_which . '_item', 'attach', "ident='{$this->ident}'", ADODB_FETCH_NUM);
            $this->attach = preg_match('/^a:[0-9]+:{s:/', $at) ? unserialize($at) : array();
            
            $items = array(
                'topic_files',
                'ans_files',
                'render_choice_files',
                'render1_choice_files',
                'render2_choice_files'
            );
            
            foreach ($items as $elem) {
                $element = $elem . '_rm';
                if (is_array($this->itemData[$element])) {
                    if (preg_match('/^render.?_choice_files$/', $elem)) {
                        $tmp = array();
                        foreach ($this->attach[$elem] as $k => $v) {
                            if (in_array($k, $this->itemData[$element])) {
                                @unlink($savePath . $v);
                                $tmp[] = '';
                            } else {
                                $tmp[$k] = $v;
                            }
                        }
                        $this->attach[$elem] = $tmp;
                        unset($tmp);
                    } else {
                        foreach ($this->itemData[$element] as $item) {
                            if (isset($this->attach[$elem][$item])) {
                                @unlink($savePath . $this->attach[$elem][$item]);
                                unset($this->attach[$elem][$item]);
                            }
                        }
                    }
                }
            }
        }
        // 處理刪除夾檔 end
        
        // 行動裝置，由於上傳的圖片和影片名稱固定，所以需要重新給予檔名
        // 取已經存在的檔名的最大號碼
        $isMobile = isMobileBrowser() ? '1' : '0';
        if ($isMobile === '1') {
            $movieNum = 1;
            $imageNum = 1;
            foreach ($this->attach as $v) {
                foreach ($v as $kk => $vv) {
                    if (preg_match("/^MOVIE\((\d+)\).MOV$/", $kk, $match)) {
                        if ($match[1] >= $movieNum) {
                            $movieNum = $match[1] + 1;
                        }
                    }
                    if (preg_match("/^IMAGE\((\d+)\).JPEG$/", $kk, $match)) {
                        if ($match[1] >= $imageNum) {
                            $imageNum = $match[1] + 1;
                        }
                    }
                }
            }
        }
        
        // 處理夾檔 (begin)
        if ($_FILES) {
            foreach ($_FILES as $file_item => $files) {
                if (!is_array($files['tmp_name'])) {
                    continue;
                }
                $newFiles = array();
                foreach ($files['tmp_name'] as $i => $source) {
                    $fault = true;
                    if (is_uploaded_file($source)) {
                        if (!is_dir($savePath)) {
                            exec("mkdir -p '$savePath'");
                        }
                        $virtualname = @tempnam($savePath, 'WM');
                        if ($virtualname !== false) {
                            @unlink($virtualname);
                            $virtualname .= strrchr($files['name'][$i], '.');
                            if (move_uploaded_file($source, $virtualname)) {
                                $fault = false;
                                // 行動裝置，影片與圖片重新給予檔名
                                if ($isMobile === '1') {
//                                    echo '<pre>';
//                                    var_dump($file_item);
//                                    var_dump(htmlspecialchars($files['name'][$i]));
//                                    var_dump(preg_match("/^image.jpeg$/", $files['name'][$i]));
//                                    var_dump($imageNum);
//                                    echo '</pre>';
                                    if (preg_match("/^trim.[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}.MOV$/", $files['name'][$i]) || preg_match("/^capturedvideo.MOV$/", $files['name'][$i])) {
                                        $files['name'][$i] = 'MOVIE(' . $movieNum . ').MOV'; 
                                        $movieNum++;
                                    }
                                    if (preg_match("/^image.jp[e]?g$/", $files['name'][$i])) {
                                        $files['name'][$i] = 'IMAGE(' . $imageNum .').JPEG'; 
                                        $imageNum++;
                                    }
                                }
                                if (preg_match('/^render.?_choice_files$/', $file_item)) {
                                    $newFiles[htmlspecialchars($files['name'][$i])] = basename($virtualname);
                                } else {
                                    $this->attach[$file_item][htmlspecialchars($files['name'][$i])] = basename($virtualname);
                                }
                            }
                        }
                    }
                    
                    if ($fault) {
                        $newFiles[$i] = '';
                    }
                }
                
                if (preg_match('/^render.?_choice_files$/', $file_item)) {
                    if (!isSet($this->attach[$file_item])) {
                        $this->attach[$file_item] = array();
                    }
                    reset($this->attach[$file_item]);
                    reset($newFiles);
                    $tmp = array();
                    for ($i = 0, $c = count($newFiles); $i < $c; $i++) {
                        if (current($newFiles) != '') {
                            $tmp[key($newFiles)] = current($newFiles);
                        } else if ($i < count($this->attach[$file_item])) {
                            $tmp[key($this->attach[$file_item])] = current($this->attach[$file_item]);
                        } else {
                            $tmp[$i] = '';
                        }
                        next($this->attach[$file_item]);
                        next($newFiles);
                    }
                    $this->attach[$file_item] = $tmp;
                    unset($tmp);
                }
            }
    } else if (count($this->itemData['files']) > 0) {
        // 處理 api 來的 base64 檔案
        foreach ($this->itemData['files'] as $file_item => $files) {
            $newFiles = array();
            foreach ($files as $i => $source) {
                $fault = true;
                $realFilename = trim($source['filename']);
                if ($realFilename !== '' && $source['base64'] !== '') {
                    if (!is_dir($savePath)) {
                        exec("mkdir -p '$savePath'");
                    }
                    $virtualName = @tempnam($savePath, 'WM');
                    if ($virtualName !== false) {
                        @unlink($virtualName);
                        $virtualName .= strrchr($realFilename, '.');
                        $base64Content = str_replace('data:image/jpeg;base64,', '', $source['base64']);
                        $base64DecodeContent = base64_decode($base64Content);

                        // 寫入圖檔
                        $fp = fopen($virtualName, 'w');
                        fputs($fp, $base64DecodeContent);
                        fclose($fp);
                        $fault = false;
                        if (preg_match('/^render.?_choice_files$/', $file_item)) {
                            $newFiles[htmlspecialchars($realFilename)] = basename($virtualName);
                        } else {
                            $this->attach[$file_item][htmlspecialchars($realFilename)] = basename($virtualName);
                        }
                    }
                }

                if ($fault === true) {
                    $newFiles[$i] = '';
                }
            }

            if (preg_match('/^render.?_choice_files$/', $file_item)) {
                if (!isSet($this->attach[$file_item])) {
                    $this->attach[$file_item] = array();
                }
                reset($this->attach[$file_item]);
                reset($newFiles);
                $tmp = array();
                for ($i = 0, $c = count($newFiles); $i < $c; $i++) {
                    if (current($newFiles) != '') {
                        $tmp[key($newFiles)] = current($newFiles);
                    } else if ($i < count($this->attach[$file_item])) {
                        $tmp[key($this->attach[$file_item])] = current($this->attach[$file_item]);
                    } else {
                        $tmp[$i] = '';
                    }
                    next($this->attach[$file_item]);
                    next($newFiles);
                }
                $this->attach[$file_item] = $tmp;
                unset($tmp);
            }
        }
    }
        if (empty($this->attach) && is_dir($savePath)) {
                @rmdir($savePath);
        }
        // 處理夾檔 (end)
        
        // 更新 quota 資訊
        $quotaUsed  = 0;
        $quotaLimit = 0;
        getCalQuota($this->courseId, $quotaUsed, $quotaLimit);
        setQuota($this->courseId, $quotaUsed);
    }
    
    /**
     * 產生「是非題」的 XML
     *
     * @return string QTI 1.2 XML format
     */
    function buildYesNoXML()
    {
        $ans    = (trim($this->itemData['answer']) == 'T') ? 'T' : 'F';
        $xmlstr = <<< EOB
<item ident="{$this->ident}" title="{$this->itemData['title']}"
      xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"
      xmlns:wm="http://www.sun.net.tw/WisdomMaster">
    <presentation label="{$this->itemData['label']}">
        <flow>
            <material>
                <mattext>{$this->itemData['topic']}</mattext>
            </material>
            <response_lid ident="ANS01" rcardinality="Single" rtiming="No">
                <render_choice>
                    <response_label ident="T">
                        <flow_mat>
                            <material>
                                <mattext>Agree</mattext>
                            </material>
                        </flow_mat>
                    </response_label>
                    <response_label ident="F">
                        <flow_mat>
                            <material>
                                <mattext>Disagree</mattext>
                            </material>
                        </flow_mat>
                    </response_label>
                </render_choice>
            </response_lid>
        </flow>
    </presentation>
    <resprocessing>
        <outcomes>
            <decvar vartype="Integer" defaultval="0" />
        </outcomes>
        <respcondition>
            <conditionvar>
                <varequal respident="ANS01">{$ans}</varequal>
            </conditionvar>
            <setvar action="Set">1</setvar>
            <displayfeedback feedbacktype="Response" linkrefid="Correct"/>
        </respcondition>
    </resprocessing>
    <itemfeedback ident="Correct">
        <flow_mat>
            <material>
                <mattext></mattext>
            </material>
        </flow_mat>
        <solution>
            <solutionmaterial>
                <material>
                    <mattext>{$this->itemData['ans_detail']}</mattext>
                </material>
            </solutionmaterial>
            <wm:refurl>{$this->itemData['refurl']}</wm:refurl>
        </solution>
        <hint enable="{$this->itemData['hintEnable']}">
            <hintmaterial>
                <material>
                    <mattext>{$this->itemData['hint']}</mattext>
                </material>
            </hintmaterial>
        </hint>
    </itemfeedback>
    <wm:hardlevel>{$this->itemData['level']}</wm:hardlevel>
    <wm:class>
        <wm:version>{$this->itemData['version']}</wm:version>
        <wm:volume>{$this->itemData['volume']}</wm:volume>
        <wm:chapter>{$this->itemData['chapter']}</wm:chapter>
        <wm:paragraph>{$this->itemData['paragraph']}</wm:paragraph>
        <wm:section>{$this->itemData['section']}</wm:section>
    </wm:class>
</item>
EOB;
        return $xmlstr;
    }
    
    /**
     * 產生「單選題」的 XML
     *
     * @return string QTI 1.2 XML format
     */
    function buildSingleSelectXML()
    {
        $ans    = max(1, $this->itemData['answer']);
        $xmlstr = <<< EOB
<item ident="{$this->ident}" title="{$this->itemData['title']}"
      xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"
      xmlns:wm="http://www.sun.net.tw/WisdomMaster">
    <presentation label="{$this->itemData['label']}">
        <flow>
            <material>
                <mattext>{$this->itemData['topic']}</mattext>
            </material>
            <response_lid ident="ANS01" rcardinality="Single" rtiming="No">
                <render_choice shuffle="Yes">
EOB;
        for ($i = 0, $c = count($this->itemData['render_choices']); $i < $c; $i++) {
            $alpha                                = $i + 1; // chr(ord('A') + $i);
            $this->itemData['render_choices'][$i] = escape_xml_content($this->itemData['render_choices'][$i]);
            $xmlstr .= <<< EOB
                        <response_label ident="{$alpha}">
                            <flow_mat>
                                <material>
                                    <mattext>{$this->itemData['render_choices'][$i]}</mattext>
                                </material>
                            </flow_mat>
                        </response_label>
EOB;
        }
        $xmlstr .= <<< EOB
                </render_choice>
            </response_lid>
        </flow>
    </presentation>
    <resprocessing>
        <outcomes>
            <decvar vartype="Integer" defaultval="0" />
        </outcomes>
        <respcondition title="Correct">
            <conditionvar>
                <varequal respident="ANS01">{$ans}</varequal>
            </conditionvar>
            <setvar action="Set">1</setvar>
            <displayfeedback feedbacktype="Response" linkrefid="Correct"/>
        </respcondition>
    </resprocessing>
    <itemfeedback ident="Correct">
        <flow_mat>
            <material>
                <mattext></mattext>
            </material>
        </flow_mat>
        <solution>
            <solutionmaterial>
                <material>
                    <mattext>{$this->itemData['ans_detail']}</mattext>
                </material>
            </solutionmaterial>
            <wm:refurl>{$this->itemData['refurl']}</wm:refurl>
        </solution>
        <hint enable="{$this->itemData['hintEnable']}">
            <hintmaterial>
                <material>
                    <mattext>{$this->itemData['hint']}</mattext>
                </material>
            </hintmaterial>
        </hint>
    </itemfeedback>
    <wm:hardlevel>{$this->itemData['level']}</wm:hardlevel>
    <wm:class>
        <wm:version>{$this->itemData['version']}</wm:version>
        <wm:volume>{$this->itemData['volume']}</wm:volume>
        <wm:chapter>{$this->itemData['chapter']}</wm:chapter>
        <wm:paragraph>{$this->itemData['paragraph']}</wm:paragraph>
        <wm:section>{$this->itemData['section']}</wm:section>
    </wm:class>
</item>
EOB;
        return $xmlstr;
    }
    
    /**
     * 產生「複選題」的 XML
     *
     * @return string QTI 1.2 XML format
     */
    function buildMultiSelectXML()
    {
        $xmlstr = <<< EOB
<item ident="{$this->ident}" title="{$this->itemData['title']}"
      xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"
      xmlns:wm="http://www.sun.net.tw/WisdomMaster">
    <presentation label="{$this->itemData['label']}">
        <flow>
            <material>
                <mattext>{$this->itemData['topic']}</mattext>
            </material>
            <response_lid ident="ANS01" rcardinality="Multiple" rtiming="No">
                <render_choice shuffle="Yes">
EOB;
        for ($i = 0, $c = count($this->itemData['render_choices']); $i < $c; $i++) {
            $this->itemData['render_choices'][$i] = escape_xml_content($this->itemData['render_choices'][$i]);
            $alpha                                = $i + 1; // chr(ord('A') + $i);
            $xmlstr .= <<< EOB
                    <response_label ident="{$alpha}">
                        <flow_mat>
                            <material>
                                <mattext>{$this->itemData['render_choices'][$i]}</mattext>
                            </material>
                        </flow_mat>
                    </response_label>
EOB;
        }
        $xmlstr .= <<< EOB
                </render_choice>
            </response_lid>
        </flow>
    </presentation>
    <resprocessing>
        <outcomes>
            <decvar vartype="Integer" defaultval="0" />
        </outcomes>
        <respcondition title="Correct">
            <conditionvar>
EOB;
            // 問卷也要塞預設值，不然app的問卷複選沒辦法回填使用者答案
            if (QTI_which === 'questionnaire') {
                $xmlstr .= <<< EOB
                <varequal respident="ANS01">1</varequal>
EOB;
            } else {
                for ($i = 0, $c = count($this->itemData['answer']); $i < $c; $i++) {
                    $ans = max(1, $this->itemData['answer'][$i]);
                    $xmlstr .= <<< EOB
                <varequal respident="ANS01">{$ans}</varequal>
EOB;
                }
            }
            $xmlstr .= <<< EOB
            </conditionvar>
            <setvar action="Set">1</setvar>
            <displayfeedback feedbacktype="Response" linkrefid="Correct"/>
        </respcondition>
    </resprocessing>
    <itemfeedback ident="Correct">
        <flow_mat>
            <material>
                <mattext></mattext>
            </material>
        </flow_mat>
        <solution>
            <solutionmaterial>
                <material>
                    <mattext>{$this->itemData['ans_detail']}</mattext>
                </material>
            </solutionmaterial>
            <wm:refurl>{$this->itemData['refurl']}</wm:refurl>
        </solution>
        <hint enable="{$this->itemData['hintEnable']}">
            <hintmaterial>
                <material>
                    <mattext>{$this->itemData['hint']}</mattext>
                </material>
            </hintmaterial>
        </hint>
    </itemfeedback>
    <wm:hardlevel>{$this->itemData['level']}</wm:hardlevel>
    <wm:class>
        <wm:version>{$this->itemData['version']}</wm:version>
        <wm:volume>{$this->itemData['volume']}</wm:volume>
        <wm:chapter>{$this->itemData['chapter']}</wm:chapter>
        <wm:paragraph>{$this->itemData['paragraph']}</wm:paragraph>
        <wm:section>{$this->itemData['section']}</wm:section>
    </wm:class>
</item>
EOB;
        return $xmlstr;
    }
    
    /**
     * 產生「填充題」的 XML
     *
     * @return string QTI 1.2 XML format
     */
    function buildFillXML()
    {
        $xmlstr = <<< EOB
<item ident="{$this->ident}" title="{$this->itemData['title']}"
      xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"
      xmlns:wm="http://www.sun.net.tw/WisdomMaster">
    <presentation label="{$this->itemData['label']}">
        <flow>
EOB;
        $topic1 = explode('((', $this->itemData['topic']);
        $xmlstr .= "\t\t\t<material><mattext>{$topic1[0]}</mattext></material>";
        $aa = '';
        for ($i = 1, $c = count($topic1); $i < $c; $i++) {
            $topic2 = explode('))', $topic1[$i]);
            if (count($topic2) < 2) {
                die('填充題答案括號不對稱！');
            }
            $temp = $topic2[0];

            $topic2[0] = strip_tags((stripslashes($topic2[0]))); // 將答案的 html tag 過濾掉
            
            $topic2[1] = str_replace($topic2[0], '', $temp) . $topic2[1]; // 將答案的 html tag 移到 )) 之後，可與 (( 之前的 html tag 配對，避免畫面亂掉
            
            $topic2[0] = str_replace('&amp;','@==@',$topic2[0]);
            $topic2[0] = str_replace('&','&amp;',$topic2[0]);
            $topic2[0] = str_replace('@==@','&amp;amp;',$topic2[0]);
            $topic2[0] = htmlspecialchars_decode($topic2[0]);
            
                    
                        // 解決使用THML編輯器輸入西班牙文後，字數計算錯誤，如Tenía應該是5，但會誤判為12
                        $anslen = mb_strlen(html_entity_decode(html_entity_decode($topic2[0])));
            
            $rsid      = sprintf("FIB%02d", $i);
            $xmlstr .= <<< EOB
            <response_str ident="{$rsid}" rcardinality="Single" rtiming="No">
                <render_fib fibtype="String" prompt="Dashline" maxchars="{$anslen}">
                    <response_label ident="A"/>
                </render_fib>
            </response_str>
            <material>
                <mattext>{$topic2[1]}</mattext>
            </material>
EOB;
            $aa .= "\t\t\t<varequal respident=\"{$rsid}\" case=\"Yes\">{$topic2[0]}</varequal>\n";
        }
        $xmlstr .= <<< EOB
        </flow>
    </presentation>
    <resprocessing wm:compare_method="default">
        <outcomes>
            <decvar vartype="Integer" defaultval="0" />
        </outcomes>
        <respcondition>
            <conditionvar>{$aa}</conditionvar>
            <displayfeedback feedbacktype="Response" linkrefid="AllCorrect"/>
        </respcondition>
    </resprocessing>
    <itemfeedback ident="AllCorrect">
        <flow_mat>
            <material>
                <mattext>All correct. Well done.</mattext>
            </material>
        </flow_mat>
        <solution>
            <solutionmaterial>
                <material>
                    <mattext>{$this->itemData['ans_detail']}</mattext>
                </material>
            </solutionmaterial>
            <wm:refurl>{$this->itemData['refurl']}</wm:refurl>
        </solution>
        <hint enable="{$this->itemData['hintEnable']}">
            <hintmaterial>
                <material>
                    <mattext>{$this->itemData['hint']}</mattext>
                </material>
            </hintmaterial>
        </hint>
    </itemfeedback>
    <wm:hardlevel>{$this->itemData['level']}</wm:hardlevel>
    <wm:class>
        <wm:version>{$this->itemData['version']}</wm:version>
        <wm:volume>{$this->itemData['volume']}</wm:volume>
        <wm:chapter>{$this->itemData['chapter']}</wm:chapter>
        <wm:paragraph>{$this->itemData['paragraph']}</wm:paragraph>
        <wm:section>{$this->itemData['section']}</wm:section>
    </wm:class>
</item>
EOB;
        return $xmlstr;
    }
    
    /**
     * 產生「簡答題」的 XML
     *
     * @return string QTI 1.2 XML format
     */
    function buildAnswerXML()
    {
        $xmlstr = <<< EOB
<item ident="{$this->ident}" title="{$this->itemData['title']}"
      xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"
      xmlns:wm="http://www.sun.net.tw/WisdomMaster">
    <presentation label="{$this->itemData['label']}">
        <flow>
            <material>
                <mattext>{$this->itemData['topic']}</mattext>
            </material>
            <response_str ident="FIB91" rcardinality="Ordered" rtiming="No">
                <render_fib fibtype="String" prompt="Box" rows="10" columns="50">
                    <response_label ident="A"/>
                </render_fib>
            </response_str>
        </flow>
    </presentation>
    <resprocessing>
        <outcomes>
            <decvar vartype="Integer" defaultval="0" />
        </outcomes>
    </resprocessing>
    <itemfeedback ident="AllCorrect">
        <flow_mat>
            <material>
                <mattext>All correct. Well done.</mattext>
        </material>
        </flow_mat>
        <solution>
            <solutionmaterial>
                <material>
                    <mattext>{$this->itemData['ans_detail']}</mattext>
                </material>
            </solutionmaterial>
            <wm:refurl>{$this->itemData['refurl']}</wm:refurl>
        </solution>
        <hint enable="{$this->itemData['hintEnable']}">
            <hintmaterial>
                <material>
                    <mattext>{$this->itemData['hint']}</mattext>
                </material>
            </hintmaterial>
        </hint>
    </itemfeedback>
    <wm:hardlevel>{$this->itemData['level']}</wm:hardlevel>
    <wm:class>
        <wm:version>{$this->itemData['version']}</wm:version>
        <wm:volume>{$this->itemData['volume']}</wm:volume>
        <wm:chapter>{$this->itemData['chapter']}</wm:chapter>
        <wm:paragraph>{$this->itemData['paragraph']}</wm:paragraph>
        <wm:section>{$this->itemData['section']}</wm:section>
    </wm:class>
</item>
EOB;
        return $xmlstr;
    }
    
    /**
     * 產生「配合題」的 XML
     *
     * @return string QTI 1.2 XML format
     */
    function buildPairXML()
    {
        $xmlstr = <<< EOB
<item ident="{$this->ident}" title="{$this->itemData['title']}"
      xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"
      xmlns:wm="http://www.sun.net.tw/WisdomMaster">
    <presentation label="{$this->itemData['label']}">
        <flow>
            <material>
                <mattext>{$this->itemData['topic']}</mattext>
            </material>
            <response_grp ident = "PAIR01" rcardinality = "Multiple">
                <render_extension>
                    <ims_render_object orientation = "Row">
EOB;
        $aa     = '';
        $cl1    = count($this->itemData['render1_choices']);
        $cl2    = count($this->itemData['render2_choices']);
        $grps   = implode(',', range(1, $cl2));
        for ($i = 0; $i < $cl1; $i++) {
            $source                                = chr(ord('A') + $i);
            $this->itemData['render1_choices'][$i] = escape_xml_content($this->itemData['render1_choices'][$i]);
            $xmlstr .= <<< EOB
                        <response_label ident = "{$source}" match_max = "1" match_group="{$grps}">
                            <material>
                                <mattext>{$this->itemData['render1_choices'][$i]}</mattext>
                            </material>
                        </response_label>
EOB;
        }
        for ($i = 0; $i < $cl2; $i++) {
            $target                                = $i + 1;
            $this->itemData['render2_choices'][$i] = escape_xml_content($this->itemData['render2_choices'][$i]);
            $xmlstr .= <<< EOB
                        <response_label ident = "{$target}">
                            <material>
                                <mattext>{$this->itemData['render2_choices'][$i]}</mattext>
                            </material>
                        </response_label>
EOB;
            // if (in_array($target, $this->itemData['answer'])) $aa .= "<varsubset respident = \"PAIR01\" setmatch = \"Exact\">$target</varsubset>";
        }
        $aa = vsprintf(str_repeat('<varsubset respident="PAIR01" setmatch="Exact">%u</varsubset>', count($this->itemData['answer'])), $this->itemData['answer']);
        $xmlstr .= <<< EOB
                    </ims_render_object>
                </render_extension>
            </response_grp>
        </flow>
    </presentation>
    <resprocessing>
        <outcomes>
            <decvar vartype="Integer" defaultval="0" />
        </outcomes>
        <respcondition>{$aa}</respcondition>
    </resprocessing>
    <itemfeedback ident="AllCorrect">
        <solution>
            <solutionmaterial>
                <material>
                    <mattext>{$this->itemData['ans_detail']}</mattext>
                </material>
            </solutionmaterial>
            <wm:refurl>{$this->itemData['refurl']}</wm:refurl>
        </solution>
        <hint enable="{$this->itemData['hintEnable']}">
            <hintmaterial>
                <material>
                    <mattext>{$this->itemData['hint']}</mattext>
                </material>
            </hintmaterial>
        </hint>
    </itemfeedback>
    <wm:hardlevel>{$this->itemData['level']}</wm:hardlevel>
    <wm:class>
        <wm:version>{$this->itemData['version']}</wm:version>
        <wm:volume>{$this->itemData['volume']}</wm:volume>
        <wm:chapter>{$this->itemData['chapter']}</wm:chapter>
        <wm:paragraph>{$this->itemData['paragraph']}</wm:paragraph>
        <wm:section>{$this->itemData['section']}</wm:section>
    </wm:class>
</item>
EOB;
        return $xmlstr;
    }
    
    /**
     * 產生「語音/附檔題」的 XML
     *
     * @return string QTI 1.2 XML format
     */
    function buildRecordXML()
    {
        $xmlstr = <<< EOB
<item ident="{$this->ident}" title="{$this->itemData['title']}"
      xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"
      xmlns:wm="http://www.sun.net.tw/WisdomMaster">
    <presentation label="{$this->itemData['label']}">
        <flow>
            <material>
                <mattext>{$this->itemData['topic']}</mattext>
            </material>
            <response_extension ident = "WM01">
                <render_extension>
EOB;
        if (is_array($this->itemData['render_extensions']) && in_array('use_record', $this->itemData['render_extensions'])) {
            $xmlstr .= <<< EOB
                    <response_label ident="REC01">
                        <material>
                            <mattext texttype="text/html"><![CDATA[
                                <BLOCKQUOTE>
                                <input type="hidden" name="mp3s[<%ITEM_ID%>][]" value="">
                                <input type="button" value="=" class="cssBtn" style="font-family: Webdings" disabled="true">
                                <input type="button" value=";" class="cssBtn" style="font-family: Webdings" disabled="true">
                                <input type="button" value="<" class="cssBtn" style="font-family: Webdings" disabled="true">
                                <OBJECT classid="clsid:9488FCAA-9836-44B2-ABA4-E72216EDA89E"
                                    codebase="/lib/anicamWB/AnicamWebSoundRec.cab#version=1,0,1,39"
                                    width="0" height="0" hspace="0" vspace="0">
                                </OBJECT><span></span></BLOCKQUOTE>]]></mattext>
                        </material>
                    </response_label>
EOB;
        }
        if (is_array($this->itemData['render_extensions']) && in_array('use_attach', $this->itemData['render_extensions'])) {
            $xmlstr .= <<< EOB
                    <response_label ident="FILE01">
                        <material>
                            <mattext texttype="text/html"><![CDATA[<BLOCKQUOTE><input type="file" name="uploads[<%ITEM_ID%>][]" id="uploads[<%ITEM_ID%>][]" size="30"/></BLOCKQUOTE>]]></mattext>
                        </material>
                    </response_label>
EOB;
        }
        $xmlstr .= <<< EOB
                </render_extension>
            </response_extension>
        </flow>
    </presentation>
    <resprocessing>
        <outcomes>
            <decvar vartype="Integer" defaultval="0" />
        </outcomes>
        <respcondition>{$aa}</respcondition>
    </resprocessing>
    <itemfeedback ident="AllCorrect">
        <solution>
            <solutionmaterial>
                <material>
                    <mattext>{$this->itemData['ans_detail']}</mattext>
                </material>
            </solutionmaterial>
            <wm:refurl>{$this->itemData['refurl']}</wm:refurl>
        </solution>
        <hint enable="{$this->itemData['hintEnable']}">
            <hintmaterial>
                <material>
                    <mattext>{$this->itemData['hint']}</mattext>
                </material>
            </hintmaterial>
        </hint>
    </itemfeedback>
    <wm:hardlevel>{$this->itemData['level']}</wm:hardlevel>
    <wm:class>
        <wm:version>{$this->itemData['version']}</wm:version>
        <wm:volume>{$this->itemData['volume']}</wm:volume>
        <wm:chapter>{$this->itemData['chapter']}</wm:chapter>
        <wm:paragraph>{$this->itemData['paragraph']}</wm:paragraph>
        <wm:section>{$this->itemData['section']}</wm:section>
    </wm:class>
</item>
EOB;
        return $xmlstr;
    }
    
    /**
     * 新增試題
     *
     * @return array array(Error Code, Error Message)
     */
    function addItem()
    {
        global $sysSession, $sysConn;
        
        dbNew(
            'WM_qti_' . $this->_QTI_which . '_item',
            'ident,title,course_id,type,version,volume,chapter,paragraph,section,level,' .
            'language,author,create_time,last_modify,content,answer,attach', 
            sprintf(
                "'$this->ident','%s','{$this->courseId}','{$this->itemData['type']}','{$this->itemData['version']}'" .
                    ",'{$this->itemData['volume']}','{$this->itemData['chapter']}','{$this->itemData['paragraph']}'" .
                    ",'{$this->itemData['section']}','{$this->itemData['level']}','$this->langIdx'" .
                    ",'{$sysSession->username}',now(),now(),'%s','%s',%s",
                addslashes($this->itemData['title']),
                addslashes(preg_replace('/>\s+</', '><', $this->xmlstr)),
                addslashes($this->itemData['ans_detail']),
                (empty($this->attach) ? 'NULL' : $sysConn->qstr(serialize($this->attach)))
            )
        );
        
        return array(
            'ErrCode' => $sysConn->ErrorNo(),
            'ErrMsg' => $sysConn->ErrorMsg()
        );
    }
    
    /**
     * 更新試題
     *
     * @return array array(Error Code, Error Message)
     */
    function updateItem()
    {
        global $sysSession, $sysConn;
        
        dbSet(
            'WM_qti_' . $this->_QTI_which . '_item',
            sprintf(
                "title='%s',course_id=%08d,type=%d,version=%d,volume=%d,chapter=%d,paragraph=%d,section=%d,level=%d," .
                    "language=%d,author='%s',last_modify=now(),content='%s',answer='%s',attach=%s",
                addslashes($this->itemData['title']),
                $this->courseId,
                $this->itemData['type'],
                $this->itemData['version'],
                $this->itemData['volume'],
                $this->itemData['chapter'],
                $this->itemData['paragraph'],
                $this->itemData['section'],
                $this->itemData['level'],
                $this->langIdx,
                $sysSession->username,
                addslashes(preg_replace('/>\s+</', '><', $this->xmlstr)),
                addslashes($this->itemData['ans_detail']),
                (count($this->attach) <= 0 ? 'NULL' : $sysConn->qstr(serialize($this->attach)))
         ),
         "ident='{$this->ident}'"
     );
        
        return array(
            'ErrCode' => $sysConn->ErrorNo(),
            'ErrMsg' => $sysConn->ErrorMsg()
        );
    }
    
    /**
     * 儲存試題
     *
     * @param array $data 試題資料
     *
     * @return array array(Error Code, Error Message)<br \>
     *     Error Code => -1: 未通過 ACL 檢查, 0: 成功, > 0: 失敗<br \>
     *     Error Message => 錯誤訊息
     */
    function saveItem($data)
    {
        global $sysSession;
        
        $this->itemData = $data;
        
        // 檢查 ACL
        $res = $this->checkACL($data['origin'], $data['ident'], $data['ticket']);
        if (!$res) {
            return array(
                'ErrCode' => -1,
                'ErrMsg' => 'Illegal Access !'
            );
        }
        
        // 轉換試題資料
        $title                   = htmlspecialchars_decode(strip_tags(stripslashes($data['topic'])));
        $title                   = escape_xml_content(htmlspecialchars(mb_strimwidth($title, 0, 40, ' ...', 'UTF-8')));
        $this->itemData['topic'] = escape_xml_content($data['topic']);
        $this->itemData['title'] = $title;
        // $title                       = escape_xml_content(mb_strimwidth(strip_tags(stripslashes($data['topic'])), 0, 40, ' ...', 'UTF-8'));
        $this->itemData['label'] = $this->itemData['title'];
        
        $this->itemData['ans_detail'] = escape_xml_content($data['ans_detail']);
        $this->itemData['hint']       = escape_xml_content($data['hint']);
        $this->itemData['hintEnable'] = $data['hintEnable'] ? 'true' : 'false';
        $this->itemData['refurl']     = escape_xml_content($data['ref_url']);
        
        // 版、冊、章、節、段 與 難易度
        $this->itemData['level']     = intval($data['level']);
        $this->itemData['version']   = intval($data['version']);
        $this->itemData['volume']    = intval($data['volume']);
        $this->itemData['chapter']   = intval($data['chapter']);
        $this->itemData['paragraph'] = intval($data['paragraph']);
        $this->itemData['section']   = intval($data['section']);
        
        // 處理夾檔
        $this->saveFiles();
        // POST 資料轉成 XML
        $this->xmlstr = '';
        switch ($this->itemData['type']) {
            case '1': // 是非題
                $this->xmlstr = $this->buildYesNoXML();
                break;
            case '2': // 單選題
                $this->xmlstr = $this->buildSingleSelectXML();
                break;
            case '3': // 複選題
                $this->xmlstr = $this->buildMultiSelectXML();
                break;
            case '4': // 填充題
                $this->xmlstr = $this->buildFillXML();
                break;
            case '5': // 簡答題
                $this->xmlstr = $this->buildAnswerXML();
                break;
            case '6': // 配合題
                $this->xmlstr = $this->buildPairXML();
                break;
            case '7': // 語音/附檔題
                $this->xmlstr = $this->buildRecordXML();
                break;
        }
        // 儲存
        $lang          = array(
            'Big5',
            'GB2312',
            'en',
            'EUC-JP',
            'user_define'
        );
        $this->langIdx = array_search($sysSession->lang, $lang);
        if ($this->langIdx === NULL || $this->langIdx === FALSE) {
            $this->langIdx = 0;
        }
        
        // 處理latex方程式（編輯模式時）
        // 說明：遇到使用 http://latex.codecogs.com/gif.latex? 方程式的，將圖片抓取後存在本地端（/base/10001/latex），學生端考試時，改讀取本地端圖片，以減少讀取次數
        $detect_content = array($data['topic'], $data['ans_detail']);
        $rsCourse = new course();
        foreach ($detect_content as $v) {
            // 改呼叫物件
            $rsCourse->transform_LATEX($v, false);
            
            // 原始寫法
////            echo '<pre>';
////            var_dump(htmlspecialchars($v));
////            echo '</pre>';
//
//            // 是否有用到方程式
////            preg_match_all("/<img alt=\\\\\".+\\\\\" src=\\\\\"http:\/\/latex.codecogs.com\/gif.latex\?(.*)\\\\\" \/>/", $v, $match);
//            preg_match_all('/img alt=.* src=.*http:\/\/latex.codecogs.com\/gif.latex\?([~!%*()_.0-9a-zA-Z\*]*).* \//', $v, $match);// 為了相容<>"會被轉換成entity code
////            echo '<pre>';
////            var_dump($match);
////            var_dump(is_array($match[1]) === true && count($match[1]) >= 1);
////            echo '</pre>';
////            die();
//            if (is_array($match[1]) === true && count($match[1]) >= 1) {
//                // 建立latex目錄
//                $folder_latex = sysDocumentRoot . '/base/' . $sysSession->school_id . '/latex';
//                if (is_dir($folder_latex) === false) {
//                    mkdir($folder_latex, 0755, true);
//                }
//                foreach ($match[1] as $latexcode) {
////                    echo '<pre>';
////                    var_dump($latexcode);
////                    var_dump('http://latex.codecogs.com/gif.latex?' . $latexcode, sysDocumentRoot . '/base/' . $sysSession->school_id . '/latex/' . base64_encode($latexcode) . '.gif');
////                    echo '</pre>';
//                    // 如果圖片不存在則複製圖片到本地端
//                    $local_file = sysDocumentRoot . '/base/' . $sysSession->school_id . '/latex/' . base64_encode($latexcode) . '.gif';
////                    echo '<pre>';
////                    var_dump(file_exists($local_file));
////                    echo '</pre>';
//                    if (file_exists($local_file) === false) {
//                        copy('http://latex.codecogs.com/gif.latex?' . $latexcode, sysDocumentRoot . '/base/' . $sysSession->school_id . '/latex/' . base64_encode($latexcode) . '.gif');
//                    }
//                }
//            }
        }
        
//        echo '<pre>';
//        var_dump('$this->itemData');
//        var_dump($this->itemData['attaches']['topic_files']);
//        var_dump($this->itemData['attaches']['render_choice_files']);
//        var_dump('ident', $data['ident'], $this->itemData['ident'], $this->ident);
//        echo '</pre>';
        
        if ($this->itemData['attaches']) {
            foreach ($this->itemData['attaches']['topic_files'] as $tf) {
//                echo '<pre>';
//                var_dump($tf);
//                var_dump(pathinfo($tf, PATHINFO_FILENAME));
//                var_dump(md5(pathinfo($tf, PATHINFO_FILENAME)));
                $newFilename = md5(pathinfo($tf, PATHINFO_FILENAME));
//                var_dump(pathinfo($tf, PATHINFO_EXTENSION));
//                echo '<pre>';
//                var_dump('preg_match', preg_match('/^\/base\/([\d]{5})\/course\/([\d]{8})\/content\/public\/temp\/.*/', $tf, $matches));
//                var_dump('preg_match', preg_match('/\/base\/([\d]{5})\/course\/([\d]{8})\/[exam|homework|questionnaire]+\/Q\/WM_ITEM1_.*/', $tf, $matches));
//                echo '</pre>';
                if (preg_match('/^\/base\/([\d]{5})\/course\/([\d]{8})\/content\/public\/temp\/.*/', $tf, $matches) || preg_match('/\/base\/([\d]{5})\/course\/([\d]{8})\/[exam|homework|questionnaire]+\/Q\/WM_ITEM1_.*/', $tf, $matches)) {
//                    echo '<pre>';
//                    var_dump($matches[0]);
//                    var_dump($matches[1]);
//                    var_dump($matches[2]);
//                    echo '</pre>';
                    $newTf = sprintf('/base/%d/course/%d/%s/Q/%s/%s', $matches[1], $matches[2], $this->_QTI_which, $this->ident, 'WMT' . $newFilename . '.' . pathinfo($tf, PATHINFO_EXTENSION));
    //                var_dump($newTf);
    //                var_dump($this->ident);
                    // /base/10001/course/10000007/exam/Q/WM_ITEM1_1000100000_10000007_1553832314_299103/WMQcNJpR.gif
    //                error_reporting(E_ALL);
    //                ini_set("display_errors", 1);
//                    echo '<pre>';
//                    var_dump('mkdir', sprintf(sysDocumentRoot . '/base/%d/course/%d/%s/Q/%s', $matches[1], $matches[2], $this->_QTI_which, $this->ident));
//                    echo '</pre>';
                    @mkdir(sprintf(sysDocumentRoot . '/base/%d/course/%d/%s/Q/%s', $matches[1], $matches[2], $this->_QTI_which, $this->ident), 0775, TRUE);
//                    echo '<pre>';
//                    var_dump('copy', sysDocumentRoot . $matches[0], sysDocumentRoot . $newTf);
//                    echo '</pre>';
                    copy(sysDocumentRoot . $matches[0], sysDocumentRoot . $newTf);
                    $this->attach['topic_files'][pathinfo($tf, PATHINFO_BASENAME)] = 'WMT' . $newFilename . '.'  . pathinfo($tf, PATHINFO_EXTENSION);
                }
            }
            
//            echo '<pre>';
//            var_dump('原資料表附檔 this->attach before', $this->attach);
//            echo '</pre>';
            
            $attachKeysBefore = array_keys($this->attach['render_choice_files']);
            $attachValuesBefore = array_values($this->attach['render_choice_files']);
            
//            echo '<pre>';
//            var_dump('原資料表附檔 attachKeysBefore', $attachKeysBefore);
//            var_dump('原資料表附檔 attachValuesBefore', $attachValuesBefore);
//            echo '</pre>';
            
            unset($this->attach['render_choice_files']);
            foreach ($this->itemData['attaches']['render_choice_files'] as $k => $rcf) {
//                echo '<pre>';
//                var_dump($k);
//                var_dump($rcf);
//                echo '</pre>';
                if ($rcf) {
//                    echo '<pre>';
    //                var_dump($rcf);
    //                var_dump(pathinfo($rcf, PATHINFO_FILENAME));
    //                var_dump(md5(pathinfo($rcf, PATHINFO_FILENAME)));
                    $newFilename = md5(pathinfo($rcf, PATHINFO_FILENAME));
    //                var_dump(pathinfo($rcf, PATHINFO_EXTENSION));
                    
//                    echo '<pre>';
//                    var_dump('preg_match', preg_match('/^\/base\/([\d]{5})\/course\/([\d]{8})\/content\/public\/temp\/.*/', $rcf, $matches));
//                    var_dump('preg_match', preg_match('/\/base\/([\d]{5})\/course\/([\d]{8})\/[exam|homework|questionnaire]+\/Q\/WM_ITEM1_.*/', $rcf, $matches));
//                    echo '</pre>';
                    if (preg_match('/^\/base\/([\d]{5})\/course\/([\d]{8})\/content\/public\/temp\/.*/', $rcf, $matches) || preg_match('/\/base\/([\d]{5})\/course\/([\d]{8})\/[exam|homework|questionnaire]+\/Q\/WM_ITEM1_.*/', $rcf, $matches)) {
        //                var_dump($matches[1]);
        //                var_dump($matches[2]);
                        $newTf = sprintf('/base/%d/course/%d/%s/Q/%s/%s', $matches[1], $matches[2], $this->_QTI_which, $this->ident, 'WMC' . $newFilename . '.' . pathinfo($rcf, PATHINFO_EXTENSION));
        //                var_dump($newTf);
        //                var_dump($this->ident);
                        // /base/10001/course/10000007/exam/Q/WM_ITEM1_1000100000_10000007_1553832314_299103/WMQcNJpR.gif
        //                error_reporting(E_ALL);
        //                ini_set("display_errors", 1);                
                        @mkdir(sprintf(sysDocumentRoot . '/base/%d/course/%d/%s/Q/%s', $matches[1], $matches[2], $this->_QTI_which, $this->ident), 0775, TRUE);
    //                    echo '<pre>';
    //                    var_dump('新上傳');
    //                    echo '</pre>';
//                        echo '<pre>';
//                        var_dump('copy', sysDocumentRoot . $matches[0], sysDocumentRoot . $newTf);
//                        echo '</pre>';
                        copy(sysDocumentRoot . $matches[0], sysDocumentRoot . $newTf);
    //                    echo '</pre>';

                        unset($this->attach['render_choice_files'][$k]);
                        $this->attach['render_choice_files'][pathinfo($rcf, PATHINFO_BASENAME)] = 'WMC' . $newFilename . '.'  . pathinfo($rcf, PATHINFO_EXTENSION);
                    }
                } else {
                    if ($attachValuesBefore[$k] === '' || isset($attachValuesBefore[$k]) === FALSE) {
//                        echo '<pre>';
//                        var_dump('無值');
//                        echo '</pre>';
                        $this->attach['render_choice_files'][$k] = '';
                    } else {
//                        echo '<pre>';
//                        var_dump('已有值');
//                        echo '</pre>';
//                        unset($this->attach['render_choice_files'][$attachKeysBefore[$k]]);
                        $this->attach['render_choice_files'][$attachKeysBefore[$k]] = $attachValuesBefore[$k];
                    }
                }
            
//                echo '<pre>';
//                var_dump('每回 $this->attach after', $this->attach);
//                echo '</pre>';
            }
            
//            echo '<pre>';
//            var_dump('final $this->attach after', $this->attach);
//            echo '</pre>';
//            die();
        }
        
        if ($this->isModify) {
            return $this->updateItem();
        }
        
//        echo '<pre>';
//        var_dump('ident', $data['ident'], $this->itemData['ident'], $this->ident);
//        echo '</pre>';
        
        return $this->addItem();
    }
}
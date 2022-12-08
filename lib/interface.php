<?php
    /*
     *        HTML 版
     * @version $Id: interface.php,v 1.6 2010-07-09 01:27:24 lst Exp $
     */
    require_once(sysDocumentRoot . '/lib/lib_wmhelp.php');
    require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');

    $xajax_save_temp = new xajax('/lib/save_temporary.server.php');
    $xajax_save_temp->registerFunction('save_temp');
    $xajax_save_temp->registerFunction('check_temp');
    $xajax_save_temp->registerFunction('clean_temp');
    $xajax_save_temp->registerFunction('restore_temp');

    $sysIndent = 0;
    $uniqObjId = 1;    // 內部自行產生的物件編號

    /**
     * showXHTML_head_B()
     * 秀出 XHTML 檔頭部份
     *
     * @param $title 網頁標題
     * @return
     **/
    function showXHTML_head_B($title, $version = null){
            global $sysSession;
            if ($_SERVER['PHP_SELF'] == '/index.php')
                $homepage = '<meta http-equiv="refresh" content="1200; URL=/" >';
            if (isset($version) === true) {
                $xu = sprintf('<meta http-equiv="X-UA-Compatible" content="IE=%s">', $version);
            } else if (basename($_SERVER['SCRIPT_FILENAME']) == 'exam_start.php') {
                $xu = '<meta http-equiv="X-UA-Compatible" content="IE=8" >';
            } else {
                // IE
                if(preg_match('/Trident\/(\d+)/', $_SERVER['HTTP_USER_AGENT'], $regs) && intval($regs[1])>=6){
//                    if($sysSession->env === 'teach') {
//                        $xu = '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE10, 11" >';
//                    } else {
                    // 因應PDF.js部份PDF需IE11才能正常顯示，IE9/10不能顯示
                        $xu = '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE10, 11" >';
//                    }
               // CHROME
               }else{
                    $xu = '<meta http-equiv="X-UA-Compatible" content="IE=8" >';
               }                
            }
            $sysCL           = array(
                'Big5' => 'zh-tw',
                'en' => 'en',
                'GB2312' => 'zh-cn',
                'EUC-JP' => 'ja'
            );
            $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
            if (is_file(sysDocumentRoot . "/base/" . $sysSession->school_id . "/door/tpl/icon.ico")) {
                $ico = '<link rel="icon" href="/base/' . $sysSession->school_id . '/door/tpl/icon.ico">';
            }
            if (empty($ACCEPT_LANGUAGE))
                $ACCEPT_LANGUAGE = 'zh-tw';
/*
        echo (in_array($_SERVER['PHP_SELF'], array('/learn/index.php',                '/academic/index.php',
                                                   '/academic/course/sysbar.php',    '/teach/exam/exam_correct.php',
                                                   '/direct/index.php',                '/online/index.php',
                                                   '/online/message.php'))
             ) ?
             '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">' :
             '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' ;
*/
        print <<< EOB
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<html>
<head>
$homepage
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
<meta http-equiv="Content-Language" content="$ACCEPT_LANGUAGE" >
$xu
$ico
<title>$title</title>


EOB;
    }

    /**
     * showXHTML_head_E()
     * 秀出 </html> TAG
     *
     * @param $extra 秀出 </html> 之前額外資訊。例如：<base target=  或 <bgsound src= ...秀出 </html> 之前額外資訊。例如：<base target=  或 <bgsound src= ...
     * @return
     **/
    function showXHTML_head_E($extra=null){
        echo $extra, "\n</head>\n";
    }


    function assign_newname(&$val)
    {
        $val = '\\1x' . uniqid(rand());
    }

    function modify_key(&$key)
    {
        $key = '/([^\w.])' . $key . '\\b/';
    }

    /**
     * showXHTML_script()
     * 秀出 script 段
     *
     * @param $type
     *             (1) 'inline'
     *             (2) 'include'
     * @param $source
     *             (1) 'inline' :後接的 $source 即為 script
     *             (2) 'include':後接的 $source 為 script 路徑
     * @return
     **/
    function showXHTML_script($type, $source, $strip=TRUE, $obfuscate=NULL){
        switch($type){
            case 'inline':
                $scr= '<script type="text/javascript" language="javascript">' .
                     ((defined('WM_RELEASE') || $strip) ?
                     ("\n<!--\n" . preg_replace(array('/\s\/\/\s.*$/m', '/\/\*.*\*\//sU', '/\s*[\n\r]\s*/'),
                                                 array('','',' '),
                                                 $source) . " \n//-->\n</script>\n")
                     : "\n<!--\n$source \n//-->\n</script>\n");
                if (is_array($obfuscate))
                {
                    $pattern = array();
                    if (preg_match_all('/\bfunction\s+(\w+)\s*\(/sU', $scr, $functions))
                    {
                        $pattern = array_flip($functions[1]);
                    }

                    if (preg_match_all('/\bvar\s+(\w+)(\s*=\s*.*)?(\s*,\s*(\w+)(\s*=\s*.*)?)*\s*;/sU', $scr, $variables))
                    {
                        $pattern = array_merge($pattern, array_flip($variables[1]), array_flip($variables[4]));
                    }

                    if (isset($pattern[''])) unset($pattern['']);
                    foreach($obfuscate as $keep) unset($pattern[$keep]);
                    krsort($pattern);
                    array_walk($pattern, 'assign_newname');
                    $pattern = array_flip($pattern);
                    array_walk($pattern, 'modify_key');

                    echo count($pattern) ? preg_replace(array_values($pattern), array_keys($pattern), $scr) : $scr;
                }
                else
                    echo $scr;
                break;
            case 'include':
                            
                // 若符合單純js檔案，增加時間戳記，減少cache困擾
                $filetime = '';
                if (preg_match('/^[\_a-zA-Z0-9\.]*.js$/', $source)) {
                    $filetime = filemtime($source);
                } else if (preg_match('/^\/[\/\_a-zA-Z0-9\.]*.js$/', $source)) {
                    $filetime = filemtime(sysDocumentRoot . $source);
                }
                                
                echo '<script type="text/javascript" language="javascript" lang="zh-tw" ',
                     "src=\"$source?$filetime\"></script>\n";
                break;
            default:
                echo '<script type="text/javascript" language="javascript" lang="zh-tw">',
                     "alert('Javascript 引用錯誤！');</script>\n";
                break;
        }
    }

    /**
     * showXHTML_css()
     * 秀出 css 段
     *
     * @param $type
     *        (1) 'inline'
     *        (2) 'include'
     * @param $source
     *        (1) 'inline' :後接的 $source 即為 css
     *        (2) 'include':後接的 $source 為 css 路徑
     * @return
     **/
    function showXHTML_css($type, $source){
        switch($type){
            case 'inline':
                echo '<style type="text/css">',
                     "\n<!--\n$source \n//-->\n</style>\n";
                break;
            case 'include':
                // 若符合單純css檔案，增加時間戳記，減少cache困擾
                $filetime = '';
                if (preg_match('/^[\_a-zA-Z0-9\.]*.css$/', $source)) {
                    $filetime = filemtime($source);
                } else if (preg_match('/^\/[\/\_a-zA-Z0-9\.]*.css$/', $source)) {
                    $filetime = filemtime(sysDocumentRoot . $source);
                }

                // 載入learn_mooc/wm.css時，要先載入 learn/wm.css
                if (dirname($source) == '/theme/default/learn_mooc') {
                    if (!defined('DEMO')) {
                        $css = basename($source);
                        $css = getThemeFile($css);
                        if (!empty($css) && ($css != $source))
                            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css?$filetime\" >\n";
                    }
                    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$source?$filetime\" >\n";
                }else{
                    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$source?$filetime\" >\n";
                    if (!defined('DEMO')) {
                        $css = basename($source);
                        $css = getThemeFile($css);
                        if (!empty($css) && ($css != $source))
                            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css?$filetime\" >\n";
                    }
                }

                break;
            default:
                echo '<script type="text/javascript" language="JavaScript">',
                     "alert('css 引用錯誤！');</script>\n";
                break;
        }
    }

    /**
     * showXHTML_body_B()
     * 秀出 <body>
     *
     * @param $extra <body> 的額外屬性
     * @return
     **/
    function showXHTML_body_B($extra=null){
        echo "<body $extra >\n";
    }

    /**
     * showXHTML_body_E()
     * 秀出 </body></html>
     *
     * @param $extra 秀出 TAG 之前，額外 html
     * @return
     **/
    function showXHTML_body_E($extra=null){
        /*
        echo "<script>
        function xxx_cancel(){ return false; }
        function xxx_nobubble(){ event.cancelBubble = true; }
        document.onselectstart=xxx_cancel;
        var xxx_nodes = document.getElementsByTagName('input');
        for(var i=0; i<xxx_nodes.length; i++) xxx_nodes[i].onselectstart=xxx_nobubble;
        var xxx_nodes = document.getElementsByTagName('textarea');
        for(var i=0; i<xxx_nodes.length; i++) xxx_nodes[i].onselectstart=xxx_nobubble;
        </script> */
        echo "
$extra
<a href=\"#\" id=\"toGo\"></a>
</body>
</html>
";
    }

    /**
     * showXHTML_table_B()
     * 秀出 <table>
     *
     * @param $extra <table> 額外屬性
     * @return
     **/
    function showXHTML_table_B($extra=null){
        global $sysIndent;
        echo str_repeat(chr(9), $sysIndent),
            "<table $extra >\n";
             // (strpos($extra, 'class=') === FALSE ? "<table class=\"cssTable\" $extra >\n" : "<table $extra >\n");
        $sysIndent++;
    }

    /**
     * showXHTML_table_E()
     * 秀出 </table>
     *
 * @param $extra 秀出 TAG 之前，額外 html
     * @return
     **/
    function showXHTML_table_E($extra=null){
        global $sysIndent;
        $sysIndent--;
        if (empty($extra))
            echo str_repeat(chr(9), $sysIndent),"</table>\n";
        else
            echo str_repeat(chr(9), $sysIndent),$extra,"\n",
                 str_repeat(chr(9), $sysIndent),"</table>\n";
    }

    /**
     * showXHTML_tr_B()
     *
     * @param $extra
     * @return
     **/
    function showXHTML_tr_B($extra=null){
        global $sysIndent;
        echo str_repeat(chr(9), $sysIndent),
             (strpos($extra, 'class=') === FALSE ? "<tr class=\"cssTr\" $extra >\n" : "<tr $extra >\n");
        $sysIndent++;
    }

    /**
     * showXHTML_tr_E()
     *
     * @param $extra
     * @return
     **/
    function showXHTML_tr_E($extra=null){
        global $sysIndent;
        $sysIndent--;
        if (empty($extra))
            echo str_repeat(chr(9), $sysIndent),"</tr>\n";
        else
            echo str_repeat(chr(9), $sysIndent),$extra,"\n",
                 str_repeat(chr(9), $sysIndent),"</tr>\n";
    }

    /**
     * showXHTML_td_B()
     *
     * @param $extra
     * @return
     **/
    function showXHTML_td_B($extra=null){
        global $sysIndent;
        echo str_repeat(chr(9), $sysIndent),
             (strpos($extra, 'class=') === FALSE ? "<td class=\"cssTd\" $extra >\n" : "<td $extra >\n");
    }

    /**
     * showXHTML_td_E()
     *
     * @param $extra
     * @return
     **/
    function showXHTML_td_E($extra=null){
        global $sysIndent;
        echo str_repeat(chr(9), $sysIndent), (empty($extra) ? "</td>\n" : "$extra </td>\n");
    }

    /**
     * showXHTML_td()
     *
     * @param $extra
     * @param $text
     * @return
     **/
    function showXHTML_td($extra=null, $text=null){
        global $sysIndent;
        // $text = htmlspecialchars($text);
        echo str_repeat(chr(9), $sysIndent),
             (strpos($extra, 'class=') === FALSE ? "<td class=\"cssTd\" $extra >$text </td>\n" : "<td $extra >$text </td>\n");
    }

    /**
     * showXHTML_th()
     *
     * @param $extra
     * @param $text
     * @return
     **/
    function showXHTML_th($extra=null, $text=null){
        global $sysIndent;
        echo str_repeat(chr(9), $sysIndent),
             (strpos($extra, 'class=') === FALSE ? "<th class=\"cssTd\" $extra >$text </th>\n" : "<th $extra >$text </th>\n");
    }

    /**
     * showXHTML_form_B()
     *
     * @param $extra
     * @param $id
     * @return
     **/
    function showXHTML_form_B($extra=null, $id=null){
        global $sysIndent, $sysSession;

        $sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn','EUC-JP' => 'ja');
        $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
        if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

        echo "\n",str_repeat(chr(9), $sysIndent),"<form " , ($id?"id=\"$id\" name=\"$id\" ":''),
             "accept-charset=\"UTF-8\" lang=\"{$ACCEPT_LANGUAGE}\" $extra >\n";
        $sysIndent++;
    }

    /**
     * showXHTML_form_E()
     *
     * @param $extra
     * @return
     **/
    function showXHTML_form_E(){
        global $sysIndent;
        $sysIndent--;
        echo "\n",str_repeat(chr(9), $sysIndent),"</form>\n";
    }

    /**
     * showXHTML_input()
     *
     * @param $type
     *        (1) text
     *        (2) checkbox
     *        (3) password
     *        (4) hidden
     *        (5) select
     *        (6) textarea
     *        (7) radio
     *        (8) button
     *        (9) submit
     *        (10) reset
     *        (11) file
     *        (12) checkboxes
     * @param $id
     * @param $value
     * @param $default 預設值
     * @param $extra
     * @return
     **/
    function showXHTML_input($type, $id=null, $value=null, $default=null, $extra=null, $separator=null){
        global $sysIndent, $uniqObjId;
        echo str_repeat(chr(9), $sysIndent);
        $first = true;
        // if (!is_array($value)) $value = htmlspecialchars($value);
        switch($type){
            case 'text':
                if (strpos($extra, 'class=') === FALSE) $extra = 'class="cssInput" ' . $extra;
                echo "<input type=\"text\" ",($id?"name=\"$id\" ":''),"value=\"$value\" $extra >\n";
                break;
            case 'number':
                if (strpos($extra, 'class=') === FALSE) $extra = 'class="cssInput" ' . $extra;
                echo "<input type=\"number\" ",($id?"name=\"$id\" ":''),"value=\"$value\" $extra >\n";
                break;
            case 'checkbox':
                echo "<input type=\"checkbox\" ",($id?"name=\"$id\" ":''),"value=\"$value\" $extra ",$default?'checked="checked"':'',">\n";
                break;
            case 'password':
                if (strpos($extra, 'class=') === FALSE) $extra = 'class="cssInput" ' . $extra;
                echo "<input type=\"password\" ",($id?"name=\"$id\" ":''),"$extra >\n";
                break;
            case 'hidden':
                $name = $id ? " name=\"$id\" " : '';
                $id   = preg_match('/(^|\s)id=[\'"]?[^\'"]+[\'"]?($|\s)/', $extra) ? '' : ($id ? "id=\"$id\" " : '');
                echo "<input type=\"hidden\" $name $id value=\"$value\" $extra >\n";
                break;
            case 'select':
                echo "<select ",($id?"name=\"$id\" ":''),"$extra >\n";
                while(list($k,$v)=each($value)){
                    echo str_repeat(chr(9), $sysIndent),
                         '<option value="', $k, '" title="' . $v . '"',
                         ((is_array($default) ? in_array($k, $default) : ($k==$default))?' selected="selected">':'>'),
                         $v, " </option>\n";
                }
                echo str_repeat(chr(9), $sysIndent),"</select>\n";
                break;
            case 'standard_select':
                echo "<select ",($id?"name=\"$id\" ":''),"$extra >\n";
                while(list($k,$v)=each($value)){
                    echo str_repeat(chr(9), $sysIndent),
                         '<option value="', $k, '" title="' . $v . '"',
                         ((is_array($default) ? in_array($k, $default) : ((String)$k===$default))?' selected="selected">':'>'),
                         $v, " </option>\n";
                }
                echo str_repeat(chr(9), $sysIndent),"</select>\n";
                break;
            case 'textarea';
                if (strpos($extra, 'class=') === FALSE) $extra = 'class="cssInput" ' . $extra;
                echo str_repeat(chr(9), $sysIndent),"<textarea ",($id?"name=\"$id\" ":''),"$extra >\n",
                     $value,"</textarea>\n";
                break;
            case 'radio':
                while(list($k,$v)=each($value)){
                    if ($first) $first=false; else echo str_repeat(chr(9), $sysIndent);
                    echo "<input type=\"radio\" id=\"sysRadioBtn{$uniqObjId}\" ",($id?"name=\"$id\" ":''),"value=\"$k\"",
                         ($k==$default?' checked="checked"':'')," $extra ><label for=\"sysRadioBtn{$uniqObjId}\">$v</label>$separator\n";
                    $uniqObjId++;
                }
                break;
            case 'standard_radio':
                while(list($k,$v)=each($value)){
                    if ($first) $first=false; else echo str_repeat(chr(9), $sysIndent);
                    echo "<label for=\"sysRadioBtn{$uniqObjId}\"><input type=\"radio\" id=\"sysRadioBtn{$uniqObjId}\" ",($id?"name=\"$id\" ":''),"value=\"$k\"",
                         ($k==$default?' checked="checked"':'')," $extra >$v</label>$separator\n";
                    $uniqObjId++;
                }
                break;
            case 'button':
            case 'submit':
            case 'reset' :
                if (strpos($extra, 'class=') === FALSE) $extra = 'class="cssBtn" ' . $extra;
                // $value = $value?(htmlspecialchars($value).' '):$value;
                echo "<input type=\"$type\" value=\"$value\" $extra >\n";
                break;
            case 'file':
                //#47320 Chrome [教師/課程管理/教材匯入] 匯入課程包，結果顯示alert「教材目錄內沒有php檔案，匯入失敗！」：仿patch3，修改檔案file模式介面
                if (strpos($extra, 'class=') === FALSE) $extra = 'class="cssInput" ' . $extra;
                if (strpos($extra, 'onkeypress') === false) $extra = 'onkeydown="return false;" ' . $extra;
                $name = $id ? "name=\"$id\" " : "name=\"uploads[]\" ";
                $id   = preg_match('/\sid=[\'"]?[^\'"]+[\'"]?\s/', $extra) ? '' : ($id ? "id=\"$id\" " : "id=\"uploads[]\" ");
                echo "<input type=\"file\" ", $name, $id, "$extra style=\"ime-mode:disabled;\">\n";
                break;
            case 'checkboxes':
                if (!is_array($default)) $default = array($default);
                foreach ($value as $k => $v) {
                    if ($first)
                        $first=false;
                    else
                        echo str_repeat(chr(9), $sysIndent);

                    echo "<input type=\"checkbox\" ", ($id?"name=\"$id\" ":''),
                         "value=\"$k\" $extra ",
                         (in_array($k, $default)?'checked="checked">':'>'),
                         $v, $separator, "\n";
                }
                break;
            case 'checkboxes_standard':
                if (!is_array($default)) $default = array($default);
                foreach ($value as $k => $v) {
                    if ($first)
                        $first=false;
                    else
                        echo str_repeat(chr(9), $sysIndent);

                    echo "<label><input type=\"checkbox\" ", ($id?"name=\"$id\" ":''),
                         "value=\"$k\" $extra ",
                         (in_array($k, $default)?'checked="checked">':'>'),
                         $v, $separator, "</label>\n";
                }
                break;
        }
    }

    /**
     * 產生一個從 $lbound 到 $ubound 的數字陣列 (用於秀出 <select> 的 <options>)
     *
     * @param $lbound 最小註標
     * @param $ubound 最大註標
     */
    function array_range($lbound, $ubound, $step=1){
        $a = array();
        if ($lbound <= $ubound && $step > 0)
            for($i=$lbound; $i<=$ubound; $i+=$step) $a[$i] = $i;

        return $a;
    }

    /**
     * showXHTML_tabs()
     *
     * @param $tabs
     *     一個二維陣列
     *     array(
     *         array('Title', 'ID', 'Function'),
     *         array('Title', 'ID', 'Function'),
     *         ...
     *         array('Title', 'ID', 'Function')
     *     )
     *
     *     Title : 顯示的文字
     *     ID : 要顯示的物件 ID
     *     Function : 要額外執行的 function (可有可無，有則執行)
     * @param $default 要顯示第幾個 tab
     * @param boolean $isDragable : 是否可以拖動，主要是顯示滑鼠游標的不同而已
     * @return
     **/
    $tabsCont = 1;
    function showXHTML_tabs($tabs, $default, $isDragable = false, $showHelp = true) {
        global $sysSession, $_SERVER, $uniqObjId, $tabsCont;
        static $isOutput;
        if (!is_array($tabs)) return '';
        // $isOutput = false;

        if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
            // 啟用 MOOC 時，強制關閉 help 按鈕
            $showHelp = false;
        }
        // 取得要顯示的佈景 (Begin)
        $theme = "/theme/{$sysSession->theme}/learn/";
        if (!empty($sysSession->env)) {
            switch ($sysSession->env) {
                case 'academic' : $theme = "/theme/{$sysSession->theme}/academic/"; break;
                case 'direct'   : $theme = "/theme/{$sysSession->theme}/direct/";   break;
                case 'teach'    : $theme = "/theme/{$sysSession->theme}/teach/";    break;
                default:
                    if (defined('DEMO'))
                        $theme = "/theme/{$sysSession->theme}/{$sysSession->env}/";
            }
        } else {
            if (strpos($_SERVER['PHP_SELF'], '/teach/') === 0)
                $theme = "/theme/{$sysSession->theme}/teach/";
            else if (strpos($_SERVER['PHP_SELF'], '/direct/') === 0)
                $theme = "/theme/{$sysSession->theme}/direct/";
            else if (strpos($_SERVER['PHP_SELF'], '/academic/') === 0)
                $theme = "/theme/{$sysSession->theme}/academic/";
        }
        // 取得要顯示的佈景 (End)

        $event = ' onclick="tabsMouseEvent(this, 2)" onmouseover="tabsMouseEvent(this, 1)" onmouseout="tabsMouseEvent(this, 0)" ';
        $cnts = count($tabs);
        if ($cnts == 1) {
            list($key, $val) = each($tabs);
            $default = is_int($key) ? $key + 1 : $key;
            // $default = key($tabs);
            $event = '';
        }
        $Cursor = ($isDragable) ? 'move' : 'default';
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
            showXHTML_tr_B();
                $tabsDef = $default;
                reset($tabs);
                foreach ($tabs as $key => $val) {
                    $i = is_int($key) ? $key + 1 : $key;
                    if ($i == $default) {
                        $tabsDef = $uniqObjId;
                        $img = 'on';
                    } else {
                        $img = 'off';
                    }

                    $act = isset($val[2]) ? $val[2] : '';
                    $tabsActs .= "\ttabsActs['{$uniqObjId}'] = new Array('{$val[1]}', '{$act}');\n";

                    $pic = getThemeFile('title_' . $img . '_01.gif');
                    if (empty($pic)) $pic = $theme . 'title_' . $img . '_01.gif';
                    showXHTML_td('class="disable-select"', '<img onselectstart="return false;" id="ImgL' . $uniqObjId . '" MyAttr="' . $uniqObjId . '" tabsIdx="' . $tabsCont . '" src="' . $pic . '?' . filemtime(sysDocumentRoot . $pic) . '" width="25" height="30" border="0" align="absbottom" />');

                    $pic = getThemeFile('title_' . $img . '_02.gif');
                    if (empty($pic)) $pic = $theme . 'title_' . $img . '_02.gif';
                    showXHTML_td('align="center" valign="bottom" nowrap="NoWrap" id="TitleID' . $uniqObjId . '" MyAttr="' . $uniqObjId . '" tabsIdx="' . $tabsCont . '" style="cursor: ' . $Cursor . '; background-image: url(\'' . $pic . '?' . filemtime(sysDocumentRoot . $pic) . '\');" class="cssTabs" onselectstart="return false;" ' . $event, $val[0]);

                    $pic = getThemeFile('title_' . $img . '_03.gif');
                    if (empty($pic)) $pic = $theme . 'title_' . $img . '_03.gif';
                    showXHTML_td('class="disable-select"', '<img onselectstart="return false;" id="ImgR' . $uniqObjId . '" MyAttr="' . $uniqObjId . '" tabsIdx="' . $tabsCont . '" src="' . $pic . '?' . filemtime(sysDocumentRoot . $pic) . '" width="28" height="30" border="0" align="absbottom" />');
                    $uniqObjId++;
                }
                showXHTML_td('width="100%"', '&nbsp;');
                //online help icon
                if ($showHelp) {
                    if ($_SERVER['PHP_SELF'] != '/wmhelp.php') {
                        $o_whicon = new wmhelp($sysSession->school_id);
                        $o_whicon->setHelpFilename($_SERVER['PHP_SELF']);
                        $pic = getThemeFile('help.gif');
                        if (empty($pic)) $pic = $theme . 'help.gif';
                        if (defined('DEMO')) {
                            showXHTML_td('', '<img border="0" src="' . $pic . '?' . filemtime(sysDocumentRoot . $pic) . '" width="21" height="21" border="0" align="middle" alt="Help" title="Help">');
                        } else {
                            if (isHelpWriter()) {
                                // $o_whicon->isHelpfileExists()
                                showXHTML_td('', '<a href="/wmhelp.php?url=' . urlencode($_SERVER['PHP_SELF']) . '" target="_blank"><img border="0" src="' . $pic . '?' . filemtime(sysDocumentRoot . $pic) . '" width="21" height="21" border="0" align="middle" alt="Help" title="Help"></a>');
                            } else {
                                // client 端開起 online help 檔案
                                if ($o_whicon->isHelpfileExists()){
                                    showXHTML_td('', '<a href="javascript:" onClick="window.open(\'' . $o_whicon->help_url . '\',\'aaa\',\'height=300,width=400,resizable=1,scrollbars=1,toolbar=0\')"><img border="0" src="' . $pic . '" width="21" height="21" border="0" align="middle" alt="Help" title="Help"></a>');
                                }
                            }
                        }
                    }
                }

            showXHTML_tr_E();
        showXHTML_table_E();
        // 圖片陣列
        $ary = array();
        for ($i = 1; $i < 4; $i++) {
            $ary[$i] = array();
            $pic = getThemeFile('title_off_0' . $i . '.gif');
            if (empty($pic)) $pic = $theme . 'title_off_0' . $i . '.gif?2017';
            $ary[$i][0] = $pic;

            $pic = getThemeFile('title_on_0' . $i . '.gif');
            if (empty($pic)) $pic = $theme . 'title_on_0' . $i . '.gif?2017';
            $ary[$i][1] = $pic;
        }
        $js = <<< EOF
    var ThemePath = "{$theme}";
    var nowTitle = new Array('');
    nowTitle[{$tabsCont}] = "{$tabsDef}";
    var tabsActs  = new Object();
{$tabsActs}
    var btmImgs = [
        [],
        [['{$ary[1][0]}'], ['{$ary[1][1]}']],
        [['{$ary[2][0]}'], ['{$ary[2][1]}']],
        [['{$ary[3][0]}'], ['{$ary[3][1]}']]
    ];

    function tabsSelect(val) {
        var obj = null;
        var idx = val;
        if ((typeof(tabsActs[val]) == "undefined") || (tabsActs[val] == null)) return false;

        obj = document.getElementById("TitleID" + nowTitle[1]);
        if (obj != null) tabsMouseEvent(obj, 0);

        obj = document.getElementById("TitleID" + idx);
        /* if (obj != null) tabsMouseEvent(obj, 2); */
        if (obj != null) obj.onclick();
    }

    function tabsMouseEvent(obj, envType) {
        var node = null, nodes = null, attr = null, idx = null;
        if ((typeof(obj) != "object") || (obj == null)) return false;

        attr = obj.getAttribute("MyAttr");
        idx  = obj.getAttribute("tabsIdx");
        if ((attr == null) || (idx == null) || (attr == nowTitle[idx])) return false;

        if ((envType == 2)) {    /* execute Click */
            if ((typeof(tabsActs[nowTitle[idx]]) == "object") && (tabsActs[nowTitle[idx]] != null)) {
                node = document.getElementById(tabsActs[nowTitle[idx]][0]);
                if (node != null) node.style.display = "none";
            }

            node = document.getElementById("TitleID" + nowTitle[idx]);
            nowTitle[idx] = attr;
            tabsMouseEvent(node, 0);

            if ((typeof(tabsActs[nowTitle[idx]]) == "object") && (tabsActs[nowTitle[idx]] != null)) {
                node = document.getElementById(tabsActs[nowTitle[idx]][0]);
                if (node != null) node.style.display = "";
                eval(tabsActs[nowTitle[idx]][1]);
            }
        /* return false; */
        }

        obj.style.backgroundImage = (envType ? ("url('" + btmImgs[2][1] + "')") : ("url('" + btmImgs[2][0] + "')"));
        node = document.getElementById("ImgL" + attr);
        if ((typeof(node) == "object") && (node != null)) {
            node.src = envType ? btmImgs[1][1] : btmImgs[1][0];
        }
        node = document.getElementById("ImgR" + attr);
        if ((typeof(node) == "object") && (node != null)) {
            node.src = envType ? btmImgs[3][1] : btmImgs[3][0];
        }
    }

EOF;

        if ($cnts > 1 && !$isOutput){
            showXHTML_script('inline', $js);
            $isOutput = true;
        } else if ($cnts > 1) {
            $js = <<< EOF
    nowTitle[{$tabsCont}] = "{$tabsDef}";
{$tabsActs}
EOF;
            showXHTML_script('inline', $js);
        }

        $tabsCont++;
    }

    /**
     * 秀出 tab 的外圍表格邊框
     *
     * @param $tabs        各 tab 的標籤
     * @param $default    載入後的內定標籤
     * @param $form_id    form ID 屬性
     * @param $form_extra    form 的額外屬性
     * @param $display_css  擴充table和tab裡的style css內容
     */
    function showXHTML_tabFrame_B($tabs, $default=1, $form_id=null, $table_id=null, $form_extra='style="display: inline"', $isDragable=false, $showHelp=true, $display_css='') {
        static $firstCall=true;

        if ($isDragable && $firstCall){
            showXHTML_script('inline', "
    function myWidth(obj){
        return obj.getElementsByTagName('table')[0].offsetWidth;
    }

    function myHeight(obj){
        return obj.getElementsByTagName('table')[0].offsetHeight;
    }
");
            $firstCall = false;
        }

        showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" '.((isset($display_css['table']))?$display_css['table']:'').' id="' . $table_id . '" ' .
                  ($isDragable? "style=\"position: absolute; display: none\" onMouseDown=\"dragLayer(this.id, 0, 0, myWidth(this), myHeight(this));\" onclick=\"event.cancelBubble=true;\"" : '')
                 );
  showXHTML_tr_B(((isset($display_css['tab']))?$display_css['tab']:''));
        showXHTML_td_B();
        showXHTML_tabs($tabs, $default, $isDragable, $showHelp);
        showXHTML_td_E();
  showXHTML_tr_E();
  showXHTML_tr_B();
    showXHTML_td_B('valign="top" class="bg01"');
    
    // $tabs[0][2] 代表是否需要FORM元素，當為0時，代表不要FORM元素
      if ($tabs[0][2] !== 0) {
        showXHTML_form_B($form_extra, $form_id);
      }
    }

    /**
     * 秀出 tab 的外圍表格邊框 結束
     */
    function showXHTML_tabFrame_E($needForm = 1){
            if ($needForm === 1) {
                showXHTML_form_E();
            }
            showXHTML_td_E();
          showXHTML_tr_E();
        showXHTML_table_E();
    }

    /**
     * 取得檔案路徑
     * @param string $name : 檔名
     * @return string 完整路徑含檔名
     *
     * 取得路徑的優先順序
     * 課程 (或個人) -> 學校 -> 系統
     * 個人：/user/1/2/user/theme/ (目前沒作用)
     * 課程：/base/$sid/course/$csid/theme/
     * 班級：/base/$sid/class/$caid/theme/ (目前沒作用)
     * 學校：/base/$sid/theme/learn (學生環境)
     * 學校：/base/$sid/theme/teach (教師環境)
     * 學校：/base/$sid/theme/direct (導師環境)
     **/
    function getThemeFile($name) {
        global $sysSession;

        if (defined('DEMO')) return '';
        $file = '';
        if ($sysSession->env == 'learn') {
            if (empty($sysSession->course_id)) {
                // 個人 (需要修改)
                include_once(sysDocumentRoot . '/lib/username.php');
                $path = MakeUserDir($sysSession->username);
                $file = $path . '/theme/' . $name;
                if (!is_dir(dirname($file))) @mkdir(dirname($file), 0770);
                if (file_exists($file) && is_file($file)) return $file;
            } else {
                // 課程
                $file = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/theme/{$name}";
                if (!is_dir(dirname($file))) @mkdir(dirname($file), 0770);
                if (file_exists($file) && is_file($file)) return str_replace(sysDocumentRoot, '', $file);
            }
        }
        // 學校
        $file = sysDocumentRoot . "/base/{$sysSession->school_id}/theme/{$sysSession->env}/{$name}";
        if (!is_dir(dirname(dirname($file)))) @mkdir(dirname(dirname($file)), 0770);
        if (!is_dir(dirname($file))) @mkdir(dirname($file), 0770);
        if (file_exists($file) && is_file($file)) return str_replace(sysDocumentRoot, '', $file);

        // 系統
        $file = sysDocumentRoot . "/theme/{$sysSession->theme}/{$sysSession->env}/{$name}";
        if (file_exists($file) && is_file($file)) return str_replace(sysDocumentRoot, '', $file);

        return '';
    }

    /**
     * 將時間字串的前導 0 之字色變為灰色
     *
     * @param string   $t  時間字串
     * @return string  前導 0 之字色變為灰色的時間字串
     */
    function zero2gray($t)
    {
        $pad = '00:00:00';
        for($i=1; $i<9; $i++)
            if (strncmp($pad, $t, $i) !== 0)
            {
                if ($i > 1)
                    return sprintf('<font color="#A0A0A0">%s</font>%s', substr($t, 0, $i-1), substr($t, $i-1));
                else
                    return $t;
            }
        return sprintf('<font color="#A0A0A0">%s</font>', $t);
    }

    /**
     * 將時間秒數轉為時間字串
     *
     * @param int   $sec  時間秒數
     * @return string  時間字串
     */
    function sec2timestamp($sec)
    {
        $pad = '00:00:00';
        $tail = strrchr($sec, '.');
        $s = (int)floor($sec);
        if ($s == 0) return 0;
        $result = '';
        for($i=0; $i<2; $i++)
        {
            $result = sprintf(':%02d', $s % 60) . $result;
            if (($s = (int)floor($s / 60)) == 0)
                if (($j = 8 - strlen($result)) > 1)
                    return substr($pad, 0, $j) . $result . $tail;
                else
                    return $result . $tail;
        }
        return sprintf('%02d%s%s', $s, $result, $tail);
    }
        
    /*
     * 取檔案最後異動時間
     */
    function getFileModifyTime($path) {
        echo filemtime(sysDocumentRoot . $path);
    }
?>

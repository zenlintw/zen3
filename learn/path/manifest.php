<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lang/learn_path.php');
    require_once(sysDocumentRoot . '/lang/mooc_notebook.php');

    if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    $icon = array();
    for ($i = 1; $i <= 7; $i++)
    {
        $src = getThemeFile($i . '.gif');
        $icon[$i] = empty($src) ? sprintf('/learn/path/icon/%d.gif', $i) : $src;
    }

    $pw = 12;
    $tl = '';
    $pd = '';
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        // 啟用 MOOC
        $pw = 0;
        $tl = ' left: 0;';
        $pd = ' style="padding: 0;"';
    }
    
    if (isset($_GET['cid'])) {
      $cid = $_GET['cid'];
    } else {
        $cid = $sysSession->course_id;
    }
?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=10, IE=8">
<?php
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
?>
<STYLE TYPE="text/css">
<!--
a:link { color: #000000; text-decoration: none }
a:active { color: #000000; text-decoration: none }
a:visited { color: #000000; text-decoration: none }
a:hover { color: #000000; text-decoration: none; position: relative;}
//-->
</STYLE>
<script>
var obj = window.scrollbars;
if ((typeof(obj) == "object") && (obj.visible == true)) {
    obj.visible = false;
}

function adjustFrameHeight(){
<?php
    if (isMobileBrowser()) {
        echo <<< BOF
    var
        node = document.getElementById('treePanel'),
        tree = document.getElementById('pathtree'),
        doc = null;

        node.style.overflowY = 'visible';

        doc = (tree.contentWindow || tree.contentDocument);
        if (doc.document) {
            doc = doc.document;
        }
        node.style.height = doc.body.clientHeight + 35 + 'px';
BOF;
    } else {
?>
    if (navigator.userAgent.indexOf('MSIE') > -1)
    {
        var height = document.body.clientHeight - document.getElementById('learn-progress').clientHeight - 5;
        if (height > 0) document.getElementById('treePanel').style.height = (document.getElementById('CGroup').style.height = height) + 34;
        var width = document.body.clientWidth - 45 - <?=$pw?>;
        if (width > 0){
            document.getElementById('CGroup').style.width = (width-2) + 'px';
            document.getElementById('treePanel').style.width = width + 'px';
            document.getElementById('learn-progress').style.width = (width-20) + 'px';
        }
    }
    else
    {
        if (document.getElementById('treePanel') !== null) {
            document.getElementById('CGroup').style.height = (document.getElementById('treePanel').style.height = window.innerHeight - document.getElementById('learn-progress').clientHeight);
            document.getElementById('CGroup').style.width = window.innerWidth - 46;
            document.getElementById('learn-progress').style.width = window.innerWidth - 66;
            document.getElementById('treePanel').style.width = window.innerWidth - 46 - <?=$pw?>;
        }
    }
<?php
    } // End if (!isMobileBrowser())
?>
}

function mm(obj, idx, isOn){
    if (obj.src.search(/-2\.gif$/) != -1) return;
    obj.src = obj.src.substring(0,obj.src.lastIndexOf('/')+2) + (isOn ? '-1.gif' : '.gif');
}

/* 控制是否提供上下移動 */
function disable_control(val) {
    /* document.getElementById('tdExpand').style.display = val ? '' : 'none';
    document.getElementById('tdPrev').style.display   = val ? '' : 'none';
    document.getElementById('tdNext').style.display   = val ? '' : 'none';
    document.getElementById('tdPrev1').style.display  = val ? '' : 'none';
    document.getElementById('tdNext1').style.display  = val ? '' : 'none';
     */
    document.getElementById('backNodeBtn1').style.display  = val ? '' : 'none';
    document.getElementById('nextNodeBtn1').style.display  = val ? '' : 'none';
}

window.onresize=function(){ adjustFrameHeight(); };

window.unload=function(){parent.s_catalog.scrolling = 'auto';};
</script>
<script type="text/javascript" language="javascript" lang="zh-tw" src="/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" language="javascript">
    /* 筆記用 */
    var cticket = '<?php echo htmlspecialchars($_COOKIE['idx'])?>',
        username = '<?php echo $sysSession->username?>',
        nowlang = '<?php echo $sysSession->lang?>',
        msg = <?php echo json_encode($MSG)?>;
</script>    
<script type="text/javascript" language="javascript" src="/public/js/notebook/gotoclass.js?<?php getFileModifyTime('/public/js/notebook/gotoclass.js');?>"></script>
</script>
<link href="/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<link href="/public/css/common.css?<?php getFileModifyTime('/public/css/common.css');?>" rel="stylesheet" />
<link href="/public/css/cour_path.css?<?php getFileModifyTime('/public/css/cour_path.css');?>" rel="stylesheet" />
<base target="c_main">
</head>
<body style="background-color: #ececec;">
    <!--學生環境-自訂課程-上課去-->
    <div class="box1" style="margin: 0; padding: 0; height: 100%;">
        <div class="abreast">
            <div id="treePanel" class="abreast-cell" style="padding: 0; width: 220px; border-right: 0;">
                <div id="learn-progress" style="display: table; padding: 10px 10px 0 10px; vertical-align: middle;">
                    <div class="progress progress-warning progress-striped" style="display: table-cell; width: 100%;">
                        <div class="bar" style="width: 0%"></div>
                    </div>
                    <div id="progressBar-text" style="display: table-cell; vertical-align: middle; font-weight: bold; padding-left: 0.5em;">
                        0%
                    </div>
                </div>
                <div id="CGroup" style="height: 90%;">
                    <iframe width="100%" height="100%" frameborder="0" border="0" scrolling="auto" name="pathtree" id="pathtree" src="pathtree.php?cid=<?=$cid?>"></iframe>
                </div>
            </div>
            <div class="abreast-cell" style="padding: 0; width: 1px; background: #FFFFFF; border-right: 1px solid #DDDDDD;"></div>
            <div id="toolbar" class="abreast-cell" style="text-align: center; padding: 0; width: 42px; border-right: 0;">
                <div id="minBtn" class="icon-close-hr" onclick="pathtree.minimize(); return false;" title="<?php echo $MSG['btn_minimize'][$sysSession->lang]; ?>" style="margin-top: 5px;"></div>
                <div id="backNodeBtn1" onclick="pathtree.nextStep(-1); return false;" title="<?php echo $MSG['btn_prev'][$sysSession->lang]; ?>" style="margin-top: 5px; display: none;"><div class="icon-up"></div></div>
                <div id="nextNodeBtn1" onclick="pathtree.nextStep(1); return false;" title="<?php echo $MSG['btn_next'][$sysSession->lang]; ?>" style="margin-top: 5px; display: none;"><div class="icon-down"></div></div>
                <!--<div onclick="pathtree.notebook(); return false;" title="<?php echo $MSG['btn_notebook'][$sysSession->lang]; ?>" style="margin-top: 5px;"><div class="icon-note"></div></div>-->
                <a name="course_notebook" title="<?php echo $MSG['btn_notebook'][$sysSession->lang]; ?>"><div class="icon-note" style="margin-top: 5px;"></div></a>
                <form name="goto" target="course_notebook" action="/message/m_notebook.php" method="POST" style="display: none;">
                    <input type="hidden" name="cid" value="<?php echo $sysSession->course_id;?>">
                    <input type="hidden" name="cname" value="<?php echo $sysSession->course_name;?>">
                    <input type="hidden" name="fid">
                    <input type="hidden" name="fname">
                </form>
                <?php
                    if (defined('enableQuickReview') && enableQuickReview == true) {
                        echo '<div onclick="parent.s_sysbar.goPersonal(\'SYS_06_01_013\'); return false;" title="'.$MSG['quick_review'][$sysSession->lang].'" style="margin-top: 5px;"><div class="icon-quickreview"></div></div>';
                    }                    
                ?>
                <div onclick="pathtree.expandingAll(); return false;" title="<?php echo $MSG['btn_expand'][$sysSession->lang];?>" style="margin-top: 5px;">
                    <div class="icon-expandctonract"></div>
                </div>
            </div>
            <div class="abreast-cell" style="padding: 0; width: 1px; background: #FFFFFF; border-right: 1px solid #DDDDDD;"></div>
        </div>
    </div>
    
</body>
<?php
/*
<body style="margin: 0; margin-top: 4px; overflow: hidden;" class="cssTbBodyBg">

<table border="0" cellpadding="0" cellspacing="0" id="toolbar">
  <tr>
    <td align="right" width="200">
      <table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="24" id="tdNote"  ><a href="javascript:;" onclick="pathtree.notebook();     return false;" title="<?php echo $MSG['btn_notebook'][$sysSession->lang];?>"><img border="0" src="<?php echo $icon[7]; ?>" width="19" height="19" onmouseover="mm(this, 6, true);" onmouseout="mm(this, 6, false);"></a></td>
          <td width="24" id="tdExpand"><a href="javascript:;" onclick="pathtree.expandingAll(); return false;" title="<?php echo $MSG['btn_expand'][$sysSession->lang];  ?>"><img border="0" src="<?php echo $icon[2]; ?>" width="19" height="19" onmouseover="mm(this, 1, true);" onmouseout="mm(this, 1, false);"></a></td>
          <td width="24" id="tdPrev"  ><a id="backNodeBtn1" href="javascript:;" onclick="pathtree.nextStep(-1);   return false;" title="<?php echo $MSG['btn_prev'][$sysSession->lang];    ?>"><img border="0" src="<?php echo $icon[4]; ?>" width="19" height="19" onmouseover="mm(this, 3, true);" onmouseout="mm(this, 3, false);"></a></td>
          <td width="24" id="tdNext"  ><a id="nextNodeBtn1" href="javascript:;" onclick="pathtree.nextStep(1);    return false;" title="<?php echo $MSG['btn_next'][$sysSession->lang];    ?>"><img border="0" src="<?php echo $icon[3]; ?>" width="19" height="19" onmouseover="mm(this, 2, true);" onmouseout="mm(this, 2, false);"></a></td>
          <td width="24" id="tdMin"   ><a href="javascript:;" onclick="pathtree.minimize();     return false;" title="<?php echo $MSG['btn_minimize'][$sysSession->lang];?>"><img border="0" src="<?php echo $icon[5]; ?>" width="19" height="19" onmouseover="mm(this, 4, true);" onmouseout="mm(this, 4, false);"></a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="display: none" id="toolbar1">
  <tr>
    <td>
      <table border="0" cellpadding="0" cellspacing="0">
        <tr><td height="24" id="tdMin1" ><a href="javascript:;" onclick="pathtree.minimize();   return false;" title="<?php echo $MSG['btn_maximize'][$sysSession->lang];?>"><img border="0" src="<?php echo $icon[6]; ?>" width="19" height="19" onmouseover="mm(this, 5, true);" onmouseout="mm(this, 5, false);"></a></td></tr>
        <tr><td height="24" id="tdNext1"><a id="nextNodeBtn2" href="javascript:;" onclick="pathtree.nextStep(1);  return false;" title="<?php echo $MSG['btn_next'][$sysSession->lang];    ?>"><img border="0" src="<?php echo $icon[3]; ?>" width="19" height="19" onmouseover="mm(this, 2, true);" onmouseout="mm(this, 2, false);"></a></td></tr>
        <tr><td height="24" id="tdPrev1"><a id="backNodeBtn2" href="javascript:;" onclick="pathtree.nextStep(-1); return false;" title="<?php echo $MSG['btn_prev'][$sysSession->lang];    ?>"><img border="0" src="<?php echo $icon[4]; ?>" width="19" height="19" onmouseover="mm(this, 3, true);" onmouseout="mm(this, 3, false);"></a></td></tr>
        <tr><td height="24" id="tdNote1"><a href="javascript:;" onclick="pathtree.notebook();   return false;" title="<?php echo $MSG['btn_notebook'][$sysSession->lang];?>"><img border="0" src="<?php echo $icon[7]; ?>" width="19" height="19" onmouseover="mm(this, 6, true);" onmouseout="mm(this, 6, false);"></a></td></tr>
      </table>
    </td>
  </tr>
</table>
<div class="cssToolbar" id="treePanel" style="height: 504px; width: 190px; overflow: hidden;<?=$tl?>">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
      <tr class="cssTr">
        <td class="cssTd">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr class="cssTrEvn">
                    <td nowrap valign="top" width="3"><img border="0" src="/theme/<?php echo $sysSession->theme;?>/<?php echo $sysSession->env;?>/cl2.gif" width="3" height="3"></td>
                    <td align="right" nowrap valign="top"><img border="0" src="/theme/<?php echo $sysSession->theme;?>/<?php echo $sysSession->env;?>/cl3.gif" width="3" height="3"></td>
                </tr>
                <tr class="cssTrEvn">
                    <td class="cssTd" colspan="2" nowrap>&nbsp;<img align="absMiddle" border="0" src="/theme/<?php echo $sysSession->theme;?>/<?php echo $sysSession->env;?>/icon_book.gif" width="22" height="12">&nbsp;Learning Path&nbsp;</td>
                </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr class="cssTr">
       <td class="cssTd">
          <table border="0" cellpadding="0" cellspacing="0" class="cssTbTable" width="100%">
            <tbody>
              <tr class="cssTbTr">
                <td class="cssTbTd" align="right" id="CGroup" nowrap<?=$pd?>><iframe width="100%" height="100%" frameborder="0" border="0" scrolling="auto" name="pathtree" id="pathtree" src="pathtree.php"></iframe></td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</div>
<script>adjustFrameHeight();</script>
</body>
*/
?>
</html>
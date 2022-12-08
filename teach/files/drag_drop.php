<?php
require_once(sysDocumentRoot . '/lang/files_manager.php');
require_once(sysDocumentRoot . '/lib/quota.php');
//echo '<pre>';
//var_dump('課程編號');
//var_dump($sysSession->course_id);
//echo '</pre>';

// 更新quota資訊
getCalQuota($sysSession->course_id, $real_used, $quota_limit);
setQuota($sysSession->course_id, $real_used);

// 取已使用容量
getQuota($sysSession->course_id, $real_used, $quota_limit);
$real_used_mb = $GLOBALS['real_used'];

// 是否超過使用空間限制
$isExceed = true;
if ($GLOBALS['real_used'] < $GLOBALS['quota_limit']) {
    $isExceed = false;
}
//echo '<pre>';
//var_dump('取已使用容量KB');
//var_dump($real_used_mb);
//var_dump('空間限制KB');
//var_dump($GLOBALS['quota_limit']);
//var_dump('是否超過使用空間限制');
//var_dump($isExceed);
//echo '</pre>';

// 上傳目的地
if (isset($_GET['currPath']) && (htmlspecialchars($_GET['currPath']) != '/')) {
    $_GET['currPath'] = rawurlencode(htmlspecialchars($_GET['currPath']));
} else {
    $_GET['currPath'] = '/';
}

// 單一檔案上傳上限
$uploadMaxFilesize = ini_get('upload_max_filesize');
switch(substr($uploadMaxFilesize, -1, 1)) {
    case 'K':
        $transform = 1024;
        break;
    
    case 'M':
        $transform = 1024 * 1024;
        break;
    
    case 'G':
        $transform = 1024 * 1024 * 1024;
        break;
}
$uploadMaxFilesize = substr($uploadMaxFilesize, 0, -1) * $transform;
//echo '<pre>';
//var_dump('單一檔案上傳上限B');
//var_dump($uploadMaxFilesize);
//echo '</pre>';
?>
<meta charset="utf-8">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<link rel="stylesheet" href="/lib/jQuery-File-Upload/css/jquery.fileupload.css">
<link rel="stylesheet" href="/theme/default/teach/wm.css">
<link rel="stylesheet" href="/theme/default/fancybox/jquery.fancybox.css">

<input id="fileupload" type="file" name="files[]" multiple style="display: none;">

<a id="share-ln" href="#inline-ln" style="display: none;" title="<?php echo $MSG['create_subnode'][$sysSession->lang];?>"><div class="ln">Open fancybox</div></a>
<div id="inline-ln" class="inline-ln" style="display: none;">
    <!--
    <div class="alert alert-danger" style="line-height: 0.5em; display: none; font-family: 微軟正黑體, Tahoma">
        <button type="button" class="close" data-dismiss="alert" style="position: relative; top: -0.3em;">×</button>
        <div></div>
    </div>
    -->
    <div class="font01"><?php echo $MSG['node_root'][$sysSession->lang];?>: <span id="node-root-name" style="font-weight: bold;"></span></div>
    <div class="font01" style="margin-top: 1em;"><?php echo $MSG['overall_progress'][$sysSession->lang];?></div>
    <div id="progress" class="progress progress-bar-striped" style="background-color: #CFCFCF">
        <div class="progress-bar progress-bar-success progress-bar-striped active" style="width: 0%;"></div>
    </div>
    <div class="font01"><?php echo $MSG['upload_file_list'][$sysSession->lang];?></div>
    <div>
        <table id="files-tables" width="100%" border="1" cellspacing="1" cellpadding="3" class="cssTable" >
            <tr class="cssTrHead" style="text-align:center; height: 2em;">
                <th style="text-align:center;"><?php echo $MSG['no'][$sysSession->lang];?></th>
                <th style="text-align:center;"><?php echo $MSG['filename'][$sysSession->lang];?></th>
                <th style="text-align:center;"><?php echo $MSG['file_size'][$sysSession->lang];?></th>
                <th style="text-align:center;"><?php echo $MSG['upload_progress'][$sysSession->lang];?></th>
                <th style="text-align:center;"><?php echo $MSG['upload_actions'][$sysSession->lang];?></th>
            </tr>
        </table>
        <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr class="cssTrEvn" style="text-align:center; height: 2em;">
                <td style="text-align:right;" colspan="5"><span><?php echo $MSG['total_file_size'][$sysSession->lang];?></span><span id="total-size">0</span><span> KB</span></td>
            </tr>
        </table>
    </div>
    <div>
        <span id="uploadStep2">
            <input <?php if ($isExceed) {echo 'disabled';}?> type="button" id="start" value="<?php echo $MSG['start_trafer'][$sysSession->lang];?>" class="btn btn-success fileinput-button" />
        </span>
    </div>
</div>
<script>
    var currPath = '<?php echo $_GET['currPath'];?>';
    var upload_max_filesize = <?php echo $uploadMaxFilesize;?>;
    
    // 取已使用容量
    var used_size = <?php echo $real_used_mb;?>;
    // 取上限容量
    var limit_size = <?php echo $GLOBALS['quota_limit'];?>;
//    console.log(used_size);
//    console.log(limit_size);
    // 上傳總大小就是可用的剩餘容量（上限容量 - 已使用容量）
    var post_max_size = limit_size - used_size;
//    console.log(post_max_size);
    <?php
    echo "var msg = " . json_encode($MSG) . ";";
    echo "var nowlang = '" . $sysSession->lang . "';";
    ?>
    
    var CHOOSE_FILES_FIRST = '<?php echo $MSG['choose_files_first'][$sysSession->lang];?>';
    var CANCEL_UPLOAD = '<?php echo $MSG['cancel_upload'][$sysSession->lang];?>';
    var FILE_SIZE_EXCEEDS_LIMIT = '<?php echo $MSG['file_size_exceeds_limit'][$sysSession->lang];?>';
    var FILE_SIZE_EXCEEDS_TOTALSIZE = '<?php echo $MSG['file_size_exceeds_totalsize'][$sysSession->lang];?>';
    var HAS_BEEN_UPLOADED = '<?php echo $MSG['has_been_uploaded'][$sysSession->lang];?>';
    var UPLOAD_COMPLETE = '<?php echo $MSG['upload_complete'][$sysSession->lang];?>';
</script>
<!--
<script src="/teach/files/drag_drop.js"></script>
-->
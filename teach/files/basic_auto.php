<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/files_manager.php');
require_once(sysDocumentRoot . '/lib/quota.php');

header("X-UA-Compatible: IE=edge");
if (isset($_GET['currPath']) && ($_GET['currPath'] != '/')) {
    $_GET['currPath'] = rawurlencode($_GET['currPath']);
}
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

if ($_GET['currPath']=='temp_learn_homework') $type = 'homework';
if ($_GET['currPath']=='temp_learn_questionnaire') $type = 'questionnaire';
$sysSession->course_id = $sysConn->GetOne("select course_id from WM_qti_{$type}_test where exam_id={$_GET['serial_number']}");
$sysSession->restore();

// 更新quota資訊
getCalQuota($sysSession->course_id, $real_used, $quota_limit);
setQuota($sysSession->course_id, $real_used);

$basePath = sprintf('%s/base/%05d/course/%08d/content', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id);
getQuota($sysSession->course_id, $real_used, $quota_limit);
$real_used_mb = $GLOBALS['real_used'];
//echo '<pre>';
//var_dump($GLOBALS['real_used'], $GLOBALS['quota_limit']);
//echo '</pre>';
//echo '<pre>';
//var_dump('取已使用容量KB');
//var_dump($real_used_mb);
//var_dump('空間限制KB');
//var_dump($GLOBALS['quota_limit']);
//var_dump('是否超過使用空間限制');
//var_dump($isExceed);
//echo '</pre>';


$isMobile = isMobileBrowser() ? '1' : '0';

if ($isMobile === '1') {
	
	function glob_recursive($pattern, $flags = 0)
	{
	    $files = glob($pattern, $flags);
	    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
	      $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
	    }
	    return $files;
	}
	
    $movieNum = 1;
    $imageNum = 1;
    
    

    $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09d/%s/', $sysSession->school_id, $sysSession->course_id, $type, $_GET['serial_number'], $sysSession->username);

    // 讀取指令路徑有哪些檔案
    $files = glob_recursive($save_path . '*');
    foreach ($files as $v) {
        if (preg_match("/MOVIE\((\d+)\).MOV$/", pathinfo($v, PATHINFO_BASENAME), $match)) {
            if ($match[1] >= $movieNum) {
                $movieNum = $match[1] + 1;
            }
        }
        if (preg_match("/IMAGE\((\d+)\).JPEG$/", pathinfo($v, PATHINFO_BASENAME), $match)) {
            if ($match[1] >= $imageNum) {
                $imageNum = $match[1] + 1;
            }
        }
    }

} else {
	$imageNum = 0;
}


?>
<!DOCTYPE HTML>
<html lang="en" style="height: 96%; display: table; width: 98%;" >
    <head>
        <meta charset="utf-8">
        <title><?php echo $MSG['upload_file'][$sysSession->lang];?></title>
        <meta name="description" content="File Upload widget with multiple file selection, drag&amp;drop support and progress bar for jQuery. Supports cross-domain, chunked and resumable file uploads. Works with any server-side platform (PHP, Python, Ruby on Rails, Java, Node.js, Go etc.) that supports standard HTML form file uploads.">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="/lib/jQuery-File-Upload/css/jquery.fileupload.css">
        <link rel="stylesheet" href="/theme/default/teach/wm.css">
    </head>
    <body style="height: 97%;">
        <div>
            <?php
            $isExceed = true;
            if ($GLOBALS['real_used'] < $GLOBALS['quota_limit']) {
                $isExceed = false;
            }
            ?>
            <?php if ($isExceed) {?>
            <div class="alert alert-danger" style="padding: 8px; z-index: 2; position: relative;">
                <button type="button" class="close" data-dismiss="alert">×</button><?php echo $MSG['quota_is_full'][$sysSession->lang];?>
            </div>
            <?php }?>
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span <?php if ($isExceed) {echo 'disabled';}?> id="uploadStep1" class="btn btn-success fileinput-button" style="z-index: 2;">
                <span><?php echo $MSG['select_files'][$sysSession->lang];?></span>
                <!-- The file input field used as target for the file upload widget -->
                <?php if ($isExceed === false) {?><input id="fileupload" type="file" name="files[]" multiple><?php }?>
            </span>
            <span><?php echo $MSG['note_multi_file_upload'][$sysSession->lang];?></span>
            <span id="uploadStep2" style="display:none;">
                <input <?php if ($isExceed) {echo 'disabled';}?> type="button" id="start" value="<?php echo $MSG['step2'][$sysSession->lang];?><?php echo $MSG['start_trafer'][$sysSession->lang];?>" class="btn btn-success fileinput-button" style="z-index: 2;" <?php if ($isExceed === false) {?>onclick="doUploadSubmit(this);"<?php }?> />
            </span>
            <span id="uploadStep3" style="display:none;">
            <input type="button" id="start" value="<?php echo $MSG['reload_page'][$sysSession->lang];?>" class="btn btn-success fileinput-button" style="z-index: 2;" onclick="document.location.reload();" />
            </span>
            <div style="height: 0.8em;"></div>
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
        </div>
        <div id="drag-note" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 1; height: 100%; text-align: center; line-height: 4.4em; font-size: 6em;"></div>
        <script src="/lib/jquery/jquery_old.js"></script>
        <!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
        <script src="/lib/jQuery-File-Upload/js/vendor/jquery.ui.widget.js"></script>
        <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
        <script src="/lib/jQuery-File-Upload/js/jquery.iframe-transport.js"></script>
        <!-- The basic File Upload plugin -->
        <script src="/lib/jQuery-File-Upload/js/jquery.fileupload.js"></script>
        <script src="/theme/default/bootstrap/js/bootstrap.min.js"></script>
        <script>
            var DROP_FILES_HERE = '<?php echo $MSG['drop_files_here'][$sysSession->lang];?>';
            var REPLOAD_PLEASE = '<?php echo $MSG['repload_please'][$sysSession->lang];?>';
        </script>
        <script src="/teach/files/basic.js"></script>
        <script>
            /*jslint unparam: true */
            /*global window, $ */
            <?php 
                echo "var currPath='{$_GET['currPath']}';\n";
                echo "var serial_number='{$_GET['serial_number']}';\n";
                echo "var upload_max_filesize = {$uploadMaxFilesize};\n";
            ?>
            // 取右上角容量資訊
//            var parent_quota_info = $('#total-filesize td', window.parent.document).text();
//            var pattern = /([\d.]*) ([KM])B \(.* ([\d.]*) ([KM])B\)/;
//            var matches = parent_quota_info.match(pattern);
            // var used_size = matches[1];
            var used_size = <?php echo $real_used_mb;?>;
//            var used_filesize_unit = matches[2];
//            var limit_size = matches[3];
            var limit_size = <?php echo $GLOBALS['quota_limit'];?>;
//            var limit_filesize_unit = matches[4];

            var num = <?php echo $imageNum ?>;
            var isMobile = <?php echo $isMobile ?>;
            var newname;
            
            // 取已使用容量
//            if (used_filesize_unit === 'K') {
//            } else if (used_filesize_unit === 'M') {
//                used_size = used_size * 1024;
//            } else {
//                used_size = used_size * 1024 * 1024;
//            }
            
//            // 取上限容量
//            if (limit_filesize_unit === 'K') {
//            } else if (limit_filesize_unit === 'M') {
//                limit_size = limit_size * 1024;
//            } else {
//                limit_size = limit_size * 1024 * 1024;
//            }
//            console.log(used_size);
//            console.log(limit_size);
            // 上傳總大小就是可用的剩餘容量（上限容量 - 已使用容量）
            var post_max_size = limit_size - used_size;
//            console.log(post_max_size);
            
            function doUploadSubmit(submitButton) {
                if ($('.itemCancel').length === 0) {
                    alert('<?php echo $MSG['choose_files_first'][$sysSession->lang];?>');
                    return false;
                } else {
                    submitButton.disabled = true;
                    document.getElementById('uploadStep1').style.display = 'none';
                    document.getElementById('uploadStep2').style.display = 'none';
                    document.getElementById('uploadStep3').style.display = 'block';
    //                
                    // 清空
                    localStorage.setItem('total-upload-filesize', 0);
                }
            }
            
            var fileItemCount = 0;
            $(function () {                
                localStorage.setItem('total-upload-filesize', 0);
                
                'use strict';
                // Change this to the location of your server-side upload handler:
                var url = '/lib/jQuery-File-Upload/server/php/index.php?currPath='+currPath+'&fileOverride=1&serial_number=' + serial_number;
                $('#fileupload').fileupload({
                    url: url,
                    dataType: 'json',
                    autoUpload: false,
                    add: function (e, data) {
                    	var tpl = $('<tr class="cssTrEvn" style="text-align:center; height: 3em;">\
                                        <td class="itemNum" style="text-align:center;"></td>\n\
                                        <td class="itemFileName" style="text-align:left;"><div style="width: 19em; overflow: hidden;"></div></td>\n\
                                        <td class="itemFileSize" style="text-align:right;padding-right:5px;width: 8em;"></td>\n\
                                        <td class="itemProgress" style="text-align:center;width:101px;position: relative;">\n\
                                            <div class="bar progress-bar progress-bar-striped active" style="background-color:#428BCA;width: 0px;block:inline;text-align:center;color:white; height: 3em;">&nbsp;</div>\n\
                                            <div class="pro" style="position: relative; position: absolute; top: 9px; right: 22px;" /></div>\n\
                                        </td>\n\
                                        <td class="itemActions" style="text-align:center; width: 6em;"><button class="itemCancel" style="z-index: 2; position: relative;"><?php echo $MSG['remove'][$sysSession->lang];?></button></td>\n\
                                    </tr>');

                        if (isMobile) {
                            var pic = data.files[0].name;
                            var re = /^image.jp[e]?g$/i;
                            if (re.test(pic)){
                            	newname = 'IMAGE(' + num + ').JPEG';
                                num++;
                            } else {
                            	newname = data.files[0].name;
                            }
                        } else {
                        	newname = data.files[0].name;
                        }
                        data.formData = { 'new': newname };               
                                    
                    	// 檔名
                        tpl.find('.itemFileName div').text(newname).attr('title', newname);
                        
                        // 檔案大小
                        if (data.files[0].size >= 1024) {
                            tpl.find('.itemFileSize').html('<span>' + parseInt(data.files[0].size/1024)+'</span> KB');
                        }else{
                            tpl.find('.itemFileSize').html('<div style="display: none;"><span >' + parseInt(data.files[0].size/1024)+'</span> KB</div>' + data.files[0].size + ' B');
                        }
                        
                        // 單一檔案不超過系統上限，則記錄實際大小到 localStorage
                        if (data.files[0].size <= upload_max_filesize) {
                            localStorage.setItem('total-upload-filesize', parseInt(localStorage.getItem('total-upload-filesize'), 10) + data.files[0].size);
                        } 
                        
                        // 總上傳大小                  
                        var total_size = parseInt(localStorage.getItem('total-upload-filesize'), 10);
//                        console.log(total_size/1024);
//                        console.log(post_max_size);
                        
                        // 判斷單一檔案大小
                        if (data.files[0].size == 0) {
                            tpl.find('.itemProgress').text('<?php echo $MSG['file_0'][$sysSession->lang];?>');
                            tpl.find('.itemCancel').remove();
                            data.context = tpl.appendTo($("#files-tables"));
                        // 判斷總檔案大小
                        } else if ((total_size/1024) > post_max_size) {
                            tpl.find('.itemProgress').text('<?php echo $MSG['file_size_exceeds_totalsize'][$sysSession->lang];?>');
                            tpl.find('.itemCancel').remove();
                            data.context = tpl.appendTo($("#files-tables"));
                        } else {
                            var that = this;
                            $.getJSON('/lib/jQuery-File-Upload/server/php/index.php', {currPath:currPath, file: newname+Date.now(), serial_number: serial_number}, function (result) {
                                var file = result.file;
//                                console.log(result);
                                data.uploadedBytes = file && file.size;
                                tpl.find('.itemNum').text(++fileItemCount);
//                                console.log(data.uploadedBytes);
                                if (data.uploadedBytes == data.files[0].size) {
                                    tpl.find('.itemProgress').text('<?php echo $MSG['has_been_uploaded'][$sysSession->lang];?>');
                                    tpl.find('.itemActions').empty();
                                    data.context = tpl.appendTo($("#files-tables"));
                                }else{
                                    // 取消上傳
                                    tpl.find('.itemCancel').click(function(){
                                        

                                        $.ajax({
                                            'url': '/lib/jQuery-File-Upload/server/php/index.php?currPath='+currPath+'&serial_number=' + serial_number + '&file=' + newname,
                                            'type': 'DELETE',
                                            'dataType': 'json',
                                            'success': function(res) {
                                                console.log(res);
                                                if (res) {
                                                    tpl.fadeOut(function(){
                                                        tpl.remove();
                                                        $(window.parent.document).find('#homeworkForm :input[value="' + newname + '"]').remove();
                                                    });
                                                } else {
                                                    tpl.fadeOut(function(){
                                                        tpl.find('.itemActions').text('取消失敗');
                                                    });
                                                }
                                            },
                                            'error': function(res){
                                            	console.log(res);
                                            }
                                        });
                                    
                                        // 新上傳的檔案總大小
                                        $('#total-size').text(parseInt($('#total-size').text()) - parseInt(tpl.find('.itemFileSize').find('span').text()));
                                        
                                        
                                    });
                                    data.context = tpl.appendTo($("#files-tables"));
                                    
                                    // 新上傳的檔案總大小
                                    $('#total-size').text(parseInt($('#total-size').text()) + parseInt(data.files[0].size/1024));
                                    
                                    // 上傳後立即開始上傳
                                    if ($('.itemCancel').length === 0) {
                                        return false;
                                    } else {
                                        console.log(data);
                                        var jqXHR = data.submit();
                                    }
                                    $.blueimp.fileupload.prototype.options.add.call(that, e, data);
                                }
                            });
                        }
                    },
                    maxRetries: 100,
                    retryTimeout: 500,
                    fail: function (e, data) {
                        console.log('fail!!');
                        console.log(e);
//                        console.log(data);
                        // jQuery Widget Factory uses "namespace-widgetname" since version 1.10.0:
                        var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload'),
                            retries = data.context.data('retries') || 0,
                            retry = function () {
                                $.getJSON('/lib/jQuery-File-Upload/server/php/index.php', {currPath:currPath, file: newname, serial_number: serial_number})
                                    .done(function (result) {
                                        var file = result.file;
                                        data.uploadedBytes = file && file.size;
                                        // clear the previous data:
                                        data.data = null;
                                        data.submit();
                                    })
                                    .fail(function () {
                                        fu._trigger('fail', e, data);
                                    });
                            };
                        if (data.errorThrown !== 'abort' &&
                                data.uploadedBytes < data.files[0].size &&
                                retries < fu.options.maxRetries) {
                            retries += 1;
                            data.context.data('retries', retries);
                            window.setTimeout(retry, retries * fu.options.retryTimeout);
                            return;
                        }
                        data.context.removeData('retries');
//                        $.blueimp.fileupload.prototype
//                            .options.fail.call(this, e, data);
                    },
                    done: function (e, data) {
                        if (typeof(window.parent.jfileUploaded) != 'undefined') {
                        	window.parent.jfileUploaded = true;
                        }
                        /*
                        $.each(data.result.files, function (index, file) {
                            $('<p/>').text(file.name).appendTo('#files');
                        });
                        */
                    },
            
                    // 單一檔案進度
                    progress: function(e, data){
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        data.context.find('.pro').text(progress + " %").change();
                        data.context.find('.bar').css('width', progress + 'px');
                        if (progress === 100) {
                            data.context.find('.bar').removeClass('active');
                            // $('.itemCancel').remove();
                            // 上傳成功檔案列表
                            if (isMobile) {
                                $(window.parent.document).find('#homeworkForm').append('<input type="hidden" name="html5_files[]" value="' + newname + '">');
                            } else {
                            	$(window.parent.document).find('#homeworkForm').append('<input type="hidden" name="html5_files[]" value="' + data.files[0].name + '">');
                            }
                        }    
                    },
                    
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
 
                        if (progress === 100) {
                            $('.container').prepend('<div class="alert alert-success" style="padding: 8px; z-index: 2; position: relative;"><button type="button" class="close" data-dismiss="alert">×</button><?php echo $MSG['upload_complete'][$sysSession->lang];?></div>');
                            
                            // 更新使用容量
                            <?php 
                                echo "var real_used_mb={$real_used_mb};\n";
                            ?>
                            var parent_quota_info = $('#total-filesize td', window.parent.document).text();
                            var start = parent_quota_info.indexOf(' ');
                            var end = parent_quota_info.indexOf('(');
                            var pattern = parent_quota_info.substr(start, end - start);
//                            console.log(real_used_mb);
//                            console.log(data.loaded/1024);
//                            console.log(Math.round(real_used_mb / 1024 * 100)/100);
//                            console.log(Math.round(data.loaded / 1024 / 1024 * 100) / 100);
                            var total_size = Math.round(((Math.round(real_used_mb / 1024 * 100)/100) + (Math.round(data.loaded / 1024 / 1024 * 100) / 100)) *100)/100 + ' MB';
                            parent_quota_info = parent_quota_info.replace(pattern, '  ' + total_size + ' ');
                            $('#total-filesize td', window.parent.document).text(parent_quota_info);
                            
                            
                        }  
                    },
                        
                    destroy: function (e, data) {
                    }
                });
            
                $('#fileupload').fileupload({maxChunkSize: 1000000})
                .on('fileuploadchunksend', function (e, data) {})
                .on('fileuploadchunkdone', function (e, data) {})
                .on('fileuploadchunkfail', function (e, data) {})
                .on('fileuploaddestroy', function (e, data) {})
                .on('fileuploadchunkalways', function (e, data) {})
                .on('fileuploadfail', function (e, data) {console.log(data.files[0].name)});
            });
        </script>
    </body>
</html>
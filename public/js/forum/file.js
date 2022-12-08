/**
 * 顯示上傳檔案的錯誤訊息
 * @param msg 訊息
 */
function showErrorMsg(msg) {
    $('#message').show().find('span').append(msg + '<br>');
}

var
    $fu  = $('#uploadfile'),
    ps   = '<div class="progress progress-striped active"><div class="bar" style="width: 0;"></div></div>',
    html = [
        '<tr class="asset">',
        '  <td class="filename span3" style="word-break: break-all;"><div class="title"></div></td>',
        '  <td class="caption span2" style="word-break: break-all;"><span class="title"></span></td>',
        '  <td class="action">',
        '    <a href="#" class="delete"></a>',
        '    <div class="cancel btn btn-gray btn-small">' + msg['cancel_upload'][nowlang] + '</div>',
        '  </td>',
        '</tr>'
    ].join('');

// 上傳檔案按鈕
$('#btnBrowse').click(function () {
    $fu.find('.fileupload-buttonbar').find('input:file').click();
});

/**
 * 新增檔案到檔案列表 (上傳中的狀態)
 * @param options
 */
function addFile(options) {
    var $elem, $tr = $(html);

    // 檔案名稱
    $tr.data('filename', options.name);
    $tr.find('.filename')
        .find('.title').text(options.name).end()
        .append('<input type="hidden" name="originalFilename[]" value="' + options.name + '">')
        .append('<input type="hidden" name="diskFilename[]" value="' + options.name + '">');

    // 素材標題
    $elem = $('<input type="text" name="title[]" value="' + options.name + '">');
    $elem.change(function () {
        $(this).parent().find('.title').text(this.value);
    });

    $tr.find('.caption')
        .find('.title').text(options.name).hide().end()
        .append($elem.hide())
        .append(ps);

    // 動作
    $elem = $tr.find('.action');
    $elem.find('a').hide();
    $elem.find('.cancel').click(function () {
        options.jqXHR.abort();
    });

    // 動作 - 刪除
    $elem.find('.delete')
        .data('url', '')
        .click(function () {
            var url = $(this).data('url'), but = this, butParent;
            butParent = $(but).parent().parent();
            if (url !== '') {
                $(but).addClass('readyDelete');
                butParent.find('.deleteFlag').val('D');
                butParent.hide();
            } else {
                butParent.remove();
            }

            // 當刪到沒有檔案列表時，隱藏欄位列
            if ($('input[class="deleteFlag"][value!="D"]').length === 0){
                $('#fileList').hide();
            }
            return false;
        });

    $tr.appendTo(options.target);
    return $tr;
}

/**
 * 切換檔案在檔案列表的狀態 (顯示上傳完成的狀態)
 * @param $tr
 * @param data
 */
function showFile($tr, data) {
    var deleteFlag = 'A'; // 預設新增

    var $node;

    // 檔案名稱
    $tr.find('.filename')
        .find('.title').text(data.original_name).end();

    $tr.find('.filename')
        .find("input[name='originalFilename[]']").val(data.original_name);// 原始檔名

    $tr.find('.filename')
        .find("input[name='diskFilename[]']").val(data.name);// 新檔名

    $tr.find('.filename')
        .append('<input type="hidden" name="deleteFlag[]" value="' + deleteFlag + '" class="deleteFlag">');

    // 素材標題
    $tr.find('.caption').empty();

    // 動作
    $node = $tr.find('.action');

    // 動作 - 顯示按鈕
    $node.find('a').css('display', 'inline-block');
    $node.find('.cancel').remove();

    // 動作 - 刪除
    $node.find('.delete').data('url', data.deleteUrl);
}

$('#message').find('.close').click(function () {
    clearErrorMsg();
    $('#message').hide();
});

/**
 * 清除上傳檔案的錯誤訊息
 */
function clearErrorMsg() {
    $('#message').show().find('span').text('');
}

// 上傳檔案
$('#files').fileupload({
    'type': 'POST',
    'dataType': 'json',
    'autoUpload': false,
    'sequentialUploads': true,
    'singleFileUploads': true,
    'dropZone': $('.forum-table'),
    'add': function (e, data) {
        // 新增檔案
        var $tbody = $('#upload-result').find('.files');
        $('#fileList').show();
        $.each(data.files, function (idx, file) {
            file.context = addFile({
                'name'     : file.name,
                'kind'     : '#kind',
                'lang'     : '#langCode',
                'target'   : $tbody,
                'progress' : true,
                'deleteUrl': '',
                'jqXHR'    : data
            });
        });
        data.submit();
    },
    'progress': function (e, data) {
        // 檔案上傳進度
        $.each(data.files, function (idx, file) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            file.context.find('.caption').find('.bar').css('width', progress + '%');
            
            if (progress === 100) {
                if (window.console) {console.log(100);}
                file.context.find('.caption').remove();
                file.context.find('.filename').attr('colspan', 2);
                file.context.find('.filename').css('border-bottom', '1px solid #dddddd');
            }
        });

        // 判斷有沒有簽名檔，來決定列橫線粗細
        if ($('#extraFm').html() !== '') {
            $('#upload-result tbody tr').css('border-bottom', '1px solid #dddddd');
            $('#upload-result tbody tr:last-child').css('border-bottom', '2px solid #dddddd');
        }
    },
    'always': function (e, data) {
        // 上傳完成後
        var elem, message = [];
        if (data.textStatus === 'success') {
            $.each(data.files, function (idx, file) {
                if (data.result.files[idx].hasOwnProperty('error')) {
                    message.push(file.name + ': ' + data.result.files[idx].error);
                    file.context.remove();
                } else {
                    elem = data.result.files[idx];
                    showFile(file.context, elem);
                }
            });
        } else {
            $.each(data.files, function (idx, file) {
                var i;
                for (i in data.messages) {
                    if (data.textStatus === 'abort') {
                        message.push(file.name + ': ' + msg['cancel_upload'][nowlang]);
                    } else if (data.messages.hasOwnProperty(i)) {
                        message.push(file.name + ': ' + data.messages[i]);
                    }
                }
                file.context.remove();
            });
        }
        if (message.length > 0) {
            showErrorMsg(message.join('<br>'));
        }
    }
});

// 控制確定取消按鈕
// https://github.com/blueimp/jQuery-File-Upload/wiki/Options
$('#files')
    .bind('fileuploadstart', function (e, data) {
        $('#btnSubmit').attr('disabled', true);
    })

// 控制確定取消按鈕
$('#files')
    .bind('fileuploadstop', function (e, data) {
        $('#btnSubmit').removeAttr('disabled');

        // 將滑鼠焦點移到本次上傳的第一個檔案的名稱欄位中
        $('.deleteFlag[value="A"]:first')
            .parents('tr:first')
            .find('input[name$="title[]"]').focus();
    });

$('#files').fileupload(
    'option',
    {
        messages: {
            uploadedBytes: msg['uploaded_over'][nowlang]
        }
    }
);

$('.delete')
    .click(function () {
        var url = $(this).data('url'), but = this, butParent;
        butParent = $(but).parent().parent();
        if (url !== '') {
            $(but).addClass('readyDelete');
            butParent.find('.deleteFlag').val('D');
            butParent.hide();
        } else {
            butParent.remove();
        }

        // 當刪到沒有檔案列表時，隱藏欄位列
        if ($('input[class="deleteFlag"][value!="D"]').length === 0){
            $('#fileList').hide();
        }
});

if (window.File && window.FileReader && window.FileList && window.Blob) {
    // 瀏覽器支援所有的 File API
} else {
    $('.multifile-upload-note').text(msg['browser_no_support'][nowlang] + ' File API');
}

$(document).on('dragover', function (e) {
    e.preventDefault();
    e.stopPropagation();
    $('.dropzone').addClass('dragover');
});

$(document).on('drop dragleave', function (e) {
    e.preventDefault();
    e.stopPropagation();
    $('.dropzone').removeClass('dragover');
});
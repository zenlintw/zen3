//(function () {
//    // 文字編輯框
//    tinymce.init({
//        language: 'zh_TW',// TODO：要改為多語系
//        selector: '#content',
//        theme   : 'modern',
//        plugins: ['code textcolor preview table'],
//        toolbar: [
//            'insertfile undo redo',
//            'styleselect',
//            'forecolor backcolor bold italic',
//            'alignleft aligncenter alignright alignjustify',
//            'bullist numlist outdent indent',
//            'preview'
//        ].join(' | '),
//        content_css : "/theme/default/learn_mooc/common.css"// http://www.tinymce.com/wiki.php/Configuration3x:content_css
//    });
//}());

// 張貼
$('#btnSubmit').click(function () {
    var
        err = false,
        $source = $('#baseFm'), $target = $('#fileupload'), msg, $extra = $('#extraFm'), $node = $("form[name='node_chain']");
        
    // 關閉按鈕
    $(this).attr('disabled', true);

    // 清空錯誤訊息
    $('.error').remove();
    

    $('#baseFm #content').val(editor.getHTML());
    
    // 圖片來源路徑
    var tmp_src = [];
    $('img', editor.getHTML()).each(function() {
    	
    	tmp_src.push($(this).attr('src')); 
    });
    
    $("input[name='img_src']").val(tmp_src.join());

//    if ($('#baseFm #content').val() === '') {
////        $('#editmsg').show().find('span').append(sgLang.fillContent);
//        alert('內文空白');
//        return false;
//    }

//    $(this).prop('disabled', true);
// console.log($source.serialize() + '&' + $target.serialize() + '&tmp=' + $('#uploadfile').find("input[name='tmp']").val() + '&' + $extra.serialize() + '&' + $node.serialize());
    if (!err) {
        $.ajax({
            'url' : appRoot + '/forum/m_writing.php',
            'type': 'POST',
            'data': $source.serialize() + '&' + $target.serialize() + '&tmp=' + $('#uploadfile').find("input[name='tmp']").val() + '&' + $extra.serialize() + '&' + $node.serialize() + '&from=' + postFrom + '&noteid=' + noteId,
            'dataType': 'json',
            'success': function (data) {
//                if (window.console) {
//                    console.log(data);
//                }

                // 顯示結果
                if (data.error === true) {
                    var sysbar = parent.document.getElementById('moocSysbar');
					// MARK掉可能會有問題，要注意
//                    if (typeof(sysbar.contentWindow.goBoard) === 'function') {
//                        sysbar.contentWindow.goBoard(1);

                        if ((data.annFlag === true)&&('teach' === env)) {
                            $("form[name='node_chain']").prop('action', '/teach/course/m_cour_annt.php');
                        } else {
                            $("form[name='node_chain']").prop('action', '/forum/m_node_chain.php');
                            if (data.nid) {
                                $("form[name='node_chain']")
                                    .find("input[name='nid']")
                                        .val(data.nid);
                            }
                        }

                        $("form[name='node_chain']")
                            .find("input[name='cid']")
                                .val(cid).end()
                                .submit();
//                    }

//                    msg = 'modifyDone';
//                    if (data.img_error === false) {
//                        msg += "\n" + 'file_format_fail';
//                    }
//                    // 刪除檔案
//                    $('.readyDelete').each(function () {
//                        var url = $(this).data('url');
//                        if (url !== '') {
//                            $.ajax({
//                                'url' : url,
//                                'type': 'DELETE',
//                                'dataType': 'json',
//                                'success': function (data) {
//                                }
//                            });
//                        }
//                    });
//                    alertModal(sgLang.editUnitTitle, msg, sgLang.btnOK, function () {
//                        location.href = appRoot + 'unit/edit/' + data.id;
//                    });
                } else {
                    msg = data.error;
                    if (msg === 'deny') {
                        location.href = appRoot + 'unit/edit/' + data.id;
                    } else {

                        // 清除所有輸入錯誤提示呈現
                        $('.alert-input-error').removeClass('alert-input-error');
                        $('input,textarea,div').tooltip('destroy');

                        //後端驗證顯示訊息
                        for (var i = 0; i < msg.length; i++) {
                            $("[name='" + msg[i].id + "']").attr('title', msg[i].message).tooltip('show');

                            // 遇到文字編輯器另外處理
                            if (msg[i].id === 'content') {
                                $('#cke_1_contents').attr('title', msg[i].message).tooltip('show');
                                $('#cke_1_contents').addClass('alert-input-error');
                            }

                            // 遇到文字編輯器另外處理
                            if (msg[i].id === 'owner') {
                                $('.forum-write').attr('title', msg[i].message).tooltip('show');
                                $('.forum-write').addClass('alert-input-error');
                            }

                            $('[name="' + msg[i].id + '"]').addClass('alert-input-error');
                        }
        
                        // 關閉按鈕
                        $('#btnSubmit').attr('disabled',false);
                    }
                }
            },
            'fail': function (data) {
                if (window.console) {console.log('post fail.');}
            }
        });
    }
    return false;
});

// 取消
$('#btnCancel').click(function () {

    var page = '';

    if ((bid === bltBid)&&('teach' === env)) {
    	page = '/teach/course/m_cour_annt.php';
    } else {
        if ($("form[name='node_chain']").find("input[name='nid']").val() === '') {
            page = '/forum/m_node_list.php';
        } else {
            page = '/forum/m_node_chain.php';
        }
    }

    $("form[name='node_chain']")
        .prop('action', page)
        .find("input[name='cid']")
            .val(cid).end()
        .submit();
});

$(function(){
    CKEDITOR.on('instanceReady', function(event) {
        $('#cke_content').css('width', '100%');
    });
    $("img").addClass('img-responsive');
})
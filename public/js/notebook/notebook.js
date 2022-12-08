// 主內文：取得指定的筆記資料
getNotebook = function(fid, id) {
    
    if ($('.msg-annouce:visible').size() === 1 && !(confirm(msg.sure_switch_note[nowlang]))) {
        return;
    } 

//    if (window.console) {
//        console.log(fid);
//        console.log(id);
//    }

    $.ajax({
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'getRecentNotebook', fid: fid, id: id},
        'url': appRoot + '/mooc/controllers/notebook_ajax.php',
        success: function(res){
//            if (window.console) {
//                console.log(res);
//            }

            if (editor === null) {
                if (isIE11 === '1') { // IE 11
                    if (setUploadFun === '1') {
                        editorFuncWithUpload();
                    } else {
                        editorFunc();
                    }
                } else {
                    if (setUploadFun === '1') {
                        editorFuncWithUpload();
                    } else {
                        editorFunc();
                    }
                }
            }

            if (editor === null) {
                if (window.console) {console.log('編輯器載入失敗，進入新增筆記模式');}
                $('.icon-new').click();
                return;
            }
            
            // 監聽異動狀況
//            if ($('#cke_notebook-mod-content').length === 0) {
                editor.on('instanceReady', function(event) {
                    listenMod();
                });
//            }
            
            if ($(res).size() >= 1) {
                
              // 監聽異動狀況
//                if ($(".subtitle-mod input[name='subject']").val() === '') {
//                    listenMod();
//                }
//                if (window.console) {
//                    console.log('顯示筆記');
//                }
                // 顯示筆記
                showNotebook(res);
        
                // 筆記分享顯示與否
                $('.share-icon').show();
                $('.share-list').hide();

        
                // 處理超連結
                $('.notebook-article a').prop('target', '_blank');
                
                // 水平線
                $('.box3-main .paper .hr').show();
                // 時間
                $('.box3-main .paper .mod-time').show();
                // 上下則
                $('.box3-main .paper .page-controller').show();
                
                // 關閉標題編輯模式
                $('.subtitle-mod').hide();
                $('.subtitle').show();
                $('.subtitle').css('height', 'initial');
                $('.subtitle').css('height', 'auto');
                
                // 關閉編輯器
                $('.notebook-mod').hide();
//                $('.notebook-mod').show();
                $('.notebook-article').show();
                
                // 顯示檔案列表
                showFileList();
                
            } else {
                // 沒資料時直接啟用編輯模式
//                if (window.console) {
//                    console.log('no data');
//                }
//                if (window.console) {
//                    console.log($('#cke_notebook-mod-content').length);
//                }
                // 筆記本第一次進入，完全沒筆記
                if ($('#cke_notebook-mod-content').length === 0) {
                    editor.on('instanceReady', function(event) {
                        addMode();
                    });
                
                // 刪除到完全沒資料
                } else {
                    addMode();
                }
            }
                
            // 產生捲軸
            $('.notebook-article').mCustomScrollbar();
            
        },
        error: function() {
            if (window.console) {
                console.log('Get path Error!!');
            }
        }
    });
};

// 紀錄最後一次閱讀紀錄
setNotebookLastRead = function(id) {

    $.ajax({
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'setNotebookLastRead', fid: fid, id: id},
        'url': appRoot + '/mooc/controllers/notebook_ajax.php',
        async: false,// FF離開頁面需要改為同步才能正確紀錄最後觀看的筆記
        success: function(res){
//            if (window.console) {
//                console.log(res);
//            }
        },
        error: function() {
            if (window.console) {
                console.log('Get path Error!!');
            }
        }
    });
};

// 主內文：顯示指定的筆記
showNotebook = function(data) {
//    if (window.console) {
//        console.log(data);
//    }
    
    // 清空筆記內文等資訊
//    $('.subtitle, .share-text, .mod-time').html('&nbsp');
    clean();
    
    $.each(data, function( key, value) {
        $('.subtitle').text(value.full_subject);
        $('.subtitle').prop('title', value.full_subject);
        $(".subtitle-mod input[name='subject']").val(value.full_subject);
        $('.mod-time').text(value.submit_time);
        
        var $iframe = $('.cke_wysiwyg_frame'), 
        $contents = $iframe.contents();

        if ($('.notebook-article .mCSB_container').length >= 1) {
            // 編輯器原始碼
            $('.notebook-article .mCSB_container').html(value.content);
            // 編輯器一般
            $contents.find('.cke_editable').html(value.content);
            // 表單
            $('#notebook-mod-content').val(value.content);
            
        // 該頁面第一次進入
        } else {
            // 編輯器原始碼
            $('.notebook-article').html(value.content);
            // 編輯器一般
            editor.setHTML(value.content);// 關鍵，ie可能塞失敗
            // 適用ie，在ready後重新塞值
            editor.on('instanceReady', function(event) {
                if (editor.status === 'ready') {
                    var $iframe = $('.cke_wysiwyg_frame'), 
                    $contents = $iframe.contents();
                    $contents.find('.cke_editable').html(value.content);
                }
            });
            // 表單
            $('#notebook-mod-content').val(value.content);
        }
        
        // 附件檔案
        $('.file-list').hide();
        $('.file-list .file').remove();
        
        var attachment = getFileListHtml(value.attachment, '');
        if (attachment >= '0') {
            var files_total = value.attachment.length;
            
            // 標記實際附件檔案數量
            // 不使用計數功能，與未讀混淆，先註解
            /* 
            $('#upload .badge').data('total', files_total)
                .prop('title', files_total);
            
            if (files_total >= 10) {
                files_total = '9+';
            }
            $('#upload .badge').text(files_total);
            $('#upload .badge').show(); */
            $('.file-list').append(attachment);
    
            // 點選檔名讀取附件
            $('.file-list .file .filename').on('click', readFile);  
            
            // 刪除附件
            $('.file-list .file .close').off('click')
                .on('click', delFile);   
        } else {
            $('#upload .badge').data('total', 0);
        }
        
        var id = value.msg_serial;
        if (window.console) {
            console.log('編輯模式：' + id);
        }
        $('.box3-main').data('id', id);
        $('.share').show();
        
//        $('.share-text').html(value.share_text);
            
        // 異動HTML編輯器內文行高
//        $contents.find('.cke_editable').css('line-height', 'aaa');
    
        // 右側選單標記現在選到的筆記
        highlightItem(id);
    
        // 設定檔案上傳筆記流水號
        $('#uploadfile').find("input[name='id']").val(id);
        
//        if (window.console) {
//            console.log(id);
//        }
        
//        // 紀錄閱讀點
//        setNotebookLastRead(id);

        // 顯示分頁列
        // $('#pageToolbar').show();

        // 更新分頁工具列
        $('#pageToolbar').paginate('refresh', {
            'total': value.total_rows,
            'pageSize': value.limit_rows,
            'pageNumber' : value.current_page
        });
    });
};

// 組附件檔案
getFileListHtml = function(data, type) {
    // 附件檔案
    var txt = '';
    for (i = 0, j = data.length; i < j; i = i + 1) {
        txt +=  '<div class="file" data-type="' + type + '" style="display: none;">' +
                    '<div class="alert alert-success">' +
                        '<button type="button" class="close">×</button>' +
                        '<div class="filename" title="' + data[i].view_filename + '" data-realfilename="' + data[i].real_filename + '">' +
                        data[i].view_filename +
                        '</div>' +
                    '</div>' +
                    '<div class="pic">' +
                        '<a class="fancybox" href="#notefile-'+i+'">' +
                            '<img id="notefile-'+i+'" src="'+'download.php?p=' + stringToBase64('type=user&viewfile=' + encodeURIComponent(data[i].view_filename) + '&realfile=' + data[i].real_filename) + '" onerror="$(this).parent().parent().hide();">' +
                        '</a>' +
                    '</div>' +
                '</div>';
    }  
    
    return txt;
};

// 顯示附件檔案區域
showFileList = function(data) {
    $('.file-list, .file').show();
};

// 讀取附件
readFile = function() {
    var link = 'download.php?p=' + stringToBase64('type=user&viewfile=' + encodeURIComponent($(this).text()) + '&realfile=' + $(this).data('realfilename'));
//    if (window.console) {
//        console.log(link);
//    }
    window.open(link, msg.reading_file[nowlang]);
};

// 刪除附件
delFile = function() {
    var id = $('.box3-main').data('id'),
        obj = $(this).parents('.alert');

//    // 移除畫面元素
//    $(obj).remove();

    // 移除畫面元素
    $(obj).removeClass('alert-success')
        .addClass('alert-error')
        .parents('.file')
        .hide(1000 , function() {$(obj).parents('.file').remove();});

    // 附件數字減少
    var files_total = $('#upload .badge').data('total');
    files_total -= 1;
    
    // 標記實際附件檔案數量
    $('#upload .badge').data('total', files_total)
        .prop('title', files_total);
    
    if (files_total === 0) {
        $('#upload .badge').hide();
    } else if (files_total >= 10) {
        files_total = '9+';
    }
    $('#upload .badge').text(files_total);
        
    // 寫入資料庫
    if (id !== null) {
        saveNotebook();
    }
};

// 右側選單：顯示指定筆記本下所有筆記標題
showNotebookTitle = function(data) {
    
    // 清空右側選單
    $('.sidebar-list .item').remove();
    
    var txt = '';
    $.each(data, function( key, value) {
        
        txt +=  '<div class="item" data-id="' + value.msg_serial + '" style="display: flex; margin-top: 0.5em; width: 93%; cursor: pointer;">' + 
                    '<div class="icon"style="width: 13px; height: 15px; background-image: url(/public/images/icon_nb_item.png);"></div>' +
//                    '<div class="title" style="position: relative; top: -0.1em; margin-left: 0.4em; font-size: 1.1em;">' + value.subject.replace(/<(?:.|\n)*?>/gm, '') + '</div>' +
                    '<div class="title" style="position: relative; top: -0.1em; margin-left: 0.4em; font-size: 1.1em; white-space: nowrap; width: 100%; overflow: hidden; text-overflow: ellipsis;" title="' + value.subject + '">' + value.subject + '</div>' +
                '</div>';
    });   
    $('.sidebar-list .items').append(txt);
};

highlightItem = function() {
    $('.sidebar-list .item .title') 
        .css('color', '#000')
        .css('font-weight', 'initial');
    $(".sidebar-list .item[data-id='" + $('.box3-main').data('id') + "'] .title")
        .css('color', '#07AEB0')
        .css('font-weight', 'bold');    
};

addSidebarItem = function(id, subject) {
    var txt = '';
    txt +=  '<div class="item" data-id="' + id + '" style="display: flex; margin-top: 0.5em; width: 93%; cursor: pointer;">' + 
                '<div class="icon"style="width: 13px; height: 15px; background-image: url(/public/images/icon_nb_item.png);"></div>' +
                '<div class="title" style="position: relative; top: -0.1em; margin-left: 0.4em; font-size: 1.1em;" title="' + subject + '"></div>' +
            '</div>';
    
    // 判斷捲軸是否產生了
    if ($('.sidebar-list .mCSB_container').length >= 1) {
        $('.sidebar-list .mCSB_container').append(txt);
    } else {
        $('.sidebar-list .items').append(txt);
    }
    
    $(".sidebar-list .item[data-id='" + id + "'] .title").text(subject);
    
    highlightItem();
};

// 點選右側筆記選單標題後，取該筆記並顯示在主內文
chgNotebook = function() {
    var sidebarId = $(this).data('id'),
        mainId = parseInt($('.box3-main').data('id'), 10);

    if (sidebarId !== mainId) {
        // 取資料
        getNotebook(fid, sidebarId);
    }
};  

// 顯示社群分享選單
showShare = function() {
    
    // 取得接收分享的網址
    var id = $('.box3-main').data('id'),
        title = $('.subtitle').text();
        
    // 取分享網址
    var link = $('.share-link').text();
    if ($('.share-link').data('id') !== null && $('.share-link').data('id') !== $('.box3-main').data('id')) {
        link = getShareLink(fid, id, title);
    }

    if (link === '') {
        alert(msg.loading_incorrect[nowlang]);
    } else {
        // 顯示社群分享選單
        $('.share-icon').hide();
        $('.share-list').show();

        $('.share-link')
            .text(link)
            .data('id', id)
            .data('title', title);    
        $('.share-list').on('click', hideShare);    
        
        // 設定分享網址
//        var gen = 'http://www.fun11code-tech.com/Encoder_Service/img.aspx?custid=1&username=public&codetype=QR&EClevel=0&data=';
//        $('.inline-wct img').attr('src', gen + encodeURIComponent(link)); 
    
        $('#share-wct').attr('href', '/lib/phpqrcode/generate.php?size=9&data=' + encodeURIComponent(encodeURIComponent(link)));
    }
}; 

// 取得本筆記接收分享的網址
getShareLink = function(fid, id, title) {

    var d = new Date(),
        share_key = md5(fid + id + username),
        data = {folderId: fid, noteId: id, noteTitle: title, shareKey: share_key},
        link = '';
    
    $.ajax({
        url: appRoot + '/xmlapi/index.php?action=create-note-share-key&ticket=' + cticket,
        datatype: 'json',
        type:     'POST',
        data: JSON.stringify(data),
        async: false,
        success: function(res){
//            if (window.console) {
//                console.log(res);
//            }
            if (res.code == 0) {
                if (res.message == "success") {
                    link = res.data.shareURL;                    
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                if (window.console) {
                    console.log('Ticket illegeal!');
                }
            } else {
                if (window.console) {
                    console.log('Get path Error!!');
                }
            }
        },
        error: function() {
            if (window.console) {
                console.log('Get path Error!!');
            }
        }
    });
    
    return link;
};

// 分享
doShare = function() {
    var shareTo = $(this).attr('class'),
        link = $('.share-link').text();
//    if (window.console) {
//        console.log(link);
//    }
    switch (shareTo) {
        case 'fb':
            window.open('http://www.facebook.com/share.php?u='+ encodeURIComponent(link));
            break;
            
        case 'line':
            // 判斷式否為觸控裝置
            var touchable = isTouchDevice();
            if (touchable === false) {
                $(".share-list #share-ln").fancybox({
                    'titlePosition': 'inline',
                    'transitionIn': 'none',
                    'transitionOut': 'none',
                    helpers : {
                        overlay : {
                            locked : false
                        }
                    }
                });
            } else {
                link = $('.share-link').text();
                location.href = 'http://line.naver.jp/R/msg/text/?' + msg.share_note_receive[nowlang] + '%0D%0A' + encodeURIComponent(link);
            }
            
            break;
            
        case 'wct':
            // 點選WECHAT圖示
            $('.share-list #share-wct').fancybox({
//                'titlePosition': 'inline',
//                'transitionIn': 'none',
//                'transitionOut': 'none',
//                helpers : {
//                    overlay : {
//                        locked : false
//                    }
//                }
                maxWidth    : 800,
                maxHeight    : 600,
                fitToView    : false,
                width            : 430,
                height            : 430,
                autoSize    : false,
                closeClick    : false,
                openEffect    : 'none',
                closeEffect    : 'none'
            });
            
            break;
            
        default:
            alert('share error!');
            break;
    }
};  

// 隱藏社群分享選單
hideShare = function() {
    $('.share-icon').show();
    $('.share-list').hide();
};  

// 設定欲刪除確認視窗的筆記名稱, id
setDelnfo = function() {
    $('.btn-del').show();
    // 設定欲刪除確認視窗的筆記名稱, id
    var id = $(this).parents('.box3-main').data('id'),
        title = '「' + $(".top input[name='subject']").val() + '」';

    $('#del-group-box').data('id', id);
    $('#del-group-box').find('.group-box-content').find('p').text(title);
};

// 刪除筆記
delNote = function() {
//    if (window.console) {
//        console.log('delNote');
//    }
    var id = $(this).parents('.group-box').data('id'),
        message = '';
//    if (window.console) {
//        console.log(id);
//    }
    // 判斷欲刪除的筆記編號有沒有成功傳到對話視窗
    if (id === undefined || id === '') {
        
        if ($(".top input[name='subject']").val() === '') {
            message = msg.del_title_error[nowlang];
        } else {
            message = msg.del_no_error[nowlang];
        }
        // 方式1: 使用客製訊息視窗
        $('#del-group-box').find('.group-box-content').find('p').text(message);

//        // 方式2: 使用內建的訊息視窗
//        alert(msg.del_no_error[nowlang]);
        $('.btn-del').hide();
            
        return false; 
    }
    
    // 避免連擊
    $('.btn-del').prop('disabled', true);
    
    // 刪除筆記本
    $.ajax({
        url: '/xmlapi/index.php?action=delete-note&note_id=' + id + '&ticket=' + cticket,
        datatype: 'json',
        type:     'POST',
        async: false,
        success: function(res){
//            if (window.console) {
//                console.log(res);
//            }
            
            if (res.code == 0) {
                if (res.message == "success") {
                    // 清空
                    clean();
                    $(".sidebar-list .item[data-id='" + id + "']").remove();
                    
                    // 關閉對話視窗
                    $('.btn-close').click();
                    
                    // 取最新資料
                    getNotebook(fid, '');
                    
                    if ($('.sidebar-list .item').length === 0) {
                        // 隱藏分頁列
                        $('#pageToolbar').hide();
                    }
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                if (window.console) {
                    console.log('Ticket illegeal!');
                }
            // 可能時機: 開分頁重複刪除
            } else if (res.code == 7) {
                alert(msg.del_no_error[nowlang]);
            } else {
                if (window.console) {
                    console.log('Get path Error!!');
                }
            }
        },
        error: function() {
            if (window.console) {
                console.log('Get path Error!!');
            }
        },
        complete : function() {
            $('.btn-del').prop('disabled', false);
        }
    });
};      

// 關閉對話視窗
closeMsgBox = function() {
    $.fancybox.close();
};    

// 開啟或關閉側邊欄位
expand = function(event) {
    var active = $('.sidebar-expand').hasClass('active'),
        old = $('.box3-sidebar').attr('style');

    if (active === false) {
        $('.sidebar-expand').addClass('active');

        $('.box3-sidebar').css('width','300px');
        $('.sidebar-expand .icon').css('background-image', 'url(/public/images/icon_collapse.png)');
        $('.sidebar-list').show();
        
        $('.sidebar-list .item').off('click')
            .on('click', chgNotebook);
    } else {
        collapse();
    }
};    

// 關閉側邊欄位
collapse = function() {
    $('.sidebar-expand').removeClass('active');
//    $('.box3-sidebar').attr('style', old + 'width: initial;')

    $('.box3-sidebar').css('width','initial');
    $('.box3-sidebar').css('width','auto');
    $('.sidebar-expand .icon').css('background-image', 'url(/public/images/icon_expand.png)');
    $('.sidebar-list').hide();
};     

clean = function() {
    $('.box3-main').data('id', '');
    
    $('.subtitle, .share-text, .mod-time').html('&nbsp');
    $(".subtitle-mod input[name='subject']").val('');
        
    var $iframe = $('.cke_wysiwyg_frame'), 
    $contents = $iframe.contents();
    $('.notebook-article .mCSB_container').html('');
    // 異動HTML編輯器內文
    $contents.find('.cke_editable').html('');
    
    $('.file-list .file').remove();
    $('#upload .badge').text(0)
        .prop('title', 0)
        .data('total', 0)
        .hide();   

    // 隱藏分頁列
    $('#pageToolbar').hide(); 
    
    // 隱藏編輯中提示
    $('.msg-annouce:visible').hide();

    // 功能按鈕
    $('.box3-main .operate .icon-new').show();
    $('.box3-main .operate .icon-save').hide();
};

// 新增模式
addMode = function() {
    $('.share').hide();
    if (isDiffArticle() === true) {
        // 進行儲存
//        if (window.console) {
//            console.log(6);
//        }
        saveNotebook(); 
    }
//    if (window.console) {
//        console.log('addMode');
//    }
    // 清空資料
    clean();
    
    // 編輯模式
    modMode();
};  

// 檢視模式
viewMode = function() {
    // 確認標題有無填寫
    var title = $("input[name='subject']");
    if (title.val() === null || title.val() === '') {
        title.attr('title', msg.title_empty[nowlang])
            .tooltip('toggle')
            .addClass('alert-lcms-error')
            .focus();
        return false;
    } else {
        title.tooltip('destroy')
            .removeClass('alert-lcms-error');
    }
    // 轉回 view 模式就主動式儲存，不需經過 isDiffArticle() 判斷
    isDiffArticle();    // 內容變更時替換顯示
    saveNotebook();
    
    var subject = $(".subtitle-mod input[name='subject']").val();
    $('.top .subtitle').text(subject);
    $(".sidebar-list .items div[data-id='" + $('.box3-main').data('id') + "'] .title").text(subject);
    
    // 標題
    $('.subtitle').show();
    $('.subtitle').css('height', 'auto');
    $('.subtitle-mod').hide();

    // 水平線
    $('.box3-main .paper .hr').show();
    // 時間
    $('.box3-main .paper .mod-time').show();
    // 上下則
    $('.box3-main .paper .page-controller').show();

    // 功能按鈕
    $('.box3-main .operate .icon-new').show();
    $('.box3-main .operate .icon-save').hide();

    // 開啟編輯器
    $('.notebook-mod').hide();
    $('.notebook-article').show();

}; 

// 編輯模式
modMode = function() {    
//    if (window.console) {
//        console.log('modMode');
//    }
    
    // 標題
    $('.subtitle').css('display', 'none');
    $('.subtitle').css('height', 0);
    $('.subtitle-mod').show();
    
    // 水平線
    $('.box3-main .paper .hr').hide();
    // 時間
    $('.box3-main .paper .mod-time').hide();
    // 上下則
    $('.box3-main .paper .page-controller').hide();

    // 功能按鈕
    $('.box3-main .operate .icon-new').hide();
    $('.box3-main .operate .icon-save').show();

    // 開啟編輯器
    $('.notebook-mod').show();
    $('.notebook-article').hide();
            
    // 調整尺寸
    $('#cke_notebook-mod-content').css('width', '100%');
    $('.cke_contents').css('height', '265px');
}; 

isDiffArticle = function() {
    var newContent = editor.getHTML().replace(/\n/g,""),
//        oldContent = $('.notebook-article .mCSB_container').html();
        oldContent = $('#notebook-mod-content').val();
//    if (window.console) {
//        console.log(newContent);
//        console.log(oldContent);
//        console.log(newContent.length);
//        console.log(oldContent.length);
//    }
    // 筆記不同 或 新筆記
    if (newContent !== oldContent || $('.box3-main').data('id') === '') {
//        if (window.console) {
//            console.log('diff');
//        }
        
        // 編輯器原始碼
        $('.notebook-article .mCSB_container').html(newContent);
        // 表單
        $('#notebook-mod-content').val(newContent);
        // 處理超連結
        $('.notebook-article a').prop('target', '_blank');
                
        
        return true;
    } else {
        return false;
    }
};

listenMod = function() { 
//    if (window.console) {
//        console.log('listenMod');
//    }
    
    $(".subtitle-mod input[name='subject']").off("blur");
    
    // 標題離開焦點
    $(".subtitle-mod input[name='subject']").on('blur', function() {
        var newContent = $(this).val(),
            oldContent = $('.top .subtitle').text();
//        if (window.console) {
//            console.log(newContent !== oldContent);
//        }
        if (newContent !== oldContent) {
//            if (window.console) {
//                console.log(2);
//            }
            $('.msg-annouce').show();
            
//            saveNotebook(); 
//            $('.top .subtitle').text(newContent);
//            $(".sidebar-list .items div[data-id='" + $('.box3-main').data('id') + "'] .title").text(newContent);
        }
    });
    
    // 偵測標題區有異動
    $(".subtitle-mod input[name='subject']").bind('input', function () {
        $('.msg-annouce').show();
     });
        
    // 編輯器一般模式進行編輯時
    editor.on('change', function() {
        // 關閉視窗
        collapse();
        
        var total = parseInt($('.times').text(), 10) + 1;
        
        if (total >= 1) {
            $('.msg-annouce').show();
        }

        if (total >= 15) {
            if (isDiffArticle() === true) {
                // 進行儲存
//                    if (window.console) {
//                        console.log(6);
//                    }
                saveNotebook(); 
            }
            total = 0;
        } 

        $('.times').text(total);
    });
        
    // 編輯器原始碼模式進行編輯時
    editor.on('mode', function() {
        // 關閉視窗
        collapse();
        
        var editable = editor.editable();
        
        // 輸入英數字或注音的音節就算1
        editable.attachListener(editable, 'input', function() {
            var total = parseInt($('.times').text(), 10) + 1;
        
            if (total >= 1) {
                $('.msg-annouce').show();
            }
            
            if (total >= 15) {
                if (isDiffArticle() === true) {
                    // 進行儲存
//                    if (window.console) {
//                        console.log(3);
//                    }
                    saveNotebook(); 
                }
                total = 0;
            } 

            $('.times').text(total);
        });
    });

    // 編輯器離開焦點
    editor.on('blur', function() {
        if (isDiffArticle() === true) {
            // 進行儲存
//            if (window.console) {
//                console.log(4);
//            }
            $('.msg-annouce').show();
//            saveNotebook(); 
        }
    });
};

// 進行儲存
saveNotebook = function(event) {
//    if (window.console) {
//        console.log('saveNotebook');
//    }
     
//    console.log('進行儲存');
    var id = $('.box3-main').data('id'),
        title = $("input[name='subject']").val(),
        content = editor.getHTML(),
        files = $('.file-list .alert-success .filename'),
        attatch = new Array();

    if (title === null || title === '') {
        return false;
    }
    
//    if (window.console) {
//        console.log(event);
//    }
    
    if (event === undefined) {
        $('.efficacy').isLoading({
           'text':       msg.msg_saving[nowlang],
           'class':      "icon-loader",
           'position':   "overlay"
       });
    }

    // 彙整附件檔案
    for (i = 0, j = files.size(); i < j; i++) {
        var obj = {'filename': files.eq(i).data('realfilename'), 'viewfilename': files.eq(i).prop('title'), 'base64': 'WM'};
        attatch[i] = obj;
    };
    
//    if (window.console) {
//        console.log(files);
//    }

    var data = {folder_id: fid, msg_serial: id, title: title, content: content, from: 'WM', attachments: attatch};

    // 進行儲存
    $.ajax({
        'type': 'POST',
        'dataType': 'json',
        'data': JSON.stringify(data),
        'async': false,
        'url': appRoot + '/xmlapi/index.php?action=note-handler&ticket=' + cticket,
        success: function(res){
//            if (window.console) {
//                console.log(res);
//            }
            
            if (res.code == 0) {
                if (res.message == "success") {
                    
                    $('.box3-main').data('id', res.data.msg_serial);
                    $('.share').show();
                        
                    // 更新文章時間
                    $(".box3-main .paper .top .mod-time").text(res.data.update_time);
                    
//                $('.box3-main').isLoading('hide');       
//                      
                    // 新增模式
                    if (id === '') {
                        // 右側選單增加
                        addSidebarItem(res.data.msg_serial, title);

                        // 顯示分頁列
                        // $('#pageToolbar').show();
                        
                        // 更新分頁工具列
                        var total = $('.sidebar-list .item').length;
                        $('#pageToolbar').paginate('refresh', {
                            'total': total,
                            'pageSize': 1,
                            'pageNumber' : total
                        });
                    }
                    
                    $('.efficacy').isLoading( "hide");
                    $('.msg-annouce').hide();
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                if (window.console) {
                    console.log('Ticket illegeal!');
                }
            } else {
                if (window.console) {
                    console.log('Get path Error!!');
                }
            }
            
//            if ($(res).size() >= 1) {
//                // 顯示筆記
//                showNotebookTitle(res);
//
//                // 右側選單標記現在選到的筆記
//                $('.sidebar-list .item .title') 
//                    .css('color', '#000')
//                    .css('font-weight', 'initial');
//                $(".sidebar-list .item[data-id='" + $('.box3-main').data('id') + "'] .title")
//                    .css('color', '#07AEB0')
//                    .css('font-weight', 'bold');
//    
//                // 產生捲軸
//                $('.sidebar-list .items').mCustomScrollbar();
//            }
        },
        error: function() {
            alert("saveNotebook Error!!");
        }
    }); 
};

$(function(){
    
    $('.main-title .title, .sidebar-expand').on('click', expand);
    
    // 滑鼠離開側邊欄區塊即收起側邊欄
    $(".box3-sidebar").on('mouseleave', collapse);
    
    // 點擊本文關閉側邊欄
    // $(".subtitle, .notebook-article, input[name='subject']").on('click', collapse);
    
    // 點擊本文開啟編輯模式
    $(".subtitle, .notebook-article").filter("*").on('click', function(e) { 
        
        var elem, evt = e ? e:event;
        if (evt.srcElement)  elem = evt.srcElement;
        else if (evt.target) elem = evt.target;
        
        // 目的：點到內文的超連結不要變成編輯
        if (elem.tagName !== 'A') {
            modMode();
        }
        return true;
    });    
    
    // 新增時關閉側邊欄
    $('.icon-new').on('click', collapse);
    
    // 新增時開啟新增模式
    $('.icon-new').on('click', addMode);

    // 儲存時開啟檢視模式
    $('.icon-save').on('click', viewMode);
    
    // 回到列表，避免IE提示兩次尚未儲存訊息
    $('.back-list').off('click');
    $('.back-list').on('click', goNotebookList);
            
    // 啟用 fancybox
    $("a.icon-delete").fancybox({
        'padding' : 0,
        'margin' : 0,
        'modal' : true,
        helpers: { 
            title: null
        }
    });         
            
    $('.icon-delete').on('click', setDelnfo);
    $('.btn-del').on('click', delNote);

    $('.btn-close').on('click', closeMsgBox);
    
    $('.share-icon').on('click', showShare);
    $('.share-list div').on('click', doShare);
    
    $('#upload .badge').on('click', showFileList);
    
    // 分頁工具列
    $('#pageToolbar').paginate({
        'total': 0,
        'showPageList': false,
        'showRefresh': false,
        'showSeparator': false,
        'btnTitleFirst': btnTitleFirst,
        'btnTitlePrev': btnTitlePrev,
        'btnTitleNext': btnTitleNext,
        'btnTitleLast': btnTitleLast,
        'btnTitleRefresh': btnTitleRefresh,
        'beforePageText': beforePageText,
        'afterPageText': afterPageText,
        'beforePerPageText': beforePerPageText,
        'afterPerPageText': afterPerPageText,
        'displayMsg': displayMsg,
        'buttonCls': '',
        'onSelectPage': function (num, size) {
            $('#selectPage').val(num);
            
            $('.sidebar-list .item').off('click')
                .on('click', chgNotebook);
            
            // 點擊右側選單
            $('.sidebar-list .item').eq(num-1).click();
//            console.log(size);
//            console.log(num);
        }
    });;
    
    // 強制先啟動編輯器，避免編輯區空白
    if (isIE11 === '1') { // IE 11
        if (setUploadFun === '1') {
            editorFuncWithUpload();
        } else {
            editorFunc();
        }
    } else {
        if (setUploadFun === '1') {
            editorFuncWithUpload();
        } else {
            editorFunc();
        }
    }
    
    getNotebook(fid, assignId);

    // 指定分頁
    $(".paginate-number").keypress(function (e) {
        if (e.keyCode == 13) {
            $('#pageToolbar').paginate('select', $(this).val());
        }
    });

    // 上下則筆記
    $(".page-controller .prev").on('click', function() {
        $('#pageToolbar').paginate('select', parseInt($(".paginate-number").val())-1);
    });
    $(".page-controller .next").on('click', function() {
        $('#pageToolbar').paginate('select', parseInt($(".paginate-number").val())+1);
    });

    // 取得指定筆記本下的所有筆記
    $.ajax({
        'type': 'POST',
        'dataType': 'json',
        'data': {action: 'getNotebookTitle', fid:fid},
        'url': appRoot + '/mooc/controllers/notebook_ajax.php',
        success: function(res){
//            if (window.console) {
//                console.log(res);
//            }
            if ($(res).size() >= 1) {
                // 顯示筆記
                showNotebookTitle(res);

                // 右側選單標記現在選到的筆記
                highlightItem();
    
                // 產生捲軸
                $('.sidebar-list .items').mCustomScrollbar();
            }
        },
        error: function() {
            if (window.console) {
                console.log('Get path Error!!');
            }
        }
    });
    
    // 附件 fancybox
    $(".file .fancybox").fancybox({
        helpers: {
            overlay: {
                locked: false,
                closeClick: false
            }
        },
        beforeShow: function(){
            tarGet= this.href;
        },
        afterClose: function(){
            $(tarGet).show();
        }
    });
}); 

window.onbeforeunload = function() {
    if (window.console) {
        console.log('onbeforeunload');
    }

    // 紀錄閱讀點
    var id = $('.box3-main').data('id');
    if (id >= 0) {
        setNotebookLastRead(id);
    }

    // 比對內文有異動 或者 標記尚未存檔
    if ($('.msg-annouce:visible').size() === 1) {
        // 進行儲存
        if (window.console) {
            console.log(5);
        }
        $('.msg-annouce').show();

        return "確定關閉此頁面？";
//        saveNotebook();
    }
};
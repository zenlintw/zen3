// 無資料時的HTML
noDataHtml = function() {
    var txt = '';
    txt = '<div class="notebook" style="margin-top: 1em;">' +
                '<div class="cover">' +
                    '<div class="icon" style="width: 13px; height: 15px; background-image: url(/public/images/icon_nb_item.png); display: block; float: left; margin-top: 0.2em;"></div>' +
                    '<div class="title" style="font-size: 1.4em; color: rgb(0, 119, 122); font-weight: bold; height: initial; margin-left: 1.5em;">' + msg.no_fit[nowlang] + '</div>' +
                '</div>' +
            '</div>';
    
    return txt;
};

// 無搜尋結果
showNoData = function() {
    var txt = noDataHtml();
    
    // 顯示 0則
    $('.total-section .total').text(0);
    
    // 已經有產生捲軸，則置換結果    
    if ($('.result .mCSB_container').length >= 1) {
        $('.result .mCSB_container').append(txt);
        
    // 沒有捲軸，先給予內文再給予捲軸
    } else {
        $('.result .items').append(txt);
        
        // 產生捲軸mCSB_container
//        $('.result .items').mCustomScrollbar();
    }
};

// 搜尋資料
doSearch = function() {

    var keyword = $('.search .search-query').val();
    
    if (keyword === '') {
        $('.search .search-query').tooltip('show');
    } else {
    
        // 如果不是在搜尋頁面，先導向搜尋頁面
        if ($('.result').length === 0) {
//            $(window.parent.frames['s_sysbar'].document).find('.wm-content').prop('notebook-search-keyword', keyword);
//            if (window.console) {
//                console.log(keyword);
//            }
            localStorage.setItem('notebook-search-keyword', keyword);
            
            $("form[name='goto']").prop('action', '/message/m_result.php')
                .find("input[name='keyword']").val(keyword).end()
                .submit();

            return false;
        } else {
//            $(window.parent.frames['s_sysbar'].document).find('.wm-content').prop('notebook-search-keyword', '')
            localStorage.setItem('notebook-search-keyword', '');
        }
    
        $('.search .search-query').tooltip('destroy');
        $('.keyword-section .keyword').text(keyword);

        // 搜尋文章
        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'data': {action: 'searchNotebooks', keyword: keyword},
            'url': appRoot + '/mooc/controllers/notebook_ajax.php',
            success: function(res){
//                if (window.console) {
//                    console.log(res);
//                }

                // 清空畫面
                $('.notebook').remove();
                
                if ($(res).size() >= 1) {
                    // 顯示搜尋結果
                    showSearchResult(res);
                    
                    // 補償暫時無法判斷筆記本為回收筒子資料夾問題
                    if ($('.total-section .total').text() === '0') {
                        showNoData();
                    }
                } else {
                    showNoData();
                }
            },
            error: function() {
                if (window.console) {
                    console.log('searchNotebooks Error!!');
                }
            }
        }); 
    }
};

// 點選搜尋結果的筆記後，導向該筆記
readNotebook = function() {
    var obj = $(this),
        fid = obj.data('fid'),
        fname = obj.data('fname'),
        id = obj.data('id')
        form = $("form[name='notebook']");
    
//    if (window.console) {
//        console.log(fid);
//        console.log(fname);
//        console.log(id);
//    }
    
    // 導向該筆記
    form.prop('method', 'POST')
        .prop('action', 'm_notebook.php')
        .find("input[name='fid']").val(fid).end()
        .find("input[name='fname']").val((fname)).end()
        .find("input[name='id']").val(id).end();

    // 前往檢視筆記
    form.submit();
};

// 回傳所有筆記名稱陣列
getAllNotebooksTitle = function() {
    var folder = [];
    
    $.ajax({
        url: '/xmlapi/index.php?action=get-notes&type=notes&extra=all&ticket=' + cticket,
        datatype: 'json',
        async: false,
        success: function(res){
//            if (window.console) {
//                console.log(res.data.notebooks);
//            }
            if (res.code == 0) {
                if (res.message == "success") {
                    $.each(res.data.notebooks, function(idx, val) {
                        if (window.console) {
                            folder[val.folder_id] = val.folder_title;
                        }
                    });
//                    if (window.console) {
//                        console.log(folder);
//                    }
                } else {
                    folder = false;
                }
            } else if (res.code == 1) {
                if (window.console) {
                    console.log('Ticket illegeal!');
                }
                folder = false;
            } else {
                if (window.console) {
                    console.log("Get path Error!!");
                }
                folder = false;
            }
        },
        error: function() {
            if (window.console) {
                console.log('get-notes Error!!');
            }
            folder = false;
        }
    });
    
    return folder;
};

// 將搜尋結果的資料顯示出來
showSearchResult = function(data) {
    // 取得所有筆記本
    var folder = [];
    var folder = getAllNotebooksTitle();
//    if (window.console) {
//        console.log(folder);
//    }
    
    var txt = '', lastFid = '', i = 1;
    
    $.each(data, function(index, value) {
        
//        if (window.console) {
//            console.log(value.folder_id);
//            console.log(value.full_subject);
//        }  
        
        var fname = '';
        fname = strip(folder[value.folder_id]);
        
        // 存在的筆記本才繼續處理
        if (fname === 'undefined') {
            return;
        }
    
        // 修正 WEB SERVICE 沒有多語系
        if (value.folder_id === 'sys_notebook') {
            fname = msg.my_notebook[nowlang];
        }
        
        // 不相同時，代表是新的筆記本，所以給予結尾
        if (i !== 1 && lastFid !== value.folder_id) {
            txt +=      '</div>' +    
                        '<div class="hr" style="border-bottom: rgb(190, 192, 192) dotted 1px; margin-bottom: 0.3em;">' + 
                        '</div>' +
                    '</div>';
        }
        
        // 不相同時，代表是新的筆記本，所以要有開頭
        if (lastFid !== value.folder_id) {
            txt +=  '<div class="notebook" style="margin-top: 1em;">' +          
                        '<div class="cover">' +       
                            '<div class="icon" style="width: 13px; height: 15px; background-image: url(/public/images/icon_nb_item.png); display: block; float: left; margin-top: 0.2em;"></div>' +     
                            '<div class="title" style="font-size: 1.4em; color: rgb(0, 119, 122); font-weight: bold; height: initial; margin-left: 1.5em;">' + fname +
                            '</div>' +      
                        '</div>' +   
                        '<div class="content" style="padding-left: 1.7em; margin-top: 0.7em; font-size: 1.2em; height: initial;">' +
                            '<div class="item" data-fid="' + value.folder_id + '" data-fname="' + encodeURIComponent(fname) + '" data-id="' + value.msg_serial + '" style="cursor: pointer;" title="' + msg.view_notes[nowlang] + '">' +  
                                '<div class="row-fluid">' +
                                    '<div class="title left span2" style="word-break: break-all;">' + value.full_subject +
                                    '</div>' +            
                                    '<div class="fit right span10" style="margin-left: 1em;">' + value.content +
                                    '</div>' +
                                '</div>' +
                            '</div>';              
            
        // 相同是代表，增加筆記「則」的內文即可
        } else {
                    txt +=  '<div class="item" data-fid="' + value.folder_id + '" data-fname="' + encodeURIComponent(fname) + '" data-id="' + value.msg_serial + '" style="cursor: pointer;"  title="' + msg.view_notes[nowlang] + '">' +  
                                '<div class="row-fluid">' +
                                    '<div class="title left span2" style="word-break: break-all;">' + value.full_subject +
                                    '</div>' +            
                                    '<div class="fit right span10" style="margin-left: 1em; word-break: break-all;">' + value.content +
                                    '</div>' +
                                '</div>' +
                            '</div>';  
        }
        
        i++;
        lastFid = value.folder_id;
    });
    
    $('.total-section .total').text(i-1);
        
    // 已經有產生捲軸，則置換結果    
    if ($('.result .mCSB_container').length >= 1) {
        $('.result .mCSB_container').append(txt);
        
    // 沒有捲軸，先給予內文再給予捲軸
    } else {
        $('.result .items').append(txt);
        
        // 產生捲軸mCSB_container
        $('.result .items').mCustomScrollbar();
    }
    
    // 調整筆記版面
    $('.result .item').css('margin-bottom', '1em');
    $('.result .item:last').css('margin-bottom', '0em');
    
    // 綁定點選內文事件
    $('.result .item').on('click', readNotebook);
};

$(function() { 
    // 點選放大鏡，進行搜尋
    $('.search .icon-search, .search .add-on').on('click', doSearch);
    
    // 搜尋欄位Enter，進行搜尋
    $('.search-bar .search-query').keyup(function(e){
        if(e.keyCode == 13){
            doSearch();
        }
    });    
    
    // 返回列表
    $('.back-list').on('click', goNotebookList);// common.js
    
//    if ($(window.parent.frames['s_sysbar']).length === 0) {
//        // 移除搜尋列
//        $('.search-bar, .fullpage-hr').remove();
//        $("form[name='goto']").remove();
//    } else {
        // 系統列筆記本關鍵字有數值，則進行查詢
//        var obj = $(window.parent.frames['s_sysbar'].document).find('.wm-content').prop('notebook-search-keyword');
        var obj = localStorage.getItem('notebook-search-keyword');
//        if (obj !== undefined && obj !== '') {
//            $('.search .search-query').val($(window.parent.frames['s_sysbar'].document).find('.wm-content').prop('notebook-search-keyword'));
//            doSearch();
//        }
        if (obj !== undefined && obj !== null && obj !== '') {
//            if (window.console) {
//                console.log(obj);
//            }
            $('.search .search-query').val(obj);
            doSearch();
        }
//    }
}); 
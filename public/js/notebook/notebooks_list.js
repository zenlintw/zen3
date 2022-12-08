var title_error =   '<div class="alert alert-error" style="">' +
                        '<button type="button" class="close" data-dismiss="alert">×</button>' +
                        '<strong>' + msg.title_error[nowlang] + '</strong>' +
                    '</div>';      
            
if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length >>> 0;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}            

// 顯示所有筆記本
showNotebooks = function(data) {
    
    // 取每本筆記本裡面有幾則
//    var action = 'getNotebookTotal';
    var txt = '';
//    console.log(data);
//    $.ajax({
//        'url':      "/mooc/controllers/notebook_ajax.php",
//        'type':     'POST',
//        'dataType': "json",
//        'data':     {action: action, username: username},
//        success: function(res){
//            if (window.console) {
//                console.log(data);
//            }
            // 組所有筆記本HTML
            var existsFolder = [];
            $.each(data, function(index, value) {
                // 不存在時
                if (existsFolder.indexOf(value.folder_id) === -1) {
                    txt += getItem(value.folder_id, value.folder_title, value.note_count);
                    existsFolder.push(value.folder_id);
                }
            }); 
    
            // 插入頁面
            var obj = $('.data9 .items');
            obj.append(txt);
            
            // 重新排版
            rendering();
            
            // 綁定筆記本
            $('.item .notebook, .item .tab-label').on("click", readNotebook);
            
            // 綁定對話視窗
            $('.btn-ok').on("click", addNote);
            $('.icon-delete').on("click", setDelInfo);
            $('.btn-del').on("click", delNote);
            $('.icon-modify').on("click", setModInfo);
            $('.btn-mod').on("click", modNote);
            
            $('.btn-close').on("click", closeMsgBox);
//        },
//        error: function() {
//            alert("Get path Error!!");
//        }
//    });
};

// 組單一筆記本HTML
getItem = function(id, title, total) {
    var txt = $('<div></div>'), operate = '', course_botebook = 'USER_' + username + '_';
//    if (window.console) {
//        console.log(course_botebook);
//        console.log(id.substr(0, 6 + username.length));
//    }
    // 我的筆記本、課程專屬筆記本不得刪除或修改名稱
    if (id !== 'sys_notebook' && id.substr(0, 6 + username.length) !== course_botebook) {
        operate =   '<div class="operate">' +
                        '<a href="#del-group-box" class="icon-delete" title="' + msg.delete_notebooks[nowlang] + '"></a>' +
                        '<div class="space"></div>' +
                        '<a href="#mod-group-box" class="icon-modify" title="' + msg.mod_notebook[nowlang] + '"></a>' +
                    '</div>';
    }
    
    if (total >= 999999999) {
        total = '999999999+';
    }
    
    // 修正 WEB SERVICE 沒有多語系
    if (id === 'sys_notebook') {
        title = msg.my_notebook[nowlang];
    }
    
    txt.append($('<div class="item" data-id="' + id + '"></div>')
            .append($('<div class="tabs" style="height: 1.4em;"></div>')
                .append($('<div class="tab" style="display: none;"></div>')
                    .append('<div class="stereo"></div>')
                    .append('<div class="tab-label">new</div>')
                )
            )
            .append($('<div class="notebook"></div>')
                .append($('<div class="cover"></div>')
                    .append($('<div class="info"></div>')
                        .append('<div class="time"></div>')
                        .append($('<div class="title"></div>')
                            .text(title)
                        )
                        .append('<div class="total">' + msg.total[nowlang] + total + msg.items[nowlang] + '</div>')
                    )
                )
            )
            .append(operate)
    );
    
    return txt.html();
};

// 重新排版
rendering = function() {
    // 排列筆記本
    var options = {
            autoResize: true, // This will auto-update the layout when the browser window is resized.
            container: $('.content'), // Optional, used for some extra CSS styling
            offset: 33, // Optional, the distance between grid items
            itemWidth: 141, // Optional, the width of a grid item
            align: 'center'
    };
    var handler = $('.data9 .item');

    // READY後才排版，免得物件寬度經過縮放後不一樣
    $(document).ready(function() {
        handler.wookmark(options);
    });         
};        

// 新增筆記本
addNote = function() {
//    if (window.console) {
//        console.log('addNote');
//    }
    var n = Math.floor(Math.random()*11),
        k = Math.floor(Math.random()* 10000000000000),
        fid = 'USER_' + k.toString(),// 數字轉字串免得變亂碼
        title = $('#add-group-box').find("input[name='title']").val(),
        data = {folder_id:fid, folder_name:title};

    if (title.length === 0 || title.length >= 13) {
        $('.alert-error').remove();
        
        $('#add-group-box .input-note')
            .hide()
            .after(title_error);
        
        return false;
    } else {
        $('#add-group-box .input-note').show();
        $('#add-group-box .alert').hide();
    }
    
    // 新增筆記本
    $.ajax({
        url: '/xmlapi/index.php?action=add-notebook&ticket=' + cticket,
        datatype: 'json',
        type:     'POST',
        data:     JSON.stringify(data),
        async: false,
        success: function(res){
            
            if (res.code == 0) {
                if (res.message == "success") {
                    // 組單本筆記本HTML
                    var txt = '';
                    txt = getItem(fid, title, 0);
    
                    // 插入頁面
                    var obj = $('.data9 .items').find("div[data-id='sys_notebook']");
                    obj.after(txt);

                    // 重新排版
                    rendering();
                    
                    // 綁定動作
                    $("div[data-id='" + fid + "']").find('.icon-delete').on("click", setDelInfo)
                        .end()
                        .find('.icon-modify').on("click", setModInfo);
            
                    // 綁定筆記本
                    $('.item .notebook, .item .tab-label').on("click", readNotebook);                   
                    
                    // 關閉對話視窗
                    $('.btn-close').click();
                    
                    // 新增視窗的筆記本名稱清空
                    $('#add-group-box').find("input[name='title']").val(''); 
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                alert('Ticket illegeal!');
            } else {
                alert("Get path Error!!");
            }
        },
        error: function() {
            alert("Get path Error!!");
        }
    });
};              

// 設定欲刪除確認視窗的筆記本名稱, fid
setModInfo = function() {
    cleanTitle();
    
    $('.btn-mod').show();
    
    // 設定欲刪除確認視窗的筆記本名稱, fid
    var obj = $(this).parents('.item'),
        fid = obj.data('id'),
        title = obj.find('.notebook').find('.info .title').text();

    $('#mod-group-box').data('id', fid);
    $('#mod-group-box').find('.group-box-content').find("input[name='title']").val(title);
};  

// 設定欲刪除確認視窗的筆記本名稱, fid
setDelInfo = function() {
    $('.btn-del').show();
    
    // 設定欲刪除確認視窗的筆記本名稱, fid
    var fid = $(this).parents('.item').data('id'),
        title = '「' + $(this).parents('.item').find('.notebook').find('.info .title').text() + '」';

    $('#del-group-box').data('id', fid);
    $('#del-group-box').find('.group-box-content').find('p').text(title);
};

// 刪除筆記本
delNote = function() {
    var fid = $(this).parents('.group-box').data('id');
    
    // 判斷欲刪除的筆記本編號有沒有成功傳到對話視窗
    if (fid === undefined) {
        // 方式1: 使用客製訊息視窗
        $('#del-group-box').find('.group-box-content').find('p').text(msg.del_no_error[nowlang]);
        $('.btn-del').hide();
        
        // 方式2: 使用內建的訊息視窗
//        alert(msg.del_no_error[nowlang]);
        return false; 
    }
    
    // 刪除筆記本
    $.ajax({
        url: '/xmlapi/index.php?action=delete-notebook&folder_id=' + fid + '&ticket=' + cticket,
        datatype: 'json',
        type:     'POST',
        async: false,
        success: function(res){
            
            if (res.code == 0) {
                if (res.message == "success") {
                    // 刪除筆記本
                    $("div[data-id='" + fid + "']").remove();

                    // 重新排版
                    rendering();
                    
                    // 關閉對話視窗
                    $('.btn-close').click();
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                alert('Ticket illegeal!');
            // 可能時機: 開分頁重複刪除
            } else if (res.code == 7) {
                alert(msg.del_no_error[nowlang]);
            } else {
                alert("Get path Error!!");
            }
        },
        error: function() {
            alert("Get path Error!!");
        }
    });
};          

// 修改筆記本
modNote = function() {
    var fid = $('#mod-group-box').data('id'),
        title = $('#mod-group-box').find("input[name='title']").val(),
        data = {folder_id:fid, folder_name:title};

    if (title.length === 0 || title.length >= 13) {
        $('.alert-error').remove();
        
        $('#mod-group-box .input-note')
            .hide()
            .after(title_error);
        
        return false;
    } else {
        $('#mod-group-box .input-note').show();
        $('#mod-group-box .alert').hide();
    }
    
    // 修改筆記本
    $.ajax({
        url: '/xmlapi/index.php?action=notebook-rename&ticket=' + cticket,
        datatype: 'json',
        type:     'POST',
        data:     JSON.stringify(data),
        async: false,
        success: function(res){
//            if (window.console) {
//                console.log(res);
//            }
            if (res.code == 0) {
                if (res.message == "success") {
                    // 更名
                    $("div[data-id='" + fid + "']").find('.notebook .cover .title').text(title);
                    
                    // 關閉對話視窗
                    $('.btn-close').click();
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                alert('Ticket illegeal!');
            } else if (res.code == 7) {// 沒異動
                closeMsgBox();
            } else {
                alert("Get path Error!!");
            }
        },
        error: function() {
            if (window.console) {
                console.log('modNote Error!!');
            }
        }
    });
};

// 檢視筆記(舊版 read.php)
readNotebook = function() {
    
    // 取筆記本編號
    var fid = $(this).parents('.item').data('id'),
        fname = encodeURIComponent($(this).parents('.item').find('.notebook .title').text()),
        obj = $("form[name='notebook']");

//    if (window.console) {
//        console.log(fname);
//        console.log(decodeURIComponent(fname));
//    }

    // 設定筆記編號
    obj.find("input[name='fid']").val(fid)
        .end()
        .find("input[name='fname']").val(fname)
        .end()
        .prop('action', appRoot + '/message/m_notebook.php');

    // 前往檢視筆記
    obj.submit();
};

// 關閉對話視窗
closeMsgBox = function() {
    $.fancybox.close();
};       

// 清除標題
cleanTitle = function() {
    $('#add-group-box')
        .find("input[name='title']").val('').end()
        .find('.input-note').show();

    $('#add-group-box, #mod-group-box')
        .find('.alert').hide();
};       

// 第一個維持第一個，其他倒序排列
rSortNotebooks = function(data) {
    var rdata = {};
    
    // 取第一個
    rdata[0] = data[0];
    
    var len = $.map(data, function(n, i) { return i; }).length;
    // 第二個以後倒序排列
    for (var i = 1; i < len; i++) {
        rdata[i] = data[len -i];
    }    
    
    return rdata;
};

$(function(){
    $('#add-notebook').on('click', cleanTitle);
			
    $("a#add-notebook").fancybox({
        'padding' : 0,
        'margin' : 0,
        'modal' : true
    });
			
    $(".item a.icon-modify").fancybox({
        'padding' : 0,
        'margin' : 0,
        'modal' : true
    });
			
    $("a.icon-delete").fancybox({
        'padding' : 0,
        'margin' : 0,
        'modal' : true
    });
    
//    $("button[data-dismiss='alert']").off('click');
//    $("button[data-dismiss='alert']").on('click', function(){
//        $(this).parents('.alert')
//            .hide();
//    });    
    
    // 取得所有筆記本
    $.ajax({
        url: '/xmlapi/index.php?action=get-notes&type=notes&extra=all&ticket=' + cticket,
        datatype: 'json',
        async: false,
        success: function(res){
//            if (window.console) {
//                console.log(res.data.notebooks);
//                
//            }
            if (res.code == 0) {
                if (res.message == "success") {
                    
                    var rNotebooks = rSortNotebooks(res.data.notebooks);
//                    if (window.console) {
//                        console.log(rNotebooks);
//                    }
            
                    showNotebooks(rNotebooks);
                } else {
                    alert(res.message);
                }
            } else if (res.code == 1) {
                alert('Ticket illegeal!');
            } else {
                alert("Get path Error!!");
            }
        },
        error: function() {
            alert("showNotebooks Error!!");
        }
    });
});
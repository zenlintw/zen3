// 給 learn\path\m_launch2.php使用
// 給 learn\path\m_launch3.php使用
var fid, title, isexists;

// 判斷有無筆記本，沒有則建立之
checkNote = function () {  
    
    // 開啟2015年版的筆記
    var cid = $("form[name='goto']").find("input[name='cid']").val(),
        cname = $("form[name='goto']").find("input[name='cname']").val();
//        username = '<?php echo $sysSession->username?>',
        fid = 'USER_' + username + '_' + cid;
//        title = cname + '<?php echo $MSG['custom_notebook'][$sysSession->lang];?>';
        title = cname + msg['custom_notebook'][nowlang];
        
    if (window.console) {
        console.log('fid', fid);
        console.log('title', title);
    }      
    
    // 取得所有筆記本
    isexists = false;
  
    $.ajax({
//        url: '/xmlapi/index.php?action=get-notes&type=notes&extra=all&ticket=<?php echo $_COOKIE['idx']?>',
        url: '/xmlapi/index.php?action=get-notes&type=notes&extra=all&ticket=' + cticket,
        datatype: 'json',
        async: false,
        success: function(res) {
//            if (window.console) {
//                console.log(res.data.notebooks);
//            }
            
            if (res.code == 0) {
                if (res.message == "success") {
                    var data = res.data.notebooks;
                    // 檢查是否有
                    for (i = 0, j = data.length; i < j; i += 1) {
//                        console.log(data[i].folder_id);
                        if (fid === data[i].folder_id) {
                            isexists = true;
                            break;
                        }
                    }
                } else {
                    if (window.console) {
                        console.log(res.message);
                    }
                }
            } 
        },
        error: function() {
            if (window.console) {
                console.log('get all notebooks Error.');
            }
        }
    });
    if (window.console) {
        console.log('筆記本是否存在');
        console.log('xmlapi\lib\note.php getNotebook()需要有第5個參數$extra，當有筆記本但沒有筆記時，就算是筆記本已經存在');
        console.log(isexists);
    }
                    
    // 判斷有無建立該課程的筆記本了，沒有則建立
    var data = {folder_id: fid, folder_name: title};
    if (isexists === false) {
        $.ajax({
            url: '/xmlapi/index.php?action=add-notebook&ticket=' + cticket,
            datatype: 'json',
            type:     'POST',
            data:     JSON.stringify(data),
            async: false,
            success: function(res){

                if (res.code == 0) {
                    if (res.message == "success") {
                        isexists = true;
                    } else {
                        if (window.console) {
                            console.log('Add notebook failure.');
                        }
                    }
                } 
            },
            error: function() {
                if (window.console) {
                    console.log('Add notebook Error.');
                }
            }
        });
    }  
    
    // 存在或者建立完成
    if (isexists === true) {
        if (window.console) {
            console.log('Notebook is exists.');
        }
        $("form[name='goto'] input[name='fid']").val(fid);
        $("form[name='goto'] input[name='fname']").val(encodeURIComponent(title));

        $("form[name='goto']").submit();
    } else {
        if (window.console) {
            console.log('Notebook is not exists.');
        }
    }
}

$(function() {
    
    // 點選筆記本圖示，進行建立筆記本
    $("a[name='course_notebook']").on('click', checkNote);
});
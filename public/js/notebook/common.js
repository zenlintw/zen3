// 回列表
goNotebookList = function() {
    location.href = 'm_notebooks_list.php';
};

// 清除HTML TAG
strip = function(html) {
   var tmp = document.createElement("DIV");
   tmp.innerHTML = html;
   return tmp.textContent || tmp.innerText || "";
};

$(function(){
    // 筆記本、筆記頁面須靠 s_sysbar frame 做搜尋，因此禁止獨立開啟該頁面
//    if ($(window.parent.frames['s_sysbar']).length === 0) {
//        // 移除搜尋列
//        $('.search-bar, .fullpage-hr').remove();
//        $("form[name='goto']").remove();
        
//        alert(msg.page_independent_error[nowlang]);
//        location.href = '/mooc/index.php';
//    } else {
//        $('.search-bar, .fullpage-hr, .back-list').show();
//        $("form[name='goto']").show();
//    }
});
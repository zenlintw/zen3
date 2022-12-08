function adv_search(keyword) {
    var obj = document.getElementById('siteHeaderSearchForm');
    if ((obj != null) && (obj != 'undefined')) {
        obj.keyword.value = keyword;
        obj.submit();
    }
}

// 輸入搜尋關鍵字後，按 ENTER 即可 submit 資料出去
$(function () {
    $('#search_box_pc').keydown(function(e){
        if(e.keyCode == 13){
             var keywordVal = $('#search_box_pc').val();

             adv_search(keywordVal);
            
            return false;
        }
    });
    
    $('#search_box_phone').keydown(function(e){
        if(e.keyCode == 13){

        	var keywordVal = $('#search_box_phone').val();

            adv_search(keywordVal);
            
            return false;
        }
    });
    
});

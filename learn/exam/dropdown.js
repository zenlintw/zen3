$(function() {
    // 解決下拉選單在CHROME無法顯示答案正確特效
    var sel = $("select");
    $.each(sel, function(k, v) {
        
//        if (window.console) {console.log($(v).val());}
//        if (window.console) {console.log($(v).find("option[data-status='c']").val());}
        
        if ($(v).val() === $(v).find("option[data-status='c']").val()) {
            $(v).css('border-color', '#008f29');
            $(v).css('border-width', '0.2em');
        }
    });
});
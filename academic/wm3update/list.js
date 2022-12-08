$(document).ready(function (){
    // 更新系統版本
    $.ajax({
        url: '/public/meta/version.txt',
        success: function (response) {
            $('#wm-version').text(response);
        }
    });
    // 更新編譯版本
    $.ajax({
        url: '/public/meta/rev.txt',
        success: function (response) {
            if (/^\d+$/.test(response)) {
                $('#wm-reversion').text(' (r' + parseInt(response, 10) + ')');
            } else {
                $('#wm-reversion').text(' (' + response + ')');
            }
        }
    });
    // 更新XML api版本
    $.ajax({
        url: '/xmlapi/version.txt',
        success: function (response) {
            $('#xmlapi-version').text(response);
        }
    });
});
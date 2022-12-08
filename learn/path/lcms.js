if (window.console) {console.log('lcms.js');}
 
function format_key(key) {
    while (key.length < 16) {
        key = key + '\u0000';
    }
    return key;
}

/* 寫入影片監聽事件到wm資料表 */
function setReadVideoLog(msg) {
    
    if (window.console) {console.log('setReadVideoLog');}
//    if (window.console) {console.log('msg: ', msg);}
    
    var smainFrame = window.parent.frames[2].document;
    var inFramePathtree = $(smainFrame).find('#pathtree');
//    if (window.console) {console.log('smainFrame', $(inFramePathtree).contents().find("input[name='course_id']").val());}
    var encid = $(inFramePathtree).contents().find("input[name='course_id']").val();
    var rid = $(inFramePathtree).contents().find("input[name='prev_node_id']").val();
    
    // 取pathtree.php中隱藏欄位，編碼後的課程編號與SCO編號
    msg['encid'] = encid;
    msg['rid'] = rid;
    
//    if (window.console) {console.log('msg: ', msg);}
    
    // 組加密的資料
    var encrypt_data;
    var key = 'readlcmsvideolog';
    key = format_key(key);
    var iv = 'fXyFiQCfgiKcyuVNCGoILQ==';
    key = CryptoJS.enc.Utf8.parse(key);
    iv = CryptoJS.enc.Base64.parse(iv);
    var ciphertext = CryptoJS.AES.encrypt(JSON.stringify(msg), key, {iv: iv});
    encrypt_data = ciphertext.toString();
//    if (window.console) {console.log('encrypt_data', encrypt_data);}
    
    // 寫入影片監聽事件到wm資料表
    $.ajax({
        'url': '/mooc/controllers/course_ajax.php',
        'type': 'POST',
        'data': {'action' : 'setReadLcmsVideoLog', 'msg': encrypt_data},
        'dataType': 'json',
        'success': function(res) {
            if (window.console) {
                console.log(res);
            }
        },
        'error': function() {
            if (window.console) {
                console.log('setReadLcmsVideoLog ajax error!');
            }
        }
    });
}
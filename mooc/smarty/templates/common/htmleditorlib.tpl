<script type="text/javascript">
    var htmleditorname = '{$htmleditorname}';// 要變成編輯器的元素名稱
    var bw0 = '{$bw0}';
    var setUploadFun = '{$setUploadFun}';
    var isIE11 = '{$isIE11}';
    var ckeditorToolbar = '{$ckeditorToolbar}';
    var nowlang = '{$nowlang}';// 目前語系
</script>
{literal}
<script>
    var trans = {
        'Big5': 'zh',
        'GB2312': 'zh-cn',
        'en': 'en',
        'EUC-JP': 'ja',
        'user_define': 'en'
    }; // 編輯器的語系
    var lang = trans[nowlang];// 給予編輯器語系
</script>
<script type="text/javascript" language="javascript" lang="zh-tw" src="/lib/ckeditor/ckeditor.js?20190730"></script>
<script type="text/javascript" language="javascript">
    var editor = null;

    function editorFunc() {
        editor = CKEDITOR.replace(htmleditorname, {
            'language': lang
        });
        editor.getHTML = function() {
            return editor.getData();
        };
        editor.setHTML = function(val) {
            return editor.setData(val, function() {}, true);
        };
    }

    function editorFuncWithUpload() {
        // 不存在，才宣告
        if (editor === null) {
            editor = CKEDITOR.replace(htmleditorname, {
                'language': lang,
                'filebrowserImageBrowseUrl': '/lib/kcfinder/browse.php?type=images',
                'filebrowserImageUploadUrl': '/lib/kcfinder/upload.php?type=images',
                'toolbar': (ckeditorToolbar.length)?ckeditorToolbar:'WM',
                'resize_enabled':(ckeditorToolbar=='PHONE')?false:true
            });
        }
        editor.getHTML = function() {
            return editor.getData();
        };
        editor.setHTML = function(val) {
            return editor.setData(val, function() {}, true);
        };
    }

    function getEditorInstance(id) {
        var ed = CKEDITOR.instances[id];
        if (ed === undefined) {
            ed = {
                'getHTML': function() {
                    return '';
                },
                'setHTML': function() {},
                'GetHTML': function() {
                    return '';
                },
                'SetHTML': function() {}
            };
        } else {
            ed.getHTML = function() {
                return ed.getData();
            };
            ed.GetHTML = function() {
                return ed.getData();
            };
            ed.setHTML = function(val) {
                return ed.setData(val, function() {}, true);
            };
            ed.SetHTML = function(val) {
                return ed.setData(val, function() {}, true);
            };
        }
        return ed;
    }
    
    if (bw0 == 'MSIE') {
        if (setUploadFun === '1') {
            window.attachEvent("onload", editorFuncWithUpload);
        } else {
            window.attachEvent("onload", editorFunc);
        }
    } else {
        if (isIE11 === '1') { // IE 11
            if (setUploadFun === '1') {
                window.addEventListener("load", editorFuncWithUpload);
            } else {
                window.attachEvent("onload", editorFunc);
            }
        } else {
            if (setUploadFun === '1') {
                window.addEventListener("load", editorFuncWithUpload, false);
            } else {
                window.addEventListener("load", editorFunc, false);
            }
        }
    } 
</script>
{/literal}
<form name="lcmsFrm" method="post" target="_blank" style="display: none;"></form>
<script language="javascript">
    var lcmsEnable = {$lcmsEnable};
    {if 0}
        // todo: 未來使用在別的頁面 cid 取值方式可能需要作調整 1.給sysSession 2.設定欄位
    {/if}
    {literal}
        // 代登入LCMS
        var gotolcms = function(){
            cid = $('.lcms-item .cover').first().data('id');// 必須等到課程區塊都載入完成，才能取到值
            if (!lcmsEnable || cid === null || cid === undefined) {
                return;
            }
            // 與後台上傳檔案統一，改成開在分頁頁籤，改使用 form submit 處理
            document.lcmsFrm.action = "/teach/course/lcms.php?action=resources&cid=" + cid;
            document.lcmsFrm.submit();
            // window.open('/teach/course/lcms.php?action=login&cid=' + cid, 'lcmsDialog', '');
        }
    {/literal}
</script>
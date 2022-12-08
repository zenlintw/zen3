<iframe id="bgFrame" name="bgFrame" style="display: none;"></iframe>
<script language="javascript">
    var lcmsEnable = {$lcmsEnable};
    {if 0}
        // todo: 未來使用在別的頁面 cid 取值方式可能需要作調整 1.給sysSession 2.設定欄位
    {/if}
    {literal}
        $(function() {
            cid = $('.lcms-item .cover').first().data('id');
            if (!lcmsEnable || cid === null || cid === undefined) {
                return;
            }
            $("#bgFrame").attr('src', '/teach/course/lcms.php?action=login&nodir=1&cid=' + cid);
        });
    {/literal}
</script>
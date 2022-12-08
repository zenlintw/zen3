<style>
{literal}
@media (max-width: 767px) {
    .data5 > .process-btn > .level1 > .main-text {
        font-size: 2em;
    }

    .process-btn {
        min-width: 50px !important;
    }

    .process-btn .level1 img{
        width: 40px;
    }

    .data5 > .process-btn > .level2 .btn {
        height: initial;
        line-height: initial;
    }

    #div-exam-percent {
        display: none;
    }

    #presentPanel {
        padding: 10px;
    }

    .cssTabs {
        font-size: 18px;
        line-height: 24px;
    }

    .cssTrHelp {
        font-size: 14px;
        line-height: 28px;
    }

    .cssTrOdd {
        font-size: 14px;
        line-height: 28px;
    }

    .cssTrEvn {
        font-size: 14px;
        line-height: 28px;
    }

    .cssBtn {
        font-size: 14px;
        height: 28px;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    #infoPanel{
        padding: 10px;
    }
    
    #infoPanel > table {
        width: 100% !important;
    }

    #infoTable{
        width: 100% !important;
        min-width: 300px;
    }

    #infoTable1{
        width: 100% !important;
        min-width: 300px;
    }

    #presentPanel > table {
        width: 100% !important;
    }

    #responseForm > table {
        width: 100% !important;
    }
    
    blockquote {
        width:80%;
    }
    
    .level2 a:first-child {
        min-width: 6em;
        margin-bottom:1em;
    }
    
    .btn-orange {
        min-width: 6em;
        margin-bottom:1em;
    }
    
    .data5 > .process-btn > .level2 .btn {
        margin-left: 0em;
        margin-top: 1em;
    }

}
{/literal}
{if $smarty.server.SCRIPT_NAME eq '/learn/exam/view_result.php'}
table {ldelim}
    width: 100% !important;
{rdelim}
{/if}
@media (max-width: 767px) {ldelim}
    {if !$isCourseTeacher}
    #div-try-button {ldelim}
        display: none;
    {rdelim}
    {/if}
{rdelim}
</style>


<script type="text/javascript">
    {literal}
    window.onload = function() {
        $("canvas").css({'vertical-align':'unset'});
    };
    {/literal}
</script>
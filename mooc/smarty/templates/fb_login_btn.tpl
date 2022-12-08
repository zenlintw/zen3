<div class="btn fb-btn"><i class="fb-icon"></i>{'loginwithfb'|WM_Lang}</div>
<script language="javascript">
    var
        clientId = '{$FB_APP_ID}',
        redirectUri = '{$appRoot}/mooc/fb_login.php';

    {literal}
    $(function () {
        $('.fb-btn').click(function () {
            window.location.href = 'https://www.facebook.com/dialog/oauth?client_id=' + clientId + '&redirect_uri=' + redirectUri + '&scope=email';
        });
    });
    {/literal}
</script>
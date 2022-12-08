<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<title>IRS</title>
<style type="text/css">
{literal}
    body {
        background-color: #FFFFFF;
        font-family: 微軟正黑體;
    }
    #login_top {
        height: 210px;
        max-width: 500px;
        margin: 0 auto;
    }
    .logo-block{
        position: relative;top: -200px;text-align: center;
    }
    .logo-image{
        width:139px;
        height: 139px;
    }

    .logo-font {
        color:#FFFFFF;
        font-size: 28px;
        line-height: 35px;
        font-weight: bolder;
    }

    .button-login {
        background: #29933A;
        border: 1px solid #FFFFFF;
        line-height: 40px;
        color: #FFFFFF;
        font-weight: bold;
        margin: 0 auto;
        border-radius: 100px;
        font-size:2rem;
        letter-spacing:-0.38px;
        text-align:center;
    }

{/literal}
</style>
<script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>
<script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>
</head>
<body>
    <div id="login_top">
        <img src="/public/images/irs/login_top_phone.png" style="width:100%;height: 210px; max-width: 500px;">
        <div class="logo-block">
            <div><img src="/public/images/irs/pic_login_phone.png" class="logo-image" /></div>
            <div class="logo-font">{'iSunFuDon'|WM_Lang}</div>
        </div>
    </div>

    <div style="height:40px;" />
    <div style="max-width: 500px;text-align: center;margin: 0 auto;">
        <div class="container" style="max-width: 500px;">
            <div class="row" style="margin-top:5rem;margin-bottom:5rem;text-align: center;">
                <div class="col-md-12" style="color: #455868;font-size:2.5rem;letter-spacing: -2px;font-weight:Bold;text-align:center;">
                    {$showMessage}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
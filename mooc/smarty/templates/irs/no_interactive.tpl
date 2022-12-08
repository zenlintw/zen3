<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<title>愛上互動</title>
<style type="text/css">
{literal}

    body {
        font-family: 微軟正黑體,Arial, Helvetica, sans-serif;
    }
    
    #bg {
        background-image:url('/public/images/irs/bg01_phone.png');
        background-repeat:no-repeat;
        background-position: center center;
        background-size: cover;
    }
    
    .center{
        margin: 0 5%;
        color:#FFFFFF;
        font-size:2.5rem;
        overflow : hidden;
        text-overflow : ellipsis;
        white-space : nowrap;
        text-align:center;
        position: relative;
        top:35%;
    }
    
    @media screen and (min-width:900px) { 
        #bg {
            background-image:url('/public/images/irs/iospad_2048_1536_163.png');
        }
    
    }
    
{/literal}
</style>
<script type="text/javascript" src="/lib/jquery/jquery.min.js"></script>

</head>
<body>

    <div id="bg">
        
        <div style="height:15%;background:rgba(255,255,255,0.20);">
            <div class="center" style="text-shadow: 0px 2px 4px #240f06;">
                {$title}
            </div>
        </div>
        <div style="height:85%;background:rgba(255,255,255,0.80);">
            <div class="container" style="max-width:750px;height:85%;">
                <div>
                    <div class="center-block" style="height:300px;width:300px;background-repeat:no-repeat;background-image:url('/public/images/irs/no_q_pad.png');background-position: center center;background-size: cover;"></div>
                </div>
                <div class="row" style="margin-top:1rem;margin-bottom:5rem;">
                    <div class="col-md-12" style="color: #455868;font-size:2.5rem;letter-spacing: -2px;font-weight:Bold;text-align:center;">{'status_over'|WM_Lang}</div>
                </div>
                
                <div class="row" style="margin-top:2.5rem;">
                    <div class="col-md-3 col-xs-2"></div>
                    <a href="#" onclick="location.replace('/mooc/user/code.php');"><div class="col-md-6 col-xs-8" style="border: 1px solid #E8483F;border-radius: 100px;color:#E8483F;font-size:2rem;letter-spacing:-0.38px;font-weight:Bold;text-align:center;">回加入互動頁面</div>
                    <div class="col-md-3 col-xs-2"></div></a>
                </div>
                
             </div>
        </div>
    </div>

</body>
</html>


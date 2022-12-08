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
                    <div class="center-block" style="height:20rem;width:20rem;background-repeat:no-repeat;background-image:url('/public/images/irs/Q_ok_success_phone.svg');background-position: center center;background-size: cover;"></div>
                </div>
                <div class="row" style="margin-top:5rem;margin-bottom:5rem;">
                    <div class="col-md-12" style="color: #455868;font-size:2.5rem;letter-spacing: -2px;font-weight:Bold;text-align:center;">{'submit_success'|WM_Lang}
                    </div>

                </div>
                {if ($type=='exam')}
                <div class="row">
                    <div class="col-md-3 col-xs-2"></div>
                    <div class="col-md-6 col-xs-8" style="background: #E8483F;border: 1px solid #E8483F;border-radius: 100px;color:#FFFFFF;font-size:2rem;letter-spacing:-0.38px;font-weight:Bold;text-align:center;">{'exam_score'|WM_Lang}{$score}{'score'|WM_Lang}</div>
                    <div class="col-md-3 col-xs-2"></div>
                </div>
                {/if}

                <div class="row" style="margin-top:2.5rem;">
                    <div class="col-md-3 col-xs-2"></div>
                    <a><div class="col-md-6 col-xs-8" style="border: 1px solid #E8483F;border-radius: 100px;color:#E8483F;font-size:2rem;letter-spacing:-0.38px;font-weight:Bold;text-align:center;" onclick="goOther();">切換至其他互動碼</div></a>
                    <div class="col-md-3 col-xs-2"></div>
                </div>

                <div class="row" style="margin-top:2.5rem;">
                    <div class="col-md-3 col-xs-2"></div>
                    <a><div class="col-md-6 col-xs-8" style="border: 1px solid #E8483F;border-radius: 100px;color:#E8483F;font-size:2rem;letter-spacing:-0.38px;font-weight:Bold;text-align:center;" onclick="get_exam();">重新整理</div></a>
                    <div class="col-md-3 col-xs-2"></div>
                </div>

             </div>
        </div>
    </div>
<script type="text/javascript">
    var code = '{$code}';
    var exam_id = '{$exam_id}';
    var type = '{$type}';
    var t_status;
    {literal}

    function get_exam() {
        $.ajax({
            'url': '/mooc/irs/irs_status.php',
            'type': 'POST',
            'data': {'action': 'get_exam','course_id':code,'exam_id':exam_id,'qti_type':type},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    var url = '/mooc/irs/check.php?action=start&goto='+ res.goto;
                    location.replace(url);
                } else {
                    clearTimeout(t_status);
                    t_status = setTimeout('get_exam()', 10000);
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    }

    function goOther() {
        location.replace('/mooc/user/code.php');
    }

    $(document).ready(function() {
        get_exam();
    });
{/literal}
</script>
</body>
</html>



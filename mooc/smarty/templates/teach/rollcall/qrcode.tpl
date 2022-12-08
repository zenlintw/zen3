<div class="">
    <div class="qrcode_left">
         <div class="qrcode active"><iframe id="iframeQrcode" src="about:blank" frameborder="0" align="center" scrolling="no" width="500" height="500"></iframe></div>
        <div class="qrcode_tip active">{'scan_qrcode'|WM_Lang}</div>
        <div class="prepare_img center-block prepare"></div>
        <div class="qrcode_tip prepare"></div>        
    </div>                
    <div class="qrcode_right">
        <div class="exam_name">{$title}</div>

        <div class="status active">{'status_active'|WM_Lang}</div>
        <div class="status prepare">{'status_prepare'|WM_Lang}</div>
        
        <div class="active" style="margin:20px 0;width:100%;font-size:24px;">
            {*<div class="row" style="font-size:90px;">
                <div id="major_count" class="col-md-4 text-center">{$major_count}</div>
                <div id="submit_num" class="col-md-4 text-center">0</div>
                <div class="col-md-4 text-center"><span id="submit_rate">0</span><span style="font-size:50px">%</span></div>
            </div>
            <div class="row">
                <div class="col-md-4 text-center">{'major_count'|WM_Lang}</div>
                <div class="col-md-4 text-center">{'submit_num'|WM_Lang}</div>
                <div class="col-md-4 text-center">{'submit_rate'|WM_Lang}</div>
            </div>
            <div class="row" style="margin-top:20px">
                <a href="#" onclick="get_status();"><div class="col-md-4 text-center" style="line-height: 100px;">{'refresh'|WM_Lang}<img class="refresh" src="/public/images/irs/ic_refresh.png"></div></a>
            </div>*}
            
            <div class="row active_number">
                <div id="major_count" class="col-md-4 text-center">{$major_count}</div>
                <div id="submit_num" class="col-md-4 text-center">0</div>
                <div class="col-md-4 text-center"><span id="submit_rate">0</span><span style="font-size:50px">%</span></div>
            </div>
            <div class="row">
                <div class="col-md-4 text-center">{'major_count'|WM_Lang}</div>
                <div class="col-md-4 text-center">{'submit_num'|WM_Lang}</div>
                <div class="col-md-4 text-center">{'submit_rate'|WM_Lang}</div>
            </div>
        </div>
        
        <div class="tip prepare"><ul><li><span style="font-size:20px">{'resolution_tip2'|WM_Lang}</span></li><li>{'pic_tip'|WM_Lang}</li></ul><div class="tip_img"></div></div>
        
        <button id="over_button" href="#over-box" class="button active">{'button_over'|WM_Lang}</button>
        <button id="start_button" href="#start-box" class="button prepare">{'button_start'|WM_Lang}</button>

        
    </div>
</div>

<div class="over-box" id="over-box">
    <div style="background: #FFFFFF;box-shadow: 1px 5px 5px 0 rgba(0,0,0,0.50);border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 298px;">
    <div style="background:#E8483F;height:6px;"></div>
    <div class="icon_warn"></div>
    <div style="text-align:center;">{'alert_over'|WM_Lang}</div>
    <div>
        <a href="#" onclick="over();"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="close_fancy();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
</div>

<div class="over-box" id="start-box">
    <div style="background: #FFFFFF;box-shadow: 1px 5px 5px 0 rgba(0,0,0,0.50);border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 298px;">
    <div style="background:#E8483F;height:6px;"></div>
    <div class="icon_warn"></div>
    <div style="text-align:center;">{'alert_start'|WM_Lang}</div>
    <div>
        <a href="#" onclick="start();"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="close_fancy();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
</div>
        
            
        

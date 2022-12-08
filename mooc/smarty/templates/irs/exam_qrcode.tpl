<div class="">
    <div style="width:635;float:left;">
        <div id="div_qrcode">
            <div class="qrcode active"><iframe id="iframe_qrcode" src="{$qrcode}" frameborder="0" align="center" scrolling="no" width="465" height="465"></iframe></div>
            <div class="qrcode_tip active">{'scan_qrcode'|WM_Lang}</div>
        </div>
        <div id="div_code" style="display:none;">    
            <div class="qrcode active code"><div class="code_number"><span class="title">互動碼</span><br>{$code}</div><div class="input_tip">輸入上方互動碼，加入互動</div></div>
        </div>
        <div class="prepare_img center-block prepare"></div>
        <div class="qrcode_tip prepare"></div>     

        <div class="switcher active">
          <input type="radio" name="balance" value="yin" id="yin" class="switcher__input switcher__input--yin" onclick="show_qrcode();">
          <label for="yin" class="switcher__label"> QRcode</label>
          
          <input type="radio" name="balance" value="yang" id="yang" class="switcher__input switcher__input--yang" onclick="show_code();">
          <label for="yang" class="switcher__label">互動碼</label>
          
          <span class="switcher__toggle"></span>
        </div>


    </div>                
    <div style="width:640;float:left;padding:0 10">
        <div class="exam_name">{$title}{if $forGuest==1}(不記名){/if}</div>

        <div class="status active">{'status_active'|WM_Lang}</div>
        <div class="status prepare">{'status_prepare'|WM_Lang}</div>
        
        <div class="active" style="margin:20px 0;width:100%;font-size:24px;">
            {*<div class="row" style="font-size:90px;">
                <div id="submit_num" class="col-md-4 text-center">0</div>
                <div id="start_num" class="col-md-4 text-center">0</div>
                <div class="col-md-4 text-center"><span id="submit_rate">0</span><span style="font-size:50px">%</span></div>
            </div>
            <div class="row">
                <div class="col-md-4 text-center">{'submit_num'|WM_Lang}</div>
                <div class="col-md-4 text-center">{'join_num'|WM_Lang}</div>
                <div class="col-md-4 text-center">{'submit_rate'|WM_Lang}</div>
            </div>
            <div class="row" style="margin-top:20px">
                <a href="#" onclick="get_status();"><div class="col-md-4 text-center" style="line-height: 100px;">{'refresh'|WM_Lang}<img class="refresh" src="/public/images/irs/ic_refresh.png"></div></a>
                <div class="col-md-4 text-center"><span id="major_count" style="font-size:50px">0</span><br>{'total_num'|WM_Lang}</div>
                <div class="col-md-4 text-center"><span id="start_rate" style="font-size:50px">0</span><span style="font-size:30px">%</span><br>{'join_rate'|WM_Lang}</div>
            </div>*}
            {if $forGuest==1}
                <div class="row" style="font-size:90px;">
                    <div id="submit_num" class="col-md-12 text-center">0</div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">{'submit_num'|WM_Lang}</div>
                </div>
            {else}
                <div class="row" style="font-size:90px;">
                    <div id="submit_num" class="col-md-4 text-center">0</div>
                    <div id="major_count" class="col-md-4 text-center">0</div>
                    <div class="col-md-4 text-center"><span id="start_rate">0</span><span style="font-size:50px">%</span></div>
                </div>
                <div class="row">
                    <div class="col-md-4 text-center">{'submit_num'|WM_Lang}</div>
                    <div class="col-md-4 text-center">{'total_num'|WM_Lang}</div>
                    <div class="col-md-4 text-center">{'join_rate'|WM_Lang}</div>
                </div>
            {/if}
            
        </div>
        
        <div class="tip prepare"><ul><li><span style="font-size:20px">{'resolution_tip2'|WM_Lang}</span></li></ul><br><br><br><br></div>
        
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
        
            
        

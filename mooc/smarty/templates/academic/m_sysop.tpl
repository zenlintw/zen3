<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<link href="/theme/{$cur_theme}/academic/wm.css" rel="stylesheet" />
<link href="/public/css/settings.css" rel="stylesheet" />
<style>
    {literal}
        .admin-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
        }
        select {
            height: 2.5em;
        }
        .group-box {
            display: none;
        }
        .group-box > .group-box-content {
            margin: 20px 20px 20px 20px;
        }
        .group-box > .group-box-content input {
            font-size: 2em;
            height: 1.2em;
        }
        .group-box > .group-box-content p {
            text-align: center;
            font-size: 2em;
            margin: 1em 2em 0 2em;
            line-height: 1em;
        }
        .group-box > .group-box-content > .input-note {
            text-align: right;
            color: red;
            font-size: 0.9em;
        }
        .group-box > .group-box-title {
            background-color: #F3800F;
            height: 50px;
            line-height: 50px;
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            padding-left: 20px;
        }
        .group-box > .group-box-button {
            margin: 20px 20px 20px 20px;
            float: right;
        }
    {/literal}
</style>
<div class="box1">
    <div class="title">{'tabs_title'|WM_Lang}</div>
    <div class="content">
        {if $curLevel gte $sysRoles.root}
            <div id="d0-3" class="box2">
                <div class="title">{'permit_root'|WM_Lang}</div>
                <div class="operate">
                    <button class="btn btn-blue addBtn" onclick="addAdmin(3);">{'btn_add'|WM_Lang}</button>
                    {if $curUser eq $sysRootAccount}
                        <button class="btn btn-blue delBtn" onclick="chkAdmin(this);">{'btn_delete'|WM_Lang}</button>
                    {/if}
                </div>
                <div class="content">
                    <form>
                        <div class="admin-list">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width='10%' style="text-align: center;">{'th_select'|WM_Lang}</th>
                                        <th width='30%'>{'th_user'|WM_Lang}</th>
                                        <th width='30%'>{'th_creator'|WM_Lang}</th>
                                        <th width='30%'>{'th_create_time'|WM_Lang}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from = $sysop_m  key = k2 item = v2}
                                        {if $v2.level eq $sysRoles.root}
                                            <tr> 
                                                <td style="text-align: center;">
                                                    {if $v2.username eq $sysRootAccount}
                                                        &nbsp;
                                                    {elseif $curUser eq $sysRootAccount}
                                                        <input type="checkbox" name="ckUname[]" value="{$v2.username},{$v2.school_id}">
                                                    {/if}
                                                </td>
                                                <td>{$v2.username}({$user[$v2.username]})</td>
                                                {if $v2.creator eq null}
                                                    <td>{$sysRootAccount}({$user.$sysRootAccount})</td>
                                                {else}
                                                    <td>{$v2.creator}({$user[$v2.creator]})</td>
                                                {/if}
                                                <td>{$v2.create_time|date_format:$timeConfig}</td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box2">
                <div class="title">{'allow_ip'|WM_Lang}</div>
                <div class="content">
                    <textarea {if $curUser eq $sysRootAccount} id="opnIP-0" name="opnIP[0]"{else if} readonly {/if}style="width: 100%; width: calc(100% - 2px); padding: 0; height: 80px;" placeholder="{'msg_limit_ip'|WM_Lang}">{strip}
                        {foreach from = $sysop_r[10001]  key = ipk item = ipv}
                            {if $ipv.username eq $sysRootAccount}
                                {$ipv.allow_ip}
                                {php}break;{/php}
                            {/if}
                        {/foreach}
                    {/strip}</textarea>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="divider-horizontal" style="margin: 1em 0;"></div>
        {/if}
        <select id="chgSch" class="pull-right">
            {foreach from = $schoolData key = sk item = sv}
                <option value="{$sk}" {if $sk eq $curSchool }selected{/if}>{$sv.school_name}({$sk})</option>
            {/foreach}
        </select>
        <div class="clearfix"></div>
            {foreach from = $sysop_r  key = k item = v}
                <div id="sch{$k}" class="schSetDiv" {if $k neq $curSchool}style="display:none;"{/if}>
                    {if $curLevel gte $sysRoles.administrator}
                        <div id="d{$k}-2" class="box2">
                            <div class="title">{'permit_administrator'|WM_Lang}</div>
                            <div class="operate">
                                {if $curLevel gte $sysRoles.administrator}
                                    <button class="btn btn-blue addBtn" onclick="addAdmin(2);">{'btn_add'|WM_Lang}</button>
                                    {if $curLevel gt $sysRoles.administrator}
                                        <button class="btn btn-blue delBtn" onclick="chkAdmin(this);">{'btn_delete'|WM_Lang}</button>
                                    {/if}
                                {/if}
                            </div>
                            <div class="content">
                                <form>
                                    <div class="admin-list">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th width='10%' style="text-align: center;">{'th_select'|WM_Lang}</th>
                                                    <th width='30%'>{'th_user'|WM_Lang}</th>
                                                    <th width='30%'>{'th_creator'|WM_Lang}</th>
                                                    <th width='30%'>{'th_create_time'|WM_Lang}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            {foreach from = $v  key = k2 item = v2}
                                                {if $v2.level eq $sysRoles.administrator}
                                                    <tr> 
                                                        <td style="text-align: center;">
                                                            {if $v2.username eq $sysRootAccount}
                                                                &nbsp;
                                                            {elseif $curLevel gt $v2.level}
                                                                <input type="checkbox" name="ckUname[]" value="{$v2.username},{$v2.school_id}">
                                                            {/if}
                                                        </td>
                                                        <td>{$v2.username}({$user[$v2.username]})</td>
                                                        {if $v2.creator eq null}
                                                            <td>{$sysRootAccount}({$user.$sysRootAccount})</td>
                                                        {else}
                                                            <td>{$v2.creator}({$user[$v2.creator]})</td>
                                                        {/if}
                                                        <td>{$v2.create_time|date_format:$timeConfig}</td>
                                                    </tr>
                                                {/if}    
                                            {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    {/if}
                    {if $curLevel gte $sysRoles.manager}
                        <div id="d{$k}-1" class="box2">
                            <div class="title">{'permit_manager'|WM_Lang}</div>
                            <div class="operate">
                                {if $curLevel gte $sysRoles.manager}
                                    <button class="btn btn-blue addBtn" onclick="addAdmin(1);">{'btn_add'|WM_Lang}</button>
                                    {if $curLevel gt $sysRoles.manager}
                                        <button class="btn btn-blue delBtn" onclick="chkAdmin(this);">{'btn_delete'|WM_Lang}</button>
                                    {/if}
                                {/if}
                            </div>
                            <div class="content">
                                <form>
                                    <div class="admin-list">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th width='10%' style="text-align: center;">{'th_select'|WM_Lang}</th>
                                                    <th width='30%'>{'th_user'|WM_Lang}</th>
                                                    <th width='30%'>{'th_creator'|WM_Lang}</th>
                                                    <th width='30%'>{'th_create_time'|WM_Lang}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            {foreach from = $v  key = k2 item = v2}
                                                {if $v2.level eq $sysRoles.manager}
                                                    <tr> 
                                                        <td style="text-align: center;">
                                                            {if $v2.username eq $sysRootAccount}
                                                                &nbsp;
                                                            {elseif $curLevel gt $v2.level}
                                                                <input type="checkbox" name="ckUname[]" value="{$v2.username},{$v2.school_id}">
                                                            {/if}
                                                        </td>
                                                        <td>{$v2.username}({$user[$v2.username]})</td>
                                                        {if $v2.creator eq null}
                                                            <td>{$sysRootAccount}({$user.$sysRootAccount})</td>
                                                        {else}
                                                            <td>{$v2.creator}({$user[$v2.creator]})</td>
                                                        {/if}
                                                        <td>{$v2.create_time|date_format:$timeConfig}</td>
                                                    </tr>
                                                {/if}    
                                            {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    {/if}
                    <div id="d{$k}-0"class="box2">
                        <div class="title">
                            {'permit_course_opener'|WM_Lang}
                            <div style="  display: inline-block; font-size: 0.8em;">
                                <input type="checkbox" name="setTea[{$k}]" value="1" {if $schoolData.$k.setTea eq true}checked="checked"{/if}> {'set_default_teacher'|WM_Lang}
                            </div>
                        </div>
                        <div class="operate">
                            <button class="btn btn-blue addBtn"  onclick="addAdmin(0);">{'btn_add'|WM_Lang}</button>
                            <button class="btn btn-blue delBtn" onclick="chkAdmin(this);">{'btn_delete'|WM_Lang}</button>
                        </div>
                        <div class="content">
                            <form>
                                <div class="admin-list">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th width='10%' style="text-align: center;">{'th_select'|WM_Lang}</th>
                                                <th width='30%'>{'th_user'|WM_Lang}</th>
                                                <th width='30%'>{'th_creator'|WM_Lang}</th>
                                                <th width='30%'>{'th_create_time'|WM_Lang}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach from = $v  key = k2 item = v2}
                                            {if $v2.level eq 1024}
                                                <tr> 
                                                    <td style="text-align: center;">
                                                        {if $v2.username eq $sysRootAccount}
                                                            &nbsp;
                                                        {elseif $curLevel gt $v2.level}
                                                            <input type="checkbox" name="ckUname[]" value="{$v2.username},{$v2.school_id}">
                                                        {/if}
                                                    </td>
                                                    <td>{$v2.username}({$user[$v2.username]})</td>
                                                    {if $v2.creator eq null}
                                                        <td>{$sysRootAccount}({$user.$sysRootAccount})</td>
                                                    {else}
                                                        <td>{$v2.creator}({$user[$v2.creator]})</td>
                                                    {/if}
                                                    <td>{$v2.create_time|date_format:$timeConfig}</td>
                                                </tr>
                                            {/if}    
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                    {if $curLevel gte $sysRoles.administrator}
                        <div class="box2">
                            <div class="title">{'allow_ip'|WM_Lang}</div>
                            <div class="content">
                                <textarea id="opnIP-{$k}" name="opnIP[{$k}]" style="width: 100%; width: calc(100% - 2px); padding: 0; height: 80px;" placeholder="{'msg_limit_ip'|WM_Lang}">{strip}
                                    {foreach from = $sysop_r[$k]  key = ipk item = ipv}
                                        {if $ipv.level eq $sysRoles.administrator}
                                            {$ipv.allow_ip}
                                            {php}break;{/php}
                                        {/if}
                                    {/foreach}

                                {/strip}</textarea>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    {/if}
                </div>
            {/foreach}
        </div>
    </div>
</div>

{*新增管理員*}
<a href="#addDiv" id="addDivBtn"></a>
<div id="addDiv" class="group-box">
    <div class="group-box-title">{'btn_add_admin'|WM_Lang}</div>
    <div class="group-box-content">
        <div>
            <form id="fmProp">
                {'th_username'|WM_Lang}
                <input type="text" name="opnName" maxlength="32" class="span5" value="">
                <span id="firstName"></span>
                {if $level gte $sysRoles.administrator}
                    <span class="lcms-red-starmark">* </span>{'th_school'|WM_Lang}
                    <select type="text" name="opnSName" class="span5">
                        {foreach from = $schoolData key = sk item = sv}
                            <option value="{$sk}">{$sv.school_name}({$sk})</option>
                        {/foreach}
                    </select>
                {/if}
                <input type="hidden" id="opnPermit" name="opnPermit" value="0">
            </form>
        </div>         
    </div>
    <div class="group-box-button">
        <button type="button" class="btn btn-warning btnNormal btn-ok" onclick="saveAdmin();">{'btn_ok'|WM_Lang}</button>
        <button type="button" class="btn aNormal btnNormal margin-left-15 btn-close" onclick="close_fancy();">{'btn_cancel'|WM_Lang}</button>
    </div>
</div>
{*刪除管理員*}
<a href="#delDiv" id="delDivBtn"></a>
<div id="delDiv" class="group-box">
    <div class="group-box-title">{'tabs_del_admin'|WM_Lang}</div>
    <div class="group-box-content">    
    </div>
    <div class="group-box-button">
        <button type="button" class="btn btn-warning btnNormal btn-ok" onclick="delAdmin();">{'btn_ok'|WM_Lang}</button>
        <button type="button" class="btn aNormal btnNormal margin-left-15 btn-close" onclick="close_fancy();">{'btn_cancel'|WM_Lang}</button>
    </div>
</div>

{*<script type="text/javascript" src="{$appRoot}/lib/xmlextras.js"></script>*}
<script type="text/javascript" src="{$appRoot}/lib/dragLayer.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/common.js"></script>
<script>
    var MSG_INPUT_USERNAME = "{'msg_need_username'|WM_Lang}";
	var MSG_INPUT_IP       = "{'msg_need_ip'|WM_Lang}";
	var MSG_ADD_SUCCESS    = "{'msg_add_success'|WM_Lang}";
	var MSG_UPDATE_SUCCESS = "{'msg_update_success'|WM_Lang}";
	var MSG_SELECT_ADMIN   = "{'msg_need_select'|WM_Lang}";

	var MSG_SELECT_ALL = "{'select_all'|WM_Lang}";
	var MSG_SELECT_CANCEL = "{'cancel_all'|WM_Lang}";
    
    var total_page = "{$total_page}";
    var delMsg = {$delMsg|@json_encode};
{*    var xmlDocs = null, xmlHttp = null, xmlVars = null;*}
    {literal}
    var orgsid = 0;
    var editMode = false;
	var nowSel = false;
    var curIp = 0;
    var deldata = '';
    var delform = null;
    var chgIp = false;

	function delAdmin11() {
		var obj = null, nodes = null, attr = null;
		var cnt = 0;
		obj = document.getElementById("tabAction");
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "checkbox") && nodes[i].checked && (nodes[i].name != 'ck')) {
				attr = nodes[i].getAttribute("explode");
				if (attr != null) continue;
				cnt++;
			}
		}
		if (cnt == 0) {
			alert(MSG_SELECT_ADMIN);
			return false;
		}
		return true;
	}

    // 關閉 fancybox
    function close_fancy() {
        $.fancybox.close();
    }

    // 新增管理員
    function addAdmin(pm) {
        $("#opnPermit").val(pm);
        if (pm == 3) {
            curIp = 0;
        } else {
            curIp = $("#chgSch").val();
        }
        
        $("#addDivBtn").trigger("click");
    }
    
    function chkAdmin(obj) {
        var delAdHtml = '';
        deldata = '';
        delform = null;
        delform = $(obj).parent().parent().find("form");
        deldata = $(delform[0]).serialize();
        if('' == deldata) {
            if(window.console) {
                console.log("no data!");
            }
            alert(MSG_SELECT_ADMIN);
            return;
        }
        
        //顯示到刪除提示
        $.each(delform.find('input:checked'),function(key, value) {
            delAdHtml += ($(value).val()).split(",")[0] + '<br>';
        });
        $("#delDiv .group-box-content").html(delAdHtml);
        
        $("#delDivBtn").trigger("click");
    }
    function delAdmin() {
        close_fancy();
        $.ajax(
            'sysop_del1.php',
            {
                'type': 'POST',
                'processData': false,
                'data': deldata + "&rtnjson=1",
                'dataType': 'json',
                'success': function (data) {
                    var delinfo = '';
                    // 移除成功刪除的資料
                    $.each(data, function(key, val) {
                        delinfo += val["username"] + ":" + delMsg[val["delstatus"]] + "\n";
                        var input = val["username"] + ',' + val["school_id"];
                        var delinput = delform.find('input[value="'+input+'"]');
                        delinput.parent().parent().remove();
                    });
                    alert(delinfo);
                    
                }
            }
        );
    }

	/**
	 * 儲存資料
	 **/
	function saveAdmin() {
        var nodes = null;
		var obj = document.getElementById("fmProp");
        var ipObj = document.getElementById("opnIP-"+curIp);
		var uname, sid, uPermit, uIP;
		var txt = "", $xml;
        
		uname = obj.opnName.value;
		if (uname == "") {
			alert(MSG_INPUT_USERNAME);
			return false;
		}
        
		uIP = ipObj.value;
		if (uIP == "") {
			alert(MSG_INPUT_IP);
            close_fancy();
            ipObj.focus();
			return false;
		}
        var obj2 = document.getElementById("chgSch");
		if (typeof(obj2) == "object") {
			sid = obj2.value;
		} else {
			sid = 0;
		}
		if (typeof(obj.opnPermit.length) == "number") {
			for (var i = 0; i < obj.opnPermit.length; i++) {
				if (obj.opnPermit[i].checked) uPermit = obj.opnPermit[i].value;
			}
		} else {
			uPermit = obj.opnPermit.value;
		}

		txt  = "<manifest>";
		txt += "<ticket></ticket>";
		txt += "<mode>" + (editMode ? 'edit' : 'add') + "</mode>";
		txt += "<username>" + uname + "</username>";
		txt += "<sid>" + sid + "</sid>";
		txt += "<osid>" + orgsid + "</osid>";
		txt += "<permit>" + uPermit + "</permit>";
		txt += "<ip>" + uIP + "</ip>";
		txt += "</manifest>";

        $xml = $.parseXML(txt);
        $.ajax(
            'sysop_save.php',
            {
                'type': 'POST',
                'processData': false,
                'data': $xml,
                'dataType': 'html',
                'success': function (data) {
                    try {
                        var res = $.parseXML(data);
                        var nodes = $(res).find("sysop");
                        if ((nodes != null) && (nodes.length > 0)) {
                            txt = editMode ? MSG_UPDATE_SUCCESS : MSG_ADD_SUCCESS;
                            alert(txt);
                            close_fancy();
                            // location.reload();
                            if (false == editMode) {
                                // 把資料建入目前表單裡
                                var rUname = nodes.find('uname').text();
                                var rName = nodes.find('name').text();
                                var rSid = nodes.find('sid').text();
                                var rSname = nodes.find('sname').text();
                                var rPermit = nodes.find('permit').text();
                                var rCtname = nodes.find('ctname').text();
                                var rCttime = nodes.find('cttime').text();
                                var divId = 'd' + ((rPermit == 3)? '0' :rSid) + '-' + rPermit;
                                $("#"+ divId + " .content .admin-list table tbody")
                                        .append('<tr><td style="text-align: center;">'+
                                        '<input type="checkbox" name="ckUname[]" value="'+rUname+','+rSid+'">'+
                                        '</td><td>'+rUname+'('+rName+')</td><td>'+rCtname+'</td><td>'+rCttime+'</td></tr>');
                            }
                            obj.reset();
                            return true;
                        }
                    }
                    catch(err) {
                        alert(data);
                        return;
                    }
                }
            }
        );
    }
	window.onload = function () {
		var obj = null;
		// document.getElementById("tb2").innerHTML = document.getElementById("tb1").innerHTML.replace(/ id="btn\w+"/g, '');

		// if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		// if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();
		// if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
        
        // 選擇分校
        $("#chgSch").on('change', function() {
            $(".schSetDiv").hide();
            $("#sch"+$(this).val()).show();
        });
        
        
        $('#addDivBtn').fancybox({ 
            'titlePosition': 'inline',
            'transitionIn': 'none',
            'transitionOut': 'none',
            'padding': 0,
			'margin': 0,
            'modal': true,
            'closeBtn': false,
            helpers : {
                overlay : {
                    locked : false
                }
            }/*,
            afterClose : function() {
                $("#videoFrame").attr('src', '');
            }*/
        });

        $('#delDivBtn').fancybox({ 
            'titlePosition': 'inline',
            'transitionIn': 'none',
            'transitionOut': 'none',
            'padding': 0,
			'margin': 0,
            'modal': true,
            'closeBtn': false,
            helpers : {
                overlay : {
                    locked : false
                }
            }/*,
            afterClose : function() {
                $("#videoFrame").attr('src', '');
            }*/
        });
        
        // IP 自動儲存
        $('textarea[name^="opnIP["]').on("change", function() {
            chgIp = true;
        });
       
        $('textarea[name^="opnIP["]').on("blur", function() {
            var ipData, postData;
            
            if (false == chgIp) {
                return;
            }
            ipData = $(this).serializeArray();
            var key = ipData[0]["name"];
            var val = ipData[0]["value"];
            postData = {sch: key, opnIP:val};
             $.ajax(
            'sysop_save.php',
            {
                'type': 'POST',
                'data': postData,
                'dataType': 'html',
                'success': function (data) {
                    if(window.console) {
                        console.log(data);
                    }
                    chgIp = false;
                }
            });
        });
        
        // 預設為教師
        $('input[name^="setTea["]').on("change", function() {
            var setTeaData, postData;
            var obj = $(this);

            var key = obj.attr("name");
            var val = obj.attr("value");
            if(!(obj.attr('checked'))){
               val = "N";
            }
            postData = {sch: key, setTea:val};

             $.ajax(
            'sysop_save.php',
            {
                'type': 'POST',
                'data': postData,
                'dataType': 'html',
                'success': function (data) {
                    if(window.console) {
                        console.log(data);
                    }
                }
            });
        });
        // 新增、刪除功能
        {/literal}
                {*
        {if $curLevel gte $sysRoles.root}
            $("#d0-3 .addBtn").on("click", function() {ldelim}
                $("#opnPermit").val(3);
                $("#addDivBtn").trigger("click");
            {rdelim});
        {/if}
        {foreach from = $schoolData key = sk item = sv}
            {if $curLevel gte $sysRoles.administrator}
                $("#d{$sk}-2 .addBtn").on("click", function() {ldelim}
                    $("#opnPermit").val(2);
                    $("#addDivBtn").trigger("click");
                {rdelim});
            {/if}
            {if $curLevel gte $sysRoles.manager}
                $("#d{$sk}-1 .addBtn").on("click", function() {ldelim}
                    $("#opnPermit").val(1);
                    $("#addDivBtn").trigger("click");
                {rdelim});
            {/if}
            $("#d{$sk}-0 .addBtn").on("click", function() {ldelim}
                $("#opnPermit").val(0);
                $("#addDivBtn").trigger("click");
            {rdelim});
        {/foreach}
        *}
        {literal}
	}  
    {/literal}
</script>
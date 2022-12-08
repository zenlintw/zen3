<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/lib/jquery/css/jquery-ui-1.8.22.custom.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/settings.css" rel="stylesheet" />
<style type="text/css">
{literal}
.data6 > .content .data{
	width: 100%
}
{/literal}
</style>
<script type="text/javascript">
{$inlineJS}
{literal}
	// 顯示tabs
	var nowTabId = 1;
	function show_page(tab)
	{
		if (nowTabId == tab) return;
		$("#tab"+tab).addClass('active');
		$("#tab"+nowTabId).removeClass('active');
		$(".group_info").hide();
		$("#group_info"+tab).show();
		nowTabId = tab;
	}
	
	// 顯示更多語言
	function showOtherLangTitle(btn) {
		if ($('.otherLangTitle').is(':visible')) {
			$('.otherLangTitle').hide();
			btn.innerText = btn_more_lang;
		}else{
			$('.otherLangTitle').show();
			btn.innerText = btn_less_lang;
		}
	}
	
	function showForumOtherLangTitle(btn) {
		if ($('.otherForumLangTitle').is(':visible')) {
			$('.otherForumLangTitle').hide();
			btn.innerText = btn_more_lang;
		}else{
			$('.otherForumLangTitle').show();
			btn.innerText = btn_less_lang;
		}
	}
	
	function showOtherChatLangTitle(btn) {
		if ($('.otherChatLangTitle').is(':visible')) {
			$('.otherChatLangTitle').hide();
			btn.innerText = btn_more_lang;
		}else{
			$('.otherChatLangTitle').show();
			btn.innerText = btn_less_lang;
		}
	}
{/literal}
</script>

<div class="box1">
    <div class="content" style="padding: 0px;">
        <div class="data6">
            <div class="title">
                <ul id="pager-switch" class="nav nav-tabs nav-orange">
                    <li id="tab1" class="active"><a href="#" onclick="show_page(1);return false;">{'manage1'|WM_Lang}</a></li>
                    <li id="tab2"><a href="#" onclick="show_page(2);return false;">{'manage2'|WM_Lang}</a></li>
                    <li id="tab3"><a href="#" onclick="show_page(3);return false;">{'manage3'|WM_Lang}</a></li>
                </ul>
            </div>
        </div>
		<div id="group_info1" class="content group_info" style="padding:0px;display:block;">
			<form id="addFm1" name="addFm1" class="form-horizontal" method="post" action="group_manage_set1.php?1">
			<input type="hidden" name="team_id" value="{$tid}">
			<input type="hidden" name="group_id" value="{$gid}">
			<input type="hidden" name="ticket" value="{$ticket}">
			<div class="data6">
				<div id="dataDiv" class="content" style="">
					<!-- 撐開距離用 -->
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child" style="padding: 0.8em 2em;"></div>
								<div class="value layout-child" style="padding: 0.8em 2em;"></div>
							</div>
						</div>
					</div>
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child">{'group_name'|WM_Lang}</div>
								<div class="value layout-child">
								{foreach from=$lang key=k_lang item=v_lang}
									{if $k_lang == $nowlang}
										 {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="team_name_{$k_lang}" name="team_name_{$k_lang}" class="input-large" style="width: 300px;" value="{$ctions.$k_lang}">&nbsp;&nbsp;<button type="button" class="btn" onclick="showOtherLangTitle(this);">{'more_lang'|WM_Lang}</button>
									{else}
										<div class="otherLangTitle" style="display:none;line-height:26px;">
										{if $k_lang eq 'en'}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{/if}
									  {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="team_name_{$k_lang}" name="team_name_{$k_lang}" class="input-large" style="width: 380px;" value="{if $ctions.$k_lang eq ''}undefined{else}{$ctions.$k_lang}{/if}"></div>
									{/if} 
								{/foreach}
								</div>
							</div>
						</div>
						<div class="comment layout-child"></div>
					</div>
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child">{'set_chief'|WM_Lang}</div>
								<div class="value layout-child">{$radioChief}</div>
							</div>
						</div>
					</div>
					<div class="divider-horizontal"></div>
				</div>
				<div class="operate" style="">
					<button id="frmSubmit" class="btn btn-blue" onclick="document.getElementById('post1').submit();">{'complete'|WM_Lang}</button>
					<button id="btnBack" class="btn btn-gray" onclick="goBack({$tid});">{'cancel'|WM_Lang}</button>
				</div>
			</div>
			</form>
		</div>
		<div id="group_info2" class="content group_info" style="padding:0px;display:none;">
			<form id="addFm2" name="addFm2" class="form-horizontal" method="post" action="group_manage_set1.php?2" onSubmit="return chkBoard();">
			<input type="hidden" name="team_id" value="{$tid}">
			<input type="hidden" name="group_id" value="{$gid}">
			<input type="hidden" name="board_id" value="{$bid}">
			<input type="hidden" name="ticket" value="{$ticket}">
			<div class="data6">
				<div id="dataDiv" class="content" style="">
					<!-- 撐開距離用 -->
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child" style="padding: 0.8em 2em;"></div>
								<div class="value layout-child" style="padding: 0.8em 2em;"></div>
							</div>
						</div>
					</div>
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child">{'forum_name'|WM_Lang}</div>
								<div class="value layout-child">
								{foreach from=$lang key=k_lang item=v_lang}
									{if $k_lang == $nowlang}
										 {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="team_forum_{$k_lang}" name="team_forum_{$k_lang}" class="input-large" style="width: 300px;" value="{$bnames.$k_lang}">&nbsp;&nbsp;<button type="button" class="btn" onclick="showForumOtherLangTitle(this);">{'more_lang'|WM_Lang}</button>
									{else}
										<div class="otherForumLangTitle" style="display:none;line-height:26px;">
										{if $k_lang eq 'en'}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{/if}
									  {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="team_forum_{$k_lang}" name="team_forum_{$k_lang}" class="input-large" style="width: 380px;" value="{if $bnames.$k_lang eq ''}undefined{else}{$bnames.$k_lang}{/if}"></div>
									{/if} 
								{/foreach}
								</div>
							</div>
						</div>
						<div class="comment layout-child"></div>
					</div>
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child">{'title_help'|WM_Lang}</div>
								<div class="value layout-child">
								<textarea name="help" rows="6" style="width:600px;">{$helpTitles}</textarea>
						    	<div id='TxtLen'>{'current_length'|WM_Lang}</div> </font>
								</div>
							</div>
						</div>
					</div>
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child">{'title_mailfollow'|WM_Lang}</div>
								<div class="value layout-child">
								<input type="radio" name="mailfollow" value="yes" onclick="chgWithAttach(this.value)"{if $mailfollows eq 'yes'} checked{/if}>{'title_yes'|WM_Lang}</input>
								<input type="radio" name="mailfollow" value="no" onclick="chgWithAttach(this.value)"{if $mailfollows eq 'no'} checked{/if}>{'title_no'|WM_Lang}</input><BR />
								<input type="checkbox" id="withattach" name="withattach" onclick="chgWithAttach(this.checked)" value="yes" {if $with_attach eq 'yes'} checked{/if}>{'with_attach'|WM_Lang}</input>
								</div>
							</div>
						</div>
					</div>
					<div class="divider-horizontal"></div>
				</div>
				<div class="operate" style="">
					<button id="frmSubmit" class="btn btn-blue" onclick="document.getElementById('post1').submit();">{'complete'|WM_Lang}</button>
					<button id="btnBack" class="btn btn-gray" onclick="goBack({$tid});">{'cancel'|WM_Lang}</button>
				</div>
			</div>
			</form>
		</div>
		<div id="group_info3" class="content group_info" style="padding:0px;display:none;">
			<form id="addFm3" name="addFm3" class="form-horizontal" method="post" action="group_manage_set1.php?3">
			<input type="hidden" name="team_id" value="{$tid}">
			<input type="hidden" name="group_id" value="{$gid}">
			<input type="hidden" name="chat_id" value="{$rid}">
			<input type="hidden" name="ticket" value="{$ticket}">
			<div class="data6">
				<div id="dataDiv" class="content" style="">
					<!-- 撐開距離用 -->
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child" style="padding: 0.8em 2em;"></div>
								<div class="value layout-child" style="padding: 0.8em 2em;"></div>
							</div>
						</div>
					</div>
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child">{'chat_room_name'|WM_Lang}</div>
								<div class="value layout-child">
								{foreach from=$lang key=k_lang item=v_lang}
									{if $k_lang == $nowlang}
										 {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="team_rname_{$k_lang}" name="team_rname_{$k_lang}" class="input-large" style="width: 300px;" value="{$rnames.$k_lang}">&nbsp;&nbsp;<button type="button" class="btn" onclick="showOtherChatLangTitle(this);">{'more_lang'|WM_Lang}</button>
									{else}
										<div class="otherChatLangTitle" style="display:none;line-height:26px;">
										{if $k_lang eq 'en'}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{/if}
									  {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="team_rname_{$k_lang}" name="team_rname_{$k_lang}" class="input-large" style="width: 380px;" value="{if $rnames.$k_lang eq ''}undefined{else}{$rnames.$k_lang}{/if}"></div>
									{/if} 
								{/foreach}
								</div>
							</div>
						</div>
					</div>
					<div class="layout-hr">
						<div class="data layout-child">
							<div class="layout-hr">
								<div class="key layout-child">&nbsp;</div>
								<div class="value layout-child">
								{'host_msg_exit'|WM_Lang}{$selChatActions}<BR />
								<input type="checkbox" name="host_change" value="1"{if $jump == 'allow'} checked="checked"{/if}>{'host_msg_allow_chg'|WM_Lang}
								</div>
							</div>
						</div>
					</div>
					<div class="divider-horizontal"></div>
				</div>
				<div class="operate" style="">
					<button id="frmSubmit" class="btn btn-blue" onclick="document.getElementById('post1').submit();">{'complete'|WM_Lang}</button>
					<button id="btnBack" class="btn btn-gray" onclick="goBack({$tid});">{'cancel'|WM_Lang}</button>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>
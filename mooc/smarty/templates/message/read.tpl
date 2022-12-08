<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/settings.css" rel="stylesheet" />
<script type="text/javascript" src="hotkey.js"></script>
<script type="text/javascript" src="read.js"></script>

<div class="box1">
	<div class="title">
        <div class="bread-crumb">
			<span class="home"><a href="javascript:;" onclick="goList();return false;">{$folderPathNow}</a></span>
			<span>&gt;</span>
			<span class="path2 now">{'tabs2_title'|WM_Lang}</span>
        </div>
    </div>
	<div class="operate" style="padding-top:0.5em;">
		<button id="btnFirst" class="btn" onclick="go_page(-1);"{if $first_serial eq $msg_serial} disabled="disabled"{/if}style="width:6em;">{'msg_first'|WM_Lang}</button>
		<button id="btnPrev" class="btn" onclick="go_page(-2);"{if $first_serial eq $msg_serial} disabled="disabled"{/if} style="width:6em;">{'msg_previous'|WM_Lang}</button>
		<button id="btnNext" class="btn" onclick="go_page(-3);"{if $last_serial eq $msg_serial} disabled="disabled"{/if} style="width:6em;">{'msg_next'|WM_Lang}</button>
		<button id="btnLast" class="btn" onclick="go_page(-4);"{if $last_serial eq $msg_serial} disabled="disabled"{/if} style="width:6em;">{'msg_last'|WM_Lang}</button>
		<button id="btnSend" class="btn" onclick="post();" style="width:8em;">{'func_send'|WM_Lang}</button>
		<button id="btnList" class="btn" onclick="reply();" style="width:5em;">{'func_reply'|WM_Lang}</button>
		<button id="btnList" class="btn" onclick="fw();" style="width:5em;">{'func_forward'|WM_Lang}</button>
		<button id="btnList" class="btn" onclick="del();" style="width:5em;">{'func_delete_s'|WM_Lang}</button>
		<button id="btnList" class="btn" onclick="mv();" style="width:5em;">{'func_move_s'|WM_Lang}</button>
    </div>
    <div class="content" style="padding:0px;">
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
							<div class="key layout-child">{'priority_order'|WM_Lang}</div>
							<div class="value layout-child">{$MsgPriority}</div>
						</div>
					</div>
				</div>
				<div class="layout-hr">
					<div class="data layout-child">
						<div class="layout-hr">
							<div class="key layout-child">{'sender'|WM_Lang}</div>
							<div class="value layout-child">{$MsgFrom}</div>
						</div>
					</div>
				</div>
				<div class="layout-hr">
					<div class="data layout-child">
						<div class="layout-hr">
							<div class="key layout-child">{'date'|WM_Lang}</div>
							<div class="value layout-child">{$MsgData.submit_time}</div>
						</div>
					</div>
				</div>
				<div class="layout-hr">
					<div class="data layout-child">
						<div class="layout-hr">
							<div class="key layout-child">{'to'|WM_Lang}</div>
							<div class="value layout-child">{$MsgTo}</div>
						</div>
					</div>
				</div>
				<div class="layout-hr">
					<div class="data layout-child">
						<div class="layout-hr">
							<div class="key layout-child">{'subject'|WM_Lang}</div>
							<div class="value layout-child" style="word-break: break-all;">{$MsgData.subject}</div>
						</div>
					</div>
				</div>
				<div class="layout-hr">
					<div class="data layout-child">
						<div class="layout-hr">
							<div class="key layout-child">{'content'|WM_Lang}</div>
							<div class="value layout-child">
							<table width="640"><tr><td id="o_content" style="word-break: break-all;">{$MsgContent}&nbsp;</td></tr></table>
							</div>
						</div>
					</div>
				</div>
				<div class="layout-hr">
					<div class="data layout-child">
						<div class="layout-hr">
							<div class="key layout-child" for="last_name">{'attachment'|WM_Lang}</div>
							<div class="value layout-child">{$MsgAttachment}</div>
						</div>
					</div>
				</div>
			<div class="divider-horizontal"></div>
			<!-- 撐開距離用 -->
			</div>
		</div>
    </div>
</div>
<form id="actFm" name="actFm" action="read.php" method="post" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="act" value="">
<input type="hidden" name="serial" value="{$msg_serial}">
</form>
<form id="mainFm" name="mainFm" action="about:blank" method="post" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="folder_id" value="">
<input type="hidden" name="fid[]" value="{$msg_serial}">
</form>

<script type="text/javascript">
	{$inlineJS}
    {literal}
    {/literal}
</script>
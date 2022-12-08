var
	xmlDoc,
	isEdited = false,
	langLst = ['Big5', 'GB2312', 'en', 'EUC-JP', 'user_define'],
	fileTarget,
	notSave = false,
    appPictureBrowser;

// ====================================
function getTarget() {
	var obj = null;
	switch (this.name) {
		case 's_main': obj = parent.s_catalog; break;
		case 'c_main': obj = parent.c_catalog; break;
		case 'main'  : obj = parent.catalog;   break;
		case 's_catalog': obj = parent.s_main; break;
		case 'c_catalog': obj = parent.c_main; break;
		case 'catalog'  : obj = parent.main;   break;
	}
	return obj;
}

function setFilename (filename) {
    if (typeof filename !== 'undefined') {
        if (fileTarget === null) {
            $('#propPanel input:text[name="cover"]')
                .val(filename.substr(1))
                .trigger('change');
        } else {
            // 把http://...等網址資訊一併寫入URL中；PREFIX_URL定義在experience_list.php裡
            $(fileTarget).parent().find('input:text[name="link\[\]"]').val(PREFIX_URL+filename.substr(1));
        }
    }
}

function browseCatalogFile() {
	fileTarget = null;
    appPictureBrowser = window.open('experience_file_catalog.php', 'appPictureBrowser', 'width=380,height=400,status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1');
    if (appPictureBrowser.closed === false) {
        // 已經開啟，則focus就好
        appPictureBrowser.focus();
    }
}
function browseUrlFile(target) {
	fileTarget = target;
    appPictureBrowser = window.open('experience_file_url.php', 'appPictureBrowser', 'width=380,height=400,status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1');
    if (appPictureBrowser.closed === false) {
        // 已經開啟，則focus就好
        appPictureBrowser.focus();
    }
}
// ====================================
function parseItem() {
	var
		str = '', idx = 0, col = 'cssTrEvn',
		$item = $(xmlDoc).find('item');

	str  = '<ol style="margin: 0; padding: 0;">';
	$item.each(function () {
		var
			$self = $(this), $lang, disable, nid;

		idx += 1;
		nid = $self.attr('id');
		col = (col === 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
		$lang = $self.find('> title ' + lang);
		if ($lang.length <= 0) {
			$lang = $self.find('> title ' + langLst[0]);
		}
		disable = ($self.attr('enable') != 0) ? '' : ' text-decoration: line-through;';
		str += '<li style="list-style: none; margin: 0;' + disable + '"  class="' + col + '">';
		str += '<input type="checkbox" value="' + nid + '" />&nbsp;';
		str += idx + '.&nbsp;';
		str += '<a href="" onclick="edit(\'' + nid + '\'); return false;" class="cssAnchor">' + $lang.text() + '</a>';
		str += '</li>';
	});
	str +='</ol>';

	$('#mainPanel').html(str);
}

// ====================================
function getSelect() {
	return $('#mainPanel input:checkbox:checked');
}

function getUrlSelect() {
	return $('#catalogUrls tr.url input[name="ck\[\]"]:checkbox:checked');
}

function reChecke($sel) {
	$sel.each(function () {
		$('#mainPanel input:checkbox[value="' + $(this).val() + '"]').prop('checked', true);
	});
}

function reUrlChecke($sel) {
	$sel.each(function () {
		$('#mainPanel input:checkbox[value="' + $(this).val() + '"]').prop('checked', true);
	});
}

function chkUrlCheck() {
	var len1 = 0, len2 = 0;
	len1 = $('#catalogUrls tr.url input[name="ck\[\]"]:checkbox:checked').length;
	len2 = $('#catalogUrls tr.url input[name="ck\[\]"]:checkbox').length;
	$('#catalogUrls input[name="ckall"]:checkbox').prop('checked', len1 === len2);
}

function selectUrlCheck() {
	var bol = $('#catalogUrls input[name="ckall"]:checkbox').prop('checked');
	$('#catalogUrls tr.url input[name="ck\[\]"]:checkbox').prop('checked', bol);
	
	notSave = true;
}

function resetUrlsCol() {
	$('#catalogUrls > tbody > tr:gt(1)').removeClass('cssTrEvn').removeClass('cssTrOdd');
	$('#catalogUrls > tbody > tr:gt(1):even').addClass('cssTrEvn');
	$('#catalogUrls > tbody > tr:gt(1):odd').addClass('cssTrOdd');
	
	notSave = true;
}

function addUrl(data) {
	var $node = $('<tr class="url"></tr>')
		.append(
			// action checkbox
			$('<td align="center"></td>')
				.append(
					$('<input type="checkbox" name="ck[]" onclick="chkUrlCheck();" />').val($(data).attr('id'))
				)
		)
		.append(
			// enable checkbox
			$('<td align="center"></td>')
				.append(
					$('<input type="checkbox" name="useit[]" />')
						.val($(data).attr('id'))
						.prop('checked', $(data).attr('enable') !== '0')
				)
		)
		.append(
			// language
			$('<td class="lang"></td>')
				.append($('#langTemp').html().replace(/tb_multi_lang_1/ig, 'tb_' + $.now()))
				.find('input:text').val('undefined').end()
		)
		.append(
			// url
			$('<td nowrap></td>')
				.append(
					$('<input type="text" name="link[]" style="width: 300px;" class="cssInput" />')
						.val($(data).find('link').text())
				)
				.append(
					$('&nbsp;<input type="button" class="cssBtn" onclick="browseUrlFile(this);" />').val(MSG_BTN_BROWSE)
				)
		)
		.insertBefore('#catalogUrlsFooter');

	if (typeof data === 'undefined') {
		$node.find('input[name="ck\[\]"]').val('NEW_' + $.now());
		resetUrlsCol();
	} else {
		// language
		$(data).find('> title').children().each(function () {
			$node.find('input:text[name="' + this.tagName + '"]').val($(this).text());
		});
	}
	
	notSave = true;
}

function delUrl() {
	var $cks = getUrlSelect();
	if ($cks.length <= 0) {
		alert(MSG_MUST_SELECT);
		return;
	}
	$cks.each(function () {
		$(this).parents('tr:first').remove();
	});
	resetUrlsCol();
	
	notSave = true;
}

function cleanUrl() {
	$('#catalogUrls tr.url').remove();
	
	notSave = true;
}

function moveUrlUp() {
	var $cks = getUrlSelect();

	if ($cks.length <= 0) {
		alert(MSG_MUST_SELECT);
		return;
	}

	$cks.each(function () {
		var $node, $prev, $tmp;

		$node = $(this).parents('tr:first');
		$prev = $node.prev('.url');
		if ($prev.length <= 0) {
			alert(MSG_ON_TOP);
			return false;
		}
		$node.insertBefore($prev);
	});

	resetUrlsCol();
}

function moveUrlDown() {
	var $cks = getUrlSelect(), ary = [], $node, $next;

	if ($cks.length <= 0) {
		alert(MSG_MUST_SELECT);
		return;
	}

	$cks.each(function () {
		ary.push(this);
	});

	while (ary.length > 0) {
		$node = $(ary.pop()).parents('tr:first');
		$next = $node.next('.url');
		if ($next.length <= 0) {
			alert(MSG_ON_BOTTOM);
			return false;
		}
		$node.insertAfter($next);
	}
	resetUrlsCol();
	
	notSave = true;
}

function propHidden() {
	if ($('#propLang input:text:visible').length > 1) {
		$('#propLang .cssBtn:visible').click();
	}
	layerAction('propPanel', false);
}

function newItem() {
	var $node, title;

	title = xmlDoc.createElement('title');
	$(langLst).each(function () {
		$(xmlDoc.createElement(this)).text('undefined').appendTo(title);
	});

	$node = $(xmlDoc.createElement('item'))
		.append(title)
		.append(xmlDoc.createElement('description'))
		.append(xmlDoc.createElement('cover'))
		.append($(xmlDoc.createElement('begin_date')).text('0000-00-00 00:00:00'))
		.append($(xmlDoc.createElement('end_date')).text('9999-12-31 23:59:59'))
		.append(xmlDoc.createElement('urls'));

	$node.attr({
		'id'    : '',
		'enable': 1
	});
	
	notSave = true;

	return $node;
}

function propSave() {
	var $cks = getSelect(), nid, pid, $node, $urls, $pnode;

	nid = $('#propPanel input:hidden[name="nid"]').val();
	if (nid === '') {
		pid = $('#propPanel input:hidden[name="posid"]').val();
		nid = 'NEW_' + $.now();
		$('#propPanel input:hidden[name="nid"]').val(nid);
		$node = newItem();
		$node.attr('id', nid);
		$pnode = $(xmlDoc).find('item[id="' + pid + '"]');
		if ($pnode.length <= 0) {
			$(xmlDoc).find('manifest').append($node);
		} else {
			$node.insertBefore($pnode);
		}
	} else {
		$node = $(xmlDoc).find('item[id="' + nid + '"]');
	}

	// language
	$('#propLang input:text').each(function () {
		var $self = $(this);
		$node.find('> title ' + $self.attr('name')).text($self.val());
	});
	// cover
	$node.find('cover').text($('#propPanel input:text[name="cover"]').val());
	// enable
	$node.attr('enable', $('#propPanel input:checkbox[name="enable"]:checked').length);
	// description
	$node.find('description').text($('#propPanel textarea[name="desc"]').val());
	// urls
	$urls = $node.find('urls');
	$urls.children().remove(); // clean url
	$('#propPanel tr.url').each(function () {
		var $self = $(this), url, title;

		url = xmlDoc.createElement('url');
		$(url).attr({
			'id'    : $self.find('input[name="ck\[\]"]').val(),
			'enable': $self.find('input[name="useit\[\]"]:checked').length
		});
		// language
		title = xmlDoc.createElement('title');
		$(langLst).each(function () {
			$(xmlDoc.createElement(this))
				.text($self.find('.lang input:text[name="' + this + '"]').val())
				.appendTo(title);
		});
		$(url).append(title)
			.append(
				$(xmlDoc.createElement('link')).text($self.find('input:text[name="link[]"]').val())
			)
			.append($(xmlDoc.createElement('begin_date')).text('0000-00-00 00:00:00'))
			.append($(xmlDoc.createElement('end_date')).text('9999-12-31 23:59:59'));
		// url
		$urls.append(url);
	});

	propHidden();
	parseItem();
	reChecke($cks);
	isEdited = true;
}

function insert(bol) {
	var $cks = getSelect();

	if (bol) {
		if (($cks.length <= 0) || ($cks.length > 1)) {
			alert(MSG_INSERT_SELECT);
			return;
		} else {
			// position id
			$('#propPanel input:hidden[name="posid"]').val($cks.first().val());
		}
	} else {
		// clean posid
		$('#propPanel input:hidden[name="posid"]').val('');
	}

	// clean id
	$('#propPanel input:hidden[name="nid"]').val('');

	// language
	$('#propLang input:text').val('undefined');

	// cover
	$('#propPanel input:text[name="cover"]').val('');
	// enable
	$('#propPanel input:checkbox[name="enable"]').prop('checked', true);
	// description
	$('#propPanel textarea[name="desc"]').val('');
	// image
	$('#propPanel .image').css('background-image', '');
	// urls
	cleanUrl();
	resetUrlsCol();
	layerAction('propPanel', true);
	
	notSave = true;
}

function edit(nid) {
	var $cks, $node;

	if (typeof nid === 'undefined') {
		$cks = getSelect();
		if ($cks.length <= 0) {
			alert(MSG_MUST_SELECT);
			return;
		}
		nid = $cks.val();
	}

	$node = $(xmlDoc).find('item[id="' + nid + '"]');
	if ($node.length <= 0) {
		return;
	}
	// language
	$node.find('> title').children().each(function () {
		$('#propLang input:text[name="' + this.tagName + '"]').val($(this).text());
	});
	// id
	$('#propPanel input:hidden[name="nid"]').val(nid);
	// cover
	$('#propPanel input:text[name="cover"]').val($node.find('cover').text()).trigger('change');
	// enable
	$('#propPanel input:checkbox[name="enable"]').prop('checked', $node.attr('enable') !== '0');
	// description
	$('#propPanel textarea[name="desc"]').val($node.find('description').text());
	// urls
	cleanUrl();
	$node.find('urls url').each(function () {
		addUrl(this);
	});
	resetUrlsCol();
	layerAction('propPanel', true);
	
	notSave = true;
}

function remove() {
	var $cks = getSelect(), ary = [], item;

	if ($cks.length <= 0) {
		alert(MSG_MUST_SELECT);
		return;
	}

	$cks.each(function () {
		ary.push(this);
	});

	while (ary.length > 0) {
		item = ary.pop();
		$(xmlDoc).find('item[id="' + $(item).val() + '"]').remove();
	}

	parseItem();
	isEdited = true;
	
	notSave = true;
}

function visibility() {
	var $cks = getSelect();

	if ($cks.length <= 0) {
		alert(MSG_MUST_SELECT);
		return;
	}

	$cks.each(function () {
		var $node;

		$node = $(xmlDoc).find('item[id="' + $(this).val() + '"]');
		// 切換啟用狀態 (enable status switch)
		$node.attr('enable', ($node.attr('enable') !== '0') ? 0 : 1);
	});

	parseItem();
	reChecke($cks);
	isEdited = true;
}

function moveUp() {
	var $cks = getSelect();

	if ($cks.length <= 0) {
		alert(MSG_MUST_SELECT);
		return;
	}

	$cks.each(function () {
		var $node, $prev, $tmp;

		$node = $(xmlDoc).find('item[id="' + $(this).val() + '"]');
		$prev = $node.prev();
		if ($prev.length <= 0) {
			alert(MSG_ON_TOP);
			return false;
		}
		$node.insertBefore($prev);
	});

	parseItem();
	reChecke($cks);
	isEdited = true;
	
	notSave = true;
}

function moveDown() {
	var $cks = getSelect(), ary = [], $node, $next;

	if ($cks.length <= 0) {
		alert(MSG_MUST_SELECT);
		return;
	}

	$cks.each(function () {
		ary.push(this);
	});

	while (ary.length > 0) {
		$node = $(xmlDoc).find('item[id="' + $(ary.pop()).val() + '"]');
		$next = $node.next();
		if ($next.length <= 0) {
			alert(MSG_ON_BOTTOM);
			return false;
		}
		$node.insertAfter($next);
	}

	parseItem();
	reChecke($cks);
	isEdited = true;
	
	notSave = true;
}

function selectAll() {
	$('#mainPanel input:checkbox').prop('checked', true);
}

function cancelAll() {
	$('#mainPanel input:checkbox').prop('checked', false);
}

function save() {
	$.ajax({
		'type'              : 'POST',
		'url'               : '/academic/App_course/experience_save.php',
		'data'              : {
			'xml': encodeURI($(xmlDoc).xml())
		},
		'processDataBoolean': false,
		'success'           : function (data) {
			if (data === 'ok') {
				isEdited = false;
				loadCatalogData();
				alert(MSG_SAVE_SUCCESS);
			} else {
				alert(MSG_SAVE_FAIL);
			}
		},
		'error'             : function () {
			alert(MSG_SAVE_FAIL);
		}
	});
	
	notSave = false;
}

// ====================================
function loadCatalogData() {
	$.ajax({
		type: "POST",
		url: '/academic/App_course/experience_data.php',
		dataType: "xml",
		success: function (xml) {
			xmlDoc = xml;
			parseItem();
		}
	});
}

$(function () {
	// Serialize dom to string
	jQuery.fn.xml = function () {
		if (this.length <= 0) {
			return "";
		}
		var txt = "";
		this.each(function () {
			if (typeof window.XMLSerializer != "undefined") {
				try {
					txt = (new XMLSerializer()).serializeToString(this);
				} catch(ex) {
					if (typeof this.xml != "undefined") {
						txt = this.xml;
					} else if (typeof this.outerHTML != "undefined") {
						txt = this.outerHTML;
					}
				}
			} else {
				if (typeof this.xml != "undefined") {
					txt = this.xml;
				} else if (typeof this.outerHTML != "undefined") {
					txt = this.outerHTML;
				}
			}
			return false;
		});
		return txt;
	};

	var obj = getTarget();
	if ((typeof obj == 'object') && (obj !== null)) {
		obj.location.replace('/academic/App_course/experience_toolbar.php');
	}

	$('#propPanel input:text[name="cover"]').change(function () {
		var val = $(this).val();

		if (null === val.match(/^(https:\/\/|http:\/\/)/ig)) {
			val = 'experience_cover.php?name=' + val;
		}

		$('#propPanel .image').css('background-image', 'url("' + val + '")');
	});
	loadCatalogData();
});

window.onunload = function () {
	var obj = getTarget();

	if ((typeof obj == 'object') && (obj !== null)) {
		obj.location.href = 'about:blank';
	}
	top.FrameExpand(0, false, '');
};

window.onbeforeunload = function() {
	if (notSave) {
		return MSG_EXIT;
	}
};

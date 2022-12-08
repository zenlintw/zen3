// I18N constants -- Chinese GB
// by Dave Lo -- dlo@interactivetools.com
HTMLArea.I18N = {

	// the following should be the filename without .js extension
	// it will be used for automatically load plugin language.
	lang: "gb",

	tooltips: {
		bold:           "粗体",
		italic:         "斜体",
		underline:      "底线",
		strikethrough:  "删除线",
		subscript:      "下标",
		superscript:    "上标",
		justifyleft:    "位置靠左",
		justifycenter:  "位置居中",
		justifyright:   "位置靠右",
		justifyfull:    "位置左右平等",
		orderedlist:    "顺序清单",
		insertorderedlist:    "顺序清单",
		unorderedlist:  "无序清单",
		insertunorderedlist:  "无序清单",
		outdent:        "减小行前空白",
		indent:         "加宽行前空白",
		forecolor:      "文字颜色",
		hilitecolor:    "背景颜色",
		horizontalrule: "水平线",
		inserthorizontalrule: "插入水平线",
		createlink:     "插入连结",
		insertimage:    "插入图形",
		inserttable:    "插入表格",
		htmlmode:       "切换HTML原始码",
		popupeditor:    "新开视窗编辑",
		about:          "关于 HTMLArea",
		showhelp:       "幫助",
		textindicator:  "字体例子",
		undo:           "复原最后一个动作",
		redo:           "重做最后一个动作",
		cut:            "剪下",
		copy:           "复制",
		paste:          "贴上",
		lefttoright:    "Direction left to right",
		righttoleft:    "Direction right to left"
	},

	buttons: {
		"ok":           "确定",
		"cancel":       "取消"
	},

	msg: {
		"Path":         "路径",
		"TEXT_MODE":    "目前为 [文字] 模式编辑，若要切回 [所见即所得] 模式请按 [<>]",

		"IE-sucks-full-screen" :
		// translate here
		"The full screen mode is known to cause problems with Internet Explorer, " +
		"due to browser bugs that we weren't able to workaround.  You might experience garbage " +
		"display, lack of editor functions and/or random browser crashes.  If your system is Windows 9x " +
		"it's very likely that you'll get a 'General Protection Fault' and need to reboot.\n\n" +
		"You have been warned.  Please press OK if you still want to try the full screen editor."
	},

	dialogs: {
		"Cancel"                                            : "取消",
		"Insert/Modify Link"                                : "插入/修改连结",
		"New window (_blank)"                               : "新视窗 (_blank)",
		"None (use implicit)"                               : "无 (use implicit)",
		"OK"                                                : "确定",
		"Other"                                             : "其它",
		"Same frame (_self)"                                : "相同的页框 (_self)",
		"Target:"                                           : "目标:",
		"Title (tooltip):"                                  : "标题 (小提示):",
		"Top frame (_top)"                                  : "最上层的页框 (_top)",
		"URL:"                                              : "URL:",
		"You must enter the URL where this link points to"  : "You must enter the URL where this link points to",

		/* insert_table.html */
		"Insert Table"                                      : "插入表格",
		"Rows:"                                             : "列:",
		"Number of rows"                                    : "列数",
		"Cols:"                                             : "栏:",
		"Number of columns"                                 : "栏数",
		"Width:"                                            : "宽度:",
		"Width of the table"                                : "表格的宽度",
		"Percent"                                           : "%",
		"Pixels"                                            : "像素",
		"Layout"                                            : "版面设计",
		"You must enter a number of rows"                   : "必须输入列数",
		"You must enter a number of columns"                : "必须输入栏数",
		"Fixed width columns"                               : "固定栏位的宽度",
		"Positioning of this table"                         : "表格对齐的位置",
		"Alignment:"                                        : "对齐:",
		"Not set"                                           : "尚未设定",
		"Left"                                              : "左",
		"Right"                                             : "右",
		"Texttop"                                           : "文字-上方",
		"Absmiddle"                                         : "绝对置中",
		"Baseline"                                          : "基准线",
		"Absbottom"                                         : "绝对置下",
		"Bottom"                                            : "下方",
		"Middle"                                            : "中间",
		"Top"                                               : "上方",
		"Leave empty for no border"                         : "不想设定边框请保持空白",
		"Border thickness:"                                 : "边框宽度:",
		"Spacing"                                           : "间距与内距",
		"Cell spacing:"                                     : "间距:",
		"Cell padding:"                                     : "内距:",
		"Space between adjacent cells"                      : "储存格跟储存格之间的距离",
		"Space between content and border in cell"          : "内容至储存格边框之间的距离",

		/* about.html */
		"Thank you"                                         : "致谢",
		"About"                                             : "关于",
		"Thanks"                                            : "致谢",
		"License"                                           : "授权",
		"Plugins"                                           : "外挂程式",

		"Select Color"                                      : "选择颜色",
		""                                                  : ""
	},

	dialogs_image: {
		/* insert_image.html */
		"OK"                                                : "确定",
		"Cancel"                                            : "取消",
		"You must enter the URL"                            : "必须输入图形网址",
		"Image URL:"                                        : "图形网址:",
		"Enter the image URL here"                          : "输入图形网址",
		"Preview"                                           : "预览",
		"Preview the image in a new window"                 : "预览图形",
		"Alternate text:"                                   : "提示文字",
		"For browsers that don't support images"            : "浏览器不支援图形显示",
		"Border thickness:"                                 : "边框宽度:",
		"Leave empty for no border"                         : "不想设定边框请保持空白",
		"Layout"                                            : "版面设计",
		"Insert Image"                                      : "插入图形",
		"Alignment:"                                        : "对齐:",
		"Not set"                                           : "尚未设定",
		"Left"                                              : "左",
		"Right"                                             : "右",
		"Texttop"                                           : "文字-上方",
		"Absmiddle"                                         : "绝对置中",
		"Baseline"                                          : "基准线",
		"Absbottom"                                         : "绝对置下",
		"Bottom"                                            : "下方",
		"Middle"                                            : "中间",
		"Top"                                               : "上方",
		"Spacing"                                           : "间距",
		"Horizontal:"                                       : "水平:",
		"Horizontal padding"                                : "水平间距",
		"Vertical:"                                         : "垂直:",
		"Vertical padding"                                  : "垂直间距",
		"Image Preview:"                                    : "图形预览:",
		""                                                  : ""
	}
};

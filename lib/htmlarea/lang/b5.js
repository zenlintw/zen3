// I18N constants -- Chinese Big-5
// by Dave Lo -- dlo@interactivetools.com
HTMLArea.I18N = {

	// the following should be the filename without .js extension
	// it will be used for automatically load plugin language.
	lang: "b5",

	tooltips: {
		bold:           "粗體",
		italic:         "斜體",
		underline:      "底線",
		strikethrough:  "刪除線",
		subscript:      "下標",
		superscript:    "上標",
		justifyleft:    "位置靠左",
		justifycenter:  "位置居中",
		justifyright:   "位置靠右",
		justifyfull:    "位置左右平等",
		orderedlist:    "編號清單",
		insertorderedlist:    "編號清單",
		unorderedlist:  "項目清單",
		insertunorderedlist:  "項目清單",
		outdent:        "減少縮排",
		indent:         "增加縮排",
		forecolor:      "文字顏色",
		hilitecolor:    "背景顏色",
		horizontalrule: "水平線",
		inserthorizontalrule: "插入水平線",
		createlink:     "插入連結",
		insertimage:    "插入圖形",
		inserttable:    "插入表格",
		htmlmode:       "切換HTML原始碼",
		popupeditor:    "新開視窗編輯",
		about:          "關於 HTMLArea",
		showhelp:       "說明",
		textindicator:  "目前的樣式",
		undo:           "復原最後一個動作",
		redo:           "重做最後一個動作",
		cut:            "剪下",
		copy:           "複製",
		paste:          "貼上",
		lefttoright:    "Direction left to right",
		righttoleft:    "Direction right to left"
	},

	buttons: {
		"ok":           "確定",
		"cancel":       "取消"
	},

	msg: {
		"Path":         "路徑",
		"TEXT_MODE":    "目前為 [文字] 模式編輯，若要切回 [所見即所得] 模式請按 [<>]",

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
		"Insert/Modify Link"                                : "插入/修改連結",
		"New window (_blank)"                               : "新視窗 (_blank)",
		"None (use implicit)"                               : "無 (use implicit)",
		"OK"                                                : "確定",
		"Other"                                             : "其它",
		"Same frame (_self)"                                : "相同的頁框 (_self)",
		"Target:"                                           : "目標:",
		"Title (tooltip):"                                  : "標題 (小提示):",
		"Top frame (_top)"                                  : "最上層的頁框 (_top)",
		"URL:"                                              : "URL:",
		"You must enter the URL where this link points to"  : "You must enter the URL where this link points to",

		/* insert_table.html */
		"Insert Table"                                      : "插入表格",
		"Rows:"                                             : "列:",
		"Number of rows"                                    : "列數",
		"Cols:"                                             : "欄:",
		"Number of columns"                                 : "欄數",
		"Width:"                                            : "寬度:",
		"Width of the table"                                : "表格的寬度",
		"Percent"                                           : "%",
		"Pixels"                                            : "像素",
		"Layout"                                            : "版面設計",
		"You must enter a number of rows"                   : "必須輸入列數",
		"You must enter a number of columns"                : "必須輸入欄數",
		"Fixed width columns"                               : "固定欄位的寬度",
		"Positioning of this table"                         : "表格對齊的位置",
		"Alignment:"                                        : "對齊:",
		"Not set"                                           : "尚未設定",
		"Left"                                              : "左",
		"Right"                                             : "右",
		"Texttop"                                           : "文字-上方",
		"Absmiddle"                                         : "絕對置中",
		"Baseline"                                          : "基準線",
		"Absbottom"                                         : "絕對置下",
		"Bottom"                                            : "下方",
		"Middle"                                            : "中間",
		"Top"                                               : "上方",
		"Leave empty for no border"                         : "不想設定邊框請保持空白",
		"Border thickness:"                                 : "邊框寬度:",
		"Spacing"                                           : "間距與內距",
		"Cell spacing:"                                     : "間距:",
		"Cell padding:"                                     : "內距:",
		"Space between adjacent cells"                      : "儲存格跟儲存格之間的距離",
		"Space between content and border in cell"          : "內容至儲存格邊框之間的距離",

		/* about.html */
		"Thank you"                                         : "致謝",
		"About"                                             : "關於",
		"Thanks"                                            : "致謝",
		"License"                                           : "授權",
		"Plugins"                                           : "外掛程式",

		"Select Color"                                      : "選擇顏色",
		""                                                  : ""
	},

	dialogs_image: {
		/* insert_image.html */
		"OK"                                                : "確定",
		"Cancel"                                            : "取消",
		"You must enter the URL"                            : "必須輸入圖形網址",
		"Image URL:"                                        : "圖形網址:",
		"Enter the image URL here"                          : "輸入圖形網址",
		"Preview"                                           : "預覽",
		"Preview the image in a new window"                 : "預覽圖形",
		"Alternate text:"                                   : "提示文字",
		"For browsers that don't support images"            : "瀏覽器不支援圖形顯示",
		"Border thickness:"                                 : "邊框寬度:",
		"Leave empty for no border"                         : "不想設定邊框請保持空白",
		"Layout"                                            : "版面設計",
		"Insert Image"                                      : "插入圖形",
		"Alignment:"                                        : "對齊:",
		"Not set"                                           : "尚未設定",
		"Left"                                              : "左",
		"Right"                                             : "右",
		"Texttop"                                           : "文字-上方",
		"Absmiddle"                                         : "絕對置中",
		"Baseline"                                          : "基準線",
		"Absbottom"                                         : "絕對置下",
		"Bottom"                                            : "下方",
		"Middle"                                            : "中間",
		"Top"                                               : "上方",
		"Spacing"                                           : "間距",
		"Horizontal:"                                       : "水平:",
		"Horizontal padding"                                : "水平間距",
		"Vertical:"                                         : "垂直:",
		"Vertical padding"                                  : "垂直間距",
		"Image Preview:"                                    : "圖形預覽:",
		""                                                  : ""
	}
};

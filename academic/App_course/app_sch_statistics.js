/**
 * 學校統計資料 (APP 專用)
*/
	function do_fun(val){

		if ( (1 <= parseInt(val)) && (parseInt(val) <= 7) ) {
			window.onunload = function () {};
		}

		switch (parseInt(val)){
			case 1:	 	// 課程統計 (course)
				parent.main.location.href = "sch_statistics.php";
				break;
			case 2:		// 登入次數統計 (login)
				parent.main.location.href = "app_sch_login_statistics.php";
				break;
			case 3:		// 上課次數統計 (course)
				parent.main.location.href = "sch_course_statistics.php";
				break;
			case 4:		// 使用者人數統計 (user)
				parent.main.location.href = "sch_user_statistics.php";
				break;
			case 5:		// 教材閱讀統計 (course)
				parent.main.location.href = "cour_material_statistics.php";
				break;
			case 6:		// User log
				parent.main.location.href = "sch_userlog_statistics.php";
				break;
			case 7:		// 硬碟空間使用率 (Quota)
				parent.main.location.href = "sch_quota.php";
				break;
		}
	}

	/*
		 * Jpgraph
		 * post_action : 程式名稱
		 * x_scale : x 軸 的資料
		 * y_scale : y 軸 的資料
		 * max_value : y 軸 的 最大值
		 * period_date : 期間
		*/
	function viwGraph(post_action,x_scale,y_scale,max_value,period_date){

		window.open('about:blank', 'viewGraphWin', 'width=420, height=350, toolbar=0, menubar=0, scrollbars=1, resizable=1, status=0');

		var obj = document.getElementById('GraphFm');
		obj.action = post_action;
		obj.x_scale.value = x_scale;
		obj.y_scale.value = y_scale;
		obj.max_val.value = max_value;
		obj.period_date.value = period_date;
		obj.submit();

	}

	function init(evnt) {
		var href = parent.main.location.href;
		if ((href.indexOf("sch_statistics.php") !==-1) || ((href.indexOf("app_sch_login_statistics.php") !==-1)) || ((href.indexOf("sch_course_statistics.php") !==-1))) {
			Calendar_setup("en_begin_date", "%Y-%m-%d", "en_begin_date", false);
			Calendar_setup("en_end_date"  , "%Y-%m-%d", "en_end_date"  , false);
		}
		if (href.indexOf("app_sch_login_statistics.php") !==-1 || href.indexOf("sch_course_statistics.php") !==-1)
		{
            Calendar_setup("single_day"      , "%Y-%m-%d", "single_day"       , false);
   			Calendar_setup("daily_from_date" , "%Y-%m-%d", "daily_from_date"  , false);
			Calendar_setup("daily_over_date" , "%Y-%m-%d", "daily_over_date"  , false);
		}
		
		var obj = null;
}

	window.onload = init;

	window.onunload = function () {
		parent.catalog.location.href = "about:blank";
		parent.FrameExpand(0, false, '');
	};

	window.onerror = function () {
		return true;
	};

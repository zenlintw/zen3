//對話視窗管理 (Popup Mananger)
var Array_Popup = new Array();

function addWindow(id, hwnd) {
    var len = Array_Popup.length;

    if (getHwnd(hwnd._id) == null) {
        Array_Popup[len] = new Array(id, hwnd);
    } else {
        updateHwnd(hwnd);
    }
}

function getHwnd(keyword) {
    if (Array_Popup.length == 0) return null;
    var element;
    for (var i = 0; i < Array_Popup.length; i++) {

        if (Array_Popup[i][0] == keyword) {
            return Array_Popup[i][1];
        }
    }
}

function updateHwnd(obj) {
    for (var i = 0; i < Array_Popup.length; i++) {

        if (Array_Popup[i][0] == obj._id) {
            Array_Popup[i][1] = obj;
        }
    }
}

// 教材選擇的對話視窗 (Content Select)
function WinContentSelect(func) {
    this._id = "WinContentSelect";
    this._url = "/lib/popup/select_content.php";
    this._width = 500;
    this._height = 400;
    this._hwnd = null;
    this._window_option = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=" + this._width + ",height=" + this._height;
    this._attfunc = func;
    addWindow(this._id, this);
}

WinContentSelect.prototype.run = function() {
    this._hwnd = window.open(this._url, this._id, this._window_option);
};

WinContentSelect.prototype.callback = function(rtnArray) {
    eval(this._attfunc + "(rtnArray)");
}

// 選擇教師的對話視窗 (Teacher Select)
function WinTeacherSelect(func) {
    this._id = "WinTeacherSelect";
    this._url = "/lib/popup/select_teacher.php";
    this._width = 500;
    this._height = 400;
    this._hwnd = null;
    this._window_option = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=" + this._width + ",height=" + this._height;
    this._attfunc = func;
    addWindow(this._id, this);
}

WinTeacherSelect.prototype.run = function() {
    this._hwnd = window.open(this._url, this._id, this._window_option);
};

WinTeacherSelect.prototype.callback = function(rtnArray) {
    eval(this._attfunc + "(rtnArray)");
}

// 選擇課程的對話視窗 (Teacher Select)
function WinCourseSelect(func) {
    this._id = "WinCourseSelect";
    this._url = "/lib/popup/select_course.php";
    this._width = 500;
    this._height = 420;
    this._hwnd = null;
    this._window_option = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=" + this._width + ",height=" + this._height;
    this._attfunc = func;
    addWindow(this._id, this);
}

WinCourseSelect.prototype.run = function() {
    this._hwnd = window.open(this._url, this._id, this._window_option);
};

WinCourseSelect.prototype.callback = function(rtnArray, rtnArray1) {
    eval(this._attfunc + "(rtnArray, rtnArray1)");
}

// 選擇教師的對話視窗 (Teacher Select) 融合選取助教
function WinMTeacherSelect(func, defaultValue) {
    // /home/WMPRO5_CCH/base/10001/course/10000001/content
    var basepath = $("input[name='basePath']").val();
    var cid_split = basepath.split('/');
    var cid = cid_split[cid_split.length-2];
    this._id = "WinMTeacherSelect";
    this._url = "/lib/popup/m_select_teacher.php?func=" + func + '&cid=' + cid;
    this._width = 960;
    this._height = 700;
    this._hwnd = null;
    this._window_option = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=" + this._width + ",height=" + this._height;
    this._attfunc = func;
    this._default_value = defaultValue;
    addWindow(this._id, this);
}

WinMTeacherSelect.prototype.run = function() {
    var dForm = document.createElement("form");
    dForm.target = this._id;
    dForm.method = "POST";
    dForm.action = this._url;
    if (typeof this._default_value !== 'undefined') {
        var dInput = document.createElement("input");
        dInput.type = "text";
        dInput.name = "selected_items";
        dInput.value = this._default_value;
        dForm.appendChild(dInput);
    }
    document.body.appendChild(dForm);
    this._hwnd = window.open('', this._id, this._window_option);
    if (this._hwnd) {
        dForm.submit();
    } else {
        alert('You must allow popups for this map to work.');
    }
    document.body.removeChild(dForm);
};

WinMTeacherSelect.prototype.callback = function(rtnArray) {
    eval(this._attfunc + "(rtnArray)");
}

// APP 推播 - Begin
// 選擇APP推播接收清單的對話視窗 (APP Push User Select)
function WinAPPPushUserSelect(func) {
    this._id = "WinAPPPushUserSelect";
    this._url = "/lib/popup/app_push_select_user.php";
    this._width = 500;
    this._height = 400;
    this._hwnd = null;
    this._window_option = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=" + this._width + ",height=" + this._height;
    this._attfunc = func;
    addWindow(this._id, this);
}

WinAPPPushUserSelect.prototype.run = function() {
    this._hwnd = window.open(this._url, this._id, this._window_option);
};

WinAPPPushUserSelect.prototype.callback = function(rtnArray) {
    eval(this._attfunc + "(rtnArray)");
}
// APP 推播 - End

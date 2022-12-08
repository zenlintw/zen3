function sumPercent(e, pnumber)
{
    if (!/^\d+$/.test(pnumber)) {
        var newValue = /^\d+/.exec(e.value);
        if (newValue != null) {
            e.value = newValue;
        } else {
            e.value = '';
        }
    }

    activeTeacherPercent();

    return false;
}

// 成績公佈日期區間
function statListScoreDateShow(val) {
    var obj = null;
    var v = (val != 1);
    obj = document.getElementById("divScore");
    if (obj != null) obj.style.display = v ? "" : "none";
    if (val === '1') {
        $('#score_begin_time').val('0000-00-00 00:00');
        $('#score_close_time').val('0000-00-00 00:00');
    }
}

// 互評、自評
function statListAsseShow(objName, state) {
    var obj = document.getElementById(objName);
    if (obj != null) {
        obj.style.display = state ? "" : "none";
    }
    if ($('#ck_peer_assessment').prop('checked') === true || $('#ck_self_assessment').prop('checked') === true) {
        $('#trOpen .strong-note').show();
        $('#ck_rating_begin_time').prop('checked', true);
        showDateInput('span_rating_begin_time', true);
        $('#trClose .strong-note').show();
        $('#ck_rating_close_time').prop('checked', true);
        showDateInput('span_rating_close_time', true);
    } else {
        $('#trOpen .strong-note').hide();
        $('#ck_rating_begin_time').prop('checked', false);
        showDateInput('span_rating_begin_time', false);
        $('#trClose .strong-note').hide();
        $('#ck_rating_close_time').prop('checked', false);
        showDateInput('span_rating_close_time', false);
    }

    if ($('#ck_peer_assessment').prop('checked') === false) {
        $('#peer_percent').val(0);
        $('#peer_times').val(3);
    }
    if ($('#ck_self_assessment').prop('checked') === false) {
        $('#self_percent').val(0);
    }

    // 控制優先權
    if ($('#ck_peer_assessment').prop('checked') === true && $('#ck_self_assessment').prop('checked') === true) {
        $('#spanAssessRation').show();
    } else {
        // $('#sysRadioBtn8').prop('checked', true);
        $('#spanAssessRation').hide();
    }

    activeTeacherPercent();
}

function activeTeacherPercent() {
    var peer = $('#peer_percent').val();
    var self = $('#self_percent').val();
    if (peer === '' || peer === null) {
        peer = 0;
    }
    if (self === '' || self === null) {
        self = 0;
    }
    $('#span_teacher_percent').text(100 - peer - self);
}
var NewCalendarColor = {
    person: "#218B85",
    course: "#D95155",
    school: "#F39C1C"
};
var CalEnvType = {
    learn: "person",
    teach: "course",
    academic: "school"
};
// 秀日曆的函數
function Calendar_setup(ifd, fmt, btn, shtime) {
    Calendar.setup({
        inputField: ifd,
        ifFormat: fmt,
        showsTime: shtime,
        time24: true,
        button: btn,
        singleClick: true,
        weekNumbers: false,
        step: 1
    });
}

function getNewCalendar(data, type) {
    if (data.calEnv != "learn" && type != CalEnvType[data.calEnv]) return;
    data.type = type;
    $.ajax({
        type: "POST",
        url: "/learn/newcalendar/cale_memo.php",
        dataType: "json",
        data: data,
        success: function(res) {
            var eventAry = [];
            var eventIdAry = [];
            for (var index in res) {
                var event = res[index];
                var color = NewCalendarColor[event.type];
                var eventObj = {
                    id: event.idx,
                    type: event.type,
                    rawdata: event,
                    className: event.type,
                    title: event.subject,
                    color: color,
                    start: event.memo_date
                };
                if (data.action == "day") {
                    if (event.time_begin && event.time_begin != null) {
                        eventObj.start = event.memo_date + "T" + event.time_begin;
                        eventObj.end = event.memo_date + "T" + event.time_end;
                        if (event.parent_idx != 0 && event.repeat_begin != "0000-00-00") {
                            eventObj.id = event.parent_idx;
                        }
                    }
                } else if (event.repeat == "day") {
                    if (event.parent_idx != 0 && event.repeat_begin != "0000-00-00") {
                        eventObj.start = event.repeat_begin;
                        eventObj.id = event.parent_idx;
                    }
                    eventObj.end = moment(event.repeat_end).add(1, 'd').format('YYYY-MM-DD');
                }
                if ($.inArray(eventObj.id, eventIdAry) === -1) {
                    eventIdAry.push(eventObj.id);
                    eventAry.push(eventObj);
                }
            }
            $('#newcalendar').fullCalendar('addEventSource', eventAry);
        },
        error: function(res) {

        }
    });
}

function saveCalendarSetting(ticket, newCalendarDispalyType) {
    $.ajax({
        type: "POST",
        url: "/learn/newcalendar/cale_memo.php",
        dataType: "json",
        data: {
            ticket: ticket,
            action: 'setting',
            type: newCalendarDispalyType
        }
    });
}


$.widget("shift.selectable", $.ui.selectable, {
    options: {},
    previousIndex: -1,
    currentIndex: -1,
    _create: function() {
        var self = this;
        $.ui.selectable.prototype._create.call(this);

        $(this.element).on('selectableselecting', function(event, ui) {
            self.currentIndex = $(self.options.filter, event.target).index(ui.selecting);
            if (event.shiftKey && self.previousIndex > -1) {
                $(self.options.filter, event.target).slice(Math.min(self.previousIndex, self.currentIndex), 1 + Math.max(self.previousIndex, self.currentIndex)).addClass('ui-selected');
                self.previousIndex = -1;
            } else {
                self.previousIndex = self.currentIndex;
            }
        });
    },
    destroy: function() {
        $.ui.selectable.prototype.destroy.call(this);
    },
    _setOption: function() {
        $.ui.selectable.prototype._setOption.apply(this, arguments);
    }
});

(function($) {
    var check = false,
        isRelative = true;

    $.elementFromPoint = function(x, y) {
        if (!document.elementFromPoint) return null;

        if (!check) {
            var sl;
            if ((sl = $(document).scrollTop()) > 0) {
                isRelative = (document.elementFromPoint(0, sl + $(window).height() - 1) == null);
            } else if ((sl = $(document).scrollLeft()) > 0) {
                isRelative = (document.elementFromPoint(sl + $(window).width() - 1, 0) == null);
            }
            check = (sl > 0);
        }

        if (!isRelative) {
            x += $(document).scrollLeft();
            y += $(document).scrollTop();
        }

        return document.elementFromPoint(x, y);
    }

})(jQuery);
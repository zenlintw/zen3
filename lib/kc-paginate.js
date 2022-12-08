(function ($) {
    var
        defaults = {
            'total': 1,
            'pageSize': 10,
            'pageNumber': 1,
            'pageList': [10, 20, 30, 50],
            'buttonCls': 'paginate-btn',
            'loading': false,
            'beforeButtons': [],
            'afterButtons': [],
            'showPageList': true,
            'showRefresh': true,
            'showSeparator': true,
            'onSelectPage': null,
            'onBeforeRefresh': null,
            'onRefresh': null,
            'onChangePageSize': null,
            'btnTitleFirst': 'First Page',
            'btnTitlePrev': 'Previous Page',
            'btnTitleNext': 'Next Page',
            'btnTitleLast': 'Last Page',
            'btnTitleRefresh': 'Refresh',
            'beforePageText': 'Page',
            'afterPageText': 'of {pages}',
            'beforePerPageText': 'Page:',
            'afterPerPageText': 'items',
            'displayMsg': 'Displaying {from} to {to} of {total} items'
        };

    /**
     * 顯示訊息
     */
    function showMessage($node, options) {
        var msg = $node.find('.paginate-message');

        msg.text(
            options.displayMsg
                .replace('{total}', options.total)
                .replace('{to}', Math.min(options.total, options.pageSize * options.pageNumber))
                .replace('{from}', (options.total === 0) ? '0' : (options.pageSize * (options.pageNumber - 1)) + 1)
        );
    }

    /**
     * 設定按鈕狀態
     */
    function buttonStatus($node, options) {
        if (options.total === 0) {
            options.pageNumber = 0;
            options.pageTotal = 0;
        } else {
            // 計算總頁數
            options.pageTotal = Math.ceil(options.total / Math.max(1, options.pageSize));
            options.pageNumber = Math.max(1, Math.min(options.pageTotal, options.pageNumber));
        }
        $node.data(options);

        $node.find('.paginate-number').val(options.pageNumber);

        if (parseInt(options.pageNumber, 10) <= 1) {
            $node.find('.paginate-first, .paginate-prev')
                .parent()
                .addClass('disabled')
                .prop('disabled', true);
        } else {
            $node.find('.paginate-first, .paginate-prev')
                .parent()
                .removeClass('disabled')
                .removeProp('disabled').removeAttr('disabled');
        }

        if (options.pageNumber === options.pageTotal) {
            $node.find('.paginate-last, .paginate-next')
                .parent()
                .addClass('disabled')
                .prop('disabled', true);
        } else {
            $node.find('.paginate-last, .paginate-next')
                .parent()
                .removeClass('disabled')
                .removeProp('disabled').removeAttr('disabled');
        }

        $node.find('.paginate-list').val(options.pageSize);
        $node.find('.paginate-number').val(options.pageNumber);
        $node.find('.paginate-number-before')
            .text(options.beforePageText.replace('{pages}', options.pageTotal));
        $node.find('.paginate-number-after')
            .text(options.afterPageText.replace('{pages}', options.pageTotal));

        showMessage($node, options);
    }

    /**
     * 換頁或跳頁
     * @param me     發生事件的 Element
     * @param $node  jQuery 物件
     * @param action first, prev, next, last 或頁數
     * @return {Boolean}
     */
    function changePage(me, $node, action) {
        var num, options = $node.data();

        if ($(me).attr('disabled') === 'disabled') {
            return false;
        }

        switch (action) {
        case 'first': // 第一頁
            options.pageNumber = 1;
            break;
        case 'prev': // 前一頁
            options.pageNumber = Math.max(1, options.pageNumber - 1);
            break;
        case 'next': // 下一頁
            options.pageNumber = Math.min(options.pageTotal, options.pageNumber + 1);
            break;
        case 'last': // 最後一頁
            options.pageNumber = options.pageTotal;
            break;
        default:
            num = parseInt(action, 10);
            if (isNaN(num)) {
                options.pageNumber = 1;
            } else {
                options.pageNumber = Math.max(1, Math.min(options.pageTotal, num));
            }
            break;
        }
        buttonStatus($node, options);
        if ((options.onSelectPage !== null) && (typeof options.onSelectPage === 'function')) {
            options.onSelectPage.call(me, options.pageNumber, options.pageSize);
        }
        return false;
    }

    /**
     * 顯示自訂按鈕
     * @param $tr
     * @param $buttons 按鈕的設定值
     * @param btnCls   按鈕的 class
     */
    function showButtons($tr, $buttons, options) {
        var btnCls;

        if (options.buttonCls !== '') {
            btnCls = 'paginate-btn ' + options.buttonCls;
        }

        $buttons.each(function () {
            var
                iconCls = 'icon', $item, $icon, self = this;

            if (self instanceof String) {
                if (self.toString() === '-') {
                    $('<td><div class="paginate-separator"></div></td>').appendTo($tr);
                } else {
                    $('<td></td>').text(self).appendTo($tr);
                }
            } else if (self instanceof Object) {
                $item = $('<a class="' + btnCls + '"></a>');
                if ((typeof self.title === 'string') && (self.title !== '')) {
                    $item.attr('title', self.title);
                }

                if ((typeof self.iconCls === 'string') && (self.iconCls !== '')) {
                    iconCls = self.iconCls;
                }
                $icon = $('<i class="' + iconCls + '"></i>');
                if ((typeof self.text === 'string') && (self.text !== '')) {
                    $icon.after('<span class="text">' + self.text + '</span>');
                }

                $item.append($icon)
                    .click(function () {
                        if (typeof self.handler === 'function') {
                            self.handler.call(this, options);
                        }
                    });
                $('<td></td>').append($item).appendTo($tr);
            }
        });
    }

    /**
     * 重新整理
     */
    function refresh(me, $node, options) {
        var res = true;

        if ((options.onBeforeRefresh !== null) && (typeof options.onBeforeRefresh === 'function')) {
            res = options.onBeforeRefresh.call(me, options.pageNumber, options.pageSize);
            if (!res) {
                return false;
            }
        }
        // 更新按鈕狀態
        buttonStatus($node, options);
        if ((options.onRefresh !== null) && (typeof options.onRefresh === 'function')) {
            options.onRefresh.call(me, options.pageNumber, options.pageSize);
        }
        return false;
    }

    /**
     * 顯示分頁按鈕
     */
    function showPaginate($node) {
        var
            buttonCls, settings,
            $tr, $item, $buttons;

        $node.addClass('paginate');

        settings = $node.data();
        if (settings.buttonCls !== '') {
            buttonCls = 'paginate-btn ' + settings.buttonCls;
        }

        $tr = $('<tr></tr>');

        // 顯示自訂圖示
        $buttons = $(settings.beforeButtons);
        showButtons($tr, $buttons, settings);
        if ($buttons.length > 0) {
            $('<td><div class="paginate-separator"></div></td>').appendTo($tr);
        }

        // 第一頁
        $item = $('<a class="' + buttonCls + '"></a>')
            .attr('title', settings.btnTitleFirst)
            .html('<i class="paginate-first"></i>')
            .click(function () {
                changePage(this, $node, 'first');
                return false;
            });
        $('<td></td>').append($item).appendTo($tr);

        // 前一頁
        $item = $('<a class="' + buttonCls + '"></a>')
            .attr('title', settings.btnTitlePrev)
            .html('<i class="paginate-prev"></i>')
            .click(function () {
                changePage(this, $node, 'prev');
                return false;
            });
        $('<td></td>').append($item).appendTo($tr);

        // 分隔線
        if (settings.showSeparator) {
            $('<td><div class="paginate-separator"></div></td>').appendTo($tr);
        }

        // 跳頁
        $item = $('<input type="text" class="paginate-number">')
            .val(settings.pageNumber)
            .change(function () {
                changePage(this, $node, $(this).val());
                return false;
            });
        $('<td></td>')
            .append(
                $('<span class="paginate-number-before"></span>')
                    .text(settings.beforePageText.replace('{pages}', settings.pageTotal))
            )
            .append($item)
            .append(
                $('<span class="paginate-number-after"></span>')
                    .text(settings.afterPageText.replace('{pages}', settings.pageTotal))
            )
            .appendTo($tr);

        // 分隔線
        if (settings.showSeparator) {
            $('<td><div class="paginate-separator"></div></td>').appendTo($tr);
        }

        // 下一頁
        $item = $('<a class="' + buttonCls + '"></a>')
            .attr('title', settings.btnTitleNext)
            .html('<i class="paginate-next"></i>')
            .click(function () {
                changePage(this, $node, 'next');
                return false;
            });
        $('<td></td>').append($item).appendTo($tr);

        // 最後一頁
        $item = $('<a class="' + buttonCls + '"></a>')
            .attr('title', settings.btnTitleLast)
            .html('<i class="paginate-last"></i>')
            .click(function () {
                changePage(this, $node, 'last');
                return false;
            });
        $('<td></td>').append($item).appendTo($tr);

        // 顯示每頁幾筆
        if (settings.showPageList) {
            $item = $('<select class="paginate-list"></select>');
            $(settings.pageList).each(function () {
                $item.append('<option value="' + this + '">' + this + '</option>');
            });
            $item.val(settings.pageSize)
                .change(function () {
                    var size = $(this).val(), options;

                    options = $node.data();
                    options.pageNumber = 1;
                    options.pageSize = parseInt(size, 10);
                    options.pageTotal = Math.ceil(options.total / Math.max(1, options.pageSize));
                    buttonStatus($node, options);

                    if ((options.onChangePageSize !== null) && (typeof options.onChangePageSize === 'function')) {
                        options.onChangePageSize.call(this, size);
                    }
                });
            $('<td></td>')
                .append(settings.beforePerPageText)
                .append($item)
                .append(settings.afterPerPageText)
                .appendTo($tr);
            if (settings.showSeparator) {
                $('<td><div class="paginate-separator"></div></td>').appendTo($tr);
            }
        }

        // 重新整理
        if (settings.showRefresh) {
            if (settings.showSeparator) {
                $('<td><div class="paginate-separator"></div></td>').appendTo($tr);
            }
            $item = $('<a class="' + buttonCls + '"></a>')
                .attr('title', settings.btnTitleRefresh)
                .html('<i class="paginate-load"></i>')
                .click(function () {
                    refresh(this, $node, $node.data());
                    return false;
                });
            $('<td></td>').append($item).appendTo($tr);
        }

        // 顯示自訂圖示
        $buttons = $(settings.afterButtons);
        if ($buttons.length > 0) {
            $('<td><div class="paginate-separator"></div></td>').appendTo($tr);
        }
        showButtons($tr, $buttons, settings);

        $('<table cellpadding="0" cellspacing="0" border="0" class="paginate-toolbar"></table>')
            .append($tr)
            .appendTo($node);

        // 顯示訊息
        $('<div class="paginate-message"></div>').appendTo($node);
        $('<div style="clear: both;"></div>').appendTo($node);
        buttonStatus($node, settings);
    }

    /**
     * 主程式
     * @param params
     * @param extra
     * @return {*}
     */
    $.fn.paginate = function (params, extra) {
        if ((typeof params === 'string') && (params === 'options')) {
            return this.data();
        }
        return this.each(function () {
            var $self = $(this), settings;

            if (typeof params === 'string') {
                if (params === 'select') {
                    settings = $self.data();
                    if (typeof extra !== 'undefined') {
                        settings.pageNumber = parseInt(extra, 10);
                    }
                    changePage(null, $self, settings.pageNumber);
                }
                if (params === 'refresh') {
                    settings = $self.data();
                    if (typeof extra !== 'undefined') {
                        settings = $.extend({}, defaults, settings, extra);
                        settings.pageTotal = Math.ceil(settings.total / Math.max(1, settings.pageSize));
                    }
                    buttonStatus($self, settings);
                }
                return true;
            }

            settings = $.extend({}, defaults, params);
            $self.data(settings);
            showPaginate($self);
        });
    };
})(jQuery);
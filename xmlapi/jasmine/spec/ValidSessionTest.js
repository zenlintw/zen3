describe("驗證 session", function () {
    var responseNoDataInfo, ticketDataNoDataInfo, responseErrDataInfo, ticketDataErrDataInfo, response, ticketData;

    // 沒傳參
    jQuery.ajax({
        url: '../index.php?action=valid-session',
        type: 'GET',
        async: false,
        complete: function (returnInfo) {
            responseNoDataInfo = returnInfo;
            ticketDataNoDataInfo = JSON.parse(responseNoDataInfo.responseText);
        }
    });
    it("驗證沒有傳參的 Response status - 須為200", function () {
        expect(responseNoDataInfo.status).toEqual(200);
    });

    it("驗證沒有傳參的 Response Data - 要有值", function () {
        expect(ticketDataNoDataInfo).toBeDefined();
    });

    it("驗證沒有傳參的 Response Data code - 要是 0", function () {
        expect(ticketDataNoDataInfo.code).toEqual(0);
    });

    it("驗證沒有傳參的 Response Data message - type 是 string", function () {
        expect(typeof(ticketDataNoDataInfo.message)).toEqual('string');
    });

    it("驗證沒有傳參的 Response Data message - 要是Success.", function () {
        expect(ticketDataNoDataInfo.message).toEqual('Success.');
    });
    // 錯誤的ticket
    jQuery.ajax({
        url: '../index.php?action=valid-session',
        type: 'GET',
        data: {
            ticket: 'gg'
        },
        async: false,
        complete: function (returnInfo) {
            responseErrDataInfo = returnInfo;
            ticketDataErrDataInfo = JSON.parse(responseErrDataInfo.responseText);
        }
    });
    it("驗證錯誤ticket的 Response status - 須為200", function () {
        expect(responseErrDataInfo.status).toEqual(200);
    });

    it("驗證錯誤ticket的 Response Data - 要有值", function () {
        expect(ticketDataErrDataInfo).toBeDefined();
    });

    it("驗證錯誤ticket的 Response Data code - 要是 1", function () {
        expect(ticketDataErrDataInfo.code).toEqual(1);
    });

    it("驗證錯誤ticket的 Response Data message - type 是 string", function () {
        expect(typeof(ticketDataErrDataInfo.message)).toEqual('string');
    });

    it("驗證錯誤ticket的 Response Data message - 要是Session not exists.", function () {
        expect(ticketDataErrDataInfo.message).toEqual('Session not exists.');
    });
    // 正確的ticket
    jQuery.ajax({
        url: '../index.php?action=valid-session',
        type: 'GET',
        data: {
            ticket: ticket
        },
        async: false,
        complete: function (returnInfo) {
            response = returnInfo;
            ticketData = JSON.parse(response.responseText);
        }
    });

    it("驗證正確ticket的 Response status - 須為200", function () {
        expect(response.status).toEqual(200);
    });

    it("驗證正確ticket的 Response Data - 要有值", function () {
        expect(ticketData).toBeDefined();
    });

    it("驗證正確ticket的 Response Data code - 要是 0", function () {
        expect(ticketData.code).toEqual(0);
    });

    it("驗證正確ticket的 Response Data message - type 是 string", function () {
        expect(typeof(ticketData.message)).toEqual('string');
    });

    it("驗證正確ticket的 Response Data message - 要是Success.", function () {
        expect(ticketData.message).toEqual('Success.');
    });
});

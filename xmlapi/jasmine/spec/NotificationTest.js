describe("推播訊息", function () {
    var response, jsonData;
    jQuery.ajax({
        url: '../index.php?action=get-notification&page=1&pagesize=20',
        type: 'GET',
        data: {
            ticket: ticket
        },
        async: false,
        complete: function (returnInfo) {
            response = returnInfo;
            jsonData = JSON.parse(response.responseText);
        }
    });

    it("驗證 status - 要是 200", function () {
        expect(response.status).toEqual(200);
    });

    it("驗證 Notification Data - 要有值", function () {
        expect(jsonData).toBeDefined();
    });

    it("驗證 Notification Code - 要是 0", function () {
        expect(jsonData.code).toEqual(0);
    });

    it("驗證 Notification Message - type 是 string", function () {
        expect(typeof(jsonData.message)).toEqual('string');
    });

    it("驗證 Notification Message - 要是'success'", function () {
        expect(jsonData.message).toEqual('success');
    });

    it("驗證 Notification List - 要有值", function () {
        expect(jsonData.data.list).toBeDefined();
    });
    describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
        describe("驗證 JSON Data > data > list > msg_id", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].msg_id).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].msg_id)).toEqual('string');
            });
            msgId = jsonData.data.list[0].msg_id;
        });
        describe("驗證 JSON Data > data > list > content", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].content).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].content)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > create_datetime", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].create_datetime).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].create_datetime)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > readed", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].readed).toBeDefined();
            });
            it("type須為number", function () {
                expect(typeof(jsonData.data.list[0].readed)).toEqual('number');
            });
        });
    });
    describe("閱讀推播訊息", function () {
        if (msgId != 0) {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=set-notification-read',
                type: 'GET',
                data: {
                    ticket: ticket,
                    msg_id: msgId
                },
                async: false,
                complete: function (returnInfo) {
                    response = returnInfo;
                    jsonData = JSON.parse(response.responseText);
                }
            });

            it("驗證 Response status - 須為200", function () {
                expect(response.status).toEqual(200);
            });

            it("驗證 JSON Data - 要有值", function () {
                expect(jsonData).toBeDefined();
            });

            it("驗證 JSON Data Code - 須為0", function () {
                expect(jsonData.code).toEqual(0);
            });

            it("驗證 JSON Data Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 JSON Data Message - 須為'success'", function () {
                expect(jsonData.message).toEqual('success');
            });
        } else {
            it("推播訊息編號錯誤", function () {
                expect(msgId).not.toEqual(0);
            });
        }
    });
});

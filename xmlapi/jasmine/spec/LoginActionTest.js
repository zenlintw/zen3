describe("登入", function () {
    describe("錯誤登入測試", function () {
        var responseNoDataInfo, jsonDataNoDataInfo, response, jsonData;

        // 沒傳參
        jQuery.ajax({
            url: '../index.php?action=login',
            type: 'GET',
            async: false,
            complete: function (returnInfo) {
                responseNoDataInfo = returnInfo;
                jsonDataNoDataInfo = JSON.parse(responseNoDataInfo.responseText);
            }
        });
        it("驗證沒有傳參的 Response status - 須為200", function () {
            expect(responseNoDataInfo.status).toEqual(200);
        });
        it("驗證沒有傳參的 Response Data - 要有值", function () {
            expect(jsonDataNoDataInfo).toBeDefined();
        });
        it("驗證沒有傳參的 Response Data Code - 須為503", function () {
            expect(jsonDataNoDataInfo.code).toEqual(503);
        });
        it("驗證沒有傳參的 Response Data > data - 須為空物件", function () {
            expect(jsonDataNoDataInfo.data).toEqual({});
        });

        // 有傳參
        jQuery.ajax({
            url: '../index.php?action=login',
            type: 'GET',
            async: false,
            data: {
                username: 'GG',
                password: 'GG'
            },
            complete: function (returnInfo) {
                response = returnInfo;
                jsonData = JSON.parse(response.responseText);
            }
        });

        it("驗證有傳參的 Response status - 須為200", function () {
            expect(response.status).toEqual(200);
        });
        it("驗證有傳參的 Response Data - 要有值", function () {
            expect(jsonData).toBeDefined();
        });
        it("驗證有傳參的 Response Data Code - 須為1", function () {
            expect(jsonData.code).toEqual(1);
        });
        it("驗證有傳參的 Response Data Message - type須為string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });
        it("驗證有傳參的 Response Data Message - 要有值，且須為Auth fail", function () {
            expect(jsonData.message).toBeDefined();
            expect(jsonData.message).toEqual('Auth fail');
        });
        it("驗證有傳參的 Response Data > data - 須為空物件", function () {
            expect(jsonData.data).toEqual({});
        });
    });
    describe("正確登入測試", function () {
        var response, jsonData;

        jQuery.ajax({
            url: '../index.php?action=login',
            type: 'GET',
            async: false,
            data: {
                username: username,
                password: password
            },
            complete: function (returnInfo) {
                response = returnInfo;
                jsonData = JSON.parse(response.responseText);
                ticket = jsonData.data.session_data.ticket;
            }
        });

        it("驗證 Response status - 須為200", function () {
            expect(response.status).toEqual(200);
        });
        it("驗證 JSON Data - 要有值", function () {
            expect(jsonData).toBeDefined();
        });
        it("驗證 JSON Data Code -須為0", function () {
            expect(jsonData.code).toEqual(0);
        });
        it("驗證 JSON Data Message - type 是 string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });
        it("驗證 JSON Data Message - 要有值", function () {
            expect(jsonData.message).toBeDefined();
        });
        it("驗證 JSON Data > data - 要有值", function () {
            expect(jsonData.data).toBeDefined();
        });
        it("驗證 JSON Data > data > session_data - 要有值", function () {
            expect(jsonData.data.session_data).not.toBeUndefined();
            expect(jsonData.data.session_data).toBeDefined();
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > session_data > ticket", function () {
                it("要有值", function () {
                    expect(jsonData.data.session_data.ticket).not.toBeUndefined();
                    expect(jsonData.data.session_data.ticket).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.session_data.ticket)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > session_data > username", function () {
                it("要有值", function () {
                    expect(jsonData.data.session_data.username).not.toBeUndefined();
                    expect(jsonData.data.session_data.username).toBeDefined();
                    expect(jsonData.data.session_data.username).toEqual(username);
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.session_data.username)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > session_data > wm_login_url", function () {
                it("要有值", function () {
                    expect(jsonData.data.session_data.wm_login_url).not.toBeUndefined();
                    expect(jsonData.data.session_data.wm_login_url).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.session_data.wm_login_url)).toEqual('string');
                });
            });
        });
    });
});

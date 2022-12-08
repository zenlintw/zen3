describe("Logo 與 Splash", function () {
    var response, jsonData;

    describe("Logo", function () {
        jQuery.ajax({
            url: '../index.php?action=get-logo',
            type: 'GET',
            data: {
                ticket: ticket,
                device: 'TABLET'
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

        it("驗證 Logo Data - 要有值", function () {
            expect(jsonData).toBeDefined();
        });

        it("驗證 Logo Code - 要是 0", function () {
            expect(jsonData.code).toEqual(0);
        });

        it("驗證 Logo Message - type 是 string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });

        it("驗證 Logo Message - 要是'success'", function () {
            expect(jsonData.message).toEqual('success');
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > img", function () {
                it("要有值", function () {
                    expect(jsonData.data.img).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.img)).toEqual('string');
                });
            });
        });
    });
    describe("Splash", function () {
        jQuery.ajax({
            url: '../index.php?action=get-splash',
            type: 'GET',
            data: {
                ticket: ticket,
                device: 'TABLET'
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

        it("驗證 Logo Data - 要有值", function () {
            expect(jsonData).toBeDefined();
        });

        it("驗證 Logo Code - 要是 0", function () {
            expect(jsonData.code).toEqual(0);
        });

        it("驗證 Logo Message - type 是 string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });

        it("驗證 Logo Message - 要是'success'", function () {
            expect(jsonData.message).toEqual('success');
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > img", function () {
                it("要有值", function () {
                    expect(jsonData.data.img).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.img)).toEqual('string');
                });
            });
        });
    });
    describe("Background Logo", function () {
        jQuery.ajax({
            url: '../index.php?action=get-background-logo',
            type: 'GET',
            data: {
                ticket: ticket,
                device: 'TABLET'
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

        it("驗證 Logo Data - 要有值", function () {
            expect(jsonData).toBeDefined();
        });

        it("驗證 Logo Code - 要是 0", function () {
            expect(jsonData.code).toEqual(0);
        });

        it("驗證 Logo Message - type 是 string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });

        it("驗證 Logo Message - 要是'success'", function () {
            expect(jsonData.message).toEqual('success');
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > img", function () {
                it("要有值", function () {
                    expect(jsonData.data.img).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.img)).toEqual('string');
                });
            });
        });
    });
});

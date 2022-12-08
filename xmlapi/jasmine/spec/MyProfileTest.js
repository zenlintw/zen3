describe("個人資料", function () {
    var response, jsonData;

    jQuery.ajax({
        url: '../index.php?action=my-profile',
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

    it("驗證 JSON Data Message - 須為'Success.'", function () {
        expect(jsonData.message).toEqual('success');
    });

    it("驗證 JSON Data > data - 要有值", function () {
        expect(jsonData.data).toBeDefined();
    });

    describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
        describe("驗證 JSON Data > data > username", function () {
            it("要有值", function () {
                expect(jsonData.data.username).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.username)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > realname", function () {
            it("要有值", function () {
                expect(jsonData.data.realname).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.realname)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > sex", function () {
            it("要有值", function () {
                expect(jsonData.data.sex).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.realname)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > birthday", function () {
            it("要有值", function () {
                expect(jsonData.data.birthday).toBeDefined();
            });
            it("type須為string", function () {
                expect(
                    typeof(jsonData.data.birthday) === 'string'
                    || jsonData.data.birthday === null
                ).toBeTruthy();
            });
        });
        describe("驗證 JSON Data > data > email", function () {
            it("要有值", function () {
                expect(jsonData.data.email).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.email)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > tel", function () {
            it("要有值", function () {
                expect(jsonData.data.tel).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.tel)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > mobile", function () {
            it("要有值", function () {
                expect(jsonData.data.mobile).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.mobile)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > major_cnt", function () {
            it("major_cnt - 要有值", function () {
                expect(jsonData.data.major_cnt).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.major_cnt)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > login_times", function () {
            it("要有值", function () {
                expect(jsonData.data.login_times).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.login_times)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > last_login", function () {
            it("要有值", function () {
                expect(jsonData.data.last_login).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.last_login)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > picture", function () {
            it("要有值", function () {
                expect(jsonData.data.picture).toBeDefined();
            });
            it("type須為string", function () {
                expect(
                    typeof(jsonData.data.picture) === 'string'
                    || jsonData.data.picture === null
                ).toBeTruthy();
            });
        });
        describe("驗證 JSON Data > data > total_time", function () {
            it("要有值", function () {
                expect(jsonData.data.total_time).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.total_time)).toEqual('string');
            });
        });
    });
});

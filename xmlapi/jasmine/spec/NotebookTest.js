describe("雲端筆記", function () {
    var response, jsonData,
        S4 = function () {
            return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
        };

    folderId = 'APP_' + S4() + S4() + '-' + S4() + '-' + S4() + '-' + S4() + '-' + S4() + S4() + S4();
    describe("新增筆記本", function () {
        jQuery.ajax({
            url: '../index.php?action=add-notebook&ticket=' + ticket,
            type: 'POST',
            data: JSON.stringify({
                folder_name: '新增的筆記本',
                folder_id: folderId
            }),
            async: false,
            complete: function (returnInfo) {
                response = returnInfo;
                jsonData = JSON.parse(response.responseText);
            }
        });

        it("驗證 status - 要是 200", function () {
            expect(response.status).toEqual(200);
        });

        it("驗證 Note Data - 要有值", function () {
            expect(jsonData).toBeDefined();
        });

        it("驗證 Notebook Code - 要是 0", function () {
            expect(jsonData.code).toEqual(0);
        });

        it("驗證 Notebook message - type 是 string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });

        it("驗證 Notebook Message - 要是'success'", function () {
            expect(jsonData.message).toEqual('success');
        });
    });
    describe("筆記本更名", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=notebook-rename&ticket=' + ticket,
            type: 'POST',
            data: JSON.stringify({
                folder_id: folderId,
                folder_name: '新增而且更名的筆記本'
            }),
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
    });
    describe("刪除筆記本", function () {
        var response, jsonData;

        jQuery.ajax({
            url: '../index.php?action=delete-notebook',
            type: 'GET',
            data: {
                ticket: ticket,
                folder_id: folderId
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
    });
});

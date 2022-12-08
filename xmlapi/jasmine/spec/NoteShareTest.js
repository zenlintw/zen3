describe("雲端筆記分享", function () {
    var response, jsonData;    
    describe("製作分享Key", function () {
        var response, jsonData;

        jQuery.ajax({
            url: '../index.php?action=create-note-share-key&ticket=' + ticket,
            type: 'POST',
            data: JSON.stringify({
                folderId: 'sys_notebook',
                noteId: 98,
                noteTime: '2014-09-02 14:42:40',
			    noteTitle: '222222222'
            }),
            async: false,
            complete: function (returnInfo) {
                response = returnInfo;
                jsonData = JSON.parse(response.responseText);
				shareKey = jsonData.shareKey;
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

		describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > shareKey", function () {
                it("要有值", function () {
                    expect(jsonData.data.shareKey).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.shareKey)).toEqual('string');
                });
            });
        });
    });
});

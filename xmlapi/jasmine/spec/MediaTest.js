describe("驗證演講廳", function () {
    describe("演講廳列表", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=media-category',
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

        it("驗證 JSON Data Message - type 是 string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });

        it("驗證 JSON Data Message - 須為'success'", function () {
            expect(jsonData.message).toEqual('success');
        });

        it("驗證 JSON Data > data - 要有值", function () {
            expect(jsonData.data.data).toBeDefined();
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > category_id", function () {
                it("要有值", function () {
                    expect(jsonData.data.data[0].category_id).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.data[0].category_id)).toEqual('string');
                });
                if (jsonData.data.data[0].category_id != '') {
                    categoryId = jsonData.data.data[0].category_id;
                }
            });
            describe("驗證 JSON Data > data > category_name", function () {
                it("要有值", function () {
                    expect(jsonData.data.data[0].category_name).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.data[0].category_name)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > img_url", function () {
                it("要有值", function () {
                    expect(jsonData.data.data[0].img_url).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.data[0].img_url)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > category_desc", function () {
                it("要有值", function () {
                    expect(jsonData.data.data[0].category_desc).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.data[0].category_desc)).toEqual('string');
                });
            });
        });
    });
    describe("單一演講廳內容", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=media-node',
            type: 'GET',
            data: {
                ticket: ticket,
                category_id: categoryId
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

        it("驗證 JSON Data Data - 要有值", function () {
            expect(jsonData).toBeDefined();
        });

        it("驗證 JSON Data > data - 要有值", function () {
            expect(jsonData.item).toBeDefined();
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > identifier", function () {
                it("要有值", function () {
                    expect(jsonData.item[0].identifier).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.item[0].identifier)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > text", function () {
                it("要有值", function () {
                    expect(jsonData.item[0].text).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.item[0].text)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > href", function () {
                it("要有值", function () {
                    expect(jsonData.item[0].href).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.item[0].href)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > leaf", function () {
                it("要有值", function () {
                    expect(jsonData.item[0].leaf).toBeDefined();
                });
                it("type須為boolean", function () {
                    expect(typeof(jsonData.item[0].leaf)).toEqual('boolean');
                });
            });
        });
    });
});

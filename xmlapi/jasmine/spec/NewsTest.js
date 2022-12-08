describe("最新消息", function () {
    describe("分頁測試", function () {
        var response, jsonData;

        jQuery.ajax({
            url: '../index.php?action=news&offset=0&size=3',
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

        it("驗證 News Data - 要有值", function () {
            expect(jsonData).toBeDefined();
            expect(jsonData.code).toBeDefined();
            expect(jsonData.data).toBeDefined();
            expect(jsonData.data.list).toBeDefined();
            expect(jsonData.data.total_size).toBeDefined();
            expect(jsonData.message).toBeDefined();
        });

        it("驗證 News Code - 要是 0", function () {
            expect(jsonData.code).toEqual(0);
        });

        it("驗證 News Data List 的格式", function () {
            expect(typeof jsonData.data.list).toEqual('object');
        });

        it("驗證 News Data TotalSize 的格式", function () {
            expect(typeof jsonData.data.total_size).toEqual('number');
        });

        it("驗證 News Data List 的數量", function () {
            expect(jsonData.data.list.length).toEqual(3);
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > list > news_id", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].news_id).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.list[0].news_id)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > title", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].title).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.list[0].title)).toEqual('string');
                });
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
            describe("驗證 JSON Data > data > list > unit", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].unit).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.list[0].unit)).toEqual('string');
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
            describe("驗證 JSON Data > data > list > attaches", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].attaches).toBeDefined();
                });
                it("type須為object", function () {
                    expect(typeof(jsonData.data.list[0].attaches)).toEqual('object');
                });
            });
        });
    });
    describe("全部測試", function () {
        var responseAll, jsonDataAll;

        jQuery.ajax({
            url: '../index.php?action=news',
            type: 'GET',
            data: {
                ticket: ticket
            },
            async: false,
            complete: function (returnInfo) {
                responseAll = returnInfo;
                jsonDataAll = JSON.parse(responseAll.responseText);
            }
        });

        it("驗證 status - 要是 200", function () {
            expect(responseAll.status).toEqual(200);
        });

        it("驗證 News Data - 要有值", function () {
            expect(jsonDataAll).toBeDefined();
            expect(jsonDataAll.code).toBeDefined();
            expect(jsonDataAll.data).toBeDefined();
            expect(jsonDataAll.data.list).toBeDefined();
            expect(jsonDataAll.data.total_size).toBeDefined();
            expect(jsonDataAll.message).toBeDefined();
        });

        it("驗證 News Code - 要是 0", function () {
            expect(jsonDataAll.code).toEqual(0);
        });

        it("驗證 News Data List 的格式", function () {
            expect(typeof jsonDataAll.data.list).toEqual('object');
        });

        it("驗證 News Data TotalSize 的格式", function () {
            expect(typeof jsonDataAll.data.total_size).toEqual('number');
        });

        it("驗證 News Data List 的數量", function () {
            expect(jsonDataAll.data.list.length).toEqual(jsonDataAll.data.total_size);
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > list > news_id", function () {
                it("要有值", function () {
                    expect(jsonDataAll.data.list[0].news_id).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonDataAll.data.list[0].news_id)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > title", function () {
                it("要有值", function () {
                    expect(jsonDataAll.data.list[0].title).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonDataAll.data.list[0].title)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > content", function () {
                it("要有值", function () {
                    expect(jsonDataAll.data.list[0].content).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonDataAll.data.list[0].content)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > create_datetime", function () {
                it("要有值", function () {
                    expect(jsonDataAll.data.list[0].create_datetime).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonDataAll.data.list[0].create_datetime)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > unit", function () {
                it("要有值", function () {
                    expect(jsonDataAll.data.list[0].unit).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonDataAll.data.list[0].unit)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > readed", function () {
                it("要有值", function () {
                    expect(jsonDataAll.data.list[0].readed).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonDataAll.data.list[0].readed)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > attaches", function () {
                it("要有值", function () {
                    expect(jsonDataAll.data.list[0].attaches).toBeDefined();
                });
                it("type須為object", function () {
                    expect(typeof(jsonDataAll.data.list[0].attaches)).toEqual('object');
                });
            });
        });
    });
});

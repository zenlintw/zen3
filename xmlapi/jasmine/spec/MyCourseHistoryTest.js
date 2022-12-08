describe("我的歷史課程", function () {
    var response, jsonData;

    jQuery.ajax({
        url: '../index.php?action=my-course-history',
        type: 'GET',
        async: false,
        complete: function (returnInfo) {
            response = returnInfo;
            jsonData = JSON.parse(response.responseText);
        }
    });

    it("驗證 status - 要是 200", function () {
        expect(response.status).toEqual(200);
    });

    it("驗證 Course Data - 要有值", function () {
        expect(jsonData).toBeDefined();
    });

    it("驗證 Course Code - 要是 0", function () {
        expect(jsonData.code).toEqual(0);
    });

    it("驗證 Course Message - type 是 string", function () {
        expect(typeof(jsonData.message)).toEqual('string');
    });

    it("驗證 Course Message - 要是'success'", function () {
        expect(jsonData.message).toEqual('success');
    });

    describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
        describe("驗證 JSON Data > data > list > course_id", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].course_id).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].course_id)).toEqual('number');
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
        describe("驗證 JSON Data > data > list > teacher", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].teacher).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].teacher)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > img_url", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].img_url).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].img_url)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > update_datetime", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].update_datetime).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].update_datetime)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > class_count", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].class_count).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].class_count)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > read_hours", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].read_hours).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].read_hours)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > post_count", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].post_count).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].post_count)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > discuss_count", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].discuss_count).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].discuss_count)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > period", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].period).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].period)).toEqual('string');
            });
        });
    });
});

describe("評量區-問卷投票", function () {
    var response, jsonData, unitType = ['school', 'course'];
    jQuery.ajax({
        url: '../index.php?action=get-questionnaire-list',
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

    it("驗證 JSON Data Message - 須為'success'", function () {
        expect(jsonData.message).toEqual('success');
    });

    it("驗證 JSON Data > data > list - 要有值", function () {
        expect(jsonData.data.list).toBeDefined();
    });
    describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
        describe("驗證 JSON Data > data > list > unit_id", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].unit_id).toBeDefined();
            });
            it("type須為number", function () {
                expect(typeof(jsonData.data.list[0].unit_id)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > unit_name", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].unit_name).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].unit_name)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > unit_type", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].unit_type).toBeDefined();
            });
            it("值是school|course", function () {
                expect(unitType.indexOf(jsonData.data.list[0].unit_type)).not.toEqual(-1);
            });
        });
        describe("驗證 JSON Data > data > list > unit_period", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].unit_period).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].unit_period)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > examId", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].examId).toBeDefined();
            });
            it("type須為number", function () {
                expect(typeof(jsonData.data.list[0].examId)).toEqual('number');
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
        describe("驗證 JSON Data > data > list > status", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].status).toBeDefined();
            });
            it("type須為number", function () {
                expect(typeof(jsonData.data.list[0].status)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > announce_type", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].announce_type).toBeDefined();
            });
            it("type須為number", function () {
                expect(typeof(jsonData.data.list[0].announce_type)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > announce_time", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].announce_time).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].announce_time)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > amount", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].amount).toBeDefined();
            });
            it("type須為number", function () {
                expect(typeof(jsonData.data.list[0].amount)).toEqual('number');
            });
        });
        describe("驗證 JSON Data > data > list > modifiable", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].modifiable).toBeDefined();
            });
            it("type須為boolean", function () {
                expect(typeof(jsonData.data.list[0].modifiable)).toEqual('boolean');
            });
        });
        describe("驗證 JSON Data > data > list > notice", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].notice).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].notice)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > beginTime", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].beginTime).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].beginTime)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > closeTime", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].closeTime).toBeDefined();
            });
            it("type須為string", function () {
                expect(typeof(jsonData.data.list[0].closeTime)).toEqual('string');
            });
        });
        describe("驗證 JSON Data > data > list > upload", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].upload).toBeDefined();
            });
            it("type須為boolean", function () {
                expect(typeof(jsonData.data.list[0].upload)).toEqual('boolean');
            });
        });
        describe("驗證 JSON Data > data > list > anonymity", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].anonymity).toBeDefined();
            });
            it("type須為boolean", function () {
                expect(typeof(jsonData.data.list[0].anonymity)).toEqual('boolean');
            });
        });
        describe("驗證 JSON Data > data > list > done", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].done).toBeDefined();
            });
            it("type須為boolean", function () {
                expect(typeof(jsonData.data.list[0].done)).toEqual('boolean');
            });
        });
        describe("驗證 JSON Data > data > list > statistics_viewable", function () {
            it("要有值", function () {
                expect(jsonData.data.list[0].statistics_viewable).toBeDefined();
            });
            it("type須為boolean", function () {
                expect(typeof(jsonData.data.list[0].statistics_viewable)).toEqual('boolean');
            });
        });
    });
});
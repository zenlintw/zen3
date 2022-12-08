describe("線上點名", function () {
    var rollcall_id = 0, rollcall_course_id = 0;
    if (teacherCourseId !== 0) {
        rollcall_id = teacherCourseId;
    }
    describe("課程列表", function () {
        describe("取得我的課程列表(僅顯示可以閱讀的課程)", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=get-rollcall-course-list',
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

            describe("驗證 data 結構", function () {
                it("驗證 JSON Data > data > list - 要有值", function () {
                    expect(jsonData.data.list).toBeDefined();
                });
                it("驗證 JSON Data > data > total_size - 要有值", function () {
                    expect(jsonData.data.total_size).toBeDefined();
                });
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > list > course_id", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].course_id).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].course_id)).toEqual('number');
                    });
                    it("要為8個字元", function () {
                        expect(jsonData.data.list[0].course_id.toString().length).toEqual(8);
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
                describe("驗證 JSON Data > data > list > img_src", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].img_src).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].img_src)).toEqual('string');
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
                describe("驗證 JSON Data > data > list > role", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].role).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].role)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > student_number", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].student_number).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].student_number)).toEqual('number');
                    });
                });
            });
        });
        // TODO: 取得我的課程搜尋
    });
    describe("取得點名紀錄列表", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=get-rollcall-record-list',
            type: 'GET',
            data: {
                ticket: ticket,
                cid: rollcall_course_id
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

        it("驗證 JSON Data Code - 須為0", function () {
            expect(jsonData.code).toEqual(0);
        });

        it("驗證 JSON Data Message - type須為string", function () {
            expect(typeof(jsonData.message)).toEqual('string');
        });

        it("驗證 JSON Data Message - 須為'success'", function () {
            expect(jsonData.message).toEqual('success');
        });

        describe("驗證 data 結構", function () {
            it("驗證 JSON Data > data > list - 要有值", function () {
                expect(jsonData.data.list).toBeDefined();
            });
            it("驗證 JSON Data > data > total_size - 要有值", function () {
                expect(jsonData.data.total_size).toBeDefined();
            });
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > list > roll_begin_time", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].roll_begin_time).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.list[0].roll_begin_time)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > roll_end_time", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].roll_end_time).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.list[0].roll_end_time)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > list > roll_id", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].roll_id).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].roll_id)).toEqual('number');
                });
                rollcall_id = jsonData.data.list[0].roll_id;
            });
            describe("驗證 JSON Data > data > list > signed", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].signed).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].signed)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > total", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].total).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].total)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > unsigned", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].unsigned).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].unsigned)).toEqual('number');
                });
            });
        });
    });
    describe("取得點名紀錄詳情", function () {
        describe("取得點名紀錄詳情(未到)", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=get-rollcall-record-detail',
                type: 'GET',
                data: {
                    ticket: ticket,
                    cid: rollcall_course_id,
                    rid: rollcall_id
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

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });

            describe("驗證 data 結構", function () {
                it("驗證 JSON Data > data > list - 要有值", function () {
                    expect(jsonData.data.list).toBeDefined();
                });
                it("驗證 JSON Data > data > total_size - 要有值", function () {
                    expect(jsonData.data.total_size).toBeDefined();
                });
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > list > memo", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].memo).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].memo)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > realname", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].realname).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].realname)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > sign_datetime", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].sign_datetime).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].sign_datetime)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > sign_status", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].sign_status).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].sign_status)).toEqual('number');
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
                describe("驗證 JSON Data > data > list > user_picture", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].user_picture).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].user_picture)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > username", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].username).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].username)).toEqual('string');
                    });
                });
            });
        });
        // TODO: 已到 未到 全部 搜尋
    });
    // 老師須同時為學生(?
    describe("測試點名流程", function () {
        describe("未點名前取得點名狀態，應為 code 2 fail", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=get-rollcall-status',
                type: 'GET',
                data: {
                    ticket: ticket,
                    cid: rollcall_course_id,
                    rid: rollcall_id
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

            it("驗證 JSON Code - 須為2", function () {
                expect(jsonData.code).toEqual(2);
            });

            it("驗證 Response Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 Response Message - 須為'fail'", function () {
                expect(jsonData.message).toEqual('fail');
            });

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });
        });
        describe("建立課程點名紀錄", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=send-rollcall-info',
                type: 'POST',
                data: {
                    ticket: ticket,
                    cid: rollcall_course_id
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

            it("驗證 JSON Code - 須為0", function () {
                expect(jsonData.code).toEqual(0);
            });

            it("驗證 Response Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 Response Message - 須為'success'", function () {
                expect(jsonData.message).toEqual('success');
            });

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > rid", function () {
                    it("要有值", function () {
                        expect(jsonData.data.rid).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.rid)).toEqual('number');
                    });
                    // 設定點名編號
                    rollcall_id = jsonData.data.rid;
                });
                describe("驗證 JSON Data > data > student_total", function () {
                    it("要有值", function () {
                        expect(jsonData.data.student_total).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.student_total)).toEqual('number');
                    });
                });
            });
        });
        describe("新增學生點名紀錄", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=send-rollcall-record',
                type: 'POST',
                data: {
                    // GET
                    ticket: ticket,
                    // POST
                    cid: rollcall_course_id,
                    data: [
                        {
                            rid: rollcall_id,
                            username: username,
                            status: 1,
                            memo: "Jasmine 測試",
                            device_ident: "JasmineTestIdentify"
                        }
                    ]
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

            it("驗證 JSON Code - 須為0", function () {
                expect(jsonData.code).toEqual(0);
            });

            it("驗證 Response Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 Response Message - 須為'success'", function () {
                expect(jsonData.message).toEqual('success');
            });

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });

            describe("驗證 data 結構", function () {
                it("驗證 JSON Data > data > list - 要有值", function () {
                    expect(jsonData.data.list).toBeDefined();
                });
                it("驗證 JSON Data > data > total_size - 要有值", function () {
                    expect(jsonData.data.total_size).toBeDefined();
                });
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > list > post_id", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].post_id).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].post_id)).toEqual('string');
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
                describe("驗證 JSON Data > data > list > readed", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].readed).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].readed)).toEqual('number');
                    });
                });
            });
        });
        describe("點名後取得點名狀態", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=get-rollcall-status',
                type: 'GET',
                data: {
                    ticket: ticket,
                    cid: rollcall_course_id,
                    rid: rollcall_id
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

            it("驗證 JSON Code - 須為0", function () {
                expect(jsonData.code).toEqual(0);
            });

            it("驗證 Response Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 Response Message - 須為'success'", function () {
                expect(jsonData.message).toEqual('success');
            });

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > sameDevice", function () {
                    it("要有值", function () {
                        expect(jsonData.data.sameDevice).toBeDefined();
                    });
                    it("type須為boolean", function () {
                        expect(typeof(jsonData.data.sameDevice)).toEqual('boolean');
                    });
                });
                describe("驗證 JSON Data > data > processing", function () {
                    it("要有值", function () {
                        expect(jsonData.data.processing).toBeDefined();
                    });
                    it("type須為boolean", function () {
                        expect(typeof(jsonData.data.processing)).toEqual('boolean');
                    });
                });
            });
        });
        describe("關閉課程點名紀錄", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=send-rollcall-info',
                type: 'POST',
                data: {
                    ticket: ticket,
                    cid: rollcall_course_id,
                    rid: rollcall_id,
                    end_time: new Date()
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

            it("驗證 JSON Code - 須為0", function () {
                expect(jsonData.code).toEqual(0);
            });

            it("驗證 Response Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 Response Message - 須為'success'", function () {
                expect(jsonData.message).toEqual('success');
            });

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });

            describe("驗證 data 結構", function () {
                it("驗證 JSON Data > data > list - 要有值", function () {
                    expect(jsonData.data.list).toBeDefined();
                });
                it("驗證 JSON Data > data > total_size - 要有值", function () {
                    expect(jsonData.data.total_size).toBeDefined();
                });
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > list > post_id", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].post_id).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].post_id)).toEqual('string');
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
                describe("驗證 JSON Data > data > list > readed", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].readed).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].readed)).toEqual('number');
                    });
                });
            });
        });
        // TODO: 編輯
        describe("編輯學生點名紀錄", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=delete-rollcall-info',
                type: 'POST',
                data: {
                    ticket: ticket,
                    cid: rollcall_course_id,
                    rid: rollcall_id
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

            it("驗證 JSON Code - 須為0", function () {
                expect(jsonData.code).toEqual(0);
            });

            it("驗證 Response Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 Response Message - 須為'success'", function () {
                expect(jsonData.message).toEqual('success');
            });

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });
        });
        describe("刪除課程點名紀錄", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=delete-rollcall-info',
                type: 'POST',
                data: {
                    ticket: ticket,
                    cid: rollcall_course_id,
                    rid: rollcall_id
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

            it("驗證 JSON Code - 須為0", function () {
                expect(jsonData.code).toEqual(0);
            });

            it("驗證 Response Message - type須為string", function () {
                expect(typeof(jsonData.message)).toEqual('string');
            });

            it("驗證 Response Message - 須為'success'", function () {
                expect(jsonData.message).toEqual('success');
            });

            it("驗證 JSON Data > data - 要有值", function () {
                expect(jsonData.data).toBeDefined();
            });
        });
    });
});
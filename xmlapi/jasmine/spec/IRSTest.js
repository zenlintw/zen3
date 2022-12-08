describe("即時互動系統", function () {
    var currentDate = new Date(),
        testList = [],
        itemList = [],
        testImg = "data:image/jpeg;base64,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",
        trueItem = {
            title: {
                text: "現在是學生嗎?",
                attaches: [
                    {
                        filename: "title.jpg",
                        base64: testImg
                    }
                ]
            },
            type: 1
        },
        choiceItem = {
            title: {
                text: "你的性別是?",
                attaches: [
                    {
                        filename: "title.jpg",
                        base64: testImg
                    }
                ]
            },
            type: 2,
            optionals: [
                {
                    text: "Man",
                    attaches: [
                        {
                            filename: "boy.jpg",
                            base64: testImg
                        }
                    ]
                },
                {
                    text: "Female",
                    attaches: [
                        {
                            filename: "girl.jpg",
                            base64: testImg
                        }
                    ]
                }
            ]
        },
        multiItem = {
            title: {
                text: "你的興趣是?",
                attaches: [
                    {
                        filename: "title.jpg",
                        base64: testImg
                    }
                ]
            },
            type: 3,
            optionals: [
                {
                    text: "運動",
                    attaches: []
                },
                {
                    text: "看電影",
                    attaches: []
                },
                {
                    text: "電玩",
                    attaches: []
                },
                {
                    text: "美食",
                    attaches: []
                }
            ]
        },
        shortItem = {
            title: {
                text: "目前的心情?",
                attaches: [
                    {
                        filename: "title.jpg",
                        base64: testImg
                    }
                ]
            },
            type: 5
        };
    describe("建立問卷", function () {
        describe("即時出題問卷", function () {
            if (teacherCourseId != 0 && (username != 'guest' || username != '')) {
                var response, jsonData,
                    bTime = currentDate,
                    cTime = new Date(currentDate.getTime() + 7200000),
                    allItem = [trueItem, choiceItem, multiItem, shortItem],
                    i;

                for(i = 0; i < allItem.length; i++) {
                    jQuery.ajax({
                        url: '../index.php?action=create-questionnaire',
                        type: 'POST',
                        data: JSON.stringify({
                            cid: teacherCourseId,
                            ticket: ticket,
                            question: {
                                type: "questionnaire",
                                item: allItem[i],
                                test: {
                                    title: {
                                        Big5: "IRS 測試新建問卷" + allItem[i].type
                                    },
                                    notice: "注意作答時間!",
                                    begin_time: bTime,
                                    close_time: cTime,
                                    rdoPublish: 0
                                }
                            },
                            validWebService: true
                        }),
                        async: false,
                        complete: function (returnInfo) {
                            response = returnInfo;
                            jsonData = JSON.parse(response.responseText);
                        }
                    });
                    describe("驗證題型" + allItem[i].type, function () {
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
                        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                            describe("驗證 JSON Data > data > qti_id", function () {
                                it("要有值", function () {
                                    expect(jsonData.data.qti_id).toBeDefined();
                                });
                                it("type須為number", function () {
                                    expect(typeof(jsonData.data.qti_id)).toEqual('number');
                                });
                                it("要為9個字元", function () {
                                    expect(jsonData.data.qti_id.toString().length).toEqual(9);
                                });
                                testList.push(jsonData.data.qti_id);
                                itemList.push(jsonData.data.item_id);
                            });
                        });
                    });
                }
            }
        });
    });
    describe("取得資料", function () {
        describe("取得課程學員列表", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=get-student-list',
                type: 'GET',
                data: {
                    ticket: ticket,
                    cid: teacherCourseId
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
            it("驗證 JSON Data > data > total_size - 要有值", function () {
                expect(jsonData.data.total_size).toBeDefined();
            });
            it("驗證 JSON Data > data > total_size - type須為 number", function () {
                expect(typeof(jsonData.data.total_size)).toEqual('number');
            });
            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > list > student_id", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].student_id).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].student_id)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > student_name", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].student_name).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].student_name)).toEqual('string');
                    });
                });
            });
        });
        describe("取得問卷", function () {
            jQuery.ajax({
                url: '../index.php?action=get-qti-test-list',
                type: 'GET',
                data: {
                    ticket: ticket,
                    cid: teacherCourseId
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

            it("驗證 JSON Data > data > total_size - 要有值", function () {
                expect(jsonData.data.total_size).toBeDefined();
            });

            it("驗證 JSON Data > data > total_size - type須為 number", function () {
                expect(typeof(jsonData.data.total_size)).toEqual('number');
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                if (jsonData.data.list.length > 0) {
                    describe("驗證 JSON Data > data > list > test_id", function () {
                        it("要有值", function () {
                            expect(jsonData.data.list[0].test_id).toBeDefined();
                        });
                        it("type須為string", function () {
                            expect(typeof(jsonData.data.list[0].test_id)).toEqual('string');
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
                }
            });
        });

        describe("取得題目", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=get-qti-item-list',
                type: 'GET',
                data: {
                    ticket: ticket,
                    cid: teacherCourseId
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

            it("驗證 JSON Data > data > total_size - 要有值", function () {
                expect(jsonData.data.total_size).toBeDefined();
            });

            it("驗證 JSON Data > data > total_size - type須為 number", function () {
                expect(typeof(jsonData.data.total_size)).toEqual('number');
            });

            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                if (jsonData.data.list.length > 0) {
                    describe("驗證 JSON Data > data > list > item_id", function () {
                        it("要有值", function () {
                            expect(jsonData.data.list[0].item_id).toBeDefined();
                        });
                        it("type須為number", function () {
                            expect(typeof(jsonData.data.list[0].item_id)).toEqual('string');
                        });
                    });
                    describe("驗證 JSON Data > data > list > type", function () {
                        it("要有值", function () {
                            expect(jsonData.data.list[0].type).toBeDefined();
                        });
                        it("type須為number", function () {
                            expect(typeof(jsonData.data.list[0].type)).toEqual('number');
                        });
                    });
                    describe("驗證 JSON Data > data > list > text", function () {
                        it("要有值", function () {
                            expect(jsonData.data.list[0].text).toBeDefined();
                        });
                        it("type須為string", function () {
                            expect(typeof(jsonData.data.list[0].text)).toEqual('string');
                        });
                    });
                    describe("驗證 JSON Data > data > list > attaches", function () {
                        it("要有值", function () {
                            expect(jsonData.data.list[0].attaches[0]).toBeDefined();
                        });
                        if (typeof jsonData.data.list[0].attaches !== "undefined" && jsonData.data.list[0].attaches.length > 0) {
                            // 有資料才驗證
                            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                                describe("filename", function () {
                                    it("要有值", function () {
                                        expect(jsonData.data.list[0].attaches[0].filename).toBeDefined();
                                    });
                                    it("type須為string", function () {
                                        expect(typeof(jsonData.data.list[0].attaches[0].filename)).toEqual('string');
                                    });
                                });
                                describe("href", function () {
                                    it("要有值", function () {
                                        expect(jsonData.data.list[0].attaches[0].href).toBeDefined();
                                    });
                                    it("type須為string", function () {
                                        expect(typeof(jsonData.data.list[0].attaches[0].href)).toEqual('string');
                                    });
                                });
                            });
                        }
                    });
                    describe("驗證 JSON Data > data > list > optionals", function () {
                        it("要有值(是非題不須有)", function () {
                            if (jsonData.data.list[0].type !== 1) {
                                expect(jsonData.data.list[0].optionals).toBeDefined();
                            } else {
                                // 是非題不需有
                                expect(jsonData.data.list[0].type).toEqual(1);
                            }
                        });
                        describe("text", function () {
                            it("要有值", function () {
                                expect(jsonData.data.list[0].optionals[0].text).toBeDefined();
                            });
                            it("type須為string", function () {
                                expect(typeof(jsonData.data.list[0].optionals[0].text)).toEqual('string');
                            });
                        });
                    });
                    describe("驗證 JSON Data > data > list > prompt", function () {
                        it("要有值(僅配合題有)", function () {
                            if (jsonData.data.list[0].type === 6) {
                                expect(jsonData.data.list[0].prompt).toBeDefined();
                            } else {
                                // 是非題不需有
                                expect(jsonData.data.list[0].type).not.toEqual(6);
                            }
                        });
                        if (typeof jsonData.data.list[0].prompt !== "undefined" && jsonData.data.list[0].prompt.length > 0) {
                            // 有資料才驗證
                            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                                describe("filename", function () {
                                    it("要有值", function () {
                                        expect(jsonData.data.list[0].prompt[0].filename).toBeDefined();
                                    });
                                    it("type須為string", function () {
                                        expect(typeof(jsonData.data.list[0].prompt[0].filename)).toEqual('string');
                                    });
                                });
                                describe("href", function () {
                                    it("要有值", function () {
                                        expect(jsonData.data.list[0].prompt[0].href).toBeDefined();
                                    });
                                    it("type須為string", function () {
                                        expect(typeof(jsonData.data.list[0].attaches[0].href)).toEqual('string');
                                    });
                                });
                            });
                        }
                    });
                }
            });
        });
    });


    describe("建立指定題目問卷", function () {
        if (teacherCourseId != 0 && (username != 'guest' || username != '')) {
            var response,
                jsonData,
                bTime = currentDate,
                cTime = new Date(currentDate.getTime() + 7200000);

            jQuery.ajax({
                url: '../index.php?action=create-questionnaire',
                type: 'POST',
                data: JSON.stringify({
                    cid: teacherCourseId,
                    ticket: ticket,
                    question: {
                        type: "questionnaire",
                        assign_items: itemList,
                        test: {
                            title: {
                                Big5: "IRS 測試新建問卷(assign)"
                            },
                            notice: "注意作答時間!",
                            begin_time: bTime,
                            close_time: cTime,
                            rdoPublish: 0
                        }
                    },
                    validWebService: true
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
            describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                describe("驗證 JSON Data > data > qti_id", function () {
                    it("要有值", function () {
                        expect(jsonData.data.qti_id).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.qti_id)).toEqual('number');
                    });
                    it("要為9個字元", function () {
                        expect(jsonData.data.qti_id.toString().length).toEqual(9);
                    });
                    testList.push(jsonData.data.qti_id);
                });
            });
        }
    });

    //describe("儲存學生作答", function () {
    //    var response, jsonData;
    //    jQuery.ajax({
    //        url: '../index.php?action=submit-exam',
    //        type: 'POST',
    //        data: {
    //            ticket: ticket,
    //            cid: teacherCourseId
    //        },
    //        data: JSON.stringify({
    //            subject: "API Test",
    //            content: "<p><font color='red'>This is api's web service test.</font></p>",
    //            reply_content : '',
    //            reply_post_id : '',
    //            attaches: [],
    //            validWebService: true
    //        }),
    //        async: false,
    //        complete: function (returnInfo) {
    //            response = returnInfo;
    //            jsonData = JSON.parse(response.responseText);
    //        }
    //    });
    //
    //    it("驗證 Response status - 須為200", function () {
    //        expect(response.status).toEqual(200);
    //    });
    //
    //    it("驗證 JSON Data - 要有值", function () {
    //        expect(jsonData).toBeDefined();
    //    });
    //
    //    it("驗證 JSON Code - 須為0", function () {
    //        expect(jsonData.code).toEqual(0);
    //    });
    //
    //    it("驗證 Response Message - type須為string", function () {
    //        expect(typeof(jsonData.message)).toEqual('string');
    //    });
    //
    //    it("驗證 Response Message - 須為'success'", function () {
    //        expect(jsonData.message).toEqual('success');
    //    });
    //
    //    it("驗證 JSON Data > data - type須為number", function () {
    //        expect(typeof(jsonData.data)).toEqual('number');
    //    });
    //
    //    it("驗證 JSON Data > data > list - 要有值", function () {
    //        expect(jsonData.data.list).toBeDefined();
    //    });
    //
    //    describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
    //        describe("驗證 JSON Data > data > list > post_id", function () {
    //            it("要有值", function () {
    //                expect(jsonData.data.list[0].post_id).toBeDefined();
    //            });
    //            it("type須為string", function () {
    //                expect(typeof(jsonData.data.list[0].post_id)).toEqual('string');
    //            });
    //        });
    //    });
    //});
    describe("問卷結果統計", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=get-questionnaire-result',
            type: 'GET',
            data: {
                ticket: ticket,
                lists: testList[4],
                type: "questionnaire"
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

        it("驗證  JSON Data > data > total - type須為number", function () {
            expect(typeof(jsonData.data.total)).toEqual('number');
        });

        it("驗證 JSON Data > data > test - 要有值", function () {
            expect(jsonData.data.test).toBeDefined();
        });

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > test > subjects", function () {
                it("要有值", function () {
                    expect(jsonData.data.test.subjects).toBeDefined();
                });
                describe("驗證 JSON Data > data > test > subjects > id", function () {
                    it("要有值", function () {
                        expect(jsonData.data.test.subjects[0].id).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.test.subjects[0].id)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > test > subjects > text", function () {
                    it("要有值", function () {
                        expect(jsonData.data.test.subjects[0].text).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.test.subjects[0].text)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > test > subjects > type", function () {
                    it("要有值", function () {
                        expect(jsonData.data.test.subjects[0].type).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.test.subjects[0].type)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > test > subjects > attaches", function () {
                    it("要有值", function () {
                        expect(jsonData.data.test.subjects[0].attaches).toBeDefined();
                    });
                });
                describe("驗證 JSON Data > data > test > subjects > option", function () {
                    it("要有值", function () {
                        expect(jsonData.data.test.subjects[0].option).toBeDefined();
                    });
                    describe("驗證 JSON Data > data > test > subjects > option > caption", function () {
                        it("要有值", function () {
                            expect(jsonData.data.test.subjects[0].option[0].caption).toBeDefined();
                        });
                    });
                    describe("驗證 JSON Data > data > test > subjects > option > users", function () {
                        it("要有值", function () {
                            expect(jsonData.data.test.subjects[0].option[0].users).toBeDefined();
                        });
                    });
                });
            });
        });
    });
    describe("關閉問卷", function () {
        var response, jsonData;

        jQuery.ajax({
            url: '../index.php?action=modify-questionnaire',
            type: 'POST',
            data: JSON.stringify({
                ticket: ticket,
                cid: teacherCourseId,
                question: {
                    eid: testList[0],
                    type: "questionnaire",
                    test: {
                        publish: "close"
                    }
                },
                jasmine: {
                    testList: testList,
                    itemList: itemList
                },
                validWebService: true
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

    describe("websocket連線測試", function () {
        var socketConnection = new WebSocket('ws://' + document.location.hostname + ':4568');

        it("驗證 連線成功訊息", function () {
            socketConnection.onmessage = function (data) {
                expect(JSON.decode(data).action.toEqual('connect'));
            }
        });

        it("驗證 從websocket取得即時課程", function () {
            socketConnection.onmessage = function (data) {
                expect(JSON.decode(data).action.toEqual('list-rooms'));
            }
            var object = {};
            object.action = 'list-rooms';
            object.sender = 'jasmine';
            object.data = {};
            socketConnection.send(JSON.stringify(object));

        });

        socketConnection.close();
    });
});
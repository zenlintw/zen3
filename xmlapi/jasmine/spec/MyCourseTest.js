describe("我的課程", function () {
    describe("課程列表", function () {
        var response, jsonData;
        describe("取得我的課程列表(包含所有已經修過的課程)", function () {
            jQuery.ajax({
                url: '../index.php?action=my-course-history',
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
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].class_count)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > read_hours", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].read_hours).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].read_hours)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > post_count", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].post_count).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].post_count)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > discuss_count", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].discuss_count).toBeDefined();
                    });
                    it("type須為number", function () {
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

        describe("取得我的課程列表(僅顯示可以閱讀的課程)", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=my-course-list',
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
                    courseId = jsonData.data.list[0].course_id;
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
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].class_count)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > read_hours", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].read_hours).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].read_hours)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > post_count", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].post_count).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].post_count)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > discuss_count", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].discuss_count).toBeDefined();
                    });
                    it("type須為number", function () {
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
                describe("驗證 JSON Data > data > list > bookmark", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].bookmark).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].bookmark)).toEqual('number');
                    });
                });
            });
        });
        describe("取得我教授的課程列表", function () {
            jQuery.ajax({
                url: '../index.php?action=teacher-course-list',
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
                    teacherCourseId = jsonData.data.list[0].course_id;
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
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].class_count)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > read_hours", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].read_hours).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].read_hours)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > post_count", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].post_count).toBeDefined();
                    });
                    it("type須為number", function () {
                        expect(typeof(jsonData.data.list[0].post_count)).toEqual('number');
                    });
                });
                describe("驗證 JSON Data > data > list > discuss_count", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].discuss_count).toBeDefined();
                    });
                    it("type須為number", function () {
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
    });
    describe("進入教室", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=go-course',
            type: 'GET',
            data: {
                ticket: ticket,
                cid: courseId
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
    });
    describe("學習節點", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=my-course-path-info',
            type: 'GET',
            data: {
                ticket: ticket,
                cid: courseId
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

        describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
            describe("驗證 JSON Data > data > course_id", function () {
                it("要有值", function () {
                    expect(jsonData.data.course_id).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.course_id)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > base_url", function () {
                it("要有值", function () {
                    expect(jsonData.data.base_url).toBeDefined();
                });
                it("type須為string", function () {
                    expect(typeof(jsonData.data.base_url)).toEqual('string');
                });
            });
            describe("驗證 JSON Data > data > progress", function () {
                it("要有值", function () {
                    expect(jsonData.data.progress).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.progress)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > path", function () {
                it("要有值", function () {
                    expect(jsonData.data.path).toBeDefined();
                });
                describe("驗證 JSON Data > data > path 資料的各項參數與是否符合API設定的型態", function () {
                    describe("驗證 JSON Data > data > path > text", function () {
                        it("要有值", function () {
                            expect(jsonData.data.path.text).toBeDefined();
                        });
                        it("type須為string", function () {
                            expect(typeof(jsonData.data.path.text)).toEqual('string');
                        });
                    });
                    describe("驗證 JSON Data > data > path > item - 要有值", function () {
                        it("要有值", function () {
                            expect(jsonData.data.path.item).toBeDefined();
                        });
                    });
                    describe("驗證 JSON Data > data > path 資料的各項參數與是否符合API設定的型態", function () {
                        describe("驗證 JSON Data > data > path > item > identifier", function () {
                            it("要有值", function () {
                                expect(jsonData.data.path.item[0].identifier).toBeDefined();
                            });
                            it("type須為string", function () {
                                expect(typeof(jsonData.data.path.item[0].identifier)).toEqual('string');
                            });
                        });
                        describe("驗證 JSON Data > data > path > item > text", function () {
                            it("要有值", function () {
                                expect(jsonData.data.path.item[0].text).toBeDefined();
                            });
                            it("type須為string", function () {
                                expect(typeof(jsonData.data.path.item[0].text)).toEqual('string');
                            });
                        });
                        describe("驗證 JSON Data > data > path > item > href", function () {
                            it("要有值", function () {
                                expect(jsonData.data.path.item[0].href).toBeDefined();
                            });
                            it("type須為string", function () {
                                expect(typeof(jsonData.data.path.item[0].href)).toEqual('string');
                            });
                        });
                        describe("驗證 JSON Data > data > path > item > itemDisabled", function () {
                            it("要有值", function () {
                                expect(jsonData.data.path.item[0].itemDisabled).toBeDefined();
                            });
                            it("type須為boolean", function () {
                                expect(typeof(jsonData.data.path.item[0].itemDisabled)).toEqual('boolean');
                            });
                        });
                        describe("驗證 JSON Data > data > path > item > leaf", function () {
                            it("要有值", function () {
                                expect(jsonData.data.path.item[0].leaf).toBeDefined();
                            });
                            it("type須為boolean", function () {
                                expect(typeof(jsonData.data.path.item[0].leaf)).toEqual('boolean');
                            });
                        });
                        describe("驗證 JSON Data > data > path > item > readed", function () {
                            it("要有值", function () {
                                expect(jsonData.data.path.item[0].readed).toBeDefined();
                            });
                            it("type須為boolean", function () {
                                expect(typeof(jsonData.data.path.item[0].readed)).toEqual('boolean');
                            });
                        });
                    });
                });
            });
        });
    });
    describe("課程公告", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=course-announce',
            type: 'GET',
            data: {
                ticket: ticket,
                cid: courseId
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

        it("驗證 JSON Data > data > total_size - type須為number", function () {
            expect(typeof(jsonData.data.total_size)).toEqual('number');
        });

        it("驗證 JSON Data > data > list - 要有值", function () {
            expect(jsonData.data.list).toBeDefined();
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
    describe("課程討論", function () {
        describe("文章列表", function () {
            var response, jsonData;
            jQuery.ajax({
                url: '../index.php?action=get-course-forum',
                type: 'GET',
                data: {
                    ticket: ticket,
                    cid: courseId
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

            it("驗證  JSON Data > data > total_size - type須為number", function () {
                expect(typeof(jsonData.data.total_size)).toEqual('number');
            });

            it("驗證 JSON Data > data > list - 要有值", function () {
                expect(jsonData.data.list).toBeDefined();
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
                describe("驗證 JSON Data > data > list > subject", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].subject).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].subject)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > poster", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].poster).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].poster)).toEqual('string');
                    });
                });
                describe("驗證 JSON Data > data > list > post_time", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].post_time).toBeDefined();
                    });
                    it("type須為string", function () {
                        expect(typeof(jsonData.data.list[0].post_time)).toEqual('string');
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
                describe("驗證 JSON Data > data > list > deletable", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].deletable).toBeDefined();
                    });
                    it("type須為boolean", function () {
                        expect(typeof(jsonData.data.list[0].deletable)).toEqual('boolean');
                    });
                });
                describe("驗證 JSON Data > data > list > attaches", function () {
                    it("要有值", function () {
                        expect(jsonData.data.list[0].attaches).toBeDefined();
                    });
                    if (jsonData.data.list[0].attaches.length > 0) {
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
        describe("張貼文章", function () {
            if (courseId != 0 && (username != 'guest' || username != '')) {
                // 如果有課程編號，且帳號是正常的，則允許測試張貼
                var response, jsonData;
                jQuery.ajax({
                    url: '../index.php?action=add-course-post',
                    type: 'POST',
                    cid: courseId,
                    data: JSON.stringify({
                        subject: "API Test",
                        content: "<p><font color='red'>This is api's web service test.</font></p>",
                        reply_content : '',
                        reply_post_id : '',
                        attaches: [],
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

                describe("驗證回傳資料的各項參數與是否符合API設定的型態", function () {
                    describe("驗證 JSON Data > data > postNodeId", function () {
                        it("要有值", function () {
                            expect(jsonData.data.postNodeId).toBeDefined();
                        });
                        it("type須為string", function () {
                            expect(typeof(jsonData.data.postNodeId)).toEqual('string');
                        });
                        postNodeId = jsonData.data.postNodeId;
                    });
                });
            } else {
                // 否則改測試課程編號與帳號
                it("課程編號錯誤", function () {
                    expect(courseId).not.toEqual(0);
                });
                it("帳號錯誤", function () {
                    expect(username).not.toEqual('');
                    expect(username).not.toEqual('guest');
                });
            }
        });
        describe("閱讀文章", function () {
            if (courseId != 0 && (username != 'guest' || username != '')) {
                // 如果有課程編號，且帳號是正常的，不允許測試張貼，所以也沒得閱讀
                var response, jsonData;
                jQuery.ajax({
                    url: '../index.php?action=set-forum-read',
                    type: 'GET',
                    data: {
                        ticket: ticket,
                        post_id: postNodeId,
                        validWebService: 1
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
            } else {
                // 否則改測試課程編號與帳號
                it("課程編號錯誤", function () {
                    expect(courseId).not.toEqual(0);
                });
                it("帳號錯誤", function () {
                    expect(username).not.toEqual('');
                    expect(username).not.toEqual('guest');
                });
            }
        });
        describe("刪除文章", function () {
            if (courseId != 0 && (username != 'guest' || username != '')) {
                // 如果有課程編號，且帳號是正常的，不允許測試張貼，所以也沒得刪除
                var response, jsonData;
                jQuery.ajax({
                    url: '../index.php?action=del-course-post',
                    type: 'GET',
                    data: {
                        ticket: ticket,
                        post_id: postNodeId,
                        validWebService: 1
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
            } else {
                // 否則改測試課程編號與帳號
                it("課程編號錯誤", function () {
                    expect(courseId).not.toEqual(0);
                });
                it("帳號錯誤", function () {
                    expect(username).not.toEqual('');
                    expect(username).not.toEqual('guest');
                });
            }
        });
    });
    describe("課程測驗", function () {
        var response, jsonData;
        jQuery.ajax({
            url: '../index.php?action=get-course-exam-list',
            type: 'GET',
            data: {
                ticket: ticket,
                cid: courseId
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
            describe("驗證 JSON Data > data > list > score", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].score).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].score)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > passed", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].passed).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].passed)).toEqual('number');
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
            describe("驗證 JSON Data > data > list > passStatus", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].passStatus).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].passStatus)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > thresholdScore", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].thresholdScore).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].thresholdScore)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > intervalTime", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].intervalTime).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].intervalTime)).toEqual('number');
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
            describe("驗證 JSON Data > data > list > pageFlow", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].pageFlow).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].pageFlow)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > examItems", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].examItems).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].examItems)).toEqual('number');
                });
            });
            describe("驗證 JSON Data > data > list > limitTimes", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].limitTimes).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].limitTimes)).toEqual('number');
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
            describe("驗證 JSON Data > data > list > doneTimes", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].doneTimes).toBeDefined();
                });
                it("type須為number", function () {
                    expect(typeof(jsonData.data.list[0].doneTimes)).toEqual('number');
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
            describe("驗證 JSON Data > data > list > support", function () {
                it("要有值", function () {
                    expect(jsonData.data.list[0].support).toBeDefined();
                });
                it("type須為boolean", function () {
                    expect(typeof(jsonData.data.list[0].support)).toEqual('boolean');
                });
            });
        });
    });
});
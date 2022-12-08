var username, password, courseId = 0, ticket, categoryId = '', postNodeId = '', msgId = 0, noteId = 0, folderId = '', teacherCourseId = 0;

// 從 local storage 抓資料
username = localStorage.getItem("unit-test-username");
password = localStorage.getItem("unit-test-password");

describe("測試資料初始化", function () {
    describe("帳號", function () {
        it("型態必須是字串", function () {
            expect(typeof(username)).toEqual('string');
        });
        it("要有值，不為空字串", function () {
            expect(username.trim()).not.toEqual('');
        });
    });
    describe("密碼", function () {
        it("型態必須是字串", function () {
            expect(typeof(password)).toEqual('string');
        });
        it("要有值，不為空字串", function () {
            expect(password.trim()).not.toEqual('');
        });
    });
});

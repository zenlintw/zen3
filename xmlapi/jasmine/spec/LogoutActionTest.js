describe("登出", function () {
    var response, jsonData;

    jQuery.ajax({
        url: '../index.php?action=logout',
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

    it("驗證 Response status - 須為 200", function () {
        expect(response.status).toEqual(200);
    });

    it("驗證 JSON Data - 要有值", function () {
        expect(jsonData).toBeDefined();
    });

    it("驗證 JSON Data Code - 要是0", function () {
        expect(jsonData.code).toEqual(0);
    });

    it("驗證 JSON Data Message - type須為string", function () {
        expect(typeof(jsonData.message)).toEqual('string');
    });

    it("驗證 JSON Data Message - 要有值", function () {
        expect(jsonData.message).toBeDefined();
    });
});
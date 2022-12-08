describe("系統時間", function () {
    var response, jsonData;
    jQuery.ajax({
        url: '../index.php?action=get-server-time',
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

    it("驗證 status - 須為200", function () {
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
        it("驗證 JSON Data > data > server_time - 須為string", function () {
            expect(typeof(jsonData.data.server_time)).toEqual('string');
        });
        it("驗證 JSON Data > data > server_time - 要有值", function () {
            expect(jsonData.data.server_time).toBeDefined();
        });
        it("驗證 JSON Data > data > server_time 格式 - 須為20YY-MM-DD HH:ii:ss", function () {
            expect(jsonData.data.server_time).toMatch(new RegExp('20\\d{2}-[0-1]{1}\\d{1}-[0-3]{1}\\d{1} [0,1]{1}\\d{1}:[0-5]{1}\\d{1}:[0-5]{1}\\d{1}'));
        });
    });
});

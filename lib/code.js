
// 判斷是否含有標點符號 (減號與底線除外)
function isIncludePunct(v){
	var reg = /'([\x21-\x2C\x2E\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x80])'/;
	return reg.test(v);
}

// 判斷字串是否含有『具管道字元的繁體中文字』
function isIncludeBig5Pipe(v){
	var reg = /(\u5F0B|\u56DB|\u5E06|\u5751|\u80B2|\u5C1A|\u6CDC|\u54BD|\u6D31|\u8FE2|\u5F91|\u781D|\u9662|\u60B4|\u740D|\u9016|\u63C9|\u7A05|\u958F|\u6703|\u816E|\u980C|\u6F0F|\u8AA1|\u615D|\u7F75|\u9B6F|\u7CD5|\u5690|\u8209|\u7515|\u7258|\u8FAE|\u758A|\u9E1B)/;
	return reg.test(v);
}

// 判斷字串是否含有『具反斜線字元的繁體中文字』
function isIncludeBig5Bs(v){
	var reg = /(\u4E48|\u529F|\u5412|\u542D|\u6C94|\u577C|\u6B7F|\u4FDE|\u67AF|\u82D2|\u5A09|\u73EE|\u8C79|\u5D24|\u6DDA|\u8A31|\u5EC4|\u7435|\u8DDA|\u6127|\u7A1E|\u923E|\u669D|\u84CB|\u58A6|\u7A40|\u95B1|\u749E|\u9910|\u7E37|\u64FA|\u9EE0|\u5B40|\u9ACF|\u8EA1)/;
	return reg.test(v);
}

// 判斷字串是否為合法帳號
// 規則：首字須為字母，最長 20 chars 最短 4 chars，可含一個底線或減號，但不得在頭尾
function isLegalAccount(v){
	var reg = /^([a-zA-Z][\\w-]{2,18}[a-zA-Z0-9])$/;
	if (reg.test(v)){
		return (v.indexOf('-') == v.lastIndexOf('-') &&
			v.indexOf('_') == v.lastIndexOf('_') )? true : false;
	}
		return false;
}

// 判斷單一個 mail 是否合乎規則
function isIllegalEmail(v){
	var reg = /^\w+(-\w+)*(\.\w+(-\w+)*)*@\w+(-\w+)*(\.\w+(-\w+)*)+$/;
	return !reg.test(v);
}

// 判斷多個 mail 是否合乎規則
function isIllegalEmails(v){
	var reg = /^\w+(-\w+)*(\.\w+(-\w+)*)*@\w+(-\w+)*(\.\w+(-\w+)*)+([ ,;]+\w+(-\w+)*(\.\w+(-\w+)*)*@\w+(-\w+)*(\.\w+(-\w+)*)+)*$/;
	return !reg.test(v);
}

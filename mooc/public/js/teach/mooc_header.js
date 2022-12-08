/* 還未實作教師環境，暫時只將顯示連結部分方法移入 */

/**
 * 顯示教師環境
 * @param {boolean} bol 顯示或隱藏
 */
function showTeachEnv(bol) {
    if (bol) {
        $('.teachEnvDiv').show();
    } else {
        $('.teachEnvDiv').hide();
    }
}

/**
 * 顯示導師環境
 * @param {boolean} bol 顯示或隱藏
 */
function showDirectEnv(bol) {
    if (bol) {
        $('.directEnvDiv').show();
    } else {
        $('.directEnvDiv').hide();
    }
}

/**
 * 顯示管理者環境
 * @param {boolean} bol 顯示或隱藏
 */
function showManagerEnv(bol) {
    if (bol) {
        $('.managerEnvDiv').show();
    } else {
        $('.managerEnvDiv').hide();
    }
}

function PersonalStyle(bol) {
    if (bol) {
        $('.wm-content').removeClass('personal');
    } else {
        $('.wm-content').addClass('personal');
    }
}

function showLinkToLcms(bol) {
    if (bol) {
        $('.linktolcmsDiv').show();
    } else {
        $('.linktolcmsDiv').hide();
    }
}

function linktolcms(){
    if( $('#mycourse-dropdown-menu li').length<=1 ) return; //沒有課程
    var sidcid = $('#mycourse-dropdown-menu li:eq(1) a').data('target');// 必須等到課程區塊都載入完成，才能取到值
    var cid=sidcid.toString().substring(5);
    if ( cid === null || cid === undefined) {
        return;
    }
    document.linktolcmsform.action = "/teach/course/lcms.php?action=resources&cid=" + cid;
    document.linktolcmsform.submit();
}

/**
 * 初始化 MOOC Sysbar
 */
function initSysbar() {

}

$(function () {
    PersonalStyle(false);
    showManagerEnv(true);
    showDirectEnv(true);
    showLinkToLcms(true);
});


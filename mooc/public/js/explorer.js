// $('.nav-adv-search .section-search .title span').text('');
// $('.nav-adv-search .section-search .title .h2').text('');

// 探索課程-點選樹狀結構
$('.link').click(function() {

    // 使文字也有點選效果
    $(this).parent().children('span.treegrid-expander').click();

    // 更換底色
    $('.link').parent().removeClass('active');
    $(this).parent().addClass('active');

    // 顯示路徑
    // 取樹最高深度
    depth = 0;
    $('.tree').find('tr').each(function(){
          if ($(this).treegrid('getDepth') > depth) {
              depth = $(this).treegrid('getDepth');
          }
    });

    // 取目前所在節點
    var obj = $(this).parent().parent('tr');
    var pathName = '';
    // 判斷是否已是第一層
    if (obj.attr('class').indexOf('parent') >= 0) {
        for (i = 1; i <= depth; i = i + 1) {
            if (obj.treegrid('getParentNode') !== null) {
                var parentObj = obj.treegrid('getParentNode').find('.link');
                pathName = '<span class="path" data-id="' + parentObj.prop('id') + '">' + parentObj.text() + '</span>' + '<span class="path-divider">></span>' + pathName;
            }
            obj = obj.treegrid('getParentNode');
        }
    }
    $('.lcms-tree-path').html(pathName + '<span class="path" data-id="' + $(this).prop('id') + '">' + $(this).text() + '</span>');

    // 探索課程-點選路徑
    $('.path').click(function() {
        $('.tree').treegrid('collapseAll');

        // 觸發點選左邊樹選項
        var nowObj = $('#' + $(this).data('id'));
        nowObj.click();

        // 取樹最高深度
        depth = 0;
        $('.tree').find('tr').each(function(){
              if ($(this).treegrid('getDepth') > depth) {
                  depth = $(this).treegrid('getDepth');
              }
        });

        // 往上展開
        // 取目前所在節點
        var obj = nowObj.parent().parent('tr');
        var pathName = '';
        // 判斷是否已是第一層
        if (obj.attr('class').indexOf('parent') >= 0) {
            for (i = 1; i <= depth; i = i + 1) {
                if (obj.treegrid('getParentNode') !== null) {
                    obj.treegrid('getParentNode').treegrid('expand');
                }
                obj = obj.treegrid('getParentNode');
            }
        }
    });

    // 取該類別課程資料
    getCourseList('getTreeCourses', this.id);

});

$(document).ready( function() {

    if (group_id.length === 8) {
        $('#' + group_id).click();

        // 取樹最高深度
        depth = 0;
        $('.tree').find('tr').each(function(){
              if ($(this).treegrid('getDepth') > depth) {
                  depth = $(this).treegrid('getDepth');
              }
        });

        // 往上展開
        // 取目前所在節點
        obj = $('#' + group_id).parent().parent('tr');
        // 判斷是否已是第一層
        if (obj.attr('class').indexOf('parent') >= 0) {
            for (i = 1; i <= depth; i = i + 1) {
                if (obj.treegrid('getParentNode') !== null) {
                    obj.treegrid('getParentNode').treegrid('expand');
                }
                obj = obj.treegrid('getParentNode');
            }
        }
    }

    // 點選 瀏覽方式、學院類別 回根目錄
    $('.course_group_root_node').click(function() {

        // 更換底色
        $('.link').parent().removeClass('active');

        // 顯示路徑
        $('.lcms-tree-path').text(exploreresults);

        // 取所有可報名課程
        getCourseList('getSigningCourses', '');
    });
});

// 課程列表設定參數
$(document).ready(new function() {
  var keyword = $('#search_box_pc').val();
  if ((typeof(keyword) != 'undefined') && (keyword!='')) {
	  getCourseList('getSearchCourses', keyword);
  } else {
      getCourseList('getSigningCourses', 'btnSigning');
  }
});
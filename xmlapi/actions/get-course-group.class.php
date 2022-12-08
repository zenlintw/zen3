<?php
    /**
     * Web Service API - 課程群組
     * 
     * 備註：       
     */
include_once(dirname(__FILE__).'/action.class.php');

class GetCourseGroupAction extends baseAction{

  function main(){
    parent::checkTicket();  // 驗證 Ticket
    global $sysSession;

    $data = array();

    $code = 2;
    switch($_GET['type']){
      case 'all':
        $all_course = dbGetAll("WM_term_group","child,permute","parent = 10000000 order by permute asc");
        foreach((array)$all_course as $key => $value){
          $array_tmp = array();
          $caption   = getCaption(dbGetOne("WM_term_course","caption","course_id = {$all_course[$key]['child']}"));
          $array_tmp['sort'] = $all_course[$key]['permute'];
          $array_tmp['id']   = $all_course[$key]['child'];
          $array_tmp['name'] = htmlspecialchars_decode($caption['Big5']);
          array_push($data,$array_tmp);
        }
        $code = 0;
        break;
      case 'my':
        $array_gid = array();
        $my_course = dbGetCol("WM_term_major","course_id","`username` = '{$sysSession->username}'");
        foreach((array)$my_course as $key => $value){
            $gids = dbGetStMr("WM_term_group","parent","child = {$my_course[$key]}");
            if ($gids) {
                while ($gidItem = $gids->FetchRow()) {
                    $gid = intval($gidItem['parent']);
                    if($gid != 0 && !in_array($gid,$array_gid)){
                        array_push($array_gid,$gid);
                    }
                }
            }
        }

        if (count($array_gid) > 0) {
            $array_gid  = implode(",",$array_gid);
            $all_course = dbGetAll("WM_term_group","child,permute","parent = 10000000 and child in ({$array_gid}) order by permute asc");
            foreach((array)$all_course as $key => $value){
              $array_tmp = array();
              $caption   = getCaption(dbGetOne("WM_term_course","caption","course_id = {$all_course[$key]['child']}"));
              $array_tmp['sort'] = $all_course[$key]['permute'];
              $array_tmp['id']   = $all_course[$key]['child'];
              $array_tmp['name'] = htmlspecialchars_decode($caption['Big5']);
              array_push($data,$array_tmp);
            }
        }
        $code = 0;
        break;
    }

    // make json
    $jsonObj = array(
        'code' => $code,
        'message' => ($code == 0) ? 'success' : 'fail',
        'data' => $data,
    );

    $jsonEncode = JsonUtility::encode($jsonObj);
    
    // output
    header('Content-Type: application/json');
    echo $jsonEncode;
    exit();
  }
}
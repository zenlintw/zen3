<?php
require_once(PATH_LIB . 'JSON.php');

class JsonUtility {

    /**
     * JSON 解碼
     * @param Object $data json object
     * @param Boolean $looseType 是否需要SERVICES_JSON_LOOSE_TYPE
     * @return Array 解碼後的陣列
     **/
    function decode($data, $looseType = true) {
        if(!function_exists('json_decode')) {
            // 如果沒有php延伸的json library，用原先的json library去解碼
            $jsonCode = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            return  $jsonCode->decode($data);
        } else {
            // 有php延伸的json library
            return json_decode($data, $looseType);
        }
    }

    /**
     * JSON 編碼
     * @param array $data json object
     * @return object 編碼後的JSON Object
     **/
    function encode($data) {
        if(!function_exists('json_encode')) {
            // 如果沒有php延伸的json library，用原先的json library去解碼
            $jsonCode = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            return  $jsonCode->encode($data);
        } else {
            // 有php延伸的json library
            return json_encode($data);
        }
    }
}
?>
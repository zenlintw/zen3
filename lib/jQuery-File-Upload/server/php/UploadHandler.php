<?php
/*
 * jQuery File Upload Plugin PHP Class 8.3.3
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
// require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');


if (!defined('ValidRoleCheck')) {
    header('HTTP/1.0 403 Forbidden');exit;
}

if (ValidRoleCheck !== 1){
    header('HTTP/1.0 403 Forbidden');exit;
}

if (!function_exists('file_put_contents')) { 
    // Define flags related to file_put_contents(), if necessary 
    if (!defined('FILE_USE_INCLUDE_PATH')) { 
        define('FILE_USE_INCLUDE_PATH', 1); 
    } 
    if (!defined('FILE_APPEND')) { 
        define('FILE_APPEND', 8); 
    } 

    function file_put_contents($filename, $data, $flags = 0) { 
        // Handle single dimensional array data 
        if (is_array($data)) { 
            // Join the array elements 
            $data = implode('', $data); 
        } 

        // Flags should be an integral value 
        $flags = (int)$flags; 
        // Set the mode for fopen(), defaulting to 'wb' 
        $mode = ($flags & FILE_APPEND) ? 'ab' : 'wb'; 
        $use_include_path = (bool)($flags & FILE_USE_INCLUDE_PATH); 

        // Open file with filename as a string 
        if ($fp = fopen("$filename", $mode, $use_include_path)) { 
            // Acquire exclusive lock if requested 
            if ($flags & LOCK_EX) { 
                if (!flock($fp, LOCK_EX)) { 
                    fclose($fp); 
                    return false; 
                } 
            } 

            // Write the data as a string 
            $bytes = fwrite($fp, "$data"); 

            // Release exclusive lock if it was acquired 
            if ($flags & LOCK_EX) { 
                flock($fp, LOCK_UN); 
            } 

            fclose($fp); 
            return $bytes; // number of bytes written 
        } else { 
            return false; 
        } 
    } 
}  

class UploadHandler
{

    var $options;

    // PHP File Upload error message codes:
    // http://php.net/manual/en/features.file-upload.errors.php
    var $error_messages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'Image exceeds maximum width',
        'min_width' => 'Image requires a minimum width',
        'max_height' => 'Image exceeds maximum height',
        'min_height' => 'Image requires a minimum height',
        'abort' => 'File upload aborted',
        'image_resize' => 'Failed to resize image'
    );

    var $image_objects = array();
    var $chinese_filename = false;  //是否為中文檔名
    
    var $upload_dir;
    var $upload_url;

    function UploadHandler($options = null, $initialize = true, $error_messages = null) {
        global $sysSession, $sysConn;
        $this->response = array();

        $examId = htmlspecialchars($_GET['serial_number']);

        switch(htmlspecialchars($_GET['currPath'])) {
            case 'temp_learn_homework':
                chkSchoolId('WM_qti_homework_test');
                $cid = $sysConn->GetOne("select course_id from WM_qti_homework_test where exam_id = {$examId}");
                $this->upload_dir = sprintf('%s/base/%d/temp/course/%d/homework/A/%d/%s/', sysDocumentRoot, $sysSession->school_id, $cid, htmlspecialchars($_GET['serial_number']), $sysSession->username);
                $this->upload_url = sprintf('/base/%d/temp/course/%d/homework/A/%d/%s/', $sysSession->school_id, $cid, htmlspecialchars($_GET['serial_number']), $sysSession->username);
                break;
            
            case 'temp_learn_questionnaire':
                chkSchoolId('WM_qti_questionnaire_test');
                $cid = $sysConn->GetOne("select course_id from WM_qti_questionnaire_test where exam_id = {$examId}");
                $this->upload_dir = sprintf('%s/base/%d/temp/course/%d/questionnaire/A/%d/%s/', sysDocumentRoot, $sysSession->school_id, $cid, htmlspecialchars($_GET['serial_number']), $sysSession->username);
                $this->upload_url = sprintf('/base/%d/temp/course/%d/questionnaire/A/%d/%s/', $sysSession->school_id, $cid, htmlspecialchars($_GET['serial_number']), $sysSession->username);
                break; 
            
            default:
                $this->upload_dir = sprintf('%s/base/%d/course/%d/content/', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id);
                $this->upload_url = sprintf('/base/%d/course/%d/content/', $sysSession->school_id, $sysSession->course_id);
                break;
        }
        
        $this->options = array(
            'script_url' => $this->get_full_url().'/'.basename($this->get_server_var('SCRIPT_NAME')),
            //TODO
            // 'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/files/',
            'upload_dir' => $this->upload_dir,
            // 'upload_url' => $this->get_full_url().'/files/',
            'upload_url' => $this->get_full_url() . $this->upload_url,
            'user_dirs' => true,
            'mkdir_mode' => 0755,
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
            // Enable to provide file downloads via GET requests to the PHP script:
            //     1. Set to 1 to download files via readfile method through PHP
            //     2. Set to 2 to send a X-Sendfile header for lighttpd/Apache
            //     3. Set to 3 to send a X-Accel-Redirect header for nginx
            // If set to 2 or 3, adjust the upload_url option to the base path of
            // the redirect parameter, e.g. '/files/'.
            'download_via_php' => false,
            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            'readfile_chunk_size' => 10 * 1024 * 1024, // 10 MiB
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Defines which files are handled as image files:
            'image_file_types' => '',
            // Use exif_imagetype on all files to correct file extensions:
            'correct_image_extensions' => false,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => false,
            // Set to 0 to use the GD library to scale and orient images,
            // set to 1 to use imagick (if installed, falls back to GD),
            // set to 2 to use the ImageMagick convert binary directly:
            'image_library' => 1,
            // Uncomment the following to define an array of resource limits
            // for imagick:
            /*
            'imagick_resource_limits' => array(
                RESOURCETYPE_MAP => 32,
                RESOURCETYPE_MEMORY => 32
            ),
            */
            // Command or path for to the ImageMagick convert binary:
            'convert_bin' => 'convert',
            // Uncomment the following to add parameters in front of each
            // ImageMagick convert call (the limit constraints seem only
            // to have an effect if put in front):
            /*
            'convert_params' => '-limit memory 32MiB -limit map 32MiB',
            */
            // Command or path for to the ImageMagick identify binary:
            'identify_bin' => 'identify',
            'image_versions' => array(
                // The empty image version key defines options for the original image:
                '' => array(
                    // Automatically rotate images based on EXIF meta data:
                    'auto_orient' => true
                ),
                'thumbnail' => array(
                    'max_width' => 80,
                    'max_height' => 80
                )
            ),
            'print_response' => true,

            // 是否允許檔案被覆蓋
            'allow_file_override' => (($_GET['fileOverride']==1)?true:false)
        );

        if ($options) {
            $this->options = $options + $this->options;
        }
        if ($error_messages) {
            $this->error_messages = $error_messages + $this->error_messages;
        }
        if ($initialize) {
            $this->initialize();
        }
    }

    function initialize() {
        switch ($this->get_server_var('REQUEST_METHOD')) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->get($this->options['print_response']);
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post($this->options['print_response']);
                break;
            case 'DELETE':
                $this->delete($this->options['print_response']);
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0 ||
            !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    function get_user_id() {
        @session_start();
        return session_id();
    }

    function get_user_path() {
        global $_GET;
        if (($this->options['user_dirs']) && (isset($_GET['currPath']))) {
            global $sysSession;
            if (($_GET['currPath'] === 'temp_learn_homework'||$_GET['currPath'] === 'temp_learn_questionnaire') && is_dir(sprintf('%s/base/%d/temp/course/%d/homework/A/%d/%s/', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id, htmlspecialchars($_GET['serial_number']), $sysSession->username)) === false) {
                mkdir(sprintf('%s/base/%d/temp/course/%d/homework/A/%d/%s/', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id, htmlspecialchars($_GET['serial_number']), $sysSession->username), 0755, true);
            } 
        
            if ($_GET['currPath'] === 'temp_learn_homework' ||$_GET['currPath'] === 'temp_learn_questionnaire' || $_GET['currPath'] == '/') return '';
//            if (mb_detect_encoding($_GET['currPath']) == 'UTF-8') {
//                return mb_convert_encoding($_GET['currPath'],'Big5','UTF-8');
//            }
            
            // 修正 教材檔案管理上傳到有單號的目錄（Gulliver's Travels）會新建立目錄（Gulliver\\\'s Travels）
            $_GET['currPath'] = str_replace("\'", "'", $_GET['currPath']);
            
            return substr($_GET['currPath'], 1);
        }
        return '';
    }

    function get_upload_path($file_name = null, $version = null) {
        $file_name = $file_name ? $file_name : '';
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_path = $version.'/';
        }
        return $this->options['upload_dir'].$this->get_user_path() . $version_path . $file_name;
    }

    function get_query_separator($url) {
        return strpos($url, '?') === false ? '?' : '&';
    }

    function get_download_url($file_name, $version = null, $direct = false) {
        if (!$direct && $this->options['download_via_php']) {
            $url = $this->options['script_url']
                .$this->get_query_separator($this->options['script_url'])
                .$this->get_singular_param_name()
                .'='.rawurlencode($file_name);
            if ($version) {
                $url .= '&version='.rawurlencode($version);
            }
            return $url.'&download=1';
        }
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_url = @$this->options['image_versions'][$version]['upload_url'];
            if ($version_url) {
                return $version_url.$this->get_user_path().rawurlencode($file_name);
            }
            $version_path = rawurlencode($version).'/';
        }
        return $this->options['upload_url'].$this->get_user_path()
            .$version_path.rawurlencode($file_name);
    }

    function set_additional_file_properties($file) {
        $file->deleteUrl = $this->options['script_url']
            .$this->get_query_separator($this->options['script_url'])
            .$this->get_singular_param_name()
            .'='.rawurlencode($file->name);
        $file->deleteType = $this->options['delete_type'];
        if ($file->deleteType !== 'DELETE') {
            $file->deleteUrl .= '&_method=DELETE';
        }
        if ($this->options['access_control_allow_credentials']) {
            $file->deleteWithCredentials = true;
        }
    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    function fix_integer_overflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    function get_file_size($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        return $this->fix_integer_overflow(filesize($file_path));
    }

    function is_valid_file_object(&$file_name) {
        $file_path = $this->get_upload_path($file_name);
        if (is_file($file_path) && $file_name[0] !== '.') {
            return true;
        }
        
//        //中文檔名要轉Big5
//        $file_path = $this->get_upload_path(mb_convert_encoding($file_name,'Big5','UTF-8'));
//        if (is_file($file_path) && $file_name[0] !== '.') {
//            $file_name = mb_convert_encoding($file_name,'Big5','UTF-8');
//            return true;
//        }
        
        return false;
    }

    function get_file_object($file_name) {
        $origFilename = $file_name;
        if ($this->is_valid_file_object($file_name)) {
            $file = new stdClass();
            //中文檔名可能會被is_valid_file_object()函式中被置換過為big5編碼
            $file->name = $origFilename;
            $file->size = $this->get_file_size(
                $this->get_upload_path($file_name)
            );
            $file->url = $this->get_download_url($file->name);
            foreach($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    if (is_file($this->get_upload_path($file_name, $version))) {
                        $file->{$version.'Url'} = $this->get_download_url(
                            $file->name,
                            $version
                        );
                    }
                }
            }
            $this->set_additional_file_properties($file);
            return $file;
        }
        return null;
    }

    function get_file_objects($iteration_method = 'get_file_object') {
        $upload_dir = $this->get_upload_path();
        if (!is_dir($upload_dir)) {
            return array();
        }
        return array_values(array_filter(array_map(
            array($this, $iteration_method),
            scandir($upload_dir)
        )));
    }

    function count_file_objects() {
        return count($this->get_file_objects('is_valid_file_object'));
    }

    function get_error_message($error) {
        return isset($this->error_messages[$error]) ?
            $this->error_messages[$error] : $error;
    }

    function get_config_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fix_integer_overflow($val);
    }

    function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(
            (int)$this->get_server_var('CONTENT_LENGTH')
        );
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            $file->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files']) &&
                ($this->count_file_objects() >= $this->options['max_number_of_files']) &&
                // Ignore additional chunks of existing files:
                !is_file($this->get_upload_path($file->name))) {
            $file->error = $this->get_error_message('max_number_of_files');
            return false;
        }
        return true;
    }

    function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? ((int)$matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    function get_unique_filename($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        if (is_null($content_range)&&($this->options['allow_file_override'])) return $name;
        while(is_dir($this->get_upload_path($name))) {
            $name = $this->upcount_name($name);
        }
        // Keep an existing filename if this is part of a chunked upload:
        $uploaded_bytes = $this->fix_integer_overflow((int)$content_range[1]);

        if (($uploaded_bytes == 0)&&(is_file($this->get_upload_path($name)))){
            unlink($this->get_upload_path($name));
        }

        while(is_file($this->get_upload_path($name))) {
            if ($uploaded_bytes === $this->get_file_size(
                    $this->get_upload_path($name))) {
                break;
            }
            $name = $this->upcount_name($name);
        }
        return $name;
    }

    function fix_file_extension($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        // Add missing file extension for known image types:
        if (strpos($name, '.') === false &&
                preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $name .= '.'.$matches[1];
        }
        if ($this->options['correct_image_extensions'] &&
                function_exists('exif_imagetype')) {
            switch(@exif_imagetype($file_path)){
                case IMAGETYPE_JPEG:
                    $extensions = array('jpg', 'jpeg');
                    break;
                case IMAGETYPE_PNG:
                    $extensions = array('png');
                    break;
                case IMAGETYPE_GIF:
                    $extensions = array('gif');
                    break;
            }
            // Adjust incorrect image file extensions:
            if (!empty($extensions)) {
                $parts = explode('.', $name);
                $extIndex = count($parts) - 1;
                $ext = strtolower(@$parts[$extIndex]);
                if (!in_array($ext, $extensions)) {
                    $parts[$extIndex] = $extensions[0];
                    $name = implode('.', $parts);
                }
            }
        }
        return $name;
    }

    function trim_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        return $name;
    }

    function get_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        $name = $this->trim_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range);
        return $this->get_unique_filename(
            $file_path,
            $this->fix_file_extension($file_path, $name, $size, $type, $error,
                $index, $content_range),
            $size,
            $type,
            $error,
            $index,
            $content_range
        );
    }

    function handle_file_upload($uploaded_file, $name, $size, $type, $error,
            $index = null, $content_range = null) {
        
        $file = new stdClass();
        $file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error,
            $index, $content_range);
        $file->size = $this->fix_integer_overflow((int)$size);
        $file->type = $type;
        if ($this->validate($uploaded_file, $file, $error, $index)) {
            $this->handle_form_data($file, $index);
            $upload_dir = $this->get_upload_path();
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, $this->options['mkdir_mode'], true);
            }
            $file_path = $this->get_upload_path($file->name);
            $append_file = $content_range && is_file($file_path) &&
                $file->size > $this->get_file_size($file_path);
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    $fpr = fopen($uploaded_file, "r");
                    $newContent = fread($fpr, filesize($uploaded_file));
                    fclose($fpr);
                    
                    $fp = fopen($file_path, 'ab');
                    if ($fp) {
                        $bytes = fwrite($fp, $newContent);
                    }
                    fclose($fp);
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = $this->get_file_size($file_path, $append_file);
            if ($file_size === $file->size) {
                $file->url = $this->get_download_url($file->name);
            } else {
                $file->size = $file_size;
                if (!$content_range && $this->options['discard_aborted_uploads']) {
                    unlink($file_path);
                    $file->error = $this->get_error_message('abort');
                }
            }
            $this->set_additional_file_properties($file);
        }
        return $file;
    }

    function readfile($file_path) {
        $file_size = $this->get_file_size($file_path);
        $chunk_size = $this->options['readfile_chunk_size'];
        if ($chunk_size && $file_size > $chunk_size) {
            $handle = fopen($file_path, 'rb');
            while (!feof($handle)) {
                echo fread($handle, $chunk_size);
                @ob_flush();
                @flush();
            }
            fclose($handle);
            return $file_size;
        }
        return readfile($file_path);
    }

    function body($str) {
        echo $str;
    }
    
    function header($str) {
        header($str);
    }

    function get_upload_data($id) {
        return @$_FILES[$id];
    }

    function get_query_param($id) {
        return @$_GET[$id];
    }

    function get_server_var($id) {
        return @$_SERVER[$id];
    }

    function handle_form_data($file, $index) {
        // Handle form data, e.g. $_POST['description'][$index]
        $isMobile = isMobileBrowser() ? '1' : '0';
        if ($isMobile && isset($_POST['new'])) {
            $file->name = $_POST['new'];
        }
    }

    function generate_response($content, $print_response = true) {
        $this->response = $content;
        if ($print_response) {
            $json = json_encode($content);
            $redirect = stripslashes($this->get_query_param('redirect'));
            if ($redirect) {
                $this->header('Location: '.sprintf($redirect, rawurlencode($json)));
                return;
            }
            $this->head();
            if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
                $files = isset($content[$this->options['param_name']]) ?
                    $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-'.(
                        $this->fix_integer_overflow((int)$files[0]->size) - 1
                    ));
                }
            }
            $this->body($json);
        }
        return $content;
    }

    function get_version_param() {
        return basename(stripslashes($this->get_query_param('version')));
    }

    function get_singular_param_name() {
        return substr($this->options['param_name'], 0, -1);
    }

    function get_file_name_param() {
        $name = $this->get_singular_param_name();
        return basename(stripslashes($this->get_query_param($name)));
    }

    function get_file_names_params() {
        $params = $this->get_query_param($this->options['param_name']);
        if (!$params) {
            return null;
        }
        foreach ($params as $key => $value) {
            $params[$key] = basename(stripslashes($value));
        }
        return $params;
    }

    function get_file_type($file_path) {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    function download() {
        switch ($this->options['download_via_php']) {
            case 1:
                $redirect_header = null;
                break;
            case 2:
                $redirect_header = 'X-Sendfile';
                break;
            case 3:
                $redirect_header = 'X-Accel-Redirect';
                break;
            default:
                return $this->header('HTTP/1.1 403 Forbidden');
        }
        $file_name = $this->get_file_name_param();
        if (!$this->is_valid_file_object($file_name)) {
            return $this->header('HTTP/1.1 404 Not Found');
        }
        if ($redirect_header) {
            return $this->header(
                $redirect_header.': '.$this->get_download_url(
                    $file_name,
                    $this->get_version_param(),
                    true
                )
            );
        }
        $file_path = $this->get_upload_path($file_name, $this->get_version_param());
        // Prevent browsers from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if (!preg_match($this->options['inline_file_types'], $file_name)) {
            $this->header('Content-Type: application/octet-stream');
            $this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
        } else {
            $this->header('Content-Type: '.$this->get_file_type($file_path));
            $this->header('Content-Disposition: inline; filename="'.$file_name.'"');
        }
        $this->header('Content-Length: '.$this->get_file_size($file_path));
        $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
        $this->readfile($file_path);
    }

    function send_content_type_header() {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    function send_access_control_headers() {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    function get_response () {
        return $this->response;
    }

    function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    function get($print_response = true) {
        $file_name = $this->get_file_name_param();
        if ($file_name) {
            $response = array(
                $this->get_singular_param_name() => $this->get_file_object($file_name)
            );
        } else {
            $response = array(
                $this->options['param_name'] => $this->get_file_objects()
            );
        }
        return $this->generate_response($response, $print_response);
    }

    function post($print_response = true) {
        $upload = $this->get_upload_data($this->options['param_name']);
        // Parse the Content-Disposition header, if available:
        $content_disposition_header = $this->get_server_var('HTTP_CONTENT_DISPOSITION');
        $file_name = $content_disposition_header ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $content_disposition_header
            )) : null;
//        if ($file_name) {
//            $file_name = mb_convert_encoding($file_name,'Big5','UTF-8');
//        }
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range_header = $this->get_server_var('HTTP_CONTENT_RANGE');
        $content_range = $content_range_header ?
            preg_split('/[^0-9]+/', $content_range_header) : null;
        $size =  $content_range ? $content_range[3] : null;
        $files = array();
        if ($upload) {
            if (is_array($upload['tmp_name'])) {
                // param_name is an array identifier like "files[]",
                // $upload is a multi-dimensional array:
                foreach ($upload['tmp_name'] as $index => $value) {
                    $files[] = $this->handle_file_upload(
                        $upload['tmp_name'][$index],
//                        $file_name ? $file_name : mb_convert_encoding($upload['name'][$index],'Big5','UTF-8'),
                        $file_name ? $file_name : $upload['name'][$index],
                        $size ? $size : $upload['size'][$index],
                        $upload['type'][$index],
                        $upload['error'][$index],
                        $index,
                        $content_range
                    );
                }
            } else {
                // param_name is a single object identifier like "file",
                // $upload is a one-dimensional array:
                $files[] = $this->handle_file_upload(
                    isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                    $file_name ? $file_name : (isset($upload['name']) ?
//                            mb_convert_encoding($upload['name'],'Big5','UTF-8') : null),
                            $upload['name'] : null),
                    $size ? $size : (isset($upload['size']) ?
                            $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
                    isset($upload['type']) ?
                            $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
                    isset($upload['error']) ? $upload['error'] : null,
                    null,
                    $content_range
                );
            }
        }
        $response = array($this->options['param_name'] => $files);
        return $this->generate_response($response, $print_response);
    }

    function delete($print_response = true) {
        $file_name = $this->get_file_name_param();
        $file_path = $this->get_upload_path($file_name);
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        
        return $this->generate_response(array('success' => $success), $print_response);
    }
}
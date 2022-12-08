<?php
/*
-------------------------------------------------------------------------------
webdav_client v0.1, a php based webdav client class.

Copyright (C) 2003 Christian Juerges

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

-------------------------------------------------------------------------------
PHP Class:      webdav_client
Implements:     a basic webdav client. 
                Trying to follow the rfc 2518 rules.
Author:         Christian Juerges (christian.juerges@xwave.ch)
Contact:        Xwave GmbH, Josefstr. 92; 8005 Zuerich, Switzerland
------------------------------------------------------------------------------- 

$Id: class_webdav_client.php,v 1.1 2010-02-24 02:39:33 saly Exp $
$Author: saly $
$Date: 2010-02-24 02:39:33 $
$Revision: 1.1 $

------------------------------------------------------------------------------
*/

class webdav_client {

  var $_debug = false;
  var $_fp; 
  var $_server;
  var $_port = 80;
  var $_path ='/';
  var $_user;
  var $_protocol = 'HTTP/1.0';
  var $_pass;
  var $_socket_timeout = 5;
  var $_errno;
  var $_errstr;
  var $_user_agent = 'php class webdav_client $Revision: 1.1 $';
  var $_crlf = "\r\n";
  var $_req;
  var $_resp_status;
  var $_parser;
  var $_xmltree;
  
  var $_tree;
  var $_ls = array();
  var $_ls_ref;
  var $_ls_ref_cdata;
  
  var $_delete = array();
  var $_delete_ref;
  var $_delete_ref_cdata;
  
  var $_lock = array();
  var $_lock_ref;
  var $_lock_rec_cdata;
  
  
  var $_null = NULL;
  // var $_buffer='';
  var $_header='';
  var $_body='';
  var $_connection_closed = false;
  
  // --------------------------------------------------------------------------
  // constructor
  /* 
  function webdav_client() {
  
  }  */
      
  // --------------------------------------------------------------------------
  // public methods to set a basic environment 
  function set_server($server) {
    $this->_server = $server;
  }
  
  function set_port($port) {
    $this->_port = $port;
  }
  
  function set_user($user) {
    $this->_user = $user;
  }
  
  function set_pass($pass) {
    $this->_pass = $pass;
  }
  
  function set_debug($debug) {
    $this->_debug = $debug;
  }
  
  // public method set_protocol
  // should be HTTP/1.0 or HTTP/1.1 be used ?
  function set_protocol($version) {
    if ($version == 1) {
      $this->_protocol = 'HTTP/1.1';
    } else {
      $this->_protocol = 'HTTP/1.0';
    }
    $this->_error_log('HTTP Protocol was set to ' . $this->_protocol); 
      
  }
  // --------------------------------------------------------------------------
  // convert ISO 8601 Date and Time Profile used in RFC 2518 to unix timestamp 
  function iso8601totime($iso8601) {
    /*
     
     date-time       = full-date "T" full-time
  
     full-date       = date-fullyear "-" date-month "-" date-mday
     full-time       = partial-time time-offset
  
     date-fullyear   = 4DIGIT
     date-month      = 2DIGIT  ; 01-12
     date-mday       = 2DIGIT  ; 01-28, 01-29, 01-30, 01-31 based on
     month/year
     time-hour       = 2DIGIT  ; 00-23
     time-minute     = 2DIGIT  ; 00-59
     time-second     = 2DIGIT  ; 00-59, 00-60 based on leap second rules
     time-secfrac    = "." 1*DIGIT
     time-numoffset  = ("+" / "-") time-hour ":" time-minute
     time-offset     = "Z" / time-numoffset
  
     partial-time    = time-hour ":" time-minute ":" time-second
                      [time-secfrac]
     */

     $regs = array();
     /*         [1]        [2]        [3]        [4]        [5]        [6]  */   
     if (ereg('^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z$', $iso8601, $regs)) {
       return mktime($regs[4],$regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);   
     }
     // to be done: regex for partial-time...apache webdav mod never returns partial-time
     
     return false;
  }
  
  // --------------------------------------------------------------------------
  // public method open
  function open() {
    // let's try to open a socket 
    $this->_error_log('open a socket connection');
    $this->_fp = fsockopen ($this->_server, $this->_port, $this->_errno, $this->_errstr, $this->_socket_timeout);
    // set_time_limit(30);
    socket_set_blocking($this->_fp, true);
    if (!$this->_fp) {
      $this->_error_log("$this->_errstr ($this->_errno)\n");
      return false;
    } else {
      $this->_connection_closed = false;
      $this->_error_log('socket is open: ' . $this->_fp);
      return true;
    } 
  }
  
  // --------------------------------------------------------------------------
  // public method close
  // closes an open socket connection 
  function close() {
    $this->_error_log('closing socket ' . $this->_fp);
    $this->_connection_closed = true;
    fclose($this->_fp);
  }

  // --------------------------------------------------------------------------
  // public method check_webdav
  // checks if server supports webdav methods
  // we only check if server returns a DAV Element in Header and if so
  // if schema 1,2 is supported...
  function check_webdav() {
    $resp = $this->options();
    if (!$resp) {
      return false;
    }
    $this->_error_log($resp['header']['DAV']);
    // check schema
    if (preg_match('/1,2/', $resp['header']['DAV'])) {
      return true;
    } 
    // otherwise return false
    return false;
  }
    
    
  // --------------------------------------------------------------------------
  // public method options
  function options() {
    $this->_header_unset();
    $this->_create_basic_request('OPTIONS');
    $this->_send_request();
    $this->_get_respond();
    $response = $this->_process_respond();     
    // validate the response ... 
    // check http-version
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
       return $response;
    }
    $this->_error_log('Response was not even http');
    return false; 
    
  }
  
  // -------------------------------------------------------------------------- 
  // public method mkdir:
  // creates a new collection/directory on webdav server
  function mkcol($path) {
    // $this->_fp = pfsockopen ($this->_server, $this->_port, $this->_errno, $this->_errstr, $this->_socket_timeout);
    $this->_path = $this->_translate_uri($path);
    $this->_header_unset();
    $this->_create_basic_request('MKCOL');
    $this->_send_request();
    $this->_get_respond();
    $response = $this->_process_respond();
    // validate the response ... 
    // check http-version
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      /* seems to be http ... proceed 
        just return what server gave us 
        rfc 2518 says:
        201 (Created) - The collection or structured resource was created in its entirety.
        403 (Forbidden) - This indicates at least one of two conditions: 1) the server does not allow the creation of collections at the given
                         location in its namespace, or 2) the parent collection of the Request-URI exists but cannot accept members.
        405 (Method Not Allowed) - MKCOL can only be executed on a deleted/non-existent resource.
        409 (Conflict) - A collection cannot be made at the Request-URI until one or more intermediate collections have been created.
        415 (Unsupported Media Type)- The server does not support the request type of the body.
        507 (Insufficient Storage) - The resource does not have sufficient space to record the state of the resource after the execution of this method.
      */  
      return $response['status']['status-code'];
    }    
    
  }
  
  // public method get:
  // gets a file from a webdav collection
  // returns status code and fills on success
  // buffer with the file received from webdav server
  function get($path, &$buffer) {
    $this->_path = $this->_translate_uri($path);
    $this->_header_unset();
    $this->_create_basic_request('GET');    
    $this->_send_request();
    $this->_get_respond();
    $response = $this->_process_respond();
    
    // validate the response 
    // check http-version 
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      // seems to be http ... proceed 
      // We expect a 200 code 
      if ($response['status']['status-code'] == 200 ) {
        $buffer = $response['body'];
      }
      return $response['status']['status-code'];
     } 
     // ups: no http status was returned ?
     return false; 
  }
  
  // --------------------------------------------------------------------------
  // public method put:
  // puts a file into a collection
  // wants data to putted as one chunk
  function put($path, $data ) {
    $this->_path = $this->_translate_uri($path);
    $this->_header_unset();
    $this->_create_basic_request('PUT');    
    // add more needed header information ...
    $this->_header_add('Content-length: ' . strlen($data)); 
    $this->_header_add('Content-type: application/octet-stream');
    // send header 
    $this->_send_request();
    // send the rest (data)
    fputs($this->_fp, $data);
    $this->_get_respond();
    $response = $this->_process_respond();
    
    // validate the response 
    // check http-version 
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      // seems to be http ... proceed 
      // We expect a 200 or 204 status code 
      // see rfc 2068 - 9.6 PUT...
      // print 'http ok<br>';
      return $response['status']['status-code'];
     } 
     // ups: no http status was returned ?
     return false; 
  }
  
  // --------------------------------------------------------------------------
  // public method put_file
  // put a file into a collection
  // wants a filename as param)
  // then puts file chunked to webdav server
  function put_file($path, $filename) {
    // try to open the file ...
    
    
    $handle = fopen ($filename, 'r');
    if ($handle) {
      // $this->_fp = pfsockopen ($this->_server, $this->_port, $this->_errno, $this->_errstr, $this->_socket_timeout);
      $this->_path = $this->_translate_uri($path);
      $this->_header_unset();
      $this->_create_basic_request('PUT'); 
      // add more needed header information ...
      $this->_header_add('Content-length: ' . filesize($filename)); 
      $this->_header_add('Content-type: application/octet-stream');
      // send header 
      $this->_send_request();
      while (!feof($handle)) {
        fputs($this->_fp,fgets($handle,4096));
      }
      fclose($handle);
      $this->_get_respond();
      $response = $this->_process_respond();
    
      // validate the response 
      // check http-version 
      if ($response['status']['http-version'] == 'HTTP/1.1' ||
        $response['status']['http-version'] == 'HTTP/1.0') {
        // seems to be http ... proceed 
        // We expect a 200 or 204 status code 
        // see rfc 2068 - 9.6 PUT...
        // print 'http ok<br>';
        return $response['status']['status-code'];
      }   
      // ups: no http status was returned ?
      return false;   
    } else {
      $this->_error_log('could not open ' . $filename);
      return false;
    }
  
  }

  // public method copy_file
  // copy a file on webdav server 
  function copy_file($src_path, $dst_path, $overwrite) {
   $this->_path = $this->_translate_uri($src_path);
   $this->_header_unset();
   $this->_create_basic_request('COPY');    
   $this->_header_add(sprintf('Destination: http://%s%s', $this->_server, $this->_translate_uri($dst_path)));
   if ($overwrite) {
     $this->_header_add('Overwrite: T');
   } else {
     $this->_header_add('Overwrite: F');
   }
   $this->_header_add(''); 
   $this->_send_request();
   $this->_get_respond();
   $response = $this->_process_respond();
   // validate the response ... 
   // check http-version
   if ($response['status']['http-version'] == 'HTTP/1.1' ||
      $response['status']['http-version'] == 'HTTP/1.0') {
     /* seems to be http ... proceed 
       just return what server gave us (as defined in rfc 2518) :
       201 (Created) - The source resource was successfully copied. The copy operation resulted in the creation of a new resource.
       204 (No Content) - The source resource was successfully copied to a pre-existing destination resource.
       403 (Forbidden) - The source and destination URIs are the same.
       409 (Conflict) - A resource cannot be created at the destination until one or more intermediate collections have been created.
       412 (Precondition Failed) - The server was unable to maintain the liveness of the properties listed in the propertybehavior XML element
           or the Overwrite header is "F" and the state of the destination resource is non-null.
       423 (Locked) - The destination resource was locked.
       502 (Bad Gateway) - This may occur when the destination is on another server and the destination server refuses to accept the resource.
       507 (Insufficient Storage) - The destination resource does not have sufficient space to record the state of the resource after the
           execution of this method.
     */  
     return $response['status']['status-code']; 
   }
   return false;
  }
  
  // public method copy_coll
  // copy a collection on webdav server 
  function copy_coll($src_path, $dst_path, $overwrite) {
   $this->_path = $this->_translate_uri($src_path);
   $this->_header_unset();
   $this->_create_basic_request('COPY');    
   $this->_header_add(sprintf('Destination: http://%s%s', $this->_server, $this->_translate_uri($dst_path)));
   $this->_header_add('Depth: Infinity');
   
   $xml  = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
   $xml .= "<d:propertybehavior xmlns:d=\"DAV:\">\r\n";
   $xml .= "  <d:keepalive>*</d:keepalive>\r\n";
   $xml .= "</d:propertybehavior>\r\n";
   
   $this->_header_add('Content-length: ' . strlen($xml)); 
   $this->_header_add('Content-type: text/xml');
   $this->_send_request();
    // send also xml 
   fputs($this->_fp, $xml);
   $this->_get_respond();
   $response = $this->_process_respond();
   // validate the response ... 
   // check http-version
   if ($response['status']['http-version'] == 'HTTP/1.1' ||
      $response['status']['http-version'] == 'HTTP/1.0') {
     /* seems to be http ... proceed 
       just return what server gave us (as defined in rfc 2518) :
       201 (Created) - The source resource was successfully copied. The copy operation resulted in the creation of a new resource.
       204 (No Content) - The source resource was successfully copied to a pre-existing destination resource.
       403 (Forbidden) - The source and destination URIs are the same.
       409 (Conflict) - A resource cannot be created at the destination until one or more intermediate collections have been created.
       412 (Precondition Failed) - The server was unable to maintain the liveness of the properties listed in the propertybehavior XML element
           or the Overwrite header is "F" and the state of the destination resource is non-null.
       423 (Locked) - The destination resource was locked.
       502 (Bad Gateway) - This may occur when the destination is on another server and the destination server refuses to accept the resource.
       507 (Insufficient Storage) - The destination resource does not have sufficient space to record the state of the resource after the
           execution of this method.
     */  
     return $response['status']['status-code']; 
   }
   return false;
  }
  
  // --------------------------------------------------------------------------
  // public method move
  // move/rename a file/collection on webdav server
  function move($src_path,$dst_path, $overwrite) {
    
    $this->_path = $this->_translate_uri($src_path);
    $this->_header_unset();
    $this->_create_basic_request('MOVE');    
    $this->_header_add(sprintf('Destination: http://%s%s', $this->_server, $this->_translate_uri($dst_path)));
    if ($overwrite) {
      $this->_header_add('Overwrite: T');
    } else {
      $this->_header_add('Overwrite: F');
    }
    $this->_header_add(''); 
    $this->_send_request();
    $this->_get_respond();
    $response = $this->_process_respond();
    // validate the response ... 
    // check http-version
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      /* seems to be http ... proceed 
        just return what server gave us (as defined in rfc 2518) :
        201 (Created) - The source resource was successfully moved, and a new resource was created at the destination.
        204 (No Content) - The source resource was successfully moved to a pre-existing destination resource.
        403 (Forbidden) - The source and destination URIs are the same.
        409 (Conflict) - A resource cannot be created at the destination until one or more intermediate collections have been created.
        412 (Precondition Failed) - The server was unable to maintain the liveness of the properties listed in the propertybehavior XML element
             or the Overwrite header is "F" and the state of the destination resource is non-null.
        423 (Locked) - The source or the destination resource was locked.
        502 (Bad Gateway) - This may occur when the destination is on another server and the destination server refuses to accept the resource.  
        
        201 (Created) - The collection or structured resource was created in its entirety.
        403 (Forbidden) - This indicates at least one of two conditions: 1) the server does not allow the creation of collections at the given
                         location in its namespace, or 2) the parent collection of the Request-URI exists but cannot accept members.
        405 (Method Not Allowed) - MKCOL can only be executed on a deleted/non-existent resource.
        409 (Conflict) - A collection cannot be made at the Request-URI until one or more intermediate collections have been created.
        415 (Unsupported Media Type)- The server does not support the request type of the body.
        507 (Insufficient Storage) - The resource does not have sufficient space to record the state of the resource after the execution of this method.
      */  
      return $response['status']['status-code']; 
    }
    return false;
  }
  
  // --------------------------------------------------------------------------
  // public method lock
  // locks a webdav resource
  function lock($path) {
    $this->_path = $this->_translate_uri($path);
    $this->_header_unset();
    $this->_create_basic_request('LOCK');    
    $this->_header_add('Timeout: Infinite');
    $this->_header_add('Content-type: text/xml');
    // create the xml request ...
    $xml =  "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
    $xml .= "<D:lockinfo xmlns:D='DAV:'\r\n>";
    $xml .= "  <D:lockscope><D:exclusive/></D:lockscope>\r\n";
    $xml .= "  <D:locktype><D:write/></D:locktype>\r\n";
    $xml .= "  <D:owner>\r\n";
    $xml .= "    <D:href>chris</D:href>\r\n";
    $xml .= "  </D:owner>\r\n";
    $xml .= "</D:lockinfo>\r\n";
    $this->_header_add('Content-length: ' . strlen($xml)); 
    $this->_send_request();
    // send also xml 
    fputs($this->_fp, $xml);
    $this->_get_respond();
    $response = $this->_process_respond();
    // validate the response ... (only basic validation)
    // check http-version
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      /* seems to be http ... proceed 
      rfc 2518 says: 
      200 (OK) - The lock request succeeded and the value of the lockdiscovery property is included in the body.
      412 (Precondition Failed) - The included lock token was not enforceable on this resource or the server could not satisfy the
           request in the lockinfo XML element.
      423 (Locked) - The resource is locked, so the method has been rejected.
      */
     
      switch($response['status']['status-code']) {
        case 200:
          // collection was successfully locked... see xml response to get lock token...
          if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
            // ok let's get the content of the xml stuff
            $this->_parser = xml_parser_create_ns();
            // forget old data...
            unset($this->_lock[$this->_parser]);
            unset($this->_xmltree[$this->_parser]);
            xml_parser_set_option($this->_parser,XML_OPTION_SKIP_WHITE,0);
            xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,0);
            xml_set_object($this->_parser, $this);
            xml_set_element_handler($this->_parser, "_lock_startElement", "_endElement");
            xml_set_character_data_handler($this->_parser, "_lock_cdata"); 
                    
            if (!xml_parse($this->_parser, $response['body'])) {
              die(sprintf("XML error: %s at line %d",
                           xml_error_string(xml_get_error_code($this->_parser)),
                           xml_get_current_line_number($this->_parser)));
            } 
          
            // Free resources 
            xml_parser_free($this->_parser); 
            // add status code to array
            $this->_lock[$this->_parser]['status'] = 200;
            return $this->_lock[$this->_parser];
                
          } else {
            print 'Missing Content-Type: text/xml header in response.<br>';
          }
          return false;
          
        default:
          // collection or file was successfully deleted 
          $this->_lock['status'] = $response['status']['status-code'];
          return $this->_lock;
      }
    }  
      
    
  }
  
  
  // --------------------------------------------------------------------------
  // public method unlock
  // unlocks a locked resource
  function unlock($path, $locktoken) {
    $this->_path = $this->_translate_uri($path);
    $this->_header_unset();
    $this->_create_basic_request('UNLOCK');    
    $this->_header_add(sprintf('Lock-Token: <%s>', $locktoken));
    $this->_send_request();
    $this->_get_respond();
    $response = $this->_process_respond();
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      /* seems to be http ... proceed 
      rfc 2518 says: 
      204 (OK) - The 204 (No Content) status code is used instead of 200 (OK) because there is no response entity body.
      */
      return $response['status']['status-code'];
     } 
    return false;
  }
  
  // --------------------------------------------------------------------------
  // public method delete:
  // deletes a collection/directory on a webdav server
  function delete($path) {
    $this->_path = $this->_translate_uri($path);
    $this->_header_unset();
    $this->_create_basic_request('DELETE');
    /* $this->_header_add('Content-Length: 0'); */
    $this->_header_add('');
    $this->_send_request();
    $this->_get_respond();
    $response = $this->_process_respond();
        
    // validate the response ... 
    // check http-version
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      // seems to be http ... proceed 
      // We expect a 207 Multi-Status status code 
      // print 'http ok<br>';
      
      switch ($response['status']['status-code']) {
        case 207:
          // collection was NOT deleted... see xml response for reason...
          // next there should be a Content-Type: text/xml; charset="utf-8" header line
          if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
            // ok let's get the content of the xml stuff
            $this->_parser = xml_parser_create_ns();
            // forget old data...
            unset($this->_delete[$this->_parser]);
            unset($this->_xmltree[$this->_parser]);
            xml_parser_set_option($this->_parser,XML_OPTION_SKIP_WHITE,0);
            xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,0);
            xml_set_object($this->_parser, $this);
            xml_set_element_handler($this->_parser, "_delete_startElement", "_endElement");
            xml_set_character_data_handler($this->_parser, "_delete_cdata"); 
                    
            if (!xml_parse($this->_parser, $response['body'])) {
              die(sprintf("XML error: %s at line %d",
                           xml_error_string(xml_get_error_code($this->_parser)),
                           xml_get_current_line_number($this->_parser)));
            } 
          
            print_r($this->_delete[$this->_parser]);
            print "<br>";
          
            // Free resources 
            xml_parser_free($this->_parser); 
            $this->_delete[$this->_parser]['status'] = $response['status']['status-code'];
            return $this->_delete[$this->_parser];
                
          } else {
            print 'Missing Content-Type: text/xml header in response.<br>';
          }
          return false;
          
        default:
          // collection or file was successfully deleted 
          $this->_delete['status'] = $response['status']['status-code'];
          return $this->_delete;
           
          
      }   
    }  
    
  }
  
  // --------------------------------------------------------------------------
  // public method ls:
  // Get's directory information from webdav server in flat array using PROPFIND
  function ls($path) {
    $this->_path = $this->_translate_uri($path);
    
    $this->_header_unset();
    $this->_create_basic_request('PROPFIND');
    $this->_header_add('Depth: 1');
    $this->_header_add('Content-type: text/xml');
    // create profind xml request...
    $xml  = "<?xml version=\"1.0\"?>\r\n";
    $xml .= "<A:propfind xmlns:A=\"DAV:\">\r\n";
    // shall we get all properties ?
    $xml .= "    <A:allprop/>\r\n";
    // or should we better get only wanted props ?
    $xml .= "</A:propfind>\r\n";
    $this->_header_add('Content-length: ' . strlen($xml)); 
    $this->_send_request();
    $this->_error_log($xml);
    fputs($this->_fp, $xml);
    $this->_get_respond();
    $response = $this->_process_respond();
    // validate the response ... (only basic validation)
    // check http-version
    if ($response['status']['http-version'] == 'HTTP/1.1' ||
       $response['status']['http-version'] == 'HTTP/1.0') {
      // seems to be http ... proceed 
      // We expect a 207 Multi-Status status code 
      // print 'http ok<br>';
      if (strcmp($response['status']['status-code'],'207') == 0 ) {
        // ok so far 
        // next there should be a Content-Type: text/xml; charset="utf-8" header line
        if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
          // ok let's get the content of the xml stuff
          $this->_parser = xml_parser_create_ns();
          // forget old data...
          unset($this->_ls[$this->_parser]);
          unset($this->_xmltree[$this->_parser]);
          xml_parser_set_option($this->_parser,XML_OPTION_SKIP_WHITE,0);
          xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,0);
          xml_set_object($this->_parser, $this);
          xml_set_element_handler($this->_parser, "_propfind_startElement", "_endElement");
          xml_set_character_data_handler($this->_parser, "_propfind_cdata"); 
          
          
          if (!xml_parse($this->_parser, $response['body'])) {
            die(sprintf("XML error: %s at line %d",
                         xml_error_string(xml_get_error_code($this->_parser)),
                         xml_get_current_line_number($this->_parser)));
          } 
          
          // Free resources 
          xml_parser_free($this->_parser); 
          return $this->_ls[$this->_parser];    
        } else {
          $this->_error_log('Missing Content-Type: text/xml header in response!!');
          return false;
        }
      }
    } 
    
    // response was not http 
    $this->_error_log('Ups in method ls: error in response from server');
    return false; 
        
    
  }

  // --------------------------------------------------------------------------
  // private xml callback and helper functions starting here 
  // --------------------------------------------------------------------------
  // private propfind xml callbacks 
  // generic endElement (used for all xml callbacks)
  function _endElement($parser, $name) {
      $this->_xmltree[$parser] = substr($this->_xmltree[$parser],0, strlen($this->_xmltree[$parser]) - (strlen($name) + 1));
  } 
  
  // --------------------------------------------------------------------------
  // private xml callback _propfind_startElement
  function _propfind_startElement($parser, $name, $attrs) {
    // lower XML Names... maybe break a RFC, don't know ...
    
    $propname = strtolower($name);
    $this->_xmltree[$parser] .= $propname . '_';
    /* foreach($attrs as $attr) {
      // print "attr: " . htmlentities($attr); 
    } */
    
    // translate xml tree to a flat array ...
    switch($this->_xmltree[$parser]) {
      case 'dav::multistatus_dav::response_':
        // new element in mu
        $this->_ls_ref =& $this->_ls[$parser][];
        break;
      case 'dav::multistatus_dav::response_dav::href_':
        $this->_ls_ref_cdata = &$this->_ls_ref['href'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::creationdate_':
        $this->_ls_ref_cdata = &$this->_ls_ref['creationdate'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getlastmodified_':
        $this->_ls_ref_cdata = &$this->_ls_ref['lastmodified'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getcontenttype_':
        $this->_ls_ref_cdata = &$this->_ls_ref['getcontenttype'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getcontentlength_':
        $this->_ls_ref_cdata = &$this->_ls_ref['getcontentlength'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_':
        $this->_ls_ref_cdata = &$this->_ls_ref['activelock_depth'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_':
        $this->_ls_ref_cdata = &$this->_ls_ref['activelock_owner'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_':
        $this->_ls_ref_cdata = &$this->_ls_ref['activelock_owner'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_':
        $this->_ls_ref_cdata = &$this->_ls_ref['activelock_timeout'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_':
        $this->_ls_ref_cdata = &$this->_ls_ref['activelock_token'];
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::locktype_dav::write_':
        $this->_ls_ref_cdata = &$this->_ls_ref['activelock_type'];
        $this->_ls_ref_cdata = 'write';
        $this->_ls_ref_cdata = &$this->_null;
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::resourcetype_dav::collection_':
        $this->_ls_ref_cdata = &$this->_ls_ref['resourcetype'];
        $this->_ls_ref_cdata = 'collection';
        $this->_ls_ref_cdata = &$this->_null;
        break;
      case 'dav::multistatus_dav::response_dav::propstat_dav::status_':
        $this->_ls_ref_cdata = &$this->_ls_ref['status'];
        break;
      
      default:
       // handle unknown xml elements...
       $this->_ls_ref_cdata = &$this->_ls_ref[$this->_xmltree[$parser]];
       // print  $this->_xmltree[$parser] . '<br>';
    }  
  }  
   
  // private method _propfind_cData
  function _propfind_cData($parser, $cdata) {
    if (trim($cdata) <> '') {
      // print htmlentities($this->_xmltree[$parser]) . '='. htmlentities($cdata) . '<br>';
      $this->_ls_ref_cdata = $cdata;
    } else {
      // do nothing
    }
  }
  
  //----------------------------------------------------------------------------
  // private xml callback for delete  
  function _delete_startElement($parser, $name, $attrs) {
    // lower XML Names... maybe break a RFC, don't know ...
    $propname = strtolower($name);
    $this->_xmltree[$parser] .= $propname . '_';
    /* foreach($attrs as $attr) {
      // print "attr: " . htmlentities($attr); 
    } */
    
    // translate xml tree to a flat array ...
    
    switch($this->_xmltree[$parser]) {
      case 'dav::multistatus_dav::response_':
        // new element in mu
        $this->_delete_ref =& $this->_delete[$parser][];
        break;
      case 'dav::multistatus_dav::response_dav::href_':
        $this->_delete_ref_cdata = &$this->_ls_ref['href'];
        break;
      
      default:
       // handle unknown xml elements...
       $this->_delete_cdata = &$this->_delete_ref[$this->_xmltree[$parser]];
       // print  $this->_xmltree[$parser] . '<br>';
    }  
  }  

  function _delete_cData($parser, $cdata) {
    if (trim($cdata) <> '') {
      // print htmlentities($this->_xmltree[$parser]) . '='. htmlentities($cdata) . '<br>';
      $this->_delete_ref_cdata = $cdata;
    } else {
      // do nothing
    }
  }
  
  //----------------------------------------------------------------------------
  // callback for lock method xml response 
  function _lock_startElement($parser, $name, $attrs) {
    // lower XML Names... maybe break a RFC, don't know ...
    $propname = strtolower($name);
    $this->_xmltree[$parser] .= $propname . '_';
    /* foreach($attrs as $attr) {
      // print "attr: " . htmlentities($attr); 
    } */
    
    // translate xml tree to a flat array ...
    /*
    dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_=
    dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_=
    dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_=
    dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_=
    */
    switch($this->_xmltree[$parser]) {
      case 'dav::prop_dav::lockdiscovery_dav::activelock_':
        // new element
        $this->_lock_ref =& $this->_lock[$parser][];
        break;
      case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::locktype_dav::write_':
        $this->_lock_ref_cdata = &$this->_lock_ref['locktype'];
        $this->_lock_cdata = 'write';
        $this->_lock_cdata = &$this->_null;
        break;
      case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::lockscope_dav::exclusive_':
        $this->_lock_ref_cdata = &$this->_lock_ref['lockscope'];
        $this->_lock_ref_cdata = 'exclusive';
        $this->_lock_ref_cdata = &$this->_null;
        break;
      case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_':
        $this->_lock_ref_cdata = &$this->_lock_ref['depth'];
        break;  
      case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_':
        $this->_lock_ref_cdata = &$this->_lock_ref['owner'];
        break;
      case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_':
        $this->_lock_ref_cdata = &$this->_lock_ref['timeout'];
        break;
      case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_':
        $this->_lock_ref_cdata = &$this->_lock_ref['locktoken'];
        break;
      default:
       // handle unknown xml elements...
       $this->_lock_cdata = &$this->_lock_ref[$this->_xmltree[$parser]];
       
    }  
  }  

  function _lock_cData($parser, $cdata) {
    if (trim($cdata) <> '') {
      $this->_error_log(($this->_xmltree[$parser]) . '='. htmlentities($cdata));
      $this->_lock_ref_cdata = $cdata;
    } else {
      // do nothing
    }
  }
  
  //----------------------------------------------------------------------------
  // private method _header_add
  function _header_add($string) {
    $this->_req[] = $string;
  }
  
  // --------------------------------------------------------------------------
  // private method _header_unset
  // unsets array _req
  function _header_unset() {
    unset($this->_req);
  }
  
  // --------------------------------------------------------------------------
  // private methode _create_basic_request
  // generates a minimum request header for all request methods 
  function _create_basic_request($method) {
    $request = '';
    $this->_header_add(sprintf('%s %s %s', $method, $this->_path, $this->_protocol));
    $this->_header_add(sprintf('Host: %s', $this->_server));
    // $request .= sprintf('Connection: Keep-Alive');
    $this->_header_add(sprintf('User-Agent: %s', $this->_user_agent));
    $this->_header_add(sprintf('Authorization: Basic %s', base64_encode("$this->_user:$this->_pass")));
    
  }
  
  // --------------------------------------------------------------------------
  // private methode _send_request
  // sends the client request to the webdav server 
  function _send_request() {
    // check if stream is declared to be open
    // only logical check we are not sure if socket is really still open ...
    if ($this->_connection_closed) {
      // reopen it 
      // be sure to close the open socket.
      $this->close();
      $this->_reopen();
    }
    
    // convert array to string 
    $buffer = implode("\r\n", $this->_req);
    $buffer .= "\r\n\r\n";
    $this->_error_log($buffer);
    fputs($this->_fp, $buffer);
  }
  
  // --------------------------------------------------------------------------
  // private methode _get_respond
  function _get_respond() {
    $this->_error_log('_get_respond()');
    // init vars (good coding style ;-)
    $buffer = '';
    $header = '';
    
    
    // following code maybe helps to improve socket behaviour ... more testing needed
    // disabled at the moment ...
    // socket_set_timeout($this->_fp,1 );
    // $socket_state = socket_get_status($this->_fp);
    
    // read stream one byte by another until http header ends
    do {
      $header.=fread($this->_fp,1);
    } while (!preg_match('/\\r\\n\\r\\n$/',$header));
    
    $this->_error_log($header);
    
    if (preg_match('/Connection: close\\r\\n/', $header)) {
      // This says that the server will close connection at the end of this stream. 
      // Therefore we need to reopen the socket, before are sending the next request...
      $this->_error_log('Connection: close found');
      $this->_connection_closed = true;
    }
    // check how to get the data on socket stream 
    // chunked or content-length (HTTP/1.1) or 
    // one block until feof is received (HTTP/1.0)
    switch(true) {
      case (preg_match('/Transfer\\-Encoding:\\s+chunked\\r\\n/',$header)):
        $this->_error_log('Getting HTTP/1.1 chunked data...');
        do {
          $byte = '';
          $chunk_size='';
          do {
            $chunk_size.=$byte;
            $byte=fread($this->_fp,1);
            // check what happens while reading, because I do not really understand how php reads the socketstream...
            // but so far - it seems to work here - tested with php v4.3.1 on apache 1.3.27 and Debian Linux 3.0 ... 
            if (strlen($byte) == 0) {
              $this->_error_log('_get_respond: warning --> read zero bytes');
            }
          } while ($byte!="\r" and strlen($byte)>0);      // till we match the Carriage Return
          fread($this->_fp, 1);                           // also drop off the Line Feed
          $chunk_size=hexdec($chunk_size);                // convert to a number in decimal system
          $buffer .= fread($this->_fp,$chunk_size);
          fread($this->_fp,2);                            // ditch the CRLF that trails the chunk
        } while ($chunk_size);                            // till we reach the 0 length chunk (end marker)
        break;
        
      // check for a specified content-length
      case preg_match('/Content\\-Length:\\s+([0-9]*)\\r\\n/',$header,$matches):
        $this->_error_log('Getting data using Content-Length');
        $buffer = fread($this->_fp,$matches[1]);
        break;
      
      // check for 204 No Content 
      // 204 responds have no body.
      // Therefore we do not need to read any data from socket stream. 
      case preg_match('/HTTP\/1\.1\ 204/',$header):
        // nothing to do, just proceed  
        $this->_error_log('204 No Content found. No further data to read..');
        break;
      default:
        // just get the data until foef appears...
        $this->_error_log('reading until feof...' . $header);
        socket_set_timeout($this->_fp,0 );
        while (!feof($this->_fp)) {
          $buffer .= fread($this->_fp, 4096);
        }
        // renew the socket timeout...does it do something ???? Is it needed. More debugging needed...
        socket_set_timeout($this->_fp, $this->_socket_timeout);
    }
    
    $this->_header = $header;
    $this->_body = $buffer;
    // $this->_buffer = $header . "\r\n\r\n" . $buffer;
    $this->_error_log($this->_header);
  }
  
  
  
  // --------------------------------------------------------------------------
  // private method _process_respond ...
  // analyse the reponse from server and divide into header and body part
  // returns an array filled with components
  function _process_respond() {
    $lines = explode("\r\n", $this->_header);
    $header_done = false;
    // $this->_error_log($this->_buffer);
    // First line should be a HTTP status line (see http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6)
    // Format is: HTTP-Version SP Status-Code SP Reason-Phrase CRLF
    list($ret_struct['status']['http-version'], 
         $ret_struct['status']['status-code'], 
         $ret_struct['status']['reason-phrase']) = explode(' ', $lines[0],3);
         
    // print "HTTP Version: '$http_version' Status-Code: '$status_code' Reason Phrase: '$reason_phrase'<br>";
    // get the response header fields
    // See http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6
    for($i=1; $i<count($lines); $i++) {
      if (rtrim($lines[$i]) == '' && !$header_done) {
        $header_done = true;
        // print "--- response header end ---<br>";
        
      }   
      if (!$header_done ) {
        // store all found headers in array ...
        list($fieldname, $fieldvalue) = explode(':', $lines[$i]);
        $ret_struct['header'][$fieldname] = trim($fieldvalue);
      } 
    }
    // print 'string len of response_body:'. strlen($response_body);
    // print '[' . htmlentities($response_body) . ']';
    $ret_struct['body'] = $this->_body; 
    return $ret_struct;
  }

  // private method _reopen
  // reopens a socket, if 'connection: closed'-header was received from server 
  function _reopen() {
    // let's try to reopen a socket 
    $this->_error_log('reopen a socket connection');
    return $this->open();
    /* 
    $this->_fp = fsockopen ($this->_server, $this->_port, $this->_errno, $this->_errstr, 5);
    set_time_limit(180);
    socket_set_blocking($this->_fp, true);
    socket_set_timeout($this->_fp,5 );
    if (!$this->_fp) {
      $this->_error_log("$this->_errstr ($this->_errno)\n");
      return false;
    } else {
      $this->_connection_closed = false;
      $this->_error_log('reopen ok...' . $this->_fp);
      return true;
    } 
    */
  }
  
  
  // private method _translate_uri
  // translates an uri to url encoded string 
  function _translate_uri($uri) {
    $parts = explode('/', $uri);
    for ($i = 0; $i < count($parts); $i++) {
      $parts[$i] = rawurlencode($parts[$i]);
    }
    return implode('/', $parts);
  }
  
  // private method _error_log
  // writes debug information to what's in php.ini defined 
  function _error_log($err_string) {
    if ($this->_debug) {
      error_log($err_string);
    }
  
  }  
  
} 




?>
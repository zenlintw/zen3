<?php
   class mime_mail{
        var $parts;
        var $to;
        var $from;
        var $headers;
        var $subject;
        var $body;
        var $body_type;
        var $charset;
        var $body_add;
        var $priority;
        var $reply;

      /* 建立類別建構者 */
      function mime_mail(){
         $this->parts      = array();
         $this->to         = "";
         $this->from       = "";
         $this->subject    = "";
         $this->body       = "";
         $this->headers    = "";
         $this->body_type  = 'text/plain';
         $this->charset    = '';
         $this->body_add   = false;
         $this->priority   = 3;
         $this->parameters = '';
         $this->reply      = '';
      }
      /* 將附檔加入郵件物件 */
      function add_attachment($message,$name="",
                              $ctype="application/octet-stream",
                              $charset=""){
         $this->parts[]=array("ctype"=>$ctype,
                              "message"=>$message,
                              "encode"=>$encode,
                              "name"=>$name,
                              "charset"=>$charset);
      }

      /* 建立 multipart 郵件的訊息部份 */
      function build_message($part)
      {
         $message  = $part['message'];
         $message  = chunk_split(base64_encode($message));
         $encoding = 'base64';
         return 'Content-Type:' . $part["ctype"] .
                 ($part['charset'] ? "; charset=\"{$part["charset"]}\"" : '') .
                 ($part['name'] ? "; name=\"{$part["name"]}\"" : '') .
                 "\nContent-Transfer-Encoding:{$encoding}\nThis is a MIME encoded message\n{$message}\n";
      }

      /* 建立一封 multipart 郵件 */
      function build_multipart(){
         $boundary  = 'b' . md5(uniqid(time()));
         $multipart = 'Content-Type:multipart/mixed;' .
                    "boundary={$boundary}\n\n" .
                    "This is a MIME encoded message.\n\n--{$boundary}";

         for ($i=sizeof($this->parts)-1;$i>=0;$i--){
            $multipart .="\n" . $this->build_message($this->parts[$i]) .
                         "--{$boundary}";
         }
         
         $multipart = str_replace(array("\r\r", "\r\0", "\r\n\r\n", "\n\n", "\n\0"),array("\r", "\r", "\r\n", "\n", "\n"),$multipart);

         return $multipart . "--\n";
      }

      /* 傳回已組合完成的郵件 */
      function get_mail($complete=true){
		$MSMail = array(
				1 => 'High',
				2 => 'High',
				3 => 'Normal',
				4 => 'Low',
				5 => 'Low'
			);

		 $mime='';
         if (!empty($this->from))
            $mime .='From: ' . $this->from . "\n";
         if (!empty($this->reply))
            $mime .='Reply-To: ' . $this->reply . "\n";
         if (!empty($this->headers))
            $mime .=$this->headers . "\n";

         if ($complete){
            if (!empty($this->to))
               $mime .="To: {$this->to}\n";
            if (!empty($this->subject))
               $mime .="Subject: {$this->subject}\n";
         }

         if (!empty($this->body) && !$this->body_add) {
            $this->add_attachment($this->body,"", $this->body_type, $this->charset);
            $this->body_add = true;
         }

         $mime .= "MIME-Version:1.0\n";
         $mime .= "X-Priority: {$this->priority}\n";
         $mime .= "X-MSMail-Priority: {$MSMail[$this->priority]}\n";
		 $mime .= $this->build_multipart();

         return $mime;
      }

      /* 寄出這封信 */
      function send(){
		 static $mail_rule;
         if (!isset($mail_rule)) $mail_rule = '/<(' . substr(sysMailRule, 2, -2) . ')>/';

         $mime=$this->get_mail(false);
         
		if (preg_match(sysMailRule, $this->from))
		{
			$tmp = $this->from;
		}
		else if (preg_match($mail_rule, $this->from, $regs))
		{
			$tmp = $regs[1];
		}
         
		if ($this->from && !preg_match('/\b -f' . preg_quote($tmp) . '\b/', $this->parameters))
		{
			$this->parameters  = preg_replace('/\s*-f[ ]?[^ ]*\s*/', '', $this->parameters);
			$this->parameters .= ' -f' . $tmp;
		}
         
         mail($this->to,$this->subject,"",$mime, $this->parameters);
      }

   } // 類別結束
?>

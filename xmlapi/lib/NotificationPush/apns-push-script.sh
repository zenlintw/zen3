#!/usr/local/php_irs/bin/php
result=`/usr/local/php_irs/bin/php /home/WM3_APP/xmlapi/lib/NotificationPush/apns-push.php $1`

#這裡必須印出數字，push-handler才能接收到
echo $result


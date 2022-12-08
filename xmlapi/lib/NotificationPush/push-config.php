<?php
define('PATH_LIB', dirname(__FILE__)."/lib/");
define('APNS_DIS_PEM', PATH_LIB . 'NotificationPush/general-apns-dis.pem');
define('APNS_DEV_PEM', PATH_LIB . 'NotificationPush/general-apns-dev.pem');
define('APNS_PUSH_SCRIPT', PATH_LIB . 'NotificationPush/apns-push-script.sh');
define('APNS_ENVIRONMENT_PRODUCTION', 'PRODUCTION');
define('APNS_ENVIRONMENT_SANDBOX', 'SANDBOX');
define('APNS_PRODUCTION_PUSH_GATEWAY', 'ssl://gateway.push.apple.com:2195');
define('APNS_PRODUCTION_FEEDBACK', 'ssl://feedback.push.apple.com:2196');
define('APNS_SANDBOX_PUSH_GATEWAY', 'ssl://gateway.sandbox.push.apple.com:2195');
define('APNS_SANDBOX_FEEDBACK', 'ssl://feedback.sandbox.push.apple.com:2196');
define('GCM_TOKEN', 'AIzaSyBISyOFNuLE3TctihmKHRiBC6APTxnWADU');
define('GCM_PUSH_GATEWAY', 'https://android.googleapis.com/gcm/send');
define('JPUSH_PUSH_GATEWAY', 'https://api.jpush.cn/v3/push');
define('JPUSH_APPKEY', 'f4d1bfdafd68683d4c674ebb');
define('JPUSH_MASTER_SECRET', 'c8791e051f322bf32e15e5a0');
define('AppUUID', 'c80f0a62-14ea-f9c9-f8cb-ba4d7bb5e83a');

// FCM 推播用 - Begin
define('SEND_NOTIFICATION_URL', 'https://fcm.googleapis.com/fcm/send');
define('SEND_NOTIFICATION_V1_URL', 'https://fcm.googleapis.com/v1/projects/%project_id%/messages:send');
define('MANAGER_GROUP_URL', 'https://iid.googleapis.com/notification');
define('QUERY_NOTIFICATION_KEY', 'https://iid.googleapis.com/notification?notification_key_name=');
define('QUERY_INSTANCE_ID', 'https://iid.googleapis.com/iid/info/%token%?details=true');
define('APNS_TO_FCM', 'https://iid.googleapis.com/iid/v1:batchImport');
// 位於Firebase後台 -> 專案設定 -> 一般 -> 專案ID
define('PROJECT_ID', 'api-project-5615307530');

// 位於Firebase後台 -> 專案設定 -> CLOUD MESSAGING -> 伺服器金鑰 (目前新舊版金鑰皆可)
define('AUTHORIZATION_KEY', 'AAAAAU6yywo:APA91bEUfU3kiMa2phvhJlv5_nYhT6gzj80Sj-ryMFv5F3bAuFWg8G91cFQmNxI7f8yJhmAi2OqV219XGA2SvuKpNbFBqDxKowcphqqXHz9TKpsiZWDyNSdD1boGahRdFpGrUTP7XF4l');
// 位於Firebase後台 -> 專案設定 -> CLOUD MESSAGING -> 寄件者ID
define('FCM_SENDER_ID', '5615307530');
// FCM 推播用 - End
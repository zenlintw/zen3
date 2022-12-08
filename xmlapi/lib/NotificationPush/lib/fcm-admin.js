/**
 提供node.js fcm admin
 參考資料：https://firebase.google.com/docs/cloud-messaging/admin/legacy-fcm
 */

var admin = require('firebase-admin');

var serviceAccount = require('../hongu-service-account.json');

const {google} = require('googleapis');
admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: 'https://instantmessage-26e66.firebaseio.com'
});

var argv = process.argv,
    action = argv[2];
if (action === '1') {
    getAccessToken();
} else if (action === '2') {
    var notificationData = argv[3];

    notificationData = JSON.parse(notificationData);
    send_notification(notificationData.tokens, notificationData.data);
}

/**
 透過http v1協定推送訊息時，需要透過admin取得Authorization token
 */
function getAccessToken() {
    return new Promise(function(resolve, reject) {
        // var key = require('./service-account.json');
        var jwtClient = new google.auth.JWT(
            serviceAccount.client_email,
            null,
            serviceAccount.private_key,
            'https://www.googleapis.com/auth/firebase.messaging',
            null
        );
        jwtClient.authorize(function(err, tokens) {
            if (err) {
                reject(err);
                return;
            }
            console.log(tokens.access_token);
            resolve(tokens.access_token);
        });
    });
}
// getAccessToken();

/********************
 向多台設備發送訊息
 *********************/
// This registration token comes from the client FCM SDKs.
// var registrationTokens = [
//   "fbMS4cHWoJs:APA91bH0nCSBEDGlD5Jf01eR3Nqa1cEiJi7uxRUVMGbHM-steqj9xbbv8u0Sj1qQvg4RSEBIUPp7k8LA3gAItekRRjZngXULN7HHKryRrcHtW1rC8xNrl-sjhMgbR_7NhiESnK3KDjcK",
//   "fBvqUxC1XzQ:APA91bFqjJrQ3sgfvRYmDe1Pv68cLaqzu36oDRGFTO4wK-Fj47D_Mq-XgWYpjld3sVJzAIXv2Jmupx5Ys1EFKXbfQrnsjnziF6tanOh_v9QwuVYI-n1IcwwtXfosGMgFF5rUhuC_yG6k"
// ];
// {"tokens": ["dg6ZBS1S1pA:APA91bEx9n1EJEOIZdWISXEWN2bAgJx3fOp7LGxTtFioSfv7y8qyHrmClJe7tJcfTM9xyxOLZvU3yPLHT2o75fA7QMcAWN7uEfubqKz7Ix2dSKd50XBiMxz8AebaBhx1u1gbm3sRiK6M","dOwi-UgVmJQ:APA91bGK--wEhR8sZGxJL5vYKQR2wfwj0PoDQVUcM-KAS8EdvCCDPfEwWIOIavGKWmIwSIPcM5G1ZNymJVDKauW3aP1yw9w7F3hGdmt_LFx3lZapzNLedZZP3qAVSEBSCCuLvEYSmPyV","fbMS4cHWoJs:APA91bH0nCSBEDGlD5Jf01eR3Nqa1cEiJi7uxRUVMGbHM-steqj9xbbv8u0Sj1qQvg4RSEBIUPp7k8LA3gAItekRRjZngXULN7HHKryRrcHtW1rC8xNrl-sjhMgbR_7NhiESnK3KDjcK"], "data": {"data": {"score": "123"}, "notification": {"title": "gogo","body":"gogo123"}}}

function send_notification(tokens, sendData) {
    // See the "Defining the message payload" section below for details
    // on how to define a message payload.
    // var payload = {
    //   data: {
    //     score: "850",
    //     time: "2:45"
    //   },
    //   notification: {
    //     title: 'Test Multi Device FCM',
    //     body: 'Body of your push notification'
    //   }
    // };

    // Send a message to the device corresponding to the provided
    // registration token.
    admin.messaging().sendToDevice(tokens, sendData).then(function(response) {
        // See the MessagingDevicesResponse reference documentation for
        // the contents of response.
        console.log("Successfully sent message:", response);

        process.exit(0);
    })
        .catch(function(error) {
            console.log("Error sending message:", error);
        });
}




/********************
 向群組發送訊息
 *********************/
// // See the "Managing device groups" link above on how to generate a
// // notification key.
// var notificationKey = "APA91bEvezOxtbPR2xaevSl1Md-aveRitvR4zDpFn6IQKU-OGIjLmq8x9tnuX838MPhkf9y3yMfaI8wzrp_K8RUZgMZG_eSzC56iHGl1iHPPSKA1LEMWvVA";

// // See the "Defining the message payload" section below for details
// // on how to define a message payload.
// var payload = {
//   data: {
//     score: "850",
//     time: "2:45"
//   }
// };

// // Send a message to the device group corresponding to the provided
// // notification key.
// admin.messaging().sendToDeviceGroup(notificationKey, payload)
//   .then(function(response) {
//     // See the MessagingDeviceGroupResponse reference documentation for
//     // the contents of response.
//     console.log("Successfully sent message:", response);
//   })
//   .catch(function(error) {
//     console.log("Error sending message:", error);
//   });

/********************
 向主題發送訊息
 *********************/

// // The topic name can be optionally prefixed with "/topics/".
// var topic = "app-group";

// // See the "Defining the message payload" section below for details
// // on how to define a message payload.
// var payload = {
//   // data: {
//   //   score: "850",
//   //   time: "2:45"
//   // },
//   // notification: {
//   //   title: 'Test Multi Device FCM',
//   //   body: 'Body of your push notification'
//   // },
//   "android":{
//        "ttl":"86400s",
//        "notification":{
//          "click_action":"OPEN_ACTIVITY_1"
//        }
//      },
//   "apns": {
//        "headers": {
//          "apns-priority": "5",
//        },
//        "notification":{
//          "click_action":"OPEN_ACTIVITY_2"
//        },
//        "payload": {
//          "aps": {
//            "category": "NEW_MESSAGE_CATEGORY"
//          }
//        }
//      },
// };

// // Send a message to devices subscribed to the provided topic.
// admin.messaging().sendToTopic(topic, payload)
//   .then(function(response) {
//     // See the MessagingTopicResponse reference documentation for the
//     // contents of response.
//     console.log("Successfully sent message:", response);
//   })
//   .catch(function(error) {
//     console.log("Error sending message:", error);
//   });
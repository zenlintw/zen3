var FCM = require('fcm-node')

var serverKey = require('./hongu-service-account.json') //put the generated private key path here

var fcm = new FCM(serverKey)

var token = 'dOwi-UgVmJQ:APA91bGK--wEhR8sZGxJL5vYKQR2wfwj0PoDQVUcM-KAS8EdvCCDPfEwWIOIavGKWmIwSIPcM5G1ZNymJVDKauW3aP1yw9w7F3hGdmt_LFx3lZapzNLedZZP3qAVSEBSCCuLvEYSmPyV'
// var token = 'fbMS4cHWoJs:APA91bH0nCSBEDGlD5Jf01eR3Nqa1cEiJi7uxRUVMGbHM-steqj9xbbv8u0Sj1qQvg4RSEBIUPp7k8LA3gAItekRRjZngXULN7HHKryRrcHtW1rC8xNrl-sjhMgbR_7NhiESnK3KDjcK'
//var token = 'fBvqUxC1XzQ:APA91bFqjJrQ3sgfvRYmDe1Pv68cLaqzu36oDRGFTO4wK-Fj47D_Mq-XgWYpjld3sVJzAIXv2Jmupx5Ys1EFKXbfQrnsjnziF6tanOh_v9QwuVYI-n1IcwwtXfosGMgFF5rUhuC_yG6k'
//var token = 'fMkbc2Qd5N4:APA91bEyRIDjH8_kq3Ddl8eR8I8LBKTua7c7gahePXcYT4gvnBXzm1801GqUy9ESzbpWXj6fEyaY9qPay77FdpE0Z70wxhV1f52Mva-EeDVAGHD6OvHNSkDIxF96cDRRrYV_Hdf-q2LX'
var message = { //this may vary according to the message type (single recipient, multicast, topic, et cetera)
    to: token,
    // to: '/topics/fudon',
    notification: {
        title: 'Title of your push notification',
        body: 'Body of your push notification'
    }
}

fcm.send(message, function(err, response){
    if (err) {
        console.log("Something has gone wrong!")
        console.log(response)
    } else {
        console.log("Successfully sent with response: ", response)
        process.exit(0);
    }
})
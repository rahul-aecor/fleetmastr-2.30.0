var fs = require('fs');
var app = require('express')();
var env = require('node-env-file');
env(__dirname + '/.env');
var options = {
  key: fs.readFileSync(process.env.HTTPS_KEY_FILE),
  cert: fs.readFileSync(process.env.HTTPS_CERT_FILE),
};

var server = require('https');
var https = server.createServer(options, app);
var io = require('socket.io')(https, {
        allowEIO3: true,
        cors: {
                //origin: "https://qa.fleetmastr.com:3001",
                origin: /qa\.fleetmastr\.com$/,
                methods: ["GET", "POST"],
                credentials : true,
        },
});
var Redis = require('ioredis');
var redis = new Redis();

var broadcastChannel = process.env.BROADCAST_CHANNEL;
var broadcastPort = process.env.BROADCAST_SERVER_PORT;

redis.subscribe(broadcastChannel, function(err, count) {

});

redis.on('message', function(channel, message) {
    console.log('Message Recieved: ' + message);
    console.log('on channel ', channel);
    message = JSON.parse(message);
    io.emit(channel + ':' + message.event, message.data);
});

https.listen(broadcastPort, function(){
    console.log('Listening on Port ', broadcastPort);
});
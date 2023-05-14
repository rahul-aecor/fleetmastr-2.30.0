var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http, {
  cors: {
    origin: '*',
  }
});
var Redis = require('ioredis');
var env = require('node-env-file');
env(__dirname + '/.env');
//var redis = new Redis(6379, process.env.BROADCAST_SERVER_ADDRESS);
var redis = new Redis(process.env.REDIS_PORT, process.env.REDIS_SERVER);

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

http.listen(broadcastPort, function(){
    console.log('Listening on Port ', broadcastPort);
});

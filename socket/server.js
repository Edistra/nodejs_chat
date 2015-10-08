var http = require('http');
var fs = require('fs');

// Chargement du fichier index.html affiché au client
var server = http.createServer(function(req, res) {
    fs.readFile('./app.html', 'utf-8', function(error, content) {
        res.writeHead(200, {"Content-Type": "text/html"});
        res.end(content);
    });
});

// Chargement de socket.io
var io = require('socket.io').listen(server);

// Quand on client se connecte, on le note dans la console
io.sockets.on('connection', function (socket) {
	socket.login = '';
	socket.emit('new_msg', {login : socket.login, msg : 'Vous êtes bien connecté !', show_login : false});

	socket.on('login', function (login) {
		socket.login = login;
		socket.broadcast.emit('new_msg', {login : socket.login, msg : socket.login+' vient de se connecter !', show_login : false});
	});	
	socket.on('new_msg', function (msg) {
		console.log(socket.login+' : '+msg);
		socket.broadcast.emit('new_msg', {login : socket.login, msg : msg, show_login : true});
	});	
});

//var ipaddress = process.env.OPENSHIFT_NODEJS_IP || "127.0.0.1";
//var port = process.env.OPENSHIFT_NODEJS_PORT || 8080;
//server.listen(port);

var ipaddress = process.env.OPENSHIFT_NODEJS_IP || "127.0.0.1";
var port = process.env.OPENSHIFT_NODEJS_PORT || 8090;
server.listen( port, ipaddress, function() {
    console.log((new Date()) + ' Server is listening on port 8090');
});

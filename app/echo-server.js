require("dotenv").config();
var Echo = require("laravel-echo-server");

/**
 * The Laravel Echo Server options.
 */

var options = {
    "authHost": "http://webrtc-project-2-video-call.herokuapp.com",
	"authEndpoint": "/broadcasting/auth",
	"clients": [],
	"database": "redis",
	"databaseConfig": {
		"redis": "redis://:pd91c638dcfb6e86fff7357d06183d06f9249a464c1916578e89148f389d30ff2@ec2-52-87-60-100.compute-1.amazonaws.com:20279",
		"sqlite": {
			"databasePath": "/database/laravel-echo-server.sqlite"
		}
	},
	"devMode": true,
	"host": "localhost",
	"port": "6001",
	"protocol": "http",
	"socketio": {
		"transports": ["websocket", "polling","flashsocket"]
	},
	"secureOptions": 67108864,
	"sslCertPath": "",
	"sslKeyPath": "",
	"sslCertChainPath": "",
	"sslPassphrase": "",
	"subscribers": {
		"http": true,
		"redis": true
	},
	"apiOriginAllow": {
		"allowCors": true,
		"allowOrigin": "*",
		"allowMethods": "(GET, POST, DELETE)",
		"allowHeaders": "Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization, X-CSRF-TOKEN, X-Socket-Id"
	},
    "verifyAuthPath": true,
    "verifyAuthServer": true
};

/**
 * Run the Laravel Echo Server.
 */
Echo.run(options);

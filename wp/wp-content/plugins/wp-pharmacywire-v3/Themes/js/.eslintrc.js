module.exports = {
	"extends": "airbnb-base",
	"env": {
		"browser": true,
		"jquery": true,
	},
	"globals": {
		"wp_pharmacywire": true,
		"pwire": true,
		"pw_json": true,
		"pw_json_login": true,
		"zxcvbn": true,
		"Foundation": true,
		"Cleave": true,
		"Dropzone": true,
		"doT": true, // profile page
	},
	"rules": {
		"indent": ["error", "tab"],
		"no-tabs": 0,
	}
};
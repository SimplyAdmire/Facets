module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		compass: {
			"dev": {
				"options": {
					"sassDir": "../Resources/Private/Styles/",
					"cssDir": "../Resources/Public/Styles/",
					"environment": "development",
					"outputStyle": "nested",
					"bundleExec": true
				}
			},
			"dist": {
				"options": {
					"sassDir": "../Resources/Private/Styles/",
					"cssDir": "../Resources/Public/Styles/",
					"environment": "production",
					"outputStyle": "compressed",
					"bundleExec": true
				}
			}
		},
		watch: {
			"css": {
				"files": "../Resources/Private/Styles/{,*/}*.{scss,sass}",
				"tasks": ["compass:dev"],
				"options": {
					"spawn": false,
					"debounceDelay": 250,
					"interrupt": true,
					"bundleExec": true
				}
			},
			"scripts": {
				"files": "../Resources/Private/Scripts/**/*.*",
				"tasks": ["concat"],
				"options": {
					"spawn": false,
					"debounceDelay": 250,
					"interrupt": true,
					"bundleExec": true
				}
			}
		},
		requirejs: {
			"compile": {
				"options": {
					"baseUrl": "../../../../Web/_Resources/Static/Packages/",
					"paths": {
						"Library": "SociaalIntranet.Boilerplate/Library/",
						"text": "SociaalIntranet.Boilerplate/Library/text"
					},
					"name": "SociaalIntranet.Boilerplate/Scripts/Site",
					"out": "../Resources/Public/Scripts/Site.js"
				}
			}
		},
		concat: {
			"mirrorApp": {
				"expand": true,
				"cwd": "../Resources/Private/Scripts/",
				"src": ["**/*.js", "**/*.html"],
				"dest": "../Resources/Public/Scripts/"
			},
			"mirrorLibrary": {
				"expand": true,
				"cwd": "dist/library/",
				"src": ["**/*.js"],
				"dest": "../Resources/Public/Library/"
			}
		},
		clean: {
			options: {
				force: true
			},
			js: ["../Resources/Public/Scripts/**/*.js"]
		}
	});

	/**
	 * Load Grunt plugins
	 */
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	grunt.registerTask('default', ['watch']);
	grunt.registerTask('compile-css', ['compass:dist']);
	grunt.registerTask('compile-js', ['concat']);
	grunt.registerTask('compile', ['compile-css', 'compile-js']);

	grunt.registerTask('dist', ['compile-css', 'concat', 'clean']);
};

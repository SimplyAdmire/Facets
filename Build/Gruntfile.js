module.exports = function (grunt) {
	grunt.loadNpmTasks("grunt-extend-config");

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		compass: grunt.file.readJSON('../../../../Build/Grunt/Defaults/compass.json'),
		watch: grunt.file.readJSON('../../../../Build/Grunt/Defaults/watch.json'),
		requirejs: grunt.file.readJSON('../../../../Build/Grunt/Defaults/requirejs.json'),
		concat: grunt.file.readJSON('../../../../Build/Grunt/Defaults/concat.json'),
		clean: {
			options: {
				force: true
			},
			js: ["../Resources/Public/Scripts/**/*.js"]
		}
	});

	grunt.extendConfig({
		concat: {
			// Libraries
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

	grunt.registerTask('dist', ['compile-css', 'concat', 'requirejs', 'clean']);
};

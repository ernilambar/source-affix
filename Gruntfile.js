module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					svn_user: 'rabmalin',
					build_dir: 'deploy/<%= pkg.name %>',
					assets_dir: '.wordpress-org'
				},
			}
		},
	});

	grunt.loadNpmTasks('grunt-wp-deploy');
};

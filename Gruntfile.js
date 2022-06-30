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
		replace : {
			readme: {
				options: {
					patterns: [
						{
							match: /Stable tag:\s?(.+)/gm,
							replacement: 'Stable tag: <%= pkg.version %>'
						}
					]
				},
				files: [
					{
						expand: true, flatten: true, src: ['readme.txt'], dest: './'
					}
				]
			},
			main: {
				options: {
					patterns: [
						{
							match: /Version:\s?(.+)/gm,
							replacement: 'Version: <%= pkg.version %>'
						}
					]
				},
				files: [
					{
						expand: true, flatten: true, src: ['<%= pkg.main_file %>'], dest: './'
					}
				]
			},
			class: {
				options: {
					patterns: [
						{
							match: /define\( \'SOURCE_AFFIX_VERSION\'\, \'(.+)\'/gm,
							replacement: "define( 'SOURCE_AFFIX_VERSION', '<%= pkg.version %>'"
						}
					]
				},
				files: [
					{
						expand: true, flatten: true, src: ['<%= pkg.main_file %>'], dest: './'
					}
				]
			}
		}
	});

	grunt.loadNpmTasks('grunt-wp-deploy');
	grunt.loadNpmTasks('grunt-replace');

	grunt.registerTask('wpdeploy', ['wp_deploy']);
	grunt.registerTask('version', ['replace:readme', 'replace:main', 'replace:class']);
};

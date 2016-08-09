module.exports = function( grunt ) {
	'use strict';

	/**
	 * Deploy files list.
	 */
	var deploy_files_list = [
		'css/**',
		'includes/**',
		'js/**',
		'views/**',
		'readme.txt',
		'class-source-affix.php',
		'class-source-affix-admin.php',
		'<%= pkg.main_file %>',
		'uninstall.php'
	];

	grunt.initConfig({

		pkg: grunt.file.readJSON( 'package.json' ),

		// Setting folder templates.
		dirs: {
			js: 'admin/js',
			css: 'admin/css',
			images: 'admin/images',
			js_public: 'public/js',
			css_public: 'public/css',
			images_public: 'public/images'
		},

		// Other options.
		options: {
			text_domain: 'source-affix'
		},

		// Copy folders.
		clean: {
			post_build: ['build'],
			remove_trunk:['build/<%= pkg.name %>/trunk/'],
			deploy: ['deploy'],
			build: ['build']
		},

		// Copy files.
		copy: {
			svn_trunk: {
				options: {
					mode: true
				},
				expand: true,
				src: deploy_files_list,
				dest: 'build/<%= pkg.name %>/trunk/'
			},
			svn_tag: {
				options: {
					mode: true
				},
				expand: true,
				src: deploy_files_list,
				dest: 'build/<%= pkg.name %>/tags/<%= pkg.version %>/'
			},
			deploy: {
				src: deploy_files_list,
				dest: 'deploy/<%= pkg.name %>',
				expand: true,
				dot: true
			}
		},

		// Replace strings.
		replace: {
			readme_txt: {
				src: [ 'readme.txt' ],
				overwrite: true,
				replacements: [{
					from: /Stable tag: (.*)/,
					to: 'Stable tag: <%= pkg.version %>'
				}]
			},
			'plugin_file': {
				src: [ '<%= pkg.main_file %>' ],
				overwrite: true,
				replacements: [{
					from: /\*\s*Version:\s*(.*)/,
					to: '* Version: <%= pkg.version %>'
				}]
			}
		},

		// Fetch from SVN.
		svn_export: {
			dev: {
				options:{
					repository: 'https://plugins.svn.wordpress.org/<%= pkg.name %>',
					output: 'build/<%= pkg.name %>'
				}
			}
		},

		// Push to SVN.
		push_svn:{
			options: {
				username: 'rabmalin',
				password: 'passwordhere',
				remove: true
			},
			main: {
				src: 'build/<%= pkg.name %>',
				dest: 'https://plugins.svn.wordpress.org/<%= pkg.name %>',
				tmp: 'build/make_svn'
			}
		},

		// Generate POT.
		makepot: {
			target: {
				options: {
					type: 'wp-plugin',
					domainPath: 'languages',
					exclude: ['build/.*', 'deploy/.*', 'node_modules/.*'],
					updateTimestamp: false,
					potHeaders: {
						'report-msgid-bugs-to': '',
						'x-poedit-keywordslist': true,
						'language-team': '',
						'Language': 'en_US',
						'X-Poedit-SearchPath-0': '../../<%= pkg.name %>',
						'plural-forms': 'nplurals=2; plural=(n != 1);',
						'Last-Translator': 'Nilambar Sharma <nilambar@outlook.com>'
					}
				}
			}
		},

		// Check textdomain.
		checktextdomain: {
			options: {
				text_domain: '<%= options.text_domain %>',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [
					'**/*.php',
					'!node_modules/**',
					'!deploy/**',
					'!build/**'
				],
				expand: true
			}
		},

		// Update text domain.
		addtextdomain: {
			options: {
				textdomain: '<%= options.text_domain %>',
				updateDomains: true
			},
			target: {
				files: {
					src: [
					'*.php',
					'**/*.php',
					'!node_modules/**',
					'!deploy/**',
					'!build/**',
					'!tests/**'
					]
				}
			}
		},

		// CSS minification.
		cssmin: {
			admin: {
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>',
					src: ['*.css', '!*.min.css'],
					dest: '<%= dirs.css %>',
					ext: '.min.css'
				}]
			},
			public: {
				files: [{
					expand: true,
					cwd: '<%= dirs.css_public %>',
					src: ['*.css', '!*.min.css'],
					dest: '<%= dirs.css_public %>',
					ext: '.min.css'
				}]
			}
		}

	});

	// Load NPM tasks to be used here.
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-push-svn' );
	grunt.loadNpmTasks( 'grunt-svn-export' );
	grunt.loadNpmTasks( 'grunt-text-replace' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	// Register tasks.
	grunt.registerTask( 'default', [] );

	grunt.registerTask( 'build', [
		'cssmin',
		'addtextdomain',
		'makepot'
	]);

	grunt.registerTask( 'precommit', [
		'checktextdomain'
	]);

	grunt.registerTask( 'textdomain', [
		'addtextdomain',
		'makepot'
	]);

	grunt.registerTask( 'deploy', [
		'clean:deploy',
		'copy:deploy'
	]);

	grunt.registerTask( 'version', [
		'replace:readme_txt',
		'replace:plugin_file'
	] );

	grunt.registerTask( 'prerelease', [
		'version',
		'textdomain'
	] );

	grunt.registerTask( 'do_svn_dry', [
		'clean:build',
		'svn_export',
		'clean:remove_trunk',
		'copy:svn_trunk',
		'copy:svn_tag'
	] );

	grunt.registerTask( 'do_svn_run', [
		'clean:build',
		'svn_export',
		'clean:remove_trunk',
		'copy:svn_trunk',
		'copy:svn_tag',
		'push_svn'
	] );

	grunt.registerTask( 'release', [
		'prerelease',
		'do_svn_run'
	] );

	grunt.registerTask( 'post_release', [
		'clean:post_build'
	] );
};

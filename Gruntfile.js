module.exports = function(grunt) {

	const SOURCE_DIR = 'src/',
	BUILD_DIR = 'build/',
	path = require('path'),
	jshintrc = grunt.file.readJSON('.jshintrc');

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	var verion_updater = {
		version: '', // to be set dynamically
		update: function (match, p1) {
			if (!this.version) {
				grunt.warn('No version number set!');
			}
			return match.replace(p1,this.version);
		}
	};
	verion_updater.update = verion_updater.update.bind(verion_updater);

	grunt.initConfig({

		pkg: grunt.file.readJSON( 'package.json' ),

		dirs: {
			lang: SOURCE_DIR + 'languages'
		},
		clean: {
			css: [
				BUILD_DIR + 'admin/css/*.min.css',
				BUILD_DIR + 'admin/css/*rtl*',
				BUILD_DIR + 'public/css/*.min.css',
				BUILD_DIR + 'public/css/*rtl*',
				BUILD_DIR + 'modules/**/css/*.min.css',
				BUILD_DIR + 'modules/**/css/*rtl*',
			],
			js: [
				BUILD_DIR + 'admin/js/*.min.js',
				BUILD_DIR + 'public/js/*.min.js',
				BUILD_DIR + 'modules/**/js/*.min.js',
			],
			i18n: [
				BUILD_DIR + 'languages/*.{pot,po,mo}',
			],
			dynamic: {
				dot: true,
				expand: true,
				cwd: BUILD_DIR,
				src: []
			},
			all: [
				BUILD_DIR + '*',
				'!' + BUILD_DIR + 'includes/cmb2/**',
				'!' + BUILD_DIR + '.git',
			]
		},
		copy: {
			all: {
				dot: true,
				expand: true,
				cwd: SOURCE_DIR,
				src: [ '**/*' ],
				dest: BUILD_DIR
			},
			i18n: {
				dot: true,
				expand: true,
				cwd: SOURCE_DIR + 'languages',
				src: [ '*.{pot,po,mo}' ],
				dest: BUILD_DIR + 'languages'
			},
			changelog: {
				src: 'changelog.md',
				dest: BUILD_DIR,
			},
			dynamic: {
				dot: true,
				expand: true,
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				src: []
			}
		},

		makepot: {
			gen: {
				options: {
					domainPath: 'languages/',
					exclude: ['includes/cmb2/.*'],
					potComments: '',
					potFilename: '<%= pkg.name %>.pot',
					type: 'wp-plugin',
					cwd: SOURCE_DIR,
					mainFile: '<%= pkg.name %>.php',
					updateTimestamp: true,
					updatePoFiles: true,
					potHeaders: {
						poedit: true,
						'language': 'en_US',
						'X-Poedit-Basepath': '..\n',
						'Plural-Forms': 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n',
						'X-Poedit-KeywordsList': '__;_e;_x;esc_attr__;esc_attr_e;esc_html__;esc_html_e\n',
						'X-Poedit-SearchPath-0': '.\n',
						'X-Poedit-SearchPathExcluded-0': 'includes/cmb2\n'
					},
					processPot: function( pot ) {
						var translation,
								excluded_meta = [
										'Plugin Name of the plugin/theme',
										'Plugin URI of the plugin/theme',
										'Author of the plugin/theme',
										'Author URI of the plugin/theme',
										'Description of the plugin/theme'
								];
						const { config } = grunt;

						for ( translation in pot.translations[''] ) {
								if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
										if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
												delete pot.translations[''][ translation ];
										}
								}
						}

						pot.headers['report-msgid-bugs-to'] = 'http://wordpress.org/support/plugin/' + config(['pkg', 'name']);
						pot.headers['last-translator'] = config(['pkg', 'title']);
						pot.headers['language-team'] = config(['pkg', 'title']);
						var today = new Date();
						pot.headers['po-revision-date'] = today.getFullYear() +'-'+ ('0' + (today.getMonth() + 1)).slice(-2) +'-'+ today.getDate() +' '+ today.getUTCHours() +':'+ today.getUTCMinutes() +'+'+ today.getTimezoneOffset();
						return pot;
					}
				}
			}
		},

		potomo: {
			gen: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.lang %>/',
					src: ['*.po'],
					dest: '<%= dirs.lang %>/',
					ext: '.mo',
					nonull: true
				}]
			}
		},

		checktextdomain: {
			all: {
				files: [
					{
						expand: true,
						cwd: SOURCE_DIR,
						src: [
							'**/*.php', // Include all files
							'!includes/cmb2/**'
						]
					}
				]
			},
			options: {
				text_domain: '<%= pkg.name %>',
				report_variable_domain: false,
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
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
				]
			}
		},
		cssmin: {
			options: {
				compatibility: 'ie7'
			},
			core: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '.min.css',
				src: [
					'admin/css/*.css',
					'public/css/*.css',
				]
			},
			modules: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '.min.css',
				src: [
					'modules/**/css/*.css',
				]
			},
			/* blocks: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '.min.css',
				src: [
					'admin/blocks/*.css'
				]
			}, */
			rtl: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '.min.css',
				src: [
					'admin/css/*-rtl.css',
					'public/css/*-rtl.css',
					'modules/**/css/*-rtl.css',
				]
			}
		},
		rtlcss: {
			options: {
				// rtlcss options
				opts: {
					clean: false,
					processUrls: { atrule: true, decl: false },
					stringMap: [
						{
							name: 'import-rtl-stylesheet',
							priority: 10,
							exclusive: true,
							search: [ '.css' ],
							replace: [ '-rtl.css' ],
							options: {
								scope: 'url',
								ignoreCase: false
							}
						}
					]
				},
				saveUnmodified: true
			},
			core: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '-rtl.css',
				src: [
					'admin/css/*.css',
					'public/css/*.css'
				]
			},
			modules: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '-rtl.css',
				src: [
					'modules/**/css/*.css'
				]
			},
			dynamic: {
				expand: true,
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				ext: '-rtl.css',
				src: []
			}
		},
		phplint: {
			options: {
				phpCmd: '/usr/bin/php5.6',
	            phpArgs: {
	                '-d': ['display_errors', 'display_startup_errors']
	            }
	        },
	        all: {
				expand: true,
				cwd: SOURCE_DIR,
				src: [ '**/*.php' ],
			},
	    },
		jshint: {
			options: jshintrc,
			config: {
				src: ['Gruntfile.js'],
				options: {
					esversion: 6
				}
			},
			core: {
				expand: true,
				cwd: SOURCE_DIR,
				src: [
					'admin/js/*.js',
					'!admin/js/*.min.js',
					'public/js/*.js',
					'!public/js/*.min.js'
				],
				// Remove once other JSHint errors are resolved
				options: {
					curly: false,
					eqeqeq: false
				}
			},
			modules: {
				expand: true,
				cwd: SOURCE_DIR,
				src: [
					'modules/**/js/*.js',
					'!modules/**/js/*.min.js'
				],
				// Remove once other JSHint errors are resolved
				options: {
					curly: false,
					eqeqeq: false
				}
			}
		},
		uglify: {
			options: {
				ASCIIOnly: true,
				screwIE8: false
			},
			core: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '.min.js',
				src: [
					'admin/js/*.js',
					'public/js/*.js',
					// Exceptions
					'!*.min.js'
				]
			},
			modules: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '.min.js',
				src: [
					'modules/**/js/*.js',
					// Exceptions
					'!*.min.js'
				]
			},
			dynamic: {
				expand: true,
				cwd: BUILD_DIR,
				dest: BUILD_DIR,
				ext: '.min.js',
				src: []
			}
		},
		
		_watch: {
			options: {
				interval: 2000
			},
			all: {
				files: [
					SOURCE_DIR + '**/*',
					'!' + SOURCE_DIR + 'admin/blocks/**/*'
				],
				tasks: ['clean:dynamic', 'copy:dynamic'],
				options: {
					dot: true,
					spawn: false
				}
			},
			js: {
				files: [
					SOURCE_DIR + 'admin/js/*.js',
					SOURCE_DIR + 'public/js/*.js'
				],
				tasks: ['clean:dynamic', 'copy:dynamic', 'uglify:dynamic'],
				options: {
					dot: true,
					spawn: false
				}
			},
			config: {
				files: [ 'Gruntfile.js' ]
			},
			rtl: {
				files: [
					SOURCE_DIR + 'admin/css/*.css',
					SOURCE_DIR + 'public/css/*.css'
				],
				tasks: ['rtlcss:dynamic'],
				options: {
					spawn: false
				}
			}
		},
		update_files: {
			config: {
				files: {
					'./': 'package{-lock.json,.json}',
				},
				options: {
					replacements: [
						{
							pattern: /"version":\s*"(\d+\.\d+\.\d+)"/i,
							replacement: verion_updater.update
						}
					]
				}
			},
			readme: {
				files: {
					'./': 'README.md',
					[SOURCE_DIR]: SOURCE_DIR + 'README.txt',
				},
				options: {
					replacements: [
						{
							pattern: /Stable tag:(?:\*\*)?[\s\t]*(\d+\.\d+\.\d+)/i,
							replacement: verion_updater.update
						}
					]
				}
			},
			mainfile: {
				files: {
					[SOURCE_DIR]: SOURCE_DIR + 'wptelegram.php'
				},
				options: {
					replacements: [
						{
							pattern: /Version:\s*(\d+\.\d+\.\d+)/i,
							replacement: verion_updater.update
						},
						{
							pattern: /'WPTELEGRAM_VER',\s*'(\d+\.\d+\.\d+)'/i,
							replacement: verion_updater.update
						}
					]
				}
			},
			'since-xyz': {
				files: [
					{
						expand: true,
						cwd: SOURCE_DIR,
						src: '**/*.php',
						dest: SOURCE_DIR
				    }
			    ],
				options: {
					replacements: [
						{
							pattern: /@since[\s\t]*(x\.y\.z)/ig,
							replacement: verion_updater.update
						}
					]
				}
			},
			'changelog-readme': {
				files: {
					[SOURCE_DIR]: SOURCE_DIR + 'README.txt',
				},
				options: {
					replacements: [
						{
							pattern: /== Changelog ==([\s\S])/i,
							replacement: function (match, p1) {

								const { version } = verion_updater;
								if (!version) {
									grunt.warn('No version number set!');
								}

								const changes = grunt.file.read('./changelog.md') // get contents of changelog file
								.match(/(?<=\#\#\sUnreleased)[\s\S]+?(?=##\s?\[\d+\.\d+\.\d+)/i)[0] // match the changes in Unreleased section
								.replace(/(^|\n)(\#\#.+)/g,'') // remove headings like Enhancements, Bug fixes
								.replace(/\n[\s\t]*\n/g,`\n`) // replace empty lines
								.trim(); // cleanup

								const replace = `\n\n= ${version} =\n${changes}\n`;
								return match.replace(p1, replace);
							}
						}
					]
				}
			},
			'changelog-md': {
				files: {
					'./': 'changelog.md'
				},
				options: {
					replacements: [
						{
							pattern: /## (Unreleased)/i,
							replacement: function (match, p1) {
								const { version } = verion_updater;
								if (!version) {
									grunt.warn('No version number set!');
								}
								var today = new Date();
								var replace = '[' + version + ' - ' + today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2) + '-' + today.getDate() + '](https://github.com/manzoorwanijk/wptelegram/releases/tag/v' + version + ')';
								return match.replace(p1, replace);
							}
						}
					]
				}
			}
		},
		buildcontrol: {
		    options: {
		      dir: 'build',
		      commit: true,
		      push: true,
		      message: 'Built %sourceName% from commit %sourceCommit% on branch %sourceBranch%'
		    },
		    remote: {
		      options: {
		        remote: '<%= pkg.repository.url %>.git',
		        branch: 'trunk'
		      }
		    },
		    local: {
		      options: {
		        remote: '../',
		        branch: 'trunk'
		      }
		    }
		},
		exec: {
			'bundle-cmb2': {
				cmd: './tools/bundle-cmb2.sh'
			}
		}

	});

	// RTL task.
	grunt.registerTask('rtl', [
		'rtlcss:core',
		'rtlcss:modules',
	]);

	// CSS task
	grunt.registerTask( 'build:css', [
		'clean:css',
		'rtl',
		'cssmin:core',
		'cssmin:modules',
		'cssmin:rtl'
	] );

	// JSHint task.
	grunt.registerTask( 'jshint:all', [
		'jshint:config',
		'jshint:core',
		'jshint:modules',
	] );

	// JS Minify task
	grunt.registerTask( 'uglify:all', [
		'uglify:core',
		'uglify:modules',
	] );

	grunt.registerTask( 'build:js', [
		'clean:js',
		'uglify:all',
	] );

	grunt.registerTask( 'build:files', [
		'clean:all',
		'copy:all'
	] );

	grunt.registerTask( 'lint:all', function() {
		grunt.task.run( [
			'phplint:all',
			'jshint:all',
		] );
	} );

	grunt.registerTask( 'build:all', function() {
		grunt.task.run( [
			'build:files',
			'build:js',
			'build:css',
		] );
	} );

	grunt.registerTask( 'build', function() {
		grunt.task.run( [
			'lint:all',
			'build:all',
		] );
	} );

	grunt.registerTask( 'bundle:cmb2', [
		'exec:bundle-cmb2'
	]);

	grunt.registerTask( 'i18n:all', [
		'checktextdomain:all',
		'makepot:gen',
		'potomo:gen',
		'clean:i18n',
		'copy:i18n',
	] );

	grunt.renameTask( 'string-replace', 'update_files' );

	grunt.registerTask( 'update:version', function() {

		let version = grunt.option('to');
		if (!version) {
			grunt.warn('No version number supplied! usage: grunt update:version --to=x.y.z');
		}

		verion_updater.version = version;

		grunt.task.run( [
			'update_files:config',
			'update_files:readme',
			'update_files:mainfile',
			'update_files:since-xyz',
		] );
	} );

	grunt.registerTask( 'update:changelog', function() {

		let version = grunt.option('to');
		if (!version) {
			grunt.warn('No version number supplied! usage: grunt update:changelog --to=x.y.z');
		}

		verion_updater.version = version;

		grunt.task.run( [
			'update_files:changelog-readme',
			'update_files:changelog-md',
		] );
	} );

	grunt.registerTask( 'prerelease', [
		'lint:all',
		'build:all',
		'bundle:cmb2',
		'i18n:all',
		'copy:changelog',
	] );

	grunt.registerTask( 'precommit', [
		'lint:all',
		'update:version',
		'update:changelog',
		'prerelease',
	] );

	grunt.registerTask( 'commit:git:trunk', [
		'buildcontrol'
	] );

	// Default task.
	grunt.registerTask('default', ['build']);

	grunt.renameTask( 'watch', '_watch' );

	grunt.registerTask( 'watch', function() {
		if ( ! this.args.length ) {
			grunt.task.run( 'build' );
		}

		grunt.task.run( '_' + this.nameArgs );
	} );

	/*
	 * Automatically updates the `:dynamic` configurations
	 * so that only the changed files are updated.
	 */
	grunt.event.on( 'watch', function( action, filepath, target ) {
		var src;

		// Only configure the dynamic tasks based on known targets.
		if ( [ 'all', 'rtl' ].indexOf( target ) === -1 ) {
			return;
		}

		// Normalize filepath for Windows.
		filepath = filepath.replace( /\\/g, '/' );

		src = [ path.relative( SOURCE_DIR, filepath ) ];

		if ( ! src ) {
			grunt.warn( 'Failed to determine the destination file.' );
			return;
		}

		if ( action === 'deleted' ) {

			// Clean up only those files that were deleted.
			grunt.config( [ 'clean', 'dynamic', 'src' ], src );
		} else {

			// Otherwise copy over only the changed file.
			grunt.config( [ 'copy', 'dynamic', 'src' ], src );

			// For css run the rtl task on just the changed file.
			if ( target === 'rtl' ) {
				grunt.config( [ 'rtlcss', 'dynamic', 'src' ], src );
			}
		}
	});
};

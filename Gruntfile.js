module.exports = function(grunt) {
"use strict";

    grunt.initConfig({

        // gets the package vars
        pkg: grunt.file.readJSON("package.json"),
        svn_settings: {
            path: "../../../../wp_plugins/<%= pkg.name %>",
            tag: "<%= svn_settings.path %>/tags/<%= pkg.version %>",
            trunk: "<%= svn_settings.path %>/trunk",
            exclude: [
                ".editorconfig",
                ".git/",
                ".gitignore",
                "node_modules/",
                "assets/js/jquery.wc-gmcf.js",
                "Gruntfile.js",
                "README.md",
                "package.json",
                "*.zip"
            ]
        },

        // javascript linting with jshint
        jshint: {
            options: {
                "bitwise": true,
                "eqeqeq": true,
                "eqnull": true,
                "immed": true,
                "newcap": true,
                "esnext": true,
                "latedef": true,
                "noarg": true,
                "node": true,
                "undef": false,
                "browser": true,
                "trailing": true,
                "jquery": true,
                "curly": true
            },
            all: [
                "Gruntfile.js",
                "assets/js/jquery.wc-gmcf.js"
            ]
        },

        // uglify to concat and minify
        uglify: {
            dist: {
                files: {
                    "assets/js/jquery.wc-gmcf.min.js": ["assets/js/jquery.wc-gmcf.js"]
                }
            }
        },

        // image optimization
        imagemin: {
            dist: {
                options: {
                    optimizationLevel: 7,
                    progressive: true
                },
                files: [{
                    expand: true,
                    cwd: "./",
                    src: ["screenshot-*.png"],
                    dest: "./"
                }]
            }
        },

        // rsync commands used to take the files to svn repository
        rsync: {
            tag: {
                src: "./",
                dest: "<%= svn_settings.tag %>",
                recursive: true,
                exclude: "<%= svn_settings.exclude %>"
            },
            trunk: {
                src: "./",
                dest: "<%= svn_settings.trunk %>",
                recursive: true,
                exclude: "<%= svn_settings.exclude %>"
            }
        },

        // shell command to commit the new version of the plugin
        shell: {
            svn_add: {
                command: 'svn add --force * --auto-props --parents --depth infinity -q',
                options: {
                    stdout: true,
                    stderr: true,
                    execOptions: {
                        cwd: "<%= svn_settings.path %>"
                    }
                }
            },
            svn_commit: {
                command: "svn commit -m 'updated the plugin version to <%= pkg.version %>'",
                options: {
                    stdout: true,
                    stderr: true,
                    execOptions: {
                        cwd: "<%= svn_settings.path %>"
                    }
                }
            }
        },

        // creates a zip of the plugin
        zip: {
            dist: {
                cwd: "./",
                src: [
                    "**",
                    "!node_modules/**",
                    "!.git/**",
                    "!.gitignore",
                    "!.editorconfig",
                    "!Gruntfile.js",
                    "!package.json",
                    "!**.zip"
                ],
                dest: "./<%= pkg.name %>-<%= pkg.version %>.zip"
            }
        }
    });

    // load tasks
    grunt.loadNpmTasks("grunt-contrib-jshint");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks("grunt-rsync");
    grunt.loadNpmTasks("grunt-shell");
    grunt.loadNpmTasks("grunt-zip");

    // compile task
    grunt.registerTask("compile", [
        "jshint",
        "uglify"
    ]);

    // deploy task
    grunt.registerTask("default", [
        "default",
        "rsync:tag",
        "rsync:trunk",
        "shell:svn_add",
        "shell:svn_commit"
    ]);

    // compile task
    grunt.registerTask("compress", [
        "compile",
        "zip"
    ]);
};

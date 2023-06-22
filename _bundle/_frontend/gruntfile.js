module.exports = function (grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            dist1: {
                src: ['css/*.css'],
                dest: 'build/anycontent.css'
            },
            dist2: {
                src: ['js/*.js'],
                dest: 'build/anycontent.js'
            },
            dist3: {
                src: [
                    'node_modules/blockui/jquery.blockUI.js',
                    //'node_modules/sortablejs/Sortable.js',
                    //'Libs/NestedSortable/mjsarfatti-nestedSortable/jquery.mjs.nestedSortable.js',
                    //'Libs/bootbox/bootbox.js'
                ],

                dest: 'build/anycontent.libs.js'
            }
        },

        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
            },
            build: {
                files:{
                    '../public/anycontent.min.js': ['build/anycontent.js'],
                    '../public/anycontent.libs.min.js': ['build/anycontent.libs.js']
                }
            }
        },


        cssmin: {
            dist: {
                options: {
                    banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
                },
                files: {
                    '../public/anycontent.min.css': ['build/anycontent.css']
                }
            }
        },

        watch: {
            scripts: {
                files: ['css/*.css', 'js/*.js'],
                tasks: ['dist'],
                options: {
                    spawn: false,
                },
            },
        },

    });

    // Load the plugin that provides the "concat" task.
    grunt.loadNpmTasks('grunt-contrib-concat');

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');


    // Load the plugin that provides the "cssmin" task.
    grunt.loadNpmTasks('grunt-contrib-cssmin');


    // Load the plugin that provides the "cssmin" task.
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('dist', ['concat:dist1', 'concat:dist2', 'concat:dist3', 'cssmin','uglify']);

    // Default task(s).
    grunt.registerTask('default', ['dist']);

};
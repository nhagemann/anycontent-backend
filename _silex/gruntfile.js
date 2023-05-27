module.exports = function (grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            dist1: {
                src: ['src/AnyContent/CMCK/Modules/Backend/*/*/grunt/*.css', 'src/AnyContent/CMCK/Modules/Backend/*/*/*/grunt/*.css'],
                dest: 'tmp/concat.css'
            },
            dist2: {
                src: ['src/AnyContent/CMCK/Modules/Backend/*/*/grunt/*.js', 'src/AnyContent/CMCK/Modules/Backend/*/*/*/grunt/*.js'],
                dest: 'tmp/concat.js'
            }
        },

        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
            },
            build: {
                src: 'tmp/concat.js',
                dest: 'web/js/anycontent.min.js'
            }
        },


        cssmin: {
            dist: {
                options: {
                    banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
                },
                files: {
                    'web/css/anycontent.min.css': ['tmp/concat.css']
                }
            }
        },

        watch: {
            scripts: {
                files: ['src/AnyContent/CMCK/Modules/Backend/*/*/grunt/*.css', 'src/AnyContent/CMCK/Modules/Backend/*/*/*/grunt/*.css', 'src/AnyContent/CMCK/Modules/Backend/*/*/grunt/*.js', 'src/AnyContent/CMCK/Modules/Backend/*/*/*/grunt/*.js'],
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

    grunt.registerTask('dist', ['concat:dist1', 'concat:dist2', 'cssmin','uglify']);

    // Default task(s).
    grunt.registerTask('default', ['dist']);

};
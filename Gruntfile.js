module.exports = function(grunt) {
    // ✅ Load Dart Sass implementation (required for grunt-sass v4+)
    const sass = require('sass');

    const scssFiles = [
        'resources/cms/scss/ecms.scss',
        'resources/cms/scss/custom.scss',
        'resources/front/scss/style.scss',
    ];

    const pagesScssFiles = [
        'application/views/front/home/style.scss',
    ];

    const jsFiles = [
        'resources/cms/javascript/core.js',
        'resources/cms/javascript/custom.js',
        'resources/front/js/functions.js'
    ];

    // Determine CSS paths
    let scssDestinations = {};
    scssFiles.map(function(path){
        scssDestinations[path.replace(/(.*)scss\/([^\/]+)\.scss$/gi, '$1css/$2.min.css')] = path;
    });

    // Determine JS minified path
    let jsDestinations = {};
    jsFiles.map(function(path){
        jsDestinations[path.replace(/(.*)\/([^\/]+)\.js$/gi, '$1/$2.min.js')] = path;
    });

    pagesScssFiles.map(function(path){
        var endPath = path.replace('application/views/front/','resources/front/css/');
        endPath = endPath.replace('.scss','.min.css');
        scssDestinations[endPath] = path;
        scssFiles.push(path.toString());
    });

    // Add theme files
    scssDestinations['resources/cms/css/theme/main.min.css'] = 'resources/cms/scss/theme/main.scss';

    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            build: {
                files: jsDestinations
            },
            theme: {
                files: {
                    'resources/cms/javascript/theme/common.min.js': 'resources/cms/javascript/theme/common.js'
                }
            }
        },
        sass: {
            build: {
                options: {
                    implementation: sass, // ✅ Required for grunt-sass v4+
                    style: 'compressed'
                },
                files: scssDestinations
            },
            theme: {
                options: {
                    implementation: sass, // ✅ Required for grunt-sass v4+
                    style: 'compressed'
                },
                files: {
                    'resources/cms/css/theme/main.min.css': 'resources/cms/scss/theme/main.scss'
                }
            }
        },
        watch: {
            js: {
                files: jsFiles,
                tasks: ['uglify:build']
            },
            css: {
                files: scssFiles,
                tasks: ['sass:build']
            }
        }
    });

    // Load Grunt plugins
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-sass');         // Uses Dart Sass via `sass` package
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Register tasks
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('js', ['uglify:build']);
    grunt.registerTask('css', ['sass:build']);
    grunt.registerTask('js:theme', ['uglify:theme']);
    grunt.registerTask('css:theme', ['sass:theme']);
};
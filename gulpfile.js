// Load plugins.
let gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    notify = require('gulp-notify'),
    cssbeautify = require('gulp-cssbeautify');

// Path variables.
let path = {
    styles: {
        src:    'styles',
        build:  './'
    }
};

// Styles.
gulp.task('styles', () => {
    return gulp.src(path.styles.src + '/styles.scss')
        .pipe(sass({}).on('error', sass.logError))
        .pipe(autoprefixer('last 2 version'))
        .pipe(cssbeautify())
        .pipe(gulp.dest(path.styles.build))
        .pipe(notify({message: 'Styles task complete', onLast: true}));
});

// Default task.
gulp.task('default', gulp.series('styles'));

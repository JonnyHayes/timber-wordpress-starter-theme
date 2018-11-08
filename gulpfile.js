var pkg = require('./package.json'),
	banner = ['/* Theme Name: <%= pkg.theme_name %>',
		'Theme URI:',
		'Author: <%= pkg.author %>',
		'Author URI: http://mvnp.com/',
		'Description: <%= pkg.description %>',
		'Version: <%= pkg.version %>',
		'License: <%= pkg.license %>',
		'License URI: <%= pkg.license_url %>',
		'Text Domain: <%= pkg.text_domain %>',
		'Domain Path: /languages */',
		''
	].join('\n'),

	os = require('os'),
	gulp = require('gulp'),
	composer = require('gulp-composer'),
	sass = require('gulp-sass'),
	rtlcss = require('gulp-rtlcss'),
	cleancss = require('gulp-clean-css'),
	concat = require('gulp-concat'),
	uglify = require('gulp-uglify'),
	rename = require('gulp-rename'),
	header = require('gulp-header'),
	debug = require('gulp-debug'),
	gulpIf = require('gulp-if'),
	wpPot = require('gulp-wp-pot'),
	jsdoc2md = require('gulp-jsdoc-to-markdown'),
	sourcemaps = require('gulp-sourcemaps');

gulp.task('sass', function(){
	return gulp.src('*.scss')
		.pipe(debug())
		.pipe(sourcemaps.init())
		.pipe(sass.sync({
			outputStyle: 'compressed'
		}).on('error', sass.logError))
		.pipe(cleancss())
		.pipe(header(banner, {
			pkg: pkg
		}))
		.pipe(sourcemaps.write('./maps'))
		.pipe(gulp.dest('./'))
		.pipe(gulpIf('*.css', rtlcss()))
		.pipe(gulpIf('*.css', rename({
			suffix: '-rtl'
		})))
		.pipe(gulpIf('*.css', gulp.dest('./')));
});

gulp.task('sass-views', function(){
	return gulp.src('css/views/*.scss', {
		base: './'
	})
		.pipe(debug())
		.pipe(sourcemaps.init())
		.pipe(sass.sync({
			outputStyle: 'compressed'
		}).on('error', sass.logError))
		.pipe(cleancss())
		.pipe(sourcemaps.write('./maps'))
		.pipe(gulp.dest('.'));
});

gulp.task('scripts', function(){
	return gulp.src(['js/lib/**/*.js', 'js/app/functions.js', 'js/app/app.js', '!js/app/admin.js'])
		.pipe(debug())
		.pipe(sourcemaps.init())
		.pipe(concat('main.js'))
		.pipe(rename('main.min.js'))
		.pipe(uglify())
		.pipe(sourcemaps.write('../maps'))
		.pipe(gulp.dest('js/'));
});

gulp.task('docs', function(){
	return gulp.src(['js/lib/**/*.js', 'js/app/functions.js', 'js/app/app.js', 'js/app/views/*.js', '!js/app/admin.js'])
		.pipe(debug())
		.pipe(concat('jsdoc.md'))
		.pipe(jsdoc2md({
			'global-index-format': 'grouped',
			'partial': ['doc/tmpl/defaultvalue.hbs', 'doc/tmpl/link.hbs', 'doc/tmpl/header.hbs', 'doc/tmpl/params-table.hbs', 'doc/tmpl/properties-list.hbs', 'doc/tmpl/properties-table.hbs', 'doc/tmpl/sig-name.hbs', 'doc/tmpl/sig-link.hbs', 'doc/tmpl/sig-link-parent.hbs', 'doc/tmpl/returns.hbs']
		}))
		.pipe(gulp.dest('./doc'));
});

gulp.task('internationalize', function(){
	return gulp.src(['**/*.php', 'views/*.twig', 'js/app/**/*.js'])
		.pipe(wpPot({
			domain: pkg.text_domain,
			package: pkg.theme_name
		}))
		.pipe(gulp.dest('languages/'));
});

gulp.task('composer', function(done){
	composer({
		'bin': 'bin/composer.phar'
	});
	done();
});

gulp.task('install-image-tools', function(done){
	switch (true){
		case (/^darwin/).test(os.platform()):
			gulp.src('./bin/darwin-*')
				.pipe(rename(function(opt){
					opt.basename = opt.basename.replace(/^darwin-/, '');
					return opt;
				}))
				.pipe(gulp.dest('./bin'));
			break;
		case (/^linux/).test(os.platform()):
			gulp.src('./bin/linux-*')
				.pipe(rename(function(opt){
					opt.basename = opt.basename.replace(/^linux-/, '');
					return opt;
				}))
				.pipe(gulp.dest('./bin'));
			break;
		case (/^win/).test(os.platform()):
			gulp.src('./bin/win-*')
				.pipe(rename(function(opt){
					opt.basename = opt.basename.replace(/^win-/, '');
					return opt;
				}))
				.pipe(gulp.dest('./bin'));
			break;
	}
	done();
});

gulp.task('default', gulp.series('composer', 'sass', 'sass-views', 'scripts', 'internationalize', 'install-image-tools'));

gulp.task('watch', function(){
	gulp.watch(['*.scss', 'css/*.scss'], gulp.series('sass'));
	gulp.watch(['css/views/*.scss'], gulp.series('sass-views'));
	gulp.watch('js/*/*.js', gulp.series('scripts'));
	gulp.watch(['*.php', 'php/*.php', 'php/acf-views/*.php', 'views/*.twig', 'js/app/**/*.js'], gulp.series('internationalize'));
});

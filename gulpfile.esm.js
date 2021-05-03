import gulp from 'gulp';
import fs from 'fs';
import { exec } from 'child_process';
import path from 'path';
import plumber from 'gulp-plumber';
import notify from 'gulp-notify';
import rename from 'gulp-rename';
import lineec from 'gulp-line-ending-corrector';
import wpi18n from 'node-wp-i18n';
import { sprintf } from '@wordpress/i18n';
import potomo from 'gulp-potomo';
import through2 from 'through2';
import gulpIgnore from 'gulp-ignore';
import minifycss from 'gulp-uglifycss';
import semverInc from 'semver/functions/inc';
import zip from 'gulp-zip';

import config from './gulp.config';

import pkg from './package.json';

const RELEASES = ['major', 'minor', 'patch'];

/**
 * Gets CLI args as object.
 */
const getCommandArgs = () => {
	const argList = process.argv;

	const arg = {};
	let a, opt, thisOpt, curOpt;
	for (a = 0; a < argList.length; a++) {
		thisOpt = argList[a].trim();
		opt = thisOpt.replace(/^-+/, '');

		if (opt === thisOpt) {
			// argument value
			if (curOpt) {
				arg[curOpt] = opt;
			}
			curOpt = null;
		} else {
			// argument name
			curOpt = opt;
			arg[curOpt] = true;
		}
	}
	return arg;
};

const errorHandler = (r) => {
	notify.onError('\n\n❌  ===> ERROR: <%= error.message %>\n')(r);
};

const calculateVersion = () => {
	const { type, ver } = getCommandArgs();

	// if the version is passed explicitly, then use that.
	if (ver) {
		return ver;
	}

	// default release.
	let release = 'patch';

	if (type) {
		if (!RELEASES.includes(type)) {
			throw new Error('Unknown release type: ' + type + '\n Usage: gulp release --type minor');
		}
		release = type;
	}
	const currentVersion = pkg.version;

	const newVersion = semverInc(currentVersion, release);
	return newVersion;
};

/**
 * Converts JS POT file to PHP using @wordpress/i18n
 *
 * @param {Function} cb Callback
 * @return {void}
 */
export const jsPotToPhp = (cb) => {
	const cmd = `npx pot-to-php ${config.domainPath + '/' + config.JSPotFilename} ${config.domainPath}/${
		config.textDomain
	}-js-translations.php ${pkg.name}`;

	exec(cmd, (err) => {
		if (err && 1 !== err.code) {
			console.error(`exec error: ${err.message}`);
		}
		cb(err);
	});
};

export const generatePotFile = (done) => {
	const options = {
		domainPath: 'languages/',
		potComments: '',
		potFilename: config.potFilename,
		type: 'wp-plugin',
		cwd: config.srcDir,
		mainFile: `${config.srcDir}/${pkg.name}.php`,
		updateTimestamp: true,
		updatePoFiles: true,
		potHeaders: {
			poedit: true,
			language: 'en_US',
			'X-Poedit-Basepath': '..\n',
			'Plural-Forms': 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n',
			'x-generator': 'node-wp-i18n',
			'X-Poedit-KeywordsList': '__;_e;_x;esc_attr__;esc_attr_e;esc_html__;esc_html_e\n',
			'X-Poedit-SearchPath-0': '.\n',
			'X-Poedit-SearchPathExcluded-0': 'assets\n',
		},
		processPot(pot) {
			let translation;
			const excluded_meta = [
				'Plugin Name of the plugin/theme',
				'Plugin URI of the plugin/theme',
				'Author of the plugin/theme',
				'Author URI of the plugin/theme',
				'Description of the plugin/theme',
			];

			for (translation in pot.translations['']) {
				if (undefined !== pot.translations[''][translation].comments.extracted) {
					if (0 <= excluded_meta.indexOf(pot.translations[''][translation].comments.extracted)) {
						delete pot.translations[''][translation];
					}
				}
			}

			pot.headers['report-msgid-bugs-to'] = config.bugReport;
			pot.headers['last-translator'] = pkg.title;
			pot.headers['language-team'] = pkg.title;
			const today = new Date();
			pot.headers['po-revision-date'] = sprintf(
				'%1$s-%2$s-%3$s %4$s:%5$s%6$s',
				today.getFullYear(),
				('0' + (today.getMonth() + 1)).slice(-2),
				today.getDate(),
				today.getUTCHours(),
				today.getUTCMinutes(),
				today.getTimezoneOffset()
			);

			return pot;
		},
	};

	wpi18n
		.makepot(options)
		.then(function (wpPackage) {
			console.log('POT file saved to ' + path.relative(wpPackage.getPath(), wpPackage.getPotFilename()));
		})
		.catch(function (error) {
			console.log(error);
		})
		.finally(done);
};

export const updateMoFiles = () => {
	return gulp
		.src(['*.po'], { cwd: config.domainPath })
		.pipe(potomo({ verbose: false }))
		.pipe(gulp.dest(config.domainPath));
};

export const i18n = gulp.series(jsPotToPhp, generatePotFile, updateMoFiles);

export const phpFixLints = (pathToFiles) => {
	const pathToPhpCbf = path.resolve(`${config.vendorBin}/phpcbf`);

	const cmd = `${pathToPhpCbf} --standard=${config.PhpStandard} ${pathToFiles}`;

	exec(cmd, (err) => {
		if (err && 1 !== err.code) {
			console.error(`exec error: ${err.message}`);
		}
	});
};

const createVersionUpdateCB = (forFile, version) => {
	let patterns = [],
		versionConst;
	const replaceCB = (match, p1) => match.replace(p1, version);

	switch (forFile) {
		case 'package':
			// replace only the first occurence
			patterns = [/"version":\s*"(\d+\.\d+\.\d+)"/i];
			break;
		case 'readme':
			patterns = [/Stable tag:(?:\*\*)?[\s\t]*(\d+\.\d+\.\d+)/i];
			break;
		case 'mainfile':
			// convert "plugin-name" to "PLUGIN_NAME_VER"
			versionConst = pkg.name.replace('-', '_').toUpperCase() + '_VER';
			patterns = [/Version:\s*(\d+\.\d+\.\d+)/i, new RegExp("'" + versionConst + "',\\s*'(\\d+\\.\\d+\\.\\d+)'")];
			break;
		case 'since-xyz':
			patterns = [/@since[\s\t]*(x\.y\.z)/gi];
			break;
	}
	return through2.obj(function (file, _, cb) {
		if (file.isBuffer()) {
			let contents = file.contents.toString();
			patterns.forEach((regex) => {
				contents = contents.replace(regex, replaceCB);
			});
			file.contents = Buffer.from(contents);
		}
		cb(null, file);
	});
};

export const updateVersion = () => {
	const version = calculateVersion();

	const srcOptions = { base: './' };

	return (
		gulp
			.src(['./package.json', './composer.json'], srcOptions)
			.pipe(createVersionUpdateCB('package', version))
			.pipe(gulp.dest('./'))

			// remove all files from the stream
			.pipe(gulpIgnore.exclude('**/*'))

			// add readme files
			.pipe(gulp.src(['./README.md', config.srcDir + '/README.txt'], srcOptions))
			.pipe(createVersionUpdateCB('readme', version))
			.pipe(gulp.dest('./'))

			// remove all files from the stream
			.pipe(gulpIgnore.exclude('**/*'))

			// add all php files
			.pipe(gulp.src(`${config.srcDir}/**/*.php`, srcOptions))
			.pipe(createVersionUpdateCB('since-xyz', version))
			.pipe(gulp.dest('./'))

			// remove all files from the stream
			.pipe(gulpIgnore.exclude('**/*'))

			// add main file
			.pipe(gulp.src(`${config.srcDir}/${pkg.name}.php`, srcOptions))
			.pipe(createVersionUpdateCB('mainfile', version))
			.pipe(gulp.dest('./'))
	);
};

export const updateChangelog = () => {
	const version = calculateVersion();

	const srcOptions = { cwd: './', base: './' };

	return (
		gulp
			.src(config.srcDir + '/README.txt', srcOptions)
			.pipe(
				through2.obj(function (file, _, cb) {
					if (file.isBuffer()) {
						const regex = /== Changelog ==([\s\S])/i;
						const contents = file.contents.toString().replace(regex, (match, p1) => {
							let changes = fs
								.readFileSync('./changelog.md', 'utf8') // get contents of changelog file
								.match(/(?<=##\sUnreleased)[\s\S]+?(?=##\s?\[\d+\.\d+\.\d+)/i)[0]
								.trim(); // match the changes in Unreleased section
							// Write the changes to release-notes.txt file
							fs.writeFileSync('./release-notes.txt', changes, 'utf8');
							changes = changes
								.replace(/(^|\n)(##.+)/g, '') // remove headings like Enhancements, Bug fixes
								.replace(/\n[\s\t]*\n/g, '\n') // replace empty lines
								.trim(); // cleanup

							const replace = `\n\n= ${version} =\n${changes}\n`;
							return match.replace(p1, replace);
						});
						file.contents = Buffer.from(contents);
					}
					cb(null, file);
				})
			)
			.pipe(gulp.dest('./'))

			// remove all files from the stream
			.pipe(gulpIgnore.exclude('**/*'))

			// add changelog.md
			.pipe(gulp.src('changelog.md', srcOptions))
			.pipe(
				through2.obj(function (file, _, cb) {
					if (file.isBuffer()) {
						const regex = /## (Unreleased)/i;
						const contents = file.contents.toString().replace(regex, (match, p1) => {
							const today = new Date();
							const url = sprintf(
								'https://github.com/manzoorwanijk/%1$s/releases/tag/v%2$s',
								pkg.name,
								version
							);
							const replace = sprintf(
								'[%1$s - %2$s-%3$s-%4$s](%5$s)',
								version,
								today.getFullYear(),
								('0' + (today.getMonth() + 1)).slice(-2),
								today.getDate(),
								url
							);

							return match.replace(p1, `Unreleased\n\n## ${replace}`);
						});
						file.contents = Buffer.from(contents);
					}
					cb(null, file);
				})
			)
			.pipe(gulp.dest('./'))
	);
};

export const styles = () => {
	return gulp
		.src(config.styleSRC, { allowEmpty: true })
		.pipe(plumber(errorHandler))
		.pipe(lineec())
		.pipe(gulp.dest(config.srcDir))
		.pipe(rename({ suffix: '.min' }))
		.pipe(minifycss({ maxLineLen: 10 }))
		.pipe(lineec())
		.pipe(gulp.dest(config.srcDir))
		.pipe(
			notify({
				message: '\n\n✅  ===> STYLES — completed!\n',
				onLast: true,
			})
		);
};

const copyChangelog = () => {
	return gulp.src('./changelog.md', { base: './' }).pipe(lineec()).pipe(gulp.dest(config.srcDir));
};

export const watchPhp = () => {
	const watcher = gulp.watch(config.watchPhp, {
		events: ['change'],
	});

	watcher.on('change', (filePath) => {
		// console.log( `File ${filePath} was updated` );
		phpFixLints(filePath);
	});
};

export const bundle = () => {
	const version = calculateVersion();
	return gulp
		.src([config.srcDir + '/**'], { ignore: config.distignore, base: './' })
		.pipe(
			rename(function (path) {
				// rename "src" to plugin name
				if (path.dirname === '.' && path.basename === 'src') {
					path.basename = pkg.name;
				}
				// replace "src" in all paths wit plugin name
				path.dirname = path.dirname.replace(/^src/, pkg.name);
			})
		)
		.pipe(zip(`${pkg.name}-${version}.zip`))
		.pipe(gulp.dest(config.bundlesDir));
};

export const build = gulp.series(i18n, styles);

export const prerelease = gulp.parallel(build, copyChangelog);

export const release = gulp.series(updateVersion, updateChangelog, prerelease, bundle);

export const dev = gulp.parallel(watchPhp);

export default dev;

/**
 * Gulp Configuration File
 */

import pkg from './package.json';

const srcDir = './src';
const name = pkg.name;

const config = {
	srcDir,
	watchPhp: [srcDir + '/**/*.php', '!' + srcDir + '/assets/**'],
	vendorBin: './vendor/bin',
	PhpStandard: 'phpcs.xml',
	styleSRC: [srcDir + '/**/css/*.css', '!' + srcDir + '/assets/static/**', '!' + srcDir + '/**/*.min.css'],
	styleDest: srcDir,

	// Translation options.
	textDomain: name,
	potFilename: `${name}.pot`,
	JSPotFilename: 'js-translations.pot',
	domainPath: srcDir + '/languages',
	packageName: 'WP Telegram',
	bugReport: 'http://wordpress.org/support/plugin/' + name,
	lastTranslator: 'Manzoor Wani <@manzoorwanijk>',
	team: 'WP Telegram Team',
};

export default config;

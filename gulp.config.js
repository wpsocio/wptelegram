/**
 * Gulp Configuration File
 */
import fs from 'fs';
import pkg from './package.json';

const srcDir = './src';
const name = pkg.name;

const distignore = fs
	.readFileSync('./.distignore', 'utf8')
	.split('\n')
	.filter(Boolean)
	.map((v) => srcDir + v);

const config = {
	srcDir,
	bundlesDir: './bundles',
	distignore,
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
	bugReport: 'https://github.com/manzoorwanijk/' + name,
	lastTranslator: 'Manzoor Wani <@manzoorwanijk>',
	team: 'WP Telegram Team',
};

export default config;

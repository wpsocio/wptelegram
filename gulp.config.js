/**
 * Gulp Configuration File
 */
import fs from 'fs';
import pkg from './package.json';

const srcDir = './src';
const name = pkg.name;

const distignore = fs
	.readFileSync('./.distignore', 'utf8')
	.split(/[\n\r]+/)
	.filter((pattern) => pattern && !pattern.startsWith('#'))
	.map((pattern) => {
		// if the pattern has a dot or a star, we won't consider it a directory
		// I know `.vscode` is a directory ;)
		const isDir = !/[\.\*]/.test(pattern) || pattern.endsWith('/');

		// for directories, match the directory and all files inside it
		return isDir ? `**/{${pattern},${pattern}/**}` : '**/' + pattern;
	});

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
	bugReport: 'https://github.com/wpsocio/' + name,
	lastTranslator: 'WP Socio',
	team: 'WP Telegram Team',
};

export default config;

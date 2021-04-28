module.exports = {
	env: {
		browser: true,
		commonjs: true,
		es6: true,
		node: true,
	},
	extends: ['plugin:prettier/recommended'],
	parserOptions: {
		sourceType: 'module',
		allowImportExportEverywhere: true,
		codeFrame: true,
		ecmaFeatures: {
			templateStrings: true,
		},
		ecmaVersion: 2020,
	},
	globals: {
		jQuery: 'readonly',
	},
};

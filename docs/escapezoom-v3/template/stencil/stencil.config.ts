import type { Config } from '@stencil/core';

/**
 * Build: from theme root, `npx stencil build --config template/stencil/stencil.config.ts`
 * Output `www` → `dist/ez-stencil` (enqueue `build/ez.esm.js` on brands page).
 */
export const config: Config = {
	namespace: 'ezt',
	taskQueue: 'async',
	srcDir: 'components',
	outputTargets: [
		{
			type: 'dist',
			esmLoaderPath: '../loader',
		},
		{
			type: 'www',
			dir: '../../dist/ez-stencil',
			empty: false,
			// Avoid generating a full dev index HTML for theme asset use.
			serviceWorker: null,
		},
	],
};

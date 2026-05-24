import { Config } from '@stencil/core';

export const config: Config = {
  namespace: 'ez-components',
  srcDir: 'stencil',
  outputTargets: [
    {
      type: 'dist',
      dir: '../dist/js',
      esmLoaderPath: './loader',
    },
  ],
  tsconfig: './tsconfig.stencil.json',
};

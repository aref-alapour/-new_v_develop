import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: false,
    sourcemap: true,
    codeSplitting: false,
    rollupOptions: {
      input: resolve(__dirname, 'assets/js/entries/front.js'),
      output: {
        format: 'es',
        entryFileNames: 'front.js',
      },
    },
  },
});

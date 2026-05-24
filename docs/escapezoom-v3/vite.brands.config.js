import { defineConfig } from 'vite';
import { resolve } from 'node:path';

/** Brands directory — Alpine + htmx.org + ez-ajax (self-contained entry). */
export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: false,
    sourcemap: true,
    codeSplitting: false,
    rollupOptions: {
      input: resolve(__dirname, 'assets/js/entries/brands-page.js'),
      output: {
        format: 'es',
        entryFileNames: 'brands-page.js',
      },
    },
  },
});

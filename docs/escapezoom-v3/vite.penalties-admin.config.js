import { defineConfig } from 'vite';
import { resolve } from 'node:path';

/** Product penalties wp-admin — HTMX + Alpine + DaisyUI (admin.css). */
export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: false,
    sourcemap: true,
    codeSplitting: false,
    rollupOptions: {
      input: resolve(__dirname, 'assets/js/entries/product-penalties-admin.js'),
      output: {
        format: 'es',
        entryFileNames: 'product-penalties-admin.js',
      },
    },
  },
});

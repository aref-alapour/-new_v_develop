import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: false,
    sourcemap: true,
    rollupOptions: {
      input: resolve(__dirname, 'assets/js/entries/admin.js'),
      output: {
        format: 'es',
        entryFileNames: 'admin.js',
        inlineDynamicImports: true,
      },
    },
  },
});

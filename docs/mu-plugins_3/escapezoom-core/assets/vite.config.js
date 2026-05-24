import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  plugins: [tailwindcss()],
  build: {
    outDir: '../dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        admin: path.resolve(__dirname, 'css/admin.css'),
        front: path.resolve(__dirname, 'css/front.css'),
      },
      output: {
        assetFileNames: (assetInfo) => {
          const raw = assetInfo.name ?? '';
          const base = raw.includes('-') ? raw.split('-').slice(0, -1).join('-') : raw.replace(/\.[^.]+$/, '');
          if (base === 'admin' || base === 'front') {
            return `css/${base}-bundle.css`;
          }
          return 'assets/[name]-[hash][extname]';
        },
        entryFileNames: (chunkInfo) => {
          const n = chunkInfo.name;
          if (n === 'admin' || n === 'front') return 'css/[name].js';
          return 'js/[name].js';
        },
      },
    },
    cssMinify: true,
  },
});

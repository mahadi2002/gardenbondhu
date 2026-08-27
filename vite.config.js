import { defineConfig } from 'vite';
import { resolve } from 'node:path';

const root = import.meta.dirname;

// Minimal Vite build for GardenBondhu's hand-rolled CSS/JS.
//
// This app's `asset()` helper (app/Core/Helpers.php) already cache-busts
// every asset with `?v=<filemtime>`, so hashed build filenames would just
// be a second, redundant cache-busting mechanism layered on top of the
// first. Instead of hashed names + a manifest.json that the PHP layouts
// would need to parse on every request, Vite is configured to emit fixed,
// predictable filenames straight back into the exact paths the views
// already reference (public/assets/css/app.css, public/assets/js/app.js,
// public/assets/js/diagnose.js). Zero changes to any view/layout file.
//
// Source lives in resources/, build output lives in public/assets/ — the
// two never overlap, so `vite build` never reads its own output.
export default defineConfig({
  publicDir: false, // public/ is served directly by PHP; never let Vite copy it
  build: {
    outDir: resolve(root, 'public/assets'),
    emptyOutDir: false, // fonts/ and img/ also live under public/assets
    assetsInlineLimit: 0,
    minify: true,
    rollupOptions: {
      input: {
        app: resolve(root, 'resources/js/app.js'),
        diagnose: resolve(root, 'resources/js/diagnose.js'),
        style: resolve(root, 'resources/css/app.css'),
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: 'css/app.css',
      },
    },
  },
});

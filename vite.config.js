import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    publicDir: false,
    plugins: [tailwindcss()],
    build: {
        outDir: 'public/assets',
        emptyOutDir: true,
        cssCodeSplit: false,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                'translation-search': 'resources/js/translation-search.js',
            },
            output: {
                entryFileNames: 'js/[name].js',
                assetFileNames: ({ names }) => names.some((name) => name.endsWith('.css'))
                    ? 'css/main.css'
                    : 'assets/[name]-[hash][extname]',
            },
        },
    },
});

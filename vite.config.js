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
            input: 'resources/js/app.js',
            output: {
                entryFileNames: 'js/app.js',
                assetFileNames: ({ names }) => names.some((name) => name.endsWith('.css'))
                    ? 'css/main.css'
                    : 'assets/[name]-[hash][extname]',
            },
        },
    },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: 'spanish-guide.test',
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        // Code splitting optimization
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vendor libraries
                    vendor: ['vue', 'vue-router', 'axios'],
                    // UI library (if we had one)
                    // ui: ['@headlessui/vue', '@heroicons/vue'],
                    // GSAP animations
                    animations: ['gsap'],
                },
            },
        },
        // Optimization settings
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        // Asset optimization
        assetsInlineLimit: 4096, // Inline assets smaller than 4kb
        cssCodeSplit: true, // Split CSS files
        sourcemap: false, // Disable sourcemaps in production for performance
        // Chunk size warning
        chunkSizeWarningLimit: 1000,
    },
    // Performance optimizations
    optimizeDeps: {
        include: ['vue', 'vue-router', 'axios', 'gsap'],
        exclude: ['@vite/client', '@vite/env'],
    },
});
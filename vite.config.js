const { defineConfig } = require('vite');

module.exports = defineConfig({
    build: {
        outDir: 'dist',
        emptyOutDir: false,
        lib: {
            entry: 'resources/js/tollerus.js',
            name: 'Tollerus',
            formats: ['iife'],
            fileName: () => 'tollerus.js',
        },
        rollupOptions: {
            output: {
                inlineDynamicImports: true,
            },
        },
    },
});

const { defineConfig } = require('vite');

module.exports = defineConfig({
    build: {
        outDir: 'dist',
        emptyOutDir: false,
        rollupOptions: {
            input: 'resources/js/tollerus.js',
            output: {
                entryFileNames: 'tollerus.js',
            },
        },
    },
});

const { defineConfig } = require('vite');

module.exports = defineConfig({
    build: {
        outDir: 'dist',
        emptyOutDir: false,
        assetsDir: '',
        manifest: false,
        rollupOptions: {
            input: {
                'tollerus-admin': 'resources/js/tollerus-admin.js',
                'tollerus-public': 'resources/js/tollerus-public.js',
            },
            output: {
                format: 'es',
                entryFileNames: '[name].js',
                inlineDynamicImports: false,
            },
        },
    },
});

import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import {viteStaticCopy} from 'vite-plugin-static-copy'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/pages/event-faq.js'],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                {
                    src: 'node_modules/@tabler/core/dist/img',
                    dest: 'image'
                },
                {
                    src: 'node_modules/@tabler/icons-webfont/dist/fonts',
                    dest: 'fonts'
                }
            ]
        })
    ],
    server: {
        hmr: {
            host: 'localhost',
            protocol: 'ws',
            port: 3000
        }
    },
    build: {
    commonjsOptions: {
        transformMixedEsModules: true
    },
    rollupOptions: {
        output: {
            assetFileNames: (assetInfo) => {
                if (/\.(woff2?|ttf|eot|otf)$/.test(assetInfo.name)) {
                    return 'fonts/[name][extname]';
                }
                return 'assets/[name][extname]';
            }
        }
    }
}

});

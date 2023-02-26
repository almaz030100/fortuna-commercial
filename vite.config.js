import { resolve } from 'path';
import {defineConfig} from "vite";
import autoprefixer from "autoprefixer";

export default defineConfig({
    root: 'src',
    build: {
        outDir: '../dist',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                index: resolve(__dirname, 'src/index.html'),
                thanks: resolve(__dirname, 'src/thanks.html')
            }
        },
        cssCodeSplit: false
    },
    publicDir: 'public',
    css: {
        postcss: {
            plugins: [
                autoprefixer
            ]
        }
    },
    server: {
        host: true,
        port: 3030
    },
    preview: {
        host: true,
        port: 5050
    }
})
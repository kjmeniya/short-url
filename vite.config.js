import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy'
// import { rtlcssPlugin } from './vite-rtlcss-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/custom.css',
                'resources/sass/frontend-accessibility.scss',
                'resources/css/front.css',
                'resources/js/app.js',
                'resources/js/pages/template.js',
                'resources/js/pages/color-modes.js',
                'resources/js/admin/validation/users.js',
                'resources/js/admin/validation/profile.js',
                'resources/js/admin/password-utils.js',
                'resources/js/admin/global-search.js',
                'resources/js/admin/live.js',
                'resources/js/admin/admin-date-filter.js',
                'resources/js/auth/validation.js',
                'resources/js/front.js',
                'resources/js/front/validation/contact.js',
                'resources/js/socket-client.js',
            ],
            refresh: true,
        }),
        // rtlcssPlugin(),
        viteStaticCopy({
            targets: [
                {
                    src: 'resources/images',
                    dest: ''
                },
                {
                    src: ['node_modules/apexcharts/dist/apexcharts.min.js'],
                    dest: 'plugins/apexcharts'
                },
                {
                    src: ['node_modules/bootstrap/dist/js/bootstrap.bundle.min.js'],
                    dest: 'plugins/bootstrap'
                },
                {
                    src: ['node_modules/bootstrap-maxlength/dist/bootstrap-maxlength.min.js'],
                    dest: 'plugins/bootstrap-maxlength'
                },
                {
                    src: ['node_modules/cropperjs/dist/cropper.min.js', 'node_modules/cropperjs/dist/cropper.min.css'],
                    dest: 'plugins/cropperjs'
                },
                {
                    src: ['node_modules/datatables.net/js/dataTables.min.js'],
                    dest: 'plugins/datatables.net'
                },
                {
                    src: ['node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js', 'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css'],
                    dest: 'plugins/datatables.net-bs5'
                },
                {
                    src: ['node_modules/flag-icons/css', 'node_modules/flag-icons/flags'],
                    dest: 'plugins/flag-icons'
                },
                {
                    src: ['node_modules/flatpickr/dist/flatpickr.min.js', 'node_modules/flatpickr/dist/flatpickr.min.css'],
                    dest: 'plugins/flatpickr'
                },
                {
                    src: ['node_modules/jquery/dist/jquery.min.js'],
                    dest: 'plugins/jquery'
                },
                {
                    src: ['node_modules/jquery-validation/dist/jquery.validate.min.js'],
                    dest: 'plugins/jquery-validation'
                },
                {
                    src: ['node_modules/lucide/dist/umd/lucide.min.js'],
                    dest: 'plugins/lucide'
                },
                {
                    src: ['node_modules/perfect-scrollbar/dist/*', 'node_modules/perfect-scrollbar/css/*'],
                    dest: 'plugins/perfect-scrollbar'
                },
                {
                    src: ['node_modules/sweetalert2/dist/sweetalert2.min.js', 'node_modules/sweetalert2/dist/sweetalert2.min.css'],
                    dest: 'plugins/sweetalert2'
                },
                {
                    src: ['node_modules/tinymce/*'],
                    dest: 'plugins/tinymce'
                },
                {
                    src: ['resources/plugins/leaflet/*'],
                    dest: 'plugins/leaflet'
                },
                {
                    src: ['node_modules/socket.io-client/dist/socket.io.min.js'],
                    dest: 'plugins/socket.io'
                },
                {
                    src: ['node_modules/select2/dist/js/select2.min.js', 'node_modules/select2/dist/css/select2.min.css'],
                    dest: 'plugins/select2'
                },
            ]
        }),
    ],
    css: {
        devSourcemap: true,
        preprocessorOptions: {
            scss: {
                silenceDeprecations: ['mixed-decls', 'color-functions', 'global-builtin', 'import'],
            }
        }
    },
});

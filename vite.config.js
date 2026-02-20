import { defineConfig } from 'vite';

export default defineConfig({
	build: {
		rollupOptions: {
			input: {
				'wc-meta-boxes-product': 'src/assets/js/wc-meta-boxes-product.js',
				'wc-meta-boxes-order': 'src/assets/js/wc-meta-boxes-order.js',
				'wc-conditional-rules-js': 'src/assets/js/wc-conditional-rules-js.js',
				'wc-user-order': 'src/assets/js/wc-user-order.js',
				'wc-product-frontend': 'src/assets/js/wc-product-frontend.js',
				'import-export-modal': 'src/assets/js/import-export-modal.js',
			},
			output: {
				entryFileNames: '[name].min.js',
				chunkFileNames: 'chunks/[name]-[hash].min.js',
				dir: 'assets/js',
			},
		},
		minify: 'terser',
	},
});

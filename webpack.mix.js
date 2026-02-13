// webpack.mix.js
let mix = require('laravel-mix');

mix.js('src/assets/js/wc-meta-boxes-product.js', 'assets/js/')
    .sass('src/assets/scss/admin-backend.scss', 'assets/css/')
    .js('src/assets/js/wc-meta-boxes-order.js', 'assets/js/')
    .sass('src/assets/scss/forms.scss', 'assets/css/')
    .js('src/assets/js/wc-conditional-rules-js.js', 'assets/js/')
    .js('src/assets/js/wc-user-order.js', 'assets/js/')
    .sass('src/assets/scss/order-frontend.scss', 'assets/css/')
    .js('src/assets/js/wc-product-frontend.js', 'assets/js/');

import minify from '@node-minify/core';
import cssnano from '@node-minify/cssnano';
import uglifyjs from '@node-minify/uglify-js';

const CSS_BASE = 'assets/css/';
const CSS_FILES = [
  'style',
  'widgets',
  'wpb-style'
];

const JS_BASE = 'assets/js/';
const JS_FILES = [
  'ajax_add_to_cart',
  'checkout_mollie',
  'checkout_paypal',
  'checkout_stripe',
  'checkout',
  'custom.customize',
  'range-control',
  'theme-color',
  'frontend/price-slider'
];

for (let file of CSS_FILES) {
  minify({
    compressor: cssnano,
    input: CSS_BASE+file+'.css',
    output: CSS_BASE+file+'.min.css',
    callback: function (err, min) {
      console.log('concat '+file);
      if (!!err) {
        console.error(err);
      }
    }
  });
}

for (let file of JS_FILES) {
  minify({
    compressor: uglifyjs,
    input: JS_BASE+file+'.js',
    output: JS_BASE+file+'.min.js',
    callback: function (err, min) {
      console.log('concat '+file);
      if (!!err) {
        console.error(err);
      }
    }
  });
}


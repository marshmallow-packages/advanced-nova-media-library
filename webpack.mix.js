let mix = require("laravel-mix");
let path = require("path");

require("./mix");

mix
  .setPublicPath("dist")
  .js("resources/js/field.js", "js")
  .sass("resources/sass/field.scss", "css")
  .vue({
    version: 3,
  })
  .nova("marshmallow/advanced-nova-media-library");

mix.alias({
  "laravel-nova": path.join(
    __dirname,
    "vendor/laravel/nova/resources/js/mixins/packages.js"
  ),
  "laravel-nova": path.join(
    __dirname,
    "../../laravel/nova/resources/js/mixins/packages.js"
  ),
  axios: path.join(__dirname, "node_modules/axios"),
  lodash: path.join(__dirname, "node_modules/lodash"),
  "form-backend-validation": path.join(
    __dirname,
    "node_modules/form-backend-validation"
  ),
  "@babel/plugin-transform-runtime": path.join(
    __dirname,
    "node_modules/@babel/plugin-transform-runtime"
  ),
  "@babel/runtime": path.join(__dirname, "node_modules/@babel/runtime"),
  vuex: path.join(__dirname, "node_modules/vuex"),
  "@inertiajs/inertia": path.join(__dirname, "node_modules/@inertiajs/inertia"),
  // "@": path.join(__dirname, "resources/js/"),
});

// .alias({
//   "laravel-nova": path.join(
//     __dirname,
//     "../../laravel/nova/resources/js/mixins/packages.js"
//   ),
//   axios: path.join(__dirname, "node_modules/axios"),
//  lodash: path.join(__dirname, "node_modules/lodash"),
//   "form-backend-validation": path.join(
//     __dirname,
//     "node_modules/form-backend-validation"
//   ),
//   "@babel/plugin-transform-runtime": path.join(
//     __dirname,
//     "node_modules/@babel/plugin-transform-runtime"
//   ),
//   "@babel/runtime": path.join(__dirname, "node_modules/@babel/runtime"),
//   vuex: path.join(__dirname, "node_modules/vuex"),
//   "@inertiajs/inertia": path.join(
//     __dirname,
//     "node_modules/@inertiajs/inertia"
//   ),
// })

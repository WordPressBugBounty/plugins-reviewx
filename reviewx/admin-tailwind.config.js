
const { join } = require('path');
const  tailwindConfig  = require('./frontend/libs/shared-tailwind/src/tailwind.config');
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    join(__dirname, 'resources/views/storeadmin/**/*.twig'),
  ],
  ...tailwindConfig
};

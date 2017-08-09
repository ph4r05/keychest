/**
 * Created by dusanklinec
 * Boilerplate to first load polyfills and then the app.
 */
'use strict';

const poly = require('./polyinit');
const loadRest = () => {
    require('./app.js');
};

poly.initPh4Polyfills({}).then(loadRest);


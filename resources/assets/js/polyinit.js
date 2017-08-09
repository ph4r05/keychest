/**
 * Created by dusanklinec
 * Polyfill initialization - common routines.
 */
'use strict';

/**
 * Promises polyfill - general
 */
if (typeof Promise === 'undefined') {
    console.log('no promises');
    require('es6-promise').polyfill();
}

function initPh4Polyfills(options){

    const fillFetch = () => new Promise((resolve) => {
        if ('fetch' in window) return resolve();

        require.ensure([], () => {
            require('whatwg-fetch');
            resolve();
        }, 'fetch');
    });

    const fillIntl = () => new Promise((resolve) => {
        if ('Intl' in window) return resolve();

        require.ensure([], () => {
            require('intl');
            require('intl/locale-data/jsonp/en.js');

            resolve();
        }, 'Intl');
    });

    const fillCoreJs = () => new Promise((resolve) => {
        if (
            'startsWith' in String.prototype &&
            'endsWith' in String.prototype &&
            'includes' in Array.prototype &&
            'assign' in Object &&
            'keys' in Object
        ) return resolve();

        require.ensure([], () => {
            require('core-js');
            console.log('core-js loaded');

            resolve();
        }, 'core-js');
    });

    return Promise.all([
        //fillFetch(),
        //fillIntl(),
        fillCoreJs()
    ]);
}

//
// Export
//
module.exports = {
    initPh4Polyfills: initPh4Polyfills
};





/**
 * Gerar uma query string a partir de um objeto ou array Javascript
 * @param {String} obj 
 */
function obj2QueryString (obj) {
    return Object.keys(obj).map(function (k) {
        return k + '=' + obj[k];
    }).join('&');
} // Fim obj2QueryString
/**
 * framework-dlx
 * @version: v1.17.07
 * @author: Diego Lepera
 *
 * Created by Diego Lepera on 2017-07-20. Please report any bug at
 * https://github.com/dlepera88-php/framework-dlx/issues
 *
 * The MIT License (MIT)
 * Copyright (c) 2017 Diego Lepera http://diegolepera.xyz/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/** @preserve
 * The MIT License (MIT) https://github.com/dlepera88-php/framework-dlx/blob/master/LICENSE
 * Copyright (c) 2017 Diego Lepera http://diegolepera.xyz/
 */

/* jshint unused: false */

// Eventos ------------------------------------------------------------------ //
/**
 * Identificar se um determinado possui um determinado evento com um determinado
 * namespace definido
 * @param  {DOM}    objeto  Objeto DOM a ser verificado
 * @param  {String} evento  Nome do evento.namespace
 * @return {Int}            Retorna a quantidade de eventos.namespaces presentes
 * no objeto
 */
function temEventoNamespace(objeto, evento) {
    var contador = 0, namespace;
    
    if (evento.indexOf('.') > -1) {
        var evt_nms = evento.split('.');
        evento = evt_nms[0];
        namespace = evt_nms[1];
    } // Fim if

    if (objeto.length === undefined) {
        if ($._data(objeto, 'events') !== undefined) {
            var _data_events = evento.length > 0 ? $._data(objeto, 'events')[evento] : $._data(objeto, 'events');

            $.each(_data_events, function () {
                if (typeof this === Array) {
                    $.each(this, function () {
                        if (this.namespace === namespace) {
                            return contador++;
                        } // Fim if
                    });
                } else {
                    if (this.namespace === namespace) {
                        return contador++;
                    } // Fim if
                } // Fim if ... else
            });
        } // Fim if
    } else {
        var qtde_objetos = objeto.length;

        for(var i = 0; i < qtde_objetos; i++) {
            if (temEventoNamespace(objeto[i], namespace)) {
                contador++;
            } // Fim if
        } // Fim for
    } // Fim if

    return contador > 0;
} // Fim function temEventoNamespace


/**
 * Adicionar um evento a um objeto realizando verificando se o objeto já possui\
 * esse evento.namespace antes
 * @param  {jQuery}     $dom   Instância jQuery do objeto que receberá esse evento
 * @param  {String}     evento Nome do evento.namespace a ser adicionado
 * @param  {Function}   acao   Função a ser executada durante o evento
 * @return {Void}
 */
function adicionarEvento($dom, evento, acao) {
    $dom.each(function () {
        if (evento.indexOf('.') < 0 || !temEventoNamespace(this, evento)) {
            $(this).on(evento, acao);
        } // Fim if
    });
} // Fim function adicionarEvento

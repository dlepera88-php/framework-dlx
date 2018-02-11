<?php
/**
 * framework-dlx
 * @version: v1.17.07
 * @author: Diego Lepera
 *
 * Created by Diego Lepera on 2017-07-28. Please report any bug at
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

namespace DLX\Ajudantes;


class Diversos {
    /**
     * Carregar informações do $_POST dentro de um determinado objeto
     *
     * @param object $objeto Objeto a ter as informações do $_POST
     */
    public static function post2Objeto($objeto) {
        if (filter_var($_SERVER['REQUEST_METHOD']) === 'POST') {
            foreach ($_POST as $nome => $valor) {
                if (property_exists($objeto, $nome)) {
                    $set = 'set' . Strings::conveter2PSR($nome);
                    $valor = Diversos::urlDecodeRecursivo($valor);

                    method_exists($objeto, $set)
                        ? $objeto->{$set}($valor)
                        : $objeto->{$nome} = $valor;
                } // Fim if
            } // Fim foreach
        } // Fim if
    } // Fim do método post2Objeto


    /**
     * Executar o comando url_decode recursivamente
     *
     * @param mixed $var Variável a ser tratada
     *
     * @return array|bool|float|int|object|string
     */
    public static function urlDecodeRecursivo($var) {
        /*
         * Variáveis vazias não precisam passar pelo "decode"
         */
        if (empty($var)) {
            return $var;
        } // Fim if

        if (is_scalar($var)) {
            return is_string($var) ? urldecode($var) : $var;
        } else {
            $var_decode = array_map(function ($v) {
                return static::urlDecodeRecursivo($v);
            }, (array)$var);

            return is_object($var) ? (object)$var_decode : $var_decode;
        } // Fim if ... else
    } // Fim function urlDecodeRecursivo
} // Fim da classe Diversos

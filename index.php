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

# Máscaras
define('MASK_CPF', '000.000.000-00');
define('MASK_CNPJ', '00.000.000/0000-00');
define('MASK_TELEFONE_CELULAR_8', '(00) 0000-0000');
define('MASK_TELEFONE_CELULAR_9', '(00) 0 0000-0000');
define('MASK_CEP', '00000-000');

# Expressões regulares
define('EXPREG_CPF', '~^(\d{3}\.){2}\d{3}-\d{2}$~');
define('EXPREG_CNPJ', '~^\d{2}(\.\d{3}){2}-\d{2}$~');
define('EXPREG_TELEFONE_GERAL', '~^\(\d{2}\)\s([6-9]\s)?\d{4}-\d{4}$~');
define('EXPREG_CEP', '~^\d{4}-\d{3}$~');

require_once 'dlx/dlx.classe.php';

try {
    $dlx_conf = filter_input_array(INPUT_GET, [
        'dlx-aplicativo' => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_EMPTY_STRING_NULL],
        'dlx-ambiente' => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_EMPTY_STRING_NULL],
        'dlx-url' => ['filter' => FILTER_SANITIZE_STRING, 'flags' => FILTER_FLAG_EMPTY_STRING_NULL]
    ]);
    $__dlx = new DLX($dlx_conf['dlx-aplicativo'], $dlx_conf['dlx-ambiente'], $dlx_conf['dlx-url']);
    $__dlx->executar();
} catch (\DLX\Excecao\DLX $ex) {
    echo $ex->obterMensagem();
} catch (Exception $ex) {
    echo $ex->getMessage();
} // Fim try ... catch

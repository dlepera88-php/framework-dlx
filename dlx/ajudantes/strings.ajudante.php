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


class Strings {
    /**
     * Remover os acentos de uma string
     *
     * @param string $texto Texto a remover os acentos
     *
     * @return string
     */
    public static function removerAcentos($texto) {
        if (mb_detect_encoding($texto) !== 'UTF-8') {
            return $texto;
        } // Fim if

        $acentos = [];

        # Letra A
        $acentos['a'] = '~[áàâã]~u';
        $acentos['A'] = '~[ÁÀÂÃ]~u';

        # Letra E
        $acentos['e'] = '~[éèê]~u';
        $acentos['E'] = '~[ÉÈÊ]~u';

        # Letra I
        $acentos['i'] = '~[íìî]~u';
        $acentos['I'] = '~[ÍÌÎ]~u';

        # Letra O
        $acentos['o'] = '~[óòôõ]~u';
        $acentos['O'] = '~[ÓÒÔÕ]~u';

        $acentos['u'] = '~[úùû]~u';
        $acentos['U'] = '~[ÚÙÛ]~u';

        # Letra C
        $acentos['c'] = '~[ç]~u';
        $acentos['C'] = '~[Ç]~u';

        return preg_replace($acentos, array_keys($acentos), $texto);
    } // Fim do método removerAcentos


    /**
     * Converter uma string para o padrão de nomenclatura PSR
     *
     * @param string $texto Texto a ser convertido
     *
     * @return mixed
     */
    public static function conveter2PSR($texto) {
        return str_replace(
            ' ', '',
            ucwords(
                implode(
                    ' ',
                    explode(
                        '-',
                        implode(
                            ' ',
                            explode(
                                '_',
                                static::removerAcentos($texto)
                            )
                        )
                    )
                )
            )
        );
    } // Fim do método conveter2PSR


    /**
     * Converte uma string no padrão PSR (nomes de variáveis, classes e métodos)
     * em URL
     * @param string $psr String a ser convertida
     */
    public static function PSR2URL($psr) {
        return strtolower(preg_replace('~([a-z])([A-Z])~', '${1}-${2}', $psr));
    } // Fim do método PSR2URL


    /**
     * Converter um valor booleano (0 ou 1, true ou false, Yes ou No) em Sim e Não para que seja melhor compreensível
     * para humanos
     *
     * @param mixed  $bool   Valor booleano a ser convertido
     * @param string $idioma Idioma a ser transcrito
     *
     * @return string
     */
    public static function bool2humano($bool, $idioma = 'pt_BR') {
        $sim_nao = [
            'pt_BR' => ['Não', 'Sim'],
            'en_US' => ['No', 'Yes'],
            'es_ES' => ['No', 'Sí']
        ];

        return array_key_exists($idioma, $sim_nao)
            ? $sim_nao[$idioma][(int)filter_var($bool, FILTER_VALIDATE_BOOLEAN)]
            : sprintf('Idioma <b>%s</b> não suportado', $idioma);
    } // Fim do método bool2humano


    /**
     * Montar uma prévia em um tamanho específico de caracteres
     *
     * @param string $texto       Texto a criar a prévia
     * @param int    $qtde        Quantidade de caracteres a ser considerado para incluir a prévia
     * @param string $reticencias String usada para indicar a continuação do texto
     *
     * @return string
     */
    public static function previaTexto($texto, $qtde = 50, $reticencias = '...') {
        $texto_limpo = trim(preg_replace('~\s{2,}~', ' ', preg_replace('~\</?[\w]+(\s+[\w\d\-_]+\=\"[\w\d\s#\-\:\\/\._;\%\?]+\")*\/?>~', ' ', $texto)));

        return strlen($texto_limpo) <= $qtde
            ? $texto_limpo
            : trim(substr($texto_limpo, 0, $qtde)) . $reticencias;
    } // Fim do método previaTexto


    /**
     * Converter um texto normal para uma URL
     *
     * @param string $texto Texto a ser convertido
     *
     * @return mixed
     */
    public static function humano2URL($texto) {
        return preg_replace(
            '~[\W\s/\.ªº]+~', '-',
            trim(mb_strtolower(static::removerAcentos($texto)))
        );
    } // Fim do método humano2URL


    /**
     * Converter uma string que está no plural para o singular (apenas pt_BR)
     *
     * @param string $texto Texto a ser convertido
     *
     * @return mixed
     */
    public static function plural2singular($texto) {
        return preg_replace(
            ['~res(\s|$)~', '~ões(\s|$)~', '~s(\s|$)~i'],
            ['r${1}', 'ão${1}', '${1}'],
            $texto
        );
    } // Fim do método plural2singular


    /**
     * Retorna o primeiro parâmetro não vazio passado.
     *
     * @return mixed
     */
    public static function naoVazio() {
        $args = func_get_args();

        foreach ($args as $valor) {
            if (!empty($valor)) {
                return $valor;
            } //  Fim if
        } // Fim foreach
    } // Fim do método naoVazio

    /**
     * Formatar uma lista para a leitura humana. Ex:
     * 'item1,item2,item3' => 'item1, item2 e item3'
     * 'item1;item2;item3' => 'item1, item2 e item3'
     *
     * @param string $lista Lista a ser formatada.
     * @param string $separador Caractere que está sendo utilizado para separar os itens da lista.
     * @return string
     */
    public static function lista2Humano($lista, $separador = ',') {
        return preg_replace(["~{$separador}\s*~", '~,\s(\w+)$~u'], [', ', ' e $1'], $lista);
    } // Fim do método lista2Humano
} // Fim do Ajudante Strings

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


class Vetores {
    /**
     * Verificar se um array é associativo
     *
     * @param array $vetor Vetor a ser verificado
     *
     * @return bool
     */
    public static function arrayAssoc(array $vetor) {
        return array_keys($vetor) !== range(0, count($vetor) - 1);
    } // Fim do método


    /**
     * Verificar se um array é multi-dimensional
     *
     * @param array $vetor Vetor a ser verificado
     *
     * @return bool
     */
    public static function arrayMulti(array $vetor) {
        return count($vetor) !== count($vetor, COUNT_RECURSIVE);
    } // Fim do método arrayMulti


    /**
     * Executar a função array_map de forma recursiva e passando a chave como segundo parâmetro da função
     *
     * @param callable $callback Função a ser aplicada nos itens do vetor
     * @param array    $vetor    Vetor a ser percorrido
     *
     * @return array
     */
    public static function arrayMapRecursivo(callable $callback, array $vetor) {
        foreach ($vetor as $chave => $valor) {
            if (is_array($vetor[$chave])) {
                $vetor[$chave] = static::arrayMapRecursivo($callback, $vetor[$chave]);
            } else {
                $vetor[$chave] = call_user_func($callback, $vetor[$chave], $chave);
            } // Fim if ... else
        }

        return $vetor;
    } // Fim do método arrayMapRecursivo


    /**
     * Serializar um vetor para o padrão de atributos HTML
     *
     * @param array $vetor Vetor a ser serializado
     *
     * @return string|null
     */
    public static function array2AtributosHTML(array $vetor) {
        $vetor_tratado = static::arrayMapRecursivo(function ($valor, $atrib) {
            if (isset($valor)) {
                /*
                 * Tratamento de alguns atributos específicos
                 */
                switch (strtolower($atrib)) {
                    case 'pattern':
                        $valor = preg_replace('#(^~|~$)#', '', $valor);
                        break;
                } // Fim switch

                // Se o valor a ser atribuído possui aspas duplas, devo informar o valor do
                // atributo com aspas simples
                return strstr($valor, '"') ? "{$atrib}='{$valor}'" : "{$atrib}=\"{$valor}\"";
            } // Fim if

            return null;
        }, $vetor);

        return implode(' ', $vetor_tratado);
    } // Fim do método array2AtributosHTML


    /**
     * Remover uma ou mais colunas de um array multi-dimensional
     *
     * @param array $vetor   Vetor multi-dimensional a ser verificado
     * @param mixed $colunas Nome ou índice da coluna a ser removida ou ainda um vetor contendo os nomes das colunas
     *
     * @return array|null Retorna false se o parâmetro $v não é um array ou o vetor sem a(s) coluna(s) especificadas
     */
    public static function removerColuna($vetor, $colunas) {
        if (!isset($vetor) || !is_array($vetor)) {
            return false;
        } // Fim if

        if (isset($colunas)) {
            foreach ($vetor as $col_n1 => $v) {
                foreach ((array)$colunas as $col) {
                    if (isset($vetor[$col_n1]) && is_array($vetor[$col_n1])) {
                        unset($vetor[$col_n1][$col]);
                    } // Fim if
                } // Fim foreach
            } // Fim foreach
        } // Fim if

        return $vetor;
    } // Fim do método removerColuna


    /**
     * Converter o valor de uma coluna de um vetor multidimensional como a chave da sua posição
     *
     * @param array  $vetor  Vetor multimensional a ser alterado
     * @param string $coluna Índice ou nome da coluna a ser transformada em chave
     *
     * @return array
     */
    public static function coluna2Chave (array $vetor, $coluna) {
        $retornar_vetor = [];

        foreach ($vetor as $conteudo) {
            $chave = $conteudo[$coluna];
            unset($conteudo[$coluna]);

            if (array_key_exists($chave, $retornar_vetor)) {
                if (!static::arrayMulti($retornar_vetor[$chave])) {
                    $retornar_vetor[$chave] = array_merge([$retornar_vetor[$chave]], [$conteudo]);
                } else {
                    array_push($retornar_vetor[$chave], $conteudo);
                } // Fim if
            } else {
                $retornar_vetor[$chave] = $conteudo;
            } // Fim if
        } // Fim foreach

        return $retornar_vetor;
    } // Fim do método coluna2Chave


    /**
     * Executar uma função recursivamente de acordo com os elementos de um vetor
     *
     * @param array    $vetor
     * @param callable $funcao
     */
    public static function executarRecursivo(array $vetor, callable $funcao) {
        foreach ($vetor as $valor) {
            call_user_func_array($funcao, [$valor]);
        } // Fim foreach
    } // Fim do método executarRecursivo


    /**
     * Procura uma chave em um array e, caso encontrado, retorna o valor correspondente a chave. Caso contrário retorna
     * nulo
     *
     * @param array  $vetor Vetor a ser analisado na busca
     * @param string $busca Valor a ser buscado
     *
     * @return mixed
     */
    public static function buscarChaveRecursivo(array $vetor, $busca) {
        foreach ($vetor as $chave => $valor) {
            if (array_key_exists($busca, $valor)) {
                return $valor[$busca];
            } // Fim if
        } // Fim foreach

        return null;
    } // Fim do método buscarRecursivo


    /**
     * Extrair elementos de acordo com o nome da chave
     *
     * @param array         $vetor  Vetor com os elementos a serem extraídos
     * @param array         $chaves Vetor com os nomes da chaves a serem extraídas
     * @param callable|null $filtro Filtro a ser aplicado aos valores do elemento através da função array_filter
     *
     * @return array
     */
    public static function extrairElementos(array $vetor, array $chaves = [], callable $filtro = null) {
        $elementos = [];

        foreach ($chaves as $key) {
            if (array_key_exists($key, $vetor)) {
                $elementos += $vetor[$key];
            } // Fim if
        } // Fim if

        return isset($filtro) ? array_filter($elementos, $filtro) : $elementos;
    } // Fim do método extrairElementos


    /**
     * Incluir uma coluna sequencial no vetor multidimensional
     *
     * @param array  $vetor Vetor para incluir a coluna
     * @param string $chave Nome da coluna / chave
     *
     * @return array
     */
    public static function adicionarSequencial(array $vetor, $chave = 'sequencia') {
        $sequencia = 0;

        return array_map(function ($v) use ($chave) {
            global $sequencia;
            $sequencia = $sequencia + 1;

            return [$chave => $sequencia] + $v;
        }, $vetor);
    } // Fim do método adicionarSequencial
} // Fim do Ajudante Vetores

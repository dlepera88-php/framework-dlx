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


class HTMLLista {
    const HTML_CELULA_TITULO_TH = '<th %s>%s</th>';
    const HTML_CELULA_TITULO_TD = '<td %s>%s</td>';

    /**
     * @var array Armazenamento das configurações de lista
     */
    protected static $conf_listas = [
        'registros' => [
            'lista'     => ['class' => 'lista-registros'],
            'cabecalho' => ['class' => 'bloco-titulos'],
            'conteudo'  => ['class' => 'bloco-registros'],
            'rodape'    => ['class' => 'bloco-rodape'],
            'linhas'    => ['class' => 'registro'],
            'subtitulo' => ['class' => 'subtitulo'],
            'celula'    => ['class' => 'dado']
        ]
    ];

    private static $conf_atual;


// Tabelas ---------------------------------------------------------------------------------------------------------- //
    public static function novaLista($nome, array $conf) {
        static::$conf_listas[filter_var($nome)] = filter_var($conf, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    } // Fim do método novaLista


    /**
     * Iniciar uma lista (TABLE)
     *
     * @param string $conf   Nome da configuração a ser usada
     * @param array  $params Vetor com os atributos adicionais a serem colocados na lista
     *
     * @return string
     */
    public static function inicioLista($conf, array $params = []) {
        static::$conf_atual = static::$conf_listas[$conf];

        return '<table ' . Vetores::array2AtributosHTML(array_replace_recursive(static::$conf_atual['lista'], $params)) . '>';
    } // Fim do método inicioLista


    public static function fimLista() {
        return '</table>';
    } // Fim do método fimLista


// Cabeçalho da lista ----------------------------------------------------------------------------------------------- //
    /**
     * Gera a a abertura da TAG THEAD
     *
     * @param array $params [opcional] Outros atributos do cabeçalho
     *
     * @return string
     */
    public static function inicioCabecalho(array $params = []) {
        return '<thead ' . Vetores::array2AtributosHTML(array_replace_recursive(static::$conf_atual['cabecalho'], $params)) . '>';
    } // Fim do método inicioCabecalho


    /**
     * Gera o fechamento da TAG THEAD
     *
     * @return string
     */
    public static function fimCabecalho() {
        return '</thead>';
    } // Fim do método fimCabecalho


    /**
     * Tratar uma string para que seja compativel com um ID de título
     *
     * @param string $id ID a ser tratado
     *
     * @return string
     */
    public static function gerarIDTitulo($id) {
        return strtolower(
            preg_replace(
                ['~^(th\-|)~', '~\s+~', '~[!?\.]+~'],
                ['th-', '-', ''],
                Strings::removerAcentos(filter_var($id))
            )
        );
    } // Fim do método gerarIDTitulo


    /**
     * Gera uma célula do tipo título de tabela (TH)
     *
     * @param string $id     ID do título
     * @param string $texto  Conteúdo da célula
     * @param array  $params [opcional] Outros atributos do título
     *
     * @return string
     */
    public static function celulaTitulo($id, $texto, array $params = []) {
        $conf = array_replace_recursive(static::$conf_atual['celula'], $params);
        $conf['id'] = static::gerarIDTitulo($id);

        return sprintf(static::HTML_CELULA_TITULO_TH, Vetores::array2AtributosHTML($conf), filter_var($texto));
    } // Fim do método celulaTitulo


// Conteúdo --------------------------------------------------------------------------------------------------------- //
    /**
     * Gera a a abertura da TAG TBODY
     *
     * @param array $params [opcional] Outros atributos do cabeçalho
     *
     * @return string
     */
    public static function inicioConteudo(array $params = []) {
        return '<tbody ' . Vetores::array2AtributosHTML(array_replace_recursive(static::$conf_atual['conteudo'], $params)) . '>';
    } // Fim do método inicioConteudo


    /**
     * Gera o fechamento da TAG TBODY
     * @return string
     */
    public static function fimConteudo() {
        return '</tbody>';
    } // Fim do método fimConteudo


    /**
     * Gera uma célula de tabela (TD)
     *
     * @param string $th     ID do título associado a esse campo
     * @param string $texto  Conteúdo da célula
     * @param array  $params [opcional] Outros atributos do título
     *
     * @return string
     */
    public static function celulaComum($th = null, $texto = null, array $params = []) {
        $conf = array_replace_recursive(static::$conf_atual['celula'], $params);

        if (isset($th)) {
            $conf['headers'] = static::gerarIDTitulo($th);
            $conf['data-th'] = $th;
        } // Fim if

        return sprintf(static::HTML_CELULA_TITULO_TD, Vetores::array2AtributosHTML($conf), filter_var($texto));
    } // Fim do método celulaComum


// Rodapé ----------------------------------------------------------------------------------------------------------- //
    /**
     * Gera a a abertura da TAG TFOOT
     *
     * @param array $params [opcional] Outros atributos do cabeçalho
     *
     * @return string
     */
    public static function inicioRodape(array $params = []) {
        return '<tfoot ' . Vetores::array2AtributosHTML(array_replace_recursive(static::$conf_atual['rodape'], $params)) . '>';
    } // Fim do método inicioRodape


    /**
     * Gera o fechamento da TAG TFOOT
     *
     * @return string
     */
    public static function fimRodape() {
        return '</tfoot>';
    } // Fim do método fimRodape


// Linhas ----------------------------------------------------------------------------------------------------------- //
    /**
     * Gera a abertura da linha (TR)
     *
     * @param array $params [opcional] Outros atributos da linha
     *
     * @return string
     */
    public static function inicioLinha(array $params = []) {
        return '<tr ' . Vetores::array2AtributosHTML(array_replace_recursive(static::$conf_atual['linhas'], $params)) . '>';
    } // Fim do método inicioLinha


    /**
     * Gera o fechamento da TAG TR
     *
     * @return string
     */
    public static function fimLinha() {
        return '</tr>';
    } // Fim do método cabecalho


// Sumário ---------------------------------------------------------------------------------------------------------- //
    /**
     * Gera um trecho HTML do subtitulo de uma tabela, usando a TAG CAPTION
     *
     * @param string $texto  Conteúdo do subtitulo
     * @param array  $params [opcional] Outros atributos do subtitulo
     *
     * @return string
     */
    public static function subtitulo($texto, array $params = []) {
        return '<caption ' . Vetores::array2AtributosHTML(array_replace_recursive(static::$conf_atual['subtitulo'], $params)) .
        '>' . filter_var($texto) . '</caption>';
    } // Fim do método sumario
} // Fim do Ajudante HTMLLista

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

use DLX\Ajudantes\Visao as AjdVisao;

class HTMLLinks {
    const HTML_LINK = '<a %s>%s</a>';

    public static $classe = 'link-person';

    private static $conf_links = [
        'desconhecido' => ['class' => 'com-icone -desconhecido'],
        'detalhes'     => ['class' => 'com-icone -detalhes'],
        'inserir'      => ['class' => 'com-icone -inserir'],
        'editar'       => ['class' => 'com-icone -editar'],
        'excluir'      => [
            'data-ajax' => true,
            // 'data-ajax-msg' => TXT_AJAX_EXCLUINDO_REGISTROS, TAREFA: Verificar msg AJAX do link 'Excluir'
            'data-acao' => 'excluir-registro',
            'class'     => 'com-icone -excluir'
        ]
    ];


    /**
     * Incluir uma nova configuração de link
     *
     * @param string $nome Nome do link
     * @param array  $conf Configuração do link
     */
    public static function novoLink($nome, array $conf) {
        static::$conf_links[filter_var($nome, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL)] = filter_var($conf, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    } // Fim do método novoLink


    /**
     * Selecionar a configuração correspondente
     *
     * @param string $conf Nome / chave da configuração a ser selecionada
     *
     * @return mixed
     */
    private static function selecionarConfiguracao($conf) {
        return array_key_exists($conf, static::$conf_links)
            ? static::$conf_links[$conf]
            : static::$conf_links['desconhecido'];
    } // Fim do método selecionarConfiguracao


    /**
     * Gerar o trecho HTML correspondente a um determinado tipo de link
     *
     * @param string      $conf       Nome da configuração a ser utilizada
     * @param string      $href       Referência para onde esse link redirecionará o usuário
     * @param string      $texto      Texto de exibição do link
     * @param string|null $title_attr Valor a ser atribuído para o atributo title
     * @param array       $params     [opcional] Outros atributos do link
     * @param string      $abrir_em   Define de que forma as opções da lista (mostrar detalhes, inserir, editar e
     *                                excluir) serão exibidas
     *
     * @return string
     */
    public static function link($conf, $href, $texto = '', $title_attr = null, array $params = [], $abrir_em = 'normal') {
        $conf = array_replace_recursive(static::selecionarConfiguracao($conf), $params);
        $href = filter_var($href);

        switch ($abrir_em) {
            case 'popup':
                $conf['href'] = 'javascript:';
                $conf['data-acao'] = 'carregar-form';
                $conf['data-acao-param-html'] = $href;
                break;

            case 'normal':
            default:
                $conf['href'] = $href;
        } // Fim switch

        if (isset($title_attr)) {
            $conf['title'] = filter_var($title_attr);
        } // Fim if

        // Atribuir a classe padrão para o link
        if (array_key_exists('class', $conf)) {
            $conf['class'] .= ' ' . static::$classe;
        } else {
            $conf['class'] = static::$classe;
        } // Fim if

        return sprintf(static::HTML_LINK, Vetores::array2AtributosHTML($conf), filter_var($texto));
    } // Fim do método link


    /**
     * Percorrer um array e criar os links de acordo com a configuração contida em cada item
     *
     * @param array         $links Array multidimensional de configurações de links
     * @param callable|null $func  Função a ser executada para editar o HTML dos links
     *
     * @return string
     */
    public static function linkArray(array $links, callable $func = null) {
        $_links = '';

        if (Vetores::arrayMulti($links)) {
            foreach ($links as $tipo => $conf) {
                if (isset($conf) && is_array($conf)) {
                    $_links .= HTMLLinks::link(
                        $tipo, $conf['url'],
                        array_key_exists('texto', $conf) ? $conf['texto'] : null,
                        array_key_exists('title', $conf) ? $conf['title'] : null,
                        array_key_exists('outros-params', $conf) ? $conf['outros-params'] : [],
                        array_key_exists('abrir-links-em', $conf) ? $conf['abrir-links-em'] : 'normal'
                    );
                } // Fim if
            } // Fim foreach
        } // Fim if

        return isset($func) ? call_user_func_array($func, [$_links]) : $_links;
    } // Fim do método linkArray
} // Fim do Ajudante HTMLLinks

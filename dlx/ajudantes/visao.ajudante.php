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


use DLX\Classes\Controle;
use DLX\Classes\Roteador;
use DLX\Excecao\DLX as DLXExcecao;

class Visao {
    public static $idiomas = [];


    /**
     * Extrair conteúdo HTML de uma determinada área de layout
     * @param string $conteudo Conteúdo HTML
     * @param string $area     Nome da área a ser extraída
     * @return string
     */
    public static function extrairAreaLayout($conteudo, $area) {
        if (preg_match("~\[{$area}\]~", $conteudo)) {
            preg_match_all("~(?s)\[{$area}\](.*?)\[/{$area}\]~", $conteudo, $html);
            
            return implode("\n", $html[1]);
        } // Fim if

        return '';
    } // Fim do método extrairAreaLayout


    /**
     * Identificar a base HTML a ser usada
     *
     * @return string
     */
    public static function identificarBaseHTML() {
        $conf_aplicativo = \DLX::$dlx->config('aplicativo');
        return "{$conf_aplicativo['raiz']}{$conf_aplicativo['home']}";
    } // Fim if


    /**
     * Montar o host completo do sistema para acesso externo
     *
     * @return string
     */
    public static function hostCompleto() {
        return strtolower(
            preg_replace(
                '~\/[0-9\.]+$~',
                '',
                filter_var($_SERVER['SERVER_PROTOCOL'])
            )
        ) . '://' . filter_var($_SERVER['HTTP_HOST']) . static::identificarBaseHTML();
    } // Fim do método hostCompleto


    /**
     * Identificar caminho relativo para voltar a raíz do sistema, com base no diretório home do aplicativo atual
     *
     * @return string
     */
    public static function diretorioRelativo() {
        return str_repeat(
            '../',
            count(
                array_filter(
                    explode('/', \DLX::$dlx->config('aplicativo', 'home')),
                    function ($v) {
                        return !empty($v);
                    }
                )
            )
        );
    } // Fim do método diretorioRelativo


// Idiomas ---------------------------------------------------------------------------------------------------------- //
    /**
     * Incluir um trecho de texto ou uma palavra com a sua tradução
     *
     * @param string $texto    Texto base que será usado para fazer a tradução
     * @param string $traducao Texto traduzido
     * @param string $idioma   Sigla do idioma
     * @param string $dominio  Nome o domínio onde se encontram as traduções
     */
    public static function adicionarTraducao($texto, $traducao = '', $idioma = 'pt_BR', $dominio = 'global') {
        if (preg_match('~^[a-z]{2}_[A-Z]{2}$~', $idioma)) {
            static::$idiomas[$dominio][$idioma][filter_var($texto)] = filter_var($traducao);
        } // Fim if
    } // Fim do método adicionarTraducao


    /**
     * Traduzir um determinado texto com base no pacote de idiomas carregado
     *
     * @param string $texto   Texto a ser traduzido
     * @param string $dominio Nome do domínio a ser considerado para essa tradução
     * @param null   $idioma  Sigla do idioma a traduzir. Quando não informado o sistema tenta usar a preferência do
     *                        usuário ou o idioma que foi configurado no arquivo de configurações
     *
     * @return mixed
     */
    public static function traduzirTexto($texto, $dominio = 'global', $idioma = null) {
        if (empty($idioma)) {
            $idioma = Sessao::dadoSessao(
                'idioma_sigla',
                FILTER_VALIDATE_REGEXP,
                \DLX::$dlx->config('aplicativo', 'idioma'),
                ['options' => ['regexp' => '~^[a-z]{2}_[A-Z]{2}$~']]
            );
        } // Fim if

        return array_key_exists($dominio, static::$idiomas) && array_key_exists($idioma, static::$idiomas[$dominio]) &&
        array_key_exists($texto, static::$idiomas[$dominio][$idioma]) && !empty(static::$idiomas[$dominio][$idioma][$texto])
            ? static::$idiomas[$dominio][$idioma][$texto]
            : $texto;
    } // Fim do método adicionarTraducao


    /**
     * Acoplar conteúdo HTML
     * @param string $url URL que contém o conteúdo HTML desejado ou caminho
     * relativo para uma funcionalidade do sistema
     * @return mixed
     */
    public static function acoplarHTML($url) {
        /* return (preg_match('~^(http|https)~', $url) && !preg_match('~^' . static::hostCompleto() . '~', $url))
            ? static::acoplarHTMLExterno($url)
            : static::acoplarHTMLInterno($url); */
        return static::acoplarHTMLExterno($url);
    } // Fim do método acoplarHTML


    public static function acoplarHTMLInterno($controle, $acao, $params = []) {
        // TODO: Desenvolver uma maneira de acoplar melhor conteúdos internos
        $controle->visao->setExibirAuto(false);
        return call_user_func_array([$controle, $acao], $params);
    } // Fim do método acoplarHTMLInterno


    private static function acoplarHTMLExterno($url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $reabrir_sessao = false;

        if (Sessao::sessaoAtiva()) {
            curl_setopt($curl, CURLOPT_COOKIE, getallheaders()['Cookie']);

            /*
             * Para a requisição do cURL conseguir iniciar a sessão é necessário
             * fechá-la. O arquivo de sessão é bloqueado pelo PHP quando a sessão
             * está ativa
             */
            session_write_close();

            $reabrir_sessao = true;
        } // Fim if

        $html = curl_exec($curl);
        curl_close($curl);

        if ($reabrir_sessao) {
            Sessao::iniciarSessao(\DLX::$dlx->config('autenticacao', 'nome'), null, true);
        } // Fim if

        return $html;
    } // Fim do método acoplarHTMLExterno
} // Fim do Ajudante Visao

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

namespace Geral\Controles;

use DLX\Ajudantes\Evento;
use DLX\Ajudantes\Sessao;
use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Classes\Visao;

abstract class BaseControle {
    use Evento;

    /**
     * @var \DLX\Classes\Visao Instância da classe Visao que será utilizada para
     * a exibição de conteúdo HTML
     */
    public $visao;

    /**
     * @var object Objeto do tipo modelo para esse controle
     */
    protected $modelo;

    /**
     * @var callable Função para verificar o permissionamento do usuário logado
     */
    protected static $verificar_perm;

    /**
     * @return object
     */
    public function getModelo() {
        return $this->modelo;
    }


    /**
     * @param object $modelo
     */
    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }


    /**
     * BaseControle constructor.
     *
     * @param string   $diretorio_visao Diretório de fontes para a visão
     * @param callable $verificar_perm  Função para verificação de permissionamento
     */
    public function __construct($diretorio_visao = '.') {
        # Instânciar um objeto de Visao
        $this->visao = new Visao($diretorio_visao, array_key_exists('dlx-mestra', $_GET) ? filter_input(INPUT_GET, 'dlx-mestra') : 'padrao');

        # Parâmetros
        $this->visao->adicionarParam('conf:base-html', AjdVisao::identificarBaseHTML());
        $this->visao->adicionarParam('conf:host-completo', AjdVisao::hostCompleto());

        $config_autent = \DLX::$dlx->config('autenticacao');

        if ((bool)$config_autent !== false) {
            if ((array_key_exists('ignorar-classes', $config_autent) && !in_array(get_called_class(), $config_autent['ignorar-classes'])) || !array_key_exists('ignorar-classes', $config_autent)) {
                $this->iniciarAutenticacao();
            } // Fim if
        } // Fim if
    } // Fim do método __construct


// Interface com o usuário -------------------------------------------------- //
    /**
     * Exibir a última mensagem ao usuário
     *
     * @param string $mensagem Mensagem a ser exibida
     * @param string $tipo     Tipo de mensagem
     * @param string $como     Informa como a mensagem deverá ser exibida:
     *                         JSON: exibe um objeto JSON com as informações
     *                         TEXTO: exibe apenas a mensagem em formato de texto puro, sem formatação
     *                         HTML: exibe a mensagem em formato HTML e formatado de acordo com a classe CSS
     *                         .mostrar-msg
     * @param object|array $infos_adicionais Informações adicionais a serem exibidas. Só funciona caso a mensagem
     * esteja sendo exibida como JSON
     */
    public function mostrarMensagemUsuario($mensagem, $tipo = '-erro', $como = 'json', $infos_adicionais = []) {
        $this->visao->mostrarMensagemUsuario($mensagem, $tipo, $como, $infos_adicionais);
    } // Fim do método mostrarMensagemUsuario


// Autenticação ------------------------------------------------------------- //
    /**
     * @param callable|null $verificar_perm Função utilizada para verificar se o
     * usuário logado tem permissão para executar determinada ação.
     *
     * @throws \DLX\Excecao\DLX
     */
    protected function iniciarAutenticacao() {
        $config_autent = \DLX::$dlx->config('autenticacao');
        self::$verificar_perm = is_callable($config_autent['verificar-perm'])
            ? $config_autent['verificar-perm']
            : function () { return true; };

        # Tentar recuperar a sessão
        Sessao::iniciarSessao($config_autent['nome'], null, true);

        $this->adicionarEvento('antes', '*', function () use ($config_autent) {
            $config_aplicativo = \DLX::$dlx->config('aplicativo');

            /*
             * Verificar se a sessão está ativa e, em seguida, tentar restaurar a sessão. Caso não funcione o usuário será
             * redirecionado para a página de login
             */
             if (!Sessao::sessaoAtiva()) {
                if (!Sessao::iniciarSessao($config_autent['nome'], null, true)) {
                    \DLX::$dlx->redirecionar(str_replace('%home%', $config_aplicativo['home'], $config_autent['url-login']));
                    die();
                } // Fim if
            } // Fim if

            /*
             * Verificar se o usuário logado tem permissão para executar essa ação
             */
            if (!call_user_func_array([$this, 'verificarPerm'], func_get_args())) {
                \DLX::$dlx->redirecionar('erro-http/403');
                die();
            } // Fim if
        });
    } // Fim do método iniciarAutenticacao


    /**
     * Verificar permissionamento
     * @param string $controle Controle a ser verificado
     * @param string $acao Ação a ser executada
     * @return bool
     */
    public function verificarPerm($controle, $acao) {
        return is_callable(static::$verificar_perm)
            ? call_user_func_array(static::$verificar_perm, [$controle, $acao])
            : true;
    } // Fim do método verificarPerm


// Outros ------------------------------------------------------------------- //
    // TODO: Função para validar a versão do framework
    public function validarVersaoDLX($versao_requerida) {
        var_dump(\DLX::$dlx->versao(), $versao_requerida);
    } // Fim do método validarVersaoDLX
} // Fim do controle BaseControle

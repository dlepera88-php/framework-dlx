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

namespace Comum\Modelos;


use DLX\Ajudantes\DataHora;
use DLX\Ajudantes\Evento;
use DLX\Ajudantes\Sessao;
use Geral\Modelos\BaseModeloRegistro;
use Geral\Modelos\RegistroEdicao;

class LogRegistro extends BaseModeloRegistro {
    use RegistroEdicao;

    /**
     * @var string Nome da tabela onde houve a alteração do registro
     */
    protected $tabela;

    /**
     * @var string PK identificadora do registro. Em casos de PK composta a mesma será gravada em formato string com as
     *      informações separadas por ponto e vírgula (;)
     */
    protected $regpk;

    /**
     * @var string Letra que identifica a ação que foi realizada nesse registro:
     *             A: Adicionado
     *             E: Editado
     *             X: Exclído
     */
    protected $acao;

    /**
     * @var string Data e hora da modificação
     */
    protected $data;

    /**
     * @var int ID do usuário que fez a modificação. Só será preenchido caso haja um usuário autenticado
     */
    protected $usuario;

    /**
     * @var string Nome do usuário que fez a modificação. Só será preenchido caso haja um usuário autenticado
     */
    protected $nome = 'Desconhecido';

    /**
     * @var string Endereço de IP da máquina onde se originou a modificação no registro. Ex: 192.168.0.100
     */
    protected $ip;

    /**
     * @var string Identificação do agente / navegador onde foi realizada a modificação
     */
    protected $agente;


    /**
     * @return string
     */
    public function getTabela() {
        return $this->tabela;
    }


    /**
     * @param string $tabela
     */
    public function setTabela($tabela) {
        $this->tabela = filter_var($tabela, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return string
     */
    public function getRegpk() {
        return $this->regpk;
    }


    /**
     * @param string $regpk
     */
    public function setRegpk($regpk) {
        $this->regpk = filter_var($regpk, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return string
     */
    public function getAcao() {
        return $this->acao;
    }


    /**
     * @param string $acao
     */
    public function setAcao($acao) {
        $this->acao = filter_var($acao, FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '~(A|E|X)~'],
            'flags'   => FILTER_NULL_ON_FAILURE
        ]);
    }


    /**
     * @param string|null $formato Formato de data a ser aplicado na exibição dessa data
     *
     * @return string
     */
    public function getData($formato = 'd/m/Y H:i') {
        return DataHora::formatar($this->data, $formato);
    }


    /**
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }


    /**
     * @return int
     */
    public function getUsuario() {
        return $this->usuario;
    }


    /**
     * @param int $usuario
     */
    public function setUsuario($usuario) {
        $this->usuario = filter_var($usuario, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
            'flags'   => FILTER_NULL_ON_FAILURE
        ]);
    }


    /**
     * @return string
     */
    public function getNome() {
        return $this->nome;
    }


    /**
     * @param string $nome
     */
    public function setNome($nome) {
        $this->nome = filter_var(empty($nome) ? 'Desconhecido' : $nome, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return string
     */
    public function getIp() {
        return $this->ip;
    }


    /**
     * @param string $ip
     */
    public function setIp($ip) {
        $this->ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_NULL_ON_FAILURE);
    }


    /**
     * @return string
     */
    public function getAgente() {
        return $this->agente;
    }


    /**
     * @param string $agente
     */
    public function setAgente($agente) {
        $this->agente = filter_var($agente);
    }


    public function __construct() {
        parent::__construct('dlx_paineldlx_registros_logs', 'log_registro_');

        /*
         * Completar as informações para incluir no log
         */
        $this->adicionarEvento('antes', 'salvar', function () {
            # Identificar o IP da máquina
            $this->setIp(filter_var($_SERVER['REMOTE_ADDR']));

            # Identificar o agente que está sendo utilizado
            $this->setAgente(filter_var($_SERVER['HTTP_USER_AGENT']));

            # Obter a data atual
            $this->setData(date(\DLX::$dlx->config('bd', 'formato-data')['completo']));

            /*
             * Se houver uma sessão ativa, obter o ID e o nome do usuário logado
             */
            if (Sessao::sessaoAtiva()) {
                $this->setUsuario(Sessao::dadoSessao('usuario_id'));
                $this->setNome(Sessao::dadoSessao('usuario_nome'));
            } // Fim if
        });
    } // Fim do método __construct
}

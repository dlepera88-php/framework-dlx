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

namespace DLX\Classes;

use DLX\Ajudantes\Evento;
use DLX\Excecao\DLX as DLXExcecao;

class Controle {
    use Evento;

    /**
     * @var string Nome do aplicativo
     */
    protected $aplicativo;

    /**
     * @var string Nome do módulo
     */
    protected $modulo;

    /**
     * @var string Nome do controle
     */
    protected $controle;

    /**
     * @var string Nome da ação / método a ser executada
     */
    protected $acao;

    /**
     * @var array Parâmetros a serem passados para o método
     */
    protected $params = [];


    /**
     * @return string
     */
    public function getAplicativo() {
        return $this->aplicativo;
    }


    /**
     * @param string $aplicativo
     */
    public function setAplicativo($aplicativo) {
        $this->aplicativo = str_replace(' ', '', ucwords(str_replace('-', ' ', filter_var($aplicativo, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL))));
    }


    /**
     * @return string
     */
    public function getModulo() {
        return $this->modulo;
    }


    /**
     * @param string $modulo
     */
    public function setModulo($modulo) {
        $this->modulo = str_replace(' ', '', ucwords(str_replace('-', ' ', filter_var($modulo, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL))));
    }


    /**
     * @return string
     */
    public function getControle() {
        return $this->controle;
    }


    /**
     * @param string $controle
     */
    public function setControle($controle) {
        $this->controle =
            (string)(!empty($this->aplicativo) ? "{$this->aplicativo}\\" : '') .
            (string)(!empty($this->modulo) ? "{$this->modulo}\\" : '') .
            'Controles\\' . filter_var($controle, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
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
        $this->acao = filter_var($acao, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }


    /**
     * @param array $params
     */
    public function setParams($params) {
        $this->params = filter_var($params, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE);
    }


    /**
     * Controle constructor.
     * @param string $aplicativo Nome do aplicativo onde o modulo está instalado
     * @param string $modulo     Nome do módulo
     * @param string $controle   Nome do controle a ser executado
     * @param string $acao       Nome do método / ação a ser executada
     * @param array  $params     Array associativo contendo os parâmetros a serem passados para o Controle
     */
    public function __construct($aplicativo, $modulo, $controle, $acao, array $params = []) {
        $this->setAplicativo($aplicativo);
        $this->setModulo($modulo);
        $this->setControle($controle);
        $this->setAcao($acao);
        $this->setParams($params);
    } // Fim do método __construct


    /**
     * Validar o controle
     * Verificar se o controle foi carregado e se o método / ação existe dentro
     * dele
     * @return bool Retorna true se o controle e ação são válidos ou false caso
     * contrário
     */
    public function validar() {
        return class_exists($this->controle) && method_exists($this->controle, $this->acao);
    } // Fim do método validar


    /**
     * Exceutar o controle solicitado
     * @return mixed
     * @throws DLXExcecao
     */
    public function executar() {
        if (!$this->validar()) {
            $this->setAplicativo(null);
            $this->setModulo('Comum');
            $this->setControle('ErroHTTP');
            $this->setAcao('mostrarErro');
            $this->setParams(['status_http' => 404, 'pg_mestra' => 'erro']);
        } // Fim if */

        return $this->chamarMetodo(new $this->controle(), $this->acao, (array)$this->params);
    } // Fim do método executar


    /**
     * Chamar um determinado método de um controle
     *
     * @param mixed  $classe Instância da classe
     * @param string $metodo Nome do método a ser executado
     * @param array  $args   Vetor associativo dos parâmetros a serem passado ao método
     *
     * @return mixed
     */
    public function chamarMetodo($classe, $metodo, array $args = []) {
        $rfx_m = new \ReflectionMethod($classe, $metodo);
        $params = array_map(
            function (&$v) {
                return (string)$v->name;
            },
            $rfx_m->getParameters()
        );
        $pass = [];

        foreach ($params as $p) {
            if (array_key_exists($p, $args)) {
                $pass[] = $args[$p];
            } // Fim if
        } // Fim foreach

        return call_user_func_array([$classe, $metodo], $pass);
    } // Fim do método chamarMetodo
} // Fim da classe Controle

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

use DLX\Ajudantes\Sessao;
use DLX\Excecao\DLX as DLXExcecao;

class Roteador {
    /**
     * @var array Armazenamentos de todas as rotas carregadas
     *            Vetor bidimensional no seguinte formato: [':metodo'][':rota']
     */
    protected $rotas = [];


    /**
     * @return array
     */
    public function getRotas() {
        return $this->rotas;
    }


    /**
     * @param array $rotas
     */
    public function setRotas($rotas) {
        $this->rotas = filter_var($rotas, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE);
    }


    /**
     * Roteador constructor.
     *
     * Direcionar a navegação através de URLs amigáveis pelo modelo MVC
     *
     * @param array $rotas Vetor bidimensional no seguinte formato: [':metodo'][':rota']
     */
    public function __construct(array $rotas) {
        $this->setRotas($rotas);
    } // Fim do método __construct


    /**
     * Validar as rotas configuradas
     *
     * Verificar se a propriedade $this->rotas é um vetor se há pelo menos mais de uma rota configurada
     *
     * @return bool true se a propriedade $this->rotas é válida e false se não
     */
    private function validarRotas() {
        return !empty($this->rotas) && (is_array($this->rotas) || (bool)(count($this->rotas)));
    } // Fim do método validarRotas


    /**
     * Obter os parâmetros da rota
     *
     * @param string $url  URL
     * @param string $rota Rota a ser analizada
     *
     * @return mixed  Retorna um array associativo com os parâmetros localizados ou null se nenhum parâmetro foi
     *                configurado para a rota
     */
    private function obterParams($url, $rota) {
        # Verificar se a rota passada é um array
        $ev = isset($rota) && is_array($rota);

        # String de parâmetros
        $sp = !$ev ? $rota : array_key_exists('params', $rota) ? $rota['params'] : '';

        # Verificar se foram passados outros parâmetros
        $op = $ev ? preg_grep('~^(aplicativo|modulo|controle|acao|params)$~', array_keys($rota), PREG_GREP_INVERT) : false;

        # Verificar se os parâmetros foram configurados na rota
        if (!preg_match('~/\:[a-z_]+~', $sp) && !$op) {
            return [];
        } // Fim if

        # Vetor a ser retornado
        $vp = [];

        # Obter os outros parâmetros
        foreach ($op as $p) {
            $vp[$p] = $rota[$p];
        } // fim foreach

        # Separar apenas os parâmetros da string
        $sop = preg_grep('~^:~', explode('/', trim($sp, '/')));
        $url = explode('/', $url);

        foreach ($sop as $c => $p) {
            if (array_key_exists($c, $url)) {
                $vp[preg_replace('~^:~', '', $p)] = $url[$c];
            } // Fim if
        } // Fim foreach

        return $vp;
    } // Fim do método obterParams


    /**
     * Obter a rota atual e indicar qual será o controle, a ação e os parâmetros utilizados
     *
     * @param string $url    URL solicitada
     *
     * @return bool|Controle
     * @throws DLXExcecao
     */
    public function obterRota($url) {
        if (!$this->validarRotas()) {
            throw new DLXExcecao('Nenhuma rota foi configurada!', 1404);
        } // Fim if

        /*
         * Alguns servidores não liberam acesso as variáveis através do INPUT_SERVER, portanto, tento obtê-las através do
         * INPUT_ENV, caso seja necessário
         */
        $metodo = strtolower(filter_var($_SERVER['REQUEST_METHOD']));
        
        if (array_key_exists($metodo, $this->rotas)) {
            foreach ($this->rotas[$metodo] as $r => $v) {
                if (preg_match("~{$r}~", $url)) {
                    $p = $this->obterParams($url, $v);

                    if (is_array($v)) {
                        if (!array_key_exists('aplicativo', $p) && array_key_exists('aplicativo', $v)) {
                            $p['aplicativo'] = $v['aplicativo'];
                        } // Fim if

                        if (!array_key_exists('modulo', $p) && array_key_exists('modulo', $v)) {
                            $p['modulo'] = $v['modulo'];
                        } // Fim if

                        if (!array_key_exists('controle', $p) && array_key_exists('controle', $v)) {
                            $p['controle'] = $v['controle'];
                        } // Fim if

                        if (!array_key_exists('acao', $p) && array_key_exists('acao', $v)) {
                            $p['acao'] = $v['acao'];
                        } // Fim if

                        if (!array_key_exists('params', $p) && array_key_exists('params', $v)) {
                            $p['params'] = $v['params'];
                        } // Fim if
                    } // Fim if

                    $params = array_diff_key($p, [
                        'aplicativo' => '',
                        'modulo'     => '',
                        'controle'   => '',
                        'acao'       => ''
                    ]);

                    return class_exists('DLX\\Classes\\Controle')
                        ? new Controle(
                            array_key_exists('aplicativo', $p) ? $p['aplicativo'] : null,
                            array_key_exists('modulo', $p) ? $p['modulo'] : null,
                            $p['controle'],
                            $p['acao'],
                            array_key_exists('params', $p) ? array_merge((array)$params, (array)$p['params']) : $params
                        ) : $p;
                } // Fim if
            } // Fim foreach
        } // Fim if

        return class_exists('DLX\\Classes\\Controle')
            ? new Controle(null, 'Comum', 'ErroHTTP', 'mostrarErro', ['status_http' => 404])
            : false;
    } // Fim do método obterRota
}

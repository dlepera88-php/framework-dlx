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
use DLX\Excecao\DLX as DLXExcecao;

trait Evento {
    /**
     * @var array Armazenamento de todos os eventos de uma determinada classe
     */
    protected $eventos = [
        'antes'  => [],
        'depois' => []
    ];


    public function __call($nome, array $args = []) {
        /*
         * Executar eventos antes de chamar o método
         */
        $this->executarEvento('antes', $nome);

        $exec = call_user_func_array([$this, $nome], $args);

        /*
         * Executar eventos depois de chamar o método
         */
        $this->executarEvento('depois', $nome);

        return $exec;
    } // Fim do método __call


    /**
     * Adicionar um evento a ser executado
     *
     * @param string       $momento Momento para executar esse evento. Antes ou depois da chamada do método
     * @param array|string $metodo  Nome do método a ser chamado. Esse será o método que será monitorado para a
     *                              execução do evento. Caso seja passado um array com os nomes dos métodos, a função
     *                              será executada recursivamente através de cada elemento do array. Obs.: Esse método
     *                              não pode ser público
     * @param callable     $funcao  Função a ser executada
     *
     * @throws DLXExcecao
     */
    protected function adicionarEvento($momento, $metodo = '*', callable $funcao) {
        if (!empty($metodo)) {
            if (is_array($metodo)) {
                foreach ($metodo as $m) {
                    $this->adicionarEvento($momento, $m, $funcao);
                } // Fim foreach
            } else {
                if (method_exists($this, $metodo) || $metodo === '*') {
                    if (method_exists($this, $metodo)) {
                        $rfx_metodo = new \ReflectionMethod($this, $metodo);

                        if ($rfx_metodo->isPublic()) {
                            throw new DLXExcecao(sprintf(AjdVisao::traduzirTexto('Não é possível atribuir eventos a métodos públicos: <b>%s</b>.'), $metodo), 1403, '-erro', 'html');
                        } // Fim if
                    } // Fim if

                    $this->eventos[$momento][$metodo][] = $funcao;
                } // Fim if
            } // Fim if ... else
        } // Fim if
    } // Fim do método adicionarEventos


    /**
     * Executar um determinado evento de um método
     *
     * @param string $momento Momento da execução do evento (antes ou depois)
     * @param string $metodo  Nome do método que está sendo executado nesse momento
     * @param array  $params  [opcional] não implementado ainda
     */
    protected function executarEvento($momento, $metodo, array $params = []) {
        /* IDEA: Tirar a execução do evento '*' de dentro desse método pois cada
        vez que um evento é executado, todos eles são executados juntos */
        $evento_todos = array_key_exists('*', $this->eventos[$momento]);
        $evento_metodo = array_key_exists($metodo, $this->eventos[$momento]);

        if ($evento_todos || $evento_metodo) {
            $funcoes = array_merge(
                $evento_todos ? $this->eventos[$momento]['*'] : [],
                $evento_metodo ? $this->eventos[$momento][$metodo] : []
            );
            
            foreach ($funcoes as $func) {
                call_user_func_array($func, ['classe' => get_called_class(), 'metodo' => $metodo] + $params);
            } // Fim foreach
        } // Fim if
    } // Fim do método executarEvento
} // Fim da classe Evento

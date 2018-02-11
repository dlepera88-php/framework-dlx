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

class Calendario {
    /**
     * Representação numérica do dia atual
     * @var int
     */
    protected $dia = 30;

    /**
     * Representação numérica do mês atual
     * @var int
     */
    protected $mes = 1;

    /**
     * Representação numérica do ano atual
     * @var int
     */
    protected $ano = 1988;

    /**
     * Vetor para armazenar os eventos a serem exibidos no calendário.
     * Será formatado no seguinte formato:
     * [
     *      ano => [
     *          mes => [
     *              dia => [
     *                  [informações do evento 1],
     *                  [informações do evento 2]
     *              ]
     *          ]
     *      ]
     * ]
     * @var  array
     */
    public $eventos = [];

    /**
     * Configurações dos dias da semana
     * @var array
     */
    private $dias_semana = [
        ['Domingo', 'Dom'],
        ['Segunda-Feira', 'Seg'],
        ['Terça-Feira', 'Ter'],
        ['Quarta-Feira', 'Qua'],
        ['Quinta-Feira', 'Qui'],
        ['Sexta-Feira', 'Sex'],
        ['Sábado', 'Sáb']
    ];

    private $meses = [
        1 => ['Janeiro', 'Jan'],
        2 => ['Fevereiro', 'Fev'],
        3 => ['Março', 'Mar'],
        4 => ['Abril', 'Abr'],
        5 => ['Maio', 'Mai'],
        6 => ['Junho', 'Jun'],
        7 => ['Julho', 'Jul'],
        8 => ['Agosto', 'Ago'],
        9 => ['Setembro', 'Set'],
        10 => ['Outubro', 'Out'],
        11 => ['Novembro', 'Nov'],
        12 => ['Dezembro', 'Dez']
    ];


    public function getDia() {
        return $this->dia;
    }

    public function setDia($dia) {
        $this->dia = filter_var($dia, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 31, 'default' => date('j')]
        ]);
    }

    public function getMes() {
        return $this->mes;
    }

    public function setMes($mes) {
        $this->mes = filter_var($mes, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 12, 'default' => date('n')]
        ]);
    }

    public function getAno() {
        return $this->ano;
    }

    public function setAno($ano) {
        $this->ano = filter_var($ano, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    }


    /**
     * [__construct description]
     * @param int $mes [description]
     * @param int $ano [description]
     * @param int $dia [description]
     */
    public function __construct($mes, $ano, $dia = null) {
        $this->setMes($mes);
        $this->setAno($ano);
        $this->setDia($dia);
    } // Fim do método __construct


// Calendário --------------------------------------------------------------- //
    /**
     * Dias de um determinado mês
     * @param  int $mes Número representativo do mês
     * @param  int $ano Número representativo do ano
     * @return array Retorna um array com todos os dias do mês/ano informado
     */
    public function diasDoMes($mes, $ano) {
        return range(1, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));
    } // Fim do método diasMes


    /**
     * Meses e dias de um determinado ano
     * @param  int $ano Número representativo do ano
     * @return array Retorna um array multi dimensional onde a chave é o mês e o
     * valor um vetor com todos os dias
     */
    public function mesesDoAno($ano) {
        $dias_mes = [];

        for($i = 1; $i < 13; $i++) {
            $dias_mes[$i] = $this->diasDoMes($i, $ano);
        } // Fim for

        return $dias_mes;
    } // Fim do método mesesAno


    /**
     * Identificar o dia da semana de uma determinada data
     * @param  [type] $dia [description]
     * @param  [type] $mes [description]
     * @param  [type] $ano [description]
     * @return [type]      Retorna o representativo numérico para o dia da semana,
     * onde 0 é domingo e 6 é sábado
     */
    public function diaDaSemana($dia, $mes, $ano) {
        return (int)date('w', mktime(0, 0, 0, $mes, $ano, $dia));
    } // Fim do método diaDaSemana


    /**
     * Obter o nome do dia da semana
     * @param  [type] $dia [description]
     * @param  [type] $mes [description]
     * @param  [type] $ano [description]
     * @return array Retorna um array contendo as informações por extenso do dia
     * da semana: [nome completo, abreviação]
     */
    public function diaDaSemanaPorExtenso($dia, $mes, $ano, $abrev = false) {
        return $this->dias_semana[$this->diaDaSemana($dia, $mes, $ano)][(int)$abrev];
    } // Fim do método diaDaSemanaPorExtenso


    /**
     * Obter o nome do mês por extenso
     * @param  [type]  $mes   [description]
     * @param  boolean $abrev [description]
     * @return [type]         [description]
     */
    public function mesPorExtenso($mes, $abrev = false) {
        // return strftime($abrev ? '%b' : '%B', mktime(null, null, null, (int)$mes, 1, date('Y')));
        return $this->meses[$mes][(int)$abrev];
    } // Fim do método mesPorExtenso


    public function formatarData($formato = 'd/m/Y', $data = null) {
        $data = !empty($data) ? $data : "{$this->getAno()}-{$this->getMes()}-{$this->getDia()}";
        $data_hora = new \DateTime($data);
        return $data_hora->format($formato);
    } // Fim do método


// Eventos ------------------------------------------------------------------ //
    /**
     * Adicionar um evento ao calendário
     * @param  array  $evento   Descrição do evento
     * @param  int $dia         [opcional] Número representativo do dia do evento.
     * Se não for informado, é utilizado o dia atual para do calendário
     * @param  int $mes         [opcional] Número representativo do mês desse evento.
     * Se não for informado, é utilizado o mês atual do calendário
     * @return void
     */
    public function adicionarEvento($evento, $data = null, $cor = '#000', $link = 'javascript:') {
        $dia = $this->formatarData('j', $data);
        $mes = $this->formatarData('n', $data);
        $ano = $this->formatarData('Y', $data);
        $this->eventos[$ano][$mes][$dia][] = [
            'evento' => $evento,
            'cor'   => $cor,
            'link'  => $link
        ];
    } // Fim do método adicionarEvento


    /**
     * Resgatar os eeventos de um determinado dia
     * @param  int $dia [description]
     * @param  int $mes [description]
     * @return array Retorna um vetor contendo os eventos desse dia
     */
    public function resgatarEventos($data = null) {
        $dia = $this->formatarData('j', $data);
        $mes = $this->formatarData('n', $data);
        $ano = $this->formatarData('Y', $data);
        return (array)$this->eventos[$ano][$mes][$dia];
    } // Fim do método resgatarEventos


// HTML --------------------------------------------------------------------- //
    public function gerarCalendarioMesHTML() {
        define('HTML_CALENDARIO_SEMANA', '<th id="%s">%s</th>');
        define('HTML_CALENDARIO_DIA', '<td header="%s" class="dia"><span class="numero">%s</span>%s</td>');
        define('HTML_CALENDARIO_LISTA_EVENTOS', '<ul class="lista-eventos">%s</ul>');
        define('HMTL_CALENDARIO_EVENTO', '<li class="evento" style="background:%s"><a href="%s">%s</a></li>');

        # Identificar em qual dia da semana o primeiro dia do mês cai.
        # Como o retorno do dia da semana é 'zero based', ou seja, inicia em 0,
        # é necessário subtrair 1
        $primeiro_dia = $this->diaDaSemana(1, $this->getMes(), $this->getAno()) - 1;

        # Total de dias que contém o mês atual
        $total_dias = count($this->diasDoMes($this->getMes(), $this->getAno()));

        # Para preencher o calendário começo pelo dia primeiro
        $dia_atual = 1;


        $html = '<table class="calendario-phphtml">
            <caption class="mes-ano">' . "{$this->mesPorExtenso($this->getMes())} {$this->getAno()}" . '</caption>
            <thead class="dias-semana"><tr>';

        # Adicionar os dias da semana no cabeçalho da tabela
        foreach($this->dias_semana as $dia) {
            $semana_id = "s-{$dia[1]}";
            $html .= sprintf(HTML_CALENDARIO_SEMANA, $semana_id, $dia[1]);
        } // Fim foreach

        $html .= '</tr></thead>';

        # Adicionar os dias do mês
        $html .= '<tbody class="dias-mes">';

        while($dia_atual <= $total_dias) {
            $html .= '<tr class="semana">';

            foreach($this->dias_semana as $dia_semana => $extenso) {
                $semana_id = "s-{$extenso[1]}";

                # Preencher os primeiros dias da semana com células vazias até
                # que comecem os dias desse mês ou após terminarem
                if (($dia_atual === 1 && $dia_semana < $primeiro_dia) || $dia_atual > $total_dias) {
                    $html .= sprintf(HTML_CALENDARIO_DIA, $semana_id, '', '');
                    continue;
                } // Fim if

                # Preencher os dias do mês
                $lista_eventos = $this->resgatarEventos("{$this->getAno()}-{$this->getMes()}-{$dia_atual}");
                $eventos = '';

                if (count($lista_eventos) > 0) {
                    $eventos = sprintf(
                        HTML_CALENDARIO_LISTA_EVENTOS,
                        implode("\n",
                            array_map(function($evt) {
                                return sprintf(
                                    HMTL_CALENDARIO_EVENTO,
                                    $evt['cor'], $evt['link'], $evt['evento']
                                );
                            },
                            $lista_eventos
                        ))
                    );
                } // Fim if

                $html .= sprintf(HTML_CALENDARIO_DIA, $semana_id, $dia_atual, $eventos);
                $dia_atual++;
            } // Fim foreach

            $html .= '</tr>';
        } // Fim while

        $html .= '</table>';

        return $html;
    } // Fim do método gerarCalendarioHTML
}

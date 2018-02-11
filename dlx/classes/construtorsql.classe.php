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

use DLX\Ajudantes\ConstrutorSQL as AjdConstrutorSQL;
use DLX\Ajudantes\Vetores as AjdVetores;
use DLX\Ajudantes\Vetores;
use DLX\Excecao\DLX as DLXExcecao;

class ConstrutorSQL {
    # Consultas
    const QUERY_INSERT = 'INSERT INTO %s';
    const QUERY_SELECT = 'SELECT %s FROM %s';
    const QUERY_UPDATE = 'UPDATE %s SET';
    const QUERY_DELETE = 'DELETE FROM %s';

    # Cláusulas
    const CLAUSULA_WHERE = 'WHERE %s';
    const CLAUSULA_HAVING = 'HAVING %s';
    const CLAUSULA_JOIN = '%s JOIN %s ON %s';
    const CLAUSULA_ORDERBY = 'ORDER BY %s';

    /**
     * @var string Armazenar consulta
     */
    private $query;

    /**
     * @var string Nome da tabela principal da consulta
     */
    private $tabela;

    /**
     * @var array Armazenamento dos campos e valores para INSERT
     */
    private $campos_insert = [];

    /**
     * @var array Junções a serem feitas na consulta
     *            Obs.: Válido somente para consultas SELECT
     */
    private $join = [];

    /**
     * @var array Cláusula WHERE
     */
    private $where = [];

    /**
     * @var array Cláusula HAVING
     */
    private $having = [];

    /**
     * @var array Cláusula GROUP BY
     */
    private $group_by = [];

    /**
     * @var array Cláusula ORDER BY
     */
    private $order_by = [];

    /**
     * @var array Cláusula SET utilizada na consulta UPDATE
     */
    private $set = [];

    /**
     * @var PDODL Objeto com a classe de conexão com o banco de dados
     */
    private $pdo;

    /**
     * @var bool Define se será usado o AjdConstrutorSQL::varExportBD ao inserir algum valor na consulta
     */
    protected $var_export = true;


    /**
     * ConstrutorSQL constructor.
     *
     * @param PDODL $pdo        Instância de conexão com banco de dados
     * @param bool  $var_export Define se será usado o AjdConstrutorSQL::varExportBD ao inserir algum valor na consulta
     */
    public function __construct(PDODL $pdo, $var_export = true) {
        $this->pdo = $pdo;
        $this->var_export = (bool)$var_export;
    } // Fim do método __construct


    function __toString() {
        $query = $this->query;

        if (count($this->campos_insert) > 0) {
            $multi = AjdVetores::arrayMulti($this->campos_insert);
            $campos = implode(', ', array_keys($multi ? $this->campos_insert[0] : $this->campos_insert));
            $query .= " ({$campos}) VALUES ";

            if ($multi) {
                $valores = [];

                foreach ($this->campos_insert as $v) {
                    $valores[] = '(' . implode(', ', $v) . ')';
                } // Fim foreach

                $query .= implode(', ', $valores);
            } else {
                $query .= '(' . implode(', ', $this->campos_insert) . ')';
            } // Fim if ... else
        } // Fim if

        if (count($this->join) > 0) {
            $query .= ' ' . implode(' ', $this->join);
        } // Fim if

        if (count($this->set)) {
            $query .= ' ' . implode(', ', $this->set);
        } // Fim if

        if (count($this->where) > 0) {
            $query .= ' ' . sprintf(static::CLAUSULA_WHERE, implode(' ', $this->where));
        } // Fim if

        if (count($this->group_by) > 0) {
            $query .= ' GROUP BY ' . implode(', ', $this->group_by);

            /*
             * A cláusula GROUP BY no SQL Server tem um comportamento diferente do MYSQL. No SQL Server é necessário que
             * todos os campos que estão na lista de campos do SELECT estejam presentes também na cláusula GROUP BY
             */
            /* if ($this->pdo->driver === 'DBLIB' || $this->pdo->driver === 'MSSQL') {
                // TAREFA: Criar script para completar a cláusula GROUP BY para SQL Server
            } // Fim if */
        } // Fim if

        if (count($this->having) > 0) {
            $query .= ' ' . sprintf(static::CLAUSULA_HAVING, implode(' ', $this->having));
        } // Fim if

        if (count($this->order_by) > 0) {
            $query .= ' ' . sprintf(static::CLAUSULA_ORDERBY, implode(', ', $this->order_by));
        } // Fim if

        return $query;
    } // Fim do método __toString


// Consultas -------------------------------------------------------------------------------------------------------- //
    /**
     * Criar uma consulta do tipo INSERT
     *
     * Esse método gera apenas uma parde da consulta. Para ter a consulta completa é necessário adicionar os campos e
     * seus valores através do método ->into.
     *
     * Ex: $sql->insert('nome_da_tabela')->into(['nome_do_campo' => 'valor atribuído']);
     *
     * @param string $tabela Nome da tabela
     *
     * @return ConstrutorSQL
     * @throws DLXExcecao
     */
    public function insert($tabela) {
        AjdConstrutorSQL::tabelaExiste($tabela);
        $this->tabela = $tabela;

        $clone = clone $this;
        $clone->query = sprintf(static::QUERY_INSERT, $tabela);

        return $clone;
    } // Fim do método insert


    /**
     * Criar uma consulta do tipo SELECT
     *
     * Selecionar informações de um banco de dados
     *
     * @param string $tabela Nome da tabela
     * @param string $alias  [opcional] Alias da tabela caso haja 'joins'
     * @param string $campos [opcional] Nomes dos campos a serem selecionado
     *
     * @return ConstrutorSQL
     * @throws DLXExcecao
     */
    public function select($tabela, $alias = '', $campos = '*') {
        AjdConstrutorSQL::tabelaExiste($tabela);
        $this->tabela = $tabela;

        $clone = clone $this;
        $clone->query = sprintf(static::QUERY_SELECT, $campos, $tabela . AjdConstrutorSQL::adAlias($alias));

        return $clone;
    } // Fim do método select


    /**
     * Criar uma consulta do tipo UPDATE
     *
     * Esse método cria apenas uma parte da consulta. Para ter a consulta completa é necessário incluir os campos e
     * seus velores através do método ->set.
     *
     * Ex.: $sql->update('nome_da_tabela')->set('nome_do_campo', 'valor atribuído', FILTER_DEFAULT);
     *
     * @param string $tabela Nome da tabela a ser atualizada
     *
     * @return ConstrutorSQL
     * @throws DLXExcecao
     */
    public function update($tabela) {
        AjdConstrutorSQL::tabelaExiste($tabela);
        $this->tabela = $tabela;

        $clone = clone $this;
        $clone->query = sprintf(static::QUERY_UPDATE, $tabela);

        return $clone;
    } // Fim do método update


    /**
     * Criar uma consulta do tipo DELETE
     *
     * Deletar informações do banco de dados
     *
     * @param string $tabela Nome da tabela
     *
     * @return ConstrutorSQL
     * @throws DLXExcecao
     */
    public function delete($tabela) {
        AjdConstrutorSQL::tabelaExiste($tabela);
        $this->tabela = $tabela;

        $clone = clone $this;
        $clone->query = sprintf(static::QUERY_DELETE, $tabela);

        return $clone;
    } // Fim do método delete


// Cláusulas -------------------------------------------------------------------------------------------------------- //
    /**
     * Adicionar uma condição simples a consulta (WHERE)
     *
     * Esse método pode ser usado em conjunto com os seguintes métodos:
     * ->select
     * ->update
     * ->delete
     *
     * @param string $condicao Condição
     * @param string $andor    Tipo da condição AND ou OR
     *
     * @return $this
     */
    public function where($condicao, $andor = '') {
        if (preg_match('~^(SELECT|UPDATE|DELETE)~', $this->query)) {
            Vetores::executarRecursivo((array)$condicao, function ($condicao) use ($andor) {
                $this->where[] = (count($this->where) > 0 ? AjdConstrutorSQL::validarAndOr($andor) : '') . " {$condicao}";
            });
        } // Fim if

        return $this;
    } // Fim do método where


    /**
     * Adicionar uma condição de funções agregadas (HAVING)
     *
     * Esse método deve ser usado em conjunto com o método ->select.
     *
     * @param string $condicao Condição
     * @param string $andor    Tipo da condição AND ou OR
     *
     * @return $this
     */
    public function having($condicao, $andor = '') {
        if (preg_match('~^(SELECT)~', $this->query)) {
            Vetores::executarRecursivo((array)$condicao, function ($condicao) use ($andor) {
                $this->having[] = (count($this->having) > 0 ? AjdConstrutorSQL::validarAndOr($andor) : '') . " {$condicao}";
            });
        } // Fim if

        return $this;
    } // Fim do método having


    /**
     * Criar junções no SELECT
     *
     * Esse método deve ser usado em conjunto com o método ->select.
     *
     * @param string $tabela Nome da tabela a ser juntada
     * @param string $alias  Alias da tabela
     * @param string $on     Cláusula ON referente a esse JOIN
     * @param string $tipo   Tipo de JOIN (INNER, LEFT, RIGHT)
     *
     * @return $this
     */
    public function join($tabela, $alias = '', $on = '', $tipo = '') {
        if (preg_match('~^(SELECT)~', $this->query)) {
            AjdConstrutorSQL::tabelaExiste($tabela);

            $this->join[] = sprintf(
                static::CLAUSULA_JOIN,
                AjdConstrutorSQL::validarTipoJoin($tipo),
                $tabela . AjdConstrutorSQL::adAlias($alias),
                $on
            );
        } // Fim if

        return $this;
    } // Fim do método join


    /**
     * Incluir agrupamento
     *
     * Esse método deve ser usado em conjunto com o método ->select.
     *
     * @param string $agrupamento Campo ou expressão de agrupamento
     *
     * @return $this
     */
    public function groupBy($agrupamento) {
        if (preg_match('~^(SELECT)~', $this->query) && !empty($agrupamento)) {
           Vetores::executarRecursivo((array)$agrupamento, function ($agrupamento) {
               $this->group_by[] = filter_var($agrupamento);
           });
        } // Fim if

        return $this;
    } // Fim do método groupBy


    /**
     * Incluir ordenação a consulta
     *
     * Esse método deve ser usado em conjunto com o método ->select.
     *
     * @param string|array $ordem Ordenaçãp
     * @param string       $tipo  Tipo de ordenação (ASC ou DESC)
     *
     * @return $this
     */
    public function orderBy($ordem, $tipo = '') {
        if (preg_match('~^(SELECT)~', $this->query)) {
            Vetores::executarRecursivo((array)$ordem, function ($o) use ($tipo) {
                $this->order_by[] = trim("{$o} " . AjdConstrutorSQL::validarTipoOrdenacao($tipo));
            });
        } // Fim if

        return $this;
    } // Fim do método orderBy


    /**
     * Incluir campos para INSERT
     *
     * Esse método deve ser usado em conjunto com o método ->insert.
     *
     * @param array $campos Array com os nomes dos campos e seus valores.
     *                      Ex: [
     *                      'campo1' => 'valor1',
     *                      'campo2' => 'valor2'
     *                      ]
     *                      Obs.: Caso o array seja multimensional será gerado um insert múltiplo
     *
     * @return $this
     * @throws DLXExcecao
     */
    public function into(array $campos) {
        if (preg_match('~^(INSERT)~', $this->query)) {
            $this->campos_insert += AjdVetores::arrayMapRecursivo(function ($valor, $campo) {
                AjdConstrutorSQL::campoExiste($campo, $this->tabela);

                return $this->var_export ? AjdConstrutorSQL::varExportBD($valor) : $valor;
            }, $campos);
        } // Fim if

        return $this;
    } // Fim do método into


    /**
     * Cláusula SET do comando UPDATE
     *
     * Esse método deve ser usado em conjunto com o método ->update.
     *
     * @param string $campo  Nome do campo
     * @param mixed  $valor  Valor a ser atribuído para esse campo
     * @param int    $filtro [opcional] Filtro a ser aplicado no valor
     *
     * @return $this;
     */
    public function set($campo, $valor, $filtro = FILTER_DEFAULT) {
        if (preg_match('~^(UPDATE)~', $this->query)) {
            AjdConstrutorSQL::campoExiste($campo, $this->tabela);

            $this->set[] = "{$campo} = " .
                ($this->var_export ? AjdConstrutorSQL::varExportBD(filter_var($valor, $filtro)) : filter_var($valor, $filtro));
        } // Fim if

        return $this;
    } // Fim do método set
} // Fim do ajudante ConstrutorSQL

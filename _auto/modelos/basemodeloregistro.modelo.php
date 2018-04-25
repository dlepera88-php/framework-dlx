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

namespace Geral\Modelos;


use Comum\Modelos\LogRegistro;
use DLX\Ajudantes\Evento;
use DLX\Ajudantes\Strings;
use DLX\Ajudantes\Vetores;
use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Ajudantes\ConstrutorSQL as AjdConstrutorSQL;
use DLX\Classes\ConstrutorSQL;
use DLX\Excecao\DLX as DLXExcecao;

abstract class BaseModeloRegistro extends BaseModelo {
    use Evento;

    /**
     * @var string Nome da tabela no banco de dados que referencia esse modelo
     */
    protected $bd_tabela;

    /**
     * @var string Prefixo utilizado para nomes de campos dessa tabela
     */
    protected $bd_prefixo;

    /**
     * @var ConstrutorSQL Instância do ConstrutorSQL para criar as instruções SQL para manipular esse registro
     */
    protected $bd_sql;

    /**
     * @var ConstrutorSQL Instância do ConstrutorSQL com o comando padrão para gerar lista de resultados
     */
    protected $bd_lista;

    /**
     * @var mixed Campo identificador do registro no banco de dados
     */
    protected $id;

    /**
     * Identificação do idioma desse registro.
     *
     * @var string
     */
    protected $idioma = 'br';

    /**
     * @var boolean Campo que define se o registro será exibido no site e/ou sistema
     */
    protected $publicar = true;

    /**
     * @var boolean Campo que define se o registro está deletado ou não. Utilizado para casos de restrições ao excluir
     *      por FK
     */
    protected $delete = false;

    /**
     * @var bool Variável de controle para indicar se o modelo atual está vazio ou não
     */
    public $reg_vazio = true;

    /**
     * @var bool Define se é permitido inserir valores em um campo PK ou não
     */
    public $insert_pk = false;

    /**
     * @var LogRegistro Instância do log de registro. Será carregado com a informação de inclusão do registro.
     */
    public $log_criacao;

    /**
     * @var LogRegistro Instância do log de registro. Será carregado com a informação da última alteração do registro.
     */
    public $log_alteracao;

    /**
     * @var LogRegistro Instância do log de registro. Será carregado com a informação de exclusão do registro, caso o
     *      mesmo esteja marcado como deletado.
     */
    public $log_exclusao;


    /**
     * @return string
     */
    public function getBdTabela() {
        return $this->bd_tabela;
    }


    /**
     * @param string $bd_tabela
     */
    public function setBdTabela($bd_tabela) {
        $this->bd_tabela = filter_var($bd_tabela, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return string
     */
    public function getBdPrefixo() {
        return $this->bd_prefixo;
    }


    /**
     * @param string $getBdPrefixo()
     */
    public function setBdPrefixo($bd_prefixo) {
        $this->bd_prefixo = filter_var($bd_prefixo, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return ConstrutorSQL
     */
    public function getBdLista() {
        return $this->bd_lista;
    }


    /**
     * @return mixed
     */
    public function getId() {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}id", $this->bd_tabela);
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId($id) {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}id", $this->bd_tabela);
        $this->id = $id;
    }

    public function getIdioma() {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}idioma", $this->bd_tabela);
        return $this->idioma;
    }

    public function setIdioma($idioma) {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}idioma", $this->bd_tabela);
        $this->idioma = filter_var($idioma, FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '~^[a-z]{2}(?:[_-][A-Za-z]{2})?~', 'default' => 'br']
        ]);
    }

    /**
     * @return boolean
     */
    public function isPublicar() {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}publicar", $this->bd_tabela);

        return (bool)$this->publicar;
    }


    /**
     * @param boolean $publicar
     */
    public function setPublicar($publicar) {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}publicar", $this->bd_tabela);
        $this->publicar = filter_var($publicar, FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * @return boolean
     */
    public function isDelete() {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}delete", $this->bd_tabela);

        return $this->delete;
    }


    /**
     * @param boolean $delete
     */
    public function setDelete($delete) {
        AjdConstrutorSQL::campoExiste("{$this->getBdPrefixo()}delete", $this->bd_tabela);
        $this->delete = filter_var($delete, FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * BaseModeloRegistro constructor.
     *
     * @param string      $tabela    Nome da tabela
     * @param string|null $prefixo   Prefixo utilizado para os nomes dos campos dessa tabela
     * @param bool        $insert_pk Define se será permitido incluir valores na PK. Deve ser usado para casos em que a
     *                               PK da tabela não é AUTO_INCRMENT
     */
    public function __construct($tabela, $prefixo = null, $insert_pk = false) {
        $this->setBdTabela($tabela);
        $this->setBdPrefixo($prefixo);

        $this->insert_pk = (bool)$insert_pk;

        $this->bd_sql = new ConstrutorSQL(\DLX::$dlx->bd);
        $this->bd_lista = $this->bd_sql->select($this->getBdTabela(), null, '__CAMPOS__')->where("{$this->getBdPrefixo()}delete = 0");

        /*
         * Gravar os logs no banco de dados
         */
        if (get_called_class() !== 'Comum\\Modelos\\LogRegistro') {
            $this->adicionarEvento('depois', 'salvar', function () {
                $pk = implode(';', $this->obterValorPK());

                if (!empty($pk)) {
                    $log = new LogRegistro();
                    $log->setTabela($this->getBdTabela());
                    $log->setAcao($this->reg_vazio ? 'A' : 'E');
                    $log->setRegpk($pk);
                    // $log->salvar();
                    $log->__call('salvar');
                } // Fim if
            });

            $this->adicionarEvento('depois', 'excluir', function () {
                $pk = implode(';', $this->obterValorPK());

                if (!empty($pk)) {
                    $log = new LogRegistro();
                    $log->setTabela($this->getBdTabela());
                    $log->setAcao('X');
                    $log->setRegpk(implode(';', $this->obterValorPK()));
                    // $log->salvar();
                    $log->__call('salvar');
                } // Fim if
            });
        } // Fim if
    } // Fim do método __construct


    /**
     * Selecionar dados de um registro através de uma UK (Unique Key)
     *
     * @param array $valores Vetor no formato 'nome_do_campo_uk' => 'valor_do_campo'.
     * Quando nenhum valor é enviado, o método retorna o primeiro registro encontrado
     *
     * @return bool Retorna true se algum registro for selecionado ou false se não
     */
    protected function selecionarUK(array $valores = []) {
        $select = $this->bd_sql->select($this->bd_tabela);

        foreach ($valores as $campo => $valor) {
            $select->where("{$this->getBdPrefixo()}{$campo} = " . AjdConstrutorSQL::varExportBD($valor), 'AND');
        } // Fim foreach
        
        $sql = \DLX::$dlx->bd->query((string)$select);

        if ($sql !== false) {
            $rs = $sql->fetch();

            if ($rs !== false) {
                foreach ($rs as $campo => $valor) {
                    $set_campo = 'set' . Strings::conveter2PSR(preg_replace("~^{$this->getBdPrefixo()}~", '', $campo));
                    $this->{$set_campo}($valor);
                } // Fim foreach
                
                if (get_called_class() !== 'Comum\\Modelos\\LogRegistro') {
                    $pk = implode(';', $this->obterValorPK());

                    $this->log_criacao = new LogRegistro();
                    $this->log_criacao->selecionarUK([
                        'tabela' => $this->getBdTabela(),
                        'regpk'  => $pk,
                        'acao'   => 'A'
                    ]);

                    $this->log_alteracao = new LogRegistro();
                    $this->log_alteracao->selecionarUK([
                        'tabela' => $this->getBdTabela(),
                        'regpk'  => $pk,
                        'acao'   => 'E'
                    ]);

                    $this->log_exclusao = new LogRegistro();
                    $this->log_exclusao->selecionarUK([
                        'tabela' => $this->getBdTabela(),
                        'regpk'  => $pk,
                        'acao'   => 'X'
                    ]);
                } // Fim if

                # Retorna true
                return !($this->reg_vazio = false);
            } // Fim if
        } // Fim if

        # Retorna false
        return !($this->reg_vazio = true);
    } // Fim do método selecionarUK


    /**
     * Selecionar os dados de um registro de acordo com a sua PK (Primary Key)
     *
     * Esse método identifica a PK da tabela e faz a consulta com base nos valores presentes em $valores
     *
     * @param mixed $valores Valor da PK ou vetor com os valores da PK em caso de PK composta
     *
     * @return bool
     * @throws \DLX\Excecao\DLX
     */
    protected function selecionarPK($valores) {
        if (!isset($valores)) {
            return true;
        } // Fim if

        $valores = (array)$valores;
        $campos = (array)\DLX::$dlx->bd->identificaPK($this->bd_tabela, $this->getBdPrefixo());
        
        if (count($campos) === count($valores)) {
            $pk = array_combine($campos, $valores);
            return $this->selecionarUK($pk);
        } // Fim if

        return false;
    } // Fim do método selecionarPK


    /**
     * Obter o valor dos campos PK.
     * Obs: É necessário que o registro já tenha sido selecionado.
     *
     * @return array
     */
    protected function obterValorPK($padrao = [':id' => 'getID']) {
        $valor_pk = $padrao;

        if (!empty($this->getBdTabela())) {
            $campos = (array)\DLX::$dlx->bd->identificaPK($this->getBdTabela(), $this->getBdPrefixo());
            $valor_pk = [];

            foreach ($campos as $campo) {
                $valor_pk[$campo] = $this->{$campo};
            } // Fim foreach
        } else {
            // Quando o registro não está vinculado a uma tabela do banco de dados,
            // uso o valor passado como padrão
            $valor_pk[':id'] = $this->{$valor_pk[':id']}();
        } // Fim if

        return $valor_pk;
    } // Fim do método obterValorPK
}


trait RegistroConsulta {
    /**
     * Listar registros da tabela referente a esse modelo
     *
     * @param null               $params         Parâmetros a serem passados para o construtor SQL. Só são considerados
     *                                           cláusulas aplicáveis a SELECT: where, order_by, group_by e having
     * @param string             $campos         Lista de campos a serem selecionados
     * @param int                $pagina         Número da página de resultados. Se for -1, serão retornados todos os
     *                                           registros SEM PAGINAÇÃO
     * @param int                $qtde           Quantidade de registros por página. Aplicável somente quando há
     *                                           paginação, ou seja, quando $pagina > -1
     * @param null               $posicao        Posição do registro a ser retornado
     * @param bool               $preparar_sql   Define se a query formada deve ser preparda (mais seguro) ou não
     *                                           (menos seguro, porém mais rápido)
     * @param ConstrutorSQL|null $construtor_sql Instância do ConstrutorSQL. Quando não informado é utilizado o
     *                                           ConstrutorSQL padrão da classe BaseModeloRegistro
     *
     * @return array|bool|null
     */
    protected function listar($params = null, $campos = '*', $pagina = 0, $qtde = 20, $posicao = null, $preparar_sql = true, ConstrutorSQL $construtor_sql = null) {
        $construtor_sql = !isset($construtor_sql) ? clone $this->bd_lista : $construtor_sql;
        $params_sql = [];

        # Filtro (WHERE e HAVING)
        if (isset($params->where)) {
            if ($preparar_sql) {
                Vetores::executarRecursivo((array)$params->where, function ($where) use (&$params_sql, &$construtor_sql) {
                    $prepare_where = AjdConstrutorSQL::clausula2Prepare($where, 'where_');
                    $params_sql += $prepare_where['valores'];
                    $construtor_sql->where($prepare_where['prepare'], 'AND');
                });
            } else {
                $construtor_sql->where($params->where, 'AND');
            } // Fim if ... else
        } // Fim if

        if (isset($params->having)) {
            if ($preparar_sql) {
                Vetores::executarRecursivo((array)$params->having, function ($having) use (&$params_sql, &$construtor_sql) {
                    $prepare_having = AjdConstrutorSQL::clausula2Prepare($having, 'having_');
                    $params_sql += $prepare_having['valores'];
                    $construtor_sql->having($prepare_having['prepare'], 'AND');
                });
            } else {
                $construtor_sql->having($params->having, 'AND');
            } // Fim if ... else
        } // Fim if

        # Agrupamento
        if (isset($params->group_by)) {
            $construtor_sql->groupBy($params->group_by);
        } // Fim if

        # Ordenação
        if (isset($params->order_by)) {
            $construtor_sql->orderBy($params->order_by);
        } // Fim if

        $query = AjdConstrutorSQL::prepararSelect($campos, $construtor_sql);
        // echo $query, '<br><br>', var_dump($params_sql);
        if ($preparar_sql) {
            $query = $pagina > 0 ? \DLX::$dlx->bd->paginacao($query, $pagina, $qtde, false) : $query;
            $sql = \DLX::$dlx->bd->prepare($query);
            $sql->execute($params_sql);
        } else {
            $sql = $pagina > 0
                ? \DLX::$dlx->bd->paginacao($query, $pagina, $qtde)
                : \DLX::$dlx->bd->query($query);
        } // Fim if ... else

        if (!$sql) {
            return false;
        } // Fim if

        $rs = $sql->fetchAll();

        return isset($posicao) && !empty($rs)
            ? $rs[$posicao < 0 ? count($rs) + $posicao : $posicao]
            : Vetores::removerColuna($rs, 'MSSQL_LINHA');
    } // Fim do método listar


    /**
     * Carregar um 'select' com VALOR e TEXTO
     *
     * @param null    $params   Parâmetros a serem passados para o construtor SQL. Só são considerados
     *                          cláusulas aplicáveis a SELECT: where, order_by, group_by e having
     * @param boolean $escrever Define se o resultado será escrito no formato json ou retornado
     * @param string  $valor    Nome do campo identificado como 'value' (sem prefixo)
     * @param string  $texto    Nome do campo identificado como 'label' (sem prefixo)
     *
     * @return array
     */
    protected function carregarSelect($params = null, $escrever = true, $valor = 'id', $texto = 'descr') {
        /*
         * Incluir uma ordenação padrão, caso não tenha sido informada pelo desenvolvedor
         */
        if (!isset($params->order_by)) {
            $params = (object)array_merge((array)$params, ['order_by' => 'TEXTO']);
        } // Fim if

        $lista = $this->listar($params, "{$this->getBdPrefixo()}{$valor} AS VALOR, {$this->getBdPrefixo()}{$texto} AS TEXTO");

        if (!isset($escrever) || $escrever) {
            echo json_encode($lista);
        } // Fim if

        return $lista;
    } // Fim carregarSelect


    /**
     * Obter a quantidade de registro de uma determinada consulta
     *
     * @param null $params Parâmetros a serem passados para o construtor SQL. Só são considerados cláusulas aplicáveis
     *                     a SELECT: where, order_by, group_by e having
     *
     * @return int  Quantidade de registros referente à consulta
     */
    protected function qtdeRegistros($params = null) {
        $rs = $this->listar($params, 'COUNT(*) AS QTDE');

        return !is_array($rs) ? 0 : (int)array_sum(array_column($rs, 'QTDE'));
    } // Fim do método qtdeRegistros
}


trait RegistroEdicao {
    /**
     * Salvar o registro no banco de dados com as informações desse modelo
     *
     * @param bool       $executar_sql   Define se a consulta SQL deve ser executada automaticamente
     * @param array|null $incluir_campos Incluir / considerar esses campos para gerar a consulta SQL
     * @param array|null $excluir_campos Excluir / desconsiderar esses campos para gerar a consulta SQL
     * @param bool       $insert_pk      Define se será permitida a inclusão em campos PK / AUTO_INCREMENT / IDENTITY
     *
     *
     * @return array|bool|string
     * @throws DLXExcecao
     */
    protected function salvar($executar_sql = true, array $incluir_campos = null, array $excluir_campos = null, $insert_pk = null) {
        $query = $this->reg_vazio
            ? AjdConstrutorSQL::criarInsert($this->bd_tabela, isset($insert_pk) ? $insert_pk : $this->insert_pk, $incluir_campos, $excluir_campos)
            : AjdConstrutorSQL::criarUpdate($this->bd_tabela, $incluir_campos, $excluir_campos);

        $dados = [];

        foreach ($query['campos'] as $campo) {
            $campo_nome = is_array($campo) ? $campo['Field'] : $campo;
            $prop_nome = preg_replace("~^{$this->getBdPrefixo()}~", '', $campo_nome);

            if (property_exists($this, $prop_nome)) {
                $dados[":{$campo_nome}"] = $this->{$prop_nome};
            } // Fim if
        } // Fim foreach

        if (!$executar_sql) {
            return [
                'query' => (string)$query['query'],
                'dados' => $dados
            ];
        } // Fim if
        // echo $query['query']; var_dump($dados);
        $sql = \DLX::$dlx->bd->prepare($query['query']);

        if (($exec = $sql->execute($dados)) === false) {
            throw new DLXExcecao(sprintf(AjdVisao::traduzirTexto('A instrução SQL de INSERT ou UPDATE não pode ser executada:<br/>%s'), $sql->errorInfo()[2]), 1500);
        } // Fim if
        
        return preg_match('~^(INSERT)~', $query['query'])
            ? $this->id = \DLX::$dlx->bd->lastInsertID("{$this->getBdPrefixo()}id")
            : $this->id;
            // : $this->obterValorPK();
    } // Fim do método salvar


    /**
     * Excluir o registro do banco de dados ou marcá-lo como deletado, caso haja essa opção
     *
     * @param bool $executar_sql Define se a consulta SQL deve ser executada automaticamente
     *
     * @return array|int
     * @throws DLXExcecao
     */
    protected function excluir($executar_sql = true) {
        if ($this->reg_vazio) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('Registro não selecionado!'), 1403);
        } // Fim if

        $excluir = $this->bd_sql->delete($this->bd_tabela);
        $pk = \DLX::$dlx->bd->identificaPK($this->bd_tabela);
        $dados = [];

        foreach ($pk as $campo) {
            $prop_nome = preg_replace("~^{$this->getBdPrefixo()}~", '', $campo);
            $excluir->where("{$campo} = :{$campo}", 'AND');
            $dados[":{$campo}"] = $this->{$prop_nome};
        } // Fim if

        if (!$executar_sql) {
            return [
                'query' => (string)$excluir,
                'dados' => $dados
            ];
        } // Fim if

        $sql = \DLX::$dlx->bd->prepare((string)$excluir);
        $exec = $sql->execute($dados);

        /*
         * Se ocorrer algum erro ao tentar excluir o registro do banco de dados, marcar esse registro como deletado
         */
        if (!$exec && property_exists($this, 'delete')) {
            $this->delete = 1;
            $exec = $this->salvar(true, array_merge($pk, ["{$this->getBdPrefixo()}delete"]));
        } // Fim if

        return !$exec ? false : $this->obterValorPK();
    } // Fim do método excluir
} // Fim do modelo BaseModeloRegistro

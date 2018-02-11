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

use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Excecao\DLX as DLXExcecao;

class PDODL extends \PDO {
    # Identificar chaves primárias
    const MYSQL_IDENTIFICA_PK = "SELECT I.COLUMN_NAME AS NOME_COLUNA FROM information_schema.KEY_COLUMN_USAGE AS I WHERE I.CONSTRAINT_NAME LIKE 'PRIMARY' AND I.TABLE_SCHEMA LIKE :base AND I.TABLE_NAME LIKE :tbl";
    const MSSQL_IDENTIFICA_PK = "SELECT column_name AS NOME_COLUNA FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE OBJECTPROPERTY(OBJECT_ID(constraint_name), 'IsPrimaryKey') = 1 AND :base <> '' AND table_name LIKE :tbl ORDER BY ORDINAL_POSITION";
    const DBLIB_IDENTIFICA_PK = self::MSSQL_IDENTIFICA_PK;

    # Obter informações dos campos
    const MYSQL_INFO_CAMPOS = 'SHOW COLUMNS FROM :tbl LIKE :cpo';
    const MSSQL_INFO_CAMPOS = "SELECT CAST(C.name AS TEXT) AS Field, CAST(T.name +'('+ CONVERT(VARCHAR(5), T.max_length) +')' AS TEXT) AS Type, CAST(( CASE C.is_nullable WHEN 0 THEN 'NO' WHEN 1 THEN 'YES' END ) AS TEXT) AS 'Null', CAST(( CASE I.is_primary_key WHEN 1 THEN 'PRI' ELSE '' END ) AS TEXT) AS 'Key', CAST(object_definition(C.default_object_id) AS TEXT) AS 'Default', CAST(( CASE C.is_identity WHEN 1 THEN 'auto_increment' ELSE '' END ) AS TEXT) AS Extra FROM sys.columns AS C INNER JOIN sys.types AS T ON( T.user_type_id = C.user_type_id ) INNER JOIN sysobjects AS O ON( O.id = C.object_id ) LEFT JOIN sys.index_columns AS IC ON( IC.column_id = C.column_id AND IC.object_id = O.id ) LEFT JOIN sys.indexes AS I ON( I.index_id = IC.index_id AND I.object_id = O.id AND I.is_primary_key = 1 ) WHERE O.xtype = 'U' AND O.name = :tbl AND C.name LIKE :cpo ORDER BY C.column_id";
    const DBLIB_INFO_CAMPOS = self::MSSQL_INFO_CAMPOS;

    public $driver;
    public $host;
    public $porta;
    public $bd;


    public function __construct($dsn, $username, $passwd, $options = null) {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->infosDSN($dsn);
    } // Fim do método mágico __construct


    /**
     * Identificar as informações de conexão através da string de conexão DSN
     *
     * @param $dsn String DSN de conexão
     */
    public function infosDSN($dsn) {
        if (preg_match('~^(?<driver>[a-z]+):~', $dsn, $dados)) {
            $this->driver = strtoupper($dados['driver']);
        } // Fim if

        if (preg_match('~host=(?<host>[\w\.\-]+)~', $dsn, $dados)) {
            $this->host = $dados['host'];
        } // Fim if

        if (preg_match('~port=(?<porta>[0-9]{1,6})~', $dsn, $dados)) {
            $this->porta = $dados['porta'];
        } // Fim if

        if (preg_match('~dbname=(?<bd>[\w]+)~', $dsn, $dados)) {
            $this->bd = $dados['bd'];
        } // Fim if
    } // Fim do método infosDSN


    /**
     * Paginação de resultados
     *
     * @param string $query Consulta a ser executada
     * @param int    $pgn   Número da página a ser considerada para o cálculo
     * @param int    $qtde  Quantidade de registros a ser exibido nessa página
     * @param bool   $exec  Define se a query criada para a paginação deve ser executada automaticamente (true) ou não
     *                      (false). Quando definido como true o método retorno um Objeto PDO. Do contrário retorna a
     *                      query em formato de string.
     *
     *
     * @return \PDOStatement|string
     */
    public function paginacao($query, $pgn = 1, $qtde = 20, $exec = true) {
        $inicio = $pgn === 1 ? 0 : ($pgn - 1) * $qtde;

        if ($qtde > 0) {
            switch ($this->driver) {
                /*
                 * No MSSQL o início da contagem dos registros é 1 e não 0 como o MYSQL
                 */
                case 'DBLIB':
                case 'MSSQL':
                    $inicio++;

                    $expreg = '~^(SELECT){1}\s+(.+)\s+(FROM){1}\s+(.+)';
                    $expreg .= stripos($query, " WHERE ") === false ? '' : '\s+(WHERE){1}\s+(.+)';
                    $expreg .= stripos($query, " GROUP ") === false ? '' : '\s+(GROUP\s+BY){1}\s+(.+)';
                    $expreg .= stripos($query, " ORDER ") === false ? '' : '\s+(ORDER\s+BY){1}\s+(.+)';
                    $expreg .= '~i';
                    preg_match($expreg, $query, $string);

                    $order_by = array_search("ORDER BY", $string);
                    if ($order_by === false) {
                        $order = $string[2];
                    } else {
                        $order = $string[$order_by + 1];

                        # Remover a cláusula ORDER BY do vetor $string
                        unset($string[$order_by], $string[$order_by + 1]);
                    } // Fim if ... else

                    $clausulas = implode(' ', array_slice($string, 2));

                    # Adicionar o número da linha na query principal
                    $query = "{$string[1]} ROW_NUMBER() OVER (ORDER BY " . trim($order) . ") AS MSSQL_LINHA, {$clausulas}";

                    # Realizar a paginação dos resultados
                    $query = "WITH paginacao AS ({$query}) SELECT * FROM paginacao WHERE MSSQL_LINHA BETWEEN {$inicio} AND " . ($inicio === 1 ? $qtde : $pgn * $qtde);
                    break;

                case 'MYSQL':
                default:
                    # Verificar se a query foi passada com o LIMIT
                    $query = preg_replace('~LIMIT\s+[\d\w,]+~i', '', $query);

                    # Realizar a paginação dos resultados
                    $query .= " LIMIT {$inicio},{$qtde}";
                    break;
            } // Fim switch
        } // Fim if

        return $exec ? $this->query($query) : $query;
    } // Fim do método paginacao


    /**
     * Verificar se uma determnada tabela existe no banco de dados
     *
     * @param string $tbl - nome da tabela a ser verificada
     *
     * @return boolean
     */
    public function tabelaExiste($tbl) {
        return (bool)$this->query("SELECT 1 FROM {$tbl}");
    } // Fim do método tabelaExiste


    /**
     * Obter informações dos campos de uma tabela
     *
     * @param string $tbl Nome da tabela
     * @param string $cpo Filtro para obter os dados de campos específicos
     *
     * @return array
     * @throws DLXExcecao
     */
    public function infoCampos($tbl, $cpo = '%%') {
        if (!$this->tabelaExiste($tbl)) {
            return [];
        } // Fim if

        $c = "static::{$this->driver}_INFO_CAMPOS";

        if (!defined($c)) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('SGBD ou banco de dados não suportado.'), 1400);
        } // Fim if

        $q = constant($c);

        # Verificar se o driver utilizado é o MySQL
        $my = $this->driver === 'MYSQL';

        $sql = $this->prepare($my ? str_replace(':tbl', $tbl, $q) : $q);

        if (!$sql->execute($my ? [':cpo' => $cpo] : [':tbl' => $tbl, ':cpo' => $cpo])) {
            var_dump($sql->errorInfo());
        } // Fim  if

        return $sql->fetchAll();
    } // Fim do método infoCampos


    /**
     * Identificar a chave primária de uma tabela
     *
     * @param string      $tbl     Nome da tabela a ser pesquisada
     * @param string|null $prefixo Se nulo o nome do campo será retornado com o prefixo. Caso contrário deve ser
     *                             informado o prefixo a ser removido
     *
     * @return string|array  String com o nome do campo PK ou um vetor com os nomes (chaves compostas) ou void se
     *                             não encontrar
     * @throws DLXExcecao
     */
    public function identificaPK($tbl, $prefixo = null) {
        if (!$this->tabelaExiste($tbl)) {
            return [];
        } // Fim if

        $c = "static::{$this->driver}_IDENTIFICA_PK";

        if (!defined($c)) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('SGBD ou banco de dados não suportado.'), 1400);
        } // Fim if

        $sql = $this->prepare(constant($c), [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
        $sql->execute([':base' => $this->bd, ':tbl' => $tbl]);

        $campos = array_column($sql->fetchAll(\PDO::FETCH_ASSOC), 'NOME_COLUNA');

        return isset($prefixo) ? array_map(function ($v) use ($prefixo) {
            return preg_replace("~^{$prefixo}~", '', $v);
        }, $campos) : $campos;
    } // Fim do método identificaPK


    /**
     * Alterar os tipos de campos de $tp1 para $tp2 nas tabelas contidas no array $tbls. Se o array for nulo ou estiver
     * vazio, o processo será feito em todas as tabelas
     *
     * @param array  $tbls Array contendo as tabelas onde deve-se executar o procedimento
     * @param string $tp1  Tipo de dado orignal
     * @param string $tp2  Tipo de dado a ser aplicado nos campos que sejam do tipo $tp1
     *
     * @return int
     */
    public function alterarTiposCampos(array $tbls, $tp1, $tp2) {
        # Query para alterar tipo do campo
        $qa = 'ALTER TABLE %s MODIFY %s %s';

        # Contar quantidade de campos alterados
        $qt = 0;

        $sql = $this->query('SHOW TABLES');

        while ($tb = $sql->fetchColumn(0)) {
            if (!empty($tbls) && !in_array($tb, $tbls)) {
                continue;
            } // Fim if

            # Todos os campos da tabela
            $tcs = $this->infoCampos($tb);

            # Campos a serem alteradas
            $cps = array_intersect_key($tcs, preg_grep("~{$tp1}~i", array_column($tcs, 'Type')));

            foreach ($cps as $c) {
                $o = [strtoupper($c['Null']) == 'NO' ? ' NULL' : ' NOT NULL', !is_null($c['Default']) ? "DEFAULT {$c['Default']}" : ''];

                $qt += $this->exec(sprintf($qa, $tb, $c['Field'], $tp2) . implode(' ', $o));
            } // Fim foreach

        } // Fim while

        return $qt;
    } // Fim do método alterarTiposCampos


    /**
     * Incluir o caractere correto de escape de acordo com o banco de dados utilizado
     *
     * @param string $valor String a ser escapada
     *
     * @return string
     */
    public function incluirEscape($valor) {
        if (!is_string($valor)) {
            return $valor;
        } // Fim if

        switch ($this->driver) {
            case 'MSSQL':
            case 'DBLIB':
                $valor_escape = '0x' . unpack('H*hex', $valor)['hex'];
                break;

            case 'MYSQL':
            default:
                $valor_escape = addslashes($valor);
        } // Fim switch

        return $valor_escape;
    } // Fim do método incluirEscape
} // Fim da classe PDODL

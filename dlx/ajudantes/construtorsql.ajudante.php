<?php
/**
 * Created by PhpStorm.
 * User: dlepera
 * Date: 24/05/16
 * Time: 22:23
 */

namespace DLX\Ajudantes;

use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Classes\ConstrutorSQL as ClsConstrutorSQL;
use DLX\Excecao\DLX as DLXExcecao;

class ConstrutorSQL {
    const SQL_CAMPO_COM_ALIAS = "%s AS '%s'";
    const SQL_CASE_SIM_NAO = "( CASE %s WHEN 0 THEN 'Não' WHEN 1 THEN 'Sim' END ) AS '%s'";
    const SQL_IMAGEM_HTML = "CONCAT('<span class=\"mostrar-imagem -mini\"><img src=\"%s', %s, '\" class=\"imagem\" alt=\"\"/></span>') AS '%s'";

    # Funções agregadas
    # Fonte: https://msdn.microsoft.com/en-us/library/ms173454.aspx?f=255&MSPPError=-2147217396
    const MSSQL_AGGREGATE_FUNCTIONS = [
        'AVG', 'CHECKSUM_AGG', 'COUNT', 'COUNT_BIG',
        'GROUPING', 'GROUPING_ID', 'MAX', 'MIN', 'SUM',
        'STDEV', 'STDEVP', 'VAR', 'VARP'
    ];

    public static function validarAndOr($andor) {
        return strtoupper(filter_var($andor, FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '~^(AND|OR|)$~i'],
            'flags'   => FILTER_NULL_ON_FAILURE
        ]));
    } // Fim do método validarAndOr


    public static function validarTipoJoin($tipo_join) {
        return strtoupper(filter_var($tipo_join, FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '~^(INNER|LEFT|RIGHT|)$~i'],
            'flags'   => FILTER_NULL_ON_FAILURE
        ]));
    } // Fim do método validarTipoJoin


    public static function validarTipoOrdenacao($ordenacao) {
        return strtoupper(filter_var($ordenacao, FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '~^(ASC|DESC|)$~i'],
            'flags'   => FILTER_NULL_ON_FAILURE
        ]));
    } // Fim do método validarTipoOrdenacao


    public static function adAlias($alias) {
        return !empty($alias) ? " AS {$alias}" : '';
    } // Fim do método adAlias


    public static function varExportBD($valor) {
        return is_scalar($valor) ? var_export($valor, true) : $valor;
    } // Fim do método varExportBD


    /**
     * Verificar se uma determinada tabela existe no banco de dados
     *
     * @param string $tabela Nome da tabela
     *
     * @throws DLXExcecao
     */
    public static function tabelaExiste($tabela) {
        if (!\DLX::$dlx->bd->tabelaExiste($tabela)) {
            throw new DLXExcecao(sprintf(AjdVisao::traduzirTexto('A tabela <b>%s</b> não foi encontrada na base de dados.'), $tabela), 1404, '-erro', 'html');
        } // Fim if
    } // Fim do método tabelaExiste


    /**
     * @param string $campo  Nome do campo
     * @param string $tabela Nome da tabela
     *
     * @throws DLXExcecao
     */
    public static function campoExiste($campo, $tabela) {
        if (empty(\DLX::$dlx->bd->infoCampos($tabela, $campo))) {
            throw new DLXExcecao(sprintf(AjdVisao::traduzirTexto('O campo <b>%s</b> não foi localizado dentro da tabela <b>%s</b> no banco de dados.'), $campo, $tabela), 1404, '-erro', 'html');
        } // Fim if
    } // Fim do método campoExiste


    /**
     * Selecionar os nomes dos campos de uma determinada tabela e filtrar de acordo com os vetores $incluir_campos e
     * $excluir_campos
     *
     * @param string     $tabela         Nome da tabela
     * @param array|null $incluir_campos Vetor com os nomes dos campos a serem considerados
     * @param array|null $excluir_campos Vetor com os nomes dos campos a serem desconsiderados
     *
     * @return array
     * @throws DLXExcecao
     */
    public static function filtrarCamposTabela($tabela, array $incluir_campos = null, array $excluir_campos = null) {
        $campos = array_filter(\DLX::$dlx->bd->infoCampos($tabela), function ($v) use ($incluir_campos, $excluir_campos) {
            return (empty($incluir_campos) || in_array($v['Field'], $incluir_campos)) &&
            (empty($excluir_campos) || !in_array($v['Field'], $excluir_campos));
        });

        return $campos;
    } // Fim do método filtrarCamposTabela


    /**
     * Criar uma instrução INSERT automaticamente de acordo com a tabela e filtro de campos
     *
     * @param string     $tabela         Nome da tabela
     * @param bool       $inserir_pk     Define se a criação da instrução deve permitir a inclusão em PK
     * @param array|null $incluir_campos Vetor com os nomes dos campos a serem considerados
     * @param array|null $excluir_campos Vetor com os nomes dos campos a serem desconsiderados
     *
     * @return string
     */
    public static function criarInsert($tabela, $inserir_pk = false, array $incluir_campos = null, array $excluir_campos = null) {
        $campos = array_column(array_filter(static::filtrarCamposTabela($tabela, $incluir_campos, $excluir_campos), function ($v) use ($inserir_pk) {
            return (!$inserir_pk && $v['Key'] !== 'PRI') || $inserir_pk;
        }), 'Field');
        $construtor_sql = new ClsConstrutorSQL(\DLX::$dlx->bd, false);


        $insert = $construtor_sql->insert($tabela)->into(
            array_combine(
                $campos,
                array_map(function ($v) use ($inserir_pk) {
                    return ":{$v}";
                }, $campos)
            )
        );

        return [
            'query'  => (string)$insert,
            'campos' => $campos
        ];
    } // Fim do método criarInsert


    /**
     * Criar uma instrução UPDATE automaticamente de acordo com a tabela e filtro de campos
     *
     * @param string     $tabela         Nome da tabela
     * @param array|null $incluir_campos Vetor com os nomes dos campos a serem considerados
     * @param array|null $excluir_campos Vetor com os nomes dos campos a serem desconsiderados
     *
     * @return array
     */
    public static function criarUpdate($tabela, array $incluir_campos = null, array $excluir_campos = null) {
        $campos = static::filtrarCamposTabela($tabela, $incluir_campos, $excluir_campos);
        $construtor_sql = new ClsConstrutorSQL(\DLX::$dlx->bd, false);
        $update = $construtor_sql->update($tabela);

        foreach ($campos as $campo) {
            $campo['Key'] === 'PRI'
                ? $update->where("{$campo['Field']} = :{$campo['Field']}", 'AND')
                : $update->set($campo['Field'], ":{$campo['Field']}");
        } // Fim foreach

        return [
            'query'  => (string)$update,
            'campos' => $campos
        ];
    } // Fim do método criarUpdate


    /**
     * Converter uma cláusula pronta de WHERE ou HAVING para um PREPARE
     *
     * @param string $clausula Cláusula a ser convertida
     * @param string $prefixo  Prefixo a ser utilizado para diferenciar as cláusulas
     *
     * @return array
     */
    public static function clausula2Prepare($clausula, $prefixo = '') {
        $expreg = '~([a-z\_]+)\s*(=|LIKE|<>|>|<|<=|>=)\s*(\'.+\'|\d+)~i';
        $trecho = preg_replace($expreg, '${1} ${2} :' . $prefixo . '${1}', $clausula);
        preg_match_all($expreg, $clausula, $valores);

        return [
            'prepare' => $trecho,
            'valores' => array_combine(array_map(function ($v) use ($prefixo) {
                return ":{$prefixo}{$v}";
            }, $valores[1]), array_map(function ($v) {
                return preg_replace(['~^\'~', '~\'$~'], '', $v);
            }, $valores[3]))
        ];
    } // Fim do método clausula2Prepare


    /**
     * Preparar SELECT formada pelo BaseModeloRegistro
     *
     * @param string                     $lista_campos   Lista de campos a serem incluídas na consulta SELECT
     * @param \DLX\Classes\ConstrutorSQL $construtor_sql Instância do ConstrutorSQL utilizada para criar a SELECT
     *
     * @return mixed
     */
    public static function prepararSelect($lista_campos, $construtor_sql) {
        if (preg_match('~(' . implode('|', static::MSSQL_AGGREGATE_FUNCTIONS) . ')\(~', $lista_campos) && strpos($lista_campos, ',')) {
            preg_match_all('~([A-Z]+\.)?[a-z]+(_[a-z]+)+~', $lista_campos, $nomes_campos);
            $nomes_campos = $nomes_campos[0];
            $construtor_sql->groupBy(
                array_filter($nomes_campos, function ($nome) use ($lista_campos) {
                    foreach (static::MSSQL_AGGREGATE_FUNCTIONS as $funcao) {
                        if (preg_match("~{$funcao}\s*\([\w\s\*\+\-\/,]*{$nome}[\w\s\*\+\-\/,]*\)~", $lista_campos)) {
                            return false;
                        } // Fim if
                    } // Fim foreach

                    return true;
                })
            );
        } // Fim if

        return preg_replace('~^(SELECT\s+)__CAMPOS__(\s+FROM)~', '${1}' . $lista_campos . '${2}', (string)$construtor_sql);
    } // Fim do método prepararSelect
}

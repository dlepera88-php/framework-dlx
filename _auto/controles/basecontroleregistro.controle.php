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


use DLX\Ajudantes\Diversos;
use DLX\Ajudantes\Sessao;
use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Excecao\DLX as DLXExcecao;

abstract class BaseControleRegistro extends BaseControle {
    /**
     * BaseControleRegistro constructor.
     * @param string        $diretorio_visao    Diretório de fontes para a visão
     * @param object|null   $modelo             Objeto do tipo modelo referente a esse controle
     * @param string        $base_dir           Diretório base para gerenciamento dos registros
     */
    public function __construct($dir_visao = '.', $modelo = null, $base_dir = '.') {
        parent::__construct($dir_visao);
        
        if (!empty($modelo)) { 
            $this->setModelo($modelo);
            $this->gerarURLs($base_dir);
        } // Fim if
    } // Fim do método __construct

// URLs ---------------------------------------------------------------------------------------- //
    /**
     * Gerar as URLs principais de registros
     * 
     * @param string $url_lista A URL da lista de registro é utilizada como base para gerar
     * as demais URLs.
     */
    protected function gerarURLs($url_lista) {
        define('URL_IMPLODE', '/:');
        $url_pk = URL_IMPLODE . 'id';

        // Nem todos os registros do sistema estão em banco de dados, portanto eu verifico primeiro
        // se o registro possui uma tabela registrada ou não. Se não tiver, a PK na URL será identificada
        // pelo padrão '/:id'
        if (method_exists($this->modelo, 'getBdTabela')) {
            $url_pk = implode(URL_IMPLODE, \DLX::$dlx->bd->identificaPK($this->modelo->getBdTabela(), $this->modelo->getBdPrefixo()));
            $url_pk = !empty($url_pk) ? URL_IMPLODE . $url_pk : null;
        } // Fim if

        if (trait_exists('Geral\Controles\RegistroConsulta')) {
            $this->url_lista = $url_lista;
            $this->url_detalhes = "{$url_lista}/mostrar-detalhes{$url_pk}";
        } // Fim if
        
        if (trait_exists('Geral\Controles\RegistroEdicao')) {
            $this->url_novo = "{$url_lista}/novo";
            $this->url_editar = "{$url_lista}/editar{$url_pk}";
            $this->url_excluir = "{$url_lista}/excluir";
        } // Fim if
    } // Fim do método gerarURLs

    /**
     * Aplica os valores da PK do registro atual em uma URL
     * @param string $url URL na qual os valores da PK serão aplicados
     * @return string URL com os valores da PK configurados
     */
    protected function PK2URL($url) {
        $pk = $this->modelo->obterValorPK();
        return str_replace(
            array_map(function($k) { return ":{$k}"; }, array_keys($pk)),
            array_values($pk),
            $url
        );
    } // Fim do método PK2URL

    /**
     * Carregar dados enviados via POST no modelo desse controle
     * @throws DLXExcecao
     */
    protected function carregarPost() {
        $campos_pk = \DLX::$dlx->bd->identificaPK($this->modelo->getBdTabela(), $this->modelo->getBdPrefixo());
        $valores_pk = array_intersect_key($_POST, array_combine($campos_pk, array_fill(0, count($campos_pk), '')));

        if (!empty($valores_pk)) {
            $this->modelo->selecionarPK($valores_pk);
        } // Fim if

        Diversos::post2Objeto($this->modelo);
    } // Fim do método carregarPost


    /**
     * Executar uma ação em lote através das PKs dos registros
     *
     * @param string $metodo Nome do método presente no modelo referente a esse
     * controle a ser executado
     * @param string $pk     Nome do campo PK da tabela
     *
     * @return object
     * @throws DLXExcecao
     */
    protected function executarLote($metodo, $pk = 'id') {
        $tid = filter_input(INPUT_POST, $pk, FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY | FILTER_NULL_ON_FAILURE);
        
        if (empty($tid)) {
            throw new DLXExcecao($this->visao->traduzir('Nenhum registro foi encontrado.'), 1404);
        } // Fim if

        # Quantidade total de registros e quantidade excluída
        $qt = (object)'';
        $qt->total = count($tid);
        $qt->modificados = [];

        foreach ($tid as $id) {
            $this->modelo->selecionarPK($id);
            $qt->modificados[] = $this->modelo->{$metodo}();
        } // Fim foreach
        
        return $qt;
    } // Fim do método executarLote
}

trait RegistroConsulta {
    /**
     * URL base da lista de registros
     * @var string
     */
    protected $url_lista;

    /**
     * @var string $url_detalhes
     * URL para exibir os detalhes do registro.
     */
    protected $url_detalhes;

    /**
     * Gerar uma lista de registros padrão
     *
     * @param string $campos         Lista de campos a serem selecionados para gerar a lista
     * @param array  $params_sql     Filtro adicional a ser adiconado a consulta
     * @param int    $qtde           Quantidade de registros a serem exibidos por página na lista
     * @param string $metodo         Método utilizado para selecionar os registros
     * @param string $abrir_links_em Define a forma como os links da lista serão abertos:
     *                               normal: abre o link normamente, obdecendo o atributo target
     *                               popup: carrega o conteúdo do link em forma de popup modal dentro da
     *                               própria página
     *
     * @throws DLXExcecao
     */
    protected function gerarLista($campos = '*', array $params_sql = [], $qtde = -1, $metodo = 'listar', $abrir_links_em = 'normal') {
        if (!method_exists($this->modelo, $metodo)) {
            throw new DLXExcecao(sprintf('O método <b>%s</b> não foi localizado.', $metodo), 1404);
        } // Fim if

        // Se a URL da lista não for informada, o sistema tenta identificar de acordo
        // com a URL
        if (!isset($this->url_lista)) {
            $this->gerarURLs(preg_replace("~^/?{\DLX::$dlx->config('aplicativo', 'home')}/?~", '', \DLX::$dlx->getURL()) . '/');
        } // Fim if

        # Gerar os parâmetros a serem passados para criar a lista de registros
        $params_sql += $this->paramsFiltro();

        # Paginação
        $qtde = $qtde < 0
            ? Sessao::dadoSessao('usuario_pref_num_registros', FILTER_VALIDATE_INT, $qtde)
            : $qtde;

        $lista = $this->modelo->{$metodo}((object)$params_sql, $campos, $get_pagina, $qtde);
        $total_registros = $this->modelo->qtdeRegistros((object)$params_sql);

        # Parâmetros
        $this->visao->adicionarParam('lista', $lista);
        $this->visao->adicionarParam('busca:campo', $get_campo);
        $this->visao->adicionarParam('busca:valor', $get_valor);
        $this->visao->adicionarParam('busca:ordem', $get_ordem);
        $this->visao->adicionarParam('busca:pagina', $get_pagina);
        $this->visao->adicionarParam('info:qtde-total-paginas', $qtde > 0 ? ceil($total_registros / $qtde) : 0);
        $this->visao->adicionarParam('info:qtde-registros-exibidos', count($lista));
        $this->visao->adicionarParam('info:qtde-total-registros', $total_registros);
        $this->visao->adicionarParam('conf:campos-pk', \DLX::$dlx->bd->identificaPK($this->modelo->getBdTabela(), $this->modelo->getBdPrefixo()));
        
        # URL base da lista
        $this->visao->adicionarParam('html:url-lista', $this->url_lista);
        $this->gerarOpcoesPadrao($abrir_links_em);
    } // Fim do método gerarLista


    /**
     * Gerar opções de lista padrão para manipulação dos registros
     * @param string $abrir_links_em Método de abertura dos links:
     * normal => abre a página na mesma janela
     * modal  => abre um modal com o conteúdo da página solicitada
     */
    protected function gerarOpcoesPadrao($abrir_links_em = 'normal') {
        /*
         * Opçoes de execução da lista de acordo com o permissionamento
         */
        $requer_autent = \DLX::$dlx->config('autenticacao') !== false;
        $sessao_ativa = Sessao::sessaoAtiva();
        $classe = get_called_class();

        $perm_inserir = $this->verificarPerm($classe, 'mostrarForm') && $this->verificarPerm($classe, 'inserir');
        $perm_salvar = $this->verificarPerm($classe, 'mostrarForm') && $this->verificarPerm($classe, 'salvar');
        $perms = [
            'inserir?'  => !$requer_autent || ($sessao_ativa && $perm_inserir),
            'detalhes?' => !$requer_autent || ($sessao_ativa && $this->verificarPerm($classe, 'mostrarDetalhes')),
            'editar?'   => !$requer_autent || ($sessao_ativa && $perm_salvar),
            'excluir?'  => !$requer_autent || ($sessao_ativa && $this->verificarPerm($classe, 'excluir'))
        ];

        $this->visao->adicionarParam('html:menu-opcoes', [
            'inserir' => $perms['inserir?'] ? [
                'url'            => $this->url_novo,
                'texto'          => sprintf($this->visao->traduzir('Adicionar %s'), $this->modelo->get__NomeModelo()),
                'abrir-links-em' => $abrir_links_em
            ] : null
        ]);

        $this->visao->adicionarParam('html:opcoes-registro', [
            'detalhes' => $perms['detalhes?'] ? [
                'texto'          => 'D',
                'url'            => $this->url_detalhes,
                'title'          => $this->visao->traduzir('Mostrar detalhes desse registro'),
                'abrir-links-em' => $abrir_links_em,
                'outros-params' => ['class' => 'com-icone -detalhes -so-icone -botao']
            ] : null,

            'editar' => $perms['editar?'] ? [
                'texto'          => 'E',
                'url'            => $this->url_editar,
                'title'          => $this->visao->traduzir('Editar esse registro'),
                'abrir-links-em' => $abrir_links_em,
                'outros-params' => ['class' => 'com-icone -editar -so-icone -botao']
            ] : null,

            'excluir' => $perms['excluir?'] ? [
                'texto'         => 'X',
                'url'           => 'javascript:',
                'title'         => $this->visao->traduzir('Excluir esse registro'),
                'outros-params' => [
                    // Código para selecionar a linha quando o botão 'excluir' for clicado
                    'onclick' => 'listaRegistros.marcarLinhasID(:pk)',
                    'data-mensagem' => $this->visao->traduzir('Deseja realmente excluir esse registro?'),
                    'data-pk' => '{"id"::pk}',
                    'data-func_depois' => 'listaRegistros.excluirLinhasSelecionadas',
                    'data-ajax-msg' => $this->visao->traduzir('Excluindo registro... Por favor, aguarde.'),
                    'class' => 'com-icone -excluir -so-icone -botao'
                ]
            ] : null
        ]);

        $this->visao->adicionarParam('html:opcoes-registro-massa', [
            'excluir' => $perms['excluir?'] ? [
                'url'           => 'javascript:',
                'texto'         => $this->visao->traduzir('Excluir'),
                'title'         => $this->visao->traduzir('Excluir registros selecionados'),
                'outros-params' => [
                    'data-mensagem' => $this->visao->traduzir('Deseja realmente excluir os registros selecionados?'),
                    'data-func_depois' => 'listaRegistros.excluirLinhasSelecionadas',
                    'data-ajax-msg' => $this->visao->traduzir('Excluindo registros selecionados... Por favor, aguarde.'),
                    'class' => 'com-icone -excluir -botao'
                ]
            ] : null
        ]);
    } // Fim do método gerarOpcoesPadrao


    /**
     * Montar o vetor com os parâmetros a serem passados para gerar a lista de
     * registros, com base no $_GET
     * @return array Retorna um vetor com os parâmetros formatados para serem
     * passados ao SQL
     */
    private function paramsFiltro() {
        if (!empty($_GET['termo'])) {
            $get_termo = explode(',', filter_input(INPUT_GET, 'termo'));
            $params_sql = [];

            foreach ($get_termo as $termo) {
                if ((bool)preg_match('~^([\wÀ-ú]+)\:([\w\d\_\s]+)$~', trim($termo), $conf)) {
                    $clausula = count(\DLX::$dlx->bd->infoCampos($this->modelo->getBdTabela(), $conf[1])) > 0 ? 'where' : 'having';
                    $valor = preg_match('~^\d+$~', $conf[2]) ? $conf[2] : '%' . trim($conf[2]) . '%';
                    $params_sql[$clausula][] = sprintf("`%s` LIKE '%s'", $conf[1], $valor);
                } else {
                    $params_sql['where'][] = "()";
                } // Fim if ... else
            } // Fim foreach

            return $params_sql;
        } // Fim if

        return [];
    } // Fim


    /**
     * Gerar uma página de detalhamento
     * Obs: A visão deverá ser incluída no controle
     *
     * @param mixed $pk  Valor da PK do registro a ser selecionado
     *
     * @throws DLXExcecao
     */
    protected function mostrarDetalhes($pk = null) {
        $this->modelo->selecionarPK($pk);
        
        if ($this->modelo->reg_vazio) {
            $this->mostrarMensagemUsuario($this->visao->traduzir('Registro não localizado para gerar a página de detalhamento.'), '-info', 'html');
            $this->visao->mostrarConteudo();
            exit;
        } // Fim if

        # Visão
        $this->visao->adicionarTemplate('comum/visoes/log_registro', -1, true, true);
        $this->visao->adicionarTemplate('comum/visoes/menu_opcoes', -1, true, true);

        # Incluir o link para voltar para a lista de registros
        $this->visao->adicionarParam('html:menu-opcoes', [
            'voltar' => [
                'texto' => $this->visao->traduzir('Voltar'),
                'url'   => $this->url_lista
            ]
        ]);

        if (!$this->modelo->reg_vazio) {
            $mais_links = [];
            $classe = get_class();
            
            # Adicionar opção para excluir o registro, CASO o usuário logado tenha
            # permissão para excluir esse tipo de registro
            if ($this->verificarPerm($classe, 'mostrarForm') && $this->verificarPerm($classe, 'inserir')) {
                $mais_links += [
                    'inserir' => [
                        'texto' => $this->visao->traduzir('Novo'),
                        'url'   => $this->url_novo
                    ]
                ];
            } // Fim if

            if ($this->verificarPerm($classe, 'mostrarForm') && $this->verificarPerm($classe, 'salvar')) {
                // Não incluir o botão de editar se a página solicitada foi a de edição. Isso pode acontecer
                // caso o usuário não tenha permissão para editar o registro e o sistema direcione ele para
                // a página de detalhes
                if (!preg_match('~/editar~', \DLX::$dlx->getURL())) {
                    $mais_links += [
                        'editar' => [
                            'texto' => $this->visao->traduzir('Editar'),
                            'title' => $this->visao->traduzir('Editar esse registro'),
                            'url'   => $this->PK2URL($this->url_editar)
                        ]
                    ];
                } // Fim if
            } // Fim if

            if ($this->verificarPerm($classe, 'excluir')) {
                $mais_links += [
                    'excluir' => [
                        'url'   => 'javascript:',
                        'texto' => $this->visao->traduzir('Excluir'),
                        'title' => $this->visao->traduzir('Excluir esse registro'),
                        'outros-params' => [
                            'data-mensagem' => $this->visao->traduzir('Deseja realmente excluir esse registro?'),
                            'data-url' => $this->url_excluir,
                            'data-pk' => json_encode($this->modelo->obterValorPK()),
                            'data-ajax-msg' => $this->visao->traduzir('Excluindo registro... Por favor, aguarde.'),
                            'class' => 'com-icone -excluir -botao'
                        ]
                    ]
                ];
            } // Fim if

            $this->visao->adicionarParam('html:mais-links', $mais_links);
        } // Fim if

        # Parâmetros
        $this->visao->tituloPagina($this->visao->traduzir('Detalhes'));
        $this->visao->adicionarParam('modelo', $this->modelo);
        $this->visao->adicionarParam('conf:mostrar-log?', true);
    } // Fim do método mostrarDetalhes


    /**
     * Selecionar informações de uma tabela e apresentá-la no formato JSON para atualizar um campo SELECT
     *
     * @param string $valor    Nome do campo (sem prefixo) que servirá como valor do SELECT
     * @param string $texto    Nome do campo (sem prefixo) que servirá como texto de exibição do SELECT
     * @param bool   $retornar Define se o JSON deverá ser retornado pelo método (true) ou apresentado ao usuário
     *                         automaticamente (false)
     *
     * @return mixed
     */
    public function carregarSelect($valor = 'id', $texto = 'nome', $retornar = false) {
        $valor = !empty($valor) ? $valor : 'id';
        $texto = !empty($texto) ? $texto : 'nome';
        $params = new \stdClass;

        if (filter_var($_SERVER['REQUEST_METHOD']) === 'POST') {
            foreach ($_POST as $campo => $valor_campo) {
                $params->where[] = "{$campo} = '" . filter_var($valor_campo, FILTER_DEFAULT) . "'";
            } // Fim foreach
        } // Fim if

        $retorno = $this->modelo->carregarSelect($params, !$retornar, $valor, $texto);

        if ($retornar) {
            return $retorno;
        } // Fim if
    } // Fim do método carregarSelect
}


trait RegistroEdicao {
    /**
     * @var string $url_novo
     * URL utilizada para exibir o formulário de inclusão um novo registro.
     */
    protected $url_novo;

    /**
     * @var string $url_editar
     * URL utilizada para exibir o formulário de edição de um determinado registro.
     */
    protected $url_editar;

    /**
     * @var string $url_excluir
     * URL utilizada para excluir registros.
     */
    protected $url_excluir;


// Formulários --------------------------------------------------------------------------------- //
    /**
     * Gerar configurações padrão para um formulário
     *
     * @param string      $form_id       ID atribuído ao formulário. Será incluído o prefixo 'form-' no HTML
     * @param string      $acao_inclusao Ação a ser executada pelo formulário caso o registro esteja sendo incluído
     * @param string      $acao_edicao   Ação a ser executada pelo formulário caso o registro esteja sendo editado
     * @param mixed|null  $pk            Valor da PK do registro a ser selecionado caso esteja editando
     * @param string      $func_depois   Nome da função Javascript a ser executada após o submit do formulário
     */
    protected function gerarForm($form_id, $acao_inclusao, $acao_edicao, $pk = null, $func_depois = 'formRegistro.registroSalvo') {
        if (!empty($this->modelo)) {
            $this->modelo->selecionarPK($pk);
            
            // Montar as opções do formulário correto. Não preciso incluir a condição para identificar,
            // se o registro será inserido ou editado, pois dentro de cada método já tem a condição 
            // necessária
            !empty($acao_inclusao) and $this->gerarFormInserir($acao_inclusao);
            !empty($acao_edicao) and $this->gerarFormEditar($acao_edicao);

            # Parâmetros
            $this->visao->adicionarParam('modelo', $this->modelo);
            $this->visao->adicionarParam('lista:pk-tabela', $this->modelo->obterValorPK());
        } else {
            $this->visao->adicionarParam('html:form-acao', !empty($acao_inclusao) ? $acao_inclusao : $acao_edicao);
        } // Fim if ... else

        # Incluir o link para voltar para a lista de registros
        $this->visao->adicionarParam('html:menu-opcoes', [
            'voltar' => [
                'texto' => $this->visao->traduzir('Voltar'),
                'url'   => $this->url_lista
            ]
        ]);

        # Parâmetros
        $this->visao->adicionarParam('conf:func-depois', $func_depois);
        $this->visao->adicionarParam('html:form-id', $form_id);
        $this->visao->adicionarParam('conf:form-url-lista', $this->url_lista);
        $this->visao->adicionarParam('conf:form-url-novo', $this->url_novo);
        $this->visao->adicionarParam('conf:form-url-editar', $this->url_editar);
    } // Fim do método gerarForm

    /**
     * Gerar form para incluir um determinado registro.
     * @param string $acao Ação (URL) utilizada para inserir um registro.
     * @return void
     */
    private function gerarFormInserir($acao) {
        if ($this->modelo->reg_vazio) {
            # Parâmetros
            $this->visao->tituloPagina(sprintf($this->visao->traduzir('Adicionar %s'), $this->modelo->get__NomeModelo()));
            $this->visao->adicionarParam('conf:inserindo-registro?', true);
            $this->visao->adicionarParam('conf:mostrar-log?', false);
            $this->visao->adicionarParam('html:form-acao', $acao);
        } // Fim if
    } // Fim do método gerarFormInserir

    /**
     * Gerar form para editar um determinado registro.
     * @param string $acao Ação (URL) utilizada para editar um registro.
     * @return void
     */
    private function gerarFormEditar($acao) {
        if (!$this->modelo->reg_vazio) {
            # Parâmetros
            $this->visao->tituloPagina(sprintf($this->visao->traduzir('Editar %s'), $this->modelo->get__NomeModelo()));
            $this->visao->adicionarParam('conf:inserindo-registro?', false);
            $this->visao->adicionarParam('conf:mostrar-log?', !$this->modelo->log_criacao->reg_vazio || !$this->modelo->log_alteracao->reg_vazio || !$this->modelo->log_exclusao->reg_vazio);
            $this->visao->adicionarParam('html:form-acao', $acao);

            # Menu de opções
            // O menu de opções inclui botões para facilitar a manipulação do registro como adicionar um
            // novo registro ou excluir o registro atual
            $mais_links = [];
            $classe = get_class();

            if ($this->verificarPerm($classe, 'mostrarForm') && $this->verificarPerm($classe, 'inserir')) {
                $mais_links += [
                    'inserir' => [
                        'texto' => $this->visao->traduzir('Novo'),
                        'title' => $this->visao->traduzir('Adicionar um novo registro'),
                        'url'   => $this->url_novo
                    ]
                ];
            } // Fim if

            if ($this->verificarPerm($classe, 'excluir')) {
                $mais_links += [
                    'excluir' => [
                        'url'   => 'javascript:',
                        'texto' => $this->visao->traduzir('Excluir'),
                        'title' => $this->visao->traduzir('Excluir esse registro'),
                        'outros-params' => [
                            'data-mensagem' => $this->visao->traduzir('Deseja realmente excluir esse registro?'),
                            'data-url' => $this->url_excluir,
                            'data-pk' => json_encode($this->modelo->obterValorPK()),
                            'data-func_depois' => 'formRegistro.registroExcluido',
                            'data-ajax-msg' => $this->visao->traduzir('Excluindo registro... Por favor, aguarde.'),
                            'class' => 'com-icone -excluir -botao'
                        ]
                    ]
                ];
            } // Fim if

            $this->visao->adicionarParam('html:mais-links', $mais_links);
        } // Fim if
    } // Fim do método gerarFormEditar


// Manipulação de registros -------------------------------------------------------------------- //
    /**
     * Inserir um determinado registro.
     * Obs: Esse método apenas executa o método salvar, porém foi criado para poder controlar o
     * permissionamento dos usuários para inserir e editar registros.
     */
    protected function inserir() {
        return $this->salvar();
    } // Fim do método inserir

    /**
     * Salvar o registro no banco de dados.
     */
    protected function salvar() {
        $this->carregarPost();
        $id = $this->modelo->salvar(true, null, ["{$this->modelo->getBdPrefixo()}delete"]);
        $this->mostrarMensagemUsuario(sprintf($this->visao->traduzir('O registro de <b>%s</b> foi salvo com sucesso!'), mb_strtolower($this->modelo->get__NomeModelo())), '-sucesso', 'json', ['id' => $id]);
    } // Fim do método salvar


    /**
     * Excluir um ou mais registros do banco de dados.
     */
    protected function excluir() {
        $excluidos = $this->executarLote('excluir');
        $total_excluidos = count($excluidos->modificados);

        if ($total_excluidos > 0) {
            $mensagem = $excluidos->total === 1
                ? $this->visao->traduzir('Registro excluído com sucesso!')
                : sprintf($this->visao->traduzir('Foram excluídos %d registros de um total de %d selecionados.'), $total_excluidos, $excluidos->total);
            $tipo = '-sucesso';
        } else {
            $mensagem = $this->visao->traduzir('Ops! Nenhum registro foi excluído. Por favor, tente novamente.');
            $tipo = '-erro';
        } // Fim if ... else

        $this->mostrarMensagemUsuario($mensagem, $tipo, 'json', ['ids' => $excluidos->modificados]);
    } // Fim do método excluir
}

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


use DLX\Ajudantes\Arquivos;
use DLX\Ajudantes\Sessao;
use DLX\Ajudantes\Vetores;
use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Excecao\DLX as DLXExcecao;

class Visao {
    # Diretórios
    const DIR_VISOES = '%svisoes/';

    # Prefixos de arquivos
    const PRFX_PAGINA_MESTRA = 'mestra';

    # Extensões de arquivos
    const EXT_TEMPLATE = 'phtml';

    # Arquivos
    const ARQUIVO_PAGINA_MESTRA = '%scomum/mestras/%s.%s.%s';

    /**
     * @var array Vetor com todas as áreas do layout a serem preenchidas pelos templates
     *            As áreas de layout devem ser configuradas da seguinte maneira:
     *            Páginas Mastras: [DLX-CONTEUDO/] => Indica que tudo que estiver entre [DLX-CONTEUDO] e
     *            [/DLX-CONTEUDO] proveniente dos templates serão incuídos aqui.
     *
     *            Templates: [DLX-CONTEUDO] Conteúdo do template [/DLX-CONTEUDO] => Indica que esse trecho de conteúdo
     *            será incluído dentro da área [DLX-CONTEUDO/] na página mestra
     */
    protected $areas_layout = ['DLX-HEAD', 'DLX-TOPO', 'DLX-MENU', 'DLX-CONTEUDO', 'DLX-RODAPE', 'DLX-ESTILOS', 'DLX-SCRIPTS'];

    /**
     * @var string Diretório onde serão buscadas as visões a serem renderizadas
     */
    protected $diretorio_visoes;

    /**
     * Nome do tema a ser carregado
     * @var string
     */
    protected $tema = 'padrao';

    /**
     * @var string Nome da página mestra a ser carregada para essa visão
     */
    protected $pagina_mestra;

    /**
     * @var string Caminho completo do arquivo onde está localizado a página mestra
     */
    protected $arquivo_pagina_mestra;

    /**
     * @var string Conteúdo HTML da página mestra
     */
    protected $conteudo_pagina_mestra;

    /**
     * @var string Conteúdo HTML presente nos templates carregados
     */
    protected $conteudo_templates;

    /**
     * TAGs de inclusão dos arquivos externos CSS e JS
     * @var string
     */
    protected $conteudo_arquivos_externos;

    /**
     * @var array Armazenar os templates a serem carregados na visão
     */
    protected $templates = [];

    /**
     * Quando TRUE exibe o conteúdo HTML montado pela Visao automaticamente
     * @var boolean
     */
    protected $exibir_auto = true;

    /**
     * Arquivos CSS a serem carregados
     * @var array
     */
    protected $arquivos_css = [];

    /**
     * Arquivos JavaScripts a serem carregados
     * @var array
     */
    protected $arquivos_js = [];

    /**
     * @var array Vetor de parâmetros as serem passados para a visão
     */
    protected $params = [
        'tag-title' => 'Sem título'
    ];

    /**
     * Classes extras a serem adicionadas em elementos HTML da página mestra.
     * As chaves devem ser os identificadores do elemento e o valor as classes a serem adicionadas.
     * @var array
     */
    protected $classes_extras = [];


    /**
     * @return string
     */
    public function getDiretorioVisoes() {
        return $this->diretorio_visoes;
    }


    /**
     * @param string $diretorio_visoes
     */
    public function setDiretorioVisoes($diretorio_visoes) {
        $this->diretorio_visoes = sprintf(
            static::DIR_VISOES,
            filter_var(preg_replace('~/?$~', '/', $diretorio_visoes), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL)
        );
    }

    public function getTema() {
        return $this->tema;
    }

    public function setTema($tema) {
        $this->tema = filter_var($tema, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }

    /**
     * @return string
     */
    public function getPaginaMestra() {
        return $this->pagina_mestra;
    }


    /**
     * @param string $pagina_mestra
     */
    public function setPaginaMestra($pagina_mestra) {
        // Quando o parâmetro HTML dlx-mestra for passado, ele deve sobre por qualquer outra chamada de
        // alteração de página mestra, pois dessa forma é possível alterar a página mestra a ser carregada
        // através da URL
        if (array_key_exists('dlx-mestra', $_GET)) {
            $pagina_mestra = filter_input(INPUT_GET, 'dlx-mestra');
        } // Fim if

        $this->pagina_mestra = filter_var($pagina_mestra, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);

        # Verificar se a página mestra é válida
        $this->validarPaginaMestra();
    }

    public function isExibirAuto() {
        return (bool)$this->exibir_auto;
    }

    public function setExibirAuto($exibir_auto) {
        $this->exibir_auto = filter_var($exibir_auto, FILTER_VALIDATE_BOOLEAN);
    }


    public function __construct($diretorio = null, $pg_mestra = 'padrao') {
        $this->setDiretorioVisoes($diretorio);
        $this->setPaginaMestra($pg_mestra);
        
        # Carregar os arquivos do tema
        // $this->carregarArquivosTema(\DLX::$dlx->config('aplicativo', 'tema'));

        # Incluir as configurações do aplicativo como parâmetros para acesso pelas visões
        $this->adicionarParam('config-aplicativo', \DLX::$dlx->config('aplicativo'));
        $this->adicionarParam('diretorio-relativo', AjdVisao::diretorioRelativo());
    } // Fim do método __construct


// Áreas do layout -------------------------------------------------------------------------------------------------- //
    /**
     * Incluir uma nova área no layout
     *
     * @param string $area Nome da área
     */
    public function adicionarAreaLayout($area) {
        $this->areas_layout[] = filter_var($area, FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '~^[A-Z0-9\-]+$~'],
            'flags'   => FILTER_NULL_ON_FAILURE
        ]);
    } // Fim do método adicionarAreaLayout


    /**
     * Identificar automaticamente todas as áreas de layout que a página mestra carregada possui
     */
    public function identificarAreas() {
        /* if (empty($this->conteudo_pagina_mestra)) {
            throw new DLXExcecao($this->traduzir('O conteúdo da página mestra ainda não foi carregado!', 'global'), 1403);
        } // Fim if */

        if (preg_match_all('~\[(.+)\/\]~', $this->conteudo_pagina_mestra, $areas)) {
            $this->areas_layout = $areas[1];
        } // Fim if
    } // Fim do método identificarAreas


// Páginas mestras -------------------------------------------------------------------------------------------------- //
    private function validarPaginaMestra() {
        $this->arquivo_pagina_mestra = sprintf(
            static::ARQUIVO_PAGINA_MESTRA,
            $this->diretorio_visoes,
            $this->pagina_mestra,
            static::PRFX_PAGINA_MESTRA,
            static::EXT_TEMPLATE
        );

        if (!file_exists($this->arquivo_pagina_mestra)) {
            $this->arquivo_pagina_mestra = Arquivos::procurarDiretoriosAntecessores($this->arquivo_pagina_mestra, $this->diretorio_visoes);

            if ($this->arquivo_pagina_mestra === false || !file_exists($this->arquivo_pagina_mestra)) {
                throw new DLXExcecao(
                    sprintf($this->traduzir('A página mestra <b>%s</b> não foi localizada ou não é um arquivo de página mestra válido.', 'global'), $this->pagina_mestra),
                    1404, '-erro', 'texto'
                );
            } // Fim if
        } // Fim if
    } // Fim do método validarPaginaMestra


    /**
     * Carregar o conteúdo da página mestra e armazenar na propriedade $conteudo_html
     */
    public function carregarPaginaMestra() {
        ob_start();
        include_once $this->arquivo_pagina_mestra;
        $this->conteudo_pagina_mestra = $this->carregarClassesExtras(ob_get_contents());
        ob_end_clean();
        
        $this->identificarAreas();
    } // Fim do método carregarPaginaMestra

    /**
     * Adicionar classes extras a elementos HTML da página mestra.
     *
     * @param string Identificador do trecho HTML. Se o identificador iniciar com ., será procurado um elemento
     * com a seguinte classe, ao iniciar com #, será procurado um elemento com aquele ID e se não iniciar com
     * nenhum desses carcateres, será procurado a seguinte tag. Funciona de forma semelhante aos seletores jQuery.
     * @param string $classes Lista de classes a serem adicionadas ao elemento HTML indicado.
     * @return void
     */
    public function adicionarClassesExtras($elemento_html, $classes) {
        $this->classes_extras[$elemento_html] .= trim(" {$classes}");
    } // Fim do método adicionarClassesExtras

    /**
     * Carregar as classes extras nos elementos conforme configurado.
     *
     * @param string $html HTML a ser modificado.
     * @return void
     */
    private function carregarClassesExtras($html) {
        $identificar_tags = function($seletor, $html) {
            define('SELETOR_TAG', '~<%s.*/?>~');
            define('SELETOR_ID', '~<.*id="%s".*/?>~');
            define('SELETOR_CLASSE', '~<.*class="[\w\d\s-]*\s?%s\s?[\w\d\s-]*".*/?>~');

            // Verifico o primeiro caractere do seletor para saber como procurar
            switch ($seletor[0]) {
                // IDs
                case '#': $usar_seletor = sprintf(SELETOR_ID, substr($seletor, 1)); break;
                case '.': $usar_seletor = sprintf(SELETOR_CLASSE, substr($seletor, 1)); break;
                default: $usar_seletor = sprintf(SELETOR_TAG, $seletor); break;
            } // Fim switch

            preg_match($usar_seletor, $html, $tags);

            return $tags;
        };

        $alterar_tag = function ($tag, $classes) {
            define('TAG_COM_CLASS', '~class="([\w\d\s-]+)"~i');
            define('TAG_SEM_CLASS', '~(/?>$)~');

            return preg_match('~class=~', $tag)
                ? preg_replace(TAG_COM_CLASS, "class=\"$1 {$classes}\"", $tag)
                : preg_replace(TAG_SEM_CLASS, " class=\"{$classes}\"$1", $tag);
        };

        foreach ($this->classes_extras as $seletor => $classes) {
            $tags = $identificar_tags($seletor, $html);

            foreach ($tags as $tag) {
                $tag_alterada = $alterar_tag($tag, $classes);
                $html = str_replace($tag, $tag_alterada, $html);
            } // Fim foreach        
        } // Fim foreach
        
        return $html;
    } // Fim do método carregarClassesExtras


// Templates -------------------------------------------------------------------------------------------------------- //
    /**
     * Incluir um template ao conteúdo a ser exibido
     *
     * @param string $template Caminho relativo do template a ser incluído
     * @param int    $ordem    Ordem sequencial de carregamento
     * @param bool   $procurar Se true, procura o template em diretórios antecessores
     *
     * @throws DLXExcecao
     */
    public function adicionarTemplate($template, $ordem = -1, $procurar = true, $opcional = false) {
        $caminho_template = "{$this->diretorio_visoes}{$template}." . static::EXT_TEMPLATE;

        if (!file_exists($caminho_template) || !is_file($caminho_template)) {
            if ($procurar) {
                $caminho_template = Arquivos::procurarDiretoriosAntecessores($caminho_template, $this->diretorio_visoes);
            } // Fim if

            if ($caminho_template === false || !$procurar) {
                if (!$opcional) {
                    throw new DLXExcecao(sprintf($this->traduzir('O template <b>%s</b> não foi localizado ou não é um arquivo de template válido.'), $template), 1404, '-info', 'html');
                } else {
                    return;
                } // Fim
            } // Fim if
        } // Fim if

        $ordem > -1
            ? $this->templates[$ordem] = $caminho_template and ksort($this->templates)
            : $this->templates[] = $caminho_template;
    } // Fim do método adicionarTemplate


    /**
     * Localizar e remover um determinado template
     *
     * @param string $template Caminho / nome do template a ser removido
     */
    public function removerTemplate($template) {
        $posicoes = array_keys(preg_grep("~{$template}." . static::EXT_TEMPLATE . "~", $this->templates));

        foreach ($posicoes as $chave) {
            unset($this->templates[$chave]);
        } // Fim foreach
    } // Fim do método removerTemplate


    /**
     * Carregar conteúdo HTML contido nos templates
     */
    public function carregarTemplates() {
        ob_start();

        foreach ($this->templates as $tpl) {
            // include_once $tpl
            include $tpl;
        } // Fim foreach

        $this->conteudo_templates = ob_get_contents();

        ob_end_clean();
    } // Fim do método carregarTemplates


// Arquivos externos -------------------------------------------------------- //
    /**
     * Adicionar um arquivo CSS ao carregamento da visão
     * @param  string $css Caminho relativo do arquivo CSS
     * @param  int    $ordem [opcional] Ordem na qual o arquivo CSS deve ser incluído.
     * Quando não informado, o arquivo é incluído no final do array
     * @return void
     */
    public function adicionarCSS($css, $ordem = null) {
        if (array_search($css, $this->arquivos_css) === false) {
            $css = AjdVisao::diretorioRelativo() . $css;
            $this->arquivos_js = Vetores::adicionarValorPos($this->arquivos_css, $css, $ordem);
        } // Fim if
    } // Fim do método adicionarCSS


    /**
     * Remover um arquivo CSS
     * @param  string $css Caminho relativo do arquivo CSS
     * @return void
     */
    public function removerCSS($css) {
        $key = array_search($css, $this->arquivos_css);
        if ($key !== false) {
            unset($this->arquivos_css[$key]);
        } // Fim if
    } // Fim do método removerCSS


    /**
     * Adicionar um arquivo JS ao carregamento da visão
     * @param  string $css   Caminho relativo do arquivo JS
     * @param  int    $ordem [opcional] Ordem na qual o arquivo JS deve ser incluído.
     * Quando não informado, o arquivo é incluído no final do array
     * @return void
     */
    public function adicionarJS($js, $ordem = null) {
        if (array_search($js, $this->arquivos_js) === false) {
            $js = AjdVisao::diretorioRelativo() . $js;
            $this->arquivos_js = Vetores::adicionarValorPos($this->arquivos_js, $js, $ordem);
        } // Fim if
    } // Fim do método adicionarJS


    /**
     * Remover um arquivo JS
     * @param  string $css Caminho relativo do arquivo JS
     * @return void
     */
    public function removerJS($js) {
        $key = array_search($js, $this->arquivos_js);
        if ($key !== false) {
            unset($this->arquivos_js[$key]);
        } // Fim if
    } // Fim do método removerJS

    /**
     * Listar todos os arquivos JS adicionados até agora.
     * @return array Retorna um array com os caminhos de cada arquivo JS
     */
    public function listaArquivosJS() {
        return $this->arquivos_js;
    } // Fim do método listaArquivosJS


    /**
     * Montar as TAGs HTML para inclusão dos arquivos JS e CSS externos
     * @return void
     */
    protected function carregarArquivosExternos() {
        // Montar HTML dos arquivos CSS
        if (count($this->arquivos_css) > 0) {
            $this->conteudo_arquivos_externos .= '[DLX-ARQUIVOS-CSS]';

            foreach ($this->arquivos_css as $css) {
                $this->conteudo_arquivos_externos .= sprintf('<link rel="stylesheet" type="text/css" href="%s"/>' . "\n", $css);
            } // Fim foreach

            $this->conteudo_arquivos_externos .= '[/DLX-ARQUIVOS-CSS]';
        } // Fim if

        if (count($this->arquivos_js) > 0) {
            $this->conteudo_arquivos_externos .= '[DLX-ARQUIVOS-JS]';

            foreach ($this->arquivos_js as $js) {
                $this->conteudo_arquivos_externos .= sprintf('<script src="%s"></script>' . "\n", $js);
            } // Fim foreach

            $this->conteudo_arquivos_externos .= '[/DLX-ARQUIVOS-JS]';
        } // Fim if
    } // Fim do método carregarArquivosExternos


    /**
     * Carregar arquivos CSS e JS referentes ao tema indicado
     * @param  string $tema Diretório relativo do tema. O tema deve estar instalado
     * no diretório configurado em DLX::DIR_TEMAS
     * @return void
     */
    public function carregarArquivosTema($tema = null) {
        $dir_tema = \DLX::DIR_TEMAS . "{$tema}/";
        $funcao = function ($arquivo) {
            return AjdVisao::diretorioRelativo() . $arquivo;
        };

        # Adicionar os arquivos CSS
        // $this->arquivos_css += array_map($funcao, Arquivos::filtrarPrefixo("{$dir_tema}css/", 'tema', 'css'));
        $this->arquivos_css = array_merge($this->arquivos_css, array_map($funcao, Arquivos::filtrarPrefixo("{$dir_tema}css/", 'tema', 'css')));

        # Adicionar os arquivos JS
        // $this->arquivos_js += array_map($funcao, Arquivos::filtrarPrefixo("{$dir_tema}js/", 'tema-min', 'js'));
        $this->arquivos_js = array_merge($this->arquivos_js, array_map($funcao, Arquivos::filtrarPrefixo("{$dir_tema}js/", 'tema-min', 'js')));
    } // Fim do método carregarArquivosTema


// Parâmetros ------------------------------------------------------------------------------------------------------- //
    /**
     * Adicionar parâmetros às visões
     *
     * Para recuperar o valor de um parâmetro em uma visão deve-se utilizar o método ->obterParams.
     * Ex: $visao->obterParams('nome_do_parametro');
     *
     * Ou recuperar todos os parâmetros de uma vez. Para isso, basta não especificar o nome do parâmetro
     * Ex: $visao->obterParams();
     *
     * Isso retornará o vetor completo de parâmetros
     *
     * @param string $nome   Nome do parâmetro
     * @param mixed  $valor  Valor a ser atribuído ao parâmetro
     * @param int    $filtro Filtro a ser aplicado ao parâmetro
     */
    public function adicionarParam($nome, $valor, $filtro = FILTER_DEFAULT) {
        $nome = filter_var($nome, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);

        $this->params[$nome] = is_scalar($valor) && !is_bool($valor)
            ? filter_var($valor, $filtro)
            : $valor;
    } // Fim do método adicionarParams


    /**
     * Obter o valor de um determinado parâmetro ou todos os parâmetros de uma vez
     *
     * Utilização:
     *
     * 1 - Retornar o valor de um parâmetro específico:
     * $param = $visao->obterParams('nome_do_parametro');
     * echo $param;
     * ---
     * 2 - Retornar um vetor com todos os parâmetros existentes:
     * $params = $visao->obterParams();
     * echo $params['nome_de_um_param'];
     * echo $params['nome_de_outro_param'];
     * ---
     * 3 - Opcionalmente também pode ser utilizado o extract() para criar variáveis a partir dos parâmetros:
     * $params = $visao->obterParams();
     * extract($params);
     * echo $nome_de_um_param;
     * echo $nome_de_outro_param;
     *
     * Obs.: Para utilizar esse método é necessário certificar-se de não incluir nomes de parâmetros com caracteres que
     * não sejam aceitos para nomenclatura de variáveis
     *
     * @param string|null $nome [opcional] Nome do parâmetro a ser retornado. Se null, retorna um vetor contendo todos
     *                          os parâmetros
     *
     * @return mixed
     */
    public function obterParams($nome = null) {
        if (!isset($nome)) {
            /**
             * Se o nome do parâmetro não for especificado retornar um vetor com todos os parâmetros
             */
            return $this->params;
        } // Fim if

        /**
         * Se o nome do parâmetro for espcificado e existir retornar o valor do mesmo
         */
        return array_key_exists($nome, $this->params)
            ? $this->params[$nome]
            : '<p class="mostrar-msg -atencao">' . sprintf($this->traduzir('O parâmetro de visão <b>%s</b> não existe.'), $nome) . '</p>';
    } // Fim do método obterParams


    /**
     * Definir o título da página
     *
     * @param string $titulo Título a ser definido para a página
     */
    public function tituloPagina($titulo) {
        $titulo = filter_var($titulo, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);

        $this->adicionarParam('tag-title', $titulo);
        $this->adicionarParam('html:titulo-pagina', $titulo);
    } // Fim do método tituloPagina


// Conteúdo --------------------------------------------------------------------------------------------------------- //
    /**
     * Renderizar o conteúdo HTML e mostrar ao usuário.
     *
     * @return void
     */
    public function mostrarConteudo() {
        $this->carregarArquivosTema($this->getTema());

        if (!isset($this->conteudo_pagina_mestra)) {
            $this->carregarPaginaMestra();
        } // Fim if

        if (!isset($this->conteudo_templates)) {
            $this->carregarTemplates();
        } // Fim if

        if (!isset($this->conteudo_arquivos_externos)) {
            $this->carregarArquivosExternos();
        } // Fim if

        $conteudo_html = '';

        if (!empty($this->conteudo_pagina_mestra) && !empty($this->conteudo_templates)) {
            $conteudo_html = $this->conteudo_pagina_mestra;
            $conteudo_total = $this->conteudo_templates . $this->conteudo_arquivos_externos;

            foreach ($this->areas_layout as $area) {
                $conteudo_html = str_replace("[{$area}/]", AjdVisao::extrairAreaLayout($conteudo_total, $area), $conteudo_html);
            } // Fim foreach
        } // Fim if

        if ($this->isExibirAuto()) {
            echo $conteudo_html;
        } // Fim if

        return $conteudo_html;
    } // Fim do método mostrarConteudo


// Traduções -------------------------------------------------------------------------------------------------------- //
    /**
     * Traduzir um determinado texto com base no pacote de idiomas carregado
     *
     * @param string $texto   Texto a ser traduzido
     * @param string $dominio Nome do domínio a ser considerado para essa tradução
     * @param null   $idioma  Sigla do idioma a traduzir. Quando não informado o sistema tenta usar a preferência do
     *                        usuário ou o idioma que foi configurado no arquivo de configurações
     *
     * @return mixed
     */
    public function traduzir($texto, $dominio = 'global', $idioma = null) {
        return AjdVisao::traduzirTexto($texto, $dominio, $idioma);
    } // Fim do método traduzir
} // Fim da Classe Visao

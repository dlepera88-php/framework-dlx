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

use DLX\Ajudantes\Arquivos;
use DLX\Ajudantes\Strings;
use DLX\Classes\PDODL;
use DLX\Classes\Roteador;

class DLX {
    /**
     * Padrão do nome do arquivo de configuração
     * @var string
     */
    const ARQUIVO_CONFIG = 'config/%s.config.php';

    /**
     * Diretório onde estão as classes do Framework DLX
     * @var string
     */
    const DIR_BIBLIOTECA = 'dlx/classes/';

    /**
     * Diretório onde serão instalados os aplicativos
     * @var string
     */
    const DIR_APLICATIVO = 'aplicativos/%s/';

    /**
     * Diretório de um determinado módulo dentro de um determinado aplicativo
     * @var string
     */
    const DIR_MODULOS = 'aplicativos/%s/modulos/%s/';

    /**
     * Diretório relativo a ser carregado automaticamente de acordo com o aplicativo
     * ou módulo
     * @var string
     */
    const DIR_AUTO = '_auto/';

    /**
     * Diretório relativo onde estrão os controles do módulo / aplicativo
     * @var string
     */
    const DIR_CONTROLES = 'controles/';

    /**
     * Diretório relativo onde estão os modelos do módulo / aplicativo
     * @var string
     */
    const DIR_MODELOS = 'modelos/';

    /**
     * Diretório relativo onde estão as visões do módulo / aplicativo
     * @var string
     */
    const DIR_VISOES = 'visoes/';

    /**
     * Diretório relativo onde estão os pacotes de idiomas do módulo / aplicativo
     * @var string
     */
    const DIR_IDIOMAS = 'web/idiomas/';

    /**
     * Diretório relativo onde estão os arquivos de rotas do módulo / aplicativo
     * @var string
     */
    const DIR_ROTAS = 'web/rotas/';

    /**
     * Diretório relativo onde estão os temas
     * @var string
     */
    const DIR_TEMAS = 'web/temas/';

    /**
     * Diretório relativo onde estão os temas
     * @var string
     */
    const DIR_JS = 'web/js/';

    # Prefixos de extensão de arquivos
    /**
     * Prefixo da extensão dos arquivos de rota
     * @var string
     */
    const PRFIX_ROTAS = 'rota';

    /**
     * Prefixo da extensão dos arquivos de idioma
     * @var string
     */
    const PRFIX_IDIOMAS = 'idioma';

    /**
     * Prefixo da extensão dos arquivos de modelo
     * @var string
     */
    const PRFIX_MODELOS = 'modelo';

    /**
     * Prefixo da extensão dos arquivos de classes diversas
     * @var string
     */
    const PRFIX_BIBLIOTECA = 'classe';

    /**
     * Prefixo da extensão dos arquivos de temas
     * @var string
     */
    // const PRFIX_TEMAS = 'tema';

    /**
     * Prefixo da extensão dos arquivos de controles
     * @var string
     */
    const PRFIX_CONTROLES = 'controle';

    /**
     * Propriedade estática que receberá a instância dessa classe, utilizando o
     * padrão Singleton
     * @var DLX
     */
    public static $dlx;

    /**
     * Vetor multidimensional com as configurações que serão aplicadas ao
     * Framework DLX
     * @var array
     */
    protected $config = [
        'aplicativo' => [
            'raiz'      => '/',
            'home'      => '',
            'nome'      => 'Framework DLX',
            'idioma'    => 'pt_BR',
            'encoding'  => 'UTF-8',
            'dir-imgs'  => 'web/imgs/',
            'dir-js'    => 'web/js/',
            'favicon'   => 'web/imgs/favicon.ico'
        ],

        'bd' => [
            'dsn'     => 'mysql:host=localhost',
            'usuario' => 'root',
            'senha'   => '',
            'formato-data'  => [
                'data'      => 'Y-m-d',
                'hora'      => 'H:i:s',
                'completo'  => 'Y-m-d H:i:s'
            ]
        ],

        /**
         * Define as opções de autenticação do aplicativo. Se for definido como
         * false, o aplicativo não irá solicitar usuário e senha pra exibir seu
         * conteúdo
         */
        'autenticacao' => false
    ];

    /**
     * Propriedade que receberá a instância do banco de dados
     * @var PDODL
     */
    public $bd;

    /**
     * Vetor multidimensional contendo as rotas adicionadas ao Framework DLX e
     * que serão passadas a classe Roteador para executar uma determinada ação
     * @var array
     */
    protected $rotas = [
        'get'   => [],
        'post'  => []
    ];

    /**
     * Nome do aplicativo a ser executado dentro do Framework DLX
     * @var string
     */
    protected $aplicativo;

    public function getAplicativo() {
        return $this->aplicativo;
    }

    public function setAplicativo($aplicativo) {
        $this->aplicativo = filter_var($aplicativo, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }

    /**
     * Nome do ambiente que deve ser carregado. Influencia diretamente nas configurações.
     * @var string
     */
    protected $ambiente;

    public function getAmbiente() {
        return $this->ambiente;
    }

    public function setAmbiente($ambiente) {
        $this->ambiente = filter_var($ambiente, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }

    /**
     * URL a ser considerada para aplicar na rota e identificar o que deve ser
     * executado
     * @var string
     */
    protected $url;

    public function getURL() {
        return $this->url;
    }

    public function setURL($url) {
        $this->url = str_replace('index.php', '', filter_var($url, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL));
    }

    /**
     * Nome do módulo que está sendo executado nesse momento
     * @var string
     */
    protected $modulo_atual;

    public function getModuloAtual() {
        return $this->modulo_atual;
    }

    public function setModuloAtual($modulo_atual) {
        $this->modulo_atual = filter_var($modulo_atual, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    }

    /**
     * DLX constructor.
     * @param string $aplicativo
     * @param string $ambiente
     * @param string $url
     * @throws Exception
     */
    public function __construct(string $aplicativo, string $ambiente = 'dev', string $url = '')
    {
        self::$dlx = $this;

        $this->setAplicativo($aplicativo);
        $this->setAmbiente($ambiente);
        $this->setURL($url);

        # Registrar a função para carregar as classes
        spl_autoload_register(__NAMESPACE__ . '\\DLX::autoloadClasses');

        # Carregar as configurações
        $this->carregarConfiguracao();

        if ($this->config('aplicativo', 'https')) {
            $this->redirecionarParaHTTPS();
        } // Fim if

        # Carregar o idioma solicitado
        $this->carregarIdioma($this->config('aplicativo', 'idioma'));

        # Carregar arquivos automaticos do Framework DLX
        $this->carregarAuto();

        # Carregar arquivos automaticos do aplicativo
        $this->carregarAuto($this->diretorioAplicativo());

        # Conectar ao banco de dados
        $this->conectarBD();
    } // Fim do método __construct


    /**
     * Exeutar a ação solicitada pela URL
     * @return void
     */
    public function executar() {
        $roteador = new Roteador($this->rotas);
        $controle = $roteador->obterRota($this->getURL());

        $this->carregarModulo($controle->getModulo());

        return $controle->executar();
    } // Fim do método executar


// Configurações ------------------------------------------------------------ //
    /**
     * Carregar as configurações presentes no arquivo de configurações
     * @return void
     */
    protected function carregarConfiguracao () {
        $arquivo = 'config.inc.php';

        if (!file_exists($arquivo)) {
            throw new Exception(sprintf('Arquivo de configuração <b>%s</b> não encontrado.', $arquivo), 404);
        } // Fim if

        include_once $arquivo;

        if (isset($config)) {
            $this->alterarConfiguracao($config);
        } // Fim if
    } // Fim do método carregarConfiguracao


    /**
     * Alterar as configurações
     * @param  array  $nova_config Configuração a ser aplicada
     * @return void
     */
    public function alterarConfiguracao($nova_config = []) {
        $this->config = array_replace_recursive($this->config, $nova_config);
    } // Fim do método alterarConfiguracao


    /**
     * Obter uma ou todas as configurações do Framework DLX.
     * @param  string $grupo Nome do grupo de configurações
     * @param  string $nome  Nome da configuração em si
     * @return mixed         Retorna o valor da configuração solicitada. Se a
     * configuração não for encontrada, retorna null e gera um log.
     */
    public function config($grupo = null, $nome = null) {
        if (empty($grupo) && empty($nome)) {
            return $this->config;
        } elseif (!empty($grupo) && empty($nome)) {
            if (array_key_exists($grupo, $this->config)) {
                return $this->config[$grupo];
            } // Fim if
        } else {
            if (array_key_exists($grupo, $this->config) && array_key_exists($nome, $this->config[$grupo])) {
                return $this->config[$grupo][$nome];
            } // Fim if
        } // Fim if ... elseif ... else

        // Gravar um log
        $this->gravarLog("Configuração [{$grupo}][{$nome}] não encontrada", 'AVISO');

        return null;
    } // Fim do método config


// Classes ------------------------------------------------------------------ //
    /**
     * Auto carregar classes de acordo com o seu namespace. Essa função deve ser
     * estática, para que o spl_autoload_register possa executá-la
     * @param  string $classe Nome da classe
     * @return boolean|void   Retorna false apenas em  em caso de erro.
     */
    public static function autoloadClasses($classe) {
        # Identificar o formato da classe
        $formato = explode('\\', $classe);

        switch (count($formato)) {
            case 4:
                list($aplicativo, $modulo, $tipo, $controle) = $formato;
                $diretorio_aplicativo = Strings::PSR2URL($aplicativo); // strtolower(preg_replace('~([a-z])([A-Z])~', '${1}-${2}', $aplicativo));
                $diretorio_modulo = Strings::PSR2URL($modulo); // strtolower(preg_replace('~([a-z])([A-Z])~', '${1}-${2}', $modulo));
                $arquivo = strtolower("aplicativos/{$diretorio_aplicativo}/modulos/{$diretorio_modulo}/{$tipo}/{$controle}." . preg_replace('~s$~', '', $tipo) . '.php');
                break;

            case 3:
            // default:
                list(, $tipo,) = explode('\\', strtolower($classe));
                $arquivo = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($classe)) . '.' . preg_replace('~s$~', '', $tipo) . '.php';

                // Quando a classe está em um diretório comum, tento verificar
                // nos diretórios acima par alocalizá-lo
                if (preg_match('~^comum~', $arquivo)) {
                    $arquivo = Arquivos::procurarDiretoriosAntecessores($arquivo, sprintf(\DLX::DIR_APLICATIVO, \DLX::$dlx->getAplicativo()));
                } // Fim if

                break;

            default: return false;
        } // Fim switch

        if (file_exists($arquivo)) {
            return require_once $arquivo;
        } else {
            self::gravarLog("Arquivo de classe {$arquivo} não localizado!", 'ERRO');
            return false;
        } // Fim if
    } // Fim do método autoloadClasses


// Aplicativos -------------------------------------------------------------- //
    /**
     * Montar o diretório completo do aplicativo atual
     * @return string Retorna o diretório completo do aplicativo atual
     */
    protected function diretorioAplicativo() {
        return sprintf(self::DIR_APLICATIVO, $this->aplicativo);
    } // Fim do método diretorioAplicativo


// Banco de dados ----------------------------------------------------------- //
    /**
     * Conectar ao banco de dados
     * @return void
     */
    protected function conectarBD() {
        $config_bd = $this->config('bd');

        if (!empty($config_bd)) {
            $this->bd = new PDODL($config_bd['dsn'], $config_bd['usuario'], $config_bd['senha']);
            $this->bd->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->bd->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        } // Fim if
    } // Fim do método conectarBD


// Rotas -------------------------------------------------------------------- //
    /**
     * Adicionar uma rota ao Framework DLX
     * @param string       $rota   Expressão Regular representativa da rota
     * @param string|array $config Configuração da rota. Para onde a rota vai
     * levar o usuário
     * @param string|array $metodo Método da requisição a qual essa rota deve ser
     * aplicada, GET ou POST. Pode ser informado um array com os 2 métodos,
     * caso a rota seja para ambos os métodos
     * @param bool $sobrepor Informa se essa rota deve sobrepor uma rota já existente.
     */
    public function adicionarRota($rota, $config, $metodo = 'get', $sobrepor = false) {
        if (isset($metodo)) {
            if (is_array($metodo)) {
                foreach ($metodo as $m) {
                    $this->adicionarRota($rota, $config, $m, $sobrepor);
                } // Fim foreach
            } else {
                if (!array_key_exists($rota, $this->rotas[$metodo]) || $sobrepor) {
                    $app_home = $this->config('aplicativo', 'home');
                    $rota = str_replace('%home%', $app_home, $rota);

                    if (!empty($app_home)) {
                        // Adicionar um pulo para posicionar os parâmetros corretamente em relação a
                        // rota que está sendo configurada. Essa ação deve ser feita apenas quando a rota
                        // define parâmetros via URL. Quando a rota envia parâmetros fixos, através de um
                        // array, não é necessário adicionar o pulo.
                        if (is_array($config) && array_key_exists('params', $config)) {
                            if (is_string($config['params'])) {
                                $config['params'] = "/-{$config['params']}";
                            } // Fim if
                        } elseif (is_string($config)) {
                            $config = "/-{$config}";
                        } // Fim if ... else
                    } // Fim if

                    $this->rotas[$metodo][$rota] = $config;
                } // Fim if
            } // Fim if
        } // Fim if ... else
    } // Fim do método adicionarRota

    /**
     * Excluir uma determinada rota.
     * @param string $rota Rota a ser excluída.
     * @param string|array $metodo Método onde a rota foi registrada.
     * @return void
     */
    public function excluirRota($rota, $metodo = 'get') {
        if (is_string($metodo)) {
            unset($this->rotas[$metodo][$rota]);
        } else {
            foreach ($metodo as $m) {
                $this->excluirRota($rota, $m);
            } // Fim foreach
        } // Fim if
    } // Fim do método excluirRota


// Auto --------------------------------------------------------------------- //
    /**
     * Carregar arquivos automaticamente
     * Arquivos no diretório _auto de um aplicativo ou módulo devem ser carregados
     * automaticamente
     * @param string|null $diretorio Diretório relativo a ser considerado para
     * localização do diretório _auto
     */
    private function carregarAuto($diretorio = null) {
        $_auto = $diretorio . static::DIR_AUTO;
        $idioma = $this->config('aplicativo', 'idioma');

        if (is_dir($_auto)) {
            $diretorios = [
                static::PRFIX_CONTROLES => $_auto . static::DIR_CONTROLES,
                static::PRFIX_MODELOS   => $_auto . static::DIR_MODELOS,
                static::PRFIX_IDIOMAS   => $_auto . static::DIR_IDIOMAS . "{$idioma}/",
                static::PRFIX_ROTAS     => $_auto . static::DIR_ROTAS
            ];

            foreach ($diretorios as $pfx => $dir) {
                \DLX\Ajudantes\Arquivos::carregarArquivos($dir, $pfx);
            } // Fim foreach
        } // Fim if
    } // Fim do método carregarAuto


// Módulos ------------------------------------------------------------------ //
    /**
     * Carregar arquivos referentes ao módulo atual
     *
     * @throws DLXExcecao
     */
    private function carregarModulo($modulo) {
        # Registrar o carregamento desse módulo
        $this->setModuloAtual($modulo);

        # Carregar diretório _auto do módulo
        $this->carregarAuto(sprintf(self::DIR_MODULOS, $this->aplicativo, strtolower($modulo)));
    } // Fim do método carregarModulo


// Idioma ------------------------------------------------------------------- //
    /**
     * Carregar arquivos de idioma.
     *
     * @param string $idioma Identificador do idioma a ser carregado.
     * @return void
     */
    public function carregarIdioma($idioma) {
        if (preg_match(EXPREG_IDIOMA, $idioma)) {
            $idioma = str_replace('-', '_', $idioma);

            if ($this->config('aplicativo', 'idioma') !== $idioma) {
                $this->alterarConfiguracao(['aplicativo' => ['idioma' => $idioma]]);
            } // Fim if

            if(function_exists('shell_exec')) {
                $encoding = $this->config('aplicativo', 'encoding');
                $locales = explode("\n", shell_exec('locale -a'));

                if (in_array("{$idioma}.{$encoding}", $locales)) {
                    $idioma = "{$idioma}.{$encoding}";
                } elseif (in_array("{$idioma}." . str_replace('-', '', strtolower($encoding)), $locales)) {
                    $idioma = "{$idioma}." . str_replace('-', '', strtolower($encoding));
                } // Fim if ... else
            } // Fim if

            // LC_ALL ainda não deve ser utilizado pois o LC_NUMERIC atrapalha o parâmetro value de
            // campos (inputs) decimais
            setlocale(LC_COLLATE, $idioma);
            setlocale(LC_CTYPE, $idioma);
            setlocale(LC_MONETARY, $idioma);
            setlocale(LC_TIME, $idioma);

            # Carregar arquivos do diretório _auto do framework
            $this->carregarAuto();

            # Carregar arquivos do diretório _auto do aplicativo
            $this->carregarAuto($this->diretorioAplicativo());
        } // Fim if
    }


// LOGS --------------------------------------------------------------------- //
    /**
     * Gravar um log no sistema de logs do servidor
     * @param  string $mensagem Mensagem a ser incluída no arquivo
     * @param  string $tipo     Tipo de mensagem que está sendo enviada
     * @param  string $arquivo  Nome do arquivo que está enviando o log. Se não
     * for informado, é utilizado o $_SERVER['SCRIPT_FILENAME']
     * @return boolean          Retorna TRUE em caso de sucesso ou FALSE em caso
     * de falha.
     */
    public function gravarLog($mensagem, $tipo = 'INFO', $arquivo = null) {
        $arquivo = empty($arquivo) ? $_SERVER['SCRIPT_FILENAME'] : $arquivo;
        return error_log("[Framework DLX - {$tipo}] {$mensagem} em {$arquivo}");
    } // Fim do método gravarLog


// Informações do Framework DLX --------------------------------------------- //
    /**
     * Identificar a versão atual do Framework DL
     * @return string Número da versão atual
     */
    public function versao() {
        $abrir = fopen('index.php', 'r');
        $ler = fread($abrir, 60);
        preg_match('~@version:\s(v[\d\.]+(\-r[\d]+)?)~', $ler, $versao);
        fclose($abrir);
        return $versao[1];
    } // Fim do método versao


// Ferramentas -------------------------------------------------------------- //]
    /**
     * Redirecionar o usuário para a versão HTTPS do site.
     * @return void
     */
    public function redirecionarParaHTTPS(): void
    {
        if(!$_SERVER['HTTPS'] || $_SERVER['HTTPS'] === 'off'){
            $cnf_raiz = preg_replace('~^/~', '', $this->config('aplicativo', 'raiz'));
            $host_https = "https://{$_SERVER['HTTP_HOST']}/{$cnf_raiz}{$this->getURL()}";
            header('HTTP/1.1 301 Moved Permanently');
            header("Location: {$host_https}");
            exit;
        } // Fim if
    } // Fim do método redirecionarParaHTTPS

    /**
     * Redirecionar para outra ação
     *
     * -------------------------
     * ** ** ** ATENÇÃO ** ** **
     * -------------------------
     * USE ESSE RECURSO COM MUITO CUIDADO. CADA REDIRECIONAMENTO NÃO 'DESCARREGA' MÓDULOS CARREGADOS ANTERIORMENTE!!! A
     * UTILIZAÇÃO DESSE RECURSO PODE CAUSAR UM CONSUMO MAIOR DE MEMÓRIA RAM DO SERVIDOR E AUMENTAR O TEMPO DE RESPOSTA
     *
     * @param string $url        URL com a rota para a ação a ser executada,
     * @param string $aplicativo Nome do aplicativo a ser carregado. Deve ser preenchido apenas quando o
     *                           redirecionamento encaminhar para um aplicativo diferente do anterior
     */
    public function redirecionar($url, $aplicativo = null) {
        if (!empty($aplicativo)) {
            $this->setAplicativo($aplicativo);
        } // Fim if

        # Alterar o status HTTP para redirecionado temporariamente
        # Status 302
        http_response_code(302);

        $this->setUrl($url);
        $this->executar();
    } // Fim do método redirecionar
} // Fim classe DLX

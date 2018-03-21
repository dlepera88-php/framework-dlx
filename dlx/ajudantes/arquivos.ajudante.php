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

class Arquivos {
    public static $extensoes = [
        /* Arquivos de imagens */
        'imagens'     => [
            'image/png'           => 'png',
            'image/jpeg'          => 'jpg',
            'image/pjpeg'         => 'jpg',
            'image/gif'           => 'gif',
            'image/bmp'           => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/fif'           => 'fif',
            'image/florian'       => 'flo',
            'image/x-icon'        => 'ico',
            'image/x-jps'         => 'jps',
        ],

        /* Arquivos de vídeo */
        'videos'      => [
            'application/x-troff-msvideo' => 'avi',
            'video/avi'                   => 'avi',
            'video/msvideo'               => 'avi',
            'video/x-msvideo'             => 'avi',
            'video/avs-video'             => 'avs',
            'video/fli'                   => 'fli',
            'video/x-fli'                 => 'fli',
            'video/x-motion-jpeg'         => 'mpeg',
            'video/quicktime'             => 'mov',
            'video/x-sgi-movie'           => 'movie',
            'video/mp4'                   => 'mp4',
            'video/ogg'                   => 'ogg',
            'video/webm'                  => 'webm',
        ],

        /* Arquivos de áudio */
        'audio'       => [
            'application/x-midi' => 'mid',
            'audio/midi'         => 'mid',
            'audio/x-mid'        => 'mid',
            'audio/x-midi'       => 'mid',
            'music/crescendo'    => 'mid',
            'x-music/x-midi'     => 'midi',
            'audio/mod'          => 'mod',
            'audio/x-mod'        => 'mod',
            'audio/mpeg'         => 'mp2',
            'audio/x-mpeg'       => 'mp2',
            'video/mpeg'         => 'mp3',
            'video/x-mpeg'       => 'mp3',
            'video/x-mpeq2a'     => 'mp2',
            'audio/mpeg3'        => 'mp3',
            'audio/x-mpeg-3'     => 'mp3',
            'audio/wav'          => 'wav',
            'audio/x-wav'        => 'wav',
        ],

        /* Arquivos Compactados */
        'compactacao' => [
            'application/x-bzip'           => 'bz',
            'application/x-bzip2'          => 'bz2',
            'application/x-compressed'     => 'gz',
            'application/x-gzip'           => 'gzip',
            'multipart/x-gzip'             => 'gzip',
            'application/x-tar'            => 'tar',
            'application/gnutar'           => 'tgz',
            'image/x-tiff'                 => 'tif',
            'application/x-zip-compressed' => 'zip',
            'application/zip'              => 'zip',
            'multipart/x-zip'              => 'zip',
        ],

        /* Pacote Office < 2007 */
        'ms-office'   => [
            'application/msword'                                                        => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template'   => 'dotx',
            'application/vnd.ms-word.document.macroEnabled.12'                          => 'docm',
            'application/vnd.ms-word.template.macroEnabled.12'                          => 'dotm',
            'application/vnd.ms-excel'                                                  => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template'      => 'xltx',
            'application/vnd.ms-excel.sheet.macroEnabled.12'                            => 'xlsm',
            'application/vnd.ms-excel.template.macroEnabled.12'                         => 'xltm',
            'application/vnd.ms-excel.addin.macroEnabled.12'                            => 'xlam',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12'                     => 'xlsb',
            'application/vnd.ms-powerpoint'                                             => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12'                       => 'ppam',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12'                => 'pptm',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12'                   => 'ppsm',
        ],

        /* Open Office */
        'open-office' => [
            'application/vnd.oasis.opendocument.text'                  => 'odt',
            'application/vnd.oasis.opendocument.text-template'         => 'ott',
            'application/vnd.oasis.opendocument.text-web'              => 'oth',
            'application/vnd.oasis.opendocument.text-master'           => 'odm',
            'application/vnd.oasis.opendocument.graphics'              => 'odg',
            'application/vnd.oasis.opendocument.graphics-template'     => 'otg',
            'application/vnd.oasis.opendocument.presentation'          => 'odp',
            'application/vnd.oasis.opendocument.presentation-template' => 'otp',
            'application/vnd.oasis.opendocument.spreadsheet'           => 'ods',
            'application/vnd.oasis.opendocument.spreadsheet-template'  => 'ots',
            'application/vnd.oasis.opendocument.chart'                 => 'odc',
            'application/vnd.oasis.opendocument.formula'               => 'odf',
            'application/vnd.oasis.opendocument.database'              => 'odb',
            'application/vnd.oasis.opendocument.image'                 => 'odi',
            'application/vnd.openofficeorg.extension'                  => 'oxt',
        ],

        /* PDF */
        'pdf'         => [
            'application/pdf' => 'pdf',
        ],

        /* Desenvolvimento */
        'dev'          => [
            'text/x-java-source' => 'java',
        ],

        /* Aplicações */
        'aplicacoes'   => [
            'application/x-navimap' => 'map',
        ],

        /* Web */
        'web'          => [
            'text/html'                => 'html',
            'text/asp'                 => 'asp',
            'application/php'          => 'php',
            'application/x-javascript' => 'js',
            'application/x-httpd-imap' => 'imap',
            'message/rfc822'           => 'mht',
        ],

        # Chaves de criptografia
        'criptografia' => [
            'application/x-pkcs12' => 'p12',
        ],

        # Mime type desconhecido
        'diversos'     => [
            'application/octet-stream' => ''
        ]
    ];


    // Arquivos ----------------------------------------------------------------------------------------------------- //
    /**
     * Criar um arquivo e inserir o conteúdo
     *
     * @param string $arquivo  Diretório e nome onde o arquivo será salvo
     * @param string $conteudo Conteúdo a ser inserido no arquivo
     *
     * @return bool
     */
    public static function criarTxt($arquivo, $conteudo) {
        # Criar e abrir o arquivo para escrita
        $a = fopen($arquivo, 'w+');

        # Escrever o conteúdo no arquivo
        $e = fwrite($a, $conteudo);

        # Fechar o arquivo
        fclose($a);

        return (bool)$e;
    } // Fim do método criarTxt


    /**
     * Obter informações sobre um arquivo específico
     *
     * @param string $caminho Caminho para o arquivo
     *
     * @return array
     */
    public static function obterInfos($caminho = '') {
        # Obter nome
        $nome = explode('.', basename($caminho));
        $nome = end($nome);

        if (extension_loaded('fileinfo')) {
            $fo = finfo_open();

            $mimetype = finfo_file($fo, $caminho, FILEINFO_MIME_TYPE);
            $mimeencode = finfo_file($fo, $caminho, FILEINFO_MIME_ENCODING);
        } else {
            # Obter o Mime-Type
            $mimetype = mime_content_type($caminho);

            # Obter o encode
            # ** Sem o finfo não foi possível encontrar o ENCODE do arquivo
            $mimeencode = '';
        } // Fim if

        # Obter a extensão
        $extensao = Vetores::buscarChaveRecursivo(static::$extensoes, $mimetype);

        # Obter o tamanho do arquivo
        $tamanho = sprintf('%u', filesize($caminho)); // Previnindo para arquivos com tamanho maior que 2GB

        return ['nome' => $nome, 'mime-type' => $mimetype, 'encoding' => $mimeencode, 'extensao' => $extensao, 'tamanho' => $tamanho];
    } // Fim do método obterInfos


    // Diretórios --------------------------------------------------------------------------------------------------- //
    /**
     * Remover diretórios, com a opção de remover os arquivos dentro dos
     * diretórios de maneira recursiva. Semelhante ao rm -r do linux
     *
     * @param string $diretorio        Caminho para o diretório a ser removido
     * @param bool   $remover_conteudo Define se o conteúdo do diretório será removido
     *
     * @return bool
     */
    public static function removerDir($diretorio, $remover_conteudo = false) {
        if (!file_exists($diretorio)) {
            return true;
        } // Fim if

        # Ler os arquivos dentro do diretório
        $ls = scandir($diretorio);

        if ($ls > 0 && !$remover_conteudo) {
            return false;
        } // Fim if

        if ($ls > 0) {
            # Filtrar diretórios ocultos
            $ls = preg_grep('#^[^\.]#', $ls);

            # Percorrer arquivo a arquivo para remover
            foreach ($ls as $linha) {
                $arquivo = "{$diretorio}/{$linha}";

                if (is_file($arquivo)) {
                    unlink($arquivo);
                } elseif (is_dir($arquivo)) {
                    static::removerDir($arquivo, true);
                } // Fim if ... else
            } // Fim foreach
        } // FIm if

        return rmdir($diretorio);
    } // Fim do método removerDir


    /**
     * Obter todos os arquivos de um diretório de acordo com o seu prefixo
     *
     * @param string $diretorio Diretório a ser escaneado
     * @param string $prefixo   Prefixo de filtro dos arquivos
     * @param string $param     Extensão do arquivo
     *
     * @return array    Vetor contendo apenas os arquivos que correspondem ao prefixo informado
     */
    public static function filtrarPrefixo($diretorio, $prefixo, $extensao = 'php') {
        // var_dump(glob("{$diretorio}*.{$prefixo}.{$extensao}"));
        return glob("{$diretorio}*.{$prefixo}.{$extensao}");
    } // Fim do método filtrarPrefixo


    /**
     * Carregar arquivos que estão dentro de um diretório específico
     *
     * @param string $diretorio Diretório dentro do módulo onde estão os arquivos a serem carregados
     * @param string $prefixo   Prefixo dos arquivos a serem considerado
     */
    public static function carregarArquivos($diretorio, $prefixo) {
        $arquivos = static::filtrarPrefixo($diretorio, $prefixo);
        
        foreach ($arquivos as $a) {
            include_once  $a;
        } // Fim foreach
    } // Fim do método carregarArquivos


    /**
     * Procurar determinado arquivo em diretórios antecessores
     *
     * @param string $arquivo  Caminho completo do arquivo que está sendo procurado
     * @param string $base     Trecho do caminho completo a ser utilizado como base durante a busca
     * @param int    $qtde_dir Quantidade máxima de diretórios antecessores a serem percorridos
     *
     * @return bool|mixed
     */
    public static function procurarDiretoriosAntecessores($arquivo, $base = null, $qtde_dir = 5) {
        $c = 0;

        $template = preg_replace("~^{$base}~", '', $arquivo);

        while (!file_exists($arquivo = "{$base}/{$template}") && $c < $qtde_dir) {
            $base = preg_replace('~\/[a-zA-Z_\-]+\/?$~', '', $base);
            $c++;
        } // Fim while

        return file_exists($arquivo) ? $arquivo : false;
    } // Fim do método procurarDiretoriosAntecessores
} // Fim da classe Arquivos

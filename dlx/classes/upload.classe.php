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
use DLX\Ajudantes\Strings;
use DLX\Ajudantes\Vetores;
use DLX\Ajudantes\Visao as AjdVisao;
use DLX\Excecao\DLX as DLXExcecao;

class Upload {
    # Propriedades do upload
    protected $diretorio;
    protected $extensoes = [];
    protected $campo;

    # Registro dos arquivos que foram salvos e que não foram salvos
    public $salvos = [];
    public $nao_salvos = [];

    # Configurações
    /**
     * Se for definido como true, interrompe o upload quando um arquivo for removido pelo filtro de extensao.
     * Se for definido como false, apenas remove o arquivo e continua o upload normalmente
     *
     * @var bool
     */
    public $conf_bloq_extensao = false;


    /**
     * @return mixed
     */
    public function getDiretorio() {
        return $this->diretorio;
    }


    /**
     * @param mixed $diretorio
     */
    public function setDiretorio($diretorio) {
        $this->diretorio = trim(filter_var($diretorio, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL), '/');
    }


    /**
     * @return array
     */
    public function getExtensoes() {
        return $this->extensoes;
    }


    /**
     * @param array $extensoes
     */
    public function setExtensoes($extensoes) {
        $this->extensoes = filter_var($extensoes, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY | FILTER_FLAG_EMPTY_STRING_NULL);
    }


    /**
     * @return mixed
     */
    public function getCampo() {
        return $this->campo;
    }


    /**
     * @param mixed $campo
     */
    public function setCampo($campo) {
        $this->campo = filter_var($campo, FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
    } // Fim do método _campo


    public function __construct($dir = '', $cmp = null) {
        $this->setDiretorio($dir);
        $this->setCampo($cmp);
    } // Fim do método de construção da classe


    private function obterArquivos() {
        if (!empty($this->campo) && !array_key_exists($this->campo, $_FILES)) {
            return;
        } // Fim if

        $arquivos = !empty($this->campo) && array_key_exists($this->campo, $_FILES) ? [$_FILES[$this->campo]] : $_FILES;
        
        foreach ($arquivos as $a) {
            if (count($a['name']) > 1) {
                foreach ($a['name'] as $chv => $nome) {
                    yield ['tmp' => $a['tmp_name'][$chv][0], 'nome' => $nome[0], 'erro' => $a['error'][$chv][0]];
                } // Fim foreach
            } else {
                yield is_array($a['name'])
                    ? ['tmp' => $a['tmp_name'][0], 'nome' => $a['name'][0], 'erro' => $a['error'][0]]
                    : ['tmp' => $a['tmp_name'], 'nome' => $a['name'], 'erro' => $a['error']];
            } // Fim if ... else
        } // Fim foreach
    } // Fim do método obterArquivos


    /**
     * Definir o nome do arquivo a ser salvo
     *
     * @param array  $o Nome original do arquivo
     * @param string $n Nome a ser utilizado para salvar o arquivo. Caso null, será usado o nome original do arquivo
     *
     * @return string Nome do arquivo sem a extensão
     */
    private function definirNome($o, $n = null) {
        return preg_replace(
            '~(\.[a-z0-9]{1,4}$|[^a-z^0-9^-])~',
            '',
            strtolower(
                Strings::removerAcentos(
                    str_replace(
                        ' ',
                        '-',
                        trim(!isset($n) ? $o : $n)
                    )
                )
            )
        );
    } // Fim do método definirNome


    /**
     * Salvar os arquivos carregados
     *
     * @param string $nm Nome do arquivo a ser salvo
     * @param bool   $se Se true, sobrescreve o arquivo atual, se existir
     *
     * @return int Quantidade de arquivos salvos
     * @throws DLXExcecao
     */
    public function salvar($nm = null, $se = false) {
        # Diretório onde os arquivos serão salvos
        $qt = 0;

        if (!file_exists($this->diretorio)) {
            throw new DLXExcecao(sprintf(AjdVisao::traduzirTexto('O diretório <b>%s</b> não foi localizado.'), $this->diretorio), 1404);
        } // Fim if
        
        foreach ($this->obterArquivos() as $a) {
            if ($a['erro'] !== 0 || !file_exists($a['tmp'])) {
                continue;
            } // Fim if

            # Contar a quantidade de arquivos enviados
            $qt++;

            # Obter as informações desse arquivo
            $i = Arquivos::obterInfos($a['tmp']);
            $ext = explode('.', $a['nome']);
            $ext = !empty($i['extensao']) ? $i['extensao'] : end($ext);

            /*
             * Verificar se a extensão do arquivo deve ser aceita ou se não há limitação das extensões
             */
            if (count($this->extensoes) > 0 && !in_array($ext, $this->extensoes)) {
                # Remover o arquivo temporário para não ter o risco de sobrecarregar o servidor
                unlink($a['tmp']);

                # Incluir o nome do arquivo no vetor $nao_salvos
                $this->nao_salvos['extensao'][] = $a['nome'];

                if ($this->conf_bloq_extensao) {
                    throw new DLXExcecao(sprintf(AjdVisao::traduzirTexto('A extensão <b>%s</b> não é aceita para upload desse tipo de arquivo.<br/>Por favor, verifique se o arquivo tem uma dessas extensões: <b>%s</b>'), $a['nome'], implode(', ', $this->extensoes)), 1403);
                } // Fim if

                # Passar para o próximo passo do laço
                continue;
            } // Fim if

            $n = $this->definirNome($a['nome'], $nm);
            $c = "{$this->diretorio}/{$n}.{$ext}";

            if (!$se) {
                $q = 0;

                while (file_exists($c)) {
                    $c = "{$this->diretorio}/{$n}-{$q}.{$ext}";
                    $q++;
                } // Fim while
            } // Fim if

            if (move_uploaded_file($a['tmp'], $c)) {
                $this->salvos[] = $c;
            } // Fim if
        } // Fim foreach

        return count($this->salvos);
    } // Fim do método salvar
} // Fim da classe Upload

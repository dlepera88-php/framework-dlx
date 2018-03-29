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

class Imagem {
    const IMAGEOPTIM_API_URL = 'https://im2.io/%s/%s/%s';

    # Propriedades dessa imagem
    protected $arquivo;
    protected $imagem;
    protected $largura;
    protected $altura;
    protected $tipo;

    # Parâmetros de edição
    protected $qlde_jpeg = 90;
    protected $qlde_png = 7;


    public function __construct($arquivo = null) {
        # Verificar se a bibilioteca GD foi inicializada
        if (!extension_loaded('GD')) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('Imagem: a biblioteca <b>php-gd</b> não foi encontrada!'), 1500);
        } // Fim if

        if (isset($arquivo)) {
            $this->setArquivo($arquivo);
        } // Fim if
    } // Fim do método mágico de construção da classe


    /**
     * @return mixed
     */
    public function getArquivo() {
        return $this->arquivo;
    }


    /**
     * @param string $arquivo
     *
     * @throws DLXExcecao
     */
    public function setArquivo($arquivo) {
        if (empty($arquivo) || !file_exists($arquivo)) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('O arquivo da imagem não foi encontrado!'), 1404);
        } // Fim if

        $this->arquivo = filter_var($arquivo, FILTER_SANITIZE_STRING);

        # Obter as dimensões do arquivo original
        list($this->largura, $this->altura, $this->tipo,) = getimagesize($this->arquivo, $infos);
    }


    /**
     * @return int
     */
    public function getQldeJpeg() {
        return $this->qlde_jpeg;
    }


    /**
     * @param int $qlde_jpeg
     */
    public function setQldeJpeg($qlde_jpeg) {
        $this->qlde_jpeg = filter_var($qlde_jpeg, FILTER_VALIDATE_INT, ['options' => [
            'min_range' => 0, 'max_range' => 100, 'default' => 90
        ]]);
    }


    /**
     * @return int
     */
    public function getQldePng() {
        return $this->qlde_png;
    }


    /**
     * @param int $qlde_png
     */
    public function setQldePng($qlde_png) {
        $this->qlde_png = filter_var($qlde_png, FILTER_VALIDATE_INT, ['options' => [
            'min_range' => 0, 'max_range' => 9, 'default' => 7
        ]]);
    }


    /**
     * @return int
     */
    public function getLargura() {
        return $this->largura;
    }


    /**
     * @return int
     */
    public function getAltura() {
        return $this->altura;
    }


    /**
     * Tranparência da imagem
     */
    public function transparencia() {
        # Configurar transparência
        imagealphablending($this->imagem, false);
        imagesavealpha($this->imagem, true);
    } // Fim do método transparencia


    /**
     * Preparar a imagem para ser exibida ou salva
     *
     * @return bool|resource
     */
    public function preparar() {
        # Recriar a imagem de acordo com o tipo
        switch ($this->tipo) {
            case 1:
                $img = imagecreatefromgif($this->arquivo);
                break;

            case 2:
                $img = imagecreatefromjpeg($this->arquivo);
                break;

            case 3:
                $img = imagecreatefrompng($this->arquivo);
                break;

            case 6:
                $img = imagecreatefromwbmp($this->arquivo);
                break;

            default:
                return false;
        } // Fim switch

        return $img;
    } // Fim do método preparar


    /**
     * Redimensionar a imagem
     *
     * PS.: Se um dos 2 parâmetros não forem informados a imagem usará tamanho relativo
     *
     * @param int|null $largura Nova largura da imagem
     * @param int|null $altura  Nova altura da imagem
     *
     * @return resource
     * @throws DLXExcecao
     */
    public function redimensionar($largura = null, $altura = null) {
        if (empty($largura) && empty($altura)) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('Para redimensionar uma imagem é necessário informar a largura e / ou a altura desejada.'), 1403);
        } // Fim if

        # Definir os valores finais para largura e altura
        $largura = empty($largura) ? ($altura * $this->largura) / $this->altura : $largura;
        $altura = empty($altura) ? ($this->altura * $largura) / $this->largura : $altura;

        # Criar a nova imagem com as dimensões finais
        $this->imagem = imagecreatetruecolor($largura, $altura);

        $this->transparencia();

        # Caso a imagem seja GIF ou PNG prepara para utilizar
        # a transparência
        if ($this->tipo == 1 || $this->tipo == 3) {
            imagecolortransparent($this->imagem);
        } // Fim if

        # Copiar a imagem original e colocá-la
        # redimensionada em $imagem
        imagecopyresampled($this->imagem, $this->preparar(), 0, 0, 0, 0, $largura, $altura, $this->largura, $this->altura);

        return $this->imagem;
    } // Fim do método redimensionar


    /**
     * Recortar a imagem
     *
     * @param int|null $largura Nova largura da imagem
     * @param int|null $altura  Nova altura da imagem
     * @param int      $coord_x Coordenada do eixo X para início do recorte
     * @param int      $coord_y Coordenada do eixo Y para fim do recorte
     *
     * @return resource
     * @throws DLXExcecao
     */
    public function recortar($largura = null, $altura = null, $coord_x = 0, $coord_y = 0) {
        if (empty($largura) && empty($altura)) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('Para recortar uma imagem é necessário informar a largura e / ou a altura desejada.'), 1403);
        } // Fim if

        # Definir os valores finais para largura e altura
        $largura = empty($largura) ? ($altura * $this->largura) / $this->altura : $largura;
        $altura = empty($altura) ? ($this->altura * $largura) / $this->largura : $altura;

        # Criar uma imagem em branco que servirá
        # como base para a nova imagem redimensionada
        $this->imagem = imagecreatetruecolor($largura, $altura);

        $this->transparencia();

        # Copiar a imagem original e colocá-la
        # redimensionada na $nova_imagem
        imagecopy($this->imagem, $this->preparar(), 0, 0, $coord_x, $coord_y, $largura, $altura);

        return $this->imagem;
    } // Fim do método recortar


    /**
     * Rotacionar a imagem
     *
     * @param int $graus Quantidade de graus a rotacionar a imagem
     *
     * @return resource
     * @throws DLXExcecao
     */
    public function rotacionar($graus) {
        if (empty($graus) || !is_numeric($graus)) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('Por favor, informe a rotação em graus desejada para essa imagem.'), 1403);
        } // Fim if

        # Rotacionar a nova imagem com transparência
        $this->imagem = imagerotate($this->preparar(), $graus, imagecolorallocatealpha($this->preparar(), 0, 0, 0, 127));

        $this->transparencia();

        return $this->imagem;
    } // Fim do método rotacionar


    /**
     * Salvar a imagem em um arquivo
     *
     * @param string $arquivo Nome do arquivo a ser salvo
     *
     * @return bool
     * @throws DLXExcecao
     */
    public function salvar($arquivo) {
        if (empty($arquivo)) {
            throw new DLXExcecao(AjdVisao::traduzirTexto('Nome do arquivo não informado!'), 1404);
        } // Fim if

        switch ($this->tipo) {
            /* IMAGEM GIF */
            case 1:
                imagegif($this->imagem, $arquivo);
                break;

            /* IMAGEM JPG */
            case 2:
                imagejpeg($this->imagem, $arquivo, $this->qlde_jpeg);
                break;

            /* IMAGEM PNG */
            case 3:
                imagepng($this->imagem, $arquivo, $this->qlde_png);
                break;

            /* IMAGEM BMP */
            case 6:
                imagewbmp($this->imagem, $arquivo);
                break;

            default:
                echo 'Tipo de imagem não suportado pelo sistema!';
                break;
        } // Fim switch

        # Destruir essa imagem e liberar o espaço em memória
        return imagedestroy($this->imagem);
    } // Fim do método salvar


    /**
     * Salvar a imagem em um arquivo
     */
    public function mostrar() {
        switch ($this->tipo) {
            /* IMAGEM GIF */
            case 1:
                # Caso o nome do arquivo não seja informado
                # a imagem será exibida diretamente. Para isso
                # será alterado o content-type da página
                header('Content-type: image/gif');

                imagegif($this->imagem);
                break;

            /* IMAGEM JPG */
            case 2:
                # Caso o nome do arquivo não seja informado
                # a imagem será exibida diretamente. Para isso
                # será alterado o content-type da página
                header('Content-type: image/jpeg');

                imagejpeg($this->imagem, null, $this->qlde_jpeg);
                break;

            /* IMAGEM PNG */
            case 3:
                # Caso o nome do arquivo não seja informado
                # a imagem será exibida diretamente. Para isso
                # será alterado o content-type da página
                header('Content-type: image/png');

                imagepng($this->imagem, null, $this->qlde_png);
                break;

            /* IMAGEM BMP */
            case 6:
                # Caso o nome do arquivo não seja informado
                # a imagem será exibida diretamente. Para isso
                # será alterado o content-type da página
                header('Content-type: image/bmp');

                imagewbmp($this->imagem);
                break;

            default:
                echo 'Tipo de imagem não suportado pelo sistema!';
                break;
        } // Fim switch

        # Destruir essa imagem e liberar o espaço em memória
        return imagedestroy($this->imagem);
    } // Fim do método mostrar


    /**
     * Otimizar as imagens para WEB
     *
     * @param  string $chave_api Chave da API ImageOptim
     * @param string  $opcoes    Opções a serem passados para a API. Documentação:
     *                           https://im2.io/api/post?username=mddxjrbbjb
     */
    public function otimizarParaWeb($chave_api = 'mddxjrbbjb', $opcoes = 'full') {
        // Caminho completo da imagem URL
        $imagem_url = AjdVisao::hostCompleto() . $this->arquivo;

        /*
         * A otimização não funciona em localhost
         */
        if (!preg_match('~//localhost~', $imagem_url)) {
            // Configurações para usar o método POST
            $post_contexto = stream_context_create([
                'http' => ['method' => 'POST'],
            ]);

            // Get image data from the API
            $imagem_optimizada = @file_get_contents(
                sprintf(
                    self::IMAGEOPTIM_API_URL,
                    $chave_api,
                    $opcoes,
                    $imagem_url
                ),
                false, $post_contexto
            );

            /*
             * Atualizar o arquivo atual APENAS caso a imagem tenha sido optimizada corretamente
             */
            if ($imagem_optimizada !== false) {
                // Salvar a imagem otimizada
                file_put_contents($this->arquivo, $imagem_optimizada);
            } // Fim if
        } // Fim if
    } // Fim do método otimizarParaWeb

// Outros -------------------------------------------------------------------------------------- //
    /**
     * Verificar se a imagem está na horizontal.
     *
     * @return boolean
     */
    public function isHorizontal() {
        return $this->altura < $this->largura;
    } // Fim do método isHorizontal

    /**
     * Verificar se a imagem está na vertical.
     *
     * @return boolean
     */
    public function isVertical() {
        return $this->altura > $this->largura;
    } // Fim do método isVertical

    /**
     * Verificar se a imagem é quadrada, ou seja, tem altura e largura iguais.
     *
     * @return boolean
     */
    public function isQuadrada() {
        return $this->altura === $this->largura;
    } // Fim do método isQuadrada

    /**
     * Obtém a orientação da imagem.
     *
     * @return string Retorna uma letra representativa da orientação da imagem:
     * v: vertical
     * h: horizontal
     * q: quadrada
     */
    public function obterOrientacao() {
        return $this->isVertical() ? 'v' : ($this->isHorizontal() ? 'h' : 'q');
    } // Fim do método obterOrientacao
} // Fim da classe Imagem

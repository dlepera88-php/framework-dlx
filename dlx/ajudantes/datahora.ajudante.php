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


class DataHora {
    /**
     * Formatar data e hora
     *
     * @param string $data_hora string contendo uma representação de data ou hora
     * @param string $formato   string contendo o formtado da data e/ou hora desejado. O farmato deve ser aceito pela
     *                          função date();
     *
     * @return bool|mixed|string
     */
    public static function formatar($data_hora, $formato = null) {
        # Se $formato estiver em branco retornar a data sem nenhum alteração
        if (empty($formato)) {
            return $data_hora;
        } // Fim if

        /*
         * Essas strings não serão aceitas, por se tratarem de datas e / ou horas inválidas
         */
        $nao_aceito = ['0000-00-00', '0000-00-00 00:00:00'];

        if (!empty($data_hora) && !in_array($data_hora, $nao_aceito)) {
            /*
             * A função strtotime() não aceita a string da data no formato brasileiro com a '/' barra separando dia, mês e ano.
             * Portanto, caso a data seja informada dessa forma substituir a '/' barra pelo '-' hifém
             */
            if (strpos($data_hora, '/') > -1) {
                $data_hora = str_replace('/', '-', $data_hora);
            } // Fim if

            return date_format(date_create($data_hora), $formato);
        } // Fim if

        return null;
    } // Fim do método formatar


    /**
     * Obter o mês por extenso
     *
     * @param int|string $mes   Representação numérica do mês a ser identificado. Pode ser um número inteiro ou uma
     *                          string
     * @param bool       $abrev Define se o extenso do mês deve ser abreviado (true) ou completo (false)
     *
     * @return string
     */
    public static function mesPorExtenso($mes, $abrev = false) {
        return strftime($abrev ? '%b' : '%B', mktime(null, null, null, (int)$mes, 1, date('Y')));
    } // Fim do método mesPorExtenso


    /**
     * Subtrair uma data de outra e retornar o intervalo entre as 2
     *
     * @param string $data1   Data de referência para sutração (minuendo)
     * @param string $data2   Data a subtrair da data 1 (subtraendo)
     * @param string $formato Formato para exibição da diferença entre as datas
     *
     * @return string
     */
    public static function subtrairData($data1, $data2, $formato = '%y') {
        $data1 = date_create(strtotime($data1));
        $data2 = date_create(strtotime($data2));
        $intervalo = date_diff($data1, $data2);

        return $intervalo->format($formato);
    } // Fim do método subtrairData
}

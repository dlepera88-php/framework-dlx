/**
 * Remover arquivos CSS duplicados.
 */
function removerCSSDuplicado() {
    var ss = [], $link_ss, arquivo_css;
    $(window).find('link[rel="stylesheet"]').each(function () {
        $link_ss = $(this);
        arquivo_css = $link_ss.attr('href');

        // Se o arquivo já existir no array ss, significa que ele está duplicado, então
        // devo remover da página agora.
        if (ss.indexOf(arquivo_css) < 0) {
            $link_ss.remove();

        // Caso contrário, adiciono ele no array para verificar os próximos.
        } else {
            ss.push(arquivo_css);
        } // Fim if ... else
    });
} // Fim function removerCSSDuplicado
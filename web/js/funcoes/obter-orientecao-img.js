/**
 * Identificar a orientação da imagem.
 * @param {DOM} img Objeto DOM da imagem a ser verificada.
 * @return {String} Retorna uma letra representativa da orientação da imagem:
 * v: vertical
 * h: horizontal
 * q: quadrada
 */
function obterOrientacaoIMG(img) {
    var w = img.naturalWidth,
        h = img.naturalHeight;
        
    return h < w ? 'h' : (h > w ? 'v' : 'q');
} // Fim da função obterOrientacaoIMG

$('[data-orientacao-img="true"]').each(function () {
    var $this = $(this), tag = this.tagName, $figure, img;

    if (this.tagName === 'IMG' || $this.find('img').length > 0) {
        $figure = this.tagName === 'IMG' ? $this.parent() : $this;
        img = this.tagName === 'IMG' ? this : $this.find('img').get(0);
        $figure.addClass('-' + obterOrientacaoIMG(img));
    } // Fim if
});
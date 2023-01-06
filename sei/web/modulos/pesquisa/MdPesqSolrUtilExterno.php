<?

class MdPesqSolrUtilExterno
{
    public static function formatarCaracteresEspeciais($q)
    {
        $arrSolrExc = array(chr(92), '/', '+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':');

        foreach ($arrSolrExc as $solrExc){
            $q = str_replace($solrExc, chr(92) . $solrExc, $q);
        }
        
		return $q;
    }

    public static function formatarOperadores($q, $tag = null)
    {
        $q = InfraString::excluirAcentos(InfraString::transformarCaixaBaixa($q));

        //remove aspas repetidas
        while (strpos($q, '""') !== false) {
            $q = str_replace('""', '"', $q);
        }

        $arrPalavrasQ = InfraString::agruparItens($q);

        for ($i = 0; $i < count($arrPalavrasQ); $i++){

            //numero de aspas impar, remover do token que ficar com apenas uma
            $arrPalavrasQ[$i] = MdPesqSolrUtilExterno::formatarCaracteresEspeciais(str_replace('"', '', $arrPalavrasQ[$i]));

            if (strpos($arrPalavrasQ[$i], ' ') !== false){
                if ($tag == null){
                    $arrPalavrasQ[$i] = '"' . $arrPalavrasQ[$i] . '"';
                } else {
                    $arrPalavrasQ[$i] = $tag . ':"' . $arrPalavrasQ[$i] . '"';
                }
            } else if ($arrPalavrasQ[$i] == 'e') {
                $arrPalavrasQ[$i] = "AND";
            } else if ($arrPalavrasQ[$i] == 'ou') {
                $arrPalavrasQ[$i] = "OR";
            } else if ($arrPalavrasQ[$i] == 'nao') {
                $arrPalavrasQ[$i] = "AND NOT";
            } else {
                if ($tag != null) {
                    $arrPalavrasQ[$i] = $tag . ':' . $arrPalavrasQ[$i];
                }
            }
        }

        $ret = '';
        for ($i = 0; $i < count($arrPalavrasQ); $i++) {
            //Adiciona operador and como padrao se nao informado
            if ($i > 0){
                if (!in_array($arrPalavrasQ[$i - 1], array('AND', 'OR', 'AND NOT', '(')) && !in_array($arrPalavrasQ[$i], array('AND', 'OR', 'AND NOT', ')'))) {
                    $ret .= " AND";
                }
            }
            $ret .= ' ' . $arrPalavrasQ[$i];
        }

        $ret = str_replace(" AND AND NOT ", " AND NOT ", $ret);

        if (substr($ret, 0, strlen(" AND NOT ")) == " AND NOT "){
            $ret = substr($ret, strlen(" AND NOT "));
            $ret = 'NOT ' . $ret;
        }

        if (substr($ret, 0, strlen(" AND ")) == " AND "){
            $ret = substr($ret, strlen(" AND "));
        }

        if (substr($ret, 0, strlen(" OR ")) == " OR "){
            $ret = substr($ret, strlen(" OR "));
        }

        if (substr($ret, strlen(" AND") * -1) == " AND"){
            $ret = substr($ret, 0, strlen(" AND") * -1);
        }

        if (substr($ret, strlen(" OR") * -1) == " OR"){
            $ret = substr($ret, 0, strlen(" OR") * -1);
        }

        if (substr($ret, strlen(" AND NOT") * -1) == " AND NOT"){
            $ret = substr($ret, 0, strlen(" AND NOT") * -1);
        }

        if($ret != ""){
            $ret = '(' . trim($ret) . ')';
        }

        return $ret;
    }

    public static function criarBarraEstatisticas($total, $inicio, $fim)
    {
        return "<div class=\"pesquisaBarraD\">" . self::obterTextoBarraEstatisticas($total, $inicio, $fim) . "</div>";
    }

    public static function obterTextoBarraEstatisticas($total, $inicio, $fim)
    {
        $ret = '';
        if ($total > 0 && $total != ""){
            if ($total < $fim) {
                $ret .= $total . ' resultado' . ($total > 1 ? 's' : '');
            } else {
                $ret .= "Exibindo " . ($inicio + 1) . " - " . $fim . " de " . $total;
            }
        }
        return $ret;
    }

    //Cria a navegacao completa
    public static function criarBarraNavegacao($totalRes, $inicio, $numResPorPag, $objPagina, $objSessao, $md5Captcha = null, $strControlador = 'md_pesq_processo_pesquisar.php', $position = 'bottom')
    {
        if ($totalRes == 0)
            return;

        $styleTop = ($position == 'top') ? 'border-top: none; padding: 2em 0 0.5em 0; border-bottom: 0.1em solid #909090; margin-top: 20px' : '';

        $nav = '<div class="pesquisaPaginas d-flex flex-column flex-md-row text-align-center" style="'.$styleTop.'">';

        $paginaAtual = $inicio / $numResPorPag + 1;

        $urlSemInicio = $strControlador . '?acao_externa=' . $_GET['acao_externa'] . "&acao_origem_externa=protocolo_pesquisar_paginado";

        $hash = (!is_null($md5Captcha)) ? "&hash=" . $md5Captcha : "";

        $nav .= '<div class="col-12 col-md-4 mx-0 px-0 text-center text-md-right">';
        if ($inicio != null) {
            $nav .= "<span class=\"pequeno\"><a href=\"javascript:pagina.ir('" . $objPagina->formatarXHTML($objSessao->assinarLink($urlSemInicio . "&inicio=" . ($inicio - $numResPorPag) . $hash)) . "')\">Anterior</a></span>\n";
        }
        $nav .= '</div>'."\n";

        if ($totalRes > $numResPorPag) {

            $nav .= '<div class="col-12 col-md-4 mx-0 px-0 text-center">'."\n";

            $numPagParaClicar = 12;

            if (ceil($totalRes / $numResPorPag) > $numPagParaClicar) {
                $iniNav = ($paginaAtual - floor(($numPagParaClicar - 1) / 2)) - 1;
                $fimNav = ($paginaAtual + ceil(($numPagParaClicar - 1) / 2));

                if ($iniNav < 0) {
                    $iniNav = 0;
                    $fimNav = $numPagParaClicar;
                }

                if ($fimNav > ceil($totalRes / $numResPorPag)) {
                    $fimNav = ceil($totalRes / $numResPorPag);
                    $iniNav = $fimNav - $numPagParaClicar;
                }
            } else {
                $iniNav = 0;
                $fimNav = ceil($totalRes / $numResPorPag);
            }

            for ($i = $iniNav; $i < $fimNav; $i++) {
                if ($inicio == 0 and $i == 0) {
                    $nav .= " <div class=\"pesquisaPaginaSelecionada\">" . ($i + 1) . "</div> ";
                } elseif (($i + 1) == ($inicio / $numResPorPag + 1)) {
                    $nav .= " <div class=\"pesquisaPaginaSelecionada\">" . ($i + 1) . "</div> ";
                } else {
                    $nav .= " <a href=\"javascript:pagina.ir('" . str_replace('+', '%2B', $objPagina->formatarXHTML($objSessao->assinarLink($urlSemInicio . "&inicio=" . ($i * $numResPorPag)))) . $hash . "')\">" . ($i + 1) . "</a>\n";
                }
            }
            $nav .= '</div>'."\n";
        }

        $nav .= '<div class="col-12 col-md-4 mx-0 px-0 text-center text-md-left">';
        if (($inicio / $numResPorPag) + 1 != ceil($totalRes / $numResPorPag)) {
            $nav .= "<span class=\"pequeno\"><a href=\"javascript:pagina.ir('" . $objPagina->formatarXHTML($objSessao->assinarLink($urlSemInicio . "&inicio=" . ($inicio + $numResPorPag))) . $hash . "')\">Próxima</a></span>\n";
        }
        $nav .= '</div>'."\n";

        $nav .= "</div>";

        return $nav;
    }

    public static function prepararUrl($url)
    {
        $pos = strpos($url, 'controlador.php');
        if ($pos !== false) {
            $url = ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL') . substr($url, $pos);
        } else {
            $pos = strpos($url, '/publicacoes/controlador_publicacoes.php');
            if ($pos !== false) {
                $url = ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL') . substr($url, $pos);
            }
        }

        if (ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'https')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }

    public static function obterTag($reg, $tag, $tipo)
    {
        $ret = $reg->xpath($tipo . '[@name=\'' . $tag . '\']');
        if (isset($ret[0])) {
            $ret = utf8_decode($ret[0]);
        } else {
            $ret = null;
        }
        return $ret;
    }
}

?>
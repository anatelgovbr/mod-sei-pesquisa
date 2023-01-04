<?php
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-11-12
 * Versão do Gerador de Código: 1.0
 *
 * Classe de Busca na solução de indexação solr.
 *
 */

require_once("MdPesqSolrUtilExterno.php");
require_once("MdPesqCriptografia.php");

class MdPesqBuscaProtocoloExterno{

    public static function executar($q, $strDescricaoPesquisa, $strObservacaoPesquisa, $inicio, $numMaxResultados = 50, $strParticipanteSolr, $md5Captcha = null)
    {
        //carrega configuracoes pesquisa
        $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
        $objParametroPesquisaDTO->retStrNome();
        $objParametroPesquisaDTO->retStrValor();
        $arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO((new MdPesqParametroPesquisaRN())->listar($objParametroPesquisaDTO), 'Valor', 'Nome');

        $bolPesquisaDocumentoProcessoRestrito   = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_PESQUISA_DOCUMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
        $bolListaDocumentoProcessoRestrito      = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
        $bolListaDocumentoProcessoPublico       = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_PUBLICO] == 'S' ? true : false;
        $bolLinkMetadadosProcessoRestrito       = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_METADADOS_PROCESSO_RESTRITO] == 'S' ? true : false;
        $bolAutocompletarInterressado           = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_AUTO_COMPLETAR_INTERESSADO] == 'S' ? true : false;
        $txtDescricaoProcedimentoAcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO];

        $dtaParamCortePesquisa = (new MdPesqParametroPesquisaRN())->existeDataCortePesquisa();

        $parametros = new stdClass();
        $partialfields = '';

        if (!$bolAutocompletarInterressado) {
            $partialfields = $strParticipanteSolr;
        }

        $grupo = [];
        $checkbox = ['P', 'G', 'R'];

        if (!InfraString::isBolVazia($_REQUEST["partialfields"])) {
            $partialfields = $partialfields . $_REQUEST["partialfields"];
            $checkbox = [];
            if (preg_match("/sta_prot:([A-Z;]+)/i", $partialfields, $checkbox) > 0) {
                $checkbox = explode(";", $checkbox[1]);
            }
        }

        // PESQUISAR EM PROCESSOS
        if (in_array("P", $checkbox)) {
            array_push($grupo, "(sta_prot:P)");
        }

        if($bolListaDocumentoProcessoPublico){
            // PESQUISAR EM DOCUMENTOS EXTERNOS (RECEBIDOS)
            if (in_array("R", $checkbox)) {
                array_push($grupo, "(sta_prot:R)");
            }

            // PESQUISAR EM DOCUMENTOS INTERNOS (GERADOS)
            if (in_array("G", $checkbox)) {
                array_push($grupo, "(sta_prot:G)");
            }
        }

        if (count($grupo) > 0) {
            //ALTERACAO PARA CONSULTA EXTERNA SEM LOGIN
            $staProtocolo = '(' . implode(" OR ", $grupo) . ')';
            if (preg_match("/sta_prot:([A-Z;]+)/i", $partialfields)) {
                $partialfields = preg_replace("/sta_prot:[A-Z;]+/i", $staProtocolo, $partialfields);
            } else {
                $partialfields .= $staProtocolo;
            }
        }

        /**
         * ELABORA A URL
         */
        $parametros->q = MdPesqSolrUtilExterno::formatarOperadores($q);
        if ($strDescricaoPesquisa != '') {
            if ($parametros->q != '') {
                $parametros->q .= ' AND ';
            }
            $parametros->q .= '(' . MdPesqSolrUtilExterno::formatarOperadores($strDescricaoPesquisa, 'desc') . ')';
        }

        if ($strObservacaoPesquisa != '') {
            if ($parametros->q != '') {
                $parametros->q .= ' AND ';
            }
            $parametros->q .= '(' . MdPesqSolrUtilExterno::formatarOperadores($strObservacaoPesquisa, 'desc' . SessaoSEI::getInstance()->getNumIdUnidadeAtual()) . ')';
        }

        $parametros->start = $inicio;

        if ($parametros->q != '' && $partialfields != '') {
            $parametros->q .= ' AND ' . $partialfields;
        } else if ($partialfields != '') {
            $parametros->q = $partialfields;
        }

        $parametros->sort = 'dta_ger desc';

        // PEGO O TEXTO LIVRE PESQUISADO SE EXISTIR
        $pesquisaLivre = null;
        preg_match("/\((.*?)\)/", $parametros->q, $pesqLivre);
        if (count($pesqLivre) > 0 && strpos($pesqLivre[1], '(') !== 0) {
            $pesquisaLivre = $pesqLivre[1];
        }

        $pesquisaProtocolo = null;
        preg_match("/prot_pesq:\*(.+)\*/", $parametros->q, $pesqProt);
        if (count($pesqProt) > 0 && strpos($pesqProt[1], '(') !== 0) {
            $pesquisaProtocolo = $pesqProt[1];
        }

        $parametros->q = utf8_encode($parametros->q);

        //MONTA URL DA BUSCA
        $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr', 'Servidor') . '/' . ConfiguracaoSEI::getInstance()->getValor('Solr', 'CoreProtocolos') . '/select?' . http_build_query($parametros) . '&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&hl.maxClauseCount=2000&rows='.$numMaxResultados.'&fl=id,id_prot,id_proc,id_doc,id_tipo_proc,id_serie,id_anexo,id_uni_ger,prot_doc,prot_proc,numero,id_usu_ger,dta_ger,dta_inc,id_assin,sta_prot,desc';
        $resultados = file_get_contents($urlBusca, true);

        $termo = !empty($q) ? $q : $pesquisaProtocolo;

        $semResultados = "<consultavazia>";
        $semResultados .= "<div class=\"sem-resultado\">Sua pesquisa pelo termo <b>" .$termo. "</b> não encontrou nenhum protocolo correspondente. <br/><br/>Sugestões:";
        $semResultados .= "<ul>";
        $semResultados .= "<li>Certifique-se de que todas as palavras estejam escritas corretamente.</li>";
        $semResultados .= "<li>Tente palavras-chave diferentes.</li>";
        $semResultados .= "<li>Tente palavras-chave mais genéricas.</li>";
        $semResultados .= "</ul>";
        $semResultados .= "</div>";
        $semResultados .= "</consultavazia>";

        if ($resultados == '') {
            return $semResultados;
        }

        $xml = simplexml_load_string($resultados);

        $arrRet = $xml->xpath('/response/result/@numFound');
        $itens = array_shift($arrRet);

        if($itens == 0){
            return $semResultados;
        }

        $registros = $xml->xpath('/response/result/doc');

        if(count($registros) == 0){
            return $semResultados;
        }

//        $html = "<table border=\"0\" class=\"pesquisaResultado\"><tbody>\n";
        $html = '';
        $removidos = 0;

        for ($i = 0; $i < count($registros); $i++) {

            $isPublico      = true;
            $idProtocolo    = InfraSolrUtil::obterTag($registros[$i], 'id_prot', 'long');
            $idProcesso     = InfraSolrUtil::obterTag($registros[$i], 'id_proc', 'long');
            $staProtocolo   = InfraSolrUtil::obterTag($registros[$i], 'sta_prot', 'str');
            $strAssinado    = InfraSolrUtil::obterTag($registros[$i], 'id_assin', 'str');
            $dtaCorteDoc    = explode('T', InfraSolrUtil::obterTag($registros[$i], 'dta_inc', 'date'))[0];

            // REMOVE DOCUMENTOS GERADOS NAO ASSINADOS EXCETO OS FORMULARIOS AUTOMATICOS
            if($staProtocolo == ProtocoloRN::$TP_DOCUMENTO_GERADO && is_null($strAssinado)){
                $objDocumentoDTO = new DocumentoDTO();
                $objDocumentoDTO->setDblIdDocumento($idProtocolo);
                $objDocumentoDTO->retStrStaDocumento();
                $objDocumentoDTO = (new DocumentoRN())->consultarRN0005($objDocumentoDTO);
                if(!empty($objDocumentoDTO) && $objDocumentoDTO->getStrStaDocumento() != DocumentoRN::$TD_FORMULARIO_AUTOMATICO){
                    $removidos++;
                    continue;
                }
            }

            // VERIFICANDO O ACESSO LOCAL DO PROTOCOLO
            $objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->retStrStaNivelAcessoGlobal();
            $objProtocoloDTO->retStrStaNivelAcessoLocal();
            $objProtocoloDTO->retStrStaProtocolo();
            $objProtocoloDTO->retDtaInclusao();
            $objProtocoloDTO->retDtaGeracao();
            $objProtocoloDTO->retDblIdProtocolo();
            $objProtocoloDTO->retStrProtocoloFormatado();
            $objProtocoloDTO->retNumIdHipoteseLegal();
            $objProtocoloDTO->setDblIdProtocolo($idProtocolo);
            $objProtocoloDTO = (new ProtocoloRN())->consultarRN0186($objProtocoloDTO);

            if(!empty($objProtocoloDTO)){

                $isPublico = ($objProtocoloDTO->getStrStaNivelAcessoLocal() == ProtocoloRN::$NA_PUBLICO);

                if($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO){

                    $isPublico = ($objProtocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_PUBLICO);

                }else{

                    if($isPublico){
                        // VERIFICANDO O ACESSO LOCAL DO PROCESSO PAI DO DOCUMENTO
                        $objProcessoDTO = new ProtocoloDTO();
                        $objProcessoDTO->retStrStaNivelAcessoGlobal();
                        $objProcessoDTO->retStrStaNivelAcessoLocal();
                        $objProcessoDTO->setStrStaProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);
                        $objProcessoDTO->setDblIdProtocolo($idProcesso);
                        $objProcessoDTO = (new ProtocoloRN())->consultarRN0186($objProcessoDTO);

                        if(!empty($objProcessoDTO)){
                            $isPublico = $objProcessoDTO->getStrStaNivelAcessoLocal() == 0;
                            if(!$bolPesquisaDocumentoProcessoRestrito && $objProcessoDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO){
                                $isPublico = false;
                            }
                        }
                    }

                    $objDocumentoDTO = new DocumentoDTO();
                    $objDocumentoDTO->setDblIdDocumento($idProtocolo);
                    $objDocumentoDTO->retDblIdDocumento();
                    $objDocumentoDTO->retDblIdProcedimento();
                    $objDocumentoDTO->retNumIdSerie();
                    $objDocumentoDTO->retStrNomeSerie();
                    $objDocumentoDTO->retStrNumero();
                    $objDocumentoDTO->retStrStaDocumento();
                    $objDocumentoDTO = (new DocumentoRN())->consultarRN0005($objDocumentoDTO);

                    if( $objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO && !empty($objDocumentoDTO) && in_array($objDocumentoDTO->getStrStaDocumento(), [DocumentoRN::$TD_EDITOR_INTERNO, DocumentoRN::$TD_FORMULARIO_GERADO]) ){
                        $objAssinaturaDTO = new AssinaturaDTO();
                        $objAssinaturaDTO->retDthAberturaAtividade();
                        $objAssinaturaDTO->setDblIdDocumento($objProtocoloDTO->getDblIdProtocolo());
                        $objAssinaturaDTO->setOrdNumIdAssinatura(InfraDTO::$TIPO_ORDENACAO_ASC);
                        $objAssinaturaDTO->setNumMaxRegistrosRetorno(1);
                        $arrObjAssinaturaDTO = (new AssinaturaRN())->listarRN1323($objAssinaturaDTO);

                        if (!empty($arrObjAssinaturaDTO) && $arrObjAssinaturaDTO[0] != null && $arrObjAssinaturaDTO[0]->isSetDthAberturaAtividade()){
                            $dtaCorteDoc = implode('-', array_reverse(explode('/', substr($arrObjAssinaturaDTO[0]->getDthAberturaAtividade(),0,10))));
                        }
                    }

                    if(
                        ( PesquisaIntegracao::verificaSeModPeticionamentoVersaoMinima() && !(new MdPetIntCertidaoRN())->verificaDocumentoEAnexoIntimacaoNaoCumprida([$objProtocoloDTO->getDblIdProtocolo(),false,false,true]) ) ||
                        ( $dtaParamCortePesquisa > $dtaCorteDoc )
                    ){
                        $isPublico = false;
                    }
                }

            }

            // Protege contra a não idexação no solr quando o processo ou documento passa de público para restrito ou quando o documento possui intimações não cumpridas:
            $isProcesso = $objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO;
            if(
                ( $isProcesso && $objProtocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO ) ||
                ( !$isPublico && !is_null($pesquisaLivre) && ($pesquisaLivre != InfraSolrUtil::obterTag($registros[$i], 'prot_doc', 'str') || $pesquisaLivre == '\*') ) ||
                ( !$isProcesso && !$bolListaDocumentoProcessoRestrito && (!empty($objProcessoDTO) && $objProcessoDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO) ) ||
                ( !$isProcesso && !$bolListaDocumentoProcessoPublico && (!empty($objProcessoDTO) && $objProcessoDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_PUBLICO) )
            ){
                $removidos++;
                continue;
            }

            $arrMetatags = [];
            $strSiglaUnidadeGeradora = $strDescricaoUnidadeGeradora = "";

            $dados = [
                'tipo_acesso'                   => InfraSolrUtil::obterTag($registros[$i], 'tipo_aces', 'str'),
                'id_unidade_acesso'             => InfraSolrUtil::obterTag($registros[$i], 'id_uni_aces', 'str'),
                'id_unidade_geradora'           => InfraSolrUtil::obterTag($registros[$i], 'id_uni_ger', 'int'),
                'protocolo_documento_formatado' => InfraSolrUtil::obterTag($registros[$i], 'prot_doc', 'str'),
                'protocolo_processo_formatado'  => InfraSolrUtil::obterTag($registros[$i], 'prot_proc', 'str'),
                'id_usuario_gerador'            => InfraSolrUtil::obterTag($registros[$i], 'id_usu_ger', 'int'),
                'id_tipo_processo'              => InfraSolrUtil::obterTag($registros[$i], 'id_tipo_proc', 'int'),
                'identificacao_protocolo'       => InfraSolrUtil::obterTag($registros[$i], 'numero', 'str'),
                'descricao_protocolo'           => InfraSolrUtil::obterTag($registros[$i], 'desc', 'str')
            ];

            if (isset($dados["id_unidade_geradora"])) {

                $objUnidadeDTO = new UnidadeDTO();
                $objUnidadeDTO->setNumIdUnidade($dados["id_unidade_geradora"]);
                $objUnidadeDTO->retStrSigla();
                $objUnidadeDTO->retStrDescricao();
                $objUnidadeRN = new UnidadeRN();
                $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
                if ($objUnidadeDTO != null) {
                    $strSiglaUnidadeGeradora = $objUnidadeDTO->getStrSigla();
                    $strDescricaoUnidadeGeradora = $objUnidadeDTO->getStrDescricao();
                }
            }

            $arrMetatags['Unidade'] = '<a alt="' . $strDescricaoUnidadeGeradora . '" title="' . $strDescricaoUnidadeGeradora . '" class="ancoraSigla">' . $strSiglaUnidadeGeradora . '</a>';
            $arrMetatags[''] = '';

            $dtaGeracao = InfraSolrUtil::obterTag($registros[$i], 'dta_ger', 'date');
            $dtaGeracao = preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", $dtaGeracao);
            $arrMetatags['Data'] = $dtaGeracao;

            // SNIPPET
            $numId = $registros[$i]->xpath("str[@name='id']");
            $numId = utf8_decode($numId[0]);
            $temp = $xml->xpath("/response/lst[@name='highlighting']/lst[@name='" . $numId . "']/arr[@name='content']/str");
            $snippet = '';

            for ($j = 0; $j < count($temp); $j++) {
                $snippetTemp = utf8_decode($temp[$j]);
                $snippetTemp = preg_replace('#<style(.*?)</style>.*?<script(.*?)</script>#is', '', $snippetTemp);
                $snippetTemp = strtoupper(trim(strip_tags($snippetTemp))) == "NULL" ? null : $snippetTemp;
                $snippetTemp = preg_replace("/<br>/i", "<br />", $snippetTemp);
                $snippetTemp = preg_replace("/&lt;.*?&gt;/", "", $snippetTemp);
                $snippet .= $snippetTemp . '<b>...</b>';
            }

            $idProcedimento = '';

            if ($objProtocoloDTO) {
                if ($objProtocoloDTO->getStrStaProtocolo() != ProtocoloRN::$TP_PROCEDIMENTO) {

                    $idProcedimento = $objDocumentoDTO->getDblIdProcedimento();
                    $dados["identificacao_protocolo"] = $objDocumentoDTO->getStrNomeSerie() . ' ' . $objDocumentoDTO->getStrNumero();

                    // Esconde highlight se intimação do documento ou anexos não estiver cumprida:
                    if( PesquisaIntegracao::verificaSeModPeticionamentoVersaoMinima() ){
                        $objMdPetIntCertidaoRN =  new MdPetIntCertidaoRN();
                        if( !$objMdPetIntCertidaoRN->verificaDocumentoEAnexoIntimacaoNaoCumprida( array($objDocumentoDTO->getDblIdDocumento(),false,false,true) ) ){
                            $snippet = '';
                        }
                    }

                    // INCLUIDO 21/12/2015 Substitui data de geração para data de assinatura de documentos gerados
                    if ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO) {
                        $objAssinaturaDTO = new AssinaturaDTO();
                        $objAssinaturaDTO->setDblIdDocumento($idProtocolo);
                        $objAssinaturaDTO->setOrdDthAberturaAtividade(InfraDTO::$TIPO_ORDENACAO_ASC);
                        $objAssinaturaDTO->retDthAberturaAtividade();
                        $objAssinaturaRN = new AssinaturaRN();
                        $arrObjAssinaturaDTO = $objAssinaturaRN->listarRN1323($objAssinaturaDTO);

                        if (is_array($arrObjAssinaturaDTO) && count($arrObjAssinaturaDTO) > 0) {
                            $objAssinaturaDTO = $arrObjAssinaturaDTO[0];
                            if ($objAssinaturaDTO != null && $objAssinaturaDTO->isSetDthAberturaAtividade()) {
                                $dtaGeracao = substr($objAssinaturaDTO->getDthAberturaAtividade(), 0, 10);
                                $arrMetatags['Data'] = $dtaGeracao;
                            }
                        }
                    }

                    if ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {
                        $arrMetatags['Data'] = $objProtocoloDTO->getDtaGeracao();
                    }
                } else {
                    $idProcedimento = $objProtocoloDTO->getDblIdProtocolo();
                }
            }

            $parametrosCriptografadosProcesso = MdPesqCriptografia::criptografa('acao_externa=md_pesq_processo_exibir&id_orgao_acesso_externo=0&id_procedimento=' . $idProcedimento);
            $urlPesquisaProcesso = 'md_pesq_processo_exibir.php?' . $parametrosCriptografadosProcesso;
            $arvore = $urlPesquisaProcesso;
            $tituloLinkNumeroProcesso = "<a href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink(MdPesqSolrUtilExterno::prepararUrl($arvore))) . "\" title=\"Acessar\" target=\"_blank\" class=\"protocoloNormal processoVisitado\" onClick=\"infraLimparFormatarTrAcessada(this.parentNode.parentNode);\">";
            $tituloLinkNumeroProcesso .= $dados["protocolo_processo_formatado"];
            $tituloLinkNumeroProcesso .= "</a>";

            //Tipo do Processo
            $strNomeTipoProcedimento = "";
            if (isset($dados["id_tipo_processo"])) {
                $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
                $objTipoProcedimentoDTO->setNumIdTipoProcedimento($dados["id_tipo_processo"]);
                $objTipoProcedimentoDTO->retStrNome();
                $objTipoProcedimentoRN = new TipoProcedimentoRN();
                $objTipoProcedimentoDTO = $objTipoProcedimentoRN->consultarRN0267($objTipoProcedimentoDTO);
                if ($objTipoProcedimentoDTO != null) {
                    $strNomeTipoProcedimento = $objTipoProcedimentoDTO->getStrNome();
                }
            }
            $titulo = $strNomeTipoProcedimento . " nº" . $tituloLinkNumeroProcesso;
            $strProtocoloDocumento = "";
            if (empty($dados["protocolo_documento_formatado"]) == false) {
                if ($objDocumentoDTO == null) {
                    print_r($idProtocolo);
                    echo ' ';
                    print_r($dados["protocolo_documento_formatado"]);
                    die;
                }
                $titulo .= " ";
                if($isPublico){
                    $parametrosCriptografadosDocumentos = MdPesqCriptografia::criptografa('acao_externa=md_pesq_documento_exibir&id_orgao_acesso_externo=0&id_documento=' . $objDocumentoDTO->getDblIdDocumento());
                    $endereco = 'md_pesq_documento_consulta_externa.php?' . $parametrosCriptografadosDocumentos;
                    $titulo .= "(<a title=\"Acessar\" target=\"_blank\" href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink($endereco)) . "\" onClick=\"infraLimparFormatarTrAcessada(this.parentNode.parentNode);\"";
                    $titulo .= " class=\"protocoloNormal\" >" . trim($dados["identificacao_protocolo"]) ."</a>)";
                    $strProtocoloDocumento .= "<a title=\"Acessar\" target=\"_blank\" href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink($endereco)) . "\" class=\"protocoloNormal processoVisitado\" onClick=\"infraLimparFormatarTrAcessada(this.parentNode.parentNode);\">";
                    $strProtocoloDocumento .= $dados["protocolo_documento_formatado"];
                    $strProtocoloDocumento .= "</a>";
                }else{
                    $titulo .= "(" . trim($dados["identificacao_protocolo"]) .")";
                    $strProtocoloDocumento .= $dados["protocolo_documento_formatado"];
                }
            }
            $tituloCompleto = "<a href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink(MdPesqSolrUtilExterno::prepararUrl($arvore))) . "\" title=\"Acessar\" target=\"_blank\" class=\"arvore\"  onClick=\"infraLimparFormatarTrAcessada(this.parentNode.parentNode);\">";
            $tituloCompleto .= "<img border=\"0\" src=\"imagens/arvore.svg\" alt=\"Acessar\" title=\"Acessar\" class=\"arvore\" />";
            $tituloCompleto .= "</a>";
            $tituloCompleto .= $titulo;
            // REMOVE TAGS DO TÍTULO
            $tituloCompleto = preg_replace("/&lt;.*?&gt;/", "", $tituloCompleto);
            if ($objProtocoloDTO) {
                if(!$isPublico && !$bolLinkMetadadosProcessoRestrito) {

                    $titulo = $strNomeTipoProcedimento . " nº " . $dados["protocolo_processo_formatado"];
                    if($objProtocoloDTO->getStrStaProtocolo() != ProtocoloRN::$TP_PROCEDIMENTO){
                        $titulo .= " (".trim($dados["identificacao_protocolo"]).")";
                    }
                    $strProtocoloDocumento = $dados["protocolo_documento_formatado"];
                    $tituloCompleto = "<img border=\"0\" src=\"imagens/arvore.svg\" title=\"Acessar\" class=\"arvore\" />";
                    $tituloCompleto .= $titulo;

                    $objHipoteseLegalDTO = new HipoteseLegalDTO();
                    $objHipoteseLegalDTO->retTodos(false);
                    $objHipoteseLegalDTO->setNumIdHipoteseLegal($objProtocoloDTO->getNumIdHipoteseLegal());
                    $objHipoteseLegalDTO = (new HipoteseLegalRN())->consultar($objHipoteseLegalDTO);

                    if ($objHipoteseLegalDTO != null) {
                        $snippet = '<b>Hipótese Legal de Restrição de Acesso: ' . $objHipoteseLegalDTO->getStrNome() . ' (' . $objHipoteseLegalDTO->getStrBaseLegal() . ')</b>';
                        if (!empty($txtDescricaoProcedimentoAcessoRestrito)) {
                            $snippet .= '<br/>' . $txtDescricaoProcedimentoAcessoRestrito;
                        }
                    } else {
                        $snippet = !empty($txtDescricaoProcedimentoAcessoRestrito) ? $txtDescricaoProcedimentoAcessoRestrito : 'Processo de Acesso Restrito';
                    }

                }

            }

            if ($objProtocoloDTO) {

                $html .= "<tr class=\"pesquisaTituloRegistro\">\n";
                $html .= "<td colspan=\"2\" class=\"pesquisaTituloEsquerda\" data-prot=\"".$objProtocoloDTO->getStrProtocoloFormatado()."\">";
                $html .= $tituloCompleto;
                $html .= "</td>\n";
                $html .= "<td class=\"pesquisaTituloDireita\">";
                $html .= $strProtocoloDocumento;
                $html .= "</td>\n";
                $html .= "</tr>\n";

                if ((!empty($snippet) && $isPublico) || (!$isPublico && !$bolLinkMetadadosProcessoRestrito)){
                    $html .= "<tr>\n";
                    $html .= "<td width=\"99%\" colspan=\"3\" class=\"resSnippet\">\n";
                    $html .= $snippet;
                    $html .= "</td>\n";
                    $html .= "</tr>\n";
                }

                if (is_array($arrMetatags) && count($arrMetatags) > 0) {
                    $html .= "<tr>\n";
                    foreach ($arrMetatags as $nomeMetaTag => $valorMetaTag) {
                        if($nomeMetaTag != 'Usuário' && $valorMetaTag != ''){
                            $html .= "<td class=\"pesquisaMetatag\" width=\"33%\"><b>" . $nomeMetaTag . ":</b> " . $valorMetaTag . "</td>\n";
                        }else{
                            $html .= "<td class=\"pesquisaMetatag\" width=\"33%\"></td>\n";
                        }
                    }
                    $html .= "</tr>\n";
                }

            }
        }
//        $html .= "</tbody></table>\n";

        if(count($registros) - $removidos == 0){
            return $semResultados;
        }

        $pagLinksTop = MdPesqSolrUtilExterno::criarBarraNavegacao($itens, $inicio, 10, PaginaSEIExterna::getInstance(), SessaoSEIExterna::getInstance(), $md5Captcha, 'md_pesq_processo_pesquisar.php', 'top');
        $pagLinksBottom = MdPesqSolrUtilExterno::criarBarraNavegacao($itens, $inicio, 10, PaginaSEIExterna::getInstance(), SessaoSEIExterna::getInstance(), $md5Captcha);

        return '<resultado itens="'.$itens.'" urlsolar="">'.$html.'</resultado>';
//        return $html;

    }

}

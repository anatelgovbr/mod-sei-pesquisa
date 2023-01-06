<?php
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-11-12
 * Versão do Gerador de Código: 1.0
 * Versão no CVS/SVN:
 *
 * sei
 * pesquisa
 * BuscaProtocoloExterno
 *
 *
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 */

/**
 * Classe de Busca na solução de indexação solr.
 *
 *
 * @package institucional_pesquisa_BuscaProtocoloExterno
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso sem nenhuma restrição,
 *         salvo pelas informações a seguir referentes
 *         a @author e @copyright que devem ser mantidas inalteradas!
 * @copyright Conselho Administrativo de Defesa Econômica ©2014-2018
 *            <http://www.cade.gov.br>
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 */


require_once("MdPesqSolrUtilExterno.php");
require_once("MdPesqCriptografia.php");

class  MdPesqBuscaProtocoloExterno
{

    public static function executar($q, $strDescricaoPesquisa, $strObservacaoPesquisa, $inicio, $numMaxResultados, $strParticipanteSolr, $md5Captcha = null)
    {

        //carrega configurações pesquisa

        $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
        $objParametroPesquisaDTO->retStrNome();
        $objParametroPesquisaDTO->retStrValor();

        $objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
        $arrObjParametroPesquisaDTO = $objParametroPesquisaRN->listar($objParametroPesquisaDTO);

        $arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO($arrObjParametroPesquisaDTO, 'Valor', 'Nome');

        $bolPesquisaDocumentoProcessoPublico = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_DOCUMENTO_PROCESSO_PUBLICO] == 'S' ? true : false;
        $bolPesquisaProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_PROCESSO_RESTRITO] == 'S' ? true : false;
        $bolLinkMetadadosProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_METADADOS_PROCESSO_RESTRITO] == 'S' ? true : false;
        $txtDescricaoProcedimentoAcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO];
        $bolAutocompletarInterressado = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_AUTO_COMPLETAR_INTERESSADO] == 'S' ? true : false;

        $parametros = new stdClass();
        $filtro = new stdClass();

        $partialfields = '';

        //die($_REQUEST["partialfields"]);

        if (!$bolAutocompletarInterressado) {
            $partialfields = $strParticipanteSolr;
        }


        if (!InfraString::isBolVazia($_REQUEST["partialfields"])) {
            $partialfields = $partialfields . $_REQUEST["partialfields"];


            $checkbox = array();
            if (preg_match("/sta_prot:([A-Z;]+)/i", $partialfields, $checkbox) > 0) {
                $checkbox = explode(";", $checkbox[1]);
            }


        } else {
            $checkbox = array('P', 'G', 'R');
        }

        $grupo = array();

        // PESQUISAR EM: PROCESSOS
        if (in_array("P", $checkbox)) {

            if ($bolPesquisaProcessoRestrito) {
                array_push($grupo, "(sta_prot:P)");
            } else {
                array_push($grupo, "(sta_prot:P)");
            }

        }

        // PESQUISAR EM: DOCUMENTOS EXTERNOS

        if ($bolPesquisaDocumentoProcessoPublico) {

            if (in_array("R", $checkbox)) {
                array_push($grupo, "(sta_prot:R AND tipo_aces:P)");
            }

            // PESQUISAR EM: DOCUMENTOS GERADOS
            $filtroDocumentoInternoAssinado = '';
            if (in_array("G", $checkbox)) {
                array_push($grupo, "(sta_prot:G)");
//                array_push($grupo, "(sta_prot:G AND tipo_aces:P)");
            }
        }


        if (count($grupo) > 0) {
            //Alteração para consulta externa sem login
            $staProtocolo = '(' . implode(" OR ", $grupo) . ')';
            //$staProtocolo = '('.implode(" OR ", $grupo).') AND tipo_acesso:P';


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


        ////////////////////////////
// 		print_r($parametros);
// 		die;   
        //////////////////////////


        $parametros->q = utf8_encode($parametros->q);


        //Monta url de busca
        $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr', 'Servidor') . '/' . ConfiguracaoSEI::getInstance()->getValor('Solr', 'CoreProtocolos') . '/select?' . http_build_query($parametros) . '&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&hl.maxClauseCount=2000&fl=id,id_prot,id_proc,id_doc,id_tipo_proc,id_serie,id_anexo,id_uni_ger,prot_doc,prot_proc,numero,id_usu_ger,dta_ger,desc';
        //$urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr','Servidor') . '/'.ConfiguracaoSEI::getInstance()->getValor('Solr','CoreProtocolos') .'/select?' . http_build_query($parametros).'&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&hl.maxClauseCount=2000&fl=id,tipo_acesso,id_unidade_acesso,id_unidade_geradora,id_unidade_aberto,identificacao_protocolo,nome_tipo_processo,protocolo_documento_formatado,protocolo_processo_formatado,sigla_unidade_geradora,descricao_unidade_geradora,sigla_usuario_gerador,nome_usuario_gerador,dta_geracao,link_arvore,id_protocolo';

        ////////////////////////////
// 				print_r($urlBusca);
// 				die;
        //////////////////////////


        // @DEBUG

        //echo 'URL:'.$urlBusca;
        //echo "PARÂMETROS: " . print_r($parametros, true);

        //Objeto que contera o xml do resultado de busca
        //$resultados = new SolrXmlResults($urlBusca, $numMaximoResultados);

        //$numSeg = InfraUtil::verificarTempoProcessamento();
        $resultados = file_get_contents($urlBusca, true);
        //$numSeg = InfraUtil::verificarTempoProcessamento($numSeg);

        if ($resultados == '') {
            throw new InfraException('Nenhum retorno encontrado no resultado da pesquisa.');
        }

        $xml = simplexml_load_string($resultados);

        $html = '';

        $arrRet = $xml->xpath('/response/result/@numFound');

        $itens = array_shift($arrRet);


        if ($itens == 0) {

            $html .= "<div class=\"sem-resultado\">";
            $html .= "Sua pesquisa pelo termo <b>" . PaginaSEI::tratarHTML($_POST["q"]) . "</b> não encontrou nenhum protocolo correspondente.";
            $html .= "<br/>";
            $html .= "<br/>";
            $html .= "Sugestões:";
            $html .= "<ul>";
            $html .= "<li>Certifique-se de que todas as palavras estejam escritas corretamente.</li>";
            $html .= "<li>Tente palavras-chave diferentes.</li>";
            $html .= "<li>Tente palavras-chave mais genéricas.</li>";
            $html .= "</ul>";
            $html .= "</div>";


        } else {

            $html = MdPesqSolrUtilExterno::criarBarraEstatisticas($itens, $inicio, ($inicio + 10));

            $registros = $xml->xpath('/response/result/doc');


            $numRegistros = sizeof($registros);

            for ($i = 0; $i < $numRegistros; $i++) {

                $dados = array();
                $titulo = "";
                $regResultado = $registros[$i];

                $dados["tipo_acesso"] = InfraSolrUtil::obterTag($regResultado, 'tipo_aces', 'str');
                $dados["id_unidade_acesso"] = InfraSolrUtil::obterTag($regResultado, 'id_uni_aces', 'str');
                $dados["id_unidade_geradora"] = InfraSolrUtil::obterTag($regResultado, 'id_uni_ger', 'int');
                //$dados["id_unidade_aberto"] = $registros[$i]->xpath("str[@name='id_unidade_aberto']");
                //$dados["identificacao_protocolo"] = $registros[$i]->xpath("str[@name='identificacao_protocolo']");
                //$dados["nome_tipo_processo"] = $registros[$i]->xpath("str[@name='nome_tipo_processo']");
                $dados["protocolo_documento_formatado"] = InfraSolrUtil::obterTag($regResultado, 'prot_doc', 'str');
                $dados["protocolo_processo_formatado"] = InfraSolrUtil::obterTag($regResultado, 'prot_proc', 'str');
                $dados["id_usuario_gerador"] = InfraSolrUtil::obterTag($regResultado, 'id_usu_ger', 'int');
                $dados['id_tipo_processo'] = InfraSolrUtil::obterTag($regResultado, 'id_tipo_proc', 'int');
                $dados["identificacao_protocolo"] = InfraSolrUtil::obterTag($regResultado, 'numero', 'str');
                $dados["descricao_protocolo"] = InfraSolrUtil::obterTag($regResultado, 'desc', 'str');


                $arrMetatags = array();
                $strSiglaUnidadeGeradora = "";
                $strDescricaoUnidadeGeradora = "";

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


                $arrMetatags['Unidade Geradora'] = '<a alt="' . $strDescricaoUnidadeGeradora . '" title="' . $strDescricaoUnidadeGeradora . '" class="ancoraSigla">' . $strSiglaUnidadeGeradora . '</a>';

                $dtaGeracao = InfraSolrUtil::obterTag($regResultado, 'dta_ger', 'date');
                $dtaGeracao = preg_replace("/(\d{4})-(\d{2})-(\d{2})(.*)/", "$3/$2/$1", $dtaGeracao);

                $arrMetatags['Data'] = $dtaGeracao;

                // SNIPPET
                $numId = $registros[$i]->xpath("str[@name='id']");
                $numId = utf8_decode($numId[0]);
                $temp = $xml->xpath("/response/lst[@name='highlighting']/lst[@name='" . $numId . "']/arr[@name='content']/str");

                $snippet = '';
                for ($j = 0; $j < count($temp); $j++) {
                    $snippetTemp = utf8_decode($temp[$j]);
                    $snippetTemp = strtoupper(trim(strip_tags($snippetTemp))) == "NULL" ? null : $snippetTemp;
                    $snippetTemp = preg_replace("/<br>/i", "<br />", $snippetTemp);
                    $snippetTemp = preg_replace("/&lt;.*?&gt;/", "", $snippetTemp);
                    $snippet .= $snippetTemp . '<b>&nbsp;&nbsp;...&nbsp;&nbsp;</b>';
                }


                // PROTOCOLO
                $idProtocolo = InfraSolrUtil::obterTag($regResultado, 'id_prot', 'long');
                $objProtocoloRN = new ProtocoloRN();
                $objProtocoloDTO = new ProtocoloDTO();
                $objProtocoloDTO->setDblIdProtocolo($idProtocolo);
                $objProtocoloDTO->retDblIdProtocolo();
                $objProtocoloDTO->retStrStaProtocolo();
                $objProtocoloDTO->retStrStaNivelAcessoGlobal();
                $objProtocoloDTO->retStrProtocoloFormatado();
                $objProtocoloDTO->retNumIdHipoteseLegal();
                $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
                $idProcedimento = '';
                if ($objProtocoloDTO) {
                    if ($objProtocoloDTO->getStrStaProtocolo() != ProtocoloRN::$TP_PROCEDIMENTO) {
                        $objDocumentoRN = new DocumentoRN();
                        $objDocumentoDTO = new DocumentoDTO();
                        $objDocumentoDTO->setDblIdDocumento($idProtocolo);
                        $objDocumentoDTO->retDblIdDocumento();
                        $objDocumentoDTO->retDblIdProcedimento();
                        $objDocumentoDTO->retNumIdSerie();
                        $objDocumentoDTO->retStrNomeSerie();
                        $objDocumentoDTO->retStrNumero();
                        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
                        $idProcedimento = $objDocumentoDTO->getDblIdProcedimento();

                        $dados["identificacao_protocolo"] = $objDocumentoDTO->getStrNomeSerie() . ' ' . $objDocumentoDTO->getStrNumero();

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

                            $objAtributoAndamentoDTO = new AtributoAndamentoDTO();
                            $objAtributoAndamentoDTO->setDblIdProtocoloAtividade($idProcedimento);
                            $objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_RECEBIMENTO_DOCUMENTO);
                            $objAtributoAndamentoDTO->setStrNome("DOCUMENTO");
                            $objAtributoAndamentoDTO->setStrIdOrigem($idProtocolo);

                            $objAtributoAndamentoDTO->retDthAberturaAtividade();

                            $objAtributoAndamentoRN = new AtributoAndamentoRN();

                            $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);

                            if ($objAtributoAndamentoDTO != null && $objAtributoAndamentoDTO->isSetDthAberturaAtividade()) {

                                $dtaGeracao = substr($objAtributoAndamentoDTO->getDthAberturaAtividade(), 0, 10);

                                $arrMetatags['Data'] = $dtaGeracao;

                            }
                        }

                    } else {
                        $idProcedimento = $objProtocoloDTO->getDblIdProtocolo();
                    }
                }

                $parametrosCriptografadosProcesso = MdPesqCriptografia::criptografa('acao_externa=md_pesq_processo_exibir&id_orgao_acesso_externo=0&id_procedimento=' . $idProcedimento);
                $urlPesquisaProcesso = 'md_pesq_processo_exibir.php?' . $parametrosCriptografadosProcesso;
                $arvore = $urlPesquisaProcesso;


                $tituloLinkNumeroProcesso = "<a href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink(MdPesqSolrUtilExterno::prepararUrl($arvore))) . "\" target=\"_blank\" class=\"protocoloNormal\">";
                $tituloLinkNumeroProcesso .= $dados["protocolo_processo_formatado"];
                $tituloLinkNumeroProcesso .= "</a>";

                $tituloProtocolo = $tituloLinkNumeroProcesso;

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


                $titulo = $strNomeTipoProcedimento . " N&deg; " . $tituloLinkNumeroProcesso;

                if (empty($dados["protocolo_documento_formatado"]) == false) {
                    if ($objDocumentoDTO == null) {
                        print_r($idProtocolo);
                        echo ' ';
                        print_r($dados["protocolo_documento_formatado"]);
                        die;
                    }

                    $titulo .= " ";
                    $parametrosCriptografadosDocumentos = MdPesqCriptografia::criptografa('acao_externa=md_pesq_documento_exibir&id_orgao_acesso_externo=0&id_documento=' . $objDocumentoDTO->getDblIdDocumento());
                    $endereco = 'md_pesq_documento_consulta_externa.php?' . $parametrosCriptografadosDocumentos;
                    $titulo .= "(<a target=\"_blank\" href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink($endereco)) . "\"";
                    $titulo .= " class=\"protocoloNormal\"";
                    $titulo .= ">" . $dados["identificacao_protocolo"] . "</a>)";

                    //$tituloProtocolo = "<a target=\"_blank\" href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink($endereco)) . "\" class=\"protocoloNormal\" >".$dados["protocolo_documento_formatado"]. "</a>";

                }


                $tituloCompleto = "<a href=\"" . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink(MdPesqSolrUtilExterno::prepararUrl($arvore))) . "\" target=\"_blank\" class=\"arvore\">";
                $tituloCompleto .= "<img border=\"0\" src=\"solr/img/arvore.svg\" alt=\"\" title=\"Visualizar árvore\" width=\"24\" height=\"24\" class=\"arvore\" />";
                $tituloCompleto .= "</a>";

                $tituloCompleto .= $titulo;

                // REMOVE TAGS DO TÍTULO
                $tituloCompleto = preg_replace("/&lt;.*?&gt;/", "", $tituloCompleto);


                if ($objProtocoloDTO) {
                    if ($objProtocoloDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO && $bolPesquisaProcessoRestrito) {

                        if (!$bolLinkMetadadosProcessoRestrito || $objProtocoloDTO->getStrStaProtocolo() != ProtocoloRN::$TP_PROCEDIMENTO) {
                            $tituloCompleto = $objProtocoloDTO->getStrProtocoloFormatado();
                            $titulo = $objProtocoloDTO->getStrProtocoloFormatado();
                            $tituloProtocolo = $objProtocoloDTO->getStrProtocoloFormatado();
                            //$tituloProtocolo = 'N° SEI (Documento/Processo)';

                            $objHipoteseLegalDTO = new HipoteseLegalDTO();
                            $objHipoteseLegalDTO->retTodos(false);
                            $objHipoteseLegalDTO->setNumIdHipoteseLegal($objProtocoloDTO->getNumIdHipoteseLegal());

                            $objHipoteseLegalRN = new HipoteseLegalRN();
                            $objHipoteseLegalDTO = $objHipoteseLegalRN->consultar($objHipoteseLegalDTO);

                            if ($objHipoteseLegalDTO != null) {
                                $snippet = '<b>Hipótese Legal de Restrição de Acesso: ' . $objHipoteseLegalDTO->getStrNome() . ' (' . $objHipoteseLegalDTO->getStrBaseLegal() . ')</b>';
                                $txtDescricaoProcedimentoAcessoRestrito = trim($txtDescricaoProcedimentoAcessoRestrito);
                                if (!empty($txtDescricaoProcedimentoAcessoRestrito)) {
                                    $snippet .= '<br/>';
                                    $snippet .= $txtDescricaoProcedimentoAcessoRestrito;
                                }
                            } else {
                                if (!empty($txtDescricaoProcedimentoAcessoRestrito)) {
                                    $snippet = $txtDescricaoProcedimentoAcessoRestrito;
                                } else {
                                    $snippet = 'Processo de Acesso Restrito';
                                }

                            }

                            unset($arrMetatags['Usuário']);
                            unset($arrMetatags['Unidade Geradora']);
                            unset($arrMetatags['Data']);
                        }
                    }
                }

                // Protege contra a não idexação no solr quando o processo passa de público para restrito.
                if ($objProtocoloDTO) {
                    if (($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO && $objProtocoloDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO && !$bolPesquisaProcessoRestrito) || ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO && $objProtocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO)) {

                        $tituloCompleto = 'ACESSO RESTRITO';
                        $titulo = 'ACESSO RESTRITO';
                        $tituloProtocolo = 'ACESSO RESTRITO';
                        unset($arrMetatags['Usuário']);
                        unset($arrMetatags['Unidade Geradora']);
                        unset($arrMetatags['Data']);
                        $snippet = 'ACESSO RESTRITO';


                    }
                }
                // Protege contra a não idexação no solr quando o documento passa de público para restrito.
                //if($objProtocoloDTO->getStrStaProtocolo() != ProtocoloRN::$TP_PROCEDIMENTO && $objProtocoloDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO && !$bolPesquisaDocumentoProcessoRestrito){
                if ($objProtocoloDTO) {
                    if ($objProtocoloDTO->getStrStaProtocolo() != ProtocoloRN::$TP_PROCEDIMENTO && $objProtocoloDTO->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO) {

                        $tituloCompleto = 'ACESSO RESTRITO';
                        $titulo = 'ACESSO RESTRITO';
                        $tituloProtocolo = 'ACESSO RESTRITO';
                        unset($arrMetatags['Usuário']);
                        unset($arrMetatags['Unidade Geradora']);
                        unset($arrMetatags['Data']);
                        $snippet = 'ACESSO RESTRITO';


                    }
                }
                if ($objProtocoloDTO) {
                    $html .= "<table border=\"0\" class=\"pesquisaResultado\">\n";
                    $html .= "<tr class=\"pesquisaTituloRegistro\">\n";
                    $html .= "<td class=\"pesquisaTituloEsquerda\">";
                    $html .= $tituloCompleto;
                    $html .= "</td>\n";
                    $html .= "<td class=\"pesquisaTituloDireita\">";
                    $html .= $tituloProtocolo;
                    $html .= "</td>\n";
                    $html .= "</tr>\n";

                    if (empty($snippet) == false)
                        $html .= "<tr>\n
											<td colspan=\"2\" class=\"resSnippet\">
												" . $snippet . "
											</td>\n
											</tr>\n";

                    if (count($arrMetatags)) {
                        $html .= "<tr>\n";
                        $html .= "<td colspan=\"2\" class=\"metatag\">\n";
                        $html .= "<table>\n";
                        $html .= "<tbody>\n";
                        $html .= "<tr>\n";

                        foreach ($arrMetatags as $nomeMetaTag => $valorMetaTag) {

                            $html .= "<td>";
                            $html .= "<b>" . $nomeMetaTag . ":</b> " . $valorMetaTag;
                            $html .= "</td>\n";


                        }

                        $html .= "</tr>\n";
                        $html .= "</tbody>\n";
                        $html .= "</table>\n";
                        $html .= "</td>\n";
                        $html .= "</tr>\n";
                    }

                    $html .= "</table>\n";
                }
            }


            $html .= MdPesqSolrUtilExterno::criarBarraNavegacao($itens, $inicio, 10, PaginaSEIExterna::getInstance(), SessaoSEIExterna::getInstance(), $md5Captcha);
        }

        return $html;

    }
}

?>

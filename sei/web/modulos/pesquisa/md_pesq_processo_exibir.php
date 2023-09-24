<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 29/09/2014
 *
 *
 */

try {
	require_once dirname(__FILE__).'/../../SEI.php';

    session_start();

	SessaoSEIExterna::getInstance()->validarSessao();
 
//	InfraDebug::getInstance()->setBolLigado(false);
//	InfraDebug::getInstance()->setBolDebugInfra(true);
//	InfraDebug::getInstance()->limpar();

  	MdPesqConverteURI::converterURI();
   	MdPesqPesquisaUtil::valiadarLink();

	//carrega configuracoes pesquisa
	$objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
	$objParametroPesquisaDTO->retStrNome();
	$objParametroPesquisaDTO->retStrValor();

	$objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
	$arrObjParametroPesquisaDTO = $objParametroPesquisaRN->listar($objParametroPesquisaDTO);
	$arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO($arrObjParametroPesquisaDTO,'Valor','Nome');

    $bolPesquisaDocumentoProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_PESQUISA_DOCUMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
	$bolListaDocumentoProcessoPublico = true; // Forcando para inutilizar o parametro
	$bolListaAndamentoProcessoPublico = true; // Forcando para inutilizar o parametro
	$bolCaptchaGerarPdf = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_CAPTCHA_PDF] == 'S' ? true : false;
	$bolLinkMetadadosProcessoRestrito =  $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_METADADOS_PROCESSO_RESTRITO] == 'S' ? true : false;
	$bolListaAndamentoProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_ANDAMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
	$bolListaDocumentoProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
	$txtDescricaoProcessoAcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO];
	$dtaCortePesquisa = (new MdPesqParametroPesquisaRN())->existeDataCortePesquisa();

	PaginaSEIExterna::getInstance()->setTipoPagina(PaginaSEIExterna::$TIPO_PAGINA_SEM_MENU);

    $dblIdProcedimento = $_GET['id_procedimento'];
     
	//Carregar dados do cabecalho
	$objProcedimentoDTO = new ProcedimentoDTO();
	$objProcedimentoDTO->retStrNomeTipoProcedimento();
	$objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
	$objProcedimentoDTO->retDtaGeracaoProtocolo();
	$objProcedimentoDTO->retStrStaNivelAcessoGlobalProtocolo();
	$objProcedimentoDTO->retStrStaNivelAcessoLocalProtocolo();
	$objProcedimentoDTO->retNumIdHipoteseLegalProtocolo();
	
	$objProcedimentoDTO->setDblIdProcedimento($dblIdProcedimento);
	$objProcedimentoDTO->setStrSinDocTodos('S');  
	$objProcedimentoDTO->setStrSinProcAnexados('S');
	//$objProcedimentoDTO->setStrSinDocAnexos('S');  
	//$objProcedimentoDTO->setStrSinDocConteudo('S');
	
	$objProcedimentoRN = new ProcedimentoRN();
	$arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);
	
	if (count($arr)==0){
	  //SessaoSEIExterna::getInstance()->sair(null, 'Processo não encontrado.');
		die('Processo não encontrado.');
	}
	
	$objProcedimentoDTO = $arr[0];

    if($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO  ){
        die('Processo não encontrado.');
    }

	if(!$bolLinkMetadadosProcessoRestrito && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() != ProtocoloRN::$NA_PUBLICO){
	    die('Processo não encontrado.');
	}
	
	//Carregar interessados no processo
	$objInteressadosParticipanteDTO = new ParticipanteDTO();
	$objInteressadosParticipanteDTO->retStrNomeContato();
	$objInteressadosParticipanteDTO->setDblIdProtocolo($dblIdProcedimento);
	$objInteressadosParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
	
	$objInteressadosParticipanteRN = new ParticipanteRN();
	
	$objInteressadosParticipanteDTO = $objInteressadosParticipanteRN->listarRN0189($objInteressadosParticipanteDTO);

	if (count($objInteressadosParticipanteDTO)==0){
	  $strInteressados = '&nbsp;';
	}else{
  		$strInteressados = '';
  		foreach($objInteressadosParticipanteDTO as $objInteressadoParticipanteDTO){
  			$strInteressados .= $objInteressadoParticipanteDTO->getStrNomeContato()."<br /> ";
  		}
	}
	
	//Mensagem Processo Restrito
	$strMensagemProcessoRestrito = '';
	$strHipoteseLegal = '';
	if($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO && $bolLinkMetadadosProcessoRestrito){
		
		$objHipoteseLegalDTO = new HipoteseLegalDTO();
		$objHipoteseLegalDTO->setNumIdHipoteseLegal($objProcedimentoDTO->getNumIdHipoteseLegalProtocolo());
		$objHipoteseLegalDTO->retStrBaseLegal();
		$objHipoteseLegalDTO->retStrNome();
		
		$objHipoteseLegalRN = new HipoteseLegalRN();
        $objHipoteseLegalDTO = $objHipoteseLegalRN->consultar($objHipoteseLegalDTO);
    				 
    	if($objHipoteseLegalDTO != null){
    		
    		$strHipoteseLegal .= '<img src="/infra_css/imagens/espaco.gif">';
    		$strHipoteseLegal .= '<img src="imagens/sei_chave_restrito.svg" style="vertical-align: middle;" title="Acesso Restrito. &#13'.PaginaSEIExterna::getInstance()->formatarXHTML($objHipoteseLegalDTO->getStrNome().' ('.$objHipoteseLegalDTO->getStrBaseLegal().')').'">';
   
    	}		
		
		$strMensagemProcessoRestrito = '<p style="font-size: 1.2em;"> '.$txtDescricaoProcessoAcessoRestrito.'</p>';
		
	}
	
	$strResultadoCabecalho = '';
	$strResultadoCabecalho .= '<table id="tblCabecalho" width="99.3%" class="infraTable" summary="Cabeçalho de Processo" >'."\n";
	$strResultadoCabecalho .= '<tr><th class="infraTh" colspan="2">Autuação</th></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Processo:</td><td>'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().$strHipoteseLegal.'</td></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Tipo:</td><td>'.PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTO->getStrNomeTipoProcedimento()).'</td></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Data de Geração:</td><td>'.$objProcedimentoDTO->getDtaGeracaoProtocolo().'</td></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Interessados:</td><td> '.$strInteressados.'</td></tr>'."\n";
	$strResultadoCabecalho .= '</table>'."\n";
 	

	//$arrObjDocumentoDTO = InfraArray::indexarArrInfraDTO($objProcedimentoDTO->getArrObjDocumentoDTO(),'IdDocumento');
  $arrObjRelProtocoloProtocoloDTO = array();
  if(($bolListaDocumentoProcessoPublico && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_PUBLICO) || ($bolListaDocumentoProcessoRestrito && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO)){
      $arrObjRelProtocoloProtocoloDTO = $objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO();
  }
  
  //Objeto Fake para paginacao
  $objProtocoloPesquisaPublicaPaginacaoDTO = new MdPesqProtocoloPesquisaPublicaDTO();
  $objProtocoloPesquisaPublicaPaginacaoDTO->retTodos(true);
  PaginaSEIExterna::getInstance()->prepararOrdenacao($objProtocoloPesquisaPublicaPaginacaoDTO, 'Registro', InfraDTO::$TIPO_ORDENACAO_ASC);
  //PaginaSEIExterna::getInstance()->prepararPaginacao($objProtocoloPesquisaPublicaPaginacaoDTO,4);
  //PaginaSEIExterna::getInstance()->processarPaginacao($objProtocoloPesquisaPublicaPaginacaoDTO);
  $arrObjProtocoloPesquisaPublicaDTO = array();
	
  $objDocumentoRN = new DocumentoRN();
	
  $numProtocolos = 0;
  $numDocumentosPdf = 0;
  $strCssMostrarAcoes = '.colunaAcoes {display:none;}'."\n";
  
  $strThCheck = PaginaSEIExterna::getInstance()->getThCheck();
  
  foreach($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO){

    if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao()==RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO){
      
      	$objDocumentoDTO = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();
      	//valida documentos para retornar
   	  	if ($objDocumentoRN->verificarSelecaoAcessoBasico($objDocumentoDTO)){
   	  	
   	  		$objProtocoloPesquisaPublicaDTO = new MdPesqProtocoloPesquisaPublicaDTO();
   	  		$objProtocoloPesquisaPublicaDTO->setStrNumeroSEI($objDocumentoDTO->getStrProtocoloDocumentoFormatado());
   	  		$objProtocoloPesquisaPublicaDTO->setStrTipoDocumento(PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrNumero()));
   	  		
   	  		
   	  		if($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){

   	  				$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
  					$objAtributoAndamentoDTO->setDblIdProtocoloAtividade($objProcedimentoDTO->getDblIdProcedimento());
  					$objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_RECEBIMENTO_DOCUMENTO);
  					$objAtributoAndamentoDTO->setStrNome("DOCUMENTO");
  					$objAtributoAndamentoDTO->setStrIdOrigem($objDocumentoDTO->getDblIdDocumento());
  					
  					$objAtributoAndamentoDTO->retDthAberturaAtividade();
  					
  					$objAtributoAndamentoRN = new AtributoAndamentoRN();
  					
  					$objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);
  					
  					if($objAtributoAndamentoDTO != null && $objAtributoAndamentoDTO->isSetDthAberturaAtividade()){
  					
  						$dtaRecebimento =  substr($objAtributoAndamentoDTO->getDthAberturaAtividade(),0,10);
  						
  						$objProtocoloPesquisaPublicaDTO->setDtaRegistro($dtaRecebimento);
  					
  					}else{
  						
  						$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objDocumentoDTO->getDtaGeracaoProtocolo());
  					}
  					
  					$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objDocumentoDTO->getDtaGeracaoProtocolo());
  			
   	  		}else if ($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO){
   	  			
   	  				$objAssinaturaDTO = new AssinaturaDTO();
   	  				$objAssinaturaDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
   	  				$objAssinaturaDTO->setOrdNumIdAssinatura(InfraDTO::$TIPO_ORDENACAO_ASC);
   	  				$objAssinaturaDTO->retDthAberturaAtividade();
   	  				
   	  				$objAssinaturaRN = new AssinaturaRN();
   	  				$arrObjAssinaturaDTO = $objAssinaturaRN->listarRN1323($objAssinaturaDTO);
   	  				
   	  				if(is_array($arrObjAssinaturaDTO) && count($arrObjAssinaturaDTO) > 0) {
   	  					
   	  					$objAssinaturaDTO = $arrObjAssinaturaDTO[0];
   	  					
   	  					if($objAssinaturaDTO != null && $objAssinaturaDTO->isSetDthAberturaAtividade()){
   	  						 
   	  						$dtaAssinatura =  substr($objAssinaturaDTO->getDthAberturaAtividade(),0,10);
   	  						 
   	  						$objProtocoloPesquisaPublicaDTO->setDtaRegistro($dtaAssinatura);
   	  						$objProtocoloPesquisaPublicaDTO->setDtaDocumento($dtaAssinatura);
   	  						 
   	  					}else{
   	  						$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objDocumentoDTO->getDtaGeracaoProtocolo());
   	  						$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objDocumentoDTO->getDtaGeracaoProtocolo());
   	  					}
   	  				}else{
   	  					
   	  					$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objDocumentoDTO->getDtaGeracaoProtocolo());
   	  					$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objDocumentoDTO->getDtaGeracaoProtocolo());
   	  				}

   	  		}
   	  		
   	  		$objProtocoloPesquisaPublicaDTO->setStrUnidade($objDocumentoDTO->getStrSiglaUnidadeGeradoraProtocolo());
   	  		$objProtocoloPesquisaPublicaDTO->setStrStaAssociacao($objRelProtocoloProtocoloDTO->getStrStaAssociacao());
   	  		$objProtocoloPesquisaPublicaDTO->setObjDocumentoDTO($objDocumentoDTO);
   	  		
   	  	
   	  		$arrObjProtocoloPesquisaPublicaDTO[] = $objProtocoloPesquisaPublicaDTO;
   	  		$numProtocolos++;
   	  	}
    }else if ($objRelProtocoloProtocoloDTO->getStrStaAssociacao()==RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO){

      	$objProcedimentoDTOAnexado = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();
      	
      	$objProtocoloPesquisaPublicaDTO = new MdPesqProtocoloPesquisaPublicaDTO();
      	$objProtocoloPesquisaPublicaDTO->setStrNumeroSEI($objProcedimentoDTOAnexado->getStrProtocoloProcedimentoFormatado());
      	$objProtocoloPesquisaPublicaDTO->setStrTipoDocumento(PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTOAnexado->getStrNomeTipoProcedimento()));
      	$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objProcedimentoDTOAnexado->getDtaGeracaoProtocolo());
      	$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objProcedimentoDTOAnexado->getDtaGeracaoProtocolo());
      	$objProtocoloPesquisaPublicaDTO->setStrUnidade($objProcedimentoDTOAnexado->getStrSiglaUnidadeGeradoraProtocolo());
      	$objProtocoloPesquisaPublicaDTO->setStrStaAssociacao($objRelProtocoloProtocoloDTO->getStrStaAssociacao());
      	$objProtocoloPesquisaPublicaDTO->setObjProcedimentoDTO($objProcedimentoDTOAnexado);
      	
      	$arrObjProtocoloPesquisaPublicaDTO[] = $objProtocoloPesquisaPublicaDTO;
      
      
      	$numProtocolos++;
    }
  }
  
  if ($numProtocolos > 0){
  	
   	$strResultado = '<table id="tblDocumentos" width="99.3%" class="infraTable" summary="Lista de Documentos" >
  					  									<caption class="infraCaption" >'.PaginaSEIExterna::getInstance()->gerarCaptionTabela("Protocolos",$numProtocolos).'</caption> 
  					 									<tr>
                                                            <th class="infraTh no-print" width="1%">'.$strThCheck.'</th>  					  									    
  					  										<th class="infraTh" width="15%">Processo / Documento</th> 
                                                            <th class="infraTh" width="15%">Tipo</th>
                                                            <th class="infraTh" width="15%">Data</th>
                                                            <th class="infraTh" width="15%">Data de Inclusão</th>
                                                            <th class="infraTh" width="15%">Unidade</th>
  					  									</tr>';
   	
   	//Monta tabela documentos
   	foreach ($arrObjProtocoloPesquisaPublicaDTO as $objProtocoloPesquisaPublicaDTO){
   		 
   		if($objProtocoloPesquisaPublicaDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO){
   	
   			$objDocumentoDTO = $objProtocoloPesquisaPublicaDTO->getObjDocumentoDTO();
   			$urlCripografadaDocumeto = MdPesqCriptografia::criptografa('acao_externa=documento_exibir&id_documento='.$objDocumentoDTO->getDblIdDocumento().'&id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo']);
   			$strLinkDocumento = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('md_pesq_documento_consulta_externa.php?'.$urlCripografadaDocumeto));

			//Protege acesso à documento público de intimação eletrônica
			$bolValidaIntimacaoEletronica = true;

			$objInfraParametroDTO = $objParametroPesquisaRN->consultarVersaoPeticionamento('4.0.2');
			if( !is_null($objInfraParametroDTO) ){
				$objMdPetIntCertidaoRN =  new MdPetIntCertidaoRN();
				if( !$objMdPetIntCertidaoRN->verificaDocumentoEAnexoIntimacaoNaoCumprida( array($objDocumentoDTO->getDblIdDocumento(),false,false,true) ) ){
					$bolValidaIntimacaoEletronica = false;	
				}
			}

			$strResultado .= '<tr class="infraTrClara">';

   			//Cria checkbox para gerar PDF, verifica se o Processo e publico e o Acesso Local do Protocolo e Publico
			if($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO && $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO){
				if($objDocumentoRN->verificarSelecaoGeracaoPdf($objDocumentoDTO) && $bolValidaIntimacaoEletronica){

                    $dtaCorteDoc = $objDocumentoDTO->getDtaInclusaoProtocolo();

                    if($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO && in_array($objDocumentoDTO->getStrStaDocumento(), [DocumentoRN::$TD_EDITOR_INTERNO, DocumentoRN::$TD_FORMULARIO_GERADO])){
                        $dtaCorteDoc = $objProtocoloPesquisaPublicaDTO->getDtaDocumento();
                    }

				    if($dtaCortePesquisa && $dtaCortePesquisa > date('Y-m-d', strtotime(str_replace('/', '-', $dtaCorteDoc)))) {
                        $strResultado .= '<td class="no-print">&nbsp;</td>';
				    }else{
                        $strResultado .= '<td align="center" class="no-print">'.PaginaSEIExterna::getInstance()->getTrCheck($numDocumentosPdf++, $objDocumentoDTO->getDblIdDocumento(), $objDocumentoDTO->getStrNomeSerie()).'</td>';
                    }
				}else{
   					$strResultado .= '<td class="no-print">&nbsp;</td>';
   				}
   			}else{
   				$strResultado .= '<td class="no-print">&nbsp;</td>';
   			}
   			
   			//Exibe link de documentos com nivel de acesso local Publico de processo publico
			if($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO && $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO ){

			    if(!$bolValidaIntimacaoEletronica){
                    $strResultado .= '<td align="center"><span class="retiraAncoraPadraoAzul">'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'</span>';
                    $strResultado .= '<img src="/infra_css/imagens/espaco.gif">';
                    $strResultado .= '<img src="../peticionamento/imagens/svg/intimacao_nao_cumprida_doc_anexo.svg" style="vertical-align: middle; width: 19px; margin-top: -3px;" title="Acesso Restrito. &#13'.'Provisoriamente em razão de Intimação Eletrônica ainda não cumprida">';
                }else{

			        $dtaCorteDoc = $objDocumentoDTO->getDtaInclusaoProtocolo();

                    if($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO && in_array($objDocumentoDTO->getStrStaDocumento(), [DocumentoRN::$TD_EDITOR_INTERNO, DocumentoRN::$TD_FORMULARIO_GERADO])){
                        $dtaCorteDoc = $objProtocoloPesquisaPublicaDTO->getDtaDocumento();
                    }

			        if($dtaCortePesquisa && $dtaCortePesquisa > date('Y-m-d', strtotime(str_replace('/', '-', $dtaCorteDoc)))){
                        $strResultado .= '<td align="center"><span class="retiraAncoraPadraoAzul">'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'</span>';
                        $strResultado .= '<img src="/infra_css/imagens/espaco.gif">';
                        $strResultado .= '<img src="../pesquisa/imagens/sei_chave_documento_restrito.svg" data-indicador="bbb" style="vertical-align: middle; width: 24px; margin-top: -3px;" title="Acesso Restrito. &#13'.'Provisoriamente em razão de necessidade de reclassificação de nível de acesso">';
                    }else{
                        $strResultado .= '<td align="center" style="padding-right:22px"><a href="javascript:void(0);" onclick="window.open(\''.$strLinkDocumento.'\');" alt="'.PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie()).'" title="'.PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie()).'" class="ancoraPadraoAzul">'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'</a></td>';
                    }
                }

			}else{
				if($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_RESTRITO){
   					
   					//necessario para retornar id hipotese legal do documento
   					$strHipoteseLegalDocumento = '';
   					$objProtocoloDocumentoDTO = new ProtocoloDTO();
   					$objProtocoloDocumentoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
   					$objProtocoloDocumentoDTO->retNumIdHipoteseLegal();
   					
   					$objProtocoloRN = new ProtocoloRN();
   					$objProtocoloDocumentoDTO = $objProtocoloRN->consultarRN0186($objProtocoloDocumentoDTO);
   					
   					if($objProtocoloDocumentoDTO != null){
   						
   						$objHipoteseLegaDocumentoDTO = new HipoteseLegalDTO();
   						$objHipoteseLegaDocumentoDTO->setNumIdHipoteseLegal($objProtocoloDocumentoDTO->getNumIdHipoteseLegal());
   						$objHipoteseLegaDocumentoDTO->retStrNome();
   						$objHipoteseLegaDocumentoDTO->retStrBaseLegal();
   						
   						$objHipoteseLegalRN = new HipoteseLegalRN();
   						$objHipoteseLegaDocumentoDTO = $objHipoteseLegalRN->consultar($objHipoteseLegaDocumentoDTO);
   						
   						
   						if($objHipoteseLegaDocumentoDTO != null){
   								
   							$strHipoteseLegalDocumento .= $objHipoteseLegaDocumentoDTO->getStrNome().' ('.$objHipoteseLegaDocumentoDTO->getStrBaseLegal().')';
   						}
   					}
   					   					
   					$strResultado .= '<td align="center" ><span class="retiraAncoraPadraoAzul">'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'</span>';
   					$strResultado .= '<img src="/infra_css/imagens/espaco.gif">';
   					$strResultado .= '<img src="imagens/sei_chave_restrito.svg" style="vertical-align: middle;" title="Acesso Restrito. &#13'.PaginaSEIExterna::getInstance()->formatarXHTML($strHipoteseLegalDocumento).'">';
   					$strResultado .= '</td>';
   					
   				}else{
   					$strResultado .= '<td align="center" style="padding-right:22px" ><span class="retiraAncoraPadraoAzul">'.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().'</span>';
   				}
   			}
   			
   			$strResultado .= '<td align="center">'.PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrNumero()).'</td>';
            $strResultado .= '<td align="center">'.$objProtocoloPesquisaPublicaDTO->getDtaDocumento().'</td>';
            if($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO && in_array($objDocumentoDTO->getStrStaDocumento(), [DocumentoRN::$TD_EDITOR_INTERNO, DocumentoRN::$TD_FORMULARIO_GERADO])){
                $strResultado .= '<td align="center">'.$objProtocoloPesquisaPublicaDTO->getDtaDocumento().'</td>';
            }else{
                $strResultado .= '<td align="center">'.$objDocumentoDTO->getDtaInclusaoProtocolo().'</td>';
            }
            $strResultado .= '<td align="center"><a alt="'.$objDocumentoDTO->getStrDescricaoUnidadeGeradoraProtocolo().'" title="'.$objDocumentoDTO->getStrDescricaoUnidadeGeradoraProtocolo().'" class="ancoraSigla">'.$objDocumentoDTO->getStrSiglaUnidadeGeradoraProtocolo().'</a></td>';
            $strResultado .= '<td align="center" class="colunaAcoes">';
   				
   			$strResultado .='</td></tr>';
   	
   		}else if($objProtocoloPesquisaPublicaDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO){
   	
   			
   			$strResultado .= '<tr class="infraTrClara">';
   			$strResultado .= '<td class="no-print">&nbsp;</td>';
   			$strHipoteseLegalAnexo = '';
   			$strProtocoloRestrito = '';
   			
   			$objProcedimentoDTOAnexado = $objProtocoloPesquisaPublicaDTO->getObjProcedimentoDTO();
   			
   			//Cria indicacao de acesso restrito com hipotese legal
   			if($objProcedimentoDTOAnexado->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_RESTRITO){
   					
   				$strHipoteseLegalAnexo = '';
   				$objProtocoloAnexoDTO = new ProtocoloDTO();
   				$objProtocoloAnexoDTO->setDblIdProtocolo($objProcedimentoDTOAnexado->getDblIdProcedimento());
   				$objProtocoloAnexoDTO->retNumIdHipoteseLegal();
   					
   				$objProtocoloRN = new ProtocoloRN();
   				$objProtocoloAnexoDTO = $objProtocoloRN->consultarRN0186($objProtocoloAnexoDTO);
   					
   				if($objProtocoloAnexoDTO != null){
   			
   					$objHipoteseLegaAnexoDTO = new HipoteseLegalDTO();
   					$objHipoteseLegaAnexoDTO->setNumIdHipoteseLegal($objProtocoloAnexoDTO->getNumIdHipoteseLegal());
   					$objHipoteseLegaAnexoDTO->retStrNome();
   					$objHipoteseLegaAnexoDTO->retStrBaseLegal();
   			
   					$objHipoteseLegalRN = new HipoteseLegalRN();
   					$objHipoteseLegaDocumentoDTO = $objHipoteseLegalRN->consultar($objHipoteseLegaAnexoDTO);
   			
   			
   					if($objHipoteseLegaDocumentoDTO != null){
   							
   						$strHipoteseLegalAnexo .= $objHipoteseLegaDocumentoDTO->getStrNome().' ('.$objHipoteseLegaDocumentoDTO->getStrBaseLegal().')';
   					}
   				}
   				$strProtocoloRestrito .= '<img src="imagens/sei_chave_restrito.svg" style="vertical-align: middle;" title="Acesso Restrito. &#13'.$strHipoteseLegalAnexo.'">';
   			}
   			
   			if($objProcedimentoDTOAnexado->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO && $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO ){
   				$parametrosCriptografadosProcesso = MdPesqCriptografia::criptografa('id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo'].'&id_procedimento='.$objProcedimentoDTOAnexado->getDblIdProcedimento());
   				$urlPesquisaProcesso = 'md_pesq_processo_exibir.php?'.$parametrosCriptografadosProcesso;
   				
   				// $strLinkProcessoAnexado = PaginaSEIExterna::getInstance()->formatarXHTML(SessaoSEIExterna::getInstance()->assinarLink('processo_acesso_externo_consulta.php?id_acesso_externo='.$_GET['id_acesso_externo'].'&id_acesso_externo_assinatura='.$_GET['id_acesso_externo_assinatura'].'&id_procedimento_anexado='.$objProcedimentoDTOAnexado->getDblIdProcedimento()));
   					
   				$strLinkProcessoAnexado = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink(MdPesqSolrUtilExterno::prepararUrl($urlPesquisaProcesso)));
                $strResultado .= '<td align="center"><a href="javascript:void(0);" onclick="window.open(\''.$strLinkProcessoAnexado.'\');" alt="'.$objProcedimentoDTOAnexado->getStrNomeTipoProcedimento().'" title="'.$objProcedimentoDTOAnexado->getStrNomeTipoProcedimento().'" class="ancoraPadraoAzul">'.$objProcedimentoDTOAnexado->getStrProtocoloProcedimentoFormatado().'</a>'.$strProtocoloRestrito.'</td>';
   				
   			}else{
   				
   				$strResultado .= '<td align="center" style="padding-right:22px" ><span class="retiraAncoraPadraoAzul">'.PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTOAnexado->getStrProtocoloProcedimentoFormatado()).' </span>'.$strProtocoloRestrito.'</td>';
   						
   			}
   			
  			$strResultado .= '<td align="center">'.PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTOAnexado->getStrNomeTipoProcedimento()).'</td>';
  			$strResultado .= '<td align="center">'.$objProtocoloPesquisaPublicaDTO->getDtaDocumento().'</td>';
            $strResultado .= '<td align="center">'.$objProtocoloPesquisaPublicaDTO->getDtaRegistro().'</td>';
  			$strResultado .= '<td align="center"><a alt="'.$objProcedimentoDTOAnexado->getStrDescricaoUnidadeGeradoraProtocolo().'" title="'.$objProcedimentoDTOAnexado->getStrDescricaoUnidadeGeradoraProtocolo().'" class="ancoraSigla">'.$objProcedimentoDTOAnexado->getStrSiglaUnidadeGeradoraProtocolo().'</a></td>';
            $strResultado .= '<td align="center" class="colunaAcoes">&nbsp;</td>';
   			$strResultado .= '</tr>';
   	
   		}
   	}
   	$strResultado.='</table>';
  									
	}	

	$arrComandos = array();
	if ($numDocumentosPdf > 0){
		if($bolCaptchaGerarPdf){
			$strComando = '<button type="button" accesskey="G" name="btnGerarPdfModal" value="Gerar PDF" onclick="gerarPdfModal();" class="infraButton"><span class="infraTeclaAtalho">G</span>erar PDF</button>';
		}else{
			$strComando = '<button type="button" accesskey="G" name="btnGerarPdfModal" value="Gerar PDF" onclick="gerarPdf();" class="infraButton"><span class="infraTeclaAtalho">G</span>erar PDF</button>';
		}
		
		$arrComandos[] = $strComando;
		
	}

	if ($bolAcaoImprimir = true){
		$arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirDiv(\'divInfraAreaTelaD\');" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
	}
 	
 	//Carregar historico
 
  $numRegistrosAtividades = 0;

  
  if(($bolListaAndamentoProcessoPublico && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_PUBLICO) || 
  	 ($bolListaAndamentoProcessoRestrito && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO) ){
  
  	$objProcedimentoHistoricoDTO = new ProcedimentoHistoricoDTO();
  	$objProcedimentoHistoricoDTO->setDblIdProcedimento($dblIdProcedimento);
  	$objProcedimentoHistoricoDTO->setStrStaHistorico(ProcedimentoRN::$TH_EXTERNO);
  	$objProcedimentoHistoricoDTO->setStrSinGerarLinksHistorico('N');
  	 
  	$objProcedimentoRN = new ProcedimentoRN();
  	$objProcedimentoDTORet = $objProcedimentoRN->consultarHistoricoRN1025($objProcedimentoHistoricoDTO);
  	$arrObjAtividadeDTO = $objProcedimentoDTORet->getArrObjAtividadeDTO();
  	 
  	$numRegistrosAtividades = count($arrObjAtividadeDTO);
  }

  if ($numRegistrosAtividades > 0){
    
    $bolCheck = false;

    $strResultadoAndamentos = '';

    $strResultadoAndamentos .= '<table id="tblHistorico" width="99.3%" class="infraTable" summary="Histórico de Andamentos">'."\n";    
    $strResultadoAndamentos .= '<caption class="infraCaption">'.PaginaSEIExterna::getInstance()->gerarCaptionTabela('Andamentos',$numRegistrosAtividades).'</caption>';
		$strResultadoAndamentos .= '<tr>';
		$strResultadoAndamentos .= '<th class="infraTh" width="20%">Data/Hora</th>';
		$strResultadoAndamentos .= '<th class="infraTh" width="10%">Unidade</th>';
		$strResultadoAndamentos .= '<th class="infraTh">Descrição</th>';
		$strResultadoAndamentos .= '</tr>'."\n";					

		$strQuebraLinha = '<span style="line-height:.5em"><br /></span>';
		 
		
    foreach($arrObjAtividadeDTO as $objAtividadeDTO){    	
        
        //InfraDebug::getInstance()->gravar($objAtividadeDTO->getNumIdAtividade());
      
        $strResultadoAndamentos .= "\n\n".'<!-- '.$objAtividadeDTO->getNumIdAtividade().' -->'."\n";
        
				if ($objAtividadeDTO->getStrSinUltimaUnidadeHistorico() == 'S'){		
					$strAbertas = 'class="andamentoAberto"';
				}else{
					$strAbertas = 'class="andamentoConcluido"';
				}	
				
				$strResultadoAndamentos .= '<tr '.$strAbertas.'>';		
				$strResultadoAndamentos .= "\n".'<td align="center">';
			  $strResultadoAndamentos .= substr($objAtividadeDTO->getDthAbertura(),0,16);
				$strResultadoAndamentos .= '</td>';
				
				$strResultadoAndamentos .= "\n".'<td align="center">';
			  $strResultadoAndamentos .= '<a alt="'.$objAtividadeDTO->getStrDescricaoUnidade().'" title="'.$objAtividadeDTO->getStrDescricaoUnidade().'" class="ancoraSigla">'.$objAtividadeDTO->getStrSiglaUnidade().'</a>';
				$strResultadoAndamentos .= '</td>';
				
				$strResultadoAndamentos .= "\n";
			  $strResultadoAndamentos .= "\n".'<td>';

				if (!InfraString::isBolVazia($objAtividadeDTO->getStrNomeTarefa())){
					$strResultadoAndamentos .= nl2br($objAtividadeDTO->getStrNomeTarefa()).$strQuebraLinha;
				}
					
				$strResultadoAndamentos .= '</td>';
					
				$strResultadoAndamentos .= '</tr>';				
  	}
    $strResultadoAndamentos .= '</table><br />';
  }
  
  
  
  AuditoriaSEI::getInstance()->auditar('processo_consulta_externa', __FILE__, strip_tags($strResultadoCabecalho)."\n".strip_tags($strResultado));
  
  if ($_POST['hdnFlagGerar']=='1'){

        if ($bolCaptchaGerarPdf == true && mb_strtoupper($_POST['txtInfraCaptcha']) != mb_strtoupper($_SESSION['INFRA_CAPTCHA_V2_'.$_POST['hdnCId']])) {

  			PaginaSEIExterna::getInstance()->setStrMensagem('Código de confirmação inválido.', PaginaSEI::$TIPO_MSG_ERRO);

        }else {

			$objDocumentoRN = new DocumentoRN();

  			$parArrObjDocumentoDTO = InfraArray::converterArrInfraDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',PaginaSEIExterna::getInstance()->getArrStrItensSelecionados()),'IdDocumento');
  			$objDocumentoDTO = new DocumentoDTO();
  			$objDocumentoDTO->retDblIdDocumento();
  			$objDocumentoDTO->setDblIdDocumento($parArrObjDocumentoDTO, InfraDTO::$OPER_IN);
  			$objDocumentoDTO->retDblIdProcedimento();
  			$objDocumentoDTO->retStrStaNivelAcessoGlobalProtocolo();
  			$objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
			$arrObjDocumentoDTO = $objDocumentoRN->listarRN0008($objDocumentoDTO);

			$arrSelecionados = PaginaSEIExterna::getInstance()->getArrStrItensSelecionados();

			foreach ($arrObjDocumentoDTO as $objDocumentoDTO){

  				//Alterardo para atender o pedido da anatel para gerar pdf de documentos de nivel de acesso local = Público e de Procedimentos Públicos mesmo se o nivel global for restrito
  				if($bolListaDocumentoProcessoRestrito){
  					if($objDocumentoDTO->getDblIdProcedimento() != $dblIdProcedimento || $objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() != ProtocoloRN::$NA_PUBLICO || $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() != ProtocoloRN::$NA_PUBLICO){
  						die ("Erro ao Gerar Pdf");
  					}

					//Protege acesso à documento público de intimação eletrônica
					if( PesquisaIntegracao::verificaSeModPeticionamentoVersaoMinima() ){
						$objMdPetIntCertidaoRN =  new MdPetIntCertidaoRN();
						if( !$objMdPetIntCertidaoRN->verificaDocumentoEAnexoIntimacaoNaoCumprida( array($objDocumentoDTO->getDblIdDocumento(),false,false,true) ) ){
							$idx_documento = array_search($objDocumentoDTO->getDblIdDocumento(), $arrSelecionados);
							unset($arrSelecionados[$idx_documento]);
						}
					}
				}else if($bolListaDocumentoProcessoPublico){
					if($objDocumentoDTO->getDblIdProcedimento() != $dblIdProcedimento || $objDocumentoDTO->getStrStaNivelAcessoGlobalProtocolo() != ProtocoloRN::$NA_PUBLICO){
  						die ("Erro ao Gerar Pdf");
  					}
  				}else{
  					die ("Erro ao Gerar Pdf");
  				}
  				
			}

			if (count($arrSelecionados)==0){
				die ("Sem documento a gerar");
			}

			$arrSelecionados = array_values($arrSelecionados);

			$objDocumentoRN = new DocumentoRN();
			$objAnexoDTO = $objDocumentoRN->gerarPdf(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrSelecionados));

			$bolGeracaoOK = true;
  		
  		}
  
  }
  
}catch(Exception $e){
  PaginaSEIExterna::getInstance()->processarExcecao($e);
}

$strTitulo = 'Pesquisa Processual';
$identificadorFormatado = strtoupper(str_replace(" ", "_", InfraString::excluirAcentos($strTitulo.round(microtime(true)*1000))));
CaptchaSEI::getInstance()->configurarCaptcha($identificadorFormatado);
 

PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(':: '.PaginaSEIExterna::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::');
PaginaSEIExterna::getInstance()->montarStyle();
CaptchaSEI::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
echo $strCssMostrarAcoes;
?>

@media print {
    .infraBarraComandos, .infraBarraComandos *, .no-print, .no-print * {
        display: none !important;
    }
}

div.infraBarraSistemaE {width:90%}
div.infraBarraSistemaD {width:5%}
div.infraBarraComandos {width:99%}

table caption {
  text-align:left !important;
  font-size: 1.2em;
  font-weight:bold;
}

.andamentoAberto {
  background-color:white;
}

.andamentoConcluido {
  background-color:white;
}


#tblCabecalho{margin-top:1;}
#tblDocumentos {margin-top:1.5em;}
#tblHistorico {margin-top:1.5em;}

<? if($bolCaptchaGerarPdf) { ?>

#divInfraModal{
	
	display: none; 
    position: fixed; 
    z-index: 1; 
    padding-top: 100px; 
    left: 0;
    top: 0;
    width: 50%; 
    height: 50%; 
    overflow: auto;
   
}

.close {
    color: white;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

.modal-header {
    padding: 2px 16px;
    background-image: url("imagens/bg_barra_sistema.jpg");
    color: white;
}
    div {
    margin: 0 auto 0 auto;
    }
.modal-body {padding: 2px 16px;}

.modal-footer {
    padding: 2px 16px;
    background-color: #5cb85c;
    color: white;
}
<? } ?>

span.retiraAncoraPadraoAzul{font-size: .875rem;}

<?
PaginaSEIExterna::getInstance()->fecharStyle();
PaginaSEIExterna::getInstance()->montarJavaScript();
CaptchaSEI::getInstance()->montarJavascript();
PaginaSEIExterna::getInstance()->abrirJavaScript();
?>

function inicializar(){

  <?if ($bolGeracaoOK){?>
    
  	window.open('<?=SessaoSEI::getInstance()->assinarLink('md_pesq_processo_exibe_arquivo.php?'.MdPesqCriptografia::criptografa('acao_externa=usuario_externo_exibir_arquivo&acao_origem_externa=protocolo_pesquisar&id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo'].'&nome_arquivo='.$objAnexoDTO->getStrNome().'&nome_download=SEI-'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'.pdf'));?>');
  	
  <?}?>

  infraEfeitoTabelas();
}


<?
if($bolCaptchaGerarPdf){ 
?>

$(document).unbind("keyup").keyup(function(e){
	e.preventDefault();
    var code = e.which;
    if(code==13){
    	var modal = document.getElementById('divInfraModal');
        if(modal.style.display == "block"){
        	fecharPdfModal();
        	gerarPdf();
    		
    	}
    }
});

function gerarPdfModal(){

	if (document.getElementById('hdnInfraItensSelecionados').value==''){
    	alert('Nenhum documento selecionado.');
    	return;
  	}

    document.getElementById('divInfraModal').style.display = "block";
    document.getElementById('txtInfraCaptcha').focus();

}

function fecharPdfModal(){
	
    var modal = document.getElementById('divInfraModal');
	modal.style.display = "none";
}

window.onclick = function(event) {
	var modal = document.getElementById('divInfraModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

<?
}
?>

function gerarPdf() {
    if (document.getElementById('hdnInfraItensSelecionados').value == ''){
        alert('Nenhum documento selecionado.');
        return false;
    }

<? if($bolCaptchaGerarPdf): ?>

    if (document.getElementById('txtInfraCaptcha').value.length != 6){
        $('.modal-alert-msg').html('<p class="alert alert-warning">Informe o código de confirmação!</p>');
        document.getElementById('txtInfraCaptcha').focus();
        return false;
    }

    if(document.getElementById('hdnInfraItensSelecionados').value != '' && document.getElementById('txtInfraCaptcha').value.length == 6){
        fecharPdfModal();
        infraExibirAviso(false);
        document.getElementById('hdnFlagGerar').value = '1';
        document.getElementById('frmProcessoAcessoExternoConsulta').submit();
    }
<? else: ?>

    document.getElementById('hdnFlagGerar').value = '1';
    document.getElementById('frmProcessoAcessoExternoConsulta').submit();

<? endif; ?>
}

<?
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>

<form id="frmProcessoAcessoExternoConsulta" method="post">

<? if($bolCaptchaGerarPdf): ?>
    <div id="divInfraModal" class="infraFundoTransparente" style="position: fixed; width: 100%; height: 100%; visibility: visible; background-image: none; background: rgba(0,0,0,.2); z-index: 9999">
       <div id="divCaptcha"  class="infraAreaDados" style="margin: auto; position: absolute; top: -200px; left: 0; bottom: 0; right: 0; height: 280px; width: 400px; background-color: white; box-shadow: 0px 5px 10px rgba(0,0,0,.3)">
            <div class="modal-header d-flex justify-content-end align-items-center" style="background: #155f9b; border-radius: 0px; padding: 5px 10px;">
                <img title="Fechar janela" onmouseover="this.src=INFRA_PATH_JS + '/modal/fechar_vermelho.png'" onmouseout="this.src=INFRA_PATH_JS + '/modal/fechar_branco.png'" src="/infra_js/modal/fechar_branco.png" onclick="fecharPdfModal();" tabindex="1" width="24">
            </div>
            <div class="modal-body p-4 text-center">
                <div class="row">
                    <div class="col-12">
                        <h6 class="font-weight-bold text-center">Digite o código da imagem:</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <div>
	                            <? CaptchaSEI::getInstance()->montarHtml(PaginaSEIExterna::getInstance()->getProxTabDados())?><br>
                                <input type="hidden" id="hdnCId" name="hdnCId" class="infraText" value="<?= $identificadorFormatado; ?>"/>
                            </div>
                        </div>
                        <div class="text-center">
                            <button id="btnEnviarCaptcha" type="button" accesskey="G" name="btnEnviarCaptcha" value="Gerar PDF" onclick="gerarPdf();" class="infraButton" style="width: 137px"><span class="infraTeclaAtalho">G</span>erar PDF</button>
                        </div>
                    </div>
                </div>
                <div class="modal-alert-msg mt-2"></div>
            </div>
        </div>
    </div>
<? endif; ?>

<? PaginaSEIExterna::getInstance()->montarBarraComandosSuperior($arrComandos);
echo $strResultadoCabecalho;
echo $strMensagemProcessoRestrito;
PaginaSEIExterna::getInstance()->montarAreaTabela($strResultado,$numProtocolos);
echo $strResultadoAndamentos;
?>
<input type="hidden" id="hdnFlagGerar" name="hdnFlagGerar" value="0" />
</form>
<?
PaginaSEIExterna::getInstance()->montarAreaDebug();

if($bolGeracaoOK){
	?>
	<script>
	if (navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ){
	
		window.open('<?=SessaoSEI::getInstance()->assinarLink('md_pesq_processo_exibe_arquivo.php?'.MdPesqCriptografia::criptografa('acao_externa=usuario_externo_exibir_arquivo&acao_origem_externa=protocolo_pesquisar&id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo'].'&nome_arquivo='.$objAnexoDTO->getStrNome().'&nome_download=SEI-'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'.pdf'));?>');
			<?
					if($bolCaptchaGerarPdf){ 
					?>

					$(document).unbind("keyup").keyup(function(e){
						e.preventDefault();
					    var code = e.which;
					    if(code==13){
					    	var modal = document.getElementById('divInfraModal');
					        if(modal.style.display == "block"){
					        	fecharPdfModal();
					        	gerarPdf();
					    	}
					    }
					});

					function gerarPdfModal(){

						if (document.getElementById('hdnInfraItensSelecionados').value==''){
					    	alert('Nenhum documento selecionado.');
					    	return;
					  	}
						var modal = document.getElementById('divInfraModal');
						modal.style.display = "block";

					}

					function fecharPdfModal(){
						
					    var modal = document.getElementById('divInfraModal');
						modal.style.display = "none";
					}

					window.onclick = function(event) {
						var modal = document.getElementById('divInfraModal');
					    if (event.target == modal) {
					        modal.style.display = "none";
					    }
					}

					<?
					}
					?>

					function gerarPdf() {

					  if (document.getElementById('hdnInfraItensSelecionados').value==''){
					    alert('Nenhum documento selecionado.');
					    return;
					  }
					  
					  <?
						if($bolCaptchaGerarPdf){ 
					  ?>
					  	fecharPdfModal();
					  <?
						}
					  ?>

					  infraExibirAviso(false);
					  
					 document.getElementById('hdnFlagGerar').value = '1';
					 document.getElementById('frmProcessoAcessoExternoConsulta').submit();
					}
	}
	</script>
<?
}
?>

<?
PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>

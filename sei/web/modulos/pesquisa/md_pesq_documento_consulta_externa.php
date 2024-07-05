<?
/**
* CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
* 03/10/2014
*
*/

try {
	
	require_once dirname(__FILE__).'/../../SEI.php';
	require_once("MdPesqConverteURI.php");
	
	SessaoSEIExterna::getInstance()->validarSessao();
	
	//	InfraDebug::getInstance()->setBolLigado(false);
	//	InfraDebug::getInstance()->setBolDebugInfra(false);
	//	InfraDebug::getInstance()->limpar();
	
	MdPesqConverteURI::converterURI();
	MdPesqPesquisaUtil::valiadarLink();
	
	$objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
	$objParametroPesquisaDTO->retStrNome();
	$objParametroPesquisaDTO->retStrValor();
	
	$objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
	$arrObjParametroPesquisaDTO = $objParametroPesquisaRN->listar($objParametroPesquisaDTO);
	
	$arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO($arrObjParametroPesquisaDTO,'Valor','Nome');
	
	$bolListaDocumentoProcessoRestrito  = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
	$bolListaDocumentoProcessoPublico   = true; // Forcando para inutilizar o parametro
	$bolLinkMetadadosProcessoRestrito   = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_METADADOS_PROCESSO_RESTRITO] == 'S' ? true : false;
	
	$objDocumentoDTO = new DocumentoDTO();
	$objDocumentoDTO->retDblIdDocumento();
	$objDocumentoDTO->retDblIdProcedimento();
	$objDocumentoDTO->retStrConteudo();
	$objDocumentoDTO->retStrNomeSerie();
	$objDocumentoDTO->retStrStaDocumento();
	$objDocumentoDTO->retStrSiglaUnidadeGeradoraProtocolo();
	$objDocumentoDTO->retStrProtocoloDocumentoFormatado();
	$objDocumentoDTO->retStrStaProtocoloProtocolo();
	$objDocumentoDTO->retDblIdDocumentoEdoc();
	$objDocumentoDTO->retStrStaEstadoProtocolo();
	$objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
	$objDocumentoDTO->retStrStaNivelAcessoGlobalProtocolo();
	$objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
	$objDocumentoDTO->retDtaInclusaoProtocolo();
	$objDocumentoDTO->retDtaGeracaoProtocolo();
	$objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
	
	$objDocumentoRN = new DocumentoRN();
	$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
	
	if($objDocumentoDTO == null){
		die('Documento não encontrado.');
	}
	
	$isLocalPublico = $objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO;
	$isGlobalPublico = $objDocumentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_PUBLICO;
	
	if ((!$isGlobalPublico && !$bolLinkMetadadosProcessoRestrito) || (!$bolListaDocumentoProcessoPublico && $isLocalPublico)){
		die('Documento não encontrado.');
	}
	
	$dtaCorteDoc = $objDocumentoDTO->getDtaInclusaoProtocolo();
	
	if($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO && in_array($objDocumentoDTO->getStrStaDocumento(), [DocumentoRN::$TD_EDITOR_INTERNO, DocumentoRN::$TD_FORMULARIO_GERADO])){
	    $objAssinaturaDTO = new AssinaturaDTO();
	    $objAssinaturaDTO->retDthAberturaAtividade();
	    $objAssinaturaDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
	    $objAssinaturaDTO->setOrdNumIdAssinatura(InfraDTO::$TIPO_ORDENACAO_ASC);
	    $objAssinaturaDTO->setNumMaxRegistrosRetorno(1);
	    $arrObjAssinaturaDTO = (new AssinaturaRN())->listarRN1323($objAssinaturaDTO);
	
	    if (!empty($arrObjAssinaturaDTO)) {
	        if($arrObjAssinaturaDTO[0] != null && $arrObjAssinaturaDTO[0]->isSetDthAberturaAtividade()){
	            $dtaCorteDoc = substr($arrObjAssinaturaDTO[0]->getDthAberturaAtividade(),0,10);
	        }
	    }
	}
	
	$dtaCortePesquisa = (new MdPesqParametroPesquisaRN())->existeDataCortePesquisa();
	if($bolListaDocumentoProcessoPublico && $dtaCortePesquisa && $dtaCortePesquisa > date('Y-m-d', strtotime(str_replace('/', '-', $dtaCorteDoc)))){
	    die('Documento não encontrado');
	}
	
	$objProtocoloProcedimentoDTO = new ProtocoloDTO();
	$objProtocoloProcedimentoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdProcedimento());
	$objProtocoloProcedimentoDTO->retStrStaNivelAcessoLocal();
	$objProtocoloProcedimentoDTO->retStrStaNivelAcessoGlobal();
	
	$objProtocoloRN = new ProtocoloRN();
	$objProtocoloProcedimentoDTO = $objProtocoloRN->consultarRN0186($objProtocoloProcedimentoDTO);
	
	if($objProtocoloProcedimentoDTO != null && $objProtocoloProcedimentoDTO->getStrStaNivelAcessoLocal() != ProtocoloRN::$NA_PUBLICO){
		die('Documento não encontrado.');
	}
	
	if ($objDocumentoDTO->getStrStaEstadoProtocolo()==ProtocoloRN::$TE_DOCUMENTO_CANCELADO){
		die('Documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' foi cancelado.');
	}
	
	if(!$bolListaDocumentoProcessoRestrito){
		if ($objDocumentoDTO->getStrStaNivelAcessoGlobalProtocolo()!= ProtocoloRN::$NA_PUBLICO){
			die('Documento não encontrado.');
		}
	}
	
	if($bolListaDocumentoProcessoRestrito){
		if($objDocumentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO ||  $objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() != ProtocoloRN::$NA_PUBLICO){
			
			die('Documento não encontrado.');
			
		}
	}
	
	//Protege acesso a documento publico de intimacao eletronica
	if( PesquisaIntegracao::verificaSeModPeticionamentoVersaoMinima() ){
		$objMdPetIntCertidaoRN = new MdPetIntCertidaoRN();
		if( !$objMdPetIntCertidaoRN->verificaDocumentoEAnexoIntimacaoNaoCumprida( array($objDocumentoDTO->getDblIdDocumento(),false,false,true)) ){
			die("Documento com acesso restrito provisoriamente em razão de Intimação Eletrônica ainda não cumprida");
		}
	}
	
	//Exibe apenas documentos de processos publicos
	
	//Carregar dados do cabecalho
	$objProcedimentoDTO = new ProcedimentoDTO();
	$objProcedimentoDTO->setDblIdProcedimento($objDocumentoDTO->getDblIdProcedimento());
	$objProcedimentoDTO->setStrSinDocTodos('S');
	$objProcedimentoDTO->setArrDblIdProtocoloAssociado(array($objDocumentoDTO->getDblIdDocumento()));
	
	$objProcedimentoRN = new ProcedimentoRN();
	$arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);
	
	if (count($arr)==0){
		die('Processo não encontrado.');
	}
	
	$objAcessoExternoRN = new AcessoExternoRN();
	
	$objProcedimentoDTO = $arr[0];
	$bolFlag = false;
	foreach($objProcedimentoDTO->getArrObjDocumentoDTO() as $dto){
		if ($dto->getDblIdDocumento() == $objDocumentoDTO->getDblIdDocumento()){
			if (SessaoSEIExterna::getInstance()->getNumIdUsuarioExterno()==null || SessaoSEIExterna::getInstance()->getNumIdUsuarioExterno()==""){
				if (!$objDocumentoRN->verificarSelecaoAcessoBasico($dto)){
					if ($dto->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO && $dto->getStrSinAssinado()=='N'){
						die('Documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' ainda não foi assinado.');
					}else{
						die('Documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' não está disponível para visualização externa.');
					}
				}
			}else{
			
				if (!$objDocumentoRN->verificarSelecaoAcessoExterno($dto)){
				
					if ($dto->getStrStaProtocoloProtocolo()!=ProtocoloRN::$TP_DOCUMENTO_GERADO){
						die('Documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' não está disponível para visualização externa.');
					}else{
					
						$objAcessoExternoDTO = new AcessoExternoDTO();
						$objAcessoExternoDTO->setDblIdDocumento($_GET['id_documento']);
						$objAcessoExternoDTO->setNumIdAcessoExterno($_GET['id_acesso_externo_assinatura']);
						
						if ($objAcessoExternoRN->contar($objAcessoExternoDTO)==0){
						
							if ($dto->getStrSinAssinado()=='N'){
								die('Documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' ainda não foi assinado.');
							}else{
								die('Documento '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado().' não está disponível para assinatura externa.');
							}
						
						}
					}
				}
		
			}
			$bolFlag = true;
			break;
		}
	}

	if (!$bolFlag){
		die('Documento não encontrado no processo.');
	}
	
	$strTitulo = $objDocumentoDTO->getStrNomeSerie().' '.$objDocumentoDTO->getStrSiglaUnidadeGeradoraProtocolo().' '.$objDocumentoDTO->getStrProtocoloDocumentoFormatado();
	
	if ($objDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_EDOC){
		if ($objDocumentoDTO->getDblIdDocumentoEdoc()!=null){
	  
			$objDocumentoRN->bloquearAssinaturaVisualizada($objDocumentoDTO);
			
			//Verificar se precisa mesmo disso aqui sobre o EDocRN
			$objEDocRN = new EDocRN();
			echo $objEDocRN->consultarHTMLDocumentoRN1204($objDocumentoDTO);
		 
		}else{
		  echo 'Documento sem conteúdo.';
		}
	}else if ($objDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_INTERNO){
		$objEditorDTO = new EditorDTO();
		$objEditorDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
		$objEditorDTO->setNumIdBaseConhecimento(null);
		$objEditorDTO->setStrSinCabecalho('S');
		$objEditorDTO->setStrSinRodape('S');
		$objEditorDTO->setStrSinIdentificacaoVersao('N');
		$objEditorDTO->setStrSinCarimboPublicacao('S');
	
		$objEditorRN = new EditorRN();
		echo  $objEditorRN->consultarHtmlVersao($objEditorDTO);
	
	}else if (isset($_GET['id_anexo'])){
	
		$objDocumentoRN->bloquearAssinaturaVisualizada($objDocumentoDTO);
	
		$objAnexoDTO = new AnexoDTO();
		$objAnexoDTO->retNumIdAnexo();
		$objAnexoDTO->retStrNome();
		$objAnexoDTO->setNumIdAnexo($_GET['id_anexo']);
		$objAnexoDTO->retDthInclusao();
		
		$objAnexoRN = new AnexoRN();
		$objAnexoDTO = $objAnexoRN->consultarRN0736($objAnexoDTO);
		
		header("Pragma: public");
		header('Pragma: no-cache');
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private","false");
		
		PaginaSEIExterna::getInstance()->montarHeaderDownload($objAnexoDTO->getStrNome());
	
		$fp = fopen($objAnexoRN->obterLocalizacao($objAnexoDTO), "rb");
		while (!feof($fp)) {
		  echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
		}
		fclose($fp);
	
	
	}else if ($objDocumentoDTO->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){
	
		$objAnexoDTO = new AnexoDTO();
		$objAnexoDTO->retNumIdAnexo();
		$objAnexoDTO->retStrNome();
		$objAnexoDTO->retNumIdAnexo();
		$objAnexoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
		$objAnexoDTO->retDblIdProtocolo();
		$objAnexoDTO->retDthInclusao();
	
		$objAnexoRN = new AnexoRN();
		$arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);
	
		if (count($arrObjAnexoDTO)!=1){
			$strResultado = '';
		}else{
			$objAnexoDTO =  $arrObjAnexoDTO[0];
			
			header("Pragma: public");
			header('Pragma: no-cache');
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private","false");
			
			PaginaSEI::getInstance()->montarHeaderDownload($objAnexoDTO->getStrNome());
			
			$fp = fopen($objAnexoRN->obterLocalizacao($objAnexoDTO), "rb");
			while (!feof($fp)) {
				echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
			}
			fclose($fp);
		 
		}
	
	}else{
		echo '<html>';
		echo '<head>';
		echo '<title>:: '.PaginaSEIExterna::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::</title>';
		echo '</head>';
		echo '<body>';
		echo MdPesqDocumentoExternoINT::formatarExibicaoConteudo(DocumentoINT::$TV_HTML, $objDocumentoDTO->getStrConteudo(), PaginaSEIExterna::getInstance(), SessaoSEIExterna::getInstance(), '');
		echo '</body>';
		echo '</html>';
	}
	
	$objDocumentoDTO->unSetStrConteudo();
	AuditoriaSEI::getInstance()->auditar('documento_consulta_externa',__FILE__,$objDocumentoDTO);

}catch(Exception $e){
	try{ LogSEI::getInstance()->gravar(InfraException::inspecionar($e)); }catch(Exception $e2){}
	die('Erro consultando documento em acesso externo.');
}

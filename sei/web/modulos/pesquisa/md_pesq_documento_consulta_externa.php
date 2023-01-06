<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
*
* 03/10/2014 - criado por alex braga
*
* Versão do Gerador de Código:
*
* Versão no CVS:
*/

try {
  require_once dirname(__FILE__).'/../../SEI.php';
  require_once ("MdPesqConverteURI.php");

  SessaoSEIExterna::getInstance()->validarSessao();
  //session_start();
  
  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(false);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////
	MdPesqConverteURI::converterURI();
	MdPesqPesquisaUtil::valiadarLink();
//    if (isset($_GET['id_acesso_externo'])){
//      SessaoSEIExterna::getInstance($_GET['id_acesso_externo'])->validarLink();  
//    }else{
//     // SessaoSEIExterna::getInstance()->validarLink();
//    }

  $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
  $objParametroPesquisaDTO->retStrNome();
  $objParametroPesquisaDTO->retStrValor();
   	
  $objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
  $arrObjParametroPesquisaDTO = $objParametroPesquisaRN->listar($objParametroPesquisaDTO);
   	
  $arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO($arrObjParametroPesquisaDTO,'Valor','Nome');

   $bolListaDocumentoProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
   $bolListaDocumentoProcessoPublico = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_PUBLICO] == 'S' ? true : false;

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
  // $objDocumentoDTO->retStrSinAssinaturaBloqueada();
   $objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
   $objDocumentoDTO->retStrStaNivelAcessoGlobalProtocolo();
   $objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
   $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
  
   $objDocumentoRN = new DocumentoRN();
   $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
   
   

   if ($objDocumentoDTO==null){
     die('Documento não encontrado.');
   }
   
   if(!$bolListaDocumentoProcessoPublico){
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

	//Protege acesso à documento público de intimação eletrônica
	$objInfraParametroDTO = $objParametroPesquisaRN->consultarVersaoPeticionamento('2.0.0');
	if( !is_null($objInfraParametroDTO) ){
		$objMdPetIntCertidaoRN = new MdPetIntCertidaoRN();
		if( !$objMdPetIntCertidaoRN->verificaDocumentoEAnexoIntimacaoNaoCumprida( array($objDocumentoDTO->getDblIdDocumento(),false,false,true)) ){
			die("Documento com acesso restrito provisoriamente em razão de Intimação Eletrônica ainda não cumprida");
		}
	}
	
	// Exibe apenas documentos de processos públicos.
	
	//Carregar dados do cabeçalho
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
		  if (SessaoSEIExterna::getInstance()->getNumIdUsuarioExterno()==null){
  		  	if (!$objDocumentoRN->verificarSelecaoAcessoExterno($dto)){
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
      
      $objEDocRN = new EDocRN();
      //echo EDocINT::montarVisualizacaoDocumento($objDocumentoDTO->getDblIdDocumentoEdoc());
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
     
    header("Pragma: public"); // required
    header('Pragma: no-cache');
    header("Expires: 0");
    header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private","false"); // required for certain browsers
     
    PaginaSEIExterna::getInstance()->montarHeaderDownload($objAnexoDTO->getStrNome());
    
    $fp = fopen($objAnexoRN->obterLocalizacao($objAnexoDTO), "rb");
    while (!feof($fp)) {
      echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
    }
    fclose($fp);
    
    
  }else if ($objDocumentoDTO->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){
    
   // $objDocumentoRN->bloquearAssinaturaVisualizada($objDocumentoDTO);
    
    
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
    //echo DocumentoINT::formatarExibicaoConteudo(DocumentoINT::$TV_HTML, $objDocumentoDTO->getStrConteudo(), PaginaSEIExterna::getInstance(), SessaoSEIExterna::getInstance(), 'documento_consulta_externa.php?id_acesso_externo='.$_GET['id_acesso_externo'].'&id_documento='.$_GET['id_documento']);
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
?>
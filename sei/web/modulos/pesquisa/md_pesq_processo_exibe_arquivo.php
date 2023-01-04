<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONдMICA
 *
 *
**/

try {
   require_once dirname(__FILE__).'/../../SEI.php';

   SessaoSEIExterna::getInstance()->validarSessao();
   
   MdPesqConverteURI::converterURI();
   MdPesqPesquisaUtil::valiadarLink();
  
//	InfraDebug::getInstance()->setBolLigado(false);
//	InfraDebug::getInstance()->setBolDebugInfra(false);
//	InfraDebug::getInstance()->limpar();
  
  switch($_GET['acao_externa']){ 
  	  	
    case 'usuario_externo_exibir_arquivo':     
      
      AuditoriaSEI::getInstance()->auditar('usuario_externo_exibir_arquivo', __FILE__);
	  
      $infraParametroDTO = new InfraParametroDTO();
      $infraParametroDTO->setStrNome('SEI_VERSAO');
      $infraParametroDTO->retStrValor();
      
      $infraParametroRN = new InfraParametroRN();
      $infraParametroDTO = $infraParametroRN->consultar($infraParametroDTO);	
      $versaoSei = $infraParametroDTO->getStrValor();
      print_r($versaoSei);
      
      if($versaoSei == '2.5.1'){
      	
      	header("Pragma: public");
      	header('Pragma: no-cache');
      	header("Expires: 0");
      	header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
      	header("Cache-Control: private","false");
      	PaginaSEIExterna::getInstance()->montarHeaderDownload($_GET['nome_arquivo'],'attachment');
      	
      	$fp = fopen(dirname(__FILE__).'/../../'.DIR_UPLOAD.'/'.$_GET['nome_arquivo'], "rb");
      	while (!feof($fp)) {
      		echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
      	}
      	fclose($fp);
      	break;
      	
      }else{
      	header("Pragma: public");
      	header('Pragma: no-cache');
      	header("Expires: 0");
      	header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
      	header("Cache-Control: private","false");
      	$strNomeDownload=$_GET['nome_download'];
      	
      	if (InfraString::isBolVazia($strNomeDownload)){
      	$strNomeDownload=$_GET['nome_arquivo'];
      	}
      	 
      	PaginaSEI::getInstance()->montarHeaderDownload($strNomeDownload,'attachment');
      	 
      	$fp = fopen(DIR_SEI_TEMP.'/'.$_GET['nome_arquivo'], "rb");
      	while (!feof($fp)) {
      	echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
      	}
      	fclose($fp);
      	 
      	break;
      	
      }
     
    default:
      throw new InfraException("Aчуo '".$_GET['acao']."' nуo reconhecida.");
  }
  
}catch(Exception $e){
  try{ LogSEI::getInstance()->gravar(InfraException::inspecionar($e)."\n".'$_GET: '.print_r($_GET,true)); }catch(Exception $e2){}
  die('Erro exibindo arquivo em acesso externo.');
}
?>
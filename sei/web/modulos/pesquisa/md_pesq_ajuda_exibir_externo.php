<?
/*
* CONSELHO ADMINISTRATIVO DE DEFESA ECONФMICA - CADE
*
* 01/10/2014 - criado por alex braga
*
*
* Versгo do Gerador de Cуdigo:
*/
try {
  require_once dirname(__FILE__).'/../../SEI.php';
  
  //session_start(); 
  
  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////
      
  //SessaoSEIExterna::getInstance()->validarLink(); 
  
  MdPesqPesquisaUtil ::valiadarLink();
  
 // SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strNomeArquivo = '';
  
  switch($_GET['acao_externa']){ 
  	  	
    case 'pesquisa_solr_ajuda_externa':
      $strConteudo = file_get_contents('../../ajuda/ajuda_solr.html');
      break;

    case 'pesquisa_fts_ajuda_externa':
      $strConteudo = file_get_contents('../../ajuda/ajuda_fts.html');
      break;

    case 'assinatura_digital_ajuda_externa':
      $strConteudo = file_get_contents('../../ajuda/assinatura_digital_ajuda.html');
      $strConteudo = str_replace('[servidor]', ConfiguracaoSEI::getInstance()->getValor('SEI','URL'), $strConteudo);
      break;
      
    default:
      throw new InfraException("Aзгo '".$_GET['acao']."' nгo reconhecida.");
  }

  header('Content-Type: text/html; charset=iso-8859-1');
  header('Vary: Accept');
  header('Cache-Control: no-cache, must-revalidate');
  header('Pragma: no-cache');

  echo $strConteudo;                
  
}catch(Exception $e){
  die('Erro realizando download do anexo:'.$e->__toString());
}
?>
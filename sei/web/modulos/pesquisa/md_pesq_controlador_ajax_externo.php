<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-09-29
 * Versão do Gerador de Código: 1.0
 * Versão no CVS/SVN:
 *
 * sei
 * pesquisa
 * controlador_ajax_externo
 *
 *
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 */

/**
 * Arquivo para realizar controle requisição ajax.
 *
 *
 * @package institucional_pesquisa_controlador_ajax_externo
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

try{
  require_once dirname(__FILE__).'/../../SEI.php';

  //session_start();
  
	//SessaoSEIExterna::getInstance()->validarLink();

	//infraTratarErroFatal(SessaoSEIExterna::getInstance(),'controlador_externo.php?acao=infra_erro_fatal_logar');
  
  InfraAjax::decodificarPost();
  //Verificar
  SessaoSEIExterna::getInstance()->validarSessao();
  MdPesqPesquisaUtil::valiadarLink();
  
  
  switch($_GET['acao_ajax_externo']){
 	
  	case 'contato_auto_completar_contexto_pesquisa':
  		
  		//alterado para atender anatel exibir apenas nome contato
  		$objContatoDTO = new ContatoDTO();
  		$objContatoDTO->retNumIdContato();
  		$objContatoDTO->retStrSigla();
  		$objContatoDTO->retStrNome();
  		
  		$objContatoDTO->setStrPalavrasPesquisa($_POST['palavras_pesquisa']);
  		
  		if ($numIdGrupoContato!=''){
  			$objContatoDTO->setNumIdGrupoContato($_POST['id_grupo_contato']);
  		}
  		
  		
  		$objContatoDTO->setNumMaxRegistrosRetorno(50);
  		$objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
  		
  		$objContatoRN = new ContatoRN();
  		
  		$arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);
  		
//   	$arrObjContatoDTO = ContatoINT::autoCompletarContextoPesquisa($_POST['palavras_pesquisa'],$_POST['id_grupo_contato']);
  		$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjContatoDTO,'IdContato', 'Nome');
  		break;
  		
  		
  	case 'unidade_auto_completar_todas':
  		$arrObjUnidadeDTO = UnidadeINT::autoCompletarUnidades($_POST['palavras_pesquisa'],true,$_POST['id_orgao']);
  		$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO,'IdUnidade', 'Sigla');
  		break;
      
   default:
      throw new InfraException("Ação '".$_GET['acao_ajax_externo']."' não reconhecida pelo controlador AJAX externo.");
  }
  

  InfraAjax::enviarXML($xml);

}catch(Exception $e){
	//LogSEI::getInstance()->gravar('ERRO AJAX: '.$e->__toString());
  InfraAjax::processarExcecao($e);
}
?>
<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONФMICA
 * 2014-09-29
 * Versгo do Gerador de Cуdigo: 1.0
 * Arquivo para realizar controle requisiзгo ajax.
 *
 */

try{
	require_once dirname(__FILE__).'/../../SEI.php';
	
	InfraAjax::decodificarPost();
	
	//Verificar se precisa mesmo de validacao de sessao
	SessaoSEIExterna::getInstance()->validarSessao();
	
	MdPesqPesquisaUtil::valiadarLink();
	
	switch($_GET['acao_ajax_externo']){
 	
		case 'contato_auto_completar_contexto_pesquisa':
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
			$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjContatoDTO,'IdContato', 'Nome');
			break;
			
		case 'unidade_auto_completar_todas':
			$arrObjUnidadeDTO = UnidadeINT::autoCompletarUnidades($_POST['palavras_pesquisa'],true,$_POST['id_orgao']);
			$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO,'IdUnidade', 'Sigla');
			break;
		
		default:
			throw new InfraException("Aзгo '".$_GET['acao_ajax_externo']."' nгo reconhecida pelo controlador AJAX externo.");
	}

InfraAjax::enviarXML($xml);

}catch(Exception $e){
	//LogSEI::getInstance()->gravar('ERRO AJAX: '.$e->__toString());
	InfraAjax::processarExcecao($e);
}
?>
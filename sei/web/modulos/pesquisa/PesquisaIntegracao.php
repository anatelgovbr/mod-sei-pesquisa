<?
	class PesquisaIntegracao extends SeiIntegracao {
		
		public function getNome(){
			return 'Módulo de Pesquisa Pública';
		}
		
		public function getVersao() {
			return '4.0.0';
		}
		
		public function getInstituicao(){
			return 'CADE - Conselho Administrativo de Defesa Econômica';
		}
		
		public function processarControlador($strAcao){
			
			switch ($strAcao) {
			
			case 'md_pesq_parametro_listar':
			case 'md_pesq_parametro_alterar':
				require_once dirname ( __FILE__ ) . '/md_pesq_parametro_pesquisa_lista.php';
				return true;
			}
		
			return false;
		
		}
		
		public function processarControladorAjaxExterno($strAcaoAjax){
			$xml = null;
			
			switch($strAcaoAjax){
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
		
			}
		
			return $xml;
		}
		
		public function montarMenuUsuarioExterno(){
			
			$objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
			$objParametroPesquisaDTO->setStrNome(MdPesqParametroPesquisaRN::$TA_MENU_USUARIO_EXTERNO);
			$objParametroPesquisaDTO->retStrValor();
			$objParametroPesquisaDTO->retStrNome();
			 
			$objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
			$objParametroPesquisaDTO = $objParametroPesquisaRN->consultar($objParametroPesquisaDTO);
			
			
			$bolMenuUsuarioExterno = $objParametroPesquisaDTO->getStrValor() == 'S' ? true : false;
			
			if($bolMenuUsuarioExterno){
				$arrModulos = ConfiguracaoSEI::getInstance()->getValor('SEI','Modulos');
				if(is_array($arrModulos) && array_key_exists('PesquisaIntegracao', $arrModulos)){
					$caminho = $arrModulos['PesquisaIntegracao'];
					$arrMenu = array();
					$arrMenu[] = '-^'.ConfiguracaoSEI::getInstance()->getValor('SEI','URL').'/modulos/'.$caminho.'/md_pesq_processo_pesquisar.php?acao_externa=protocolo_pesquisar&acao_origem_externa=protocolo_pesquisar&id_orgao_acesso_externo=0^^Pesquisa Pública^_blank^';
						
					return $arrMenu;
						
				}
			}
			
			return null;
			
		}
		
	}
?>
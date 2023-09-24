<?
	class PesquisaIntegracao extends SeiIntegracao {
		
		public function getNome()
		{
			return 'SEI Pesquisa Pública';
		}
		
		public function getVersao()
		{
			return '4.1.2';
		}
		
		
		public function getPeticionamentoMenorVersaoRequerida()
		{
			return '4.0.2';
		}
		
		public function getInstituicao()
		{
			return 'Anatel - Agência Nacional de Telecomunicações (desenvolvido originalmente pelo CADE)';
		}
		
		public function processarControlador($strAcao)
		{
			switch ($strAcao) {
			case 'md_pesq_parametro_listar':
			case 'md_pesq_parametro_alterar':
				require_once dirname ( __FILE__ ) . '/md_pesq_parametro_pesquisa_lista.php';
				return true;
			}
			return false;
		}
		
		public function processarControladorAjaxExterno($strAcaoAjax)
		{
			$xml = null;
			
			switch($strAcaoAjax){
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
			}
			return $xml;
		}
		
		public function montarMenuUsuarioExterno()
		{
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
					$arrMenu[] = '-^'.ConfiguracaoSEI::getInstance()->getValor('SEI','URL').'/modulos/'.$caminho.'/md_pesq_processo_pesquisar.php?acao_externa=protocolo_pesquisar&acao_origem_externa=protocolo_pesquisar^^Pesquisa Pública^_blank^';
					return $arrMenu;
				}
			}
			return null;
		}
		
		public static function verificaSeModPeticionamentoVersaoMinima()
		{
			
			$bolVersaoValida = false;
			
			$arrModulos = ConfiguracaoSEI::getInstance()->getValor('SEI','Modulos');

			if(is_array($arrModulos) && array_key_exists('PeticionamentoIntegracao', $arrModulos)){

				$objInfraParametroDTO = new InfraParametroDTO();
				$objInfraParametroDTO->setStrNome('VERSAO_MODULO_PETICIONAMENTO');
				$objInfraParametroDTO->retStrValor();

				$objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
				$arrObjInfraParametroDTO = $objInfraParametroBD->consultar($objInfraParametroDTO);
				$strVersaoInstalada = $arrObjInfraParametroDTO->getStrValor();

				$bolVersaoValida = version_compare($strVersaoInstalada, self::getPeticionamentoMenorVersaoRequerida(), '>=');

			}
				
			return $bolVersaoValida;
				
		}

	}
?>
<?

class PesquisaIntegracao extends SeiIntegracao {
	
	public function getNome()
	{
		return 'SEI Pesquisa Pública';
	}
	
	public function getVersao()
	{
		return '4.3.2';
	}
	
	
	public static function getPeticionamentoMenorVersaoRequerida()
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
	
	/**
	 * FUNCTION ADICIONADAS PARA ATENDER A MIGRAÇÃO DOS PARAMETROS DO MÓDULO UTILIDADES PARA O PESQUISA PÚBLICA
	 */
	
	/**
	 *  Verifica se está na última conclusão
	 * @param $idProtocolo
	 * @return bool
	 */
	public function verificaUltimaConclusao($idProtocolo)
	{
		$objAtividadeDTO = new AtividadeDTO();
		$objAtividadeDTO->setDblIdProtocolo($idProtocolo);
		$objAtividadeDTO->setDthConclusao(null);
		$objAtividadeDTO->retNumIdUnidade();
		$objAtividadeDTO = (new AtividadeRN())->contarRN0035($objAtividadeDTO);
		
		if ($objAtividadeDTO > 0) {
			return false;
		}
		return true;
	}
	
	/**
	 * @param $idProcedimento
	 * @return mixed
	 * @throws InfraException
	 */
	public function listarDocumentos($idProcedimento)
	{
		if (!isset($idProcedimento)) {
			throw new InfraException('Parâmetro $idProcedimento não informado.');
		}
		
		$objDocumentoDTO = new DocumentoDTO();
		$objDocumentoDTO->retDblIdDocumento();
		$objDocumentoDTO->retNumIdSerie();
		$objDocumentoDTO->retDblIdProcedimento();
		$objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
		$objDocumentoDTO->setDblIdProcedimento($idProcedimento);
		
		return (new DocumentoRN())->listarRN0008($objDocumentoDTO);
	}
	
	/**
	 * Lista processos anexados ao processo principal
	 * @param $idProcedimento
	 * @return mixed
	 * @throws InfraException
	 */
	public function listarProcessosAnexado($idProcedimento)
	{
		if (!isset($idProcedimento)) {
			throw new InfraException('Parâmetro $idProcedimento não informado.');
		}
		
		$objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
		$objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
		$objRelProtocoloProtocoloDTO->setDblIdProtocolo1($idProcedimento);
		$objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
		$objRelProtocoloProtocoloDTO = (new RelProtocoloProtocoloRN())->listarRN0187($objRelProtocoloProtocoloDTO);
		
		return $objRelProtocoloProtocoloDTO;
	}
	
	/**
	 * Lista processos anexados ao processo principal
	 * @param $idProcedimento
	 * @return mixed
	 * @throws InfraException
	 */
	public function listarProcessosAnexadores($idProcedimento)
	{
		if (!isset($idProcedimento)) {
			throw new InfraException('Parâmetro $idProcedimento não informado.');
		}
		
		$objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
		$objRelProtocoloProtocoloDTO->retDblIdProtocolo2($idProcedimento);
		$objRelProtocoloProtocoloDTO->setDblIdProtocolo1();
		$objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
		$objRelProtocoloProtocoloDTO = (new RelProtocoloProtocoloRN())->listarRN0187($objRelProtocoloProtocoloDTO);
		
		return $objRelProtocoloProtocoloDTO;
	}

	public static function isAnexadoAProcessoRestrito($idProcedimento){

		$anexoDeRestrito = false;

		// Verifica se o Processo pai do Protocolo está anexado a Processo Restrito:
		$objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
		$objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
		$objRelProtocoloProtocoloDTO->setDblIdProtocolo2($idProcedimento);
		$objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
		$listaProcessosAnexadores = (new RelProtocoloProtocoloRN())->listarRN0187($objRelProtocoloProtocoloDTO);

		if (!empty($listaProcessosAnexadores)) {
			
			foreach ($listaProcessosAnexadores as $processoAnexador) {
				
				$objProtocoloDTO = new ProtocoloDTO();
				$objProtocoloDTO->setDblIdProtocolo($processoAnexador->getDblIdProtocolo1());
				$objProtocoloDTO->retStrStaNivelAcessoGlobal();
				$objProtocoloDTO->retStrStaNivelAcessoLocal();
				$objProcessoAnexador = (new ProtocoloRN())->consultarRN0186($objProtocoloDTO);

				if(!empty($objProcessoAnexador) && in_array($objProcessoAnexador->getStrStaNivelAcessoGlobal(), [ProtocoloRN::$NA_RESTRITO])){
					$anexoDeRestrito = true;
				}

			}

		}

		return $anexoDeRestrito;
	}
	
	public function validaProcessoAnexo($idProcedimento, $arrValor)
	{
		$lista = '';
		$objProcedimentoRN = new ProcedimentoRN();
		$objProcedimentoDTO = new ProcedimentoDTO();
		$objProcedimentoDTO->setDblIdProcedimento($idProcedimento);
		$objProcedimentoDTO->retStrStaNivelAcessoLocalProtocolo();
		$objProcedimentoDTO->retNumIdHipoteseLegalProtocolo();
		$objProcedimentoDTO->retStrNomeTipoProcedimento();
		$objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
		$objProcedimentoDTO->retNumIdHipoteseLegalProtocolo();
		$objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
		
		if (in_array($objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo(), [ProtocoloRN::$NA_RESTRITO])) {
			if (in_array($objProcedimentoDTO->getNumIdHipoteseLegalProtocolo(), $arrValor)) {
				$objHipotesePrincipalRN = new HipoteseLegalRN();
				$objHipotesePrincipalDTO = new HipoteseLegalDTO();
				$objHipotesePrincipalDTO->setNumIdHipoteseLegal($objProcedimentoDTO->getNumIdHipoteseLegalProtocolo());
				$objHipotesePrincipalDTO->retStrNome();
				$objHipotesePrincipalDTO->retStrBaseLegal();
				$objHipotesePrincipalDTO = $objHipotesePrincipalRN->consultar($objHipotesePrincipalDTO);
				
				if ($objHipotesePrincipalDTO) {
					$lista = $lista . "-   " . $objProcedimentoDTO->getStrNomeTipoProcedimento() . " (" . $objProcedimentoDTO->getStrProtocoloProcedimentoFormatado() . "): " . $objHipotesePrincipalDTO->getStrNome() . " (" . $objHipotesePrincipalDTO->getStrBaseLegal() . ")\n";
				}
			}
		}
		return $lista;
	}
	
	/**
	 * Verifica se Existe documentos restritos e se o valor da hipotese legal está setada no parâmentro
	 * @param $documentos
	 * @param $arrValor
	 * @return string
	 */
	public function verificaDocumentoRestrito($documentos, $arrValor)
	{
		$objProtocoloRN = new ProtocoloRN();
		$listaDocumentos = '';
		
		foreach ($documentos as $documento) {
			$objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
			$objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
			$objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$NA_RESTRITO);
			$objPesquisaProtocoloDTO->setDblIdProtocolo($documento->getDblIdDocumento());
			$arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
			
			$idHipoteseLegal = $arrObjProtocoloDTO[0]->getNumIdHipoteseLegal();
			$nivelAcesso     = $documento->getStrStaNivelAcessoLocalProtocolo();
			
			if ($arrObjProtocoloDTO && $nivelAcesso == ProtocoloRN::$NA_RESTRITO && in_array($idHipoteseLegal, $arrValor)) {
				$objHipoteseRN  = new HipoteseLegalRN();
				$objHipoteseDTO = new HipoteseLegalDTO();
				$objHipoteseDTO->setNumIdHipoteseLegal($idHipoteseLegal);
				$objHipoteseDTO->retStrNome();
				$objHipoteseDTO->retStrBaseLegal();
				$objHipoteseDTO = $objHipoteseRN->consultar($objHipoteseDTO);
				
				if ($objHipoteseDTO) {
					$listaDocumentos = $listaDocumentos . "-   ".$arrObjProtocoloDTO[0]->getStrNomeSerieDocumento()." (".$arrObjProtocoloDTO[0]->getStrProtocoloFormatado(). "): ".$objHipoteseDTO->getStrNome()." (".$objHipoteseDTO->getStrBaseLegal().")\n";
				}
			}
		}
		
		return $listaDocumentos;
	}
	
	public function bloquearProcesso($objProcedimentoAPI)
	{
		
		$idProcedimento = $objProcedimentoAPI[0]->getIdProcedimento();
		
		// migrado infra parametro do Utilidades para Peticionamento
		$objAtividadeDTO = new AtividadeDTO();
		$objAtividadeDTO->setDblIdProtocolo($idProcedimento);
		$objAtividadeDTO->setDthConclusao(null);
		$objAtividadeDTO->retNumIdUnidade();
		$countOjAtividade = (new AtividadeRN())->contarRN0035($objAtividadeDTO);
		
		$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
		$strValor = $objInfraParametro->getValor('MODULO_PESQUISA_PUBLICA_BLOQUEAR_BLOQUEAR_PROCESSO_COM_DOCUMENTO_RESTRITO_USANDO_HIPOTESE_LEGAL', false);
		$arrValor = [];
		
		if (!empty($strValor) && $countOjAtividade == 1) {
			
			$arrValor        = array_merge($arrValor, explode(',', $strValor));
			$objProtocoloRN  = new ProtocoloRN();
			$documentos      = self::listarDocumentos($idProcedimento);
			$listaDocumentos = '';
			
			// Valida processo principal
			$objPrcPrincipalDTO = new ProcedimentoDTO();
			$objPrcPrincipalDTO->setDblIdProcedimento($idProcedimento);
			$objPrcPrincipalDTO->retStrStaNivelAcessoLocalProtocolo();
			$objPrcPrincipalDTO->retNumIdHipoteseLegalProtocolo();
			$objPrcPrincipalDTO->retStrNomeTipoProcedimento();
			$objPrcPrincipalDTO->retStrProtocoloProcedimentoFormatado();
			$objPrcPrincipalDTO->retNumIdHipoteseLegalProtocolo();
			$objPrcPrincipalDTO = (new ProcedimentoRN())->consultarRN0201($objPrcPrincipalDTO);
			
			if ($objPrcPrincipalDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_RESTRITO) {
				if (in_array($objPrcPrincipalDTO->getNumIdHipoteseLegalProtocolo(), $arrValor)) {
					$objHipotesePrincipalRN = new HipoteseLegalRN();
					$objHipotesePrincipalDTO = new HipoteseLegalDTO();
					$objHipotesePrincipalDTO->setNumIdHipoteseLegal($objPrcPrincipalDTO->getNumIdHipoteseLegalProtocolo());
					$objHipotesePrincipalDTO->retStrNome();
					$objHipotesePrincipalDTO->retStrBaseLegal();
					$objHipotesePrincipalDTO = $objHipotesePrincipalRN->consultar($objHipotesePrincipalDTO);
					
					if ($objHipotesePrincipalDTO) {
						$listaDocumentos = $listaDocumentos . "-   " . $objPrcPrincipalDTO->getStrNomeTipoProcedimento() . " (" . $objPrcPrincipalDTO->getStrProtocoloProcedimentoFormatado() . "): " . $objHipotesePrincipalDTO->getStrNome() . " (" . $objHipotesePrincipalDTO->getStrBaseLegal() . ")\n";
					}
				}
			}
			
			// Valida documentos anexados ao processo principal
			foreach ($documentos as $documento) {
				$objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
				$objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
				$objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$NA_RESTRITO);
				$objPesquisaProtocoloDTO->setDblIdProtocolo($documento->getDblIdDocumento());
				$arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
				
				$idHipoteseLegal = $arrObjProtocoloDTO[0]->getNumIdHipoteseLegal();
				$nivelAcesso = $documento->getStrStaNivelAcessoLocalProtocolo();
				
				if ($arrObjProtocoloDTO && $nivelAcesso == ProtocoloRN::$NA_RESTRITO && in_array($idHipoteseLegal, $arrValor)) {
					
					$objHipoteseRN  = new HipoteseLegalRN();
					$objHipoteseDTO = new HipoteseLegalDTO();
					$objHipoteseDTO->setNumIdHipoteseLegal($idHipoteseLegal);
					$objHipoteseDTO->retStrNome();
					$objHipoteseDTO->retStrBaseLegal();
					$objHipoteseDTO = $objHipoteseRN->consultar($objHipoteseDTO);
					
					if ($objHipoteseDTO) {
						$listaDocumentos = $listaDocumentos . "-   ".$arrObjProtocoloDTO[0]->getStrNomeSerieDocumento()." (".$arrObjProtocoloDTO[0]->getStrProtocoloFormatado(). "): ".$objHipoteseDTO->getStrNome()." (".$objHipoteseDTO->getStrBaseLegal().")\n";
					}
				}
			}
			
			// Valida Processos anexados ao processo principal
			$listaProcessos = self::listarProcessosAnexado($idProcedimento);
			
			if ($listaProcessos) {
			
				$listaDocProcessoAnexo = '';
				$listaMsgProcessos = '';
				foreach ($listaProcessos as $processo) {
					$listaProcesso    = self::validaProcessoAnexo($processo->getDblIdProtocolo2(), $arrValor);
					$listaMsgProcesso = $listaMsgProcessos . $listaProcesso;
					$documentos       = self::listarDocumentos($processo->getDblIdProtocolo2());
					
					if ($documentos) {
						$listaDocProcessoAnexo = self::verificaDocumentoRestrito($documentos, $arrValor);
					}
				}
				
				$listaProcessosAnexado = self::listarProcessosAnexado($listaProcessos[0]->getDblIdProtocolo2());
				if ($listaProcessosAnexado) {
					$listaDocProcessoAnexo2 = '';
					foreach ($listaProcessosAnexado as $processoAnexado) {
						$documentos2 = self::listarDocumentos($processoAnexado->getDblIdProtocolo2());
						if ($documentos2) {
							$listaDocProcessoAnexo2 = self::verificaDocumentoRestrito($documentos2, $arrValor);
						}
					}
				}
				
				$listaProcessosAnexadores = self::listarProcessosAnexadores($listaProcessos[0]->getDblIdProtocolo2());
				if ($listaProcessosAnexadores) {
					$listaDocProcessoAnexo3 = '';
					foreach ($listaProcessosAnexadores as $processoAnexador) {
						$documentos3 = self::listarDocumentos($processoAnexador->getDblIdProtocolo2());
						if ($documentos3) {
							$listaDocProcessoAnexo3 = self::verificaDocumentoRestrito($documentos3, $arrValor);
						}
					}
				}
				
			}
			
			if (!empty($listaDocumentos.$listaDocProcessoAnexo.$listaDocProcessoAnexo2.$listaDocProcessoAnexo3.$listaMsgProcesso)) {
				$msg = "Não é possível bloquear o processo n ".$objPrcPrincipalDTO->getStrProtocoloProcedimentoFormatado().", pois nele ou em processo anexado/anexador ainda constam documentos com Nível de Acesso Restrito usando as Hipóteses Legais abaixo: \n\n" . $listaDocumentos.$listaDocProcessoAnexo.$listaDocProcessoAnexo2.$listaMsgProcesso;
				$objInfraException = new InfraException();
				$objInfraException->lancarValidacao($msg);
			}
			
		}
		
		return parent::bloquearProcesso($objProcedimentoAPI);
		
	}
	
	public static function verificaSeModPesquisaPublicaVersaoMinima()
	{
		$arrModulos = ConfiguracaoSEI::getInstance()->getValor('SEI','Modulos');

		if(is_array($arrModulos) && array_key_exists('PesquisaIntegracao', $arrModulos)){
			$objInfraParametroDTO = new InfraParametroDTO();
			$objInfraParametroDTO->setStrNome('VERSAO_MODULO_PESQUISA_PUBLICA');
			$objInfraParametroDTO->retStrValor();

			$objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
			$arrObjInfraParametroDTO = $objInfraParametroBD->consultar($objInfraParametroDTO);

			if(!empty($arrObjInfraParametroDTO)){
				return version_compare($arrObjInfraParametroDTO->getStrValor(), '4.3.0', '>=');
			}
		}

		return false;
	}

	private function validaDocumentoPublicoPesquisaPublica($idDocumento)
	{
		// Consultar protocolo do documento
		$objProtocoloDTO = new ProtocoloDTO();
		$objProtocoloDTO->setDblIdProtocolo($idDocumento);
		$objProtocoloDTO->retStrStaNivelAcessoLocal();
		$objProtocoloDTO->retStrStaNivelAcessoGlobal();
		$objProtocoloDTO->retStrStaProtocolo();
		$objProtocoloDTO->retDtaInclusao();
		$objProtocoloDTO->retDblIdProtocolo();
		$objProtocoloDTO = (new ProtocoloRN())->consultarRN0186($objProtocoloDTO);

		if (empty($objProtocoloDTO)
			|| $objProtocoloDTO->getStrStaNivelAcessoLocal() != ProtocoloRN::$NA_PUBLICO
			|| $objProtocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO) {
			return false;
		}

		// Consultar dados do documento
		$objDocumentoDTO = new DocumentoDTO();
		$objDocumentoDTO->setDblIdDocumento($idDocumento);
		$objDocumentoDTO->retDblIdDocumento();
		$objDocumentoDTO->retDblIdProcedimento();
		$objDocumentoDTO->retStrStaDocumento();
		$objDocumentoDTO = (new DocumentoRN())->consultarRN0005($objDocumentoDTO);

		if (empty($objDocumentoDTO)) {
			return false;
		}

		// Verificar processo pai do documento - deve ter nivel de acesso local publico
		$idProcedimento = $objDocumentoDTO->getDblIdProcedimento();
		$objProcessoDTO = new ProtocoloDTO();
		$objProcessoDTO->setDblIdProtocolo($idProcedimento);
		$objProcessoDTO->setStrStaProtocolo(ProtocoloRN::$TP_PROCEDIMENTO);
		$objProcessoDTO->retStrStaNivelAcessoLocal();
		$objProcessoDTO->retStrStaNivelAcessoGlobal();
		$objProcessoDTO = (new ProtocoloRN())->consultarRN0186($objProcessoDTO);

		if (empty($objProcessoDTO) || $objProcessoDTO->getStrStaNivelAcessoLocal() != ProtocoloRN::$NA_PUBLICO) {
			return false;
		}

		// Verificar se o processo pai esta anexado a processo restrito
		$objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
		$objRelProtocoloProtocoloDTO->retDblIdProtocolo1();
		$objRelProtocoloProtocoloDTO->setDblIdProtocolo2($idProcedimento);
		$objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
		$listaProcessosAnexadores = (new RelProtocoloProtocoloRN())->listarRN0187($objRelProtocoloProtocoloDTO);

		if (!empty($listaProcessosAnexadores)) {
			foreach ($listaProcessosAnexadores as $processoAnexador) {
				$objProtAnexadorDTO = new ProtocoloDTO();
				$objProtAnexadorDTO->setDblIdProtocolo($processoAnexador->getDblIdProtocolo1());
				$objProtAnexadorDTO->retStrStaNivelAcessoGlobal();
				$objProcessoAnexador = (new ProtocoloRN())->consultarRN0186($objProtAnexadorDTO);

				if (!empty($objProcessoAnexador) && $objProcessoAnexador->getStrStaNivelAcessoGlobal() != ProtocoloRN::$NA_PUBLICO) {
					return false;
				}
			}
		}

		// Documentos gerados devem ser assinados (exceto formularios automaticos)
		if ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO
			&& $objDocumentoDTO->getStrStaDocumento() != DocumentoRN::$TD_FORMULARIO_AUTOMATICO) {

			$objAssinaturaDTO = new AssinaturaDTO();
			$objAssinaturaDTO->setDblIdDocumento($idDocumento);
			$objAssinaturaDTO->retNumIdAssinatura();
			$objAssinaturaDTO->setNumMaxRegistrosRetorno(1);
			$arrObjAssinaturaDTO = (new AssinaturaRN())->listarRN1323($objAssinaturaDTO);

			if (empty($arrObjAssinaturaDTO)) {
				return false;
			}
		}

		// Verificar data de corte da pesquisa
		$dtaParamCortePesquisa = (new MdPesqParametroPesquisaRN())->existeDataCortePesquisa();
		if ($dtaParamCortePesquisa) {
			$dtaCorteDoc = $objProtocoloDTO->getDtaInclusao();

			// Para documentos gerados (editor interno ou formulario gerado), usar data da primeira assinatura
			if ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO
				&& in_array($objDocumentoDTO->getStrStaDocumento(), [DocumentoRN::$TD_EDITOR_INTERNO, DocumentoRN::$TD_FORMULARIO_GERADO])) {

				$objAssinDTO = new AssinaturaDTO();
				$objAssinDTO->retDthAberturaAtividade();
				$objAssinDTO->setDblIdDocumento($idDocumento);
				$objAssinDTO->setOrdNumIdAssinatura(InfraDTO::$TIPO_ORDENACAO_ASC);
				$objAssinDTO->setNumMaxRegistrosRetorno(1);
				$arrObjAssinDTO = (new AssinaturaRN())->listarRN1323($objAssinDTO);

				if (!empty($arrObjAssinDTO) && $arrObjAssinDTO[0] != null && $arrObjAssinDTO[0]->isSetDthAberturaAtividade()) {
					$dtaCorteDoc = substr($arrObjAssinDTO[0]->getDthAberturaAtividade(), 0, 10);
				}
			}

			$dtaCorteDocFormatada = date('Y-m-d', strtotime(str_replace('/', '-', $dtaCorteDoc)));
			if ($dtaParamCortePesquisa > $dtaCorteDocFormatada) {
				return false;
			}
		}

		return true;
	}

	public function verificarAcessoProtocolo($arrObjProcedimentoAPI, $arrObjDocumentoAPI)
	{
		$ret = null;

		if (!self::verificaSeModPesquisaPublicaVersaoMinima()) {
			return $ret;
		}

		foreach ($arrObjDocumentoAPI as $objDocumentoAPI) {
			if ($objDocumentoAPI->getNivelAcesso() != ProtocoloRN::$NA_SIGILOSO) {
				if ($this->validaDocumentoPublicoPesquisaPublica($objDocumentoAPI->getIdDocumento())) {
					$ret[$objDocumentoAPI->getIdDocumento()] = SeiIntegracao::$TAM_PERMITIDO;
				}
			}
		}

		return $ret;
	}

	public function concluirProcesso($arrObjProcedimentoAPI)
	{
		
		$ultimaConclusao = $this->verificaUltimaConclusao($arrObjProcedimentoAPI[0]->getIdProcedimento());
		$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
		$strValor = $objInfraParametro->getValor('MODULO_PESQUISA_PUBLICA_BLOQUEAR_CONCLUIR_PROCESSO_COM_DOCUMENTO_RESTRITO_USANDO_HIPOTESE_LEGAL', false);
		$arrValor = [];
		
		if (!empty($strValor) && $ultimaConclusao) {
			
			$arrValor        = array_merge($arrValor, explode(',', $strValor));
			$objProtocoloRN  = new ProtocoloRN();
			$documentos      = $this->listarDocumentos($arrObjProcedimentoAPI[0]->getIdProcedimento());
			$listaDocumentos = '';
			
			// Valida processo principal
			$objPrcPrincipalDTO = new ProcedimentoDTO();
			$objPrcPrincipalDTO->setDblIdProcedimento($arrObjProcedimentoAPI[0]->getIdProcedimento());
			$objPrcPrincipalDTO->retStrStaNivelAcessoLocalProtocolo();
			$objPrcPrincipalDTO->retStrStaNivelAcessoGlobalProtocolo();
			$objPrcPrincipalDTO->retNumIdHipoteseLegalProtocolo();
			$objPrcPrincipalDTO->retStrNomeTipoProcedimento();
			$objPrcPrincipalDTO->retStrProtocoloProcedimentoFormatado();
			$objPrcPrincipalDTO->retNumIdHipoteseLegalProtocolo();
			$objPrcPrincipalDTO = (new ProcedimentoRN())->consultarRN0201($objPrcPrincipalDTO);
			
			// Caso o Processo esteja com restrição por Hipótese Legal
			if ($objPrcPrincipalDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_RESTRITO) {
				if (in_array($objPrcPrincipalDTO->getNumIdHipoteseLegalProtocolo(), $arrValor)) {
					$objHipotesePrincipalDTO = new HipoteseLegalDTO();
					$objHipotesePrincipalDTO->setNumIdHipoteseLegal($objPrcPrincipalDTO->getNumIdHipoteseLegalProtocolo());
					$objHipotesePrincipalDTO->retStrNome();
					$objHipotesePrincipalDTO->retStrBaseLegal();
					$objHipotesePrincipalDTO = (new HipoteseLegalRN())->consultar($objHipotesePrincipalDTO);
					
					if ($objHipotesePrincipalDTO) {
						$listaDocumentos = $listaDocumentos . "-   " . $objPrcPrincipalDTO->getStrNomeTipoProcedimento() . " (" . $objPrcPrincipalDTO->getStrProtocoloProcedimentoFormatado() . "): " . $objHipotesePrincipalDTO->getStrNome() . " (" . $objHipotesePrincipalDTO->getStrBaseLegal() . ")\n";
					}
				}
			}
			
			// Valida documentos anexados ao processo principal
			foreach ($documentos as $documento) {
				
				$nivelAcesso = $documento->getStrStaNivelAcessoLocalProtocolo();
				
				$objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
				$objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
				$objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$NA_RESTRITO);
				$objPesquisaProtocoloDTO->setDblIdProtocolo($documento->getDblIdDocumento());
				$arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
				
				if($arrObjProtocoloDTO && $nivelAcesso == ProtocoloRN::$NA_RESTRITO){
					
					$idHipoteseLegal = $arrObjProtocoloDTO[0]->getNumIdHipoteseLegal();
					
					if ( in_array($idHipoteseLegal, $arrValor) ) {
						
						$objHipoteseDTO = new HipoteseLegalDTO();
						$objHipoteseDTO->setNumIdHipoteseLegal($idHipoteseLegal);
						$objHipoteseDTO->retStrNome();
						$objHipoteseDTO->retStrBaseLegal();
						$objHipoteseDTO = (new HipoteseLegalRN())->consultar($objHipoteseDTO);
						
						if ($objHipoteseDTO) {
							$listaDocumentos = $listaDocumentos . "-   ".$arrObjProtocoloDTO[0]->getStrNomeSerieDocumento()." (".$arrObjProtocoloDTO[0]->getStrProtocoloFormatado(). "): ".$objHipoteseDTO->getStrNome()." (".$objHipoteseDTO->getStrBaseLegal().")\n";
						}
						
					}
					
				}
				
			}
			
			// Valida Processos anexados ao processo principal
			$listaProcessos = $this->listarProcessosAnexado($arrObjProcedimentoAPI[0]->getIdProcedimento());
			
			if ($listaProcessos) {
				$listaDocProcessoAnexo = '';
				$listaMsgProcessos = '';
				foreach ($listaProcessos as $processo) {
					$listaProcesso    = $this->validaProcessoAnexo($processo->getDblIdProtocolo2(), $arrValor);
					$listaMsgProcesso = $listaMsgProcessos . $listaProcesso;
					$documentos       = $this->listarDocumentos($processo->getDblIdProtocolo2());
					
					if ($documentos) {
						$listaDocProcessoAnexo = $this->verificaDocumentoRestrito($documentos, $arrValor);
					}
				}
				
				$listaProcessosAnexado = $this->listarProcessosAnexado($listaProcessos[0]->getDblIdProtocolo2());
				if ($listaProcessosAnexado) {
					$listaDocProcessoAnexo2 = '';
					foreach ($listaProcessosAnexado as $processoAnexado) {
						$documentosAnexados = $this->listarDocumentos($processoAnexado->getDblIdProtocolo2());
						if ($documentosAnexados) {
							$listaDocProcessoAnexo2 = $this->verificaDocumentoRestrito($documentos, $arrValor);
						}
					}
				}
			}
			
			if (!empty($listaDocumentos.$listaDocProcessoAnexo.$listaDocProcessoAnexo2.$listaMsgProcesso)) {
				$objInfraException = new InfraException();
				$msg = "Não é possível concluir o processo nº ".$objPrcPrincipalDTO->getStrProtocoloProcedimentoFormatado().", pois nele ou em processo anexado ainda constam documentos com Nível de Acesso Restrito usando as Hipóteses Legais abaixo: \n\n" . $listaDocumentos.$listaDocProcessoAnexo.$listaDocProcessoAnexo2.$listaMsgProcesso;
				return $objInfraException->lancarValidacao($msg);
			}
			
		}
		
		return parent::concluirProcesso($arrObjProcedimentoAPI);
		
	}

}

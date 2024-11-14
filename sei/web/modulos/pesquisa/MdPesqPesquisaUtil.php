<?

class MdPesqPesquisaUtil {

	public static function criarBarraEstatisticas($total,$inicio,$fim)
	{
		return "<div class=\"pesquisaBarraD\">".self::obterTextoBarraEstatisticas($total,$inicio,$fim)."</div>";
	}

	public static function obterTextoBarraEstatisticas($total,$inicio,$fim)
	{
		$ret = '';
		if ($total > 0 && $total != ""){
			if ($total < $fim) {
				$ret .= $total.' resultado'.($total>1?'s':'');
			} else {
				$ret .= "Exibindo " . ($inicio+1) . " - " . $fim . " de " . $total;
			}
		}
		return $ret;
	}

	//Cria a navegacao completa
	public static function criarBarraNavegacao($totalRes, $inicio, $numResPorPag, $objPagina, $objSessao, $strLocalPesquisa,$md5Captcha = null,$strControlador = 'processo_pesquisar.php')
	{
		if ($totalRes == 0)
			return;

		$nav = "<div class=\"pesquisaPaginas\">";

		$paginaAtual = $inicio / $numResPorPag + 1;

		$urlSemInicio = $strControlador.'?acao_externa='.$_GET['acao_externa']."&acao_origem_externa=protocolo_pesquisar_paginado";
		
		$urlSemInicio .= '&local_pesquisa='.$strLocalPesquisa;
		
		$hash = (!is_null($md5Captcha)) ? "&hash=".$md5Captcha : "";

		if ($inicio != null ){
			$nav .= "<span class=\"pequeno\"><a href=\"javascript:pagina.ir('" . $objPagina->formatarXHTML($objSessao->assinarLink($urlSemInicio . '&num_pagina='. ($paginaAtual - 2) . "&inicio_cade=" . ($inicio - $numResPorPag) . $hash)) . "')\">Anterior</a></span>\n";
		}
			
		if ($totalRes > $numResPorPag){
			$numPagParaClicar = 12;

			if (ceil($totalRes / $numResPorPag) > $numPagParaClicar){
				$iniNav = ($paginaAtual - floor(($numPagParaClicar - 1) / 2)) - 1;
				$fimNav = ($paginaAtual + ceil(($numPagParaClicar - 1) / 2));

				if ($iniNav < 0){
					$iniNav = 0;
					$fimNav = $numPagParaClicar;
				}

				if ($fimNav > ceil($totalRes / $numResPorPag)){
					$fimNav = ceil($totalRes / $numResPorPag);
					$iniNav = $fimNav - $numPagParaClicar;
				}
			} else {
				$iniNav = 0;
				$fimNav = ceil($totalRes / $numResPorPag);
			}

			for ($i = $iniNav; $i < $fimNav; $i++){
				$numPagina = $i;
				if ($inicio == 0 AND $i == 0){
					$nav .= " <b>" . ($i + 1) . "</b> ";
				} elseif (($i + 1) == ($inicio / $numResPorPag + 1)){
					$nav .= " <b>" . ($i + 1) . "</b> ";
				} else {
					//$nav .= " <a href=\"javascript:pagina.ir('" . str_replace('+','%2B',$objPagina->formatarXHTML($objSessao->assinarLink($urlSemInicio . '&num_pagina='.$numPagina."&inicio=" . ($i * $numResPorPag)))).'#Pesquisa_Siscade' . "')\">" . ($i + 1) . "</a>\n";
					$nav .= " <a href=\"javascript:pagina.ir('" . str_replace('+','%2B',$objPagina->formatarXHTML($objSessao->assinarLink($urlSemInicio . '&num_pagina='.$numPagina."&inicio_cade=" . ($i * $numResPorPag) . $hash)))."')\">" . ($i + 1) . "</a>\n";
					
				}
			}
		}

		if (($inicio / $numResPorPag) + 1 != ceil($totalRes / $numResPorPag)){
			$nav .= "<span class=\"pequeno\"><a href=\"javascript:pagina.ir('" . $objPagina->formatarXHTML($objSessao->assinarLink($urlSemInicio . '&num_pagina='.$paginaAtual . "&inicio_cade=" . ($inicio + $numResPorPag) . $hash)) . "')\">Próxima</a></span>\n";
		}

		$nav .= "</div>";

		return $nav;
	}
	
	public static function buscaParticipantes($strNomeParticipante)
	{
		$objContatoDTO = new ContatoDTO();
    	$objContatoDTO->retNumIdContato();
    	$objContatoDTO->setStrPalavrasPesquisa($strNomeParticipante);
  	  	$objContatoDTO->setNumMaxRegistrosRetorno(50);
    	$objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
        $arrObjContatoDTO = (new ContatoRN())->pesquisarRN0471($objContatoDTO);
		
		return MdPesqPesquisaUtil::preparaIdParticipantes($arrObjContatoDTO);
	}
	
	public static function valiadarLink($strLink = null)
	{	
		if ($strLink == null){
			$strLink = $_SERVER['REQUEST_URI'];
		}
		
		$strLink = urldecode($strLink);
		
		if (trim($strLink == '')){
			return;
		}
		
		$arrParametros = (array('id_orgao_acesso_externo','id_procedimento','id_documento'));
		
		foreach ($arrParametros as $strParametros){
			if(isset($_GET[$strParametros])){
				
				if(trim($_GET[$strParametros]) == ''){
					 throw new InfraException('Link externo inválido.');
				}
				
				if(!is_numeric($_GET[$strParametros])){
					throw new InfraException('Link externo inválido.');
				}
			}
		}
		
		$arrScriptFileName = (array('processo_exibir.php','documento_consulta_externa.php'));
		
		if(in_array(basename($_SERVER['SCRIPT_FILENAME']), $arrScriptFileName)){
			if(!isset($_GET['acao_externa']) || trim($_GET['acao_externa'])==''){
 				throw new InfraException('Link externo inválido.');
			}
			
			if(!isset($_GET['id_orgao_acesso_externo'])){
				throw new InfraException('Link externo inválido.');
			}
		}
		
		if (basename($_SERVER['SCRIPT_FILENAME']) == 'controlador_ajax_externo.php'){
		
			if (!isset($_GET['acao_ajax_externo']) || trim($_GET['acao_ajax_externo'])==''){
				throw new InfraException('Link externo inválido.');
			}
		
			
			$arrAcaoAjaxExterno = (array('contato_auto_completar_contexto_pesquisa','unidade_auto_completar_todas'));
			
			if (!in_array($_GET['acao_ajax_externo'], $arrAcaoAjaxExterno)) {
				throw new InfraException('Link externo inválido.');
			}
		}
		
		// Corrige problema de nao exibir a descricao do Orgao na barra superior
		if (isset($_GET['id_orgao_acesso_externo'])){
			if (SessaoSEIExterna::getInstance()->getNumIdOrgaoUsuarioExterno()==null){
				$objOrgaoDTO = new OrgaoDTO();
				$objOrgaoDTO->setBolExclusaoLogica(false);
				$objOrgaoDTO->retNumIdOrgao();
				$objOrgaoDTO->retStrSigla();
				$objOrgaoDTO->retStrDescricao();
				$objOrgaoDTO->setNumIdOrgao($_GET['id_orgao_acesso_externo']);

				$objOrgaoRN = new OrgaoRN();
				$objOrgaoDTO = $objOrgaoRN->consultarRN1352($objOrgaoDTO);

				if ($objOrgaoDTO==null){
					throw new InfraException('Link externo inválido.');
				}

				SessaoSEIExterna::getInstance()->setAtributo('ID_ORGAO_USUARIO_EXTERNO', $objOrgaoDTO->getNumIdOrgao());
				SessaoSEIExterna::getInstance()->setAtributo('SIGLA_ORGAO_USUARIO_EXTERNO', $objOrgaoDTO->getStrSigla());
				SessaoSEIExterna::getInstance()->setAtributo('DESCRICAO_ORGAO_USUARIO_EXTERNO', $objOrgaoDTO->getStrDescricao());
			}
		}
	}
	
	private static function preparaIdParticipantes($arrObjContatoDTO)
	{
		$strIdParticipante = '';
		if(!empty($arrObjContatoDTO) && count($arrObjContatoDTO) == 1){
			$strIdParticipante = $strIdParticipante.'id_int:*'.$arrObjContatoDTO[0]->getNumIdContato().'* AND ';
		}
		else if (!empty($arrObjContatoDTO) && count($arrObjContatoDTO) > 1){
			$count = 0;
			$strIdParticipante = 'id_int:(';
			for($i=0;$i < count($arrObjContatoDTO); $i++){
				$count = $count + 1;
				$strIdParticipante = $strIdParticipante.'*'.$arrObjContatoDTO[$i]->getNumIdContato().'*';
				if($count < count($arrObjContatoDTO)){
					$strIdParticipante = $strIdParticipante.' OR ';
				}
				if($count == count($arrObjContatoDTO)){
					$strIdParticipante = $strIdParticipante.') AND ';
				}
			}
		}
		return $strIdParticipante;	
	}

}

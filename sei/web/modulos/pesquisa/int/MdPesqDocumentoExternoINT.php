<?php

/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 29/11/2016
 * Versão do Gerador de Código: 1.0
 * Classe Banco de dados Procedimento siscade.
 *
 */

class MdPesqDocumentoExternoINT extends DocumentoINT{
	
	public static function formatarExibicaoConteudo($strTipoVisualizacao, $strConteudo, $objInfraPagina=null, $objInfraSessao=null, $strLinkDownload=null)
	{
		$strResultado = '';
	
		if (!InfraString::isBolVazia($strConteudo)){
	
			if (substr($strConteudo,0,5) != '<?xml'){
				$strResultado = $strConteudo;
			}else{
	
				//die($strConteudo);
	
				//internamente o DOM utiliza UTF-8 mesmo passando iso-8859-1
				//por isso e necessario usar utf8_decode
				$objXml = new DomDocument('1.0','iso-8859-1');
	
				/*
				 $strConteudo = '<?xml version="1.0"?>
				<documento>
				<atributo id="" tipo="" nome="" titulo="Atributo A">nomeA</atributo>
				<atributo id="" tipo="" nome="" titulo="Atributo B">nomeB</atributo>
				<atributo id="" tipo="" nome="" titulo="Atributo C">
				<valores>
				<valor id="" tipo="" nome="" titulo="Valor C1">nomeC1</valor>
				<valor id="" tipo="" nome="" titulo="Valor C2">nomeC2</valor>
				</valores>
				</atributo>
				<atributo id="" tipo="" nome="" titulo="Atributo D">
				<valores id="" tipo="" nome="" titulo="Valores D1">
				<valor id="" tipo="" nome="" titulo="Valor D1V1">D1V1</valor>
				<valor id="" tipo="" nome="" titulo="Valor D1V2">D1V2</valor>
				<valor id="" tipo="" nome="" titulo="Valor D1V3">D1V3</valor>
				</valores>
				<valores id="" tipo="" nome="" titulo="Valores D2">
				<valor id="" tipo="" nome="" titulo="Valor D2V1">D2V1</valor>
				<valor id="" tipo="" nome="" titulo="Valor D2V2">D2V2</valor>
				<valor id="" tipo="" nome="" titulo="Valor D2V3">D2V3</valor>
				</valores>
				<valores id="" tipo="" nome="" titulo="Valores D3">
				<valor id="" tipo="" nome="" nome="d3v1" titulo="Valor D3V1">D3V1</valor>
				<valor id="" tipo="" nome="" titulo="Valor D3V2">D3V2</valor>
				<valor id="" tipo="" nome="" titulo="Valor D3V3">D3V3</valor>
				</valores>
				</atributo>
				</documento>';
				*/
	
				$objXml->loadXML($strConteudo);
	
				$arrAtributos = $objXml->getElementsByTagName('atributo');
	
				if ($strTipoVisualizacao == self::$TV_HTML){
	
					$strNovaLinha = '<br />';
					$strItemInicio = '<b>';
					$strItemFim = '</b>';
					$strSubitemInicio = '<i>';
					$strSubitemFim = '</i>';
					$strEspaco = '&nbsp;';
	
				}else{
	
					$strNovaLinha = "\n";
					$strItemInicio = '';
					$strItemFim = '';
					$strSubitemInicio = '';
					$strSubitemFim = '';
					$strEspaco = ' ';
	
				}
	
				$strResultado = '';
	
				if ($objInfraSessao!=null){
					$bolAcaoDownload = $objInfraSessao->verificarPermissao('documento_download_anexo');
				}
	
				foreach($arrAtributos as $atributo){
	
					$arrValores = $atributo->getElementsByTagName('valores');
	
					if ($arrValores->length==0){
						//nao mostra item que nao possua valor
						if (!InfraString::isBolVazia($atributo->nodeValue)){
							$strResultado .= $strNovaLinha.$strItemInicio.self::formatarTagConteudo($strTipoVisualizacao,$atributo->getAttribute('titulo')).$strItemFim.': '.$strNovaLinha.$strEspaco.$strEspaco.self::formatarTagConteudo($strTipoVisualizacao,$atributo->nodeValue);
							$strResultado .= $strNovaLinha;
						}
					}else{
							
						if ($atributo->getAttribute('titulo')!=''){
							$strResultado .= $strNovaLinha.$strItemInicio.self::formatarTagConteudo($strTipoVisualizacao,$atributo->getAttribute('titulo')).$strItemFim.':';
						}
	
						foreach($arrValores as $valores){
	
							if ($valores->getAttribute('titulo')!=''){
								$strResultado .= $strNovaLinha.$strEspaco.$strEspaco.$strSubitemInicio.self::formatarTagConteudo($strTipoVisualizacao,$valores->getAttribute('titulo')).':'.$strSubitemFim;
							}
	
							$arrValor = $valores->getElementsByTagName('valor');
	
							foreach($arrValor as $valor){
	
								$strResultado .= $strNovaLinha.$strEspaco.$strEspaco.$strEspaco.$strEspaco;
	
								if ($valor->getAttribute('titulo')!=''){
									$strResultado .= self::formatarTagConteudo($strTipoVisualizacao,$valor->getAttribute('titulo')).': ';
								}
									
								if ($valor->getAttribute('tipo')=='ANEXO'){
									if ($objInfraPagina==null || $objInfraSessao==null || $strLinkDownload==null){
										$strResultado .= self::formatarTagConteudo($strTipoVisualizacao,$valor->nodeValue);
									}else {
										if ($bolAcaoDownload){
											$objAnexoDTO = new AnexoDTO();
											$objAnexoDTO->setNumIdAnexo($valor->getAttribute('id'));
											$objAnexoRN = new AnexoRN();
											if ($objAnexoRN->contarRN0734($objAnexoDTO)>0){
												//$strResultado .= '<a href="'.$objInfraPagina->formatarXHTML($objInfraSessao->assinarLink($strLinkDownload.'&id_anexo='.$valor->getAttribute('id'))).'" target="_blank" class="ancoraVisualizacaoDocumento">'.self::formatarTagConteudo($strTipoVisualizacao,$valor->nodeValue).'</a>';
												  $strResultado = '<span>'.self::formatarTagConteudo($strTipoVisualizacao,$valor->nodeValue).'<span>';	
											}else{
												$strResultado .= '<a href="javascript:void(0);" onclick="alert(\'Este anexo foi excluído.\');"  class="ancoraVisualizacaoDocumento">'.self::formatarTagConteudo($strTipoVisualizacao,$valor->nodeValue).'</a>';
											}
										}else{
											$strResultado .= self::formatarTagConteudo($strTipoVisualizacao,$valor->nodeValue);
										}
									}
								}else{
									$strResultado .= self::formatarTagConteudo($strTipoVisualizacao,$valor->nodeValue);
								}
							}
	
							if ($arrValor->length>1){
								$strResultado .= $strNovaLinha;
							}
						}
						$strResultado .= $strNovaLinha;
					}
				}
			}
		}
		return $strResultado;
	}
	
}

?>
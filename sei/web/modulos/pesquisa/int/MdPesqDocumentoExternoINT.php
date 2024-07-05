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
	
				//internamente o DOM utiliza UTF-8 mesmo passando iso-8859-1 por isso e necessario usar utf8_decode
				$objXml = new DomDocument('1.0','iso-8859-1');
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

<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 29/11/2016
 * Versão do Gerador de Código: 1.0
 * Classe mantem dados processo DBCade.
 *
 */

require_once dirname(__FILE__).'/../../../SEI.php';

class MdPesqProtocoloPesquisaPublicaDTO extends InfraDTO {
	
	public function getStrNomeTabela()
	{
		return null;
	}
	
	public function montar()
	{
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'NumeroSEI');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'TipoDocumento');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_DTA, 'Documento');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_DTA, 'Registro');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'Unidade' );
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'StaAssociacao');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ, 'DocumentoDTO');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_OBJ, 'ProcedimentoDTO');
	}
	
}

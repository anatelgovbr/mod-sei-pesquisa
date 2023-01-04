<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-09-29
 * Versão do Gerador de Código: 1.0
 * Versão no CVS/SVN:
 *
 * sei
 * pesquisa
 * Processo DTO
 *
 *
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 */

/**
 * Classe mantem dados processo DBCade.
 *
 *
 * @package institucional_pesquisa_processo_pesquisar
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso sem nenhuma restrição, 
 *         salvo pelas informações a seguir referentes
 * @copyright Conselho Administrativo de Defesa Econômica ©2014-2018
 *            <http://www.cade.gov.br>
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 */


require_once dirname(__FILE__).'/../../../SEI.php';

class MdPesqProtocoloPesquisaPublicaDTO extends InfraDTO {
	
	
	
	public function getStrNomeTabela(){
		return null;
	}
	
	
	
	public function montar(){
		
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

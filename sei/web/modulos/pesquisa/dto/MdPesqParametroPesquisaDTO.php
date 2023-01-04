<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONOMICA
 * 29/11/2016
 * Versão do Gerador de Código: 1.39.0
 *
 */

require_once dirname(__FILE__).'/../../../SEI.php';

class MdPesqParametroPesquisaDTO extends InfraDTO {

  public function getStrNomeTabela()
  {
  	 return 'md_pesq_parametro';
  }

  public function montar()
  {
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Nome',
                                   'nome');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Valor',
                                   'valor');
	
    $this->configurarPK('Nome', InfraDTO::$TIPO_PK_INFORMADO);
  }
  
}
?>

<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONOMICA
 * 29/11/2016
 * Versão do Gerador de Código: 1.39.0
 *
 */

require_once dirname(__FILE__).'/../../../SEI.php';

class MdPesqParametroPesquisaRN extends InfraRN {
  public static $TA_AUTO_COMPLETAR_INTERESSADO = 'AUTO_COMPLETAR_INTERESSADO';
  public static $TA_CAPTCHA = 'CAPTCHA';
  public static $TA_CAPTCHA_PDF = 'CAPTCHA_PDF';
  public static $TA_CHAVE_CRIPTOGRAFIA = 'CHAVE_CRIPTOGRAFIA';
  public static $TA_DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO = 'DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO';
  public static $TA_PESQUISA_DOCUMENTO_PROCESSO_RESTRITO = 'PESQUISA_DOCUMENTO_PROCESSO_RESTRITO';
  public static $TA_LISTA_ANDAMENTO_PROCESSO_PUBLICO = 'LISTA_ANDAMENTO_PROCESSO_PUBLICO';
  public static $TA_LISTA_ANDAMENTO_PROCESSO_RESTRITO = 'LISTA_ANDAMENTO_PROCESSO_RESTRITO';
  public static $TA_LISTA_DOCUMENTO_PROCESSO_PUBLICO = 'LISTA_DOCUMENTO_PROCESSO_PUBLICO';
  public static $TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO = 'LISTA_DOCUMENTO_PROCESSO_RESTRITO';
  public static $TA_MENU_USUARIO_EXTERNO = 'MENU_USUARIO_EXTERNO';
  public static $TA_METADADOS_PROCESSO_RESTRITO = 'METADADOS_PROCESSO_RESTRITO';
  public static $TA_DATA_CORTE = 'DATA_CORTE';

  public function __construct()
  {
    parent::__construct();
  }

  protected function inicializarObjInfraIBanco()
  {
    return BancoSEI::getInstance();
  }
  
  protected function consultarConectado(MdPesqParametroPesquisaDTO $objParametroPesquisaDTO)
  {
  	try {
  
        //Valida Permissao
        SessaoSEI::getInstance()->validarPermissao('md_pesq_parametro_consultar',__METHOD__,$objParametroPesquisaDTO);

        //Regras de Negocio
        //$objInfraException = new InfraException();

  		//$objInfraException->lancarValidacoes();
  
  		$objParametroPesquisaBD = new MdPesqParametroPesquisaBD($this->getObjInfraIBanco());
  		$ret = $objParametroPesquisaBD->consultar($objParametroPesquisaDTO);
  
  		//Auditoria
  		return $ret;
  	}catch(Exception $e){
  		throw new InfraException('Erro consultando Parâmetro da Pesquisa.',$e);
  	}
  }
  
  protected function alterarParametrosControlado($objArrParametroPesquisaDTO)
  {
  	try {
  		
  		// validaPermissao
  		SessaoSEI::getInstance()->validarAuditarPermissao('md_pesq_parametro_alterar',__METHOD__,$objArrParametroPesquisaDTO);
  		foreach ($objArrParametroPesquisaDTO as $objParametroPesquisaDTO){
  			$this->alterar($objParametroPesquisaDTO);
  		}
  		
  	} catch (Exception $e) {
  		throw new InfraException('Erro alterando Configurações da Pesquisa.',$e);
  	}
  }

  protected function alterarControlado(MdPesqParametroPesquisaDTO $objParametroPesquisaDTO)
  {
    try {

      //Valida Permissao
		SessaoSEI::getInstance()->validarAuditarPermissao('md_pesq_parametro_alterar',__METHOD__,$objParametroPesquisaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      if ($objParametroPesquisaDTO->isSetStrNome()){
        $this->validarStrNome($objParametroPesquisaDTO, $objInfraException);
      }
      if ($objParametroPesquisaDTO->isSetStrValor()){
        $this->validarStrValor($objParametroPesquisaDTO, $objInfraException);
      }

      $objInfraException->lancarValidacoes();

      $objParametroPesquisaBD = new MdPesqParametroPesquisaBD($this->getObjInfraIBanco());
      $objParametroPesquisaBD->alterar($objParametroPesquisaDTO);

      //Auditoria
    }catch(Exception $e){
      throw new InfraException('Erro alterando Parâmetro da Pesquisa.',$e);
    }
  }

  protected function listarConectado(MdPesqParametroPesquisaDTO $objParametroPesquisaDTO)
  {
    try {

      //Valida Permissao
      SessaoSEI::getInstance()->validarPermissao('md_pesq_parametro_listar',__METHOD__,$objParametroPesquisaDTO);

      //Regras de Negocio
      //$objInfraException = new InfraException();

      //$objInfraException->lancarValidacoes();

      $objParametroPesquisaBD = new MdPesqParametroPesquisaBD($this->getObjInfraIBanco());
      $ret = $objParametroPesquisaBD->listar($objParametroPesquisaDTO);

      //Auditoria
      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro listando Parâmetro da Pesquisas.',$e);
    }
  }

  protected function consultarVersaoPeticionamentoConectado($versao=null)
  {
	$arrModulos = ConfiguracaoSEI::getInstance()->getValor('SEI','Modulos');
	if(is_array($arrModulos) && array_key_exists('PeticionamentoIntegracao', $arrModulos)){
		$objInfraParametroDTO = new InfraParametroDTO();
		$objInfraParametroDTO->setStrNome('VERSAO_MODULO_PETICIONAMENTO');
		$objInfraParametroDTO->retStrValor();

		$objInfraParametroBD = new InfraParametroBD($this->getObjInfraIBanco());
		$arrObjInfraParametroDTO = $objInfraParametroBD->consultar($objInfraParametroDTO);

		//versao do parametro e igual ou maior que a enviada
		if (!is_null($versao)){
			if ($arrObjInfraParametroDTO){
				$arr_versao_parametro = explode('.',$arrObjInfraParametroDTO->getStrValor());
				$arr_versao = explode('.',$versao);
				for ($i=0;$i<count($arr_versao_parametro);$i++){
					if (isset($arr_versao[$i])) {
						if( intval($arr_versao_parametro[$i]) < intval($arr_versao[$i]) ){
							return null;
						}else if( intval($arr_versao_parametro[$i]) > intval($arr_versao[$i]) ){
							return $arrObjInfraParametroDTO;
						}
					}
				}
			}
		}
		return $arrObjInfraParametroDTO;
	}else{
		return null;
	}
  }

  private function validarStrNome(MdPesqParametroPesquisaDTO $objParametroPesquisaDTO, InfraException $objInfraException)
  {
  	if (InfraString::isBolVazia($objParametroPesquisaDTO->getStrNome())){
  		$objInfraException->adicionarValidacao('Nome não informado.');
  	}else{
  		$objParametroPesquisaDTO->setStrNome(trim($objParametroPesquisaDTO->getStrNome()));
  
  		if (strlen($objParametroPesquisaDTO->getStrNome())>100){
  			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
  		}
  	}
  }
  
  private function validarStrValor(MdPesqParametroPesquisaDTO $objParametroPesquisaDTO, InfraException $objInfraException)
  {
  	if (InfraString::isBolVazia($objParametroPesquisaDTO->getStrValor())){
  		$objParametroPesquisaDTO->setStrValor(null);
  	}else{
  		$objParametroPesquisaDTO->setStrValor(trim($objParametroPesquisaDTO->getStrValor()));
  	}
  }
 
 protected function cadastrarControlado(MdPesqParametroPesquisaDTO $objParametroPesquisaDTO)
 {
    try{

      //Valida Permissao
      SessaoSEI::getInstance()->validarAuditarPermissao('md_pesq_parametro_cadastrar',__METHOD__,$objParametroPesquisaDTO);

      //Regras de Negocio
      $objInfraException = new InfraException();

      $this->validarStrNome($objParametroPesquisaDTO, $objInfraException);
      $this->validarStrValor($objParametroPesquisaDTO, $objInfraException);

      $objInfraException->lancarValidacoes();

      $objParametroPesquisaBD = new MdPesqParametroPesquisaBD($this->getObjInfraIBanco());
      $ret = $objParametroPesquisaBD->cadastrar($objParametroPesquisaDTO);

      //Auditoria
      return $ret;

    }catch(Exception $e){
      throw new InfraException('Erro cadastrando Parâmetro da Pesquisa.',$e);
    }
  }

    protected function existeDataCortePesquisaConectado(){

        $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
        $objParametroPesquisaDTO->setStrNome('DATA_CORTE');
        $objParametroPesquisaDTO->retStrValor();
        $objParametroPesquisaDTO = (new MdPesqParametroPesquisaBD($this->getObjInfraIBanco()))->consultar($objParametroPesquisaDTO);

        if($objParametroPesquisaDTO && !empty($objParametroPesquisaDTO->getStrValor())) {
            $data = explode('-', $objParametroPesquisaDTO->getStrValor());
            if(checkdate($data[1], $data[2], $data[0])){
                if($objParametroPesquisaDTO->getStrValor() <= date('Y-m-d')){
                    return $objParametroPesquisaDTO->getStrValor();
                }
            }
        }

        return false;

    }

}
?>
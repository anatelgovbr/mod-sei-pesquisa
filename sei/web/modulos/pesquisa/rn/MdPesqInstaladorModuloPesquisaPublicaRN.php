<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONOMICA
 *
 * 29/11/2016 - criado por alex braga
 *
 *
 */
 
require_once dirname(__FILE__).'/../../../SEI.php';

class MdPesqInstaladorModuloPesquisaPublicaRN extends InfraRN{
	
	private $numSeg = 0;
	private $versaoAtualDesteModulo = '3.0.0';
	private $nomeDesteModulo = 'Pesquisa Pública';
	private $nomeParametroModulo = 'VERSAO_MODULO_PESQUISA_PUBLICA';
	
	public function __construct(){
		parent::__construct();
	}
	
	protected function inicializarObjInfraIBanco(){
		return BancoSEI::getInstance();
	}
	
	private function inicializar($strTitulo){
	
		ini_set('max_execution_time','0');
		ini_set('memory_limit','-1');
	
		try {
			@ini_set('zlib.output_compression','0');
			@ini_set('implicit_flush', '1');
		}catch(Exception $e){}
	
		ob_implicit_flush();
	
		InfraDebug::getInstance()->setBolLigado(true);
		InfraDebug::getInstance()->setBolDebugInfra(true);
		InfraDebug::getInstance()->setBolEcho(true);
		InfraDebug::getInstance()->limpar();
	
		$this->numSeg = InfraUtil::verificarTempoProcessamento();
	
		$this->logar($strTitulo);
	}
	
	private function logar($strMsg){
		InfraDebug::getInstance()->gravar($strMsg);
		flush();
	}
	
	private function finalizar($strMsg=null, $bolErro){
	
		if (!$bolErro) {
			$this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
			$this->logar('TEMPO TOTAL DE EXECUÇÃO: ' . $this->numSeg . ' s');
		}else{
			$strMsg = 'ERRO: '.$strMsg;
		}
	
		if ($strMsg!=null){
			$this->logar($strMsg);
		}
	
		InfraDebug::getInstance()->setBolLigado(false);
		InfraDebug::getInstance()->setBolDebugInfra(false);
		InfraDebug::getInstance()->setBolEcho(false);
		$this->numSeg = 0;
		
	}
	
	private function instalarv300(){
		
	
						
			$objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
			
			$this->logar('EXECUTANDO A INSTALACAO DA VERSAO 3.0.0 DO NODULO DE PESQUISA PUBLICA NA BASE DO SEI');
			$this->logar('CRIANDO A TABELA md_pesq_parametro');
			
			BancoSEI::getInstance()->executarSql(' CREATE TABLE md_pesq_parametro (
					nome '.$objInfraMetaBD->tipoTextoVariavel(100). ' NOT NULL , 
					valor '.$objInfraMetaBD->tipoTextoGrande().'
					)');
			$objInfraMetaBD->adicionarChavePrimaria('md_pesq_parametro', 'pk_md_pesq_parametro', array('nome'));
			
			$this->logar('TABELA md_pesq_parametro CRIADA COM SUCESSO');
			$this->logar('INSERINDO DADOS NA TABELA md_pesq_parametro');
			
				$arrParametroPesquisaDTO = array(
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_CAPTCHA , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_CAPTCHA_PDF , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_ANDAMENTO_PROCESSO_PUBLICO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_PROCESSO_RESTRITO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_METADADOS_PROCESSO_RESTRITO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_ANDAMENTO_PROCESSO_RESTRITO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO , 'Valor' => 'Processo ou Documento de Acesso Restrito - Para condições de acesso verifique a <a style="font-size: 1em;" href="http://[orgao]/link_condicao_acesso" target="_blank">Condição de Acesso</a> ou entre em contato pelo e-mail: sei@orgao.gov.br'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_DOCUMENTO_PROCESSO_PUBLICO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_PUBLICO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_AUTO_COMPLETAR_INTERESSADO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_MENU_USUARIO_EXTERNO , 'Valor' => 'S'),
      			array('Nome' => MdPesqParametroPesquisaRN::$TA_CHAVE_CRIPTOGRAFIA , 'Valor' => 'ch@c3_cr1pt0gr@f1a'),
      	);
      		
      	$arrObjParametroPesquisaDTO = InfraArray::gerarArrInfraDTOMultiAtributos('MdPesqParametroPesquisaDTO', $arrParametroPesquisaDTO);
      		
      	$objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
      	
      	foreach ($arrObjParametroPesquisaDTO as $objParametroPesquisaDTO){
      		
      		$objParametroPesquisaRN->cadastrar($objParametroPesquisaDTO);
      	}
      	
      
	}
	
	protected function AtualizarVersaoConectado(){
		
		$this->inicializar('INICIANDO ATUALIZACAO DO MODULO DE PESQUISA PUBLICA NO SEI VERSAO '.SEI_VERSAO);
		
		//testando se esta usando BDs suportados
		if (!(BancoSEI::getInstance() instanceof InfraMySql) &&
				!(BancoSEI::getInstance() instanceof InfraSqlServer) &&
				!(BancoSEI::getInstance() instanceof InfraOracle)){
		
			$this->finalizar('BANCO DE DADOS NAO SUPORTADO: '.get_parent_class(BancoSEI::getInstance()),true);
		
		}
		
		//testando permissoes de criacoes de tabelas
		$objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
		
		if (count($objInfraMetaBD->obterTabelas('sei_teste'))==0){
			BancoSEI::getInstance()->executarSql('CREATE TABLE sei_teste (id '.$objInfraMetaBD->tipoNumero().' null)');
		}
		
		BancoSEI::getInstance()->executarSql('DROP TABLE sei_teste');
		
		//checando qual versao instalar
		$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
			
		$strVersaoModuloPesquisa = $objInfraParametro->getValor($this->nomeParametroModulo, false);
			
		if (InfraString::isBolVazia($strVersaoModuloPesquisa)){
			$this->instalarv300();
			//adicionando parametro para controlar versao do modulo
			BancoSei::getInstance()->executarSql('insert into infra_parametro (valor, nome ) VALUES( \''. $this->versaoAtualDesteModulo .'\',  \''. $this->nomeParametroModulo .'\' )' );
			$this->logar('ATUALIZAÇÔES DA VERSÃO ' . $this->versaoAtualDesteModulo .' DO MÓDULO PESQUISA PÚBLICA INSTALADAS COM SUCESSO NA BASE DO SEI');
			$this->finalizar('FIM', false);
		}else{
			$this->logar('SEI - MÓDULO PESQUISA PÚBLICA v' . $this->versaoAtualDesteModulo . ' JÁ INSTALADO');
			$this->finalizar('FIM', false);
		}
	
		
	}

}

?>
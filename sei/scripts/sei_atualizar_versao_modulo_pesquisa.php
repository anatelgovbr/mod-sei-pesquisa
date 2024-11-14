<?
require_once dirname(__FILE__) . '/../web/SEI.php';

class MdPesqAtualizadorSeiRN extends InfraRN
{

    private $numSeg = 0;
    private $versaoAtualDesteModulo = '4.2.0';
    private $nomeDesteModulo = 'MÓDULO DE PESQUISA PÚBLICA';
    private $nomeParametroModulo = 'VERSAO_MODULO_PESQUISA_PUBLICA';
    private $historicoVersoes = array('3.0.0', '4.0.0', '4.0.1', '4.1.0', '4.2.0');

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function inicializar($strTitulo)
    {
        session_start();
        SessaoSEI::getInstance(false);
		
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        @ini_set('implicit_flush', '1');
        ob_implicit_flush();

        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(true);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();

        $this->numSeg = InfraUtil::verificarTempoProcessamento();

        $this->logar($strTitulo);
    }

    protected function logar($strMsg)
    {
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    protected function finalizar($strMsg = null, $bolErro = false)
    {
        if (!$bolErro) {
            $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
            $this->logar('TEMPO TOTAL DE EXECUÇÃO: ' . $this->numSeg . ' s');
        } else {
            $strMsg = 'ERRO: ' . $strMsg;
        }

        if ($strMsg != null) {
            $this->logar($strMsg);
        }

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        $this->numSeg = 0;
        die;
    }

	protected function normalizaVersao($versao)
    {
		$ultimoPonto = strrpos($versao, '.');
		if ($ultimoPonto !== false) {
			$versao = substr($versao, 0, $ultimoPonto) . substr($versao, $ultimoPonto + 1);
		}
		return $versao;
	}

    protected function atualizarVersaoConectado()
    {

        try {
            $this->inicializar('INICIANDO A INSTALAÇÃO/ATUALIZAÇÃO DO ' . $this->nomeDesteModulo . ' NO SEI VERSÃO ' . SEI_VERSAO);

            //checando BDs suportados
            if (!(BancoSEI::getInstance() instanceof InfraMySql) &&
                !(BancoSEI::getInstance() instanceof InfraSqlServer) &&
                !(BancoSEI::getInstance() instanceof InfraOracle) &&
                !(BancoSEI::getInstance() instanceof InfraPostgreSql)) {
                $this->finalizar('BANCO DE DADOS NÃO SUPORTADO: ' . get_parent_class(BancoSEI::getInstance()), true);
            }

            //testando versao do framework
            $numVersaoInfraRequerida = '2.0.18';
            if (version_compare(VERSAO_INFRA, $numVersaoInfraRequerida) < 0) {
                $this->finalizar('VERSÃO DO FRAMEWORK PHP INCOMPATÍVEL (VERSÃO ATUAL ' . VERSAO_INFRA . ', SENDO REQUERIDA VERSÃO IGUAL OU SUPERIOR A ' . $numVersaoInfraRequerida . ')', true);
            }

            //checando permissoes na base de dados
            $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

            if (count($objInfraMetaBD->obterTabelas('sei_teste')) == 0) {
                BancoSEI::getInstance()->executarSql('CREATE TABLE sei_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
            }

            BancoSEI::getInstance()->executarSql('DROP TABLE sei_teste');

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

            $strVersaoModuloPesquisa = $objInfraParametro->getValor($this->nomeParametroModulo, false);

            switch ($strVersaoModuloPesquisa) {
                case '':
                    $this->instalarv300();
                case '3.0.0':
                    $this->instalarv400();
                case '4.0.0':
                    $this->instalarv401();
                case '4.0.1':
                    $this->instalarv410();
	            case '4.1.0':
		            $this->instalarv420();
                    break;

                default:
                    $this->finalizar('A VERSÃO MAIS ATUAL DO ' . $this->nomeDesteModulo . ' (v' . $this->versaoAtualDesteModulo . ') JÁ ESTÁ INSTALADA.');
                    break;

            }

            $this->finalizar('FIM');
            InfraDebug::getInstance()->setBolDebugInfra(true);
        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(true);
            InfraDebug::getInstance()->setBolDebugInfra(true);
            InfraDebug::getInstance()->setBolEcho(true);
            throw new InfraException('Erro instalando/atualizando versão.', $e);
        }
    }

    protected function instalarv300()
    {
        $nmVersao = '3.0.0';
		
        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        $this->logar('CRIANDO A TABELA md_pesq_parametro');
        BancoSEI::getInstance()->executarSql('CREATE TABLE md_pesq_parametro (
					nome ' . $objInfraMetaBD->tipoTextoVariavel(100) . ' NOT NULL ,
					valor ' . $objInfraMetaBD->tipoTextoGrande() . ' NOT NULL
					)');

        $objInfraMetaBD->adicionarChavePrimaria('md_pesq_parametro', 'pk_md_pesq_parametro', array('nome'));

        $this->logar('TABELA md_pesq_parametro CRIADA COM SUCESSO');
        $this->logar('INSERINDO DADOS NA TABELA md_pesq_parametro');

        $arrParametroPesquisaDTO = array(
            array('Nome' => 'CAPTCHA', 'Valor' => 'S'),
            array('Nome' => 'CAPTCHA_PDF', 'Valor' => 'S'),
            array('Nome' => 'LISTA_ANDAMENTO_PROCESSO_PUBLICO', 'Valor' => 'S'),
            array('Nome' => 'PROCESSO_RESTRITO', 'Valor' => 'S'),
            array('Nome' => 'METADADOS_PROCESSO_RESTRITO', 'Valor' => 'S'),
            array('Nome' => 'LISTA_ANDAMENTO_PROCESSO_RESTRITO', 'Valor' => 'S'),
            array('Nome' => 'DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO', 'Valor' => 'Processo ou Documento de Acesso Restrito - Para condições de acesso verifique a <a style="font-size: 1em;" href="http://[orgao]/link_condicao_acesso" target="_blank">Condição de Acesso</a> ou entre em contato pelo e-mail: sei@orgao.gov.br'),
            array('Nome' => 'DOCUMENTO_PROCESSO_PUBLICO', 'Valor' => 'S'),
            array('Nome' => 'LISTA_DOCUMENTO_PROCESSO_PUBLICO', 'Valor' => 'S'),
            array('Nome' => 'LISTA_DOCUMENTO_PROCESSO_RESTRITO', 'Valor' => 'S'),
            array('Nome' => 'AUTO_COMPLETAR_INTERESSADO', 'Valor' => 'S'),
            array('Nome' => 'MENU_USUARIO_EXTERNO', 'Valor' => 'S'),
            array('Nome' => 'CHAVE_CRIPTOGRAFIA', 'Valor' => 'ch@c3_cr1pt0gr@f1a'),
        );

        $arrObjParametroPesquisaDTO = InfraArray::gerarArrInfraDTOMultiAtributos('MdPesqParametroPesquisaDTO', $arrParametroPesquisaDTO);

        $objParametroPesquisaRN = new MdPesqParametroPesquisaRN();

        foreach ($arrObjParametroPesquisaDTO as $objParametroPesquisaDTO) {

            $objParametroPesquisaRN->cadastrar($objParametroPesquisaDTO);
        }
		
		$this->logar('ADICIONANDO PARÂMETRO ' . $this->nomeParametroModulo . ' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');
        BancoSEI::getInstance()->executarSql('INSERT INTO infra_parametro (valor, nome) VALUES( \'3.0.0\',  \'' . $this->nomeParametroModulo . '\' )');
        $this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' REALIZADA COM SUCESSO NA BASE DO SEI');
    }

    protected function instalarv400()
    {
        $nmVersao = '4.0.0';

        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        $objInfraMetaBD->setBolValidarIdentificador(true);

        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        $arrTabelas = array('md_pesq_parametro');

        $this->fixIndices($objInfraMetaBD, $arrTabelas);

        $this->atualizarNumeroVersao($nmVersao);
    }

    protected function instalarv401()
    {
        $nmVersao = '4.0.1';

        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        $this->atualizarNumeroVersao($nmVersao);
    }

    protected function instalarv410()
    {
        $nmVersao = '4.1.0';
        
        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        $objInfraMetaBD->setBolValidarIdentificador(true);

        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        $this->logar('ALTERANDO COLUNA valor NA TABELA md_pesq_parametro PARA ACEITAR VALOR NULO ANTES DE ADICIONAR O PARAMETRO DATA_CORTE');
        if (BancoSEI::getInstance() instanceof InfraOracle) {
        	
            BancoSEI::getInstance()->executarSql('alter table md_pesq_parametro rename column valor to valor_old');
            $objInfraMetaBD->adicionarColuna('md_pesq_parametro', 'valor', $objInfraMetaBD->tipoTextoGrande(), 'NULL');
            BancoSEI::getInstance()->executarSql('UPDATE md_pesq_parametro SET valor = valor_old');
            $objInfraMetaBD->excluirColuna('md_pesq_parametro','valor_old');
            
        } else if (BancoSEI::getInstance() instanceof InfraPostgreSql) {
	
	        BancoSEI::getInstance()->executarSql('ALTER TABLE md_pesq_parametro ALTER COLUMN valor DROP NOT NULL');
	
        }else {
        	
            $objInfraMetaBD->alterarColuna('md_pesq_parametro', 'valor', $objInfraMetaBD->tipoTextoGrande(), 'NULL');
            
        }
        
        $this->logar('INSERINDO NOVO PARÂMETRO "DATA_CORTE" NA TABELA md_pesq_parametro');

        $MdPesqParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
        $MdPesqParametroPesquisaDTO->setStrNome('DATA_CORTE');
        $MdPesqParametroPesquisaDTO->setStrValor(null);
        $MdPesqParametroPesquisaDTO = (new MdPesqParametroPesquisaRN())->cadastrar($MdPesqParametroPesquisaDTO);

        $this->logar('REMOVENDO PARAMETRO "PROCESSO_RESTRITO" NA TABELA md_pesq_parametro');
        $mdPesqParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
        $mdPesqParametroPesquisaDTO->setStrNome('PROCESSO_RESTRITO');
        (new MdPesqParametroPesquisaBD(BancoSEI::getInstance()))->excluir($mdPesqParametroPesquisaDTO);

        $this->logar('ATUALIZANDO NOME DO PARAMETRO "DOCUMENTO_PROCESSO_PUBLICO" PARA "PESQUISA_DOCUMENTO_PROCESSO_RESTRITO"');
        $sqlTabela = 'UPDATE md_pesq_parametro SET nome=\'PESQUISA_DOCUMENTO_PROCESSO_RESTRITO\' WHERE nome =\'DOCUMENTO_PROCESSO_PUBLICO\'';
        BancoSEI::getInstance()->executarSql($sqlTabela);

        $this->atualizarNumeroVersao($nmVersao);
    }
    
    protected function instalarv420(){
	
	    $nmVersao = '4.2.0';
	
	    $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '.$nmVersao.' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');
	
	    $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
	    $objInfraMetaBD->setBolValidarIdentificador(true);
	
	    $this->logar('>>>> MIGRANDO PARÂMETROS DO UTILIDADES PARA O PESQUISA PÚBLICA');
	
	    $arrStrModulos = [ 0 => 'UTILIDADES' , 1 => 'PESQUISA_PUBLICA'];
	
	    $strNomeAtual   = $arrStrModulos[0];
	    $strNomeQueSera = $arrStrModulos[1];
	
	    $arrParametros = array(
		    'MODULO_'.$strNomeAtual.'_BLOQUEAR_BLOQUEAR_PROCESSO_COM_DOCUMENTO_RESTRITO_USANDO_HIPOTESE_LEGAL',
		    'MODULO_'.$strNomeAtual.'_BLOQUEAR_CONCLUIR_PROCESSO_COM_DOCUMENTO_RESTRITO_USANDO_HIPOTESE_LEGAL',
	    );
	
	    $objInfraParametroRN = new InfraParametroRN();
	    $objInfraParametro   = new InfraParametro(BancoSEI::getInstance());
	
	    foreach ( $arrParametros as $str ) {
		
		    $arrNomeParam    = explode( '_' , $str );
		    $arrNomeParam[1] = $strNomeQueSera;
		    $strNovoParam    = implode( '_' , $arrNomeParam );
		
		    if ( $objInfraParametro->isSetValor( $str ) ){
			
			    $vlrParam = $objInfraParametro->getValor( $str );
			
			    // processo para cadastrar o parametro no modulo do peticionamento
			    $objInfraParametroDTO = new InfraParametroDTO();
			    $objInfraParametroDTO->setStrNome($strNovoParam);
			    $objInfraParametroDTO->setStrValor($vlrParam);
			    $objInfraParametroRN->cadastrar($objInfraParametroDTO);
			
			    $this->logar('------------------------------------------------------------------------');
			    $this->logar("Cadastrado o parâmetro: $strNovoParam");
			    $this->logar('------------------------------------------------------------------------');
			
			    // processo para excluir o parametro usado como referencia do modulo utilidades
			    $objInfraParametroDTO = new InfraParametroDTO();
			    $objInfraParametroDTO->setStrNome($str);
			    $objInfraParametroDTO->retTodos();
			    $objInfraParametroDTO = $objInfraParametroRN->listar($objInfraParametroDTO);
			    $objInfraParametroRN->excluir($objInfraParametroDTO);
			    $this->logar('------------------------------------------------------------------------');
			    $this->logar("Excluído o parâmetro: $str");
			    $this->logar('------------------------------------------------------------------------');
			
		    }else{
			
			    // processo para cadastrar o parametro no modulo do peticionamento
			    $objInfraParametroDTO = new InfraParametroDTO();
			    $objInfraParametroDTO->setStrNome($strNovoParam);
			    $objInfraParametroDTO->setStrValor(NULL);
			    $objInfraParametroRN->cadastrar($objInfraParametroDTO);
			
			    $this->logar('------------------------------------------------------------------------');
			    $this->logar("Cadastrado o parâmetro: $strNovoParam");
			    $this->logar('------------------------------------------------------------------------');
		    	
		    }
		
	    }
	    
	    $excluirParametro = 'MODULO_UTILIDADES_BLOQUEAR_GERAR_PROCESSO_SEM_PELO_MENOS_UM_INTERESSADO';
	
	    // processo para excluir o parametro usado como referencia do modulo utilidades
	    $objInfraParametroDTO = new InfraParametroDTO();
	    $objInfraParametroDTO->setStrNome($excluirParametro);
	    $objInfraParametroDTO->retTodos();
	    $objInfraParametroDTO = $objInfraParametroRN->listar($objInfraParametroDTO);
	
	    $objInfraParametroRN->excluir($objInfraParametroDTO);
	    
	    $this->logar('------------------------------------------------------------------------');
	    $this->logar("Excluído o parâmetro: $excluirParametro");
	    $this->logar('------------------------------------------------------------------------');
	    
	    $this->atualizarNumeroVersao($nmVersao);
    	
    }

	protected function fixIndices(InfraMetaBD $objInfraMetaBD, $arrTabelas)
    {
        InfraDebug::getInstance()->setBolDebugInfra(true);
        
        $this->logar('ATUALIZANDO INDICES...');
		
		$objInfraMetaBD->processarIndicesChavesEstrangeiras($arrTabelas);
		
		InfraDebug::getInstance()->setBolDebugInfra(false);
    }

	/**
	 * Atualiza o número de versão do módulo na tabela de parâmetro do sistema
	 *
	 * @param string $parStrNumeroVersao
	 * @return void
	 */
	private function atualizarNumeroVersao($parStrNumeroVersao)	{
		$this->logar('ATUALIZANDO PARÂMETRO '. $this->nomeParametroModulo .' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');

		$objInfraParametroDTO = new InfraParametroDTO();
		$objInfraParametroDTO->setStrNome($this->nomeParametroModulo);
		$objInfraParametroDTO->retTodos();
		$objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
		$arrObjInfraParametroDTO = $objInfraParametroBD->listar($objInfraParametroDTO);

		foreach ($arrObjInfraParametroDTO as $objInfraParametroDTO) {
			$objInfraParametroDTO->setStrValor($parStrNumeroVersao);
			$objInfraParametroBD->alterar($objInfraParametroDTO);
		}
        
		$this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $parStrNumeroVersao .' DO '. $this->nomeDesteModulo .' REALIZADA COM SUCESSO NA BASE DO SEI');
	}

}

try {

    SessaoSEI::getInstance(false);
    BancoSEI::getInstance()->setBolScript(true);

    $configuracaoSEI = new ConfiguracaoSEI();
    $arrConfig = $configuracaoSEI->getInstance()->getArrConfiguracoes();

    if (!isset($arrConfig['SEI']['Modulos'])) {
        throw new InfraException('PARÂMETRO DE MÓDULOS NO CONFIGURAÇÃO DO SEI NÃO DECLARADO');
    } else {
        $arrModulos = $arrConfig['SEI']['Modulos'];
        if (!key_exists('PesquisaIntegracao', $arrModulos)) {
            throw new InfraException('MÓDULO PESQUISA PÚBLICA NÃO DECLARADO NA CONFIGURAÇÃO DO SEI');
        }
    }

    if (!class_exists('PesquisaIntegracao')) {
        throw new InfraException('A CLASSE PRINCIPAL "PesquisaIntegracao" DO MÓDULO NÃO FOI ENCONTRADA');
    }

    InfraScriptVersao::solicitarAutenticacao(BancoSei::getInstance());
    $objVersaoSeiRN = new MdPesqAtualizadorSeiRN();
    $objVersaoSeiRN->atualizarVersao();
    exit;

} catch (Exception $e) {
    echo(InfraException::inspecionar($e));
    try {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
    }
    exit(1);
}
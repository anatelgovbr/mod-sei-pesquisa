<?
require_once dirname(__FILE__) . '/../web/SEI.php';

class MdPesqAtualizadorSeiRN extends InfraRN
{

    private $numSeg = 0;
    private $versaoAtualDesteModulo = '4.0.0';
    private $nomeDesteModulo = 'MÓDULO DE PESQUISA PÚBLICA';
    private $nomeParametroModulo = 'VERSAO_MODULO_PESQUISA_PUBLICA';
    private $historicoVersoes = array('3.0.0', '4.0.0');

    public function __construct()
    {
        parent::__construct();
    }

    protected function getHistoricoVersoes()
    {
        return $this->historicoVersoes;
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    private function inicializar($strTitulo)
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

    private function logar($strMsg)
    {
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    private function finalizar($strMsg = null, $bolErro = false)
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

    protected function atualizarVersaoConectado()
    {

        try {
            $this->inicializar('INICIANDO A INSTALAÇÃO/ATUALIZAÇÃO DO ' . $this->nomeDesteModulo . ' NO SEI VERSÃO ' . SEI_VERSAO);

            //checando BDs suportados
            if (!(BancoSEI::getInstance() instanceof InfraMySql) &&
                !(BancoSEI::getInstance() instanceof InfraSqlServer) &&
                !(BancoSEI::getInstance() instanceof InfraOracle)) {
                $this->finalizar('BANCO DE DADOS NÃO SUPORTADO: ' . get_parent_class(BancoSEI::getInstance()), true);
            }

            //testando versao do framework
            $numVersaoInfraRequerida = '1.532.1';
            $versaoInfraFormatada = (int)str_replace('.', '', VERSAO_INFRA);
            $versaoInfraReqFormatada = (int)str_replace('.', '', $numVersaoInfraRequerida);

            if ($versaoInfraFormatada < $versaoInfraReqFormatada) {
                $this->finalizar('VERSÃO DO FRAMEWORK PHP INCOMPATÍVEL (VERSÃO ATUAL ' . VERSAO_INFRA . ', SENDO REQUERIDA VERSÃO IGUAL OU SUPERIOR A ' . $numVersaoInfraRequerida . ')', true);
            }


            //checando permissoes na base de dados
            $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

            if (count($objInfraMetaBD->obterTabelas('sei_teste')) == 0) {
                BancoSEI::getInstance()->executarSql('CREATE TABLE sei_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
            }

            BancoSEI::getInstance()->executarSql('DROP TABLE sei_teste');

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

            $strVersaoModuloPeticionamento = $objInfraParametro->getValor($this->nomeParametroModulo, false);

            switch ($strVersaoModuloPeticionamento) {
                case '':
                    $this->instalarv300();
                case '3.0.0':
                    $this->instalarv400();
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

    private function instalarv300()
    {
        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO ' . $this->versaoAtualDesteModulo . ' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        $this->logar('CRIANDO A TABELA md_pesq_parametro');

        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_pesq_parametro (
					nome ' . $objInfraMetaBD->tipoTextoVariavel(100) . ' NOT NULL , 
					valor ' . $objInfraMetaBD->tipoTextoGrande() . ' NULL
					)');
        $objInfraMetaBD->adicionarChavePrimaria('md_pesq_parametro', 'pk_md_pesq_parametro', array('nome'));

        $this->logar('TABELA md_pesq_parametro CRIADA COM SUCESSO');
        $this->logar('INSERINDO DADOS NA TABELA md_pesq_parametro');

        $arrParametroPesquisaDTO = array(
            array('Nome' => MdPesqParametroPesquisaRN::$TA_CAPTCHA, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_CAPTCHA_PDF, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_ANDAMENTO_PROCESSO_PUBLICO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_PROCESSO_RESTRITO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_METADADOS_PROCESSO_RESTRITO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_ANDAMENTO_PROCESSO_RESTRITO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO, 'Valor' => 'Processo ou Documento de Acesso Restrito - Para condições de acesso verifique a <a style="font-size: 1em;" href="http://[orgao]/link_condicao_acesso" target="_blank">Condição de Acesso</a> ou entre em contato pelo e-mail: sei@orgao.gov.br'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_DOCUMENTO_PROCESSO_PUBLICO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_PUBLICO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_AUTO_COMPLETAR_INTERESSADO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_MENU_USUARIO_EXTERNO, 'Valor' => 'S'),
            array('Nome' => MdPesqParametroPesquisaRN::$TA_CHAVE_CRIPTOGRAFIA, 'Valor' => null),
        );

        $arrObjParametroPesquisaDTO = InfraArray::gerarArrInfraDTOMultiAtributos('MdPesqParametroPesquisaDTO', $arrParametroPesquisaDTO);

        $objParametroPesquisaRN = new MdPesqParametroPesquisaRN();

        foreach ($arrObjParametroPesquisaDTO as $objParametroPesquisaDTO) {

            $objParametroPesquisaRN->cadastrar($objParametroPesquisaDTO);
        }

        $this->logar('ATUALIZANDO PARÂMETRO ' . $this->nomeParametroModulo . ' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');
        BancoSEI::getInstance()->executarSql('UPDATE infra_parametro SET valor = \'3.0.0\' WHERE nome = \'' . $this->nomeParametroModulo . '\' ');

        $this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO ' . $this->versaoAtualDesteModulo . ' DO ' . $this->nomeDesteModulo . ' REALIZADA COM SUCESSO NA BASE DO SEI');

    }

    private function instalarv400()
    {

        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO ' . $this->versaoAtualDesteModulo . ' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        $objInfraMetaBD->setBolValidarIdentificador(true);

        $this->logar('ALTERANDO A TABELA - alterando md_pesq_parametro.valor para NULL');
        $objInfraMetaBD->alterarColuna('md_pesq_parametro', 'valor', $objInfraMetaBD->tipoTextoGrande(), 'NULL');


        $this->logar('ATUALIZANDO PARÂMETRO ' . $this->nomeParametroModulo . ' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');
        BancoSEI::getInstance()->executarSql('UPDATE infra_parametro SET valor = \'4.0.0\' WHERE nome = \'' . $this->nomeParametroModulo . '\' ');

        $this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO ' . $this->versaoAtualDesteModulo . ' DO ' . $this->nomeDesteModulo . ' REALIZADA COM SUCESSO NA BASE DO SEI');

    }

    protected function fixIndices(InfraMetaBD $objInfraMetaBD, $arrTabelas)
    {
        InfraDebug::getInstance()->setBolDebugInfra(true);

        $this->logar('ATUALIZANDO INDICES...');

        $objInfraMetaBD->processarIndicesChavesEstrangeiras($arrTabelas);

        InfraDebug::getInstance()->setBolDebugInfra(false);
    }

}

try {

    SessaoSEI::getInstance(false);
    BancoSEI::getInstance()->setBolScript(true);

    $configuracaoSEI = new ConfiguracaoSEI();
    $arrConfig = $configuracaoSEI->getInstance()->getArrConfiguracoes();

    if (!isset($arrConfig['SEI']['Modulos'])) {
        throw new InfraException('PARÂMETROS DE MÓDULOS NO CONFIGURAÇÃO DO SEI NÃO DECLARADO');
    } else {
        $arrModulos = $arrConfig['SEI']['Modulos'];
        if (!key_exists('PesquisaIntegracao', $arrModulos)) {
            throw new InfraException('MÓDULO DO PESQUISA PÚBLICA NÃO DECLARADO NA CONFIGURAÇÃO DO SEI');
        }
    }

    if (!class_exists('PesquisaIntegracao')) {
        throw new InfraException('A CLASSE PRINCIPAL "PESQUISAINTEGRACAO" DO MÓDULO DO PESQUISA PÚBLICA NÃO ENCONTRADA');
    }

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

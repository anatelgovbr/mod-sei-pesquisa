<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-09-29
 * Versão do Gerador de Código: 1.0
 * Versão no CVS/SVN:
 *
 * sei
 * pesquisa
 * processo_pesquisar
 *
 *
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 */

/**
 * Pagina de apresentação da página de pesquisa.
 *
 *
 * @package Cade_pesquisa_processo_pesquisar
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso sem nenhuma restrição,
 *         salvo pelas informações a seguir referentes
 * @copyright Conselho Administrativo de Defesa Econômica ©2014-2018
 *            <http://www.cade.gov.br>
 * @author Alex Alves Braga <bsi.alexbraga@gmail.com>
 */


try {
    require_once dirname(__FILE__) . '/../../SEI.php';
    require_once dirname(__FILE__) . '/MdPesqBuscaProtocoloExterno.php';
    require_once("MdPesqConverteURI.php");

//	session_start();

    SessaoSEIExterna::getInstance()->validarSessao();

// 	InfraDebug::getInstance()->setBolLigado(false);
// 	InfraDebug::getInstance()->setBolDebugInfra(false);
// 	InfraDebug::getInstance()->limpar();

    $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
    $objParametroPesquisaDTO->retStrNome();
    $objParametroPesquisaDTO->retStrValor();

    $objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
    $arrObjParametroPesquisaDTO = $objParametroPesquisaRN->listar($objParametroPesquisaDTO);

    $arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO($arrObjParametroPesquisaDTO, 'Valor', 'Nome');

    $bolCaptcha = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_CAPTCHA] == 'S' ? true : false;
    $bolAutocompletarInterressado = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_AUTO_COMPLETAR_INTERESSADO] == 'S' ? true : false;


    //	SessaoSEIExterna::getInstance()->validarLink();
    MdPesqPesquisaUtil::valiadarLink();

    PaginaSEIExterna::getInstance()->setBolXHTML(false);

    if ($bolCaptcha) {
        $strCodigoParaGeracaoCaptcha = InfraCaptcha::obterCodigo();
        $md5Captcha = md5(InfraCaptcha::gerar($strCodigoParaGeracaoCaptcha));
    } else {
        $md5Captcha = null;
    }
    if (isset($_POST['hdnFlagPesquisa']) || isset($_POST['sbmLimpar'])) {

        if (isset($_POST['sbmLimpar'])) {

            PaginaSEIExterna::getInstance()->limparCampos();
            PaginaSEIExterna::getInstance()->salvarCampo('rdoData', '');
            PaginaSEIExterna::getInstance()->salvarCampo('chkSinProcessos', 'P');


        } else {

            PaginaSEIExterna::getInstance()->salvarCampo('chkSinProcessos', $_POST['chkSinProcessos']);
            PaginaSEIExterna::getInstance()->salvarCampo('chkSinDocumentosGerados', $_POST['chkSinDocumentosGerados']);
            PaginaSEIExterna::getInstance()->salvarCampo('chkSinDocumentosRecebidos', $_POST['chkSinDocumentosRecebidos']);

            PaginaSEIExterna::getInstance()->salvarCamposPost(array('q',
                'txtParticipante',
                'hdnIdParticipante',
                'txtAssinante',
                'hdnIdAssinante',
                'txtDescricaoPesquisa',
                'txtObservacaoPesquisa',
                'txtAssunto',
                'hdnIdAssunto',
                'txtUnidade',
                'hdnIdUnidade',
                'txtProtocoloPesquisa',
                'selTipoProcedimentoPesquisa',
                'selSeriePesquisa',
                'txtNumeroDocumentoPesquisa',
                'rdoData',
                'txtDataInicio',
                'txtDataFim',
                'hdnSiglasUsuarios',
                'txtSiglaUsuario1',
                'txtSiglaUsuario2',
                'txtSiglaUsuario3',
                'txtSiglaUsuario4'
            ));

        }


    } else {

        PaginaSEIExterna::getInstance()->salvarCampo('q', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtProtocoloPesquisa', $strProtocoloFormatadoLimpo);
        PaginaSEIExterna::getInstance()->salvarCampo('chkSinProcessos', 'P');
        PaginaSEIExterna::getInstance()->salvarCampo('chkSinDocumentosGerados', '');
        PaginaSEIExterna::getInstance()->salvarCampo('chkSinDocumentosRecebidos', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtParticipante', '');
        PaginaSEIExterna::getInstance()->salvarCampo('hdnIdParticipante', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtAssinante', '');
        PaginaSEIExterna::getInstance()->salvarCampo('hdnIdAssinante', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtDescricaoPesquisa', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtObservacaoPesquisa', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtAssunto', '');
        PaginaSEIExterna::getInstance()->salvarCampo('hdnIdAssunto', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtUnidade', '');
        PaginaSEIExterna::getInstance()->salvarCampo('hdnIdUnidade', '');
        //PaginaSEIExterna::getInstance()->salvarCampo('txtProtocoloPesquisa', '');
        PaginaSEIExterna::getInstance()->salvarCampo('selTipoProcedimentoPesquisa', '');
        PaginaSEIExterna::getInstance()->salvarCampo('selSeriePesquisa', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtNumeroDocumentoPesquisa', '');
        PaginaSEIExterna::getInstance()->salvarCampo('rdoData', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtDataInicio', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtDataFim', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtSiglaUsuario1', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtSiglaUsuario2', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtSiglaUsuario3', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtSiglaUsuario4', '');
        PaginaSEIExterna::getInstance()->salvarCampo('hdnSiglasUsuarios', '');
    }


    switch ($_GET['acao_externa']) {

        case 'protocolo_pesquisar':
        case 'protocolo_pesquisa_rapida':


            $strTitulo = 'Pesquisa Pública';


            // Altero os caracteres 'Coringas' por aspas Duplas para não dar erro de Js no IE
            $strPalavrasPesquisa = str_replace("$*", '\"', PaginaSEIExterna::getInstance()->recuperarCampo('q'));
            $strSinProcessos = PaginaSEIExterna::getInstance()->recuperarCampo('chkSinProcessos');
            $strSinDocumentosGerados = PaginaSEIExterna::getInstance()->recuperarCampo('chkSinDocumentosGerados');
            $strSinDocumentosRecebidos = PaginaSEIExterna::getInstance()->recuperarCampo('chkSinDocumentosRecebidos');
            $strIdParticipante = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdParticipante');
            $strNomeParticipante = PaginaSEIExterna::getInstance()->recuperarCampo('txtParticipante');
            $strIdAssinante = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdAssinante');
            $strNomeAssinante = PaginaSEIExterna::getInstance()->recuperarCampo('txtAssinante');
            $strDescricaoPesquisa = PaginaSEIExterna::getInstance()->recuperarCampo('txtDescricaoPesquisa');
            $strObservacaoPesquisa = PaginaSEIExterna::getInstance()->recuperarCampo('txtObservacaoPesquisa');
            $strIdAssunto = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdAssunto');
            $strDescricaoAssunto = PaginaSEIExterna::getInstance()->recuperarCampo('txtAssunto');
            $strIdUnidade = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdUnidade');
            $strDescricaoUnidade = PaginaSEIExterna::getInstance()->recuperarCampo('txtUnidade');
            $strProtocoloPesquisa = PaginaSEIExterna::getInstance()->recuperarCampo('txtProtocoloPesquisa');
            $numIdTipoProcedimento = PaginaSEIExterna::getInstance()->recuperarCampo('selTipoProcedimentoPesquisa', 'null');
            $numIdSerie = PaginaSEIExterna::getInstance()->recuperarCampo('selSeriePesquisa', 'null');
            $strNumeroDocumentoPesquisa = PaginaSEIExterna::getInstance()->recuperarCampo('txtNumeroDocumentoPesquisa');
            $strStaData = PaginaSEIExterna::getInstance()->recuperarCampo('rdoData');
            $strDataInicio = PaginaSEIExterna::getInstance()->recuperarCampo('txtDataInicio');
            $strDataFim = PaginaSEIExterna::getInstance()->recuperarCampo('txtDataFim');
            $strSiglaUsuario1 = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario1');
            $strSiglaUsuario2 = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario2');
            $strSiglaUsuario3 = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario3');
            $strSiglaUsuario4 = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario4');
            $strUsuarios = PaginaSEIExterna::getInstance()->recuperarCampo('hdnSiglasUsuarios');
            $strParticipanteSolr = '';

            //Opção de Auto Completar Interressado
            if (!$bolAutocompletarInterressado) {

                if (!InfraString::isBolVazia($strNomeParticipante)) {
                    $strParticipanteSolr = MdPesqPesquisaUtil::buscaParticipantes($strNomeParticipante);
                }

            }


            $strDisplayAvancado = 'block';
            $bolPreencheuAvancado = false;
            if (($strSinProcessos == 'P' || $strSinDocumentosGerados == 'G' || $strSinDocumentosRecebidos == 'R') &&
                !InfraString::isBolVazia($strIdParticipante) ||
                !InfraString::isBolVazia($strParticipanteSolr) ||
                !InfraString::isBolVazia($strIdAssinante) ||
                !InfraString::isBolVazia($strDescricaoPesquisa) ||
                !InfraString::isBolVazia($strObservacaoPesquisa) ||
                !InfraString::isBolVazia($strIdAssunto) ||
                !InfraString::isBolVazia($strIdUnidade) ||
                !InfraString::isBolVazia($strProtocoloPesquisa) ||
                !InfraString::isBolVazia($numIdTipoProcedimento) ||
                !InfraString::isBolVazia($numIdSerie) ||
                !InfraString::isBolVazia($strNumeroDocumentoPesquisa) ||
                !InfraString::isBolVazia($strDataInicio) ||
                !InfraString::isBolVazia($strDataFim) ||
                !InfraString::isBolVazia(str_replace(',', '', $strUsuarios))) {

                //$strDisplayAvancado = 'none';
                $bolPreencheuAvancado = true;
            }

            $q = PaginaSEIExterna::getInstance()->recuperarCampo('q');

            $inicio = intval($_REQUEST["inicio"]);

            $strResultado = '';

            if (isset($_POST['sbmPesquisar']) || ($_GET['acao_origem_externa'] == "protocolo_pesquisar_paginado")) {

                $objMdPesqParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
                $mdPesqParametroPesquisaRN = new MdPesqParametroPesquisaRN();
                $objMdPesqParametroPesquisaDTO->setStrNome(MdPesqParametroPesquisaRN::$TA_CHAVE_CRIPTOGRAFIA);
                $objMdPesqParametroPesquisaDTO->retTodos();
                $objMdPesqParametroPesquisaDTO = $mdPesqParametroPesquisaRN->consultar($objMdPesqParametroPesquisaDTO);
                if ($objMdPesqParametroPesquisaDTO->getStrValor() != "" && !is_null($objMdPesqParametroPesquisaDTO->getStrValor())) {
                    if (md5($_POST['txtCaptcha']) != $_POST['hdnCaptchaMd5'] && $_GET['hash'] != $_POST['hdnCaptchaMd5'] && $bolCaptcha == true) {
                        PaginaSEIExterna::getInstance()->setStrMensagem('Código de confirmação inválido.', PaginaSEI::$TIPO_MSG_ERRO);
                    } else {
                        //preencheu palavra de busca ou alguma opção avançada
                        if (!InfraString::isBolVazia($q) || $bolPreencheuAvancado) {

                            try {
                                $strResultado = MdPesqBuscaProtocoloExterno::executar($q, $strDescricaoPesquisa, $strObservacaoPesquisa, $inicio, 100, $strParticipanteSolr, $md5Captcha);
                            } catch (Exception $e) {
                                LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
                                throw new InfraException('Erro realizando pesquisa.', $e);
                            }
                        }
                    }
                } else {
                    PaginaSEIExterna::getInstance()->setStrMensagem('A Pesquisa Pública do SEI está desativada temporariamente por falta de parametrização na sua administração.', PaginaSEI::$TIPO_MSG_ERRO);
                }

            }


            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $strItensSelTipoProcedimento = TipoProcedimentoINT::montarSelectNome('null', '&nbsp;', $numIdTipoProcedimento);
    $strItensSelSerie = SerieINT::montarSelectNomeRI0802('null', '&nbsp;', $numIdSerie);

    $strLinkAjaxContatos = SessaoSEIExterna::getInstance()->assinarLink('md_pesq_controlador_ajax_externo.php?acao_ajax_externo=contato_auto_completar_contexto_pesquisa&id_orgao_acesso_externo=0');
    $strLinkAjaxUnidade = SessaoSEIExterna::getInstance()->assinarLink('md_pesq_controlador_ajax_externo.php?acao_ajax_externo=unidade_auto_completar_todas&id_orgao_acesso_externo=0');

    $strLinkAjuda = PaginaSEIExterna::getInstance()->formatarXHTML(SessaoSEIExterna::getInstance()->assinarLink('md_pesq_ajuda_exibir_externo.php?acao_externa=pesquisa_solr_ajuda_externa&id_orgao_acesso_externo=0'));

    if ($strStaData == '0') {
        $strDisplayPeriodoExplicito = $strDisplayAvancado;
    } else {
        $strDisplayPeriodoExplicito = 'none';
    }

} catch (Exception $e) {
    PaginaSEIExterna::getInstance()->processarExcecao($e);
}
PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(':: ' . PaginaSEIExterna::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEIExterna::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
?>
    .row{margin-top: 10px;}
    .mb-3{margin-bottom:0px !important; width: 50% !important}
    .data{width:auto !important; display: inline-block !important;}
    .infraImgModulo{vertical-align: middle;}
    #txtDataInicio{width:70%;}
    #txtDataFim{width:70%;}
    table.pesquisaResultado {
    border-collapse: collapse;
    border-spacing: 0px;
    font-size: .875rem;
    width: 100%;
    }
    table.pesquisaResultado td {
    padding: .3em .5em;
    }
    table.pesquisaResultado a.protocoloAberto,
    table.pesquisaResultado a.protocoloNormal{
    font-size: .875rem;
    }

    table.pesquisaResultado a.protocoloAberto:hover,
    table.pesquisaResultado a.protocoloNormal:hover{
    text-decoration:underline !important;
    }

    tr.pesquisaTituloRegistro td {
    background: #e0e0e0;
    }

    tr.pesquisaTituloRegistro span {
    font-size: .875rem;
    }

    td.pesquisaTituloEsquerda {
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
    }


    td.pesquisaTituloDireita {
    text-align:right;
    width:20%;
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
    }

    div#conteudo td.pesquisaMetatag {
    color: #333333;
    text-align: center;
    white-space: nowrap;
    padding-bottom: 2em;
    }

    table.pesquisaResultado td.pesquisaMetatag > b {
    color: #006600;
    font-weight: normal;
    }
    td.pesquisaTituloEsquerda img.arvore {
    margin: 0px 5px -3px 0px;
    vertical-align: sub;
    }
<?php
PaginaSEIExterna::getInstance()->fecharStyle();
PaginaSEIExterna::getInstance()->montarJavaScript();
PaginaSEIExterna::getInstance()->adicionarJavaScript('solr/js/sistema.js');
PaginaSEIExterna::getInstance()->abrirJavaScript();
?>


    var objAutoCompletarInteressadoRI1225 = null;
    var objAutoCompletarUsuario = null;
    var objAutoCompletarAssuntoRI1223 = null;
    var objAutoCompletarUnidade = null;


    function inicializar(){

    $('#divInfraBarraSistemaPadraoE').html('<div class="align-self-center"><span id="spnInfraIdentificacaoSistema">Sistema Eletrônico de Informações</span></div>');

    infraOcultarMenuSistemaEsquema();

<? if ($bolAutocompletarInterressado) { ?>

    //Interessado/Remetente
    objAutoCompletarInteressadoRI1225 = new infraAjaxAutoCompletar('hdnIdParticipante','txtParticipante','<?= $strLinkAjaxContatos ?>');
    //objAutoCompletarInteressadoRI1225.maiusculas = true;
    //objAutoCompletarInteressadoRI1225.mostrarAviso = true;
    //objAutoCompletarInteressadoRI1225.tempoAviso = 1000;
    //objAutoCompletarInteressadoRI1225.tamanhoMinimo = 3;
    objAutoCompletarInteressadoRI1225.limparCampo = true;
    //objAutoCompletarInteressadoRI1225.bolExecucaoAutomatica = false;


    objAutoCompletarInteressadoRI1225.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtParticipante').value;
    };
    objAutoCompletarInteressadoRI1225.selecionar('<?= $strIdParticipante; ?>','<?= PaginaSEI::getInstance()->formatarParametrosJavascript($strNomeParticipante) ?>');

<? } ?>

    //Unidades
    objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidade','txtUnidade','<?= $strLinkAjaxUnidade ?>');


    objAutoCompletarUnidade.limparCampo = true;
    objAutoCompletarUnidade.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUnidade').value;
    };
    objAutoCompletarUnidade.selecionar('<?= $strIdUnidade; ?>','<?= PaginaSEIExterna::getInstance()->formatarParametrosJavascript($strDescricaoUnidade) ?>');

    document.getElementById('txtProtocoloPesquisa').focus();


    //remover a string null dos combos
    document.getElementById('selTipoProcedimentoPesquisa').options[0].value='';
    document.getElementById('selSeriePesquisa').options[0].value='';

    infraProcessarResize();


<? if ($strLinkVisualizarSigilosoPublicado != '') { ?>
    infraAbrirJanela('<?= $strLinkVisualizarSigilosoPublicado ?>','janelaSigilosoPublicado',750,550,'location=0,status=1,resizable=1,scrollbars=1',false);
<? } ?>


    sistemaInicializar();


    }


    function tratarPeriodo(valor){
    if (valor=='0'){
    document.getElementById('divPeriodoExplicito').style.display='block';
    document.getElementById('txtDataInicio').value='';
    document.getElementById('txtDataFim').value='';
    }else if (valor =='30'){
    document.getElementById('divPeriodoExplicito').style.display='none';
    document.getElementById('txtDataInicio').value='<?php echo ProtocoloINT::calcularDataInicial(30); ?>';
    document.getElementById('txtDataFim').value='<?php echo date('d/m/Y'); ?>';
    }else if (valor =='60'){
    document.getElementById('divPeriodoExplicito').style.display='none';
    document.getElementById('txtDataInicio').value='<?php echo ProtocoloINT::calcularDataInicial(60); ?>';
    document.getElementById('txtDataFim').value='<?php echo date('d/m/Y'); ?>';
    }
    }

    function sugerirUsuario(obj){
    if (infraTrim(obj.value)==''){
    obj.value = '<?= SessaoSEIExterna::getInstance()->getStrSiglaUsuario() ?>';
    }
    }


    function obterUsuarios(){
    var objHdnUsuarios = document.getElementById('hdnSiglasUsuarios');
    objHdnUsuarios.value = '';

    if (document.getElementById('txtSiglaUsuario1').value != ''){
    if (objHdnUsuarios.value == ''){
    objHdnUsuarios.value += infraTrim(document.getElementById('txtSiglaUsuario1').value);
    }else {
    objHdnUsuarios.value += ',' + infraTrim(document.getElementById('txtSiglaUsuario1').value);
    }
    }
    if (document.getElementById('txtSiglaUsuario2').value != ''){
    if (objHdnUsuarios.value == ''){
    objHdnUsuarios.value += infraTrim(document.getElementById('txtSiglaUsuario2').value);
    }else {
    objHdnUsuarios.value += ',' + infraTrim(document.getElementById('txtSiglaUsuario2').value);
    }
    }
    if (document.getElementById('txtSiglaUsuario3').value != ''){
    if (objHdnUsuarios.value == ''){
    objHdnUsuarios.value += infraTrim(document.getElementById('txtSiglaUsuario3').value);
    }else {
    objHdnUsuarios.value += ',' + infraTrim(document.getElementById('txtSiglaUsuario3').value);
    }
    }
    if (document.getElementById('txtSiglaUsuario4').value != ''){
    if (objHdnUsuarios.value == ''){
    objHdnUsuarios.value += infraTrim(document.getElementById('txtSiglaUsuario4').value);
    }else {
    objHdnUsuarios.value += ',' + infraTrim(document.getElementById('txtSiglaUsuario4').value);
    }
    }
    }


    function onSubmitForm(){

    if (!document.getElementById('chkSinProcessos').checked && !document.getElementById('chkSinDocumentosGerados').checked && !document.getElementById('chkSinDocumentosRecebidos').checked){
    alert('Selecione pelo menos uma das opções de pesquisa avançada: Processos, Documentos Gerados ou Documento Recebidos');
    return false;
    }

    limpaFields();
    return partialFields();

    }

    function exibirAvancado(){

    if (document.getElementById('divAvancado').style.display=='none'){
    document.getElementById('divAvancado').style.display = 'block';

    if (document.getElementById('optPeriodoExplicito').checked){
    document.getElementById('divPeriodoExplicito').style.display='block';
    }else{
    document.getElementById('divPeriodoExplicito').style.display='none';
    }
    document.getElementById('divUsuario').style.display = 'block';

    }else{
    document.getElementById('divAvancado').style.display = 'none';
    document.getElementById('divPeriodoExplicito').style.display='none';
    document.getElementById('divUsuario').style.display='none';
    document.getElementById('txtProtocoloPesquisa').focus();
    }

    infraProcessarResize();
    }

<?
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="seiSearch" name="seiSearch" method="post" onsubmit="return onSubmitForm();"
          action="<?= PaginaSEIExterna::getInstance()->formatarXHTML(SessaoSEIExterna::getInstance()->assinarLink('md_pesq_processo_pesquisar.php?acao_externa=' . $_GET['acao_externa'] . '&acao_origem_externa=' . $_GET['acao_externa'] . $strParametros)) ?>">

        <div class="row">
            <div class="col-sm-12 col-md-9 col-lg-9 col-xl-6">
                <div class="row" id="divGeral">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblProtocoloPesquisa" for="txtProtocoloPesquisa" accesskey=""
                               class="infraLabelOpcional">Nº Processo / Documento:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <input type="text" id="txtProtocoloPesquisa" name="txtProtocoloPesquisa"
                               class="infraText form-control"
                               value="<?= PaginaSEIExterna::tratarHTML($strProtocoloPesquisa); ?>"
                               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                    </div>
                </div>
                <div class="row" id="divAvancado">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblPalavrasPesquisa" for="q" accesskey="" class="infraLabelOpcional">Pesquisa
                            Livre:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <div class="input-group mb-3">
                            <input type="text" id="q" name="q" class="infraText form-control" style="width: 85%"
                                   value="<?= str_replace('\\', '', str_replace('"', '&quot;', PaginaSEIExterna::tratarHTML($strPalavrasPesquisa))) ?>"
                                   tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                            <a id="ancAjuda" href="<?= $strLinkAjuda ?>" target="janAjuda" title="Ajuda para Pesquisa"
                               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>">
                                <img src="<?= PaginaSEIExterna::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg"
                                     class="infraImgModulo"/>
                            </a>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblPesquisarEm" accesskey="" class="infraLabelObrigatorio">Pesquisar em:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <input type="checkbox" id="chkSinProcessos" name="chkSinProcessos" value="P"
                               class="infraCheckbox" <?= ($strSinProcessos == 'P' ? 'checked="checked"' : '') ?>
                               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                        <label id="lblSinProcessos" for="chkSinProcessos" accesskey=""
                               class="infraLabelCheckbox">Processos</label>
                        <input type="checkbox" id="chkSinDocumentosGerados" name="chkSinDocumentosGerados" value="G"
                               class="infraCheckbox" <?= ($strSinDocumentosGerados == 'G' ? 'checked="checked"' : '') ?>
                               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                        <label id="lblSinDocumentosGerados" for="chkSinDocumentosGerados" accesskey=""
                               class="infraLabelCheckbox">Documentos Gerados</label>
                        <input type="checkbox" id="chkSinDocumentosRecebidos" name="chkSinDocumentosRecebidos" value="R"
                               class="infraCheckbox" <?= ($strSinDocumentosRecebidos == 'R' ? 'checked="checked"' : '') ?>
                               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                        <label id="lblSinDocumentosRecebidos" for="chkSinDocumentosRecebidos" accesskey=""
                               class="infraLabelCheckbox">Documentos Externos</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblParticipante" for="txtParticipante" accesskey="" class="infraLabelOpcional">Interessado
                            /
                            Remetente:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <input type="text" id="txtParticipante" name="txtParticipante" class="infraText form-control"
                               value="<?= PaginaSEIExterna::tratarHTML($strNomeParticipante); ?>"
                               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                        <input type="hidden" id="hdnIdParticipante" name="hdnIdParticipante" class="infraText"
                               value="<?= PaginaSEIExterna::tratarHTML($strIdParticipante) ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblUnidade" for="txtUnidade" class="infraLabelOpcional">Unidade Geradora:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <input type="text" id="txtUnidade" name="txtUnidade" class="infraText form-control"
                               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"
                               value="<?= PaginaSEIExterna::tratarHTML($strDescricaoUnidade) ?>"/>
                        <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText"
                               value="<?= PaginaSEIExterna::tratarHTML($strIdUnidade) ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblTipoProcedimentoPesquisa" for="selTipoProcedimentoPesquisa" accesskey=""
                               class="infraLabelOpcional">Tipo do Processo:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <select id="selTipoProcedimentoPesquisa" name="selTipoProcedimentoPesquisa"
                                class="infraSelect form-control"
                                tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelTipoProcedimento ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblSeriePesquisa" for="selSeriePesquisa" accesskey="" class="infraLabelOpcional">Tipo
                            do
                            Documento:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <select id="selSeriePesquisa" name="selSeriePesquisa" class="infraSelect form-control"
                                tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelSerie ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblData" class="infraLabelOpcional">Data entre:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-5">
                                <div class="input-group mb-3 data">
                                    <input type="text" id="txtDataInicio" name="txtDataInicio"
                                           onkeypress="return infraMascaraData(this, event)"
                                           class="infraText"
                                           value="<?= PaginaSEIExterna::tratarHTML($strDataInicio); ?>"
                                           tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                                    <img id="imgDataInicio"
                                         src="<?= PaginaSEIExterna::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg"
                                         onclick="infraCalendario('txtDataInicio',this);" alt="Selecionar Data Inicial"
                                         title="Selecionar Data Inicial" class="infraImgModulo"
                                         tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                                    <label id="lblDataE" for="txtDataE" accesskey=""
                                           class="infraLabelOpcional">&nbsp;e&nbsp;</label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-5">
                                <div class="input-group mb-3 data">
                                    <input type="text" id="txtDataFim" name="txtDataFim"
                                           onkeypress="return infraMascaraData(this, event)"
                                           class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strDataFim); ?>"
                                           tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                                    <img id="imgDataFim"
                                         src="<?= PaginaSEIExterna::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg"
                                         onclick="infraCalendario('txtDataFim',this);"
                                         alt="Selecionar Data Final" title="Selecionar Data Final"
                                         class="infraImgModulo"
                                         tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-4">
                <? if ($bolCaptcha) { ?>
                    <div class="row">
                        <div class="col-sm-12 col-md-10 col-lg-10 col-xl-3">
                            <label id="lblCaptcha" accesskey="" class="infraLabelObrigatorio">
                                <img src="/infra_js/infra_gerar_captcha.php?codetorandom=<?= $strCodigoParaGeracaoCaptcha; ?>"
                                     alt="Não foi possível carregar imagem de confirmação"/> </label>
                        </div>
                    </div>
                <? } ?>
                <? if ($bolCaptcha) { ?>
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-4">
                            <label id="lblCodigo" for="txtCaptcha" accesskey="" class="infraLabelOpcional">Digite o
                                código: </label><br/>
                            <input type="text" id="txtCaptcha" name="txtCaptcha" class="infraText form-control"
                                   maxlength="4"
                                   value=""/><br/>
                        </div>
                    </div>
                <? } ?>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-6">
                        <? if ($bolCaptcha) { ?>
                            <input type="submit" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar"
                                   class="infraButton"/>
                            <input type="submit" id="sbmLimpar" name="sbmLimpar" value="Limpar"
                                   class="infraButton"/>
                        <? } else { ?>
                            <input type="submit" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar"
                                   class="infraButton"/>
                            <input type="submit" id="sbmLimpar" name="sbmLimpar" value="Limpar"
                                   class="infraButton"/>
                        <? } ?>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="txtNumeroDocumentoPesquisa" name="txtNumeroDocumentoPesquisa" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strNumeroDocumentoPesquisa); ?>"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtAssinante" name="txtAssinante" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strNomeAssinante); ?>"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnIdAssinante" name="hdnIdAssinante" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strIdAssinante) ?>"/>
        <input type="hidden" id="txtDescricaoPesquisa" name="txtDescricaoPesquisa" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strDescricaoPesquisa); ?>"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtAssunto" name="txtAssunto" class="infraText"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"
               value="<?= PaginaSEIExterna::tratarHTML($strDescricaoAssunto) ?>"/>
        <input type="hidden" id="hdnIdAssunto" name="hdnIdAssunto" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strIdAssunto) ?>"/>
        <input type="hidden" id="txtSiglaUsuario1" name="txtSiglaUsuario1" onfocus="sugerirUsuario(this);"
               class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario1); ?>"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtSiglaUsuario2" name="txtSiglaUsuario2" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario2); ?>"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtSiglaUsuario3" name="txtSiglaUsuario3" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario3); ?>"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtSiglaUsuario4" name="txtSiglaUsuario4" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario4); ?>"
               tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnSiglasUsuarios" name="hdnSiglasUsuarios" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strUsuarios) ?>"/>
        <input type="hidden" id="hdnSiglasUsuarios" name="hdnSiglasUsuarios" class="infraText"
               value="<?= PaginaSEIExterna::tratarHTML($strUsuarios) ?>"/>
        <? if ($bolCaptcha) { ?>
            <input type="hidden" id="hdnCaptchaMd5" name="hdnCaptchaMd5" class="infraText"
                   value="<?= md5(InfraCaptcha::gerar(PaginaSEIExterna::tratarHTML($strCodigoParaGeracaoCaptcha))); ?>"/>
        <? } ?>
        <input id="partialfields" name="partialfields" type="hidden" value=""/>
        <input id="requiredfields" name="requiredfields" type="hidden" value=""/>
        <input id="as_q" name="as_q" type="hidden" value=""/>

        <input type="hidden" id="hdnFlagPesquisa" name="hdnFlagPesquisa" value="1"/>
        <?

        if ($strResultado != '') {
            echo '<div class="row">';
            echo '<div class="col-sm-12 col-md-12 col-lg-12 col-xl-8">';
            echo '<div id="conteudo" style="width:99%;" class="infraAreaTabela">';
            echo $strResultado;
            echo '</div>';
            echo '</div>';
        }


        PaginaSEIExterna::getInstance()->montarAreaDebug();
        ?>
    </form>
<?
PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>
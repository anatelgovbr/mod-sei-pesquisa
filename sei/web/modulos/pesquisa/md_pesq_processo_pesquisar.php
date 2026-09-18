<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-09-29
 * Versão do Gerador de Código: 1.0
 *
 * Pagina de apresentação da página de pesquisa.
 *
 */

try {
    require_once dirname(__FILE__) . '/../../SEI.php';
	require_once("MdPesqBuscaProtocoloExterno.php");
	require_once("MdPesqPesquisaUtil.php");
    require_once("MdPesqConverteURI.php");
    
    session_start();

    SessaoSEIExterna::getInstance()->validarSessao();

// 	InfraDebug::getInstance()->setBolLigado(false);
// 	InfraDebug::getInstance()->setBolDebugInfra(false);
// 	InfraDebug::getInstance()->limpar();

	$strTitulo = 'Pesquisa Pública';

    $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
    $objParametroPesquisaDTO->retStrNome();
    $objParametroPesquisaDTO->retStrValor();
    $arrObjParametroPesquisaDTO = (new MdPesqParametroPesquisaRN())->listar($objParametroPesquisaDTO);

    $arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO($arrObjParametroPesquisaDTO, 'Valor', 'Nome');

    // Montagem do multiple select de orgaos
    $objOrgaoDTO = new OrgaoDTO();
    $objOrgaoDTO->retNumIdOrgao();
    $objOrgaoDTO->retStrSigla();
    $objOrgaoDTO->retStrDescricao();
    $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $arrObjOrgaoDTO = (new OrgaoRN())->listarRN1353($objOrgaoDTO);

    $numOrgaos = count($arrObjOrgaoDTO);

	if($numOrgaos > 1){

		$arrNumIdOrgao      = !empty($_POST['selOrgaoPesquisa']) && is_array($_POST['selOrgaoPesquisa']) ? array_map('trim', $_POST['selOrgaoPesquisa']) : [];
		$strOptionsOrgaos   = '';

		foreach($arrObjOrgaoDTO as $objOrgaoDTO){

			$strOptionsOrgaos .= '<option value="'.$objOrgaoDTO->getNumIdOrgao().'"';
			if ((count($arrNumIdOrgao) > 0 && in_array($objOrgaoDTO->getNumIdOrgao(), $arrNumIdOrgao)) || (count($arrNumIdOrgao) == 0)){
				$strOptionsOrgaos .= ' selected="selected"';
			}
			$strOptionsOrgaos .= '>'.PaginaPublicacoes::tratarHTML($objOrgaoDTO->getStrSigla()).'</option>'."\n";

		}

    }
	// Final da montagem do multiple select de orgaos

    $bolCaptcha = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_CAPTCHA] == 'S' ? true : false;
    $bolAutocompletarInterressado = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_AUTO_COMPLETAR_INTERESSADO] == 'S' ? true : false;
    $strLinkAjaxPesquisar = SessaoSEIExterna::getInstance()->assinarLink('md_pesq_controlador_ajax_externo.php?acao_ajax_externo=protocolo_pesquisar');

    if (isset($_GET['id_orgao_acesso_externo']) && !ctype_digit((string)$_GET['id_orgao_acesso_externo'])) {
        header('HTTP/1.1 400 Bad Request');
        exit;
    }

    MdPesqPesquisaUtil::valiadarLink();

    PaginaSEIExterna::getInstance()->setBolXHTML(false);

    if ($bolCaptcha) {

        CaptchaSEI::getInstance()->configurarCaptcha($strTitulo);
    }

    $arrNumIdOrgao = [];

    if (isset($_POST['hdnFlagPesquisa']) || isset($_POST['sbmLimpar'])) {

        if (isset($_POST['sbmLimpar'])) {

            PaginaSEIExterna::getInstance()->limparCampos();
            PaginaSEIExterna::getInstance()->salvarCampo('rdoData', '');
            PaginaSEIExterna::getInstance()->salvarCampo('chkSinProcessos', 'P');

        } else {

	        $arrNumIdOrgao = !empty($_POST['selOrgaoPesquisa']) && is_array($_POST['selOrgaoPesquisa']) ? array_map('trim', $_POST['selOrgaoPesquisa']) : [];

            PaginaSEIExterna::getInstance()->salvarCampo('selOrgaoPesquisa', implode(',',$arrNumIdOrgao));
            PaginaSEI::getInstance()->salvarCampo('chkSinRestringirOrgao', $_POST['chkSinRestringirOrgao']);

            PaginaSEIExterna::getInstance()->salvarCampo('chkSinProcessos', $_POST['chkSinProcessos']);
            PaginaSEIExterna::getInstance()->salvarCampo('chkSinDocumentosGerados', $_POST['chkSinDocumentosGerados']);
            PaginaSEIExterna::getInstance()->salvarCampo('chkSinDocumentosRecebidos', $_POST['chkSinDocumentosRecebidos']);

            PaginaSEIExterna::getInstance()->salvarCamposPost(array(
                'q',
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
        PaginaSEIExterna::getInstance()->salvarCampo('selOrgaoPesquisa', '');
        PaginaSEIExterna::getInstance()->salvarCampo('txtProtocoloPesquisa', '');
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

    $captchaValidado = '1'; // 1 = Listagem, 2 = Captcha inválido 3 = Captcha validado
    switch ($_GET['acao_externa']) {

        case 'protocolo_pesquisar':
        case 'protocolo_pesquisa_rapida':

            // Altero os caracteres 'Coringas' por aspas Duplas para não dar erro de Js no IE
            $strPalavrasPesquisa        = str_replace("$*", '\"', PaginaSEIExterna::getInstance()->recuperarCampo('q'));
            $strSinProcessos            = PaginaSEIExterna::getInstance()->recuperarCampo('chkSinProcessos');
            $strSinDocumentosGerados    = PaginaSEIExterna::getInstance()->recuperarCampo('chkSinDocumentosGerados');
            $strSinDocumentosRecebidos  = PaginaSEIExterna::getInstance()->recuperarCampo('chkSinDocumentosRecebidos');
            $strIdParticipante          = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdParticipante');
            $strNomeParticipante        = PaginaSEIExterna::getInstance()->recuperarCampo('txtParticipante');
            $strIdAssinante             = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdAssinante');
            $strNomeAssinante           = PaginaSEIExterna::getInstance()->recuperarCampo('txtAssinante');
            $strDescricaoPesquisa       = PaginaSEIExterna::getInstance()->recuperarCampo('txtDescricaoPesquisa');
            $strObservacaoPesquisa      = PaginaSEIExterna::getInstance()->recuperarCampo('txtObservacaoPesquisa');
            $strIdAssunto               = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdAssunto');
            $strDescricaoAssunto        = PaginaSEIExterna::getInstance()->recuperarCampo('txtAssunto');
            $strIdUnidade               = PaginaSEIExterna::getInstance()->recuperarCampo('hdnIdUnidade');
            $strDescricaoUnidade        = PaginaSEIExterna::getInstance()->recuperarCampo('txtUnidade');
            $strProtocoloPesquisa       = PaginaSEIExterna::getInstance()->recuperarCampo('txtProtocoloPesquisa');
            $numIdTipoProcedimento      = PaginaSEIExterna::getInstance()->recuperarCampo('selTipoProcedimentoPesquisa', 'null');
            $numIdSerie                 = PaginaSEIExterna::getInstance()->recuperarCampo('selSeriePesquisa', 'null');
            $strNumeroDocumentoPesquisa = PaginaSEIExterna::getInstance()->recuperarCampo('txtNumeroDocumentoPesquisa');
            $strStaData                 = PaginaSEIExterna::getInstance()->recuperarCampo('rdoData');
            $strDataInicio              = PaginaSEIExterna::getInstance()->recuperarCampo('txtDataInicio');
            $strDataFim                 = PaginaSEIExterna::getInstance()->recuperarCampo('txtDataFim');
            $strSiglaUsuario1           = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario1');
            $strSiglaUsuario2           = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario2');
            $strSiglaUsuario3           = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario3');
            $strSiglaUsuario4           = PaginaSEIExterna::getInstance()->recuperarCampo('txtSiglaUsuario4');
            $strUsuarios                = PaginaSEIExterna::getInstance()->recuperarCampo('hdnSiglasUsuarios');

            $strParticipanteSolr        = '';
            $q                          = $_POST['q'];
            $inicio                     = intval($_GET['inicio']);
            $rowsSolr                   = intval($_GET['rowsSolr']);
            $id_orgao_acesso_externo    = intval($_GET['id_orgao_acesso_externo']);
            $selOrgaoPesquisa           = $_POST['selOrgaoPesquisa'];

            $arrNumIdOrgaosSelecionados = [];
            if (PaginaSEI::getInstance()->recuperarCampo('selOrgaoPesquisa') != '') {
                $arrNumIdOrgaosSelecionados = explode(',', PaginaSEI::getInstance()->recuperarCampo('selOrgaoPesquisa'));
            }

            $strSinRestringirOrgao = PaginaSEI::getInstance()->recuperarCampo('chkSinRestringirOrgao');

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

                $bolPreencheuAvancado = true;
            }

            $objMdPesqParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
            $objMdPesqParametroPesquisaDTO->setStrNome(MdPesqParametroPesquisaRN::$TA_CHAVE_CRIPTOGRAFIA);
            $objMdPesqParametroPesquisaDTO->retTodos();
            $objMdPesqParametroPesquisaDTO = (new MdPesqParametroPesquisaRN())->consultar($objMdPesqParametroPesquisaDTO);

            $parametrosSolr = [
                'q'                             => $q,
                'strDescricaoPesquisa'          => $strDescricaoPesquisa,
                'strObservacaoPesquisa'         => $strObservacaoPesquisa,
                'inicio'                        => $inicio,
                'rowsSolr'                      => $rowsSolr,
                'strParticipanteSolr'           => $strParticipanteSolr,
                'md5Captcha'                    => null,
                'id_orgao_acesso_externo'       => $id_orgao_acesso_externo,
                'selOrgaoPesquisa'              => $selOrgaoPesquisa,
                'strIdUnidade'                  => $strIdUnidade,
                'numMaxResultados'              => 100,
                'selTipoProcedimentoPesquisa'   => $numIdTipoProcedimento,
                'selSeriePesquisa'              => $numIdSerie,
                'txtDataInicio'                 => $strDataInicio,
                'txtDataFim'                    => $strDataFim,
                'strIdParticipante'             => $strIdParticipante
            ];

            $inicio = intval($_REQUEST["inicio"]);

            $strResultado = '';

            if (!empty($_POST)) {
                $captchaValidado = '3'; // Captcha validado
                if ($bolCaptcha == true && !CaptchaSEI::getInstance()->verificar()) {
                        PaginaSEIExterna::getInstance()->setStrMensagem('Desafio não foi resolvido.', PaginaSEI::$TIPO_MSG_ERRO);
                        $captchaValidado = '2'; // Captcha inválido
                }
	            /*if ($objMdPesqParametroPesquisaDTO->getStrValor() != "" && !is_null($objMdPesqParametroPesquisaDTO->getStrValor())) {
                    if ($bolCaptcha == true && !CaptchaSEI::getInstance()->verificar()) {
                        PaginaSEIExterna::getInstance()->setStrMensagem('Desafio não foi resolvido.', PaginaSEI::$TIPO_MSG_ERRO);
                    } else {
                        //preencheu palavra de busca ou alguma opção avançada
                        if (!InfraString::isBolVazia($q) || $bolPreencheuAvancado) {
                            try {
                                $strResultado = MdPesqBuscaProtocoloExterno::executar($parametrosSolr);
                            } catch (Exception $e) {
                                LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
                                throw new InfraException('Erro realizando pesquisa.', $e);
                            }
                        } else {
                            $xml = '<consultavazia>
                                        <div class="sem-resultado">
                                            <p class="alert alert-warning"> 
                                                Informe parametros para pesquisa.
                                            </p>
                                        </div>
                                    </consultavazia>';
                        }
                    }

                } else {
                    PaginaSEIExterna::getInstance()->setStrMensagem('A Pesquisa Pública do SEI está desativada temporariamente por falta de parametrização na sua administração.', PaginaSEI::$TIPO_MSG_ERRO);
                }*/

            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $strItensSelTipoProcedimento = TipoProcedimentoINT::montarSelectNome('null', '&nbsp;', $numIdTipoProcedimento);
    $strItensSelSerie = SerieINT::montarSelectNomeRI0802('null', '&nbsp;', $numIdSerie);

    $strLinkAjaxContatos = SessaoSEIExterna::getInstance()->assinarLink('md_pesq_controlador_ajax_externo.php?acao_ajax_externo=contato_auto_completar_contexto_pesquisa&id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo']);
    $strLinkAjaxUnidade = SessaoSEIExterna::getInstance()->assinarLink('md_pesq_controlador_ajax_externo.php?acao_ajax_externo=unidade_auto_completar_todas&id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo']);

    $strLinkAjuda = PaginaSEIExterna::getInstance()->formatarXHTML(SessaoSEIExterna::getInstance()->assinarLink('md_pesq_ajuda_exibir_externo.php?acao_externa=pesquisa_publica_ajuda&id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo']));

	$strDisplayPeriodoExplicito = ($strStaData == '0') ? $strDisplayAvancado : 'none';

} catch (Exception $e) {
    PaginaSEIExterna::getInstance()->processarExcecao($e);
}
PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(':: ' . PaginaSEIExterna::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEIExterna::getInstance()->montarStyle();
CaptchaSEI::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
PaginaSEIExterna::getInstance()->fecharStyle();
PaginaSEIExterna::getInstance()->montarJavaScript();
CaptchaSEI::getInstance()->montarJavascript();
PaginaSEIExterna::getInstance()->adicionarJavaScript('solr/js/sistema.js');
PaginaSEIExterna::getInstance()->abrirJavaScript();
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo);
?>
    <!-- Aviso para quando o JavaScript estiver desabilitado -->
    <noscript>
        <div class="alert alert-warning mt-4" role="alert">
            <strong>JavaScript desabilitado:</strong> Para que a pesquisa pública de documentos e processos funcione corretamente é necessário que o JavaScript esteja habilitado nas configurações de seu navegador. Habilite-o e recarregue a página.
        </div>
    </noscript>

    <form id="seiSearch" name="seiSearch" method="post" class="mb-5"
          action="<?= PaginaSEIExterna::getInstance()->formatarXHTML(SessaoSEIExterna::getInstance()->assinarLink('md_pesq_processo_pesquisar.php?acao_externa=' . $_GET['acao_externa'] . '&acao_origem_externa=' . $_GET['acao_externa'] . $strParametros)) ?>"
          onsubmit="return OnSubmitForm();">

        <div class="row">
            <div class="col-sm-12 col-md-9 col-lg-9 col-xl-6">
                <div class="row" id="divGeral">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblProtocoloPesquisa" for="txtProtocoloPesquisa" accesskey=""
                               class="infraLabelOpcional">Nº SEI<br>(protocolo Processo/Documento):</label>
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
                        <label id="lblPalavrasPesquisa" for="q" accesskey="" class="infraLabelOpcional">Texto para Pesquisa:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <div class="input-group mb-0">
                            <input type="text" id="q" name="q" class="infraText form-control" style="width: 85%; margin-right: 2px;"
                                   value="<?= str_replace('\\', '', str_replace('"', '&quot;', PaginaSEIExterna::tratarHTML($strPalavrasPesquisa))) ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                            <a id="ancAjuda" href="<?= $strLinkAjuda ?>" target="janAjuda" title="Ajuda para Pesquisa" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>">
                                <img src="<?= PaginaSEIExterna::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg" class="infraImgModulo"/>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblPesquisarEm" accesskey="" class="infraLabelObrigatorio">Pesquisar em:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <label id="lblSinProcessos" for="chkSinProcessos" accesskey="" class="infraLabelCheckbox">
                            <input type="checkbox" id="chkSinProcessos" name="chkSinProcessos" value="P" class="infraCheckbox" <?= ($strSinProcessos == 'P' ? 'checked="checked"' : '') ?> tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
							Processos
                        </label>
                        <label id="lblSinDocumentosGerados" for="chkSinDocumentosGerados" accesskey="" class="infraLabelCheckbox">
                            <input type="checkbox" id="chkSinDocumentosGerados" name="chkSinDocumentosGerados" value="G" class="infraCheckbox" <?= ($strSinDocumentosGerados == 'G' ? 'checked="checked"' : '') ?> tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
							Documentos Gerados
                        </label>
                        <label id="lblSinDocumentosRecebidos" for="chkSinDocumentosRecebidos" accesskey="" class="infraLabelCheckbox">
                            <input type="checkbox" id="chkSinDocumentosRecebidos" name="chkSinDocumentosRecebidos" value="R" class="infraCheckbox" <?= ($strSinDocumentosRecebidos == 'R' ? 'checked="checked"' : '') ?> tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
							Documentos Externos
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblParticipante" for="txtParticipante" accesskey="" class="infraLabelOpcional">Interessado / Remetente:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <input type="text" id="txtParticipante" name="txtParticipante" class="infraText form-control" value="<?= PaginaSEIExterna::tratarHTML($strNomeParticipante); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
						<input type="hidden" id="hdnIdParticipante" name="hdnIdParticipante" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strIdParticipante) ?>"/>
                    </div>
                </div>

	            <?php if(is_numeric($numOrgaos) && $numOrgaos > 1): ?>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblOrgaoPesquisa" for="selOrgaoPesquisa" accesskey="" class="infraLabelOpcional">Órgão Gerador:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <select multiple id="selOrgaoPesquisa" name="selOrgaoPesquisa[]" onchange="tratarSelecaoOrgao()" class="w-100 infraSelect multipleSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                          <?= $strOptionsOrgaos; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblUnidade" for="txtUnidade" class="infraLabelOpcional">Unidade Geradora:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <input type="text" id="txtUnidade" name="txtUnidade" class="infraText form-control" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>" value="<?= PaginaSEIExterna::tratarHTML($strDescricaoUnidade) ?>"/>
						<input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strIdUnidade) ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblTipoProcedimentoPesquisa" for="selTipoProcedimentoPesquisa" accesskey="" class="infraLabelOpcional">Tipo do Processo:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
                        <select id="selTipoProcedimentoPesquisa" name="selTipoProcedimentoPesquisa" class="infraSelect form-select" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelTipoProcedimento ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <label id="lblSeriePesquisa" for="selSeriePesquisa" accesskey="" class="infraLabelOpcional">Tipo do Documento:</label>
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
						<select id="selSeriePesquisa" name="selSeriePesquisa" class="infraSelect form-select" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>">
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
                            <div class="col-6 col-lg-4 col-xl-5">
                                <div class="input-group mb-3 data">
                                    <input type="text" id="txtDataInicio" name="txtDataInicio" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strDataInicio); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                                    <img id="imgDataInicio" src="<?= PaginaSEIExterna::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg" onclick="infraCalendario('txtDataInicio',this);" alt="Selecionar Data Inicial" title="Selecionar Data Inicial" class="infraImgModulo" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
									<label id="lblDataE" for="txtDataE" accesskey="" class="infraLabelOpcional">&nbsp;e&nbsp;</label>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4 col-xl-5">
                                <div class="input-group mb-3 data">
                                    <input type="text" id="txtDataFim" name="txtDataFim" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strDataFim); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
									<img id="imgDataFim" src="<?= PaginaSEIExterna::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg" onclick="infraCalendario('txtDataFim',this);" alt="Selecionar Data Final" title="Selecionar Data Final" class="infraImgModulo" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                <? if($bolCaptcha){ CaptchaSEI::getInstance()->montarHtml(PaginaSEIExterna::getInstance()->getProxTabDados()); } ?>
                <div class="row js-required">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-6 mt-4 mt-sm-4 mt-xl-0 mt-lg-0 mt-md-0">
                        <input type="submit" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton"/>
                        <input type="reset" id="sbmLimpar" name="sbmLimpar" value="Limpar" class="infraButton"/>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="txtNumeroDocumentoPesquisa" name="txtNumeroDocumentoPesquisa" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strNumeroDocumentoPesquisa); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtAssinante" name="txtAssinante" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strNomeAssinante); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnIdAssinante" name="hdnIdAssinante" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strIdAssinante) ?>"/>
        <input type="hidden" id="txtDescricaoPesquisa" name="txtDescricaoPesquisa" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strDescricaoPesquisa); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtAssunto" name="txtAssunto" class="infraText" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>" value="<?= PaginaSEIExterna::tratarHTML($strDescricaoAssunto) ?>"/>
        <input type="hidden" id="hdnIdAssunto" name="hdnIdAssunto" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strIdAssunto) ?>"/>
        <input type="hidden" id="txtSiglaUsuario1" name="txtSiglaUsuario1" onfocus="sugerirUsuario(this);" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario1); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtSiglaUsuario2" name="txtSiglaUsuario2" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario2); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtSiglaUsuario3" name="txtSiglaUsuario3" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario3); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="txtSiglaUsuario4" name="txtSiglaUsuario4" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strSiglaUsuario4); ?>" tabindex="<?= PaginaSEIExterna::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnSiglasUsuarios" name="hdnSiglasUsuarios" class="infraText" value="<?= PaginaSEIExterna::tratarHTML($strUsuarios) ?>"/>
        <? if ($bolCaptcha) { ?>
            <input type="hidden" id="hdnCId" name="hdnCId" class="infraText" value="<?= $identificadorFormatado; ?>"/>
        <? } ?>
        <input id="partialfields" name="partialfields" type="hidden" value=""/>
        <input id="requiredfields" name="requiredfields" type="hidden" value=""/>
        <input id="as_q" name="as_q" type="hidden" value=""/>
        <input type="hidden" id="hdnFlagPesquisa" name="hdnFlagPesquisa" value="1"/>
    </form>

    <div id="conteudo" class="retorno-ajax" style="width:99%;">
        <table border="0" class="pesquisaResultado">
            <tbody></tbody>
        </table>
        <div class="ajax-loading" style="position: absolute; width: 97%; background: #F8F8F8; padding: 7px 10px 4px; text-align: center; display: none;">
            <div class="d-flex justify-content-center align-items-center">
                <img src="../../../infra_css/svg/aguarde.svg" alt="" style="d-inline-block">
                <span>Pesquisando...</span>
            </div>
        </div>
        <div class="total-registros-infinite"></div>
    </div>
    <? PaginaSEIExterna::getInstance()->montarAreaDebug(); ?>
<?
require_once("md_pesq_processo_pesquisar_css.php");
require_once("md_pesq_processo_pesquisar_js.php");
PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>
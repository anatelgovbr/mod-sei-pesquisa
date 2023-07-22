<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECON�MICA
 * 2014-09-29
 * Vers�o do Gerador de C�digo: 1.0
 * Arquivo para realizar controle requisi��o ajax.
 *
 */

try{
	require_once dirname(__FILE__).'/../../SEI.php';

	session_start();

	InfraAjax::decodificarPost();
	
	//Verificar se precisa mesmo de validacao de sessao
	SessaoSEIExterna::getInstance()->validarSessao();
	
	MdPesqPesquisaUtil::valiadarLink();
	
	switch($_GET['acao_ajax_externo']){
 	
		case 'contato_auto_completar_contexto_pesquisa':
			$objContatoDTO = new ContatoDTO();
			$objContatoDTO->retNumIdContato();
			$objContatoDTO->retStrSigla();
			$objContatoDTO->retStrNome();
			
			$objContatoDTO->setStrPalavrasPesquisa($_POST['palavras_pesquisa']);
			
			if ($numIdGrupoContato!=''){
				$objContatoDTO->setNumIdGrupoContato($_POST['id_grupo_contato']);
			}
			
			$objContatoDTO->setNumMaxRegistrosRetorno(50);
			$objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
			
			$objContatoRN = new ContatoRN();
			$arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);
			$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjContatoDTO,'IdContato', 'Nome');
			break;
			
		case 'unidade_auto_completar_todas':
			$arrObjUnidadeDTO = UnidadeINT::autoCompletarUnidades($_POST['palavras_pesquisa'],true,$_POST['id_orgao']);
			$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO,'IdUnidade', 'Sigla');
			break;

        case 'protocolo_pesquisar':

            $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
            $objParametroPesquisaDTO->retStrNome();
            $objParametroPesquisaDTO->retStrValor();
            $arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO((new MdPesqParametroPesquisaRN())->listar($objParametroPesquisaDTO), 'Valor', 'Nome');

            $bolCaptcha = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_CAPTCHA] == 'S' ? true : false;
            $bolAutocompletarInterressado = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_AUTO_COMPLETAR_INTERESSADO] == 'S' ? true : false;

            // Altero os caracteres 'Coringas' por aspas Duplas para n�o dar erro de Js no IE
            $strPalavrasPesquisa        = str_replace("$*", '\"', $_POST['q']);
            $strSinProcessos            = $_POST['chkSinProcessos'];
            $strSinDocumentosGerados    = $_POST['chkSinDocumentosGerados'];
            $strSinDocumentosRecebidos  = $_POST['chkSinDocumentosRecebidos'];
            $strIdParticipante          = $_POST['hdnIdParticipante'];
            $strNomeParticipante        = $_POST['txtParticipante'];
            $strIdAssinante             = $_POST['hdnIdAssinante'];
            $strNomeAssinante           = $_POST['txtAssinante'];
            $strDescricaoPesquisa       = $_POST['txtDescricaoPesquisa'];
            $strObservacaoPesquisa      = $_POST['txtObservacaoPesquisa'];
            $strIdAssunto               = $_POST['hdnIdAssunto'];
            $strDescricaoAssunto        = $_POST['txtAssunto'];
            $strIdUnidade               = $_POST['hdnIdUnidade'];
            $strDescricaoUnidade        = $_POST['txtUnidade'];
            $strProtocoloPesquisa       = $_POST['txtProtocoloPesquisa'];
            $numIdTipoProcedimento      = $_POST['selTipoProcedimentoPesquisa'] ?? null;
            $numIdSerie                 = $_POST['selSeriePesquisa'] ?? null;
            $strNumeroDocumentoPesquisa = $_POST['txtNumeroDocumentoPesquisa'];
            $strStaData                 = $_POST['rdoData'];
            $strDataInicio              = $_POST['txtDataInicio'];
            $strDataFim                 = $_POST['txtDataFim'];
            $strSiglaUsuario1           = $_POST['txtSiglaUsuario1'];
            $strSiglaUsuario2           = $_POST['txtSiglaUsuario2'];
            $strSiglaUsuario3           = $_POST['txtSiglaUsuario3'];
            $strSiglaUsuario4           = $_POST['txtSiglaUsuario4'];
            $strUsuarios                = $_POST['hdnSiglasUsuarios'];
            $strParticipanteSolr        = '';
            $q                          = $_POST['q'];
            $inicio                     = intval($_GET['inicio']);
            $rowsSolr                   = intval($_GET['rowsSolr']);

            //Op��o de Auto Completar Interressado
            if (!$bolAutocompletarInterressado) {
                if (!InfraString::isBolVazia($strNomeParticipante)) {
                    $strParticipanteSolr = MdPesqPesquisaUtil::buscaParticipantes($strNomeParticipante);
                }
            }

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

            if ($objMdPesqParametroPesquisaDTO->getStrValor() != "" && !is_null($objMdPesqParametroPesquisaDTO->getStrValor())) {
                if (($bolCaptcha == true && sha1(mb_strtoupper($_POST['txtInfraCaptcha'])) != $_POST['hdnCaptchaSha1']) && $_GET['isPaginacao'] == 'false') {
                    $xml = '<consultavazia><div class="sem-resultado"><p class="alert alert-danger">C�digo de confirma��o inv�lido.</p></div></consultavazia>';
                } else {
                    if (!InfraString::isBolVazia($q) || $bolPreencheuAvancado) {
                        try {
                            $xml = MdPesqBuscaProtocoloExterno::executar($q, $strDescricaoPesquisa, $strObservacaoPesquisa, $inicio, $rowsSolr, $strParticipanteSolr, null);
                        } catch (Exception $e) {
                            LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
                            throw new InfraException('Erro realizando pesquisa.', $e);
                        }
                    }
                }
            } else {
                $xml = '<consultavazia><div class="sem-resultado"><p class="alert alert-danger">A Pesquisa P�blica do SEI est� desativada temporariamente por falta de parametriza��o na sua administra��o.</p></div></consultavazia>';
            }
            break;

        case 'protocolo_pesquisar_captcha_reload':
            $xml = '';

            $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
            $objParametroPesquisaDTO->retStrNome();
            $objParametroPesquisaDTO->retStrValor();
            $arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO((new MdPesqParametroPesquisaRN())->listar($objParametroPesquisaDTO), 'Valor', 'Nome');

            if ($arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_CAPTCHA] == 'S') {
                $strCodigoParaGeracaoCaptcha = InfraCaptcha::obterCodigo();
                $md5Captcha = md5(InfraCaptcha::gerar($strCodigoParaGeracaoCaptcha));
                $srcImgCaptcha = '/infra_js/infra_gerar_captcha.php?codetorandom='.$strCodigoParaGeracaoCaptcha.'&i='.$_GET['i'];
                $xml = '<captcha><scrImgCaptcha>'.$srcImgCaptcha.'</scrImgCaptcha><md5Captcha>'.$md5Captcha.'</md5Captcha></captcha>';
            }
            break;

		case 'get_captcha_code':
			$xml = '<captcha>'.sha1(mb_strtoupper($_SESSION['INFRA_CAPTCHA_V2_'.$_GET['i']])).'</captcha>';
			break;
		
		default:
			throw new InfraException("A��o '".$_GET['acao_ajax_externo']."' n�o reconhecida pelo controlador AJAX externo.");
	}

InfraAjax::enviarXML($xml);

}catch(Exception $e){
	//LogSEI::getInstance()->gravar('ERRO AJAX: '.$e->__toString());
	InfraAjax::processarExcecao($e);
}
?>
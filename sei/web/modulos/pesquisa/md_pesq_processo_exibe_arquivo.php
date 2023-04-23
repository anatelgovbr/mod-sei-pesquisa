<?

/** CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA **/

try {

	require_once dirname(__FILE__).'/../../SEI.php';

	SessaoSEIExterna::getInstance()->validarSessao();
	MdPesqConverteURI::converterURI();
	MdPesqPesquisaUtil::valiadarLink();

	switch($_GET['acao_externa']){

		case 'usuario_externo_exibir_arquivo':

			AuditoriaSEI::getInstance()->auditar('usuario_externo_exibir_arquivo', __FILE__);

			header("Pragma: public");
			header('Pragma: no-cache');
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private","false");

			$strNomeDownload = InfraString::isBolVazia($_GET['nome_download']) ? $_GET['nome_arquivo'] : $_GET['nome_download'];
			PaginaSEI::getInstance()->montarHeaderDownload($strNomeDownload,'attachment');

			$fp = fopen(DIR_SEI_TEMP.'/'.$_GET['nome_arquivo'], "rb");
			while (!feof($fp)) {
				echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
			}
			fclose($fp);

			break;

		default:

			throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");

	}

}catch(Exception $e){

	try{ LogSEI::getInstance()->gravar(InfraException::inspecionar($e)."\n".'$_GET: '.print_r($_GET,true)); }catch(Exception $e2){}
	die('Erro exibindo arquivo em acesso externo.');

}

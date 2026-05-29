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

			$strNomeArquivo = isset($_GET['nome_arquivo']) ? trim($_GET['nome_arquivo']) : '';
			$bolNomeArquivoInvalido = (
				$strNomeArquivo === '' ||
				preg_match('/^[a-zA-Z0-9._-]+$/', $strNomeArquivo) !== 1 ||
				strpos($strNomeArquivo, '/') !== false ||
				strpos($strNomeArquivo, '\\') !== false
			);

			if ($bolNomeArquivoInvalido) {
				AuditoriaSEI::getInstance()->auditar(
					'usuario_externo_exibir_arquivo_tentativa_invalida',
					__FILE__,
					'Parametro nome_arquivo invalido. hash='.substr(hash('sha256', $strNomeArquivo), 0, 16)
				);
				throw new InfraException('Arquivo indisponivel.');
			}

			$strDiretorioTempReal = realpath(DIR_SEI_TEMP);
			if ($strDiretorioTempReal === false) {
				throw new InfraException('Arquivo indisponivel.');
			}

			$strCaminhoArquivo = $strDiretorioTempReal.DIRECTORY_SEPARATOR.$strNomeArquivo;
			$strCaminhoArquivoReal = realpath($strCaminhoArquivo);
			$bolCaminhoInvalido = (
				$strCaminhoArquivoReal === false ||
				strpos($strCaminhoArquivoReal, $strDiretorioTempReal.DIRECTORY_SEPARATOR) !== 0
			);

			if ($bolCaminhoInvalido) {
				AuditoriaSEI::getInstance()->auditar(
					'usuario_externo_exibir_arquivo_tentativa_invalida',
					__FILE__,
					'Caminho de arquivo invalido. hash='.substr(hash('sha256', $strNomeArquivo), 0, 16)
				);
				throw new InfraException('Arquivo indisponivel.');
			}

			if (!is_file($strCaminhoArquivoReal) || !is_readable($strCaminhoArquivoReal)) {
				throw new InfraException('Arquivo indisponivel.');
			}

			header("Pragma: public");
			header('Pragma: no-cache');
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private","false");

			$strNomeDownload = InfraString::isBolVazia($_GET['nome_download']) ? $strNomeArquivo : $_GET['nome_download'];
			PaginaSEI::getInstance()->montarHeaderDownload($strNomeDownload,'attachment');

			$fp = fopen($strCaminhoArquivoReal, "rb");
			while (!feof($fp)) {
				echo fread($fp, TAM_BLOCO_LEITURA_ARQUIVO);
			}
			fclose($fp);

			break;

		default:

			throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");

	}

}catch(Exception $e){

	try{
		$arrGetChaves = isset($_GET) ? array_keys($_GET) : array();
		LogSEI::getInstance()->gravar(
			InfraException::inspecionar($e)."\n".
			'$_GET_chaves: '.implode(',', $arrGetChaves)
		);
	}catch(Exception $e2){}
	
	die('Erro exibindo arquivo em acesso externo.');

}

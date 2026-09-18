<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-10-02
 * Versão do Gerador de Código: 1.0
 * Arquivo para conversão de url
 *
 */

require_once("MdPesqCriptografia.php");
class MdPesqConverteURI{
	
	public static function converterURI()
	{
		try {
			$strQuery = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
			if (!is_string($strQuery) || $strQuery === '') {
				throw new InfraException('Parametros criptografados ausentes.');
			}

			$arrQuery = [];
			parse_str($strQuery, $arrQuery);

			if (isset($arrQuery[MdPesqCriptografia::PARAMETRO_LINK])) {
				$strParametrosCriptografados = $arrQuery[MdPesqCriptografia::PARAMETRO_LINK];
			} else {
				$strParametrosCriptografados = explode('&', $strQuery, 2)[0];
				if (strpos($strParametrosCriptografados, '=') !== false) {
					throw new InfraException('Parametros criptografados ausentes.');
				}
			}

			if (!is_string($strParametrosCriptografados) || $strParametrosCriptografados === '') {
				throw new InfraException('Parametros criptografados invalidos.');
			}

			$arrParametros = MdPesqCriptografia::descriptografa($strParametrosCriptografados);
			if (!is_string($arrParametros) || $arrParametros === '') {
				throw new InfraException('Parametros criptografados invalidos.');
			}

			$novosParametros = [];
			parse_str($arrParametros, $novosParametros);

			if (empty($novosParametros)) {
				throw new InfraException('Parametros criptografados invalidos.');
			}

			$new_query_string = http_build_query($novosParametros);
			$_SERVER['REQUEST_URI'] = strtok($_SERVER['REQUEST_URI'], '?').'?'.$new_query_string;
			$_SERVER['QUERY_STRING'] = $new_query_string;
			$_GET = $novosParametros;
		}catch (Exception $e){
			throw new InfraException('Erro validando url.', $e);
		}
	}
}

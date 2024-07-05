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
			$arr = explode('?', $_SERVER['REQUEST_URI']);
			$arrParametros = MdPesqCriptografia::descriptografa($arr[1]);
			$parametros = explode('&', $arrParametros);
			$chaves = array();
			$valores = array();
			foreach ($parametros as $parametro){
				$arrChaveValor = explode('=', $parametro);
				$chaves[] = $arrChaveValor[0];
				$valores[] = $arrChaveValor[1];
			}
			$novosParametros = array_combine($chaves, array_values($valores));
			$new_query_string = http_build_query($novosParametros);
			$_SERVER['REQUEST_URI'] = $arr[0].'?'.$new_query_string;
			$_GET = $novosParametros;
		}catch (Exception $e){
			throw new InfraException('Erro validando url.', $e);
		}
	}
}

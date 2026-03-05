<?
/**
 * CONSELHO ADMINISTRATIVO DE DEFESA ECONÔMICA
 * 2014-09-29
 * Versão do Gerador de Código: 1.0
 *
 * Arquivo para realizar criptografia de parametros.
 *
 */

class MdPesqCriptografia{

	private static $KEY = 'c@d3s3mp@p3l';

	public static function criptografa($texto)
	{
	    try {
	        return strtr(
	            base64_encode(
	                openssl_encrypt(
	                    serialize($texto),
	                    'aes-256-cbc',
	                    md5(MdPesqCriptografia::getChaveCriptografia(), true),
	                    OPENSSL_RAW_DATA,
	                    md5(md5(MdPesqCriptografia::getChaveCriptografia()), true)
	                )
	            ),
	            '+/=', '-_,'
	        );
	    } catch (Exception $e) {

	        throw new InfraException('Erro validando link externo.',$e);
	    }
	}

	public static function descriptografa($texto)
	{
	    try {
	        return unserialize(
	            rtrim(
	                openssl_decrypt(
	                    base64_decode(strtr($texto, '-_,', '+/=')),
	                    'aes-256-cbc',
	                    md5(MdPesqCriptografia::getChaveCriptografia(), true),
	                    OPENSSL_RAW_DATA,
	                    md5(md5(MdPesqCriptografia::getChaveCriptografia()), true)
	                ),
	                "\0"
	            )
	        );
	    } catch (Exception $e) {

	        throw new InfraException('Erro validando link externo.',$e);
	    }
	}

	private static function getChaveCriptografia()
	{
	    $objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
	    $objParametroPesquisaDTO->setStrNome(MdPesqParametroPesquisaRN::$TA_CHAVE_CRIPTOGRAFIA);
	    $objParametroPesquisaDTO->retStrValor();
	    $objParametroPesquisaDTO->retStrNome();

	    $objParametroPesquisaRN = new MdPesqParametroPesquisaRN();

	    $objParametroPesquisaDTO = $objParametroPesquisaRN->consultar($objParametroPesquisaDTO);

	    if($objParametroPesquisaDTO != null && !empty($objParametroPesquisaDTO->getStrValor())){

	        return $objParametroPesquisaDTO->getStrValor();
	    }

	    return $KEY;
	}

	//Alterar metodo de criptografia por uma criptografia php
	public static function criptografaSiteCade($texto)
	{
	    $caminho = dirname(__FILE__).'/criptografia/mascaraArgumentos.jar';
	    $instrucao = 'java -jar '.$caminho.' \'criptografa\' \''.$texto.'\' \''.MdPesqCriptografia::$KEY.'\' ';

	    exec( $instrucao, $saida, $retorno);
	    if($retorno !=0){
	        throw new InfraException('Erro validando link externo.',$e);
	    }else{
	        return $saida;
	    }
	}

	//Alterar metodo de criptografia por uma criptografia php
	public static function descriptografaSiteCade($texto)
	{
	    $caminho = dirname(__FILE__).'/criptografia/mascaraArgumentos.jar';
	    $instrucao = 'java -jar '.$caminho.' \'descriptografa\' \''.$texto.'\' \''.MdPesqCriptografia::$KEY.'\' ';

	    exec( $instrucao, $saida, $retorno);
	    if($retorno !=0){
	        throw new InfraException('Erro validando link externo.',$e);
	    }else{
	        return $saida;
	    }
	}

	//Alterar metodo de criptografia por uma criptografia php
	public static function descriptografaArgumentos($parametro)
	{
	    $parametrosCriptografados = $_SERVER['QUERY_STRING'];
	    $parametrosDescriptografados = MdPesqCriptografia::descriptografa($parametrosCriptografados);
	    $arrParametros = explode("&", $parametrosDescriptografados[0]);
	    $bolRecuperParametro = false;
	    $valorParametro = '';
	    foreach ($arrParametros as $arrParametro){
	        $parametroRetorno = explode("=", $arrParametro);
	        if($parametroRetorno[0] == $parametro){
	            $bolRecuperParametro = true;
	            $valorParametro = $parametroRetorno[1];
	            break;
	        }else{
	            $bolRecuperParametro = false;
	        }
	    }
	    if($bolRecuperParametro){
	        return $valorParametro;
	    }else{
	        throw new InfraException('Erro recuperando parâmetro.');
	    }
	}
}

<script type="text/javascript">

    function inicializar(){

  <?if ($bolGeracaoOK){?>
    
  	window.open('<?=SessaoSEI::getInstance()->assinarLink('md_pesq_processo_exibe_arquivo.php?'.MdPesqCriptografia::criptografa('acao_externa=usuario_externo_exibir_arquivo&acao_origem_externa=protocolo_pesquisar&id_orgao_acesso_externo='.$_GET['id_orgao_acesso_externo'].'&nome_arquivo='.$objAnexoDTO->getStrNome().'&nome_download=SEI-'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'.pdf'));?>');
  	
  <?}?>

  infraEfeitoTabelas();

}

<?
if($bolCaptchaGerarPdf){ 
?>

$(document).unbind("keyup").keyup(function(e){
	e.preventDefault();
    var code = e.which;
    if(code==13){
    	var modal = document.getElementById('divInfraModal');
        if(modal.style.display == "block"){
        	fecharPdfModal();
        	gerarPdf();
    		
    	}
    }
});

function gerarPdfModal(){

	if (document.getElementById('hdnInfraItensSelecionados').value==''){
    	alert('Nenhum documento selecionado.');
    	return;
  	}

    document.getElementById('divInfraModal').style.display = "block";
    document.getElementById('txtInfraCaptcha').focus();

}

function fecharPdfModal(){
	
    var modal = document.getElementById('divInfraModal');
	modal.style.display = "none";
}

window.onclick = function(event) {
	var modal = document.getElementById('divInfraModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

<?
}
?>

function gerarPdf() {
    if (document.getElementById('hdnInfraItensSelecionados').value == ''){
        alert('Nenhum documento selecionado.');
        return false;
    }

<? if($bolCaptchaGerarPdf): ?>

    if (document.getElementById('txtInfraCaptcha').value.length != 6){
        $('.modal-alert-msg').html('<p class="alert alert-warning">Informe o código de confirmação!</p>');
        document.getElementById('txtInfraCaptcha').focus();
        return false;
    }

    if(document.getElementById('hdnInfraItensSelecionados').value != '' && document.getElementById('txtInfraCaptcha').value.length == 6){
        fecharPdfModal();
        infraExibirAviso(false);
        document.getElementById('hdnFlagGerar').value = '1';
        document.getElementById('frmProcessoAcessoExternoConsulta').submit();
    }
<? else: ?>

    document.getElementById('hdnFlagGerar').value = '1';
    document.getElementById('frmProcessoAcessoExternoConsulta').submit();

<? endif; ?>
}


</script>

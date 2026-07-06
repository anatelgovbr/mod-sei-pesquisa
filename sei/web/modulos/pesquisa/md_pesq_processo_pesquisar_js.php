<script type="text/javascript">


var objAutoCompletarInteressadoRI1225 = null;
var objAutoCompletarUnidade = null;

function inicializar(){

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
        var orgaosSelecionados = obterOrgaosSelecionados();
        if(orgaosSelecionados == null){
            return 'palavras_pesquisa='+document.getElementById('txtUnidade').value;
        }else{
            return 'palavras_pesquisa='+document.getElementById('txtUnidade').value+'&id_orgao='+obterOrgaosSelecionados();
        }
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

inicializar();

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

$(document).ready(function(){

    updateCaptcha();

    var paginar     = true;
    var formChanged = false;
    var buscaInicio = 0;
    var rowsSolr    = 50;
    var qtdeItens   = 0;
    var consultaVazia = false;

    partialFields();

    var initdata = $('#seiSearch').serialize();

    $('#seiSearch').on('keyup change paste', 'input, select, textarea', function(){
        formChanged = pesquisar = true;
    });

    var timer;
    $('#divInfraAreaTelaD').on('scroll', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            if(($('#divInfraAreaTelaD').prop('scrollHeight') - $('#divInfraAreaTelaD').scrollTop()) <= Math.ceil($('#divInfraAreaTelaD').height()) && paginar) {
                carregarProximaPagina();
            }
        }, 500);
    });

    if (<?= $captchaValidado ?> == '3') {
        $('input[name=partialfields]').val('');
        partialFields();
        buscaInicio = 0;
        qtdeItens = 0;
        paginar = true;

        $('.retorno-ajax > table > tbody tr, .sem-resultado').remove();
        $('.total-registros-infinite').empty();

        carregarProximaPagina();
    }

    $('body').on('reset', '#seiSearch', function(e){
        $('.retorno-ajax > table > tbody tr, .sem-resultado').remove();
        $('.total-registros-infinite').empty();
        $('input[name=txtProtocoloPesquisa]').focus();
        pesquisar = true;
    });

    function updateCaptcha(){
        $('#infraImgRecarregarCaptcha').trigger('click');
        $('#txtInfraCaptcha').val('');
    }

    // Função para carregar mais resultados
    function carregarProximaPagina(){
        $('.ajax-loading').show();

        $.post('<?= $strLinkAjaxPesquisar ?>&isPaginacao=true&inicio=' + buscaInicio + '&rowsSolr=' + rowsSolr, $('#seiSearch').serialize())
            .done(function(data){
                consultaVazia = false;

                if (data.itens > 0) {
                    // Guarda o total real do Solr na primeira resposta valida
                    if (qtdeItens == 0) {
                        qtdeItens = data.itens;
                    }

                    // Verifica se veio HTML util (nao vazio e sem tag consultavazia)
                    var htmlUtil = data.html && $.trim(data.html) !== '' && data.html.indexOf('<consultavazia>') === -1;

                    if (htmlUtil) {
                        $('.retorno-ajax > table > tbody:last-child').append(data.html);
                    } else {
                        // Pagina inteira filtrada no servidor - marcar pra tentar proxima
                        consultaVazia = true;
                    }
                } else {
                    // Solr retornou 0 resultados (consultavazia real)
                    consultaVazia = true;
                }

                buscaInicio += rowsSolr;
            })
            .always(function() {
                $('.ajax-loading').hide();
                updateCaptcha();

                if (consultaVazia && buscaInicio < qtdeItens) {
                    // Ainda ha paginas no Solr para tentar - buscar proxima
                    carregarProximaPagina();
                } else if (consultaVazia && $('.retorno-ajax table tbody tr').length === 0 && $('.sem-resultado').length === 0) {
                    // Esgotou todas as paginas e nao exibiu nenhum resultado - mostrar mensagem
                    $('.retorno-ajax').append(
                        '<div class="sem-resultado">' +
                        'Sua pesquisa não encontrou nenhum protocolo correspondente.' +
                        '<br/><br/>Sugestões:' +
                        '<ul>' +
                        '<li>Certifique-se de que todas as palavras estejam escritas corretamente.</li>' +
                        '<li>Tente palavras-chave diferentes.</li>' +
                        '<li>Tente palavras-chave mais genéricas.</li>' +
                        '</ul></div>'
                    );
                    paginar = false;
                } else if (consultaVazia) {
                    // Esgotou paginas mas ja tem resultados na tela - apenas parar
                    paginar = false;
                }
            });
    }

    function verificarRegistros(){
        var totalTela = $('table tbody tr.pesquisaTituloRegistro').length;

        if(totalTela < 10 && buscaInicio < qtdeItens){
            carregarProximaPaginaInicial(); // chama apenas se realmente precisar
        }
    }

    // Função para carregar mais resultados
    function carregarProximaPaginaInicial(){
        $('.ajax-loading').show();

        $.post('<?= $strLinkAjaxPesquisar ?>&isPaginacao=true&inicio=' + buscaInicio + '&rowsSolr=' + rowsSolr, $('#seiSearch').serialize())
            .done(function(data){
                if (data.itens > 0) {
                    if (qtdeItens == 0) {
                        qtdeItens = data.itens;
                    }
                    var htmlUtil = data.html && $.trim(data.html) !== '' && data.html.indexOf('<consultavazia>') === -1;
                    if (htmlUtil) {
                        $('.retorno-ajax > table > tbody:last-child').append(data.html);
                    }
                }
                buscaInicio += rowsSolr;
            })
            .always(function() {
                $('.ajax-loading').hide();
                updateCaptcha();
                verificarRegistros();
            });
    }

});

$( document ).ready(function() {
    $("#selOrgaoPesquisa").multipleSelect({
        filter: false,
        minimumCountSelected: 1,
        selectAll: true
    });
    tratarSelecaoOrgao();
});

function restringirOrgao(){
    if (document.getElementById('chkSinRestringirOrgao').checked){
        $("#selOrgaoPesquisa").multipleSelect('uncheckAll');
        document.getElementById('chkSinRestringirOrgao').checked = true;
        $("#selOrgaoPesquisa").multipleSelect('check', <?=SessaoSEI::getInstance()->getNumIdOrgaoUnidadeAtual()?>);
    }
}

function obterOrgaosSelecionados() {
    if ($("#selOrgaoPesquisa").length === 0) {
        return null;
    }
    return $("#selOrgaoPesquisa").multipleSelect("getSelects");
}

function tratarSelecaoOrgao(){
    $('#txtUnidade, #hdnIdUnidade').val('');
}

function OnSubmitForm(){
    if (!document.getElementById('chkSinProcessos').checked && !document.getElementById('chkSinDocumentosGerados').checked && !document.getElementById('chkSinDocumentosRecebidos').checked){
        alert('Selecione pelo menos uma das opções de pesquisa avançada: Processos, Documentos Gerados ou Documento Recebidos');
        return false;
    }

    if ($("#selOrgaoPesquisa").length > 0 && $("#selOrgaoPesquisa").multipleSelect("getSelects").length == 0) {
        alert('Nenhum Órgão Gerador selecionado.');
        return false;
    }

    <? CaptchaSEI::getInstance()->validarOnSubmit('seiSearch'); ?>
}
</script>
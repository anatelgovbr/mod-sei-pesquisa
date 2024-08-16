var SEP_COOKIE = "#_";

/**
 * SISTEMA DA PAGINAÇÃO
 */

var pagina = function() {
	pagina.formulario = document.getElementById("seiSearch");
	pagina.campoPartialFields = document.getElementById("partialfields");
}

pagina.formulario = null;
pagina.campoPartialFields = null;

pagina.ir = function(endereco) {
	
	endereco = endereco.infraReplaceAll('&amp;', '&');
	endereco = endereco.infraReplaceAll('+', '%2B');
	
	pagina.formulario.action = endereco;
	
	if (typeof(window.onSubmitForm)=='function' && !window.onSubmitForm()){
	  return;
	}
	
    pagina.formulario.submit();
}

/**
 * FUNÇÃO PARA REMOVER ACENTOS 
 * ALTERADO PARA TROCAR PARA ENTITIES TAMBEM
 */

function removerAcentos(texto) {
	
	var contador = 0;
	var letras = {
		  procurar: ["á", "Á", "ã", "Ã", "à", "À", "â", "Â", "ä", "Ä", "ç", "Ç", "é", "É", "ê", "Ê", "è", "È", "ë", "Ë", "í", "Í", "ì", "Ì", "ï", "Ï", "ó", "Ó", "õ", "Õ", "ô", "Ô", "ö", "Ö", "ò", "Ò", "ú", "Ú", "ü", "Ü", "ù", "Ù", "ñ", "Ñ" ,"("    ,")"],
		substituir: ["a", "A", "a", "A", "a", "A", "a", "A", "a", "A", "c", "C", "e", "E", "e", "E", "e", "E", "e", "E", "i", "I", "i", "I", "i", "I", "o", "O", "o", "O", "o", "O", "o", "O", "o", "O", "u", "U", "u", "U", "u", "U", "n", "N", "&#40;", "&#41;"]
	}
	
	for (contador; contador < letras.procurar.length; contador += 1) {
		texto = texto.replace(letras.procurar[contador], letras.substituir[contador]);
	}
	
	return texto;
}

/**
 * CLASSE CAMPO
 */

var Campo = function(parametros) {
	if (typeof parametros == "function") {
		this.tratamento = parametros;
	} else if (typeof parametros == "object") {
		this.id = (typeof parametros.id == "object" || typeof parametros.id == "string") ? parametros.id : null;
		this.nome = typeof parametros.nome == "string" ? parametros.nome : null;
		this.opcoes = typeof parametros.opcoes == "object" ? parametros.opcoes : { };
		this.tratamento = null;
		
		this.objeto = function(indice) {
			return document.getElementById(indice == null ? this.id : this.id[indice]);
		}
	}
}

/**
 * SISTEMA DE VALIDAÇÃO
 */

var validar = function() {
	var campo;
	var opcoes;
	var contador;
	var valor;
	
	// VARRE OS CAMPOS
	for (contador = 0; contador < validar.campos.length; contador += 1) {
		campo = validar.campos[contador];
		
		// VERIFICA SE POSSUI UMA FUNÇÃO PRÓPRIA DE TRATAMENTO
		
		if (campo.tratamento instanceof Function) {
			
		} else {
			// VERIFICA SE É COMPOSTO POR MAIS DE UM CAMPO (CHECKBOX OU RADIO)
			
			if (campo.id instanceof Array) {
				
			} else {
				opcoes = campo.opcoes;
				
				// DETERMINA QUE ELEMENTO É
				switch (campo.objeto().tagName.toUpperCase()) {
					case "SELECT":
						
						
						break;
					case "INPUT":
					case "TEXTAREA":
						valor = validar.limpar(campo.objeto().value);
						if (valor.length > 0) {
							if (opcoes.dividir) {
								valor = validar.dividir(valor);
								
								if (valor.length > 1) {
									valor = "(" + campo.nome + ":" + escape(escape(valor.join(" OR " + campo.nome + ":"))).replace(/%253A/g, ":").replace(/%257C/g, " OR ") + ")";
								} else {
									valor = campo.nome + ":" + escape(escape(valor[0]));
								}
							} else {
								valor = campo.nome + ":" + escape(escape(valor));
							}
						}
						
						break;
				}
			}
		}
	}
}

validar.campos = [ ]
validar.formulario = null;

validar.dividir = function(valor, regra) {
	var contador = 0;
	var valorNovo = [ ]
	
	if ((regra instanceof RegExp) == false) {
		regra = /[ ,;]/;
	}
	
	// QUEBRA QUALQUER ESPAÇO EM BRANCO, VÍRGULA OU PONTO-E-VÍRGULA
	valor = valor.split(regra);
	
	for (contador = 0; contador < valor.length; contador += 1) {
		if (valor[contador].length > 0) {
			valorNovo.push(valor[contador]);
		}
	}
	
	return valorNovo;
}

// MÉTODO PARA LIMPEZA DO VALOR DE UM CAMPO

validar.limpar = function(valor) {
	var contador = 0;
	var letras = {
		  procurar: ["á", "Á", "ã", "Ã", "à", "À", "â", "Â", "ä", "Ä", "ç", "Ç", "é", "É", "ê", "Ê", "è", "È", "ë", "Ë", "í", "Í", "ì", "Ì", "ï", "Ï", "ó", "Ó", "õ", "Õ", "ô", "Ô", "ö", "Ö", "ò", "Ò", "ú", "Ú", "ü", "Ü", "ù", "Ù", "ñ", "Ñ"],
		substituir: ["a", "A", "a", "A", "a", "A", "a", "A", "a", "A", "c", "C", "e", "E", "e", "E", "e", "E", "e", "E", "i", "I", "i", "I", "i", "I", "o", "O", "o", "O", "o", "O", "o", "O", "o", "O", "u", "U", "u", "U", "u", "U", "n", "N"]
	}
	
	valor = String(valor).replace(/^\s+|\s+$/g, "");
	
	for (contador; contador < letras.procurar.length; contador += 1) {
		valor = valor.replace(letras.procurar[contador], letras.substituir[contador]);
	}
	
	return valor;
}

/**
 * FUNÇÃO INVOCADA PELO ONLOAD DO BODY
 */

function sistemaInicializar() {
	// VALIDAÇÃO DO FORMULÁRIO AVANÇADO
	
	validar.formulario = document.getElementById("seiSearch");

	// PESQUISAR EM
	validar.campos.push(new Campo({
		id: ["chkSinProcessos", "chkSinDocumentosGerados", "chkSinDocumentosRecebidos"],
		nome: "sta_prot"
	}));
	
	// INTERESSADO / REMETENTE
	validar.campos.push(new Campo({
		id: "hdnIdParticipante",
		nome: "id_int"
    }));
	
	// ASSINANTE
	validar.campos.push(new Campo({
		id: "hdnIdAssinante",
		nome: "id_assin"
	}));
	
	// ASSUNTO
	validar.campos.push(new Campo({
		id: "hdnIdAssunto",
		nome: "id_assun"
	}));
	
	// UNIDADES
	validar.campos.push(new Campo({
		id: "hdnIdUnidade",
		nome: "id_uni_ger"
	}));
	
	// NÚMERO SEI!
	validar.campos.push(new Campo({
		id: "txtProtocoloPesquisa",
		nome: "prot_pesq"
	}));
	
	// TIPO DO PROCESSO
	validar.campos.push(new Campo({
		id: "selTipoProcedimentoPesquisa",
		nome: "id_tipo_proc"
	}));
	
	// TIPO DO DOCUMENTO
	validar.campos.push(new Campo({
		id: "selSeriePesquisa",
		nome: "id_serie"
	}));
	
	// NÚMERO DO DOCUMENTO
	validar.campos.push(new Campo({
		id: "txtNumeroDocumentoPesquisa",
		nome: "numero"
	}));
	
	// SIGLA DO USUÁRIO
	validar.campos.push(new Campo({
		id: "hdnSiglasUsuarios",
		nome: "id_usu_ger",
		opcoes: {
			dividir: true
		}
	}));
	
	// DATA
	validar.campos.push(new Campo(function() {
		
	}));
	
	// PAGINAÇÃO
	
	pagina();
}

/**
 * SISTEMA PARA A MANIPULAÇÃO DO FORMULÁRIO AVANÇADO
 */

function ResBusca(dados)
{
   this.query = dados[0];
   this.totalRes = dados[1];
   this.url = dados[2];
   this.toString = function(){return "{" + this.query + SEP_COOKIE + this.totalRes + SEP_COOKIE + this.url + "}";};
}

var CookieResultado =
{
    addResultado: function (resultado)
    {
        var testeDuplicado = CookieResultado.isDuplicado(resultado.query);
        if (testeDuplicado != -1)
           CookieResultado.remove(testeDuplicado);
        var valorCookie = Cookies.getCookie("res") == null ? "" : Cookies.getCookie("res");
        valorCookie = resultado.toString() + valorCookie;
        if (valorCookie.split("}{").length > 6)
           valorCookie = valorCookie.substring(0, valorCookie.lastIndexOf("{"));
        Cookies.setCookie("res", valorCookie);
    },
    
    getListaResultados: function()
    {
        var valorCookie = Cookies.getCookie("res");
        if (valorCookie != null && valorCookie.length > 0)
        {
            valorCookie = valorCookie.substring(1, valorCookie.length - 1);
            valorCookie = valorCookie.split("}{");
            var arrayResultados = new Array();
            for (var i = 0; i < valorCookie.length; i++)
                arrayResultados[arrayResultados.length] = new ResBusca(valorCookie[i].split(SEP_COOKIE));
            return arrayResultados;
        }
        return null;
    },

    isDuplicado: function(termo)
    {
        var valorCookie = Cookies.getCookie("res");
        if (valorCookie != null && valorCookie.length > 0)
        {
            valorCookie = valorCookie.substring(1, valorCookie.length - 1);
            valorCookie = valorCookie.split("}{");
            for (var i = 0; i < valorCookie.length; i++)
            {
                var temp = valorCookie[i].split(SEP_COOKIE);
                if (temp[0].toLowerCase() == termo.toLowerCase())
                   return i;
            }
        }
        return -1;
    },
 
    remove: function(posicao)
    {
       var listaResultados = CookieResultado.getListaResultados();
       Cookies.setCookie("res", '');
       for (var i = (listaResultados.length - 1); i >= 0; i = i - 1)
       {
          if (posicao != i)
          {
             CookieResultado.addResultado(new ResBusca(new Array(listaResultados[i].query, listaResultados[i].totalRes, listaResultados[i].url)));
          }
       }
    }
}

var Cookies = 
{
    setCookie: function (name, value, expires, path, domain, secure)
    {
      document.cookie = "busca_" + name + "=" + value +
                    ((expires) ? "; expires=" + expires : "") +
                    ((path) ? "; path=" + path : "") +
                    ((domain) ? "; domain=" + domain : "") +
                    ((secure) ? "; secure" : "");
    },
    
    getCookie: function(name)
    {
      var nameEQ = "busca_" + name + "=";
      var ca = document.cookie.split(';');
      for(var i=0; i < ca.length; i++) 
      {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
      }
      return null;
    },
    
    clearAllCookies: function()
    {
        var ca = document.cookie.split(';');

        for (var i=0;i < ca.length;i++) 
        {
            var c = trim(ca[i]);
            // Don't allow for cookies that start with two underscores or that are one
            // character.
            if (c.match(/^busca_/) != null)
            {
              var cookieName = c;
              cookieName = cookieName.replace(/\=.*$/, "");
              cookieName = cookieName.replace(/^busca_/, "");
              Cookies.setCookie(cookieName, '');
            }
        }
    }
}

var Util = {

   getParametro: function(parametro)
   {
      var url = location.href;
      var paramValue = null;
      if (url.indexOf("&" + parametro + "=") != -1)
      {
         var paramValue = url.substring(url.indexOf("&" + parametro + "=") + parametro.length + 1);
      }
      else if (url.indexOf("?" + parametro + "=") != -1)
      {
         var paramValue = url.substring(url.indexOf("?" + parametro + "=") + parametro.length + 1);
      }
      if (paramValue != null)
      {
         if (paramValue.indexOf("&") != -1)
            paramValue = paramValue.substring(0, paramValue.indexOf("&"));
      }
      return paramValue;
   },
   
   getUrl: function(uri)
   {
      var url = location.href;
      if (!uri)
      {
        url = url.substring(url.indexOf("://") + 3);
        url = url.substring(url.indexOf("/"));
      }
      return url;
   }
}

/*
 * exemplo new Array("nome da meta tag", "id do input") new Array("DT_nome da
 * meta tag") VocÃª pode colocar ids de campos input (type=text) e select
 */

var mt = new Array(
		
    // INTERESSADO
	new Array("id_int", "hdnIdParticipante"),
	
//	// REMETENTE Sera implementado depois
//	new Array("id_rem", "hdnIdParticipante"),
//	
//	// DESTINATARIO
//	new Array("id_dest", "hdnIdParticipante"),
		
	// ASSINANTE
	new Array("id_assin", "hdnIdAssinante"),
	
	// ASSUNTO
	new Array("id_assun", "hdnIdAssunto"),
	
	// UNIDADES
	new Array("id_uni_ger", "hdnIdUnidade"),
	
	// NÚMERO SEI
	new Array("prot_pesq", "txtProtocoloPesquisa"),
	
	// TIPO DO PROCESSO
	new Array("id_tipo_proc", "selTipoProcedimentoPesquisa"),
	
	// TIPO DO DOCUMENTO
	new Array("id_serie", "selSeriePesquisa"),
	
	// NÚMERO DO DOCUMENTO
	new Array("numero", "txtNumeroDocumentoPesquisa")
);

var mtCheckbox = new Array(
	new Array("sta_prot", "chkSinProcessos", "chkSinDocumentosGerados", "chkSinDocumentosRecebidos")
);

/*
 * exemplo new Array("nome da meta tag", "id campo data inicial", "id campo data final", "nome para exibiÃ§Ã£o")
 */
var mtRangeData = new Array(
	// DATA
	new Array("dta_ger", "txtDataInicio", "txtDataFim", "Período")
);

var rangeMetaTags = "";

function trim(stringToTrim) {
	return String(stringToTrim).replace(/^\s+|\s+$/g, "");
}

function getFullNumber(number)
{
   return number.length > 1 ? number : "0" + number;
}


function utf8Decode(utftext) {
	var string = "";
	if (utftext != null) {
		var i = 0;
		var c = c1 = c2 = 0;
		while (i < utftext.length) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			} else if ((c > 191) && (c > 224)) {
				c2 = utftext.charCodeAt(i + 1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = utftext.charCodeAt(i + 1);
				c3 = utftext.charCodeAt(i + 2);
				string += String.fromCharCode(((c & 15) << 12)
						| ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
	}
	while (string.indexOf("+") != -1)
		string = string.replace("\+", " ");
	return string;
}

function utf8Encode(string) {
	var utftext = "";
	if (string != null) {
		string = string.replace(/\r\n/g, "\n");
		for ( var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			var ch = string.charAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);

			} else if ((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			} else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
	}
	return utftext;
}

function URLEncode(string) {
	var returnString = escape(this.utf8Encode(string));
	//returnString = returnString.replace(/\./g, "%2E");
	//returnString = returnString.replace(/\|/g, "%7C");
	return returnString.replace(/:/g, "%3A");
}

function URLDecode(string) {
	return this.utf8Decode(unescape(string));
}

function partialFields() {
	var contador;
	var erro = false;
	var idUnidadeAberto;
	var mtName;
	var mtValue;
	var partFields = document.getElementById("partialfields");

	for (x = 0; x < mt.length; x++) {
		mtName = mt[x][0];

		if (document.getElementById(mt[x][1]).tagName.toLowerCase() == "input")
			mtValue = utf8Encode(formatarCaracteresEspeciais(removerAcentos(trim(document.getElementById(mt[x][1]).value))));
		else if (document.getElementById(mt[x][1]).tagName.toLowerCase() == "select") {
			mtValue = utf8Encode(trim(document.getElementById(mt[x][1]).options[document.getElementById(mt[x][1]).selectedIndex].value));
		}

		switch (mtName) {

			case "prot_pesq":

				//mtValue = mtValue.replace(/[^0-9]/g, "");
				mtValue = mtValue.replace(/[^0-9a-zA-Z]/g, "");



				if (mtValue.length > 0 && mtValue.toUpperCase() != "NULL") {
					if (partFields.value.length > 0) {
						partFields.value += " AND ";
					}
					partFields.value += mtName + ":*" + mtValue +"*";
				}
				break;

			default:
				if (mtValue.length > 0 && mtValue.toUpperCase() != "NULL") {

					var bolCampoMultiplo = false;

					if (mtName == 'id_int' ||
						mtName == 'id_rem' ||
						mtName == 'id_dest' ||
						mtName == 'id_assun' ||
						mtName == 'id_uni_aces' ||
						mtName == 'id_uni_aces' ||
						mtName == 'id_assin'){

						bolCampoMultiplo = true;
					}

					if (partFields.value.length > 0) {
						partFields.value += " AND ";
					}
					if (bolCampoMultiplo){
						partFields.value += mtName + ":*" + mtValue + "*";
					}else{
						partFields.value += mtName + ":" + mtValue;
					}

				}

				break;
		}


	}


	// SIGLAS DOS USUÁRIOS
	var strVerificacao = removerAcentos(trim(document.getElementById("hdnSiglasUsuarios").value));

	if (strVerificacao != ''){

	    var siglas = strVerificacao.split(',');

		if (siglas.length > 0) {
			if (partFields.value.length > 0) {
				partFields.value += " AND ";
			}
			partFields.value += "(id_usu_ger:" + siglas.join(" OR id_usu_ger:") + ")";
		}
	}

	// CHECKBOX DO PESQUISAR EM

	for (contador = 0; contador < mtCheckbox.length; contador += 1) {
		var campo;
		var campos;
		var dados = [];

		for (campos = 1; campos < mtCheckbox[contador].length; campos += 1) {
			campo = document.getElementById(mtCheckbox[contador][campos]);

			if (campo.checked) {
				dados.push(campo.value);
			}
		}

		if (dados.length > 0) {
			if (partFields.value.length > 0) {
				partFields.value += " AND ";
			}

			partFields.value += mtCheckbox[contador][0] + ":" + dados.join(";");
		}
	}

    var dataInicio =  infraTrim(document.getElementById('txtDataInicio').value);
    var dataFim =  infraTrim(document.getElementById('txtDataFim').value);

	if (dataInicio!='' || dataFim!=''){

	   if (dataInicio != '' && !infraValidarData(document.getElementById('txtDataInicio'))){
	     return false;
	   }

	   if (dataFim!='' && !infraValidarData(document.getElementById('txtDataFim'))){
	     return false;
	   }

	   if (dataInicio!='' && dataFim!='' && infraCompararDatas(dataInicio,dataFim) < 0){
	     alert('Período de datas inválido.');
	     document.getElementById('txtDataInicio').focus();
	     return false;
	   }

		var dia1 = dataInicio.substr(0,2);
		var mes1 = dataInicio.substr(3,2);
		var ano1 = dataInicio.substr(6,4);

		var dia2 = dataFim.substr(0,2);
		var mes2 = dataFim.substr(3,2);
		var ano2 = dataFim.substr(6,4);

        if (partFields.value.length > 0) {
			partFields.value += " AND ";
		}

		const current_date = new Date();
        const current_month = String(current_date.getMonth() + 1).padStart(2, '0');
        const current_day = String(current_date.getDate()).padStart(2, '0');

		if (dataInicio != '' && dataFim != '') {
		  partFields.value += '(dta_ger:[' + ano1 + '-' + mes1 + '-' + dia1 + 'T00:00:00Z TO ' + ano2 + '-' + mes2 + '-' + dia2 +'T00:00:00Z] OR dta_inc:[' + ano1 + '-' + mes1 + '-' + dia1 + 'T00:00:00Z TO ' + ano2 + '-' + mes2 + '-' + dia2 +'T00:00:00Z])';
		}else if (dataInicio != ''){
		  partFields.value += '(dta_ger:[' + ano1 + '-' + mes1 + '-' + dia1 + 'T00:00:00Z TO ' + current_date.getFullYear() + '-' + current_month + '-' + current_day +'T00:00:00Z] OR dta_inc:[' + ano1 + '-' + mes1 + '-' + dia1 + 'T00:00:00Z TO ' + current_date.getFullYear() + '-' + current_month + '-' + current_day +'T00:00:00Z])';
		}else{
		  partFields.value += '(dta_ger:[1899-08-01T00:00:00Z TO ' + ano2 + '-' + mes2 + '-' + dia2 +'T00:00:00Z] OR dta_inc:[1899-08-01T00:00:00Z TO ' + ano2 + '-' + mes2 + '-' + dia2 +'T00:00:00Z])';
		}
	}
	
	return true;
}

function checkForInt(e) {
	var charCode = (e.which != null) ? e.which : event.keyCode;
	return (charCode < 32 || (charCode >= 48 && charCode <= 57));
}

function checkForString(e) {
	var k;
	document.all ? k = e.keyCode : k = e.which;
	return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8);
}

function selecionaOp(elId, op) {
	var el = document.getElementById(elId);
	for ( var i = 0; i < el.options.length; i++)
		if (el.options[i].value.toLowerCase() == op.toLowerCase()) {
			el.selectedIndex = i;
			break;
		}
}

function moveFoco(el, tamanho, idCampo)
{
	if (el.value.length == tamanho)
	{
            document.getElementById(idCampo).focus();
	}
}

function limpaFields() {
	rangeMetaTags = "";
	document.getElementById("partialfields").value = "";
}

function formatarCaracteresEspeciais(txt){
	  
    arrExc = Array(String.fromCharCode(92),'/','+','-','&','|','!','(',')','{','}','[',']','^','~','*','?',':');

    for(var i=0;i<arrExc.length;i++){
   	  txt = txt.infraReplaceAll(arrExc[i],String.fromCharCode(92) + arrExc[i]);
	}
    
    return txt;
  }

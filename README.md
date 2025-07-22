# Módulo SEI Pesquisa Pública
- Este módulo foi desenvolvido originalmente pelo CADE (Conselho Administrativo de Defesa Econômica).
- Desde da v4.0.0 do módulo, que o adaptou para o SEI 4.0, a Anatel vem mantendo e evoluindo o SEI Pesquisa Pública.

## Requisitos
- Requisito Mínimo é o SEI 4.1.5 instalado/atualizado - Não é compatível com versões anteriores e em versões mais recentes é necessário conferir antes se possui compatibilidade.
	- Verificar valor da constante de versão no arquivo /sei/web/SEI.php ou, após logado no sistema, parando o mouse sobre a logo do SEI no canto superior esquerdo.
- **Atenção**: nas máquinas que rodam o SEI deve instalar a biblioteca PHP "php-mcrypt".
- Antes de executar os scripts de instalação/atualização, o usuário de acesso aos bancos de dados do SEI e do SIP, constante nos arquivos ConfiguracaoSEI.php e ConfiguracaoSip.php, deverá ter permissão de acesso total ao banco de dados, permitindo, por exemplo, criação e exclusão de tabelas.
- Os códigos-fonte do Módulo podem ser baixados a partir do link a seguir, devendo sempre utilizar a versão mais recente: [https://github.com/anatelgovbr/mod-sei-pesquisa/releases](https://github.com/anatelgovbr/mod-sei-pesquisa/releases "Clique e acesse")
- Se já tiver instalado versão principal com a execução dos scripts de banco do módulo no SEI e no SIP, **em versões intermediárias basta sobrescrever os códigos** e não precisa executar os scripts de banco novamente.
	- Atualizações apenas de código são identificadas com o incremento apenas do terceiro dígito da versão (p. ex. v4.1.1, v4.1.2) e não envolve execução de scripts de banco.

## Procedimentos para Instalação
1. Fazer backup dos bancos de dados do SEI e do SIP.
2. Carregar no servidor os arquivos do módulo nas pastas correspondentes nos servidores do SEI e do SIP.
	- **Caso se trate de atualização de versão anterior do Módulo**, antes de copiar os códigos-fontes para a pasta "/sei/web/modulos/pesquisa", é necessário excluir os arquivos anteriores pré existentes na mencionada pasta, para não manter arquivos de códigos que foram renomeados ou descontinuados.
3. Editar o arquivo "/sei/config/ConfiguracaoSEI.php", tomando o cuidado de usar editor que não altere o charset do arquivo, para adicionar a referência à classe de integração do módulo e seu caminho relativo dentro da pasta "/sei/web/modulos" na array 'Modulos' da chave 'SEI':

		'SEI' => array(
			...
			'Modulos' => array(
				'PesquisaIntegracao' => 'pesquisa',
				),
			),

4. Antes de seguir para os próximos passos, é importante conferir se o Módulo foi corretamente declarado no arquivo "/sei/config/ConfiguracaoSEI.php". Acesse o menu **Infra > Módulos** e confira se consta a linha correspondente ao Módulo, pois, realizando os passos anteriores da forma correta, independente da execução do script de banco, o Módulo já deve ser reconhecido na tela aberta pelo menu indicado.
5. Rodar o script de banco "/sip/scripts/sip_atualizar_versao_modulo_pesquisa.php" em linha de comando no servidor do SIP, verificando se não houve erro em sua execução, em que ao final do log deverá ser informado "FIM". Exemplo de comando de execução:

		/usr/bin/php -c /etc/php.ini /opt/sip/scripts/sip_atualizar_versao_modulo_pesquisa.php 2>&1 > atualizacao_pesquisa_sip.log

6. Rodar o script de banco "/sei/scripts/sei_atualizar_versao_modulo_pesquisa.php" em linha de comando no servidor do SEI, verificando se não houve erro em sua execução, em que ao final do log deverá ser informado "FIM". Exemplo de comando de execução:

		/usr/bin/php -c /etc/php.ini /opt/sei/scripts/sei_atualizar_versao_modulo_pesquisa.php 2>&1 > atualizacao_pesquisa_sei.log

7. **IMPORTANTE**: Na execução dos dois scripts de banco acima, ao final deve constar o termo "FIM", o "TEMPO TOTAL DE EXECUÇÃO" e a informação de que a instalação/atualização foi realizada com sucesso na base de dados correspondente (SEM ERROS). Do contrário, o script não foi executado até o final e algum dado não foi inserido/atualizado no respectivo banco de dados, devendo recuperar o backup do banco e repetir o procedimento.
	- Constando ao final da execução do script as informações indicadas, pode logar no SEI e SIP e verificar no menu **Infra > Parâmetros** dos dois sistemas se consta o parâmetro "VERSAO_MODULO_PESQUISA_PUBLICA" com o valor da última versão do módulo.
8. Em caso de erro durante a execução do script, verificar (lendo as mensagens de erro e no menu Infra > Log do SEI e do SIP) se a causa é algum problema na infraestrutura local ou ajustes indevidos na estrutura de banco do core do sistema. Neste caso, após a correção, deve recuperar o backup do banco pertinente e repetir o procedimento, especialmente a execução dos scripts de banco indicados acima.
9. Após a execução com sucesso, com um usuário com permissão de Administrador no SEI, seguir os passos dispostos no tópico "Orientações Negociais" mais abaixo.
10. **Atenção**: nas máquinas que rodam o SEI deve instalar a biblioteca PHP "php-mcrypt".

## Orientações Negociais
1. Imediatamente após a instalação com sucesso, com usuário com permissão de "Administrador" do SEI, acessar o menu de administração do Módulo pelo seguinte caminho: Administração > Pesquisa Pública > Parâmetros de Pesquisa. Somente com tudo parametrizado adequadamente será possível o uso do módulo por meio da página de Pesquisa Pública:

		http://[Servidor_PHP]/sei/modulos/pesquisa/md_pesq_processo_pesquisar.php?acao_externa=protocolo_pesquisar&acao_origem_externa=protocolo_pesquisar&id_orgao_acesso_externo=[id_orgao_acesso_externo]

2. **Atenção:** Cuidado com o preenchimento do campo "Chave para criptografia dos links de processos e documentos" na Administração do módulo. Leia o texto no ícone de ajuda sobre o citado campo.
3. A partir da versão 3.0.6 do módulo de Pesquisa Pública existe integração com o Módulo de Peticionamento e Intimação Eletrônicos, em que a Pesquisa Pública percebe se existe o mencionado módulo na versão 2.0.0 ou superior instalado no SEI e, com isso, tem comportamento próprio na tela de acesso ao processo pela Pesquisa Pública para **proteger o acesso a documento público que esteja relacionado com Intimação Eletrônica ainda não cumprida**.
	- Este comportamento visa a proteção da pesquisa dentro do conteúdo e o acesso do teor do documento público relacionado com intimação eletrônica ainda não cumprida, para evitar o conhecimento prévio do teor de documento objeto de intimação antes do devido Cumprimento da Intimação Eletrônica.
	- Após o Cumprimento da Intimação Eletrônica pelos destinatários, por ser documento Público, o acesso a seu teor por meio da Pesquisa Pública será liberado normalmente.
4. A partir da versão 4.1.0 do módulo de Pesquisa Pública foi criado o parâmetro "Data de Corte Opcional" na Administração do módulo. Leia o texto no ícone de ajuda sobre o citado campo.
	- Caso seja inserida uma Data de Corte, o módulo protege a pesquisa dentro do conteúdo e o acesso aos documentos com nível de acesso Público que tenham data de inclusão (no caso de Documento Externo ou Automático) ou data da primeira assinatura (no caso de Documento Gerado ou Formulário) anterior à data de corte informada.
	- Nesse cenário, no acesso ao processo, ao lado do protocolo do documento constará o ícone de uma chave azul indicando a situação de restrição provisória em razão de necessidade de reclassificação de nível de acesso.
5. Os tipos de Andamentos listados quando é aberto um processo pela Pesquisa Pública é parametrizável no mesmo local que administra a lista de Andamentos visíveis para Acesso Externo do SEI, pelo menu Administração > Histórico, marcando ou desmarcando a coluna "Resumido".
6. A partir da versão 4.1.2 do módulo foram eliminados quatro parâmetros na Administração que ocultavam metadados sobre o processo, o que seria contrário à LAI. O teor de documentos restritos nunca é pesquisável e acessado e a listagem em si dos Protocolos constantes no processo e dos Andamentos ocorridos no processo não é informação restrita que possa ser ocultada.

## Erros ou Sugestões
1. [Abrir Issue](https://github.com/anatelgovbr/mod-sei-pesquisa/issues) no repositório do GitHub do módulo se ocorrer erro na execução dos scripts de banco do módulo no SEI ou no SIP acima.
2. [Abrir Issue](https://github.com/anatelgovbr/mod-sei-pesquisa/issues) no repositório do GitHub do módulo se ocorrer erro na operação do módulo.
3. Na abertura da Issue utilizar o modelo **"1 - Reportar Erro"**.

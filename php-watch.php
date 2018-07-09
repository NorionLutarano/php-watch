<?php
/*
* criado por andré luiz em 05/07/2018
* projeto php-watch é similar ao sass-watch
* versão beta
* objetivo: tornar as aplicações backend mais rápidas a serem executadas.
* observações: 
*  variável diretório tem que está nesse formato /diretório/. Iniciando com / e terminando com /.
*  variável diretório bibliotecas diretório/ não iniando com / e sim terminando.
*  a aplicação tem que ser executada no mesmo lugar do arquivo.
*  tanto diretório a ser salvo quando o diretório das bibliotecas tem que está na mesmo diretório do arquivo a
*  ser alterado.
*
*/

class php_watch 
{
	
	function __construct($diretorio_salvo,$diretorio_bibliotecas,$arquivo){
		$this->diretorio_salvo=getcwd().$diretorio_salvo."/"; // onde o arquivo será salvo
		$this->diretorio_bibliotecas=$diretorio_bibliotecas; // diretório onde contém os arquivos da importação
		$this->arquivo=$arquivo;// path do arquivo a ser monitorado
		$this->assinatura_arquivo=md5($arquivo);// variável usada para verificar alterações no arquivo
		$this->lista_arquivos=[];//lista dos arquivos da biblioteca
	}


	function capturar_arquivos(){
		//obtendo a lista de arquivos e diretórios do diretório da biblioteca
		$listar_diretorio=scandir(getcwd()."/".$this->diretorio_bibliotecas);
		//pecorrer a lista de arquivos capturados
		for($contador=2;$contador<count($listar_diretorio);$contador++){
			//verifica se não é um diretório
			if(!is_dir($listar_diretorio[$contador])){
				//verifica se é um arquivo php
				if(strpos($listar_diretorio[$contador],".php")){
					//salva na lista de arquivos, todos os arquivos php
					$this->lista_arquivos[]=$this->diretorio_bibliotecas.$listar_diretorio[$contador];
				}
			}	
		}
	}


	function monitorar(){
		//fica monitorando até ser encerrado no terminal com ctrl+c
		while(true){
			//obtem md5 do arquivo atual
			$assinatura_atual= md5(file_get_contents($this->arquivo));		
			//compara md5 do arquivo atualmente com md5 inicial, se for diferente o arquivo foi alterado
			if($this->assinatura_arquivo!=$assinatura_atual){
				//captura a lista de arquivos da biblioteca, fica dentro do loop para se manter atualizado
				$this->capturar_arquivos(); print_r($this->lista_arquivos);
				//guarda a nova assinatura md5 do arquivo
				$this->assinatura_arquivo=$assinatura_atual;
				// pega o conteúdo do arquivo a ser monitorado, como uma string
				$arquivo =file_get_contents($this->arquivo);
				// substitui include por um espaço vazio
				$arquivo= str_replace("include"," ",$arquivo);
				// substitui require por um espaço vazio
				$arquivo= str_replace("require"," ",$arquivo);
				//pecorre a lista de arquivos da biblioteca
				for($contador=0;$contador<count($this->lista_arquivos);$contador++){
					//verifica se a biblioteca foi importada
					if(strpos($arquivo,"'".$this->lista_arquivos[$contador]."';")){
						//obtem o conteúdo da biblioteca
						$biblioteca=file_get_contents(getcwd()."/".$this->lista_arquivos[$contador]);
						//remove os <?php ? > do arquivo
						$biblioteca=str_replace("<?php", " ", $biblioteca);
						$biblioteca=str_replace("?>", " ", $biblioteca);
						//armazena o formato a ser substituido no arquivo
						$arquivo_substitudo="'".$this->lista_arquivos[$contador]."';";
						//substitui o import pelo conteudo do arquivo					
						$arquivo=str_replace($arquivo_substitudo,$biblioteca,$arquivo);
					}
					//salva o novo arquivo no diretório especificado
					file_put_contents($this->diretorio_salvo.$this->arquivo,$arquivo);
					
				}			
			}
			//limpar array da lista de arquivos, evita sobrecargar
			unset($this->lista_arquivos);	
		}

	}




}


?>
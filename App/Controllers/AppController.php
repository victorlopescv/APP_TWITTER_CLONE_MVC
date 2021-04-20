<?php  

namespace App\Controllers;
//recursos miniframework
use MF\Controller\Action;
use MF\Model\Container;


class AppController extends Action{
	
	public function timeline() {


		//validando autenticacao do usuario e iniciando a _SESSION
		$this->validaAutenticacao();	

		//recuperação dos tweets
		$tweet = Container::getModel("Tweet");
		$tweet->__set('id_usuario',$_SESSION['id']);

		//variavéis de páginação
		$total_registros_pagina = 10;
		$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1 ;
		$deslocamento = ($pagina - 1) * $total_registros_pagina;
/*
		$total_registros_pagina = 10;
		$deslocamento = 10; =  (2-1)*10
		$pagina = 2;
	
		$total_registros_pagina = 10;
		$deslocamento = 20; (3-1)*10 = (pagina - 1) * total_registros_pagina 
		$pagina = 3;
*/
		//echo "<br><br>Página: $pagina | Total de registros por página: $total_registros_pagina | Deslocamento: $deslocamento";
		//$tweets = $tweet->getAll();
		//resgata tweets por pagina e insere em tweets no obj view
		$tweets = $tweet->getPorPaginacao($total_registros_pagina,$deslocamento);
		$this->view->tweets = $tweets;

		//resgata numero total de tweets para em seguida realizar soma de total de paginas e armazena em total_de_paginas dentro do obj view
		$total_registros = $tweet->getTotalRegistros();
		$this->view->total_de_paginas = ceil( $total_registros['total']/$total_registros_pagina);

		$this->view->pagina_ativa = $pagina;

		//recuperação informações usuario
		$usuario = Container::getModel('Usuario');	
		$usuario->__set('id',$_SESSION['id']);

		$this->view->nomeUsuario = $usuario->nomeUsuarioLogado();	
		$this->view->totalTweets = $usuario->totalTweets();
		$this->view->totalSeguindo = $usuario->totalSeguindo();
		$this->view->totalSeguidores = $usuario->totalSeguidores();

		//renderizando pagina timeline
		$this->render("timeline");
			
	}

	public function tweet(){

		$this->validaAutenticacao();	
			
		$tweet = Container::getModel('Tweet');

		if ($_POST['tweet'] != '') {
			
		$tweet->__set('tweet',$_POST['tweet']); 
		$tweet->__set('id_usuario',$_SESSION['id']);

		$tweet->salvar();
		
		}

		header("Location: /timeline");	
	}

	public function validaAutenticacao(){

		session_start();

		if (!isset($_SESSION['id']) || $_SESSION['id'] == '' || !isset($_SESSION['nome']) || $_SESSION['nome'] == '' ) {
			header("Location: /?login=erro");
		}
	}

	public function quemSeguir(){

		$this->validaAutenticacao();

		$pesquisarPor = isset($_GET['pesquisarPor']) ? $_GET['pesquisarPor'] : '';
		$usuarios = array();

		$usuario = Container::getModel('Usuario');
		$usuario->__set('id',$_SESSION['id']);
			
		if ($pesquisarPor != '') {
		$usuario->__set('nome',$pesquisarPor);
		$usuarios = $usuario->getAll();

		}

		$this->view->nomeUsuario = $usuario->nomeUsuarioLogado();	
		$this->view->totalTweets = $usuario->totalTweets();
		$this->view->totalSeguindo = $usuario->totalSeguindo();
		$this->view->totalSeguidores = $usuario->totalSeguidores();
		$this->view->usuarios = $usuarios;		
		
		$this->render('quemSeguir');
	}

	public function acao(){
		
		$this->validaAutenticacao();

		$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
		$id_usuario_seguindo = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';
		$id_tweet = isset($_GET['id_tweet']) ? $_GET['id_tweet'] : '';

		$usuario = Container::getModel('Usuario');        
        $usuario->__set('id',$_SESSION['id']);

        if ($acao == 'seguir') {
        	$usuario->seguirUsuario($id_usuario_seguindo);
        	header("Location: /quem_seguir");

        } if ($acao == 'deixar_de_seguir') {
        	$usuario->deixarseguirUsuario($id_usuario_seguindo);
        	header("Location: /quem_seguir");
        	
        }if ($acao == 'remover_tweet') {
        	$usuario->remover_tweet($id_tweet);
        	header("Location: /timeline");
        	

        }


	}

}

?>

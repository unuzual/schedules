<?php
	require_once("framework/core/core.php");

	function setCodeAlerta($numeroCodigo){
		if($numeroCodigo==4)
			header("location:sucesso/".$numeroCodigo);
		else
			header("location:error/".$numeroCodigo);
		//$_SESSION['status'] = $numeroCodigo;
	}

function connect_db(){

		$mysqli = new mysqli(db_user, db_login, db_password, db_name);
		mysqli_set_charset($mysqli,"utf8");

		if ($mysqli->connect_errno) {
   			echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		}

		return $mysqli;
	}


	function check_double($email) {

		$mysqli = connect_db();

		$table = db_table;

		$result = mysqli_query($mysqli,"SELECT * FROM px_eventos WHERE email = '$email'");

		$num = mysqli_num_rows($result);

		if ($num==0){
			$sql = "INSERT INTO users (email) VALUES ('$email')";

			mysqli_query($mysqli,$sql);

			return 1;
		}

		else{
			return 2;
		}

	}


	function check_double_referral($email,$id) {

		$mysqli = connect_db();

		$result = mysqli_query($mysqli,"SELECT * FROM `px_referral` WHERE Referred_Email = '$email' AND RefereeID = '$id' ");

		$num = mysqli_num_rows($result);

		if ($num==0){

			return 1;
		}

		else{
			return 2;
		}

	}


	function check_double_evento($nome_evento) {

		$mysqli = connect_db();

		$result = mysqli_query($mysqli,"SELECT * FROM px_eventos WHERE nome_evento = '$nome_evento'");

		$num = mysqli_num_rows($result);

		if ($num==0){

			return false;
		}

		else{
			return true;
		}

	}

	function check_Horario($year,$week) {

		$mysqli = connect_db();

		$result = mysqli_query($mysqli,"SELECT * FROM px_schedules WHERE wk_number = '".$week."' and year = '".$year."' ");

		$num = mysqli_num_rows($result);

		if ($num==0){

			return false;
		}

		else{
			return true;
		}

	}




	function atualizaHorario($ano,$semana,$string) {

		$mysqli = connect_db();


		if(check_Horario($ano,$semana))
			$result = mysqli_query($mysqli,"UPDATE px_schedules SET horarios = '".$string."' WHERE wk_number='".$semana."' AND year='".$ano."'");

		else
			$result = mysqli_query($mysqli,"INSERT INTO px_schedules (ID, id_adm, horarios,wk_number, year) VALUES (NULL, '".$_SESSION['ID']."', '".$string."', '".$semana."', '".$ano."')"); 

	}


	function autentica($login,$senha){

		$mysqli = connect_db();

		$result = mysqli_query($mysqli,"SELECT * FROM px_user WHERE email = '$login' AND password='$senha' ");

		//echo mysqli_num_rows($result);
		if(mysqli_num_rows($result)!=false){
			$nome = mysqli_query($mysqli,"SELECT nome FROM px_user WHERE email = '$login'");
			$row = mysqli_fetch_array($nome);

			return $row['nome'];
		}

		else{
			header('Location: home');
			return false;
		}

		
	}

	function criaCadastro($nomeCadastro,$emailCadastro,$passwordCadastro,$refereeID=0){
		$mysqli = connect_db();

		if(check_double($emailCadastro)==1){

			if ($refereeID!=0){

				$result = mysqli_query($mysqli,"UPDATE px_referral SET `Status` = '1' WHERE `Referred_email`='$emailCadastro' and `RefereeID`='$refereeID'");

				if (mysqli_num_rows($result)==false)
					criaReferral($emailCadastro,1);
				//;
			}


			$result = mysqli_query($mysqli,"INSERT INTO px_user (email, nome, password) VALUES ('$emailCadastro','$nomeCadastro', '$passwordCadastro')");	
			
			return true;
			}
		
		else{
		
			setCodeAlerta(6);

			return false;
		}
	}

	function entrarSistema($email,$senha){
		if(isset($email) and (autentica($email,$senha)!=false)){

		$mysqli = connect_db();

		$result = mysqli_query($mysqli,"SELECT ID FROM px_user WHERE email = '$email'");

		$id = mysqli_fetch_array($result);

		$_SESSION['nome'] = autentica($email,$senha);
		$_SESSION['email'] = $email;
		$_SESSION['password'] = $senha;
		$_SESSION['ID'] = $id[0];
		$_SESSION['logado'] = true;

		}

		else{

			if(check_double($email)==1){
				//echo "entrei aqui";
				//setCodeAlerta(1);
			}

			else {
				header('Location: home');
			}
		}

	}

	function headerLogin($logado){


		if($logado){
					include_once PAGES_URL."logado.php";
			}

		else{
				include_once PAGES_URL."form-login.php";
		
		}	
	}

function areaCadastro($logado){

	if($logado){
			include_once PAGES_URL."indica.php";
		}

	else{
			include_once PAGES_URL."form-cadastro.php";    
	}	
	
	}


	function displayAlerta($alertClass,$textoAlerta,$codAlerta){
		if($codAlerta!=0)
			echo '
				<div class="row">
					<div class="col-md-offset-6 col-md-2 text-center">
						<div class="alert '.$alertClass.' alert-dismissable alertas">
	   						<button type="button" name="buttonAlert" class="close" data-dismiss="alert" aria-hidden="true">&times; </button>
	   						'.$textoAlerta.'
						</div>
					</div>
				</div>
			';

			//unset($_SESSION['status']);
	}

	function enviaMail($to,$subject,$nome_evento,$data,$uuid_evento,$uuid_participante){

			$url=url.'certificado/'.$uuid_evento.'/'.$uuid_participante;

			$message = file_get_contents('framework/template/email/certificado.html');

			// Replace the % with the actual information
			$message = str_replace('%evento%', $nome_evento, $message);
			$message = str_replace('%data%', $data, $message);
			$message = str_replace('%url%', $url, $message);


			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->CharSet = 'UTF-8';
			$mail->Host = 'smtp.mandrillapp.com';
			$mail->SMTPAuth= true;
			$mail->Username= MAIL_USERNAME;
			$mail->Password = MAIL_PASSWORD;
			$mail->SMTPSecure= 'tls';
			$mail->Port = 587;
			$mail->From = MAIL_FROM;
			$mail->FromName = MAIL_FROM_NAME;
			$mail->addAddress($to);
			$mail->Subject = $subject;
			$mail->MsgHTML($message);
			//$mail->AltBody(strip_tags($message));

			if(!$mail->send()) {
			    echo 'Message could not be sent.';
			    echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
			    echo 'Message has been sent';
			}
	}


	
	function uuidGen($echo=false) {
			if($echo)
				echo md5(uniqid(rand(), true));

			return md5(uniqid(rand(), true));
	}

	//function mysqli_fetch_all($result){
      //    while($row = $result->fetch_array())
		//	{
		//		$rows[] = $row;
		//	}

		//	return $rows;
    //}

	function sanitizeDate($data){
		$dia = ($data[0].$data[1]);
		$mes = ($data[3].$data[4]);
		$ano = ($data[6].$data[7].$data[8].$data[9]);

		return ($ano.'-'.$mes.'-'.$dia);

	}

	function reSanitizeDate($data){
		$dia = ($data[8].$data[9]);
		$mes = ($data[5].$data[6]);
		$ano = ($data[0].$data[1].$data[2].$data[3]);

		return ($dia.'/'.$mes.'/'.$ano);

	}

	function dadosEvento($id){

		$mysqli = connect_db();

		$result = mysqli_query($mysqli,"SELECT * FROM px_eventos WHERE id = '$id'");
		$event_data = mysqli_fetch_array($result);

		return $event_data;
	}
function printaTabela($dados){

		$horarios = array('7:00h às 8:00h','8:00h às 9:00h','9:00h às 10:00h',
			'10:00h às 11:00h','11:00h às 12:00h','12:00h às 14:00h','14:00h às 15:00h',		
			'15:00h às 16:00h','16:00h às 17:00h','17:00h às 18:00h','18:00h às 19:00h',
			'19:00h às 20:00h','20:00h às 21:00h');

		$materias = $dados;
		echo '<table class="table table-striped text-center" style="margin-bottom:120px;"><tr><th>Horário</th>
		<th>Segunda-Feira</th><th>Terça-Feira</th><th>Quarta-Feira</th>
		<th>Quinta-Feira</th><th>Sexta-Feira</th><th>Sábado</th></tr>';

		for($i=0;$i<13;$i++)
		{
				echo "<tr>";
				echo "<td class='horarios'>".$horarios[$i]."</td>";

				for($j=0;$j<6;$j++)
				{
					//cada td é uma materia do horario
					echo "<td class='aulas'>".converteMateria($materias[$j][$i])."</td>";
				}
				echo "</tr>";
		}

	}

	function converteMateria($numero,$tabelaView=true){

		if($tabelaView)
			switch($numero){
				case -3:
					return "<span class='feriado'>Feriado</span>";
				case -2:
					return "<span class='almoco'>Almoço</span>";
				case-1:
					return "<span class='semAula'>Sem aula</span>";
				case 0:
					return "<span class='naoDefinido'>Não Definido</span>";
				case 1:
					return "<span class='legislacao'>Legislação</span>";
				case 2:
					return "<span class='direcaoDefensiva'>Direçao Defensiva</span>";
				case 3:
					return "<span class='primeirosSocorros'>Primeiros Socorros</span>";
				case 4:
					return "<span class='meioAmbiente'>Meio Ambiente</span>";
				case 5:
					return "<span class='mecanica'>Mecânica</span>";
			}

		else
			switch($numero){
				case -3:
					return "Feriado";
				case -2:
					return "Almoço";
				case-1:
					return "Sem aula";
				case 0:
					return "Não Definido";
				case 1:
					return "Legislação";
				case 2:
					return "Direçao Defensiva";
				case 3:
					return "Primeiros Socorros";
				case 4:
					return "Meio Ambiente";
				case 5:
					return "Mecânica";
			}
	}

	function converteToMateria($numero){
			
			switch($numero){
				case "feriado":
					return -3;
				case "almoco":
					return -2;
				case "semAula":
					return -1;
				case "naoDefinido":
					return 0;
				case "legislacao":
					return 1;
				case "direcaoDefensiva":
					return 2;
				case "primeirosSocorros":
					return 3;
				case "meioAmbiente":
					return 4;
				case "mecanica":
					return 5;
				}
	}

	function resolveHorario($numberWeek=false,$year=false){

		$mysqli = connect_db();

		if($numberWeek!=false and $year!=false){
			$result = mysqli_query($mysqli,"SELECT horarios FROM px_schedules WHERE wk_number ='".$numberWeek."' AND  year ='".$year."' ");
		}

		else{
			$numberWeek=date('W'); //Semana Atual
			$result = mysqli_query($mysqli,"SELECT horarios FROM px_schedules WHERE wk_number ='".$numberWeek."'");
		}

		$horario = mysqli_fetch_array($result);

		list($segunda,$terca,$quarta,$quinta,$sexta,$sabado) = split('\|',$horario[0]);

		$temp1 = explode(',',$segunda);
		$temp2 = explode(',',$terca);
		$temp3 = explode(',',$quarta);
		$temp4 = explode(',',$quinta);
		$temp5 = explode(',',$sexta);
		$temp6 = explode(',',$sabado);

		$diasSemana=array();

		array_push($diasSemana,$temp1,$temp2,$temp3,$temp4,$temp5,$temp6);

		return $diasSemana;
	}

	function habilitarEdicao(){
		$editar=BASE_URL."editar";
		$home=BASE_URL."home";

		if(($_GET['to']=='editar') and $_SESSION['logado']){
			echo'<div class="col-md-5">
				<a href="'.$home.'"><button class="btn btn-primary btn-block" style="color:white;" type="submit"><strong>Voltar para Home</strong></button></a>
			</div>';
		}

		else
		 echo'<div class="col-md-5">
				<a href="'.$editar.'"><button class="btn btn-primary btn-block" style="color:white;" name="submitEvento" type="submit"><strong>Editar Horários</strong></button></a>
			</div>';
	}

	function geraEdicao($numberWeek=false,$year=false){
		$firstLine=true;

		$dias = array('Segunda-Feira','Terça-Feira','Quarta-Feira',
			'Quinta-Feira','Sexta-Feira','Sábado');

			$horarios = array('Horários','7:00h às 8:00h','8:00h às 9:00h','9:00h às 10:00h',
			'10:00h às 11:00h','11:00h às 12:00h','12:00h às 14:00h','14:00h às 15:00h',		
			'15:00h às 16:00h','16:00h às 17:00h','17:00h às 18:00h','18:00h às 19:00h',
			'19:00h às 20:00h','20:00h às 21:00h');

		echo '
		<table class="table table-striped text-center" style="margin-bottom:70px;">';

		if($numberWeek!=false and $year!=false)
			$arrayHorarios=resolveHorario($numberWeek,$year);

		for($j=0;$j<14;$j++){
			echo "<tr>
					<th>".$horarios[$j].
			"</th>";
				for($i=0;$i<6;$i++)
				{
							  echo '<td>';
							  if($firstLine)
							  	echo'	<span style="text-align:center;"><strong>'.$dias[$i].'</strong></span>';
							   else{
							   	if($j==6)
							   		echo'<strong>Almoço</strong>';	
							   	else
							   		if($numberWeek!=false and $year!=false)
							   			geraSelect($arrayHorarios[$i][$j-1],$i,$j);
							    		//echo'	<input type="text" class="form-control" name="'.$i."-".$j.'" value="'.converteMateria($arrayHorarios[$i][$j-1],false).'">';
							    	else
							    		echo'	<input type="text" class="form-control" name="'.$i."-".$j.'">';
								}

							    echo'</td>';
				}
			echo'</tr>';

			$firstLine=false;
		}
	}

	function voltaSemana(){
			$editar=BASE_URL."editar/";

			if(isset($_GET['wk_number']) and isset($_GET['year']))
				$editar=$editar.$_GET['year']."/".($_GET['wk_number']-1);

			else{
				$ano=date("Y");
				$semana=date("W")-1;

				$editar=$editar.$ano."/".$semana;
			}

			return $editar;
	}

	function avancaSemana(){
			$editar=BASE_URL."editar/";

			if(isset($_GET['wk_number']) and isset($_GET['year']))
				$editar=$editar.$_GET['year']."/".($_GET['wk_number']+1);


			else{
				$ano=date("Y");
				$semana=date("W")+1;

				$editar=$editar.$ano."/".$semana;
			}

			return $editar;
	}

	function geraSelect($arrayHorarios,$i,$j){

		switch($arrayHorarios){
				case -3:
					echo '<select class="form-control text-center padding feriado" name="'.$i."-".$j.'" >
														<option value="feriado" selected="selected" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';
					break;
				case -2:
					echo '<select class="form-control text-center padding almoco" name="'.$i."-".$j.'" >
														<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" selected="selected" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';

					break;

				case -1:
					echo '<select class="form-control text-center padding semAula" name="'.$i."-".$j.'" >
														<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" selected="selected" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';				
					break;

				case 0:
					echo '<select class="form-control text-center padding naoDefinido" name="'.$i."-".$j.'" >
														<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" selected="selected" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';	
					break;

				case 1:
					echo '<select class="form-control text-center padding legislacao" name="'.$i."-".$j.'" >
														<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" selected="selected" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';
					break;				
				
				return;
				case 2:
					echo '<select class="form-control text-center padding direcaoDefensiva" name="'.$i."-".$j.'" >
														<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" selected="selected" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';				
				
					break;

				case 3:
					echo '<select class="form-control text-center padding primeirosSocorros" name="'.$i."-".$j.'" >
												<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" selected="selected" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';				
					break;

				case 4:
					echo '<select class="form-control text-center padding meioAmbiente" name="'.$i."-".$j.'" >
														<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" selected="selected" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';
				
					break;

				case 5:
					echo '<select class="form-control text-center padding mecanica" name="'.$i."-".$j.'" >
														<option value="feriado" class="feriado">Feriado</option>
														<option value="almoco" class="almoco">Almoço</option>
														<option value="semAula" class="semAula">Sem Aula</option>
														<option value="naoDefinido" class="naoDefinido">Não Definido</option>	
													    <option value="legislacao" class="legislacao">Legislacão</option>
													    <option value="meioAmbiente" class="meioAmbiente">Meio Ambiente</option>
													    <option value="mecanica" selected="selected" class="mecanica">Mecânica</option>
													    <option value="primeirosSocorros" class="primeirosSocorros">Primeiros Socorros</option>
													    <option value="direcaoDefensiva" class="direcaoDefensiva">Direção Defensiva</option>
												</select>';

				
					break;
		}
	}


?>
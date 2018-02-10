<?php
const MAIL_REGEX = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';

const TEL_REGEX = "/^0([0-9]){9}$/";

const NAME_REGEX = '/[a-zA-ZáàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ -]{3,25}/';

if(isset($_POST['submit'])) {
	try {
		$serverName = "localhost";
		$dbName = "bdd_ternaire";
		$userName = "root";
		$password = "";
		$errors=array();
		$warnings=array();
        //les valeurs de l'adhérent
        $adh = [];

        //Création d'un objet PDO pour la connexion à la bdd
        //Celui ci prend au plus 4 paramètres : DSN, username, password, tableau d'options
        $db = new PDO('mysql:host='.$serverName.';dbname='.$dbName.';charset=utf8', $userName, $password);

        // set the PDO error mode to exception
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // utiliser la connexion ici

        date_default_timezone_set('UTC');


        try {
			if (validateInputs()) {
				//TODO : fabriquer $adh et enregistrer

			}
		} catch (Exception $e) {
			//TODO signaler les erreurs
			
		}
        //TODO Gérer champ `adh_isvalid` !!!
	

	//Création d'un numéro d'adhésion unique :

	//Acquisition de la date d'adhésion et formatage en : année mois jour sur deux chiffres
	//ex : 181231 <=> 31 décembre 2018 (convention adoptée au 1er janvier 2018)
		$dateAdh = $adh['date_debut'] = date('ymd', strtotime($_POST['adh_date_debut']));

	//Vérification dans la base, de l'existence d'adhésions datant du même jour que celle saisie :
		$query='SELECT COUNT(*) FROM `t_adherent` WHERE `adh_num` LIKE CONCAT("'.$dateAdh.'\\___")';
		$res=$db->query($query);
	//$lastIndex renvoie un nombre entre 0 et n, représentant le nombre d'adhésions à la même date
		$lastIndex=$res->fetchColumn();
		$lastIndex++;
	//Le numéro d'adhésion est ainsi créé sur le modèle : date(yymmdd)_indice(nn)
	//Exemple : la dixième adhésion saisie le 22 janvier 2018 portera le numéro 180122_10
	//La première adhésion du même jour portait le numéro 180122_01.
		//$adh_num = $adh['num'] = $dateAdh.'_'. str_pad($lastIndex, 2, "0", STR_PAD_LEFT);

		if($lastIndex<10){
			$adh_num = $adh['num'] = $dateAdh.'_0'.$lastIndex;
		}
		else{
			$adh_num = $adh['num'] = $dateAdh.'_'.$lastIndex;
		}

		//echo ("Numéro d'adhérent : ".$adh['num'])."<br/><br/><br/>");


	//vérifie que la valeur ne soit pas nulle ou vide
		if(empty($_POST['adh_nom'])){
			$errors["nom"] = "Le nom fait partie des informations obligatoires";
		}
		else{
		//test_input applique trim, stripslashes et htmlspecialchars, cf. bas de page.
			$adh_nom = $adh['nom'] = test_input($_POST['adh_nom']);
		//Vérifie que le nom soit conforme aux attentes (cf. message d'erreur).
			if (!preg_match(NAME_REGEX, $adh_nom)) {
				$errors["nom"] = "Le nom doit être composé d'au moins 3 caractères.\n Sont valides les lettres accentuées ou non, en majuscules ou minuscules, les tirets et espaces.";
			}
		}
		
		if(empty($_POST['adh_prenom'])){
			$errors["prenom"] = "Le prénom fait partie des informations obligatoires";
		}
		else{
		//test_input applique trim, stripslashes et htmlspecialchars, cf. bas de page.
			$adh_prenom = $adh['prenom'] = test_input($_POST['adh_prenom']);
		//Vérifie que le nom soit conforme aux attentes (cf. message d'erreur).
			if (!preg_match(NAME_REGEX, $adh_prenom)) {
				$errors["prenom"] = "Le prénom doit être composé d'au moins 3 caractères.\n Sont valides les lettres accentuées ou non, en majuscules ou minuscules, les tirets et espaces.";
			}
		}
		
		if(empty($_POST['adh_telephone'])){
			$adh_telephone="";
			$warnings["telephone"]="Avertissement : Vous n'avez pas saisi de numéro de téléphone";
		}
		else{
		//test_input applique trim, stripslashes et htmlspecialchars, cf. bas de page.
			$adh_telephone = $adh['telephone'] = test_input($_POST['adh_telephone']);
		//Vérifie que le nom soit conforme aux attentes (cf. message d'erreur).
			if (!preg_match(TEL_REGEX, $adh_telephone)) {
				$warnings["telephone"] = "Avertissement : Le numéro de téléphone doit être composé de 10 chiffres et commencer par \"0\".";
			}
		}
		
		if(empty($_POST['adh_mail'])){
			$errors["mail"] = "Erreur : L'email fait partie des informations obligatoires";
			$adh_mail="";
		}
		else{
		//test_input applique trim, stripslashes et htmlspecialchars, cf. bas de page.
			$adh_mail = $adh['mail'] = test_input($_POST['adh_mail']);
		//Vérifie que le nom soit conforme aux attentes (cf. message d'erreur).
			if (!preg_match(MAIL_REGEX, $adh_mail)){
				$errors["mail"] = "Erreur : L'email ne semble pas valide, contactez un administrateur si vous êtes sûr qu'il n'y a pourtant pas d'erreur.";
			}
		}
	
	//Calcul de la date de fin d'adhésion en fonction de celle de début :
		$dateFinAdh = $adh['date_fin'] = calculateEndDate($_POST['adh_date_debut']); //date('Y-m-d', mktime(0,0,0,$moisFin,1,$anneeFin));
		
		$adh_saisie_date = $adh['saisie_date'] = date("Y-m-d");
		/*if(empty($_POST['adh_saisie_date'])){
			$adh_saisie_date=date("Y-m-d");
		}
		else{
			$adh_saisie_date=$_POST['adh_saisie_date'];
		}*/
		
		
		if(empty($_POST['adh_saisie_auteur'])){
			$adh_saisie_auteur="";
		}
		else{
		//test_input applique trim, stripslashes et htmlspecialchars, cf. bas de page.
			$adh_saisie_auteur = $adh['saisie_auteur'] = test_input($_POST['adh_saisie_auteur']);
		//Vérifie que le nom soit conforme aux attentes (cf. message d'erreur).
			if (!preg_match('/[a-zA-ZáàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ -]{3,25}/', $adh_saisie_auteur)) {
				$warnings[] = "Le prénom doit être composé d'au moins 3 caractères.\n Sont valides les lettres accentuées ou non, en majuscules ou minuscules, les tirets et espaces.";
			}
		}
		
		$adh['type'] = $_POST['adh_type'];
		
	//TO DO : Créer tableau adh 
	//Après vérifications (TO DO), insertion dans la base des valeurs saisies dans le formulaire
		/*$insertAdh = "INSERT INTO `t_adherent`(`adh_num`, `adh_nom`, `adh_prenom`, `adh_telephone`, `adh_mail`, `adh_date_debut`, `adh_date_fin`, `adh_saisie_date`, `adh_saisie_auteur`, `adh_type`, `adh_commentaire`) VALUES ('{$adh_num}','{$adh_nom}','{$adh_prenom}','{$adh_telephone}','{$adh_mail}','{$_POST['adh_date_debut']}','{$dateFinAdh}','{$adh_saisie_date}','{$adh_saisie_auteur}','{$_POST['adh_type']}', '')"; //.test_input($_POST['adh_commentaire'])});*/
		
		if (!empty($errors)){
			echo ("L'adhésion n'a pas été saisie...<br/>");
			foreach($errors as $err){
				echo ("ERREUR : ".$err."<br/>");
			}

		}
		else{
			
			if ($db->query(prepareInsertQuery('t_adherent', $adh))) {
				var_dump($_POST);
				var_dump($adh);
			}
			
			else {
				echo "<script type= 'text/javascript'>alert('Data not successfully Inserted.');</script>";
			}
		}
		if (!empty($warnings)){
			echo("Veuillez vérifier les avertissements suivants et faire appel à un administrateur si une correction s'impose.<br/>");
			foreach($warnings as $warn){
				echo ("ATTENTION : ".$warn."<br/>");
			}
		}

		//et maintenant, fermez-la !
		$db = null;
		
	}

	
	catch(PDOException $e) {
		echo "Connection failed: " . $e->getMessage();
	}
}

else{
	echo ("empty form");
}

function validateInputs() {
	//TODO : valider les $_POST
}

function calculateEndDate($start_date) {
	$moisFin = date('m',strtotime($start_date))+1;
	$anneeFin = date('Y',strtotime($start_date))+1;
	return date('Y-m-d', mktime(0,0,0,$moisFin,1,$anneeFin));
}

function prepareInsertQuery ($table, array $tofeed, $prefix = '') {
	//TODO : remplacer adh par le $prefix
	$keys = "(`adh_".implode("`,`adh_", array_keys($tofeed))."`)";
	$values = "('".implode("','", array_values($tofeed))."')";
	
	return "INSERT INTO `{$table}` {$keys} VALUES {$values} ";
}

function test_input($data){
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}
?>
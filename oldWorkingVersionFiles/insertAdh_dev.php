<?php
const MAIL_REGEX = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';

const TEL_REGEX = "/^0([0-9]){9}$/";

const NAME_REGEX = '/[a-zA-ZáàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ -]{3,25}/';

if(isset($_POST['submit'])) {
    try {
        //Ouvrir la connexion :
        include 'dbCon.php';
        $db = createPdoObject();

        //les valeurs de l'adhérent
        $adh = [];

        date_default_timezone_set('UTC');

        try {
            if (validateInputs()) {
                //TODO : fabriquer $adh et enregistrer
                $adh['isvalid'] = TRUE;

                $adh['date_debut'] = $_POST['adh_date_debut'];

                $adh['num'] = createUniqueAdhNumber($adh['date_debut'], $db);

                //Calcul de la date de fin d'adhésion en fonction de celle de début :
                $adh['date_fin'] = calculateEndDate($_POST['adh_date_debut']); //date('Y-m-d', mktime(0,0,0,$moisFin,1,$anneeFin));

                //La valeur "date de saisie de l'adhésion" prend automatiquement la date du jour.
                $adh['saisie_date'] = date("Y-m-d");

                //Le type d'adhésion est récupéré de la valeur du bouton radio sélectionné dans le formulaire.
                $adh['type'] = $_POST['adh_type'];

                $adh['nom'] = cleanInput($_POST['adh_nom']);
                $adh['prenom'] = cleanInput($_POST['adh_prenom']);
                $adh['telephone'] = cleanInput($_POST['adh_telephone']);
                $adh['mail'] = cleanInput($_POST['adh_mail']);
                $adh['saisie_auteur'] = cleanInput($_POST['adh_saisie_auteur']);

                /*//Technique fainéante => problème de préfixes et champs "submit" en trop...
                foreach ($_POST as $key => $val) {
                    $adh[$key] = cleanInput($val);
                }*/

                var_dump($adh);
                var_dump(prepareInsertQuery('t_adherent', $adh));
            }
        } catch (Exception $e) {
            //TODO signaler les erreurs
        }


        //Après vérifications (TO DO), insertion dans la base des valeurs saisies dans le formulaire
        if($db->query(prepareInsertQuery('t_adherent', $adh))) {
            var_dump($_POST);
            var_dump($adh);
        }

        else {
            echo "<script type= 'text/javascript'>alert('Data not successfully Inserted.');</script>";
        }
        //fermer la connexion :
        delPdoObject($db);
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
    $valid = FALSE;

    //vérifie que la valeur ne soit pas nulle ou vide et qu'elle corresponde aux attentes.

    if(empty($_POST['adh_nom']) || !preg_match(NAME_REGEX, cleanInput($_POST['adh_nom']))){
        throw new Exception('Le nom est obligatoire, il doit être composé d\'au moins 3 caractères :\n Sont valides les lettres accentuées ou non, en majuscules ou minuscules, les tirets et espaces.');
        $valid = FALSE;
    }
    else{
        $valid = TRUE;
    }

    if(empty($_POST['adh_prenom']) || !preg_match(NAME_REGEX, cleanInput($_POST['adh_prenom']))){
        throw new Exception('Le prénom est obligatoire, il doit être composé d\'au moins 3 caractères :\n Sont valides les lettres accentuées ou non, en majuscules ou minuscules, les tirets et espaces.');
        $valid = FALSE;
    }
    else{
        $valid = TRUE;
    }

    if(!empty($_POST['adh_telephone'])){
        if(!preg_match(TEL_REGEX, cleanInput($_POST['adh_telephone']))){
            throw new Exception('Le numéro de téléphone doit être composé de 10 chiffres et commencer par "0".');
            $valid = FALSE;
        }
    }
    else{
        $valid = TRUE;
    }

    if(empty($_POST['adh_mail']) || !preg_match(MAIL_REGEX, cleanInput($_POST['adh_mail']))){
        throw new Exception('L\'email ne semble pas valide, contactez un administrateur si vous êtes sûr qu\'il n\'y a pourtant pas d\'erreur.');
        $valid = FALSE;
    }
    else{
        $valid = TRUE;
    }

    if(empty($_POST['adh_saisie_auteur']) || !preg_match(NAME_REGEX, cleanInput($_POST['adh_saisie_auteur']))){
        throw new Exception('Le nom de l\'auteur de la saisie est obligatoire, il doit être composé d\'au moins 3 caractères :\n Sont valides les lettres accentuées ou non, en majuscules ou minuscules, les tirets et espaces.');
        $valid = FALSE;
    }
    else{
        $valid = TRUE;
    }

    return $valid;
}

function createUniqueAdhNumber($startDate, $connection){
    //Création d'un numéro d'adhésion unique :

    //Acquisition de la date d'adhésion et formatage en : année mois jour sur deux chiffres
    //ex : 181231 <=> 31 décembre 2018 (convention adoptée au 1er janvier 2018)
    $adhNum = date('ymd', strtotime($startDate));

    //Vérification dans la base, de l'existence d'adhésions datant du même jour que celle saisie :
    $query = 'SELECT COUNT(*) FROM `t_adherent` WHERE `adh_num` LIKE CONCAT("'.$adhNum.'\\___")';
    $res = $connection->query($query);
    //$lastIndex renvoie un nombre entre 0 et n, représentant le nombre d'adhésions à la même date
    $lastIndex = $res->fetchColumn();
    $lastIndex++;
    //Le numéro d'adhésion est ainsi créé sur le modèle : date(yymmdd)_indice(nn)
    //Exemple : la dixième adhésion saisie le 22 janvier 2018 portera le numéro 180122_10
    //La première adhésion du même jour portait le numéro 180122_01.
    //$adh_num = $adh['num'] = $dateAdh.'_'. str_pad($lastIndex, 2, "0", STR_PAD_LEFT);
    if($lastIndex<10){
        $adhNum = $adhNum.'_0'.$lastIndex;
    }
    else{
        $adhNum = $adhNum.'_'.$lastIndex;
    }
    return $adhNum;
}

//Calcule la date de fin d'adhésion à partir de la date de début.
function calculateEndDate($start_date) {
    $moisFin = date('m',strtotime($start_date))+1;
    $anneeFin = date('Y',strtotime($start_date))+1;
    return date('Y-m-d', mktime(0,0,0,$moisFin,1,$anneeFin));
}

//écrit la requête à envoyer à la base pour l'insertion.
function prepareInsertQuery($table, array $tofeed, $prefix = '') {
    //TODO : remplacer adh par le $prefix
    $keys = "(`adh_".implode("`,`adh_", array_keys($tofeed))."`)";
    $values = "('".implode("','", array_values($tofeed))."')";

    return "INSERT INTO `{$table}` {$keys} VALUES {$values}";
}

//cleanInput nettoie la chaine de tout caractère indésirable.
function cleanInput($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
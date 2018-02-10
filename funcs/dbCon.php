<?php
    function createPdoObject()
    {
        try {
            $config = parse_ini_file(__DIR__ . "/config.ini");

            //Création d'un objet PDO pour la connexion à la bdd
            //Celui ci prend au plus 4 paramètres : DSN, username, password, tableau d'options
            $dbcon = new PDO($config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=' . $config['charset'], $config['user'], $config['passwd']);

            // set the PDO error mode to exception
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $dbcon;
            /*$query = 'SELECT * FROM t_adherent';
            foreach ($dbcon->query($query) as $row){
                var_dump($row);
            }*/
        }

        catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    function delPdoObject($dbcon){
        $dbcon=null;
    }

<?php

function adminer_object()
{

    class AdminerSoftware extends Adminer
    {

        /**
         * @return return always true
         */
        function login($login, $password)
        {
            return true;
        }

        //skryje výběr databáze
        function databasesPrint($missing)
        {
        }

        function homepage()
        {
            return true;
        }
    }

    return new AdminerSoftware;
}


if(isset($_GET['guid'])){

    $guid=$_GET['guid'];
    $guid = preg_replace("/[^a-zA-Z0-9]+/", "", $guid); //remove all except alphanumeric

    if(strlen($guid)!=30){
        die('Guid is not valid');
    }


    define("DB", '../../../_DB/created/'.$guid.'.sqlite');

}
else{
    define("DB", $_GET["db"]);
}

session_start();
$_SESSION["pwds"]['sqlite'][''][''] = "";
$_SESSION["db"]['sqlite'][''][''][DB] = true;




include "adminer.php";

if (isset($_GET['schema'])) {
?>
    <style>
        #menu {
            display: none
        }

        #content {
            margin: 0;
            padding-left: 30px
        }
        .links{
            display:none
        }
    </style>
<?php
}

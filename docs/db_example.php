<?php

$host = “127.0.0.1”;
$db = “personal_contacts_database”;
$user = “ApiUser”;
$pass = “FakePassword”;


$dsn = “mysql:host=$host;dbname=$db”;
$options = [    
	PDO::ATTR_ERRMODE  => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES   => false
    ];
    
    try{
    	$pdo = new PDO($dsn, $user, $pass, $options);
    	echo "Connected to database.";
    	
    }
    catch(\PDOException $e){
    	error_log($e->getMessage());
    	exit("Connection failed.");
    	
    }

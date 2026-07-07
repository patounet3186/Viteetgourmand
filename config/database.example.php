<?php

function getDatabase(): PDO
{
    $host = 'mysql-votrecompte.alwaysdata.net';
    $dbname = 'votrecompte_viteetgourmand';
    $username = 'votre_utilisateur';
    $password = 'votre_mot_de_passe';

    return new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

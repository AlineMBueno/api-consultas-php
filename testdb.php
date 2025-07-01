<?php
$host = 'localhost:3307';
$db   = 'consultas_api';
$user = 'apiuser';
$pass = 'senhaSegura123';


echo $host;
echo  "<br>";
echo $db ;
echo  "<br>";
echo $user;
echo  "<br>";
echo $pass;
echo  "<br>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✅ Conexão com banco de dados OK!";
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage();
}

echo $host;
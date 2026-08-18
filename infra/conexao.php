<?php

$host = "localhost";
$user = "root";
$senha = "";
$banco = "cadastro_pratos";


$conexao = new msqli($host, $user, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro na conexão combanco: ". $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

?>
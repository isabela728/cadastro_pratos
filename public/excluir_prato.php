<?php
include "../infra/conexao.php";

 $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
 if($id <= 0){
	 die('ID inválido.');
 }

 $sql = "DELETE FROM pratos WHERE id_prato = ?";
 $stmt = $conexao->prepare($sql);
 if ($stmt) {
	 $stmt->bind_param("i", $id);
	 $stmt->execute();
	 $stmt->close();
 }


 header("Location: ../index.php?deleted=1");
?>
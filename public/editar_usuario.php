<?php
include "../infra/conexao.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
if($id <= 0){
    die('ID inválido.');
}

// Processa atualização (POST)
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if($nome === '' || $email === ''){
        $erro = 'Nome e email são obrigatórios.';
    } else {
        $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id_usuario = ?";
        $stmt = $conexao->prepare($sql);
        if(!$stmt) die('Prepare falhou: ' . $conexao->error);
        $stmt->bind_param('ssi', $nome, $email, $id);
        if($stmt->execute()){
            $stmt->close();
            header('Location: cadastrar_usuario.php?edited=1');
            exit();
        } else {
            $erro = 'Erro ao atualizar: ' . $stmt->error;
            $stmt->close();
        }
    }
}

// Busca usuário para preencher o formulário
$sql = "SELECT id_usuario, nome, email FROM usuarios WHERE id_usuario = ?";
$stmt = $conexao->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();
} else {
    $usuario = null;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cadastro de Pratos</title>
    <link rel="stylesheet" href="style/styles.css">
</head>

<body>
    <header>
        <h1>Editar usuário</h1>
    </header>
    <main>
        <h2>Editando o usuário <?php echo htmlspecialchars($usuario["nome"] ?? ''); ?>!</h2>
        <form action="editar_usuario.php?id=<?php echo urlencode($id); ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario["id_usuario"] ?? ''); ?>">

            <label for="nome">Nome:</label>
            <input type="text" name="nome" required value="<?php echo htmlspecialchars($usuario["nome"] ?? ''); ?>">
            <br>
            <label for="email">Email:</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($usuario["email"] ?? ''); ?>">
            <br>
            <button type="submit">Atualizar</button>
        </form>

    </main>
    <footer>

    </footer>


</body>

</html>

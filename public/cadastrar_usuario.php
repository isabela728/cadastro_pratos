<?php
session_start();

include("../infra/conexao.php");

$erro_msg = '';
$success_msg = '';

if(isset($_GET['success']) && $_GET['success'] == '1'){
    $success_msg = 'Usuário cadastrado com sucesso!';
}
if(isset($_GET['edited']) && $_GET['edited'] == '1'){
    $success_msg = 'Usuário atualizado com sucesso!';
}
if(isset($_GET['deleted']) && $_GET['deleted'] == '1'){
    $success_msg = 'Usuário excluído com sucesso!';
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if($nome === '' || mb_strlen($nome) > 100){
        $erro_msg = 'Preencha todos os campos corretamente.';
    } elseif($email === '' || mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erro_msg = 'Preencha todos os campos corretamente.';
    } else {
        $sql = "INSERT INTO usuarios (nome,email) VALUES (?, ?)";

        $stmt = $conexao->prepare($sql);
        if($stmt === false){
            die('Prepare falhou: ' . $conexao->error);
        }
        $stmt->bind_param("ss", $nome, $email);

        if($stmt->execute() === TRUE){
            $stmt->close();
            echo '<script>window.top.location.href = "../index.php";</script>';
            exit();
        }else{
            $erro_msg = 'Erro ao cadastrar: ' . $stmt->error;
            $stmt->close();
        }
    }

}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/syle.css">
    <title>Cadastrar usuário</title>
</head>
<body class="pagina_formulario">

    <div class="formulario_cabecalho">
        <h1>Novo Usuário</h1>
    </div>
    <form class="formulario" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <br>
        <label>Nome:</label>
        <input type="text" name="nome" required value="<?php echo htmlspecialchars($nome ?? ''); ?>">
        <br>
        <?php if($erro_msg): ?>
            <div class="mensagem_erro"><?php echo htmlspecialchars($erro_msg); ?></div>
        <?php endif; ?>
        <?php if($success_msg): ?>
            <div style="color: green"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <br>
        <div class="botoes_forms">
            <a class="botao" href="../index.php" target="_top">Cancelar</a>
            <button class="botao botao_principal" type="submit">Cadastrar usuário</button>
        </div>
    </form>

</body>
</html>
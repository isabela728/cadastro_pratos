<?php
session_start();

include("../infra/conexao.php");

$erro_msg = '';

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nome === '' || mb_strlen($nome) > 100 || $email === '' || mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            echo '<!doctype html><html><head><meta charset="utf-8"><title>Sucesso</title></head><body>';
            echo '<script>if (top) top.location.href = "../index.php?success_user=1"; else location.href = "../index.php?success_user=1";</script>';
            echo '</body></html>';
            exit();
        } else {
            $_SESSION['popup_error'] = 'Erro ao cadastrar: ' . $stmt->error;
            $stmt->close();
            header('Location: ../index.php');
            exit();
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
        <input type="email" name="email" required value="<?php echo $email ?? ''; ?>">
        <br>
        <label>Nome:</label>
        <input type="text" name="nome" required value="<?php echo $nome ?? ''; ?>">
        <br>
        <?php if($erro_msg): ?>
            <div class="mensagem_erro"><?php echo $erro_msg; ?></div>
        <?php endif; ?>
        <br>
        <div class="botoes_forms">
            <a class="botao" href="../index.php" target="_top">Cancelar</a>
            <button class="botao botao_principal" type="submit">Cadastrar usuário</button>
        </div>
    </form>

</body>
</html>
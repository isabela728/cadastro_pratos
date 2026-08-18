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

    if($nome === '' || $email === ''){
        $erro_msg = 'Preencha todos os campos antes de cadastrar.';
    } else {
        $sql = "INSERT INTO usuarios (nome,email) VALUES (?, ?)";

        $stmt = $conexao->prepare($sql);
        if($stmt === false){
            die('Prepare falhou: ' . $conexao->error);
        }
        $stmt->bind_param("ss", $nome, $email);

        if($stmt->execute() === TRUE){
            $stmt->close();
            header('Location: ../index.php?sucesso=usuario');
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
        <p class="texto_pequeno">GESTÃO INTERNA</p>
        <h1>Novo integrante</h1>
    </div>
    <form class="formulario" method="POST" target="_top">
        <label>Email:</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <br>
        <label>Nome:</label>
        <input type="text" name="nome" required value="<?php echo htmlspecialchars($nome ?? ''); ?>">
        <br>
        <?php if($erro_msg): ?>
            <div style="color: red"><?php echo $erro_msg; ?></div>
        <?php endif; ?>
        <?php if($success_msg): ?>
            <div style="color: green"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <br>
        <div class="rodape_formulario">
            <a class="botao botao_claro" href="../index.php" target="_top">Cancelar</a>
            <button class="botao botao_principal" type="submit">Cadastrar membro</button>
        </div>
    </form>
    <div class="lista_secundaria">
    <?php
    
        $sql = "SELECT * FROM usuarios";
        $resultado = $conexao->query($sql);

        if($resultado === false){
            echo '<div style="color:orange">Não foi possível listar usuários: ' . htmlspecialchars($conexao->error) . '</div>';
        } else {
            if ($resultado->num_rows > 0) {
                    echo "<h4>Usuários cadastrados:</h4>";
                    echo "<table border='1' cellpadding='6' cellspacing='0'>";
                    echo "<thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>";
                    while($row = $resultado->fetch_assoc()) {
                        $id = isset($row['id_usuario']) ? htmlspecialchars($row['id_usuario']) : '';
                        $nome = isset($row['nome']) ? htmlspecialchars($row['nome']) : '';
                        $email = isset($row['email']) ? htmlspecialchars($row['email']) : '';
                        $editar_usuario = "editar_usuario.php?id=" . urlencode($id);
                        $excluir_usuario = "excluir_usuario.php?id=" . urlencode($id);
                        echo "<tr>
                                <td>" . $id . "</td>
                                <td>" . $nome . "</td>
                                <td>" . $email . "</td>
                                <td><a href='" . $editar_usuario . "'>Editar</a> | <a href='" . $excluir_usuario . "' onclick=\"return confirm('Confirma exclusão deste usuário?');\">Excluir</a></td></tr>";
                    }
                    echo "</tbody>
                        </table>";
            } else {
                echo "Nenhum usuário cadastrado.";
            }
        }

    ?>
    </div>



</body>
</html>
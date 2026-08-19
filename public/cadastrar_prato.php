<?php
session_start();

include("../infra/conexao.php");

$erro_msg = '';
$usuarios = [];

$nome = $descricao = $preco = $categoria = '';
$id_usuario = 0;

$stmt_usuarios = $conexao->prepare("SELECT id_usuario, nome FROM usuarios ORDER BY nome");
if ($stmt_usuarios) {
    $stmt_usuarios->execute();
    $resultado_usuarios = $stmt_usuarios->get_result();
    while ($usuario = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
    $stmt_usuarios->close();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(',', '.', trim($_POST['preco'] ?? ''));
    $categoria = trim($_POST['categoria'] ?? '');
    $id_usuario = (int) ($_POST['id_usuario'] ?? 0);

    $usuario_existe = false;
    $consulta_usuario = $conexao->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
    if ($consulta_usuario) {
        $consulta_usuario->bind_param('i', $id_usuario);
        $consulta_usuario->execute();
        $usuario_existe = $consulta_usuario->get_result()->num_rows > 0;
        $consulta_usuario->close();
    }

    if($nome === '' || mb_strlen($nome) > 100 || $descricao === '' || mb_strlen($descricao) > 200 || $preco === '' || !is_numeric($preco) || (float) $preco < 0 || (float) $preco > 99999999.99 || $categoria === '' || mb_strlen($categoria) > 50 || !$usuario_existe){
        $erro_msg = 'Preencha todos os campos corretamente.';
    } else {
        $sql = "INSERT INTO pratos (nome, descricao, preco, categoria, id_usuario) VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);
        if($stmt === false){
            die('Prepare falhou: ' . $conexao->error);
        }
        $preco = (float) $preco;
        $stmt->bind_param("ssdsi", $nome, $descricao, $preco, $categoria, $id_usuario);

        if($stmt->execute() === TRUE){
            $stmt->close();
            echo '<!doctype html><html><head><meta charset="utf-8"><title>Sucesso</title></head><body>';
            echo '<script>if (top) top.location.href = "../index.php?success=1"; else location.href = "../index.php?success=1";</script>';
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
    <title>Cadastrar prato</title>
</head>
<body class="pagina_formulario">

    <div class="formulario_cabecalho">
        <h1>Novo prato</h1>
    </div>
    <form class="formulario" method="POST">
        <label>Nome:</label>
        <input type="text" name="nome" required value="<?php echo $nome ?? ''; ?>">
        <br>
        <label>Descrição:</label>
        <input type="text" name="descricao" required value="<?php echo $descricao ?? ''; ?>">
        <br>
        <label>Preço:</label>
        <input type="number" name="preco" step="0.01" min="0" required value="<?php echo $preco ?? ''; ?>">
        <br>
        <label>Categoria:</label>
        <select name="categoria" required>
            <option value="">Selecione a categoria</option>
            <option value="Lanche" <?php echo $categoria === 'Lanche' ? 'selected' : ''; ?>>Lanche</option>
            <option value="Prato Principal" <?php echo $categoria === 'Prato Principal' ? 'selected' : ''; ?>>Prato Principal</option>
            <option value="Sobremesa" <?php echo $categoria === 'Sobremesa' ? 'selected' : ''; ?>>Sobremesa</option>
            <option value="Bebida" <?php echo $categoria === 'Bebida' ? 'selected' : ''; ?>>Bebida</option>
        </select>
        <br>
        <label>Usuário responsável:</label>
        <select name="id_usuario" required>
            <option value="">Selecione o usuário</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?php echo $usuario['id_usuario']; ?>" <?php echo (int) ($id_usuario ?? 0) === (int) $usuario['id_usuario'] ? 'selected' : ''; ?>>
                    <?php echo $usuario['nome']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <?php if($erro_msg): ?>
            <div class="mensagem_erro"><?php echo $erro_msg; ?></div>
        <?php endif; ?>
        
        <br>
        <div class="botoes_forms">
            <a class="botao" href="../index.php" target="_top">Cancelar</a>
            <button class="botao botao_principal" type="submit">Salvar prato</button>
        </div>
    </form>
</body>
</html>
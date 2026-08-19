<?php
include "../infra/conexao.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$usuarios = [];
$erro = '';

$stmt_usuarios = $conexao->prepare("SELECT id_usuario, nome FROM usuarios ORDER BY nome");
if ($stmt_usuarios) {
    $stmt_usuarios->execute();
    $resultado_usuarios = $stmt_usuarios->get_result();
    while ($usuario = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
    $stmt_usuarios->close();
}

if($id <= 0){
    die('ID inválido.');
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
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

    if ($nome === '' || mb_strlen($nome) > 100 || $descricao === '' || mb_strlen($descricao) > 200 || $preco === '' || !is_numeric($preco) || (float) $preco < 0 || (float) $preco > 99999999.99 || $categoria === '' || mb_strlen($categoria) > 50 || !$usuario_existe) {
        $erro = 'Preencha todos os campos corretamente.';
    } else {
        $sql = "UPDATE pratos SET nome = ?, descricao = ?, preco = ?, categoria = ?, id_usuario = ? WHERE id_prato = ?";
        $stmt = $conexao->prepare($sql);
        if(!$stmt) die('Prepare falhou: ' . $conexao->error);
        $preco = (float) $preco;
        $stmt->bind_param('ssdsii', $nome, $descricao, $preco, $categoria, $id_usuario, $id);
        if ($stmt->execute()) {
            $stmt->close();
            echo '<!doctype html><html><head><meta charset="utf-8"><title>Sucesso</title></head><body>';
            echo '<script>if (top) top.location.href = "../index.php"; else location.href = "../index.php";</script>';
            echo '</body></html>';
            exit();
        } else {
            $erro = 'Erro ao atualizar: ' . $stmt->error;
            $stmt->close();
        }
    }
}

$sql = "SELECT id_prato, nome, descricao, preco, categoria, id_usuario FROM pratos WHERE id_prato = ?";
$stmt = $conexao->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $prato = $resultado->fetch_assoc();
    $stmt->close();
} else {
    $prato = null;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/syle.css">
    <title>Editar prato</title>
</head>

<body class="pagina_formulario">
    <div class="formulario_cabecalho">
        <h1>Editar prato</h1>
    </div>
    <main class="formulario">
        <p class="subtitulo">Atualize as informações de <?php echo $prato["nome"] ?? ''; ?>.</p>
        <form action="editar_pratos.php?id=<?php echo urlencode($id); ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $prato["id_prato"] ?? ''; ?>">

            <label for="nome">Nome:</label>
            <input type="text" name="nome" required value="<?php echo $prato["nome"] ?? ''; ?>">
            <br>
            <label for="descricao">Descrição:</label>
            <input type="text" name="descricao" required value="<?php echo $prato["descricao"] ?? ''; ?>">
            <br>
            <label for="preco">Preço:</label>
            <input type="number" name="preco" step="0.01" min="0" required value="<?php echo $prato["preco"] ?? ''; ?>">
            <br>
            <label for="categoria">Categoria:</label>
            <select name="categoria" required>
                <option value="">Selecione a categoria</option>
                <option value="Lanche" <?php echo ($prato["categoria"] ?? '') === 'Lanche' ? 'selected' : ''; ?>>Lanche</option>
                <option value="Prato Principal" <?php echo ($prato["categoria"] ?? '') === 'Prato Principal' ? 'selected' : ''; ?>>Prato Principal</option>
                <option value="Sobremesa" <?php echo ($prato["categoria"] ?? '') === 'Sobremesa' ? 'selected' : ''; ?>>Sobremesa</option>
                <option value="Bebida" <?php echo ($prato["categoria"] ?? '') === 'Bebida' ? 'selected' : ''; ?>>Bebida</option>
            </select>
            <br>
            <label for="id_usuario">Usuário responsável:</label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">Selecione o usuário</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['id_usuario']; ?>" <?php echo (int) ($prato['id_usuario'] ?? 0) === (int) $usuario['id_usuario'] ? 'selected' : ''; ?>>
                        <?php echo $usuario['nome']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <div class="botoes_forms">
                <a class="botao" href="../index.php" target="_top">Cancelar</a>
                <button class="botao botao_principal" type="submit">Salvar alterações</button>
            </div>
        </form>
        <?php if($erro): ?>
            <div class="mensagem_erro"><?php echo $erro; ?></div>
        <?php endif; ?>
    </main>
</body>

</html>

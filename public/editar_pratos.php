<?php
include "../infra/conexao.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$usuarios = [];
$erro = '';

$resultado_usuarios = $conexao->query("SELECT id_usuario, nome FROM usuarios ORDER BY nome");
if ($resultado_usuarios) {
    while ($usuario = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
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

    if($nome === '' || mb_strlen($nome) > 100){
        $erro = 'Preencha todos os campos corretamente.';
    } elseif($descricao === '' || mb_strlen($descricao) > 200){
        $erro = 'Preencha todos os campos corretamente.';
    } elseif($preco === '' || !is_numeric($preco) || (float) $preco < 0 || (float) $preco > 99999999.99){
        $erro = 'Preencha todos os campos corretamente.';
    } elseif($categoria === '' || mb_strlen($categoria) > 50){
        $erro = 'Preencha todos os campos corretamente.';
    } elseif(!$usuario_existe){
        $erro = 'Preencha todos os campos corretamente.';
    } else {
        $sql = "UPDATE pratos SET nome = ?, descricao = ?, preco = ?, categoria = ?, id_usuario = ? WHERE id_prato = ?";
        $stmt = $conexao->prepare($sql);
        if(!$stmt) die('Prepare falhou: ' . $conexao->error);
        $preco = (float) $preco;
        $stmt->bind_param('ssdsii', $nome, $descricao, $preco, $categoria, $id_usuario, $id);
        if($stmt->execute()){
            $stmt->close();
            echo '<script>window.top.location.href = "../index.php";</script>';
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
        <p class="subtitulo">Atualize as informações de <?php echo htmlspecialchars($prato["nome"] ?? ''); ?>.</p>
        <form action="editar_pratos.php?id=<?php echo urlencode($id); ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($prato["id_prato"] ?? ''); ?>">

            <label for="nome">Nome:</label>
            <input type="text" name="nome" required value="<?php echo htmlspecialchars($prato["nome"] ?? ''); ?>">
            <br>
            <label for="descricao">Descrição:</label>
            <input type="text" name="descricao" required value="<?php echo htmlspecialchars($prato["descricao"] ?? ''); ?>">
            <br>
            <label for="preco">Preço:</label>
            <input type="number" name="preco" step="0.01" min="0" required value="<?php echo htmlspecialchars($prato["preco"] ?? ''); ?>">
            <br>
            <label for="categoria">Categoria:</label>
            <input type="text" name="categoria" required value="<?php echo htmlspecialchars($prato["categoria"] ?? ''); ?>">
            <br>
            <label for="id_usuario">Usuário responsável:</label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">Selecione o usuário</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>" <?php echo (int) ($prato['id_usuario'] ?? 0) === (int) $usuario['id_usuario'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($usuario['nome']); ?>
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
            <div class="mensagem_erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
    </main>
</body>

</html>

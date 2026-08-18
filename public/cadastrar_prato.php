<?php
session_start();

include("../infra/conexao.php");

$erro_msg = '';
$success_msg = '';
$usuarios = [];

$resultado_usuarios = $conexao->query("SELECT id_usuario, nome FROM usuarios ORDER BY nome");
if ($resultado_usuarios) {
    while ($usuario = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
}

if(isset($_GET['success']) && $_GET['success'] == '1'){
    $success_msg = 'Prato cadastrado com sucesso!';
}
if(isset($_GET['edited']) && $_GET['edited'] == '1'){
    $success_msg = 'Prato atualizado com sucesso!';
}
if(isset($_GET['deleted']) && $_GET['deleted'] == '1'){
    $success_msg = 'Prato excluído com sucesso!';
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(',', '.', trim($_POST['preco'] ?? ''));
    $categoria = trim($_POST['categoria'] ?? '');
    $id_usuario = (int) ($_POST['id_usuario'] ?? 0);

    if($nome === '' || $descricao === '' || $preco === '' || !is_numeric($preco) || $categoria === '' || $id_usuario <= 0){
        $erro_msg = 'Preencha todos os campos antes de cadastrar.';
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
            header('Location: ../index.php?sucesso=prato');
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
    <title>Cadastrar prato</title>
</head>
<body class="pagina_formulario">

    <div class="formulario_cabecalho">
        <p class="texto_pequeno">FICHA TÉCNICA</p>
        <h1>Novo prato</h1>
    </div>
    <form class="formulario" method="POST" target="_top">
        <label>Nome:</label>
        <input type="text" name="nome" required value="<?php echo htmlspecialchars($nome ?? ''); ?>">
        <br>
        <label>Descrição:</label>
        <input type="text" name="descricao" required value="<?php echo htmlspecialchars($descricao ?? ''); ?>">
        <br>
        <label>Preço:</label>
        <input type="number" name="preco" step="0.01" min="0" required value="<?php echo htmlspecialchars($preco ?? ''); ?>">
        <br>
        <label>Categoria:</label>
        <input type="text" name="categoria" required value="<?php echo htmlspecialchars($categoria ?? ''); ?>">
        <br>
        <label>Usuário responsável:</label>
        <select name="id_usuario" required>
            <option value="">Selecione o usuário</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>" <?php echo (int) ($id_usuario ?? 0) === (int) $usuario['id_usuario'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($usuario['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
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
            <button class="botao botao_principal" type="submit">Salvar prato</button>
        </div>
    </form>
    <div class="lista_secundaria">
    <?php
    
        $sql = "SELECT * FROM pratos";
        $resultado = $conexao->query($sql);

        if($resultado === false){
            echo '<div style="color:orange">Não foi possível listar pratos: ' . htmlspecialchars($conexao->error) . '</div>';
        } else {
            if ($resultado->num_rows > 0) {
                    echo "<h4>Pratos cadastrados:</h4>";
                    echo "<table border='1' cellpadding='6' cellspacing='0'>";
                    echo "<thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Preço</th>
                                <th>Categoria</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>";
                    while($row = $resultado->fetch_assoc()) {
                        $id = isset($row['id_prato']) ? htmlspecialchars($row['id_prato']) : '';
                        $nome = isset($row['nome']) ? htmlspecialchars($row['nome']) : '';
                        $descricao = isset($row['descricao']) ? htmlspecialchars($row['descricao']) : '';
                        $preco = isset($row['preco']) ? htmlspecialchars($row['preco']) : '';
                        $categoria = isset($row['categoria']) ? htmlspecialchars($row['categoria']) : '';
                        $editar_prato = "editar_pratos.php?id=" . urlencode($id);
                        $excluir_prato = "excluir_prato.php?id=" . urlencode($id);
                        echo "<tr>
                                <td>" . $id . "</td>
                                <td>" . $nome . "</td>
                                <td>" . $descricao . "</td>
                                <td>" . $preco . "</td>
                                <td>" . $categoria . "</td>
                                <td><a href='" . $editar_prato . "'>Editar</a> | <a href='" . $excluir_prato . "' onclick=\"return confirm('Confirma exclusão deste prato?');\">Excluir</a></td></tr>";
                    }
                    echo "</tbody>
                        </table>";
            } else {
                echo "Nenhum prato cadastrado.";
            }
        }

    ?>
    </div>



</body>
</html>
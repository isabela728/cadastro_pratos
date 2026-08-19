<?php
session_start();

include "infra/conexao.php";

$busca = trim($_GET['busca'] ?? '');
$id_usuario = (int) ($_GET['id_usuario'] ?? 0);
$tela_aberta = $_GET['abrir'] ?? '';
$pratos = [];
$usuarios = [];

$stmt_usuarios = $conexao->prepare("SELECT id_usuario, nome FROM usuarios ORDER BY nome");
if ($stmt_usuarios) {
    $stmt_usuarios->execute();
    $resultado_usuarios = $stmt_usuarios->get_result();
    while ($usuario = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
    $stmt_usuarios->close();
}

$sql_pratos = "SELECT pratos.id_prato, pratos.nome, pratos.descricao, pratos.preco,
                      pratos.categoria, usuarios.nome AS nome_usuario,
                      usuarios.email AS email_usuario
               FROM pratos
               LEFT JOIN usuarios ON usuarios.id_usuario = pratos.id_usuario
               WHERE (pratos.nome LIKE ? OR pratos.descricao LIKE ?)
                 AND (? = 0 OR pratos.id_usuario = ?)
               ORDER BY pratos.id_prato DESC";

$stmt_pratos = $conexao->prepare($sql_pratos);
$texto_busca = "%{$busca}%";
$stmt_pratos->bind_param('ssii', $texto_busca, $texto_busca, $id_usuario, $id_usuario);
$stmt_pratos->execute();
$resultado_pratos = $stmt_pratos->get_result();

while ($prato = $resultado_pratos->fetch_assoc()) {
    $pratos[] = $prato;
}

$mensagem_sucesso = '';
$mensagem_popup = '';

if(isset($_GET['success']) && $_GET['success'] == '1'){
    $mensagem_sucesso = 'Prato cadastrado com sucesso!';
}
if(isset($_GET['edited']) && $_GET['edited'] == '1'){
    $mensagem_sucesso = 'Prato atualizado com sucesso!';
}
if(isset($_GET['deleted']) && $_GET['deleted'] == '1'){
    $mensagem_sucesso = 'Prato excluído com sucesso!';
}

if(isset($_GET['success_user']) && $_GET['success_user'] == '1'){
    $mensagem_sucesso = 'Usuário cadastrado com sucesso!';
}
if(isset($_GET['edited_user']) && $_GET['edited_user'] == '1'){
    $mensagem_sucesso = 'Usuário atualizado com sucesso!';
}
if(isset($_GET['deleted_user']) && $_GET['deleted_user'] == '1'){
    $mensagem_sucesso = 'Usuário excluído com sucesso!';
}

if (!empty($_SESSION['erro_popup'])) {
    $mensagem_popup = $_SESSION['erro_popup'];
    unset($_SESSION['erro_popup']);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/syle.css">
    <title>Cadastro de Pratos</title>
</head>
<body>
    <header>
        <div>
            <h1>Cadastro de Pratos</h1>
        </div>
        <nav class="botoes_cabecalho">
            <a class="botao" href="?abrir=usuario">Cadastrar usuário</a>
            <a class="botao botao_principal" href="?abrir=prato">Novo prato</a>
        </nav>
    </header>

    <main>
        <section class="filtros">
            <form method="GET">
                <label for="busca">Buscar prato</label>
                <input id="busca" type="search" name="busca" value="<?php echo $busca; ?>" placeholder="Digite o nome ou descrição">

                <label for="id_usuario">Responsável</label>
                <select id="id_usuario" name="id_usuario">
                    <option value="0">Todos os responsáveis</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['id_usuario']; ?>" <?php echo $id_usuario === (int) $usuario['id_usuario'] ? 'selected' : ''; ?>>
                            <?php echo $usuario['nome']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="botao botao_principal" type="submit">Buscar</button>
            </form>
        </section>

        <section class="tabela_container">
            <div class="titulo_tabela">
                <h2>Pratos cadastrados</h2>
                <span><?php echo count($pratos); ?> resultado(s)</span>
            </div>

            <?php if (count($pratos) > 0): ?>
                <div class="tabela_rolagem">
                    <table>
                        <thead>
                            <tr>
                                <th>Prato</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Preço</th>
                                <th>Responsável</th>
                                <th>Email</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pratos as $prato): ?>
                                <tr>
                                    <td><strong><?php echo $prato['nome']; ?></strong></td>
                                    <td><?php echo $prato['descricao']; ?></td>
                                    <td><span class="tag"><?php echo $prato['categoria']; ?></span></td>
                                    <td class="preco">R$ <?php echo number_format((float) $prato['preco'], 2, ',', '.'); ?></td>
                                    <td><?php echo $prato['nome_usuario'] ?: 'Não informado'; ?></td>
                                    <td><?php echo $prato['email_usuario'] ?: 'Não informado'; ?></td>
                                    <td class="acoes">
                                        <a href="?abrir=editar_prato&id=<?php echo $prato['id_prato']; ?>">Editar</a>
                                        <a href="public/excluir_prato.php?id=<?php echo $prato['id_prato']; ?>" onclick="return confirm('Deseja excluir este prato?');">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mensagem_vazia">Nenhum prato encontrado</p>
            <?php endif; ?>
        </section>

    </main>

    <?php if ($tela_aberta === 'prato' || $tela_aberta === 'usuario' || $tela_aberta === 'editar_prato'): ?>
        <div class="fundo_modal">
            <section class="modal<?php echo $tela_aberta === 'prato' ? ' modal_prato' : ''; ?><?php echo $tela_aberta === 'usuario' ? ' modal_usuario' : ''; ?><?php echo $tela_aberta === 'editar_prato' ? ' modal_editar_prato' : ''; ?>">
                <a class="fechar_modal" href="index.php" aria-label="Fechar">&times;</a>
                <?php if ($tela_aberta === 'editar_prato'): ?>
                    <iframe src="public/editar_pratos.php?id=<?php echo (int) ($_GET['id'] ?? 0); ?>" title="Editar prato"></iframe>
                <?php else: ?>
                    <iframe src="public/cadastrar_<?php echo $tela_aberta === 'prato' ? 'prato' : 'usuario'; ?>.php" title="Cadastro"></iframe>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagem_sucesso) || !empty($mensagem_popup)): ?>
        <div class="popup <?php echo !empty($mensagem_sucesso) ? 'success' : 'error'; ?> show" role="alert" aria-live="assertive">
            <a class="fechar_mensagem" href="index.php" aria-label="Fechar">&times;</a>
            <div><?php echo !empty($mensagem_sucesso) ? $mensagem_sucesso : $mensagem_popup; ?></div>
        </div>
    <?php endif; ?>
</body>
</html>
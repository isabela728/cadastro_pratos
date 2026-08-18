<?php

include "infra/conexao.php";

$busca = trim($_GET['busca'] ?? '');
$id_usuario = (int) ($_GET['id_usuario'] ?? 0);
$tela_aberta = $_GET['abrir'] ?? '';
$pratos = [];
$usuarios = [];

$resultado_usuarios = $conexao->query("SELECT id_usuario, nome FROM usuarios ORDER BY nome");
if ($resultado_usuarios) {
    while ($usuario = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
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

function escapar($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
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
                <input id="busca" type="search" name="busca" value="<?php echo escapar($busca); ?>" placeholder="Digite o nome ou descrição">

                <label for="id_usuario">Responsável</label>
                <select id="id_usuario" name="id_usuario">
                    <option value="0">Todos os responsáveis</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo escapar($usuario['id_usuario']); ?>" <?php echo $id_usuario === (int) $usuario['id_usuario'] ? 'selected' : ''; ?>>
                            <?php echo escapar($usuario['nome']); ?>
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
                                    <td><strong><?php echo escapar($prato['nome']); ?></strong></td>
                                    <td><?php echo escapar($prato['descricao']); ?></td>
                                    <td><span class="tag"><?php echo escapar($prato['categoria']); ?></span></td>
                                    <td class="preco">R$ <?php echo number_format((float) $prato['preco'], 2, ',', '.'); ?></td>
                                    <td><?php echo escapar($prato['nome_usuario'] ?: 'Não informado'); ?></td>
                                    <td><?php echo escapar($prato['email_usuario'] ?: 'Não informado'); ?></td>
                                    <td class="acoes">
                                        <a href="?abrir=editar_prato&id=<?php echo escapar($prato['id_prato']); ?>">Editar</a>
                                        <a href="public/excluir_prato.php?id=<?php echo escapar($prato['id_prato']); ?>" onclick="return confirm('Deseja excluir este prato?');">Excluir</a>
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
</body>
</html>
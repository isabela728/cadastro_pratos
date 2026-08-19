# Cadastro de Pratos

Sistema para cadastrar usuários e organizar os pratos de um restaurante. Cada prato fica ligado ao usuário responsável pelo cadastro.

## Funcionalidades

- Cadastrar usuários com nome e email.
- Cadastrar, editar e excluir pratos.
- Informar o usuário responsável por cada prato.
- Buscar pratos por nome ou descrição.
- Filtrar pratos por usuário.
- Visualizar o responsável e o email na tabela.

## Tecnologias

PHP, MySQL, HTML, CSS e XAMPP.

## Estrutura principal

- `index.php`: tela principal, busca, filtros e tabela de pratos.
- `public/`: formulários e ações do sistema.
- `infra/conexao.php`: conexão com o banco.
- `database/db.sql`: criação do banco e das tabelas.
- `style/syle.css`: estilos da aplicação.

O banco possui as tabelas `usuarios` e `pratos`. A tabela `pratos` usa o campo `id_usuario` para identificar quem cadastrou cada prato.

## Como executar no XAMPP

1. Abra o painel de controle do XAMPP e clique em **Start** no Apache e no MySQL. Os dois serviços precisam estar ativos.
2. Coloque a pasta inteira do projeto dentro de:

	```text
	C:\xampp\htdocs
	```

3. Abra `http://localhost:(numero da porta do apache)/phpmyadmin` no navegador. O phpMyAdmin é a página usada para administrar o MySQL.
4. Clique em **Importar**, selecione o arquivo `database/db.sql` dentro do projeto e execute a importação. Esse arquivo cria o banco `cadastro_pratos` e as tabelas necessárias.

	Outra opção é abrir a aba **SQL** do phpMyAdmin, copiar todo o conteúdo do arquivo `database/db.sql`, colar no campo de consulta e clicar em **Executar**.
5. Depois, abra o sistema em:

	```text
	http://localhost:(numero da porta do apache)/cadastro_pratos/
	```

A conexão padrão está configurada em `infra/conexao.php`:

```php
$host = "localhost";
$user = "root";
$senha = "";
$banco = "cadastro_pratos";
```

Se deixar `$senha = ""` e não funcionar, tente usar `$senha = "root"`, que é uma senha comum em algumas instalações do XAMPP. O valor precisa ser igual a senha configurada no MySQL do seu computador.

## Segurança

Os formulários validam os dados no servidor, mesmo que o atributo `required` seja removido do HTML. As consultas que recebem dados do usuário usam Prepared Statements com `prepare()`, `bind_param()` e `execute()`, ajudando a evitar SQL Injection.

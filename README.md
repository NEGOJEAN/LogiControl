# LogiControl — Sistema de Gestão Logística

O **LogiControl** é um projeto acadêmico para registrar e acompanhar informações de uma operação logística. Permite cadastrar produtos, endereços e transportadores, registrar fretes e consultar indicadores.

## Funcionalidades implementadas

- **Dashboard:** totais de produtos, transportadores, fretes e entregas pendentes, além dos fretes recentes.
- **Produtos:** cadastro, listagem e exclusão.
- **Endereços:** cadastro, listagem e exclusão.
- **Transportadores:** cadastro, listagem e exclusão.
- **Fretes:** cadastro, listagem e exclusão, relacionando produto, transportador, origem e destino.
- **Relatórios:** indicadores calculados a partir dos dados cadastrados.
- **Gráficos:** quantidade de fretes por mês no ano corrente.

> A versão testada não possui autenticação de usuários nem edição de registros. As funcionalidades acima correspondem ao que foi implementado.

## Tecnologias

PHP, MySQL, HTML, CSS, PDO, XAMPP (Apache e MySQL) e phpMyAdmin.

## Estrutura

```text
LogiControl/
├── assets/css/style.css
├── config/conexao.php
├── database/logicontrol.sql
├── pages/
│   ├── produtos.php
│   ├── enderecos.php
│   ├── transportadores.php
│   └── fretes.php
├── index.php
├── LEIA-ME.txt
└── README.md
```

## Requisitos

- XAMPP instalado.
- Apache e MySQL iniciados.
- Navegador web.

## Instalação local

1. Coloque a pasta `LogiControl` em `C:\xampp\htdocs\`.
2. Inicie Apache e MySQL no XAMPP.
3. Acesse `http://localhost/phpmyadmin`.
4. Faça backup do banco existente antes de importar qualquer script.
5. Importe `database/logicontrol.sql` para criar tabelas que ainda não existirem.
6. Confira `config/conexao.php`. A configuração padrão usa host `localhost`, banco `logicontrol`, usuário `root` e senha vazia. Ajuste se seu XAMPP usar outra configuração.
7. Acesse `http://localhost/LogiControl/`.

**Atenção:** o script utiliza `CREATE TABLE IF NOT EXISTS`; ele não modifica tabelas que já existem. Se já houver tabelas com esses nomes e estrutura diferente, confira os campos antes de executar a aplicação.

## Ordem sugerida de testes

1. Cadastrar um produto.
2. Cadastrar pelo menos dois endereços.
3. Cadastrar um transportador.
4. Cadastrar um frete com os registros anteriores.
5. Conferir Dashboard e Relatórios.
6. Conferir o gráfico mensal.

## Banco de dados

Banco: `logicontrol`

Tabelas: `produtos`, `enderecos`, `transportadores` e `fretes`.

A tabela `fretes` utiliza `produto_id`, `transportador_id`, `endereco_origem_id` e `endereco_destino_id` para relacionar os registros.

## Commits a cada alteração

Registre cada etapa ou correção em um commit separado, com mensagem clara. Exemplos:

```bash
git add .
git commit -m "Cria estrutura inicial do projeto"
git add .
git commit -m "Implementa cadastro de produtos"
git add .
git commit -m "Implementa cadastro de enderecos"
git add .
git commit -m "Implementa cadastro de transportadores"
git add .
git commit -m "Implementa cadastro de fretes"
git add .
git commit -m "Adiciona dashboard e relatorios"
git add .
git commit -m "Adiciona grafico mensal"
git add .
git commit -m "Atualiza documentacao e README"
```

Os comandos acima são exemplos de histórico a ser utilizado nas etapas correspondentes; não significam que esses commits já tenham sido realizados.

## Publicar no GitHub

1. Crie um repositório **público** chamado `LogiControl` no GitHub.
2. Na pasta do projeto, abra o terminal e execute:

```bash
git init
git add .
git commit -m "Adiciona versao inicial do LogiControl"
git branch -M main
git remote add origin https://github.com/SEU-USUARIO/LogiControl.git
git push -u origin main
```

3. Troque `SEU-USUARIO` pelo seu usuário do GitHub.
4. Após cada alteração futura, execute:

```bash
git add .
git commit -m "Descreve a alteracao realizada"
git push
```

5. Confira se o repositório está público e copie a URL da página principal para entregar no ambiente virtual.

## Evidências

Adicione as capturas de tela em `docs/evidencias/` se desejar disponibilizá-las diretamente no repositório.

Projeto acadêmico. Os dados demonstrativos utilizados nos testes são fictícios.

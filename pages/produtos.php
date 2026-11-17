<?php

$campos = [
    'codigo' => 'Código do produto',
    'nome' => 'Nome do produto',
    'descricao' => 'Descrição do produto',
    'peso' => 'Peso (kg)',
    'quantidade' => 'Quantidade',
    'valor_unitario' => 'Valor unitário (R$)'
];

// Valores iniciais do formulário
$produtoEdicao = [
    'id' => '',
    'codigo' => '',
    'nome' => '',
    'descricao' => '',
    'peso' => '',
    'quantidade' => '',
    'valor_unitario' => ''
];

// SALVAR OU ATUALIZAR PRODUTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar' || $acao === 'atualizar') {

        $dados = [
            trim($_POST['codigo'] ?? ''),
            trim($_POST['nome'] ?? ''),
            trim($_POST['descricao'] ?? ''),
            $_POST['peso'] ?? 0,
            $_POST['quantidade'] ?? 0,
            $_POST['valor_unitario'] ?? 0
        ];

        if ($acao === 'atualizar') {

            $id = (int)($_POST['id'] ?? 0);

            $sql = "UPDATE produtos SET
                    codigo = ?,
                    nome = ?,
                    descricao = ?,
                    peso = ?,
                    quantidade = ?,
                    valor_unitario = ?
                    WHERE id = ?";

            $pdo->prepare($sql)->execute([...$dados, $id]);

            $_SESSION['mensagem'] = 'Produto atualizado com sucesso!';

        } else {

            $sql = "INSERT INTO produtos
                    (codigo, nome, descricao, peso, quantidade, valor_unitario)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $pdo->prepare($sql)->execute($dados);

            $_SESSION['mensagem'] = 'Produto cadastrado com sucesso!';
        }

        header('Location: index.php?pagina=produtos');
        exit;
    }
}

// CARREGAR PRODUTO PARA EDIÇÃO
if (isset($_GET['editar'])) {

    $id = (int)$_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);

    $produtoEdicao = $stmt->fetch(PDO::FETCH_ASSOC) ?: $produtoEdicao;
}

// EXCLUIR PRODUTO
if (isset($_GET['excluir'])) {

    $id = (int)$_GET['excluir'];

    $pdo->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);

    $_SESSION['mensagem'] = 'Produto excluído.';

    header('Location: index.php?pagina=produtos');
    exit;
}

// LISTAR PRODUTOS
$lista = $pdo->query(
    "SELECT * FROM produtos ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<p class="subtitle">
    Cadastre e gerencie os produtos da operação logística.
</p>

<section class="panel">

    <h2>
        <?= $produtoEdicao['id'] ? 'Editar produto' : 'Informações do produto' ?>
    </h2>

    <form method="post" class="form-grid">

        <input type="hidden"
               name="acao"
               value="<?= $produtoEdicao['id'] ? 'atualizar' : 'salvar' ?>">

        <input type="hidden"
               name="id"
               value="<?= (int)$produtoEdicao['id'] ?>">

        <label>
            Código do produto
            <input name="codigo"
                   value="<?= h($produtoEdicao['codigo']) ?>"
                   placeholder="Ex.: PRD001"
                   required>
        </label>

        <label>
            Nome do produto
            <input name="nome"
                   value="<?= h($produtoEdicao['nome']) ?>"
                   placeholder="Digite o nome do produto"
                   required>
        </label>

        <label class="full">
            Descrição do produto
            <textarea name="descricao"
                      placeholder="Descreva as características do produto..."><?= h($produtoEdicao['descricao']) ?></textarea>
        </label>

        <label>
            Peso (kg)
            <input type="number"
                   name="peso"
                   step="0.01"
                   min="0"
                   value="<?= h($produtoEdicao['peso']) ?>">
        </label>

        <label>
            Quantidade
            <input type="number"
                   name="quantidade"
                   min="0"
                   value="<?= h($produtoEdicao['quantidade']) ?>">
        </label>

        <label>
            Valor unitário (R$)
            <input type="number"
                   name="valor_unitario"
                   step="0.01"
                   min="0"
                   value="<?= h($produtoEdicao['valor_unitario']) ?>">
        </label>

        <div class="form-actions full">

            <button class="button primary">
                <?= $produtoEdicao['id'] ? 'Atualizar produto' : 'Salvar produto' ?>
            </button>

            <a href="index.php?pagina=produtos"
               class="button secondary">
                <?= $produtoEdicao['id'] ? 'Cancelar edição' : 'Limpar' ?>
            </a>

        </div>

    </form>

</section>

<section class="panel">

    <h2>Produtos cadastrados</h2>

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Produto</th>
                    <th>Peso</th>
                    <th>Quantidade</th>
                    <th>Valor unitário</th>
                    <th>Ação</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($lista as $p): ?>

                    <tr>

                        <td><?= h($p['codigo']) ?></td>

                        <td><?= h($p['nome']) ?></td>

                        <td><?= h($p['peso']) ?> kg</td>

                        <td><?= h($p['quantidade']) ?></td>

                        <td>
                            R$ <?= number_format(
                                (float)$p['valor_unitario'],
                                2,
                                ',',
                                '.'
                            ) ?>
                        </td>

                        <td>

                            <a class="button secondary"
                               href="index.php?pagina=produtos&editar=<?= (int)$p['id'] ?>">
                                Editar
                            </a>

                            <a class="delete"
                               onclick="return confirm('Excluir este produto?')"
                               href="index.php?pagina=produtos&excluir=<?= (int)$p['id'] ?>">
                                Excluir
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (!$lista): ?>

                    <tr>
                        <td colspan="6" class="empty">
                            Nenhum produto cadastrado.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</section>

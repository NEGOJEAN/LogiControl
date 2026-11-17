<?php
// Valores iniciais do formulário
$transportadorEdicao = [
    'id' => '',
    'nome' => '',
    'cnpj' => '',
    'telefone' => '',
    'email' => '',
    'endereco' => ''
];

// CADASTRAR OU ATUALIZAR TRANSPORTADOR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar' || $acao === 'atualizar') {
        $dados = [
            trim($_POST['nome'] ?? ''),
            trim($_POST['cnpj'] ?? ''),
            trim($_POST['telefone'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['endereco'] ?? '')
        ];

        if ($acao === 'atualizar') {
            $id = (int)($_POST['id'] ?? 0);

            $sql = "UPDATE transportadores
                    SET nome = ?, cnpj = ?, telefone = ?, email = ?, endereco = ?
                    WHERE id = ?";

            $parametros = $dados;
            $parametros[] = $id;

            $pdo->prepare($sql)->execute($parametros);
            $_SESSION['mensagem'] = 'Transportador atualizado com sucesso!';
        } else {
            $sql = "INSERT INTO transportadores (nome, cnpj, telefone, email, endereco)
                    VALUES (?, ?, ?, ?, ?)";

            $pdo->prepare($sql)->execute($dados);
            $_SESSION['mensagem'] = 'Transportador cadastrado com sucesso!';
        }

        header('Location: index.php?pagina=transportadores');
        exit;
    }
}

// CARREGAR TRANSPORTADOR PARA EDIÇÃO
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM transportadores WHERE id = ?");
    $stmt->execute([$id]);

    $transportadorEdicao = $stmt->fetch(PDO::FETCH_ASSOC) ?: $transportadorEdicao;
}

// EXCLUIR TRANSPORTADOR
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];

    $pdo->prepare("DELETE FROM transportadores WHERE id = ?")->execute([$id]);

    $_SESSION['mensagem'] = 'Transportador excluído.';
    header('Location: index.php?pagina=transportadores');
    exit;
}

// LISTAR TRANSPORTADORES
$lista = $pdo->query("SELECT * FROM transportadores ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<p class="subtitle">Cadastre e gerencie os transportadores da operação logística.</p>

<section class="panel">
    <h2><?= $transportadorEdicao['id'] ? 'Editar transportador' : 'Informações do transportador' ?></h2>

    <form method="post" class="form-grid">
        <input type="hidden" name="acao" value="<?= $transportadorEdicao['id'] ? 'atualizar' : 'salvar' ?>">
        <input type="hidden" name="id" value="<?= (int)$transportadorEdicao['id'] ?>">

        <label>Nome do transportador
            <input name="nome"
                   value="<?= h($transportadorEdicao['nome']) ?>"
                   placeholder="Ex.: Transportadora Brasil"
                   required>
        </label>

        <label>CNPJ
            <input name="cnpj"
                   value="<?= h($transportadorEdicao['cnpj']) ?>"
                   placeholder="Ex.: 12.345.678/0001-90">
        </label>

        <label>Telefone
            <input name="telefone"
                   value="<?= h($transportadorEdicao['telefone']) ?>"
                   placeholder="Ex.: (44) 99999-9999">
        </label>

        <label>E-mail
            <input type="email"
                   name="email"
                   value="<?= h($transportadorEdicao['email']) ?>"
                   placeholder="Ex.: contato@transportadora.com">
        </label>

        <label class="full">Endereço
            <input name="endereco"
                   value="<?= h($transportadorEdicao['endereco']) ?>"
                   placeholder="Ex.: Avenida Paraná, 1500">
        </label>

        <div class="form-actions full">
            <button class="button primary">
                <?= $transportadorEdicao['id'] ? 'Atualizar transportador' : 'Salvar transportador' ?>
            </button>

            <a href="index.php?pagina=transportadores" class="button secondary">
                <?= $transportadorEdicao['id'] ? 'Cancelar edição' : 'Limpar' ?>
            </a>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Transportadores cadastrados</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Endereço</th>
                    <th>Ação</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($lista as $t): ?>
                    <tr>
                        <td><?= h($t['nome']) ?></td>
                        <td><?= h($t['cnpj']) ?></td>
                        <td><?= h($t['telefone']) ?></td>
                        <td><?= h($t['email']) ?></td>
                        <td><?= h($t['endereco']) ?></td>
                        <td>
                            <a class="button secondary"
                               href="index.php?pagina=transportadores&editar=<?= (int)$t['id'] ?>">
                                Editar
                            </a>

                            <a class="delete"
                               onclick="return confirm('Excluir este transportador?')"
                               href="index.php?pagina=transportadores&excluir=<?= (int)$t['id'] ?>">
                                Excluir
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$lista): ?>
                    <tr>
                        <td colspan="6" class="empty">Nenhum transportador cadastrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

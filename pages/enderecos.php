<?php
// Valores iniciais do formulário
$enderecoEdicao = [
    'id' => '', 'nome' => '', 'cep' => '', 'estado' => '',
    'cidade' => '', 'bairro' => '', 'rua' => '', 'numero' => '', 'complemento' => ''
];

// CADASTRAR OU ATUALIZAR ENDEREÇO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar' || $acao === 'atualizar') {
        $dados = [
            trim($_POST['nome'] ?? ''),
            trim($_POST['cep'] ?? ''),
            strtoupper(trim($_POST['estado'] ?? '')),
            trim($_POST['cidade'] ?? ''),
            trim($_POST['bairro'] ?? ''),
            trim($_POST['rua'] ?? ''),
            trim($_POST['numero'] ?? ''),
            trim($_POST['complemento'] ?? '')
        ];

        if ($acao === 'atualizar') {
            $id = (int)($_POST['id'] ?? 0);
            $sql = "UPDATE enderecos SET nome=?, cep=?, estado=?, cidade=?, bairro=?, rua=?, numero=?, complemento=? WHERE id=?";
            $parametros = $dados;
            $parametros[] = $id;
            $pdo->prepare($sql)->execute($parametros);
            $_SESSION['mensagem'] = 'Endereço atualizado com sucesso!';
        } else {
            $sql = "INSERT INTO enderecos (nome, cep, estado, cidade, bairro, rua, numero, complemento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute($dados);
            $_SESSION['mensagem'] = 'Endereço cadastrado com sucesso!';
        }

        header('Location: index.php?pagina=enderecos');
        exit;
    }
}

// CARREGAR ENDEREÇO PARA EDIÇÃO
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM enderecos WHERE id = ?");
    $stmt->execute([$id]);
    $enderecoEdicao = $stmt->fetch(PDO::FETCH_ASSOC) ?: $enderecoEdicao;
}

// EXCLUIR ENDEREÇO
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $pdo->prepare("DELETE FROM enderecos WHERE id = ?")->execute([$id]);
    $_SESSION['mensagem'] = 'Endereço excluído.';
    header('Location: index.php?pagina=enderecos');
    exit;
}

// LISTAR ENDEREÇOS
$lista = $pdo->query("SELECT * FROM enderecos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<p class="subtitle">Cadastre e gerencie os endereços utilizados nas operações logísticas.</p>

<section class="panel">
    <h2><?= $enderecoEdicao['id'] ? 'Editar endereço' : 'Informações do endereço' ?></h2>

    <form method="post" class="form-grid">
        <input type="hidden" name="acao" value="<?= $enderecoEdicao['id'] ? 'atualizar' : 'salvar' ?>">
        <input type="hidden" name="id" value="<?= (int)$enderecoEdicao['id'] ?>">

        <label class="full">Nome do endereço
            <input name="nome" value="<?= h($enderecoEdicao['nome']) ?>" placeholder="Ex.: Centro de Distribuição - Matriz" required>
        </label>

        <label>CEP
            <input name="cep" value="<?= h($enderecoEdicao['cep']) ?>" placeholder="Ex.: 87501-000">
        </label>

        <label>Estado (UF)
            <input name="estado" value="<?= h($enderecoEdicao['estado']) ?>" maxlength="2" placeholder="Ex.: PR">
        </label>

        <label>Cidade
            <input name="cidade" value="<?= h($enderecoEdicao['cidade']) ?>" placeholder="Ex.: Umuarama">
        </label>

        <label>Bairro
            <input name="bairro" value="<?= h($enderecoEdicao['bairro']) ?>" placeholder="Ex.: Centro">
        </label>

        <label>Rua
            <input name="rua" value="<?= h($enderecoEdicao['rua']) ?>" placeholder="Ex.: Avenida Paraná">
        </label>

        <label>Número
            <input name="numero" value="<?= h($enderecoEdicao['numero']) ?>" placeholder="Ex.: 1500">
        </label>

        <label class="full">Complemento
            <input name="complemento" value="<?= h($enderecoEdicao['complemento']) ?>" placeholder="Ex.: Galpão 2">
        </label>

        <div class="form-actions full">
            <button class="button primary">
                <?= $enderecoEdicao['id'] ? 'Atualizar endereço' : 'Salvar endereço' ?>
            </button>
            <a href="index.php?pagina=enderecos" class="button secondary">
                <?= $enderecoEdicao['id'] ? 'Cancelar edição' : 'Limpar' ?>
            </a>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Endereços cadastrados</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nome</th><th>CEP</th><th>Cidade/UF</th><th>Rua</th><th>Número</th><th>Ação</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $e): ?>
                    <tr>
                        <td><?= h($e['nome']) ?></td>
                        <td><?= h($e['cep']) ?></td>
                        <td><?= h($e['cidade']) ?>/<?= h($e['estado']) ?></td>
                        <td><?= h($e['rua']) ?></td>
                        <td><?= h($e['numero']) ?></td>
                        <td>
                            <a class="button secondary" href="index.php?pagina=enderecos&editar=<?= (int)$e['id'] ?>">Editar</a>
                            <a class="delete" onclick="return confirm('Excluir este endereço?')" href="index.php?pagina=enderecos&excluir=<?= (int)$e['id'] ?>">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$lista): ?>
                    <tr><td colspan="6" class="empty">Nenhum endereço cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

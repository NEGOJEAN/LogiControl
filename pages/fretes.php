<?php
$produtos = $pdo->query("SELECT id, nome, codigo FROM produtos ORDER BY nome")->fetchAll();
$transportadores = $pdo->query("SELECT id, nome FROM transportadores ORDER BY nome")->fetchAll();
$enderecos = $pdo->query("SELECT id, nome, cidade FROM enderecos ORDER BY nome")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    $codigo = trim($_POST['codigo'] ?? '');
    $transportadorId = (int)($_POST['transportador_id'] ?? 0);
    $origemId = (int)($_POST['endereco_origem_id'] ?? 0);
    $destinoId = (int)($_POST['endereco_destino_id'] ?? 0);
    $dataEnvio = $_POST['data_envio'] ?: null;
    $previsaoEntrega = $_POST['previsao_entrega'] ?: null;
    $status = $_POST['status'] ?? 'Pendente';
    $produtoIds = array_values(array_unique(array_filter(array_map('intval', $_POST['produto_ids'] ?? []), fn($id) => $id > 0)));
    $quantidades = $_POST['quantidades'] ?? [];

    if (!$codigo || !$transportadorId || !$origemId || !$destinoId || !$produtoIds) {
        $_SESSION['mensagem'] = 'Preencha os campos obrigatórios e selecione pelo menos um produto.';
        header('Location: index.php?pagina=fretes');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Mantém produto_id preenchido para compatibilidade com consultas antigas do sistema.
        $sqlFrete = "INSERT INTO fretes
            (codigo, produto_id, transportador_id, endereco_origem_id,
             endereco_destino_id, data_envio, previsao_entrega, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sqlFrete)->execute([
            $codigo, $produtoIds[0], $transportadorId, $origemId,
            $destinoId, $dataEnvio, $previsaoEntrega, $status
        ]);

        $freteId = (int)$pdo->lastInsertId();
        $stmtProduto = $pdo->prepare(
            "INSERT INTO frete_produtos (frete_id, produto_id, quantidade) VALUES (?, ?, ?)"
        );

        foreach ($produtoIds as $produtoId) {
            $quantidade = max(1, (int)($quantidades[$produtoId] ?? 1));
            $stmtProduto->execute([$freteId, $produtoId, $quantidade]);
        }

        $pdo->commit();
        $_SESSION['mensagem'] = 'Frete cadastrado com sucesso!';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['mensagem'] = 'Não foi possível cadastrar o frete. Verifique se o código já existe e tente novamente.';
    }

    header('Location: index.php?pagina=fretes');
    exit;
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM fretes WHERE id = ?")->execute([(int)$_GET['excluir']]);
    $_SESSION['mensagem'] = 'Frete excluído.';
    header('Location: index.php?pagina=fretes');
    exit;
}

$sqlLista = "
    SELECT f.*,
        GROUP_CONCAT(CONCAT(p.nome, ' (Qtd.: ', fp.quantidade, ')')
            ORDER BY p.nome SEPARATOR ', ') AS produtos,
        t.nome AS transportador,
        o.cidade AS origem,
        d.cidade AS destino
    FROM fretes f
    LEFT JOIN frete_produtos fp ON fp.frete_id = f.id
    LEFT JOIN produtos p ON p.id = fp.produto_id
    JOIN transportadores t ON t.id = f.transportador_id
    JOIN enderecos o ON o.id = f.endereco_origem_id
    JOIN enderecos d ON d.id = f.endereco_destino_id
    GROUP BY f.id
    ORDER BY f.id DESC
";
$lista = $pdo->query($sqlLista)->fetchAll();
$podeCadastrar = !empty($produtos) && !empty($transportadores) && count($enderecos) >= 2;
?>

<p class="subtitle">Cadastre e gerencie os fretes da operação logística.</p>

<section class="panel">
    <h2>Informações do frete</h2>
    <?php if (!$podeCadastrar): ?>
        <div class="alert warning">Antes de cadastrar um frete, cadastre pelo menos um produto, um transportador e dois endereços (origem e destino).</div>
    <?php endif; ?>

    <form method="post" class="form-grid">
        <input type="hidden" name="acao" value="salvar">

        <label>Código do frete
            <input name="codigo" placeholder="Ex.: FR006" required>
        </label>

        <div class="full">
            <strong>Produtos do frete</strong>
            <p class="subtitle">Marque um ou mais produtos e informe a quantidade de cada um.</p>
            <div class="product-selection">
                <?php foreach ($produtos as $p): ?>
                    <label class="product-option">
                        <span>
                            <input type="checkbox" name="produto_ids[]" value="<?= (int)$p['id'] ?>" class="produto-checkbox">
                            <?= h($p['codigo'] . ' - ' . $p['nome']) ?>
                        </span>
                        <span class="quantity-field">
                            Quantidade
                            <input type="number" name="quantidades[<?= (int)$p['id'] ?>]" value="1" min="1" step="1" disabled class="quantidade-produto">
                        </span>
                    </label>
                <?php endforeach; ?>
                <?php if (!$produtos): ?><p class="empty">Nenhum produto cadastrado.</p><?php endif; ?>
            </div>
        </div>

        <label>Transportador
            <select name="transportador_id" required>
                <option value="">Selecione o transportador</option>
                <?php foreach ($transportadores as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= h($t['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Endereço de origem
            <select name="endereco_origem_id" required>
                <option value="">Selecione a origem</option>
                <?php foreach ($enderecos as $e): ?>
                    <option value="<?= (int)$e['id'] ?>"><?= h($e['nome'] . ' - ' . $e['cidade']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Endereço de destino
            <select name="endereco_destino_id" required>
                <option value="">Selecione o destino</option>
                <?php foreach ($enderecos as $e): ?>
                    <option value="<?= (int)$e['id'] ?>"><?= h($e['nome'] . ' - ' . $e['cidade']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Data de envio<input type="date" name="data_envio"></label>
        <label>Previsão de entrega<input type="date" name="previsao_entrega"></label>

        <label>Status
            <select name="status">
                <option>Pendente</option>
                <option>Em trânsito</option>
                <option>Concluído</option>
            </select>
        </label>

        <div class="form-actions full">
            <button class="button primary" <?= !$podeCadastrar ? 'disabled' : '' ?>>Salvar frete</button>
            <button type="reset" class="button secondary">Limpar</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Fretes cadastrados</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Código</th><th>Produtos e quantidades</th><th>Transportador</th><th>Origem</th><th>Destino</th><th>Envio</th><th>Status</th><th>Ação</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $f): ?>
                    <tr>
                        <td><?= h($f['codigo']) ?></td>
                        <td><?= h($f['produtos'] ?: '-') ?></td>
                        <td><?= h($f['transportador']) ?></td>
                        <td><?= h($f['origem']) ?></td>
                        <td><?= h($f['destino']) ?></td>
                        <td><?= $f['data_envio'] ? date('d/m/Y', strtotime($f['data_envio'])) : '-' ?></td>
                        <td><span class="status"><?= h($f['status']) ?></span></td>
                        <td><a class="delete" onclick="return confirm('Excluir este frete?')" href="index.php?pagina=fretes&excluir=<?= (int)$f['id'] ?>">Excluir</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$lista): ?><tr><td colspan="8" class="empty">Nenhum frete cadastrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.product-selection { display: grid; gap: 8px; margin-top: 10px; }
.product-option { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 10px 12px; border: 1px solid #dbe3ef; border-radius: 8px; background: #fff; }
.product-option > span:first-child { display: flex; align-items: center; gap: 8px; }
.product-option input[type="checkbox"] { width: auto; }
.quantity-field { display: flex; align-items: center; gap: 8px; }
.quantity-field input { width: 78px; }
</style>

<script>
document.querySelectorAll('.produto-checkbox').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const quantidade = this.closest('.product-option').querySelector('.quantidade-produto');
        quantidade.disabled = !this.checked;
        if (this.checked && (!quantidade.value || Number(quantidade.value) < 1)) {
            quantidade.value = 1;
        }
    });
});
</script>

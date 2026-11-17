<?php
require_once __DIR__ . '/config/conexao.php';
$pagina = $_GET['pagina'] ?? 'dashboard';
$permitidas = ['dashboard','produtos','enderecos','transportadores','fretes','relatorios','graficos'];
if (!in_array($pagina, $permitidas, true)) $pagina = 'dashboard';

function total(PDO $pdo, string $tabela): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM `$tabela`")->fetchColumn();
}
function h($valor): string { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); }

if (session_status() === PHP_SESSION_NONE) session_start();

// Exportação do relatório diário em CSV
if ($pagina === 'relatorios' && ($_GET['exportar'] ?? '') === 'csv') {
    $dataRelatorio = $_GET['data'] ?? '';
    $dataValida = DateTime::createFromFormat('!Y-m-d', $dataRelatorio);

    if (!$dataValida || $dataValida->format('Y-m-d') !== $dataRelatorio) {
        http_response_code(400);
        exit('Data inválida para gerar o relatório.');
    }

    $sqlExportacao = "SELECT f.codigo, p.nome AS produto, t.nome AS transportador,
                             o.cidade AS origem, d.cidade AS destino,
                             f.data_envio, f.previsao_entrega, f.status
                      FROM fretes f
                      INNER JOIN produtos p ON p.id = f.produto_id
                      INNER JOIN transportadores t ON t.id = f.transportador_id
                      INNER JOIN enderecos o ON o.id = f.endereco_origem_id
                      INNER JOIN enderecos d ON d.id = f.endereco_destino_id
                      WHERE f.data_envio = ?
                      ORDER BY f.codigo";

    $stmtExportacao = $pdo->prepare($sqlExportacao);
    $stmtExportacao->execute([$dataRelatorio]);
    $fretesExportacao = $stmtExportacao->fetchAll(PDO::FETCH_ASSOC);

    $nomeArquivo = 'relatorio_fretes_' . $dataRelatorio . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

    $saida = fopen('php://output', 'w');
    // BOM UTF-8 para o Excel reconhecer corretamente os acentos
    fwrite($saida, "\\xEF\\xBB\\xBF");
    fputcsv($saida, ['Código', 'Produto', 'Transportador', 'Origem', 'Destino',
                     'Data de envio', 'Previsão de entrega', 'Status'], ';');

    foreach ($fretesExportacao as $frete) {
        fputcsv($saida, [
            $frete['codigo'],
            $frete['produto'],
            $frete['transportador'],
            $frete['origem'],
            $frete['destino'],
            $frete['data_envio'] ? date('d/m/Y', strtotime($frete['data_envio'])) : '',
            $frete['previsao_entrega'] ? date('d/m/Y', strtotime($frete['previsao_entrega'])) : '',
            $frete['status']
        ], ';');
    }

    fclose($saida);
    exit;
}

$mensagem = $_SESSION['mensagem'] ?? '';
unset($_SESSION['mensagem']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LogiControl | Gestão Logística</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<aside class="sidebar">
  <div class="brand"><span class="brand-icon">LC</span><span>LogiControl</span></div>
  <div class="nav-label">MENU PRINCIPAL</div>
  <nav>
    <?php
    $menus = ['dashboard'=>'Dashboard','produtos'=>'Produtos','enderecos'=>'Endereços','transportadores'=>'Transportadores','fretes'=>'Fretes','relatorios'=>'Relatórios','graficos'=>'Gráficos'];
    foreach ($menus as $chave=>$nome):
      $icone = ['dashboard'=>'▦','produtos'=>'□','enderecos'=>'⌖','transportadores'=>'♙','fretes'=>'⇄','relatorios'=>'▤','graficos'=>'▥'][$chave];
    ?>
      <a class="<?= $pagina===$chave?'active':'' ?>" href="index.php?pagina=<?= h($chave) ?>"><span><?= $icone ?></span><?= h($nome) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-bottom">Sistema de Gestão<br><strong>LogiControl</strong></div>
</aside>
<main class="main">
  <header class="topbar"><div><span class="eyebrow">GESTÃO LOGÍSTICA</span><h1><?= h($menus[$pagina]) ?></h1></div><div class="user-chip"><span class="avatar">A</span><span>Administrador</span></div></header>
  <?php if ($mensagem): ?><div class="alert success"><?= h($mensagem) ?></div><?php endif; ?>
  <?php if ($pagina==='dashboard'): ?>
    <p class="subtitle">Acompanhe as principais informações da sua operação logística.</p>
    <section class="stats">
      <article class="stat-card"><span class="stat-icon blue">□</span><div><small>Total de produtos</small><strong><?= total($pdo,'produtos') ?></strong><small>Produtos cadastrados</small></div></article>
      <article class="stat-card"><span class="stat-icon purple">♙</span><div><small>Transportadores</small><strong><?= total($pdo,'transportadores') ?></strong><small>Transportadores cadastrados</small></div></article>
      <article class="stat-card"><span class="stat-icon green">⇄</span><div><small>Fretes realizados</small><strong><?= total($pdo,'fretes') ?></strong><small>Fretes registrados</small></div></article>
      <article class="stat-card"><span class="stat-icon orange">◷</span><div><small>Entregas pendentes</small><strong><?= (int)$pdo->query("SELECT COUNT(*) FROM fretes WHERE status='Pendente'")->fetchColumn() ?></strong><small>Aguardando conclusão</small></div></article>
    </section>
    <section class="panel"><div class="panel-heading"><div><h2>Últimos fretes cadastrados</h2><p>Visualize as movimentações mais recentes</p></div><a class="button secondary" href="index.php?pagina=fretes">Ver fretes</a></div>
    <?php
    $ultimos=$pdo->query("SELECT f.codigo,t.nome AS transportador,o.cidade AS origem,d.cidade AS destino,f.status FROM fretes f JOIN transportadores t ON t.id=f.transportador_id JOIN enderecos o ON o.id=f.endereco_origem_id JOIN enderecos d ON d.id=f.endereco_destino_id ORDER BY f.id DESC LIMIT 6")->fetchAll();
    ?>
    <div class="table-wrap"><table><thead><tr><th>Código</th><th>Transportador</th><th>Origem</th><th>Destino</th><th>Status</th></tr></thead><tbody>
    <?php if (!$ultimos): ?><tr><td colspan="5" class="empty">Nenhum frete cadastrado até o momento.</td></tr><?php endif; ?>
    <?php foreach($ultimos as $f): ?><tr><td><strong><?= h($f['codigo']) ?></strong></td><td><?= h($f['transportador']) ?></td><td><?= h($f['origem']) ?></td><td><?= h($f['destino']) ?></td><td><span class="status"><?= h($f['status']) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div></section>
  <?php elseif (in_array($pagina,['produtos','enderecos','transportadores','fretes'],true)): ?>
    <?php include __DIR__ . '/pages/' . $pagina . '.php'; ?>
  <?php elseif ($pagina==='relatorios'): ?>
    <p class="subtitle">Acompanhe os indicadores e resultados da operação logística.</p>
    <?php $concluidos=(int)$pdo->query("SELECT COUNT(*) FROM fretes WHERE status='Concluído'")->fetchColumn(); ?>
    <section class="stats">
      <article class="stat-card"><div><small>Fretes realizados</small><strong><?= total($pdo,'fretes') ?></strong></div></article>
      <article class="stat-card"><div><small>Fretes pendentes</small><strong><?= (int)$pdo->query("SELECT COUNT(*) FROM fretes WHERE status='Pendente'")->fetchColumn() ?></strong></div></article>
      <article class="stat-card"><div><small>Fretes concluídos</small><strong><?= $concluidos ?></strong></div></article>
      <article class="stat-card"><div><small>Transportadores</small><strong><?= total($pdo,'transportadores') ?></strong></div></article>
    </section>
    <section class="panel">
      <h2>Relatório diário de fretes</h2>
      <p class="subtitle">Selecione a data de envio para consultar e baixar os fretes registrados naquele dia.</p>
      <?php
      $dataRelatorio = $_GET['data'] ?? date('Y-m-d');
      $dataValida = DateTime::createFromFormat('!Y-m-d', $dataRelatorio);
      if (!$dataValida || $dataValida->format('Y-m-d') !== $dataRelatorio) {
          $dataRelatorio = date('Y-m-d');
      }

      $sqlDiario = "SELECT f.codigo, p.nome AS produto, t.nome AS transportador,
                           o.cidade AS origem, d.cidade AS destino,
                           f.data_envio, f.previsao_entrega, f.status
                    FROM fretes f
                    INNER JOIN produtos p ON p.id = f.produto_id
                    INNER JOIN transportadores t ON t.id = f.transportador_id
                    INNER JOIN enderecos o ON o.id = f.endereco_origem_id
                    INNER JOIN enderecos d ON d.id = f.endereco_destino_id
                    WHERE f.data_envio = ?
                    ORDER BY f.codigo";
      $stmtDiario = $pdo->prepare($sqlDiario);
      $stmtDiario->execute([$dataRelatorio]);
      $fretesDoDia = $stmtDiario->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <form method="get" class="form-grid">
        <input type="hidden" name="pagina" value="relatorios">
        <label>Data de envio
          <input type="date" name="data" value="<?= h($dataRelatorio) ?>" required>
        </label>
        <div class="form-actions">
          <button type="submit" class="button primary">Consultar fretes</button>
          <a class="button secondary" href="index.php?pagina=relatorios&data=<?= h($dataRelatorio) ?>&exportar=csv">Baixar relatório CSV</a>
        </div>
      </form>

      <p class="subtitle">Resultado para <?= date('d/m/Y', strtotime($dataRelatorio)) ?>: <strong><?= count($fretesDoDia) ?></strong> frete(s).</p>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Código</th><th>Produto</th><th>Transportador</th><th>Origem</th><th>Destino</th><th>Envio</th><th>Previsão</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($fretesDoDia as $f): ?>
              <tr>
                <td><?= h($f['codigo']) ?></td>
                <td><?= h($f['produto']) ?></td>
                <td><?= h($f['transportador']) ?></td>
                <td><?= h($f['origem']) ?></td>
                <td><?= h($f['destino']) ?></td>
                <td><?= $f['data_envio'] ? date('d/m/Y', strtotime($f['data_envio'])) : '-' ?></td>
                <td><?= $f['previsao_entrega'] ? date('d/m/Y', strtotime($f['previsao_entrega'])) : '-' ?></td>
                <td><span class="status"><?= h($f['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$fretesDoDia): ?>
              <tr><td colspan="8" class="empty">Nenhum frete encontrado para esta data.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <h2>Resumo de movimentações logísticas</h2>
      <p class="subtitle">Indicadores calculados com base nos registros cadastrados no sistema.</p>
      <div class="report-line"><span>Produtos cadastrados</span><strong><?= total($pdo,'produtos') ?></strong></div>
      <div class="report-line"><span>Endereços cadastrados</span><strong><?= total($pdo,'enderecos') ?></strong></div>
      <div class="report-line"><span>Fretes em trânsito</span><strong><?= (int)$pdo->query("SELECT COUNT(*) FROM fretes WHERE status='Em trânsito'")->fetchColumn() ?></strong></div>
    </section>
  <?php elseif ($pagina==='graficos'): ?>
    <p class="subtitle">Visualize a quantidade de fretes registrados por mês.</p>
    <?php $dados=$pdo->query("SELECT MONTH(data_envio) mes, COUNT(*) total FROM fretes WHERE data_envio IS NOT NULL AND YEAR(data_envio)=YEAR(CURDATE()) GROUP BY MONTH(data_envio)")->fetchAll(); $meses=['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']; $map=[]; foreach($dados as $d)$map[(int)$d['mes']]=(int)$d['total']; $max=max(1,...array_values($map ?: [0])); ?>
    <section class="panel"><h2>Fretes realizados por mês</h2><p class="subtitle">Quantidade de fretes — <?= date('Y') ?></p><div class="chart">
      <?php foreach($meses as $i=>$mes): $v=$map[$i+1]??0; ?><div class="bar-column"><strong><?= $v ?></strong><div class="bar" style="height:<?= max(4,($v/$max)*180) ?>px"></div><small><?= substr($mes,0,3) ?></small></div><?php endforeach; ?>
    </div></section>
  <?php endif; ?>
  <footer>© <?= date('Y') ?> LogiControl — Sistema de Gestão Logística</footer>
</main>
</body></html>

<?php
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='salvar') {
$pdo->prepare("INSERT INTO fretes (codigo,produto_id,transportador_id,endereco_origem_id,endereco_destino_id,data_envio,previsao_entrega,status) VALUES (?,?,?,?,?,?,?,?)")->execute([trim($_POST['codigo']),(int)$_POST['produto_id'],(int)$_POST['transportador_id'],(int)$_POST['endereco_origem_id'],(int)$_POST['endereco_destino_id'],$_POST['data_envio']?:null,$_POST['previsao_entrega']?:null,$_POST['status']]);
$_SESSION['mensagem']='Frete cadastrado com sucesso!'; header('Location: index.php?pagina=fretes'); exit; }
if(isset($_GET['excluir'])){$pdo->prepare("DELETE FROM fretes WHERE id=?")->execute([(int)$_GET['excluir']]);$_SESSION['mensagem']='Frete excluído.';header('Location: index.php?pagina=fretes');exit;}
$produtos=$pdo->query("SELECT id,nome,codigo FROM produtos ORDER BY nome")->fetchAll();
$transportadores=$pdo->query("SELECT id,nome FROM transportadores ORDER BY nome")->fetchAll();
$enderecos=$pdo->query("SELECT id,nome,cidade FROM enderecos ORDER BY nome")->fetchAll();
$lista=$pdo->query("SELECT f.*,p.nome produto,t.nome transportador,o.cidade origem,d.cidade destino FROM fretes f JOIN produtos p ON p.id=f.produto_id JOIN transportadores t ON t.id=f.transportador_id JOIN enderecos o ON o.id=f.endereco_origem_id JOIN enderecos d ON d.id=f.endereco_destino_id ORDER BY f.id DESC")->fetchAll();
?>
<p class="subtitle">Cadastre e gerencie os fretes da operação logística.</p>
<section class="panel"><h2>Informações do frete</h2>
<?php if(!$produtos || !$transportadores || count($enderecos)<2): ?><div class="alert warning">Antes de cadastrar um frete, cadastre pelo menos um produto, um transportador e dois endereços (origem e destino).</div><?php endif; ?>
<form method="post" class="form-grid"><input type="hidden" name="acao" value="salvar">
<label>Código do frete<input name="codigo" placeholder="Ex.: FR004" required></label>
<label>Produto<select name="produto_id" required><option value="">Selecione o produto</option><?php foreach($produtos as $p): ?><option value="<?= $p['id'] ?>"><?= h($p['codigo'].' - '.$p['nome']) ?></option><?php endforeach; ?></select></label>
<label>Transportador<select name="transportador_id" required><option value="">Selecione o transportador</option><?php foreach($transportadores as $t): ?><option value="<?= $t['id'] ?>"><?= h($t['nome']) ?></option><?php endforeach; ?></select></label>
<label>Endereço de origem<select name="endereco_origem_id" required><option value="">Selecione a origem</option><?php foreach($enderecos as $e): ?><option value="<?= $e['id'] ?>"><?= h($e['nome'].' - '.$e['cidade']) ?></option><?php endforeach; ?></select></label>
<label>Endereço de destino<select name="endereco_destino_id" required><option value="">Selecione o destino</option><?php foreach($enderecos as $e): ?><option value="<?= $e['id'] ?>"><?= h($e['nome'].' - '.$e['cidade']) ?></option><?php endforeach; ?></select></label>
<label>Data de envio<input type="date" name="data_envio"></label><label>Previsão de entrega<input type="date" name="previsao_entrega"></label>
<label>Status<select name="status"><option>Pendente</option><option>Em trânsito</option><option>Concluído</option></select></label>
<div class="form-actions full"><button class="button primary" <?= (!$produtos || !$transportadores || count($enderecos)<2)?'disabled':'' ?>>Salvar frete</button><button type="reset" class="button secondary">Limpar</button></div></form></section>
<section class="panel"><h2>Fretes cadastrados</h2><div class="table-wrap"><table><thead><tr><th>Código</th><th>Produto</th><th>Transportador</th><th>Origem</th><th>Destino</th><th>Envio</th><th>Status</th><th>Ação</th></tr></thead><tbody>
<?php foreach($lista as $f): ?><tr><td><?= h($f['codigo']) ?></td><td><?= h($f['produto']) ?></td><td><?= h($f['transportador']) ?></td><td><?= h($f['origem']) ?></td><td><?= h($f['destino']) ?></td><td><?= $f['data_envio']?date('d/m/Y',strtotime($f['data_envio'])):'-' ?></td><td><span class="status"><?= h($f['status']) ?></span></td><td><a class="delete" onclick="return confirm('Excluir este frete?')" href="index.php?pagina=fretes&excluir=<?= (int)$f['id'] ?>">Excluir</a></td></tr><?php endforeach; ?>
<?php if(!$lista): ?><tr><td colspan="8" class="empty">Nenhum frete cadastrado.</td></tr><?php endif; ?></tbody></table></div></section>

<?php
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='salvar') {
$pdo->prepare("INSERT INTO enderecos (nome,cep,estado,cidade,bairro,rua,numero,complemento) VALUES (?,?,?,?,?,?,?,?)")->execute([trim($_POST['nome']),trim($_POST['cep']),strtoupper(trim($_POST['estado'])),trim($_POST['cidade']),trim($_POST['bairro']),trim($_POST['rua']),trim($_POST['numero']),trim($_POST['complemento'])]);
$_SESSION['mensagem']='Endereço cadastrado com sucesso!'; header('Location: index.php?pagina=enderecos'); exit; }
if(isset($_GET['excluir'])){$pdo->prepare("DELETE FROM enderecos WHERE id=?")->execute([(int)$_GET['excluir']]);$_SESSION['mensagem']='Endereço excluído.';header('Location: index.php?pagina=enderecos');exit;}
$lista=$pdo->query("SELECT * FROM enderecos ORDER BY id DESC")->fetchAll();
?>
<p class="subtitle">Cadastre e gerencie os endereços utilizados nas operações logísticas.</p>
<section class="panel"><h2>Informações do endereço</h2><form method="post" class="form-grid"><input type="hidden" name="acao" value="salvar">
<label class="full">Nome do endereço<input name="nome" placeholder="Ex.: Centro de Distribuição - Matriz" required></label>
<label>CEP<input name="cep" placeholder="Ex.: 87501-000"></label><label>Estado (UF)<input name="estado" maxlength="2" placeholder="Ex.: PR"></label>
<label>Cidade<input name="cidade" placeholder="Ex.: Umuarama"></label><label>Bairro<input name="bairro" placeholder="Ex.: Centro"></label>
<label>Rua<input name="rua" placeholder="Ex.: Avenida Paraná"></label><label>Número<input name="numero" placeholder="Ex.: 1500"></label>
<label class="full">Complemento<input name="complemento" placeholder="Ex.: Galpão 2"></label>
<div class="form-actions full"><button class="button primary">Salvar endereço</button><button type="reset" class="button secondary">Limpar</button></div></form></section>
<section class="panel"><h2>Endereços cadastrados</h2><div class="table-wrap"><table><thead><tr><th>Nome</th><th>CEP</th><th>Cidade/UF</th><th>Rua</th><th>Número</th><th>Ação</th></tr></thead><tbody>
<?php foreach($lista as $e): ?><tr><td><?= h($e['nome']) ?></td><td><?= h($e['cep']) ?></td><td><?= h($e['cidade']) ?>/<?= h($e['estado']) ?></td><td><?= h($e['rua']) ?></td><td><?= h($e['numero']) ?></td><td><a class="delete" onclick="return confirm('Excluir este endereço?')" href="index.php?pagina=enderecos&excluir=<?= (int)$e['id'] ?>">Excluir</a></td></tr><?php endforeach; ?>
<?php if(!$lista): ?><tr><td colspan="6" class="empty">Nenhum endereço cadastrado.</td></tr><?php endif; ?></tbody></table></div></section>

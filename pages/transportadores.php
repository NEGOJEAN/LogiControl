<?php
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='salvar') {
$pdo->prepare("INSERT INTO transportadores (nome,cnpj,telefone,email,endereco) VALUES (?,?,?,?,?)")->execute([trim($_POST['nome']),trim($_POST['cnpj']),trim($_POST['telefone']),trim($_POST['email']),trim($_POST['endereco'])]);
$_SESSION['mensagem']='Transportador cadastrado com sucesso!'; header('Location: index.php?pagina=transportadores'); exit; }
if(isset($_GET['excluir'])){$pdo->prepare("DELETE FROM transportadores WHERE id=?")->execute([(int)$_GET['excluir']]);$_SESSION['mensagem']='Transportador excluído.';header('Location: index.php?pagina=transportadores');exit;}
$lista=$pdo->query("SELECT * FROM transportadores ORDER BY id DESC")->fetchAll();
?>
<p class="subtitle">Cadastre e gerencie os transportadores da operação logística.</p>
<section class="panel"><h2>Informações do transportador</h2><form method="post" class="form-grid"><input type="hidden" name="acao" value="salvar">
<label>Nome do transportador<input name="nome" placeholder="Ex.: Transportadora Brasil" required></label>
<label>CNPJ<input name="cnpj" placeholder="Ex.: 12.345.678/0001-90"></label>
<label>Telefone<input name="telefone" placeholder="Ex.: (44) 99999-9999"></label>
<label>E-mail<input type="email" name="email" placeholder="Ex.: contato@transportadora.com"></label>
<label class="full">Endereço<input name="endereco" placeholder="Ex.: Avenida Paraná, 1500"></label>
<div class="form-actions full"><button class="button primary">Salvar transportador</button><button type="reset" class="button secondary">Limpar</button></div></form></section>
<section class="panel"><h2>Transportadores cadastrados</h2><div class="table-wrap"><table><thead><tr><th>Nome</th><th>CNPJ</th><th>Telefone</th><th>E-mail</th><th>Endereço</th><th>Ação</th></tr></thead><tbody>
<?php foreach($lista as $t): ?><tr><td><?= h($t['nome']) ?></td><td><?= h($t['cnpj']) ?></td><td><?= h($t['telefone']) ?></td><td><?= h($t['email']) ?></td><td><?= h($t['endereco']) ?></td><td><a class="delete" onclick="return confirm('Excluir este transportador?')" href="index.php?pagina=transportadores&excluir=<?= (int)$t['id'] ?>">Excluir</a></td></tr><?php endforeach; ?>
<?php if(!$lista): ?><tr><td colspan="6" class="empty">Nenhum transportador cadastrado.</td></tr><?php endif; ?></tbody></table></div></section>

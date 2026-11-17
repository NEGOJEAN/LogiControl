<?php
$campos=['codigo'=>'Código do produto','nome'=>'Nome do produto','descricao'=>'Descrição do produto','peso'=>'Peso (kg)','quantidade'=>'Quantidade','valor_unitario'=>'Valor unitário (R$)'];
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='salvar') {
  $sql="INSERT INTO produtos (codigo,nome,descricao,peso,quantidade,valor_unitario) VALUES (?,?,?,?,?,?)";
  $pdo->prepare($sql)->execute([trim($_POST['codigo']),trim($_POST['nome']),trim($_POST['descricao']),$_POST['peso']?:0,$_POST['quantidade']?:0,$_POST['valor_unitario']?:0]);
  $_SESSION['mensagem']='Produto cadastrado com sucesso!'; header('Location: index.php?pagina=produtos'); exit;
}
if (isset($_GET['excluir'])) { $pdo->prepare("DELETE FROM produtos WHERE id=?")->execute([(int)$_GET['excluir']]); $_SESSION['mensagem']='Produto excluído.'; header('Location: index.php?pagina=produtos'); exit; }
$lista=$pdo->query("SELECT * FROM produtos ORDER BY id DESC")->fetchAll();
?>
<p class="subtitle">Cadastre e gerencie os produtos da operação logística.</p>
<section class="panel"><h2>Informações do produto</h2><form method="post" class="form-grid"><input type="hidden" name="acao" value="salvar">
<label>Código do produto<input name="codigo" placeholder="Ex.: PRD001" required></label>
<label>Nome do produto<input name="nome" placeholder="Digite o nome do produto" required></label>
<label class="full">Descrição do produto<textarea name="descricao" placeholder="Descreva as características do produto..."></textarea></label>
<label>Peso (kg)<input type="number" name="peso" step="0.01" min="0" placeholder="Ex.: 10,50"></label>
<label>Quantidade<input type="number" name="quantidade" min="0" placeholder="Ex.: 100"></label>
<label>Valor unitário (R$)<input type="number" name="valor_unitario" step="0.01" min="0" placeholder="Ex.: 59,90"></label>
<div class="form-actions full"><button class="button primary">Salvar produto</button><button type="reset" class="button secondary">Limpar</button></div></form></section>
<section class="panel"><h2>Produtos cadastrados</h2><div class="table-wrap"><table><thead><tr><th>Código</th><th>Produto</th><th>Peso</th><th>Quantidade</th><th>Valor unitário</th><th>Ação</th></tr></thead><tbody>
<?php foreach($lista as $p): ?><tr><td><?= h($p['codigo']) ?></td><td><?= h($p['nome']) ?></td><td><?= h($p['peso']) ?> kg</td><td><?= h($p['quantidade']) ?></td><td>R$ <?= number_format((float)$p['valor_unitario'],2,',','.') ?></td><td><a class="delete" onclick="return confirm('Excluir este produto?')" href="index.php?pagina=produtos&excluir=<?= (int)$p['id'] ?>">Excluir</a></td></tr><?php endforeach; ?>
<?php if(!$lista): ?><tr><td colspan="6" class="empty">Nenhum produto cadastrado.</td></tr><?php endif; ?></tbody></table></div></section>

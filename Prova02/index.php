<?php
require_once 'conexao.php';

$erro = '';
$codigo_edit = null;
$nome_edit = '';
$link_edit = '';
$descricao_edit = '';
$exibir_form = isset($_GET['acao']) && ($_GET['acao'] === 'novo' || $_GET['acao'] === 'editar');


if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && !empty($_GET['codigo'])) {
    $stmt = $pdo->prepare("DELETE FROM jogos WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $_GET['codigo']]);
    header("Location: index.php");
    exit;
}

if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && !empty($_GET['codigo'])) {
    $stmt = $pdo->prepare("SELECT * FROM jogos WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $_GET['codigo']]);
    $jogo_edit = $stmt->fetch();
    if ($jogo_edit) {
        $codigo_edit = $jogo_edit['codigo'];
        $nome_edit = $jogo_edit['nome'];
        $link_edit = $jogo_edit['link'];
        $descricao_edit = $jogo_edit['descricao'];
        $exibir_form = true;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'] ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (!empty($nome) && !empty($link)) {
        try {
            if ($codigo) {
                $stmt = $pdo->prepare("UPDATE jogos SET nome = :nome, link = :link, descricao = :descricao WHERE codigo = :codigo");
                $stmt->execute([':nome' => $nome, ':link' => $link, ':descricao' => $descricao, ':codigo' => $codigo]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO jogos (nome, link, descricao) VALUES (:nome, :link, :descricao)");
                $stmt->execute([':nome' => $nome, ':link' => $link, ':descricao' => $descricao]);
            }
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $exibir_form = true;
            if ($e->getCode() == 23000) {
                $erro = "O nome do jogo já existe na base de dados!";
            } else {
                $erro = "Erro ao guardar: " . $e->getMessage();
            }
        }
    } else {
        $exibir_form = true;
        $erro = "Preencha os campos obrigatórios.";
    }
}


$jogos = $pdo->query("SELECT * FROM jogos ORDER BY codigo ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Catálogo Jogos</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; margin: 40px auto; max-width: 600px; }
        .container { background-color: #eaeaea; padding: 30px; border: 1px solid #ccc; }
        h1 { text-align: center; margin-top: 0; }
        .btn-novo { display: inline-block; background-color: #5b7be0; color: #000; font-weight: bold; padding: 8px 20px; text-decoration: none; border: 1px solid #3b5bc0; margin-bottom: 20px; }
        .form-card { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ccc; }
        label { display: block; margin-top: 8px; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 6px; margin-top: 4px; box-sizing: border-box; }
        .btn-salvar { margin-top: 10px; background-color: #5b7be0; font-weight: bold; padding: 8px 15px; border: none; cursor: pointer; }
        .btn-cancelar { text-decoration: none; color: #333; margin-left: 10px; font-size: 14px; }
        .jogo-item { margin-bottom: 20px; font-size: 18px; }
        .jogo-header a { color: #0000ee; }
        .jogo-descricao { margin-left: 30px; color: #333; font-size: 15px; margin-top: 4px; }
        .acoes { margin-left: 30px; margin-top: 5px; font-size: 13px; }
        .acoes a { margin-right: 10px; color: #333; text-decoration: none; }
        .erro { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h1>Catálogo Jogos</h1>


    <?php if (!$exibir_form): ?>
        <a href="index.php?acao=novo" class="btn-novo">+ Novo</a>
    <?php endif; ?>

 
    <?php if ($exibir_form): ?>
        <div class="form-card">
            <h3><?= $codigo_edit ? 'Alterar Jogo #' . htmlspecialchars($codigo_edit) : 'Adicionar Novo Jogo' ?></h3>
            
            <?php if ($erro): ?>
                <p class="erro"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <input type="hidden" name="codigo" value="<?= htmlspecialchars($codigo_edit ?? '') ?>">

                <label for="nome">Nome do Jogo *</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome_edit) ?>" required>

                <label for="link">Link *</label>
                <input type="text" id="link" name="link" value="<?= htmlspecialchars($link_edit) ?>" required placeholder="https://...">

                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="3"><?= htmlspecialchars($descricao_edit) ?></textarea>

                <button type="submit" class="btn-salvar"><?= $codigo_edit ? 'Atualizar' : '+ Cadastrar' ?></button>
                <a href="index.php" class="btn-cancelar">Cancelar</a>
            </form>
        </div>
    <?php endif; ?>

 
    <?php if (empty($jogos)): ?>
        <p>Nenhum jogo cadastrado.</p>
    <?php else: ?>
        <?php foreach ($jogos as $jogo): ?>
            <div class="jogo-item">
                <div class="jogo-header">
                    <?= htmlspecialchars($jogo['codigo']) ?> - <?= htmlspecialchars($jogo['nome']) ?> - <a href="<?= htmlspecialchars($jogo['link']) ?>" target="_blank"><?= htmlspecialchars($jogo['link']) ?></a>
                </div>
                <?php if (!empty($jogo['descricao'])): ?>
                    <div class="jogo-descricao">
                        <?= nl2br(htmlspecialchars($jogo['descricao'])) ?>
                    </div>
                <?php endif; ?>
                <div class="acoes">
                    <a href="index.php?acao=editar&codigo=<?= $jogo['codigo'] ?>">✏️ Alterar</a> | 
                    <a href="index.php?acao=excluir&codigo=<?= $jogo['codigo'] ?>" onclick="return confirm('Excluir este jogo?');">❌ Excluir</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
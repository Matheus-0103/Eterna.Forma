<?php
// PBI-001: Realizar Cadastro do Desportista
// PBI-002: Realizar Login como Desportista
require_once __DIR__ . '/config.php';

$modo   = $_GET['modo'] ?? 'login';   // login | cadastro
$erros  = [];
$sucesso = false;
$campos  = ['nome'=>'','sobrenome'=>'','email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ── LOGIN ────────────────────────────────────────────────────────
    if ($acao === 'login') {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $erros['geral'] = 'Preencha e-mail e senha.';
        } else {
            $stmt = getDB()->prepare('SELECT id, nome, senha_hash, ativo FROM desportistas WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($senha, $user['senha_hash'])) {
                $erros['geral'] = 'E-mail ou senha incorretos.';
            } elseif (!$user['ativo']) {
                $erros['geral'] = 'Conta inativa. Contate o suporte.';
            } else {
                loginDesportista($user['id'], $user['nome']);
                header('Location: desportista_dashboard.php');
                exit;
            }
        }
        $modo = 'login';

    // ── CADASTRO ─────────────────────────────────────────────────────
    } elseif ($acao === 'cadastro') {
        $nome      = trim($_POST['nome']      ?? '');
        $sobrenome = trim($_POST['sobrenome'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $senha     = $_POST['senha']          ?? '';
        $confirmar = $_POST['confirmar']      ?? '';
        $campos    = compact('nome','sobrenome','email');

        if (strlen($nome) < 2)       $erros['nome']      = 'Mínimo 2 caracteres.';
        if (strlen($sobrenome) < 2)  $erros['sobrenome'] = 'Mínimo 2 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido.';
        if (strlen($senha) < 8)      $erros['senha']     = 'Mínimo 8 caracteres.';
        if ($senha !== $confirmar)   $erros['confirmar'] = 'Senhas não coincidem.';

        if (empty($erros)) {
            try {
                $pdo = getDB();
                $chk = $pdo->prepare('SELECT id FROM desportistas WHERE email = ? LIMIT 1');
                $chk->execute([$email]);
                if ($chk->fetch()) {
                    $erros['email'] = 'E-mail já cadastrado.';
                } else {
                    $pdo->prepare(
                        'INSERT INTO desportistas (nome,sobrenome,email,senha_hash) VALUES (?,?,?,?)'
                    )->execute([$nome, $sobrenome, $email, hashSenha($senha)]);
                    $sucesso = true;
                    $campos  = ['nome'=>'','sobrenome'=>'','email'=>''];
                }
            } catch (PDOException $e) {
                $erros['geral'] = 'Erro interno. Tente novamente.';
                error_log($e->getMessage());
            }
        }
        $modo = 'cadastro';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SportMatch — Desportistas</title>
  <link rel="stylesheet" href="sportmatch.css">
</head>
<body>
<div class="card">
  <div class="panel-left">

    <div class="tab-row">
      <a class="tab <?= $modo==='login'?'active':'' ?>"     href="desportista_login.php?modo=login">Entrar</a>
      <a class="tab <?= $modo==='cadastro'?'active':'' ?>"  href="desportista_login.php?modo=cadastro">Cadastrar</a>
    </div>

    <?php if (!empty($erros['geral'])): ?>
      <div class="alert-box error"><?= h($erros['geral']) ?></div>
    <?php elseif ($sucesso): ?>
      <div class="alert-box success">🎉 Conta criada! <a href="desportista_login.php?modo=login">Faça login</a></div>
    <?php endif; ?>

    <?php if ($modo === 'login'): ?>
    <!-- ══ FORMULÁRIO LOGIN ══ -->
    <form method="POST" data-sm-form>
      <input type="hidden" name="acao" value="login">
      <div class="field-wrap">
        <label class="field-label" for="email">E-mail</label>
        <input class="field-input" type="email" id="email" name="email" placeholder="seu@email.com" required>
        <span class="field-error" id="err-email"></span>
      </div>
      <div class="field-wrap">
        <label class="field-label" for="senha">Senha</label>
        <input class="field-input" type="password" id="senha" name="senha" placeholder="Sua senha" required>
        <span class="field-error" id="err-senha"></span>
      </div>
      <button class="btn-primary" type="submit">
        <span class="btn-label">Entrar</span><div class="spinner"></div>
      </button>
    </form>

    <?php else: ?>
    <!-- ══ FORMULÁRIO CADASTRO ══ -->
    <form method="POST" data-sm-form>
      <input type="hidden" name="acao" value="cadastro">
      <div class="field-row">
        <div class="field-wrap">
          <label class="field-label" for="nome">Nome</label>
          <input class="field-input <?= isset($erros['nome'])?'invalid':'' ?>" type="text" id="nome" name="nome" placeholder="João" value="<?= h($campos['nome']) ?>" required>
          <span class="field-error" id="err-nome"><?= h($erros['nome']??'') ?></span>
        </div>
        <div class="field-wrap">
          <label class="field-label" for="sobrenome">Sobrenome</label>
          <input class="field-input <?= isset($erros['sobrenome'])?'invalid':'' ?>" type="text" id="sobrenome" name="sobrenome" placeholder="Silva" value="<?= h($campos['sobrenome']) ?>" required>
          <span class="field-error" id="err-sobrenome"><?= h($erros['sobrenome']??'') ?></span>
        </div>
      </div>
      <div class="field-wrap">
        <label class="field-label" for="email">E-mail</label>
        <input class="field-input <?= isset($erros['email'])?'invalid':'' ?>" type="email" id="email" name="email" placeholder="seu@email.com" value="<?= h($campos['email']) ?>" required>
        <span class="field-error" id="err-email"><?= h($erros['email']??'') ?></span>
      </div>
      <div class="field-wrap">
        <label class="field-label" for="senha">Senha</label>
        <input class="field-input <?= isset($erros['senha'])?'invalid':'' ?>" type="password" id="senha" name="senha" placeholder="Mín. 8 caracteres" required minlength="8">
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        <span class="strength-label" id="strengthLabel"></span>
        <span class="field-error" id="err-senha"><?= h($erros['senha']??'') ?></span>
      </div>
      <div class="field-wrap">
        <label class="field-label" for="confirmar">Confirmar senha</label>
        <input class="field-input <?= isset($erros['confirmar'])?'invalid':'' ?>" type="password" id="confirmar" name="confirmar" placeholder="Repita a senha" required>
        <span class="field-error" id="err-confirmar"><?= h($erros['confirmar']??'') ?></span>
      </div>
      <button class="btn-primary" type="submit">
        <span class="btn-label">Criar conta</span><div class="spinner"></div>
      </button>
    </form>
    <?php endif; ?>

  </div>

  <div class="panel-right">
    <div class="logo-mark">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <h1 class="r-title">Área do Desportista</h1>
    <p class="r-sub">Encontre parceiros de treino com o mesmo perfil fitness que o seu.</p>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div><p class="feature-title">Perfil fitness completo</p><p class="feature-text">Nível, objetivo, modalidades e disponibilidade.</p></div>
    </div>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
      <div><p class="feature-title">Match inteligente</p><p class="feature-text">Score de similaridade automático entre perfis.</p></div>
    </div>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
      <div><p class="feature-title">Suporte direto</p><p class="feature-text">Abra chamados para a equipe técnica.</p></div>
    </div>
    <a href="index.php" style="color:rgba(245,230,214,.45);font-size:.8rem;text-decoration:none;position:relative;">← Voltar ao início</a>
  </div>
</div>
<script src="sportmatch.js"></script>
</body>
</html>

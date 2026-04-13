<?php
// PBI-007: Realizar Cadastro da Equipe Técnica
// PBI-008: Realizar Login como Equipe Técnica
require_once __DIR__ . '/config.php';

$modo  = $_GET['modo'] ?? 'login';
$erros = [];
$sucesso = false;
$campos  = ['nome'=>'','sobrenome'=>'','email'=>'','especialidade'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ── LOGIN ───────────────────────────────────────────────────────
    if ($acao === 'login') {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        if (!$email || !$senha) {
            $erros['geral'] = 'Preencha e-mail e senha.';
        } else {
            $stmt = getDB()->prepare('SELECT id,nome,senha_hash,ativo FROM equipe_tecnica WHERE email=? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($senha, $user['senha_hash']))
                $erros['geral'] = 'Credenciais inválidas.';
            elseif (!$user['ativo'])
                $erros['geral'] = 'Conta inativa.';
            else {
                loginTecnico($user['id'], $user['nome']);
                header('Location: tecnico_dashboard.php'); exit;
            }
        }
        $modo = 'login';

    // ── CADASTRO ────────────────────────────────────────────────────
    } elseif ($acao === 'cadastro') {
        $nome         = trim($_POST['nome']         ?? '');
        $sobrenome    = trim($_POST['sobrenome']    ?? '');
        $email        = trim($_POST['email']        ?? '');
        $especialidade= trim($_POST['especialidade']?? '');
        $senha        = $_POST['senha']    ?? '';
        $confirmar    = $_POST['confirmar']?? '';
        $campos       = compact('nome','sobrenome','email','especialidade');

        if (strlen($nome)<2)     $erros['nome']      = 'Mínimo 2 caracteres.';
        if (strlen($sobrenome)<2)$erros['sobrenome'] = 'Mínimo 2 caracteres.';
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido.';
        if (strlen($senha)<8)    $erros['senha']     = 'Mínimo 8 caracteres.';
        if ($senha!==$confirmar) $erros['confirmar'] = 'Senhas não coincidem.';

        if (empty($erros)) {
            try {
                $pdo = getDB();
                $chk = $pdo->prepare('SELECT id FROM equipe_tecnica WHERE email=? LIMIT 1');
                $chk->execute([$email]);
                if ($chk->fetch()) { $erros['email'] = 'E-mail já cadastrado.'; }
                else {
                    $pdo->prepare('INSERT INTO equipe_tecnica (nome,sobrenome,email,senha_hash,especialidade) VALUES (?,?,?,?,?)')->execute([$nome,$sobrenome,$email,hashSenha($senha),$especialidade]);
                    $sucesso = true;
                    $campos  = ['nome'=>'','sobrenome'=>'','email'=>'','especialidade'=>''];
                }
            } catch(PDOException $e) { $erros['geral']='Erro interno.'; error_log($e->getMessage()); }
        }
        $modo = 'cadastro';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SportMatch — Equipe Técnica</title>
  <link rel="stylesheet" href="sportmatch.css">
</head>
<body>
<div class="card">
  <div class="panel-left">
    <div class="tab-row">
      <a class="tab <?= $modo==='login'?'active':'' ?>"    href="tecnico_login.php?modo=login">Entrar</a>
      <a class="tab <?= $modo==='cadastro'?'active':'' ?>" href="tecnico_login.php?modo=cadastro">Cadastrar</a>
    </div>

    <?php if (!empty($erros['geral'])): ?><div class="alert-box error"><?= h($erros['geral']) ?></div>
    <?php elseif ($sucesso): ?><div class="alert-box success">✅ Técnico cadastrado! <a href="tecnico_login.php?modo=login">Faça login</a></div>
    <?php endif; ?>

    <?php if ($modo === 'login'): ?>
    <form method="POST" data-sm-form>
      <input type="hidden" name="acao" value="login">
      <div class="field-wrap">
        <label class="field-label">E-mail</label>
        <input class="field-input" type="email" id="email" name="email" placeholder="tecnico@sportmatch.com" required>
        <span class="field-error" id="err-email"></span>
      </div>
      <div class="field-wrap">
        <label class="field-label">Senha</label>
        <input class="field-input" type="password" id="senha" name="senha" placeholder="Sua senha" required>
        <span class="field-error" id="err-senha"></span>
      </div>
      <button class="btn-primary" type="submit"><span class="btn-label">Entrar</span><div class="spinner"></div></button>
    </form>
    <?php else: ?>
    <form method="POST" data-sm-form>
      <input type="hidden" name="acao" value="cadastro">
      <div class="field-row">
        <div class="field-wrap">
          <label class="field-label">Nome</label>
          <input class="field-input <?= isset($erros['nome'])?'invalid':'' ?>" type="text" id="nome" name="nome" placeholder="Ana" value="<?= h($campos['nome']) ?>" required>
          <span class="field-error" id="err-nome"><?= h($erros['nome']??'') ?></span>
        </div>
        <div class="field-wrap">
          <label class="field-label">Sobrenome</label>
          <input class="field-input <?= isset($erros['sobrenome'])?'invalid':'' ?>" type="text" id="sobrenome" name="sobrenome" placeholder="Costa" value="<?= h($campos['sobrenome']) ?>" required>
          <span class="field-error" id="err-sobrenome"><?= h($erros['sobrenome']??'') ?></span>
        </div>
      </div>
      <div class="field-wrap">
        <label class="field-label">E-mail</label>
        <input class="field-input <?= isset($erros['email'])?'invalid':'' ?>" type="email" id="email" name="email" placeholder="tecnico@sportmatch.com" value="<?= h($campos['email']) ?>" required>
        <span class="field-error" id="err-email"><?= h($erros['email']??'') ?></span>
      </div>
      <div class="field-wrap">
        <label class="field-label">Especialidade</label>
        <input class="field-input" type="text" id="especialidade" name="especialidade" placeholder="Ex: Suporte N2, Fitness, DevOps" value="<?= h($campos['especialidade']) ?>">
      </div>
      <div class="field-wrap">
        <label class="field-label">Senha</label>
        <input class="field-input <?= isset($erros['senha'])?'invalid':'' ?>" type="password" id="senha" name="senha" placeholder="Mín. 8 caracteres" required minlength="8">
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        <span class="strength-label" id="strengthLabel"></span>
        <span class="field-error" id="err-senha"><?= h($erros['senha']??'') ?></span>
      </div>
      <div class="field-wrap">
        <label class="field-label">Confirmar senha</label>
        <input class="field-input <?= isset($erros['confirmar'])?'invalid':'' ?>" type="password" id="confirmar" name="confirmar" placeholder="Repita a senha" required>
        <span class="field-error" id="err-confirmar"><?= h($erros['confirmar']??'') ?></span>
      </div>
      <button class="btn-primary" type="submit"><span class="btn-label">Cadastrar Técnico</span><div class="spinner"></div></button>
    </form>
    <?php endif; ?>
  </div>

  <div class="panel-right">
    <div class="logo-mark">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
    </div>
    <h1 class="r-title">Equipe Técnica</h1>
    <p class="r-sub">Gerencie chamados, atendentes e desportistas da plataforma.</p>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div><p class="feature-title">Gestão de chamados</p><p class="feature-text">Levante, responda e encerre tickets.</p></div>
    </div>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
      <div><p class="feature-title">Cadastro de Atendentes</p><p class="feature-text">Gerencie atendentes de academia.</p></div>
    </div>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg></div>
      <div><p class="feature-title">Relatórios</p><p class="feature-text">Visibilidade total sobre a operação.</p></div>
    </div>
    <a href="index.php" style="color:rgba(245,230,214,.45);font-size:.8rem;text-decoration:none;position:relative;">← Voltar ao início</a>
  </div>
</div>
<script src="sportmatch.js"></script>
</body>
</html>

<?php
// PBI-012: Realizar Login como Atendente de Academia
require_once __DIR__ . '/config.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!$email || !$senha) {
        $erros['geral'] = 'Preencha e-mail e senha.';
    } else {
        $stmt = getDB()->prepare('SELECT id,nome,senha_hash,ativo FROM atendentes WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($senha, $user['senha_hash'])) {
            $erros['geral'] = 'Credenciais inválidas.';
        } elseif (!$user['ativo']) {
            $erros['geral'] = 'Conta inativa. Contate a equipe técnica.';
        } else {
            loginAtendente($user['id'], $user['nome']);
            header('Location: atendente_dashboard.php'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SportMatch — Atendente</title>
  <link rel="stylesheet" href="sportmatch.css">
</head>
<body>
<div class="card">
  <div class="panel-left">
    <div class="tab-row">
      <span class="tab active">Atendente de Academia</span>
    </div>

    <?php if (!empty($erros['geral'])): ?>
      <div class="alert-box error"><?= h($erros['geral']) ?></div>
    <?php endif; ?>

    <form method="POST" data-sm-form>
      <div class="field-wrap">
        <label class="field-label">E-mail</label>
        <input class="field-input" type="email" id="email" name="email" placeholder="atendente@academia.com" required>
        <span class="field-error" id="err-email"></span>
      </div>
      <div class="field-wrap">
        <label class="field-label">Senha</label>
        <input class="field-input" type="password" id="senha" name="senha" placeholder="Sua senha" required>
        <span class="field-error" id="err-senha"></span>
      </div>
      <button class="btn-primary" type="submit">
        <span class="btn-label">Entrar</span><div class="spinner"></div>
      </button>
    </form>

    <p style="font-size:.82rem;color:var(--muted);text-align:center;margin-top:8px">
      Sua conta é criada pela Equipe Técnica.
    </p>
  </div>

  <div class="panel-right">
    <div class="logo-mark">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </div>
    <h1 class="r-title">Área do Atendente</h1>
    <p class="r-sub">Visualize desportistas e gerencie o acesso à academia.</p>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div><p class="feature-title">Consulta de perfis</p><p class="feature-text">Visualize desportistas cadastrados.</p></div>
    </div>
    <div class="feature-item">
      <div class="feature-dot"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
      <div><p class="feature-title">Acesso simplificado</p><p class="feature-text">Interface focada no atendimento presencial.</p></div>
    </div>
    <a href="index.php" style="color:rgba(245,230,214,.45);font-size:.8rem;text-decoration:none;position:relative;">← Voltar ao início</a>
  </div>
</div>
<script src="sportmatch.js"></script>
</body>
</html>

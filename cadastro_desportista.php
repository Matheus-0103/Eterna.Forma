<?php
// Cadastro backend aqui
$erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome     = $_POST["nome"]     ?? "";
    $sobrenome = $_POST["sobrenome"] ?? "";
    $email    = $_POST["email"]    ?? "";
    $senha    = $_POST["senha"]    ?? "";
    $confirmar = $_POST["confirmar"] ?? "";
    // TODO: validar e inserir no banco de dados
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Conta</title>
  <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="wrap">
  <div class="card">

    <div class="panel-left">
      <div class="tab-row">
        <a class="tab" href="login.php">Entrar</a>
        <a class="tab active" href="cadastro.php">Cadastrar</a>
      </div>

      <?php if ($erro): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <form method="POST" action="cadastro.php">
        <div class="field-row">
          <div class="field-wrap">
            <label class="field-label" for="nome">Nome</label>
            <input class="field-input" type="text" id="nome" name="nome" placeholder="João" required>
          </div>
          <div class="field-wrap">
            <label class="field-label" for="sobrenome">Sobrenome</label>
            <input class="field-input" type="text" id="sobrenome" name="sobrenome" placeholder="Silva" required>
          </div>
        </div>
        <div class="field-wrap">
          <label class="field-label" for="email">E-mail</label>
          <input class="field-input" type="email" id="email" name="email" placeholder="seu@email.com" required>
        </div>
        <div class="field-wrap">
          <label class="field-label" for="senha">Senha</label>
          <input class="field-input" type="password" id="senha" name="senha" placeholder="Mín. 8 caracteres" required minlength="8">
        </div>
        <div class="field-wrap">
          <label class="field-label" for="confirmar">Confirmar senha</label>
          <input class="field-input" type="password" id="confirmar" name="confirmar" placeholder="Repita a senha" required>
        </div>
        <button class="btn-primary" type="submit">Criar conta</button>
      </form>
    </div>

    <div class="panel-right">
      <div class="logo-mark">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="19" y1="8" x2="19" y2="14"/>
          <line x1="22" y1="11" x2="16" y2="11"/>
        </svg>
      </div>
      <h1 class="r-title">Crie sua conta grátis</h1>
      <p class="r-sub">Junte-se a milhares de usuários e comece agora mesmo.</p>

      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div>
          <p class="feature-title">Configuração em minutos</p>
          <p class="feature-text">Sem burocracia. Comece a usar imediatamente.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
          <p class="feature-title">Suporte 24/7</p>
          <p class="feature-text">Nossa equipe pronta para te ajudar.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <div>
          <p class="feature-title">Plano gratuito incluso</p>
          <p class="feature-text">Sem cartão de crédito. Cancele quando quiser.</p>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>

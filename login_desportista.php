<?php
// Login backend aqui
$erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $senha = $_POST["senha"] ?? "";
    // TODO: validar credenciais no banco de dados
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar</title>
  <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="wrap">
  <div class="card">

    <div class="panel-left">
      <div class="tab-row">
        <a class="tab active" href="login.php">Entrar</a>
        <a class="tab" href="cadastro.php">Cadastrar</a>
      </div>

      <?php if ($erro): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="field-wrap">
          <label class="field-label" for="email">E-mail</label>
          <input class="field-input" type="email" id="email" name="email" placeholder="seu@email.com" required>
        </div>
        <div class="field-wrap">
          <label class="field-label" for="senha">Senha</label>
          <input class="field-input" type="password" id="senha" name="senha" placeholder="••••••••" required>
        </div>

        <a class="forgot" href="recuperar.php">Esqueci minha senha</a>

        <button class="btn-primary" type="submit">Entrar</button>
      </form>

      <div class="divider">
        <div class="divider-line"></div>
        <span class="divider-text">ou continue com</span>
        <div class="divider-line"></div>
      </div>
      <div class="social-row">
        <a class="btn-social" href="oauth/google.php">
          <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#4285F4" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Google
        </a>
        <a class="btn-social" href="oauth/facebook.php">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
          </svg>
          Facebook
        </a>
      </div>
    </div>

    <div class="panel-right">
      <div class="logo-mark">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
        </svg>
      </div>
      <h1 class="r-title">Bem-vindo de volta</h1>
      <p class="r-sub">Acesse sua conta e retome de onde parou.</p>

      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div>
          <p class="feature-title">Acesso seguro</p>
          <p class="feature-text">Seus dados protegidos com criptografia.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>
        </div>
        <div>
          <p class="feature-title">Histórico completo</p>
          <p class="feature-text">Tudo salvo e sincronizado para você.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div>
          <p class="feature-title">Dashboard em tempo real</p>
          <p class="feature-text">Métricas e relatórios atualizados.</p>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>

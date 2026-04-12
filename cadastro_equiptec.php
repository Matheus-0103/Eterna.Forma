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
  <title>Criar Conta Equipe Técnica</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: Inter, system-ui, sans-serif;
      background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
      color: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .wrap {
      width: min(100%, 1080px);
      padding: 24px;
    }

    .card {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1px;
      background: #111827;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 32px 80px rgba(15, 23, 42, 0.45);
    }

    .panel-left,
    .panel-right {
      background: #111827;
      padding: 40px;
    }

    .panel-right {
      background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 24px;
    }

    .tab-row {
      display: flex;
      gap: 8px;
      margin-bottom: 32px;
    }

    .tab {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 12px 16px;
      border-radius: 999px;
      text-decoration: none;
      color: #cbd5e1;
      font-weight: 600;
      background: rgba(255,255,255,0.03);
      transition: background 0.2s ease, color 0.2s ease;
    }

    .tab:hover {
      background: rgba(255,255,255,0.08);
    }

    .tab.active {
      background: #2563eb;
      color: #ffffff;
    }

    .alert {
      margin-bottom: 24px;
      padding: 14px 16px;
      border-radius: 16px;
      background: rgba(239, 68, 68, 0.15);
      color: #fecaca;
      border: 1px solid rgba(248, 113, 113, 0.3);
    }

    .field-row {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
    }

    .field-wrap {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 18px;
    }

    .field-label {
      font-size: 0.95rem;
      color: #cbd5e1;
    }

    .field-input {
      width: 100%;
      padding: 14px 16px;
      border-radius: 18px;
      border: 1px solid rgba(148, 163, 184, 0.18);
      background: rgba(255,255,255,0.04);
      color: #f8fafc;
      font-size: 1rem;
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    .field-input:focus {
      outline: none;
      border-color: #60a5fa;
      background: rgba(255,255,255,0.12);
    }

    button.btn-primary {
      width: 100%;
      margin-top: 8px;
      padding: 16px 18px;
      border: none;
      border-radius: 18px;
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: #ffffff;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    button.btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 30px rgba(37, 99, 235, 0.2);
    }

    .logo-mark {
      width: fit-content;
      padding: 16px;
      border-radius: 18px;
      background: rgba(249, 250, 251, 0.05);
      border: 1px solid rgba(255,255,255,0.08);
    }

    .r-title {
      margin: 0;
      font-size: clamp(2rem, 2.8vw, 3.3rem);
      line-height: 1.05;
    }

    .r-sub {
      margin: 0;
      max-width: 38rem;
      color: #cbd5e1;
      line-height: 1.8;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 20px 0;
      border-top: 1px solid rgba(255,255,255,0.06);
    }

    .feature-item:first-child {
      border-top: none;
    }

    .feature-dot {
      width: 40px;
      height: 40px;
      border-radius: 14px;
      display: grid;
      place-items: center;
      background: rgba(59, 130, 246, 0.12);
    }

    @media (max-width: 870px) {
      .card {
        grid-template-columns: 1fr;
      }

      .panel-right {
        padding: 28px 24px;
      }
    }

    @media (max-width: 560px) {
      .field-row {
        grid-template-columns: 1fr;
      }

      .panel-left,
      .panel-right {
        padding: 28px 20px;
      }
    }
  </style>
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

      <form id="cadastroForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
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
    
      </div>
      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-dot">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('cadastroForm');
    var senha = document.getElementById('senha');
    var confirmar = document.getElementById('confirmar');

    if (!form || !senha || !confirmar) {
      return;
    }

    var errorMessage = document.createElement('div');
    errorMessage.className = 'alert';
    errorMessage.style.display = 'none';
    confirmar.parentNode.appendChild(errorMessage);

    function validatePasswords() {
      if (senha.value && confirmar.value && senha.value !== confirmar.value) {
        errorMessage.textContent = 'As senhas não coincidem.';
        errorMessage.style.display = 'block';
        return false;
      }
      errorMessage.textContent = '';
      errorMessage.style.display = 'none';
      return true;
    }

    senha.addEventListener('input', validatePasswords);
    confirmar.addEventListener('input', validatePasswords);

    form.addEventListener('submit', function (event) {
      if (!validatePasswords()) {
        event.preventDefault();
        confirmar.focus();
      }
    });
  });
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SportMatch</title>
  <link rel="stylesheet" href="sportmatch.css">
  <style>
    body { display:flex; align-items:center; justify-content:center; }
    .hero { text-align:center; color:var(--cream); max-width:540px; padding: 0 24px; animation: cardIn .6s ease both; }
    .hero-logo { font-family:var(--font-serif); font-size:3rem; color:var(--cream); margin-bottom:8px; }
    .hero-sub  { font-size:1.05rem; color:rgba(245,230,214,.65); margin-bottom:40px; line-height:1.7; }
    .access-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(150px,1fr)); gap:14px; }
    .access-card {
      background: rgba(245,230,214,.08);
      border: 1px solid rgba(245,230,214,.15);
      border-radius: 14px; padding: 24px 16px;
      text-decoration: none; color: var(--cream);
      transition: background .2s, transform .15s;
      display: flex; flex-direction: column; align-items: center; gap: 10px;
    }
    .access-card:hover { background: rgba(245,230,214,.15); transform: translateY(-3px); }
    .access-icon { width:44px; height:44px; border-radius:12px; background:rgba(192,83,42,.4); display:flex; align-items:center; justify-content:center; }
    .access-label { font-weight:600; font-size:.9rem; }
    .access-desc  { font-size:.78rem; color:rgba(245,230,214,.55); text-align:center; }
  </style>
</head>
<body>
<div class="hero">
  <div class="hero-logo">SportMatch</div>
  <p class="hero-sub">Conecte desportistas com perfis similares.<br>Plataforma de match fitness completa.</p>
  <div class="access-grid">
    <!-- Desportista -->
    <a class="access-card" href="desportista_login.php">
      <div class="access-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <span class="access-label">Desportistas</span>
      <span class="access-desc">Entrar ou criar conta</span>
    </a>
    <!-- Equipe Técnica -->
    <a class="access-card" href="tecnico_login.php">
      <div class="access-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      </div>
      <span class="access-label">Equipe Técnica</span>
      <span class="access-desc">Gestão e suporte</span>
    </a>
    <!-- Atendente -->
    <a class="access-card" href="atendente_login.php">
      <div class="access-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F5E6D6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <span class="access-label">Atendente</span>
      <span class="access-desc">Academia — login</span>
    </a>
  </div>
</div>
</body>
</html>

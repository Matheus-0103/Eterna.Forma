<?php
require_once __DIR__ . '/config.php';
authRequired('atendente');

$aid  = $_SESSION['user_id'];
$nome = $_SESSION['user_nome'];
$pdo  = getDB();

// Busca desportistas com seus perfis
$busca = trim($_GET['busca'] ?? '');
$params = [];
$sql = 'SELECT d.id, d.nome, d.sobrenome, d.email, d.criado_em, d.ativo,
               pf.nivel, pf.objetivo, pf.modalidades, pf.localizacao, pf.idade
        FROM desportistas d
        LEFT JOIN perfis_fitness pf ON pf.desportista_id = d.id
        WHERE 1=1';
if ($busca) {
    $sql .= ' AND (d.nome LIKE ? OR d.sobrenome LIKE ? OR d.email LIKE ? OR pf.localizacao LIKE ?)';
    $like = '%'.$busca.'%';
    $params = [$like,$like,$like,$like];
}
$sql .= ' ORDER BY d.criado_em DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$desportistas = $stmt->fetchAll();

$totalAtivos  = $pdo->query('SELECT COUNT(*) FROM desportistas WHERE ativo=1')->fetchColumn();
$totalPerfis  = $pdo->query('SELECT COUNT(*) FROM perfis_fitness')->fetchColumn();
$totalMatches = $pdo->query('SELECT COUNT(*) FROM matches WHERE status="aceito"')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SportMatch — Atendente</title>
  <link rel="stylesheet" href="sportmatch.css">
</head>
<body class="dash">
<div class="dash-wrap">

  <div class="dash-header">
    <div>
      <div class="dash-title">Painel do Atendente</div>
      <div class="dash-sub">Olá, <?= h($nome) ?></div>
    </div>
    <div class="dash-nav">
      <a href="atendente_dashboard.php" class="active">Desportistas</a>
      <a href="logout.php">Sair</a>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Desportistas Ativos</div><div class="stat-value"><?= $totalAtivos ?></div></div>
    <div class="stat-card"><div class="stat-label">Perfis Completos</div><div class="stat-value"><?= $totalPerfis ?></div></div>
    <div class="stat-card"><div class="stat-label">Matches Realizados</div><div class="stat-value"><?= $totalMatches ?></div></div>
  </div>

  <!-- Busca -->
  <form method="GET" style="display:flex;gap:10px;margin-bottom:16px">
    <input class="field-input" type="text" name="busca" placeholder="Buscar por nome, e-mail ou cidade..." value="<?= h($busca) ?>" style="flex:1">
    <button class="btn-primary" type="submit" style="width:auto;padding:11px 24px">Buscar</button>
    <?php if($busca): ?><a href="atendente_dashboard.php" class="btn-secondary" style="padding:11px 16px">Limpar</a><?php endif; ?>
  </form>

  <div class="table-wrap">
    <table>
      <thead><tr><th>Desportista</th><th>E-mail</th><th>Nível</th><th>Objetivo</th><th>Modalidades</th><th>Cidade</th><th>Status</th><th>Cadastro</th></tr></thead>
      <tbody>
      <?php if(empty($desportistas)): ?>
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">Nenhum desportista encontrado.</td></tr>
      <?php endif; ?>
      <?php foreach($desportistas as $d):
        $mods = json_decode($d['modalidades']??'[]',true)?:[];
      ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div class="profile-avatar" style="width:36px;height:36px;font-size:.85rem"><?= strtoupper(substr($d['nome'],0,1).substr($d['sobrenome'],0,1)) ?></div>
            <div>
              <div style="font-weight:600;font-size:.9rem"><?= h($d['nome'].' '.$d['sobrenome']) ?></div>
              <?php if($d['idade']): ?><div style="font-size:.78rem;color:var(--muted)"><?= $d['idade'] ?> anos</div><?php endif; ?>
            </div>
          </div>
        </td>
        <td style="font-size:.82rem"><?= h($d['email']) ?></td>
        <td><?= $d['nivel'] ? '<span class="badge badge-'.$d['nivel'].'">'.ucfirst($d['nivel']).'</span>' : '<span style="color:var(--muted);font-size:.8rem">—</span>' ?></td>
        <td style="font-size:.85rem"><?= $d['objetivo'] ? ucfirst($d['objetivo']) : '—' ?></td>
        <td style="font-size:.8rem;color:var(--muted)"><?= $mods ? implode(', ', array_slice($mods,0,3)) : '—' ?></td>
        <td style="font-size:.85rem"><?= h($d['localizacao']??'—') ?></td>
        <td><span class="badge" style="background:<?= $d['ativo']?'#D1FAE5':'#FEE2E2' ?>;color:<?= $d['ativo']?'#065F46':'#991B1B' ?>"><?= $d['ativo']?'Ativo':'Inativo' ?></span></td>
        <td style="font-size:.8rem;color:var(--muted)"><?= date('d/m/Y', strtotime($d['criado_em'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="sportmatch.js"></script>
</body>
</html>

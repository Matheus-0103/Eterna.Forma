<?php
// PBI-009: Realizar levantamento de Chamados
// PBI-010: Responder chamados técnicos para os usuários
// PBI-011: Realizar Cadastro do Atendente
require_once __DIR__ . '/config.php';
authRequired('tecnico');

$tid  = $_SESSION['user_id'];
$nome = $_SESSION['user_nome'];
$pdo  = getDB();
$aba  = $_GET['aba'] ?? 'chamados';
$msg  = ['tipo'=>'','texto'=>''];

// ════════════════════════════════════════════════════════════════════
// CHAMADOS — responder / atribuir / fechar
// ════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'responder_chamado') {
        $cid      = intval($_POST['chamado_id'] ?? 0);
        $resposta = trim($_POST['resposta'] ?? '');
        $status   = $_POST['novo_status'] ?? 'em_andamento';
        if ($cid && $resposta) {
            $pdo->prepare(
                'UPDATE chamados SET resposta=?,status=?,tecnico_id=?,respondido_em=NOW() WHERE id=?'
            )->execute([$resposta, $status, $tid, $cid]);
            $msg = ['tipo'=>'success','texto'=>'Resposta enviada!'];
        } else {
            $msg = ['tipo'=>'error','texto'=>'Preencha a resposta.'];
        }
        $aba = 'chamados';
    }

    // ════════════════════════════════════════════════════════════════
    // ATENDENTES — criar / editar / excluir
    // ════════════════════════════════════════════════════════════════
    elseif ($acao === 'criar_atendente') {
        $anome     = trim($_POST['anome']     ?? '');
        $asobrenome= trim($_POST['asobrenome']?? '');
        $aemail    = trim($_POST['aemail']    ?? '');
        $aacademia = trim($_POST['aacademia'] ?? '');
        $asenha    = $_POST['asenha'] ?? '';

        $errs = [];
        if (strlen($anome)<2)     $errs[] = 'Nome inválido.';
        if (!filter_var($aemail,FILTER_VALIDATE_EMAIL)) $errs[]='E-mail inválido.';
        if (strlen($asenha)<8)    $errs[] = 'Senha mínimo 8 caracteres.';

        if (empty($errs)) {
            try {
                $chk = $pdo->prepare('SELECT id FROM atendentes WHERE email=? LIMIT 1'); $chk->execute([$aemail]);
                if ($chk->fetch()) { $msg = ['tipo'=>'error','texto'=>'E-mail já cadastrado.']; }
                else {
                    $pdo->prepare('INSERT INTO atendentes (nome,sobrenome,email,senha_hash,academia,cadastrado_por) VALUES (?,?,?,?,?,?)')->execute([$anome,$asobrenome,$aemail,hashSenha($asenha),$aacademia,$tid]);
                    $msg = ['tipo'=>'success','texto'=>'Atendente cadastrado com sucesso!'];
                }
            } catch(PDOException $e) { $msg=['tipo'=>'error','texto'=>'Erro interno.']; error_log($e->getMessage()); }
        } else {
            $msg = ['tipo'=>'error','texto'=>implode(' ',$errs)];
        }
        $aba = 'atendentes';
    }

    elseif ($acao === 'toggle_atendente') {
        $aid = intval($_POST['atendente_id'] ?? 0);
        $pdo->prepare('UPDATE atendentes SET ativo = NOT ativo WHERE id=?')->execute([$aid]);
        $msg = ['tipo'=>'success','texto'=>'Status do atendente atualizado.'];
        $aba = 'atendentes';
    }

    elseif ($acao === 'excluir_atendente') {
        $aid = intval($_POST['atendente_id'] ?? 0);
        $pdo->prepare('DELETE FROM atendentes WHERE id=?')->execute([$aid]);
        $msg = ['tipo'=>'success','texto'=>'Atendente excluído.'];
        $aba = 'atendentes';
    }
}

// ── Dados ─────────────────────────────────────────────────────────
$filtroStatus = $_GET['status'] ?? '';
$filtroPrio   = $_GET['prio']   ?? '';
$sqlWhere     = 'WHERE 1=1';
$params       = [];
if ($filtroStatus) { $sqlWhere .= ' AND c.status=?'; $params[] = $filtroStatus; }
if ($filtroPrio)   { $sqlWhere .= ' AND c.prioridade=?'; $params[] = $filtroPrio; }

$chamados = $pdo->prepare("
    SELECT c.*, d.nome as dnome, d.sobrenome as dsobrenome, t.nome as tnome
    FROM chamados c
    JOIN desportistas d ON d.id=c.desportista_id
    LEFT JOIN equipe_tecnica t ON t.id=c.tecnico_id
    $sqlWhere ORDER BY
    FIELD(c.prioridade,'critica','alta','media','baixa'),
    FIELD(c.status,'aberto','em_andamento','resolvido','fechado'),
    c.criado_em DESC
");
$chamados->execute($params);
$chamados = $chamados->fetchAll();

$atendentes = $pdo->query('SELECT a.*, t.nome as tec_nome FROM atendentes a LEFT JOIN equipe_tecnica t ON t.id=a.cadastrado_por ORDER BY a.criado_em DESC')->fetchAll();

// Stats
$stats = $pdo->query('SELECT
    COUNT(*) as total,
    SUM(status="aberto") as abertos,
    SUM(status="em_andamento") as andamento,
    SUM(status="resolvido") as resolvidos
FROM chamados')->fetch();
$totalDesp = $pdo->query('SELECT COUNT(*) FROM desportistas WHERE ativo=1')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eterna Forma — Painel Técnico</title>
  <link rel="stylesheet" href="eterna_forma.css">
  <style>
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:100; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal-box { background:var(--white); border-radius:16px; padding:32px; max-width:480px; width:100%; box-shadow:0 24px 64px rgba(0,0,0,.25); }
    .modal-title { font-family:var(--font-serif); font-size:1.2rem; color:var(--brown-dark); margin-bottom:20px; }
  </style>
</head>
<body class="dash">
<div class="dash-wrap">

  <div class="dash-header">
    <div>
      <div class="dash-title">Painel Técnico</div>
      <div class="dash-sub">Olá, <?= h($nome) ?></div>
    </div>
    <div class="dash-nav">
      <a href="?aba=chamados"   class="<?= $aba==='chamados'?'active':'' ?>">Chamados</a>
      <a href="?aba=atendentes" class="<?= $aba==='atendentes'?'active':'' ?>">Atendentes</a>
      <a href="logout.php">Sair</a>
    </div>
  </div>

  <?php if ($msg['texto']): ?>
    <div class="alert-box <?= h($msg['tipo']) ?>" style="margin-bottom:20px"><?= h($msg['texto']) ?></div>
  <?php endif; ?>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Chamados</div><div class="stat-value"><?= $stats['total'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Abertos</div><div class="stat-value" style="color:var(--rust)"><?= $stats['abertos'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Em Andamento</div><div class="stat-value" style="color:var(--blue)"><?= $stats['andamento'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Resolvidos</div><div class="stat-value" style="color:var(--green)"><?= $stats['resolvidos'] ?></div></div>
    <div class="stat-card"><div class="stat-label">Desportistas</div><div class="stat-value"><?= $totalDesp ?></div></div>
    <div class="stat-card"><div class="stat-label">Atendentes</div><div class="stat-value"><?= count($atendentes) ?></div></div>
  </div>

  <!-- ══════════════ ABA: CHAMADOS ══════════════ -->
  <?php if ($aba === 'chamados'): ?>

  <!-- Filtros -->
  <form method="GET" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <input type="hidden" name="aba" value="chamados">
    <select name="status" class="field-select" style="width:auto;padding:8px 12px;font-size:.85rem" onchange="this.form.submit()">
      <option value="">Todos os status</option>
      <?php foreach(['aberto','em_andamento','resolvido','fechado'] as $s): ?>
      <option value="<?= $s ?>" <?= $filtroStatus===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="prio" class="field-select" style="width:auto;padding:8px 12px;font-size:.85rem" onchange="this.form.submit()">
      <option value="">Todas prioridades</option>
      <?php foreach(['critica','alta','media','baixa'] as $p): ?>
      <option value="<?= $p ?>" <?= $filtroPrio===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if($filtroStatus||$filtroPrio): ?>
    <a href="?aba=chamados" class="btn-secondary" style="padding:8px 14px;font-size:.85rem">Limpar</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Desportista</th><th>Título</th><th>Categoria</th><th>Prioridade</th><th>Status</th><th>Data</th><th>Ação</th></tr></thead>
      <tbody>
      <?php if(empty($chamados)): ?>
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">Nenhum chamado encontrado.</td></tr>
      <?php endif; ?>
      <?php foreach($chamados as $c): ?>
      <tr>
        <td style="color:var(--muted);font-size:.8rem">#<?= $c['id'] ?></td>
        <td><?= h($c['dnome'].' '.$c['dsobrenome']) ?></td>
        <td style="max-width:180px"><span title="<?= h($c['descricao']) ?>"><?= h(substr($c['titulo'],0,40)) ?></span></td>
        <td><?= ucfirst($c['categoria']) ?></td>
        <td><span class="badge badge-<?= $c['prioridade'] ?>"><?= ucfirst($c['prioridade']) ?></span></td>
        <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst(str_replace('_',' ',$c['status'])) ?></span></td>
        <td style="font-size:.8rem"><?= date('d/m/Y', strtotime($c['criado_em'])) ?></td>
        <td>
          <?php if(!in_array($c['status'],['resolvido','fechado'])): ?>
          <button class="btn-secondary" style="padding:5px 10px;font-size:.78rem"
            onclick="abrirModal(<?= $c['id'] ?>, '<?= addslashes(h($c['titulo'])) ?>', '<?= addslashes(h($c['descricao'])) ?>', '<?= h($c['resposta']??'') ?>')">Responder</button>
          <?php else: ?>
          <span style="font-size:.78rem;color:var(--muted)"><?= $c['tnome']?h('por '.$c['tnome']):'—' ?></span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal de resposta -->
  <div class="modal-overlay" id="modalResp">
    <div class="modal-box">
      <div class="modal-title" id="modalTitle">Responder Chamado</div>
      <form method="POST" data-sm-form>
        <input type="hidden" name="acao" value="responder_chamado">
        <input type="hidden" name="chamado_id" id="modalChamadoId">
        <div class="field-wrap" style="margin-bottom:12px">
          <label class="field-label">Descrição do chamado</label>
          <div id="modalDesc" style="font-size:.85rem;color:var(--muted);padding:10px;background:#f7f0e8;border-radius:8px;line-height:1.6"></div>
        </div>
        <div class="field-wrap" style="margin-bottom:12px">
          <label class="field-label">Resposta</label>
          <textarea class="field-textarea" id="resposta" name="resposta" placeholder="Digite a resposta técnica..." required style="min-height:100px"></textarea>
          <span class="field-error" id="err-resposta"></span>
        </div>
        <div class="field-wrap" style="margin-bottom:16px">
          <label class="field-label">Novo Status</label>
          <select class="field-select" name="novo_status">
            <option value="em_andamento">Em Andamento</option>
            <option value="resolvido">Resolvido</option>
          </select>
        </div>
        <div style="display:flex;gap:10px">
          <button class="btn-primary" type="submit" style="flex:1"><span class="btn-label">Enviar Resposta</span><div class="spinner"></div></button>
          <button type="button" class="btn-secondary" onclick="fecharModal()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ══════════════ ABA: ATENDENTES ══════════════ -->
  <?php elseif ($aba === 'atendentes'): ?>
  <div class="form-card" style="margin-bottom:24px">
    <div class="form-title">Cadastrar Atendente</div>
    <form method="POST" data-sm-form>
      <input type="hidden" name="acao" value="criar_atendente">
      <div class="field-row">
        <div class="field-wrap">
          <label class="field-label">Nome</label>
          <input class="field-input" type="text" id="anome" name="anome" placeholder="Maria" required>
          <span class="field-error" id="err-anome"></span>
        </div>
        <div class="field-wrap">
          <label class="field-label">Sobrenome</label>
          <input class="field-input" type="text" id="asobrenome" name="asobrenome" placeholder="Santos">
        </div>
      </div>
      <div class="field-row">
        <div class="field-wrap">
          <label class="field-label">E-mail</label>
          <input class="field-input" type="email" id="aemail" name="aemail" placeholder="atendente@academia.com" required>
          <span class="field-error" id="err-aemail"></span>
        </div>
        <div class="field-wrap">
          <label class="field-label">Academia</label>
          <input class="field-input" type="text" name="aacademia" placeholder="Nome da academia">
        </div>
      </div>
      <div class="field-wrap">
        <label class="field-label">Senha inicial</label>
        <input class="field-input" type="password" name="asenha" placeholder="Mín. 8 caracteres" required minlength="8">
      </div>
      <button class="btn-primary" type="submit"><span class="btn-label">Cadastrar Atendente</span><div class="spinner"></div></button>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead><tr><th>Nome</th><th>E-mail</th><th>Academia</th><th>Cadastrado por</th><th>Status</th><th>Ações</th></tr></thead>
      <tbody>
      <?php if(empty($atendentes)): ?>
      <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">Nenhum atendente cadastrado.</td></tr>
      <?php endif; ?>
      <?php foreach($atendentes as $a): ?>
      <tr>
        <td><?= h($a['nome'].' '.$a['sobrenome']) ?></td>
        <td style="font-size:.85rem"><?= h($a['email']) ?></td>
        <td><?= h($a['academia']??'—') ?></td>
        <td style="font-size:.82rem;color:var(--muted)"><?= h($a['tec_nome']??'—') ?></td>
        <td><span class="badge" style="background:<?= $a['ativo']?'#D1FAE5':'#FEE2E2' ?>;color:<?= $a['ativo']?'#065F46':'#991B1B' ?>"><?= $a['ativo']?'Ativo':'Inativo' ?></span></td>
        <td style="display:flex;gap:6px">
          <form method="POST"><input type="hidden" name="acao" value="toggle_atendente"><input type="hidden" name="atendente_id" value="<?= $a['id'] ?>"><button class="btn-secondary" style="padding:5px 10px;font-size:.78rem"><?= $a['ativo']?'Desativar':'Ativar' ?></button></form>
          <form method="POST" onsubmit="return confirm('Excluir este atendente?')"><input type="hidden" name="acao" value="excluir_atendente"><input type="hidden" name="atendente_id" value="<?= $a['id'] ?>"><button class="btn-secondary" style="padding:5px 10px;font-size:.78rem;color:var(--red);border-color:var(--red)">Excluir</button></form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div>

<script src="eterna_forma.js"></script>
<script>
function abrirModal(id, titulo, desc, resposta) {
  document.getElementById('modalChamadoId').value = id;
  document.getElementById('modalTitle').textContent = 'Chamado #' + id + ' — ' + titulo;
  document.getElementById('modalDesc').textContent  = desc;
  document.getElementById('resposta').value = resposta || '';
  document.getElementById('modalResp').classList.add('open');
}
function fecharModal() {
  document.getElementById('modalResp').classList.remove('open');
}
document.getElementById('modalResp')?.addEventListener('click', function(e) {
  if (e.target === this) fecharModal();
});
</script>
</body>
</html>

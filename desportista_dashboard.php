<?php
// PBI-003: Criar perfil com dados Fitness
// PBI-004: Buscar desportistas com perfis similares
// PBI-005: Realizar Match com desportistas similares
// PBI-006: Manter Chamados para a equipe de Suporte
require_once __DIR__ . '/config.php';
authRequired('desportista');

$uid  = $_SESSION['user_id'];
$nome = $_SESSION['user_nome'];
$pdo  = getDB();
$aba  = $_GET['aba'] ?? 'perfil';
$msg  = ['tipo'=>'','texto'=>''];

// ════════════════════════════════════════════════════════════════════
// CRUD — PERFIL FITNESS
// ════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar_perfil') {
    $modalidades = $_POST['modalidades'] ?? [];
    $dados = [
        'idade'         => intval($_POST['idade'] ?? 0)          ?: null,
        'sexo'          => $_POST['sexo']         ?? null,
        'peso_kg'       => floatval($_POST['peso_kg'] ?? 0)      ?: null,
        'altura_cm'     => intval($_POST['altura_cm'] ?? 0)      ?: null,
        'nivel'         => $_POST['nivel']        ?? 'iniciante',
        'objetivo'      => $_POST['objetivo']     ?? 'saude',
        'modalidades'   => json_encode($modalidades),
        'disponibilidade'=> json_encode($_POST['disponibilidade'] ?? []),
        'localizacao'   => trim($_POST['localizacao'] ?? ''),
        'bio'           => trim($_POST['bio'] ?? ''),
    ];
    // UPSERT
    $exists = $pdo->prepare('SELECT id FROM perfis_fitness WHERE desportista_id = ?');
    $exists->execute([$uid]);
    if ($exists->fetch()) {
        $pdo->prepare(
            'UPDATE perfis_fitness SET idade=:idade,sexo=:sexo,peso_kg=:peso_kg,altura_cm=:altura_cm,
             nivel=:nivel,objetivo=:objetivo,modalidades=:modalidades,disponibilidade=:disponibilidade,
             localizacao=:localizacao,bio=:bio WHERE desportista_id=:id'
        )->execute(array_merge($dados, [':id' => $uid]));
    } else {
        $pdo->prepare(
            'INSERT INTO perfis_fitness (desportista_id,idade,sexo,peso_kg,altura_cm,nivel,objetivo,modalidades,disponibilidade,localizacao,bio)
             VALUES (:id,:idade,:sexo,:peso_kg,:altura_cm,:nivel,:objetivo,:modalidades,:disponibilidade,:localizacao,:bio)'
        )->execute(array_merge([':id'=>$uid], $dados));
    }
    $msg = ['tipo'=>'success','texto'=>'Perfil salvo com sucesso!'];
    $aba = 'perfil';
}

// ════════════════════════════════════════════════════════════════════
// MATCH — aceitar / recusar
// ════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['acao']??'', ['aceitar_match','recusar_match','solicitar_match'])) {
    $acao = $_POST['acao'];
    if ($acao === 'solicitar_match') {
        $alvo = intval($_POST['alvo_id'] ?? 0);
        // Calcula score
        $pA = $pdo->prepare('SELECT * FROM perfis_fitness WHERE desportista_id=?'); $pA->execute([$uid]); $pA = $pA->fetch();
        $pB = $pdo->prepare('SELECT * FROM perfis_fitness WHERE desportista_id=?'); $pB->execute([$alvo]); $pB = $pB->fetch();
        $score = ($pA && $pB) ? calcularSimilaridade($pA, $pB) : 0;
        // Evita duplicata
        $chk = $pdo->prepare('SELECT id FROM matches WHERE (desportista_a_id=? AND desportista_b_id=?) OR (desportista_a_id=? AND desportista_b_id=?)');
        $chk->execute([$uid,$alvo,$alvo,$uid]);
        if (!$chk->fetch()) {
            $pdo->prepare('INSERT INTO matches (desportista_a_id,desportista_b_id,score_similaridade) VALUES (?,?,?)')->execute([$uid,$alvo,$score]);
            $msg = ['tipo'=>'success','texto'=>'Solicitação de match enviada!'];
        } else {
            $msg = ['tipo'=>'error','texto'=>'Match já existente com este desportista.'];
        }
    } else {
        $matchId = intval($_POST['match_id'] ?? 0);
        $novoStatus = ($acao === 'aceitar_match') ? 'aceito' : 'recusado';
        // Só o "b" pode aceitar/recusar
        $pdo->prepare('UPDATE matches SET status=? WHERE id=? AND desportista_b_id=?')->execute([$novoStatus,$matchId,$uid]);
        $msg = ['tipo'=>'success','texto'=>'Match atualizado.'];
    }
    $aba = 'matches';
}

// ════════════════════════════════════════════════════════════════════
// CHAMADOS — criar / encerrar
// ════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao']??'') === 'abrir_chamado') {
    $titulo    = trim($_POST['titulo']    ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'] ?? 'outro';
    $prioridade= $_POST['prioridade'] ?? 'media';
    if ($titulo && $descricao) {
        $pdo->prepare('INSERT INTO chamados (desportista_id,titulo,descricao,categoria,prioridade) VALUES (?,?,?,?,?)')->execute([$uid,$titulo,$descricao,$categoria,$prioridade]);
        $msg = ['tipo'=>'success','texto'=>'Chamado aberto com sucesso!'];
    } else {
        $msg = ['tipo'=>'error','texto'=>'Título e descrição são obrigatórios.'];
    }
    $aba = 'chamados';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao']??'') === 'fechar_chamado') {
    $cid = intval($_POST['chamado_id'] ?? 0);
    $pdo->prepare('UPDATE chamados SET status="fechado" WHERE id=? AND desportista_id=?')->execute([$cid,$uid]);
    $msg = ['tipo'=>'success','texto'=>'Chamado encerrado.'];
    $aba = 'chamados';
}

// ── Leitura de dados ───────────────────────────────────────────────
$perfil = $pdo->prepare('SELECT * FROM perfis_fitness WHERE desportista_id=?'); $perfil->execute([$uid]); $perfil = $perfil->fetch();
$totalChamados = $pdo->prepare('SELECT COUNT(*) FROM chamados WHERE desportista_id=? AND status NOT IN ("fechado","resolvido")'); $totalChamados->execute([$uid]); $totalChamados = $totalChamados->fetchColumn();
$totalMatches  = $pdo->prepare('SELECT COUNT(*) FROM matches WHERE (desportista_a_id=? OR desportista_b_id=?) AND status="aceito"'); $totalMatches->execute([$uid,$uid]); $totalMatches = $totalMatches->fetchColumn();

// Busca similares (exclui já matched e o próprio)
$similares = [];
if ($perfil) {
    $todosPerfis = $pdo->query('SELECT pf.*, d.nome, d.sobrenome, d.id as did FROM perfis_fitness pf JOIN desportistas d ON d.id=pf.desportista_id WHERE pf.desportista_id != '.$uid.' AND d.ativo=1')->fetchAll();
    foreach ($todosPerfis as $p) {
        $score = calcularSimilaridade($perfil, $p);
        if ($score >= 30) {
            $p['score'] = $score;
            $similares[] = $p;
        }
    }
    usort($similares, fn($a,$b) => $b['score'] <=> $a['score']);
    $similares = array_slice($similares, 0, 12);
}

// Matches do usuário
$matches = $pdo->prepare('
    SELECT m.*, 
        da.nome as nome_a, da.sobrenome as sobre_a,
        db.nome as nome_b, db.sobrenome as sobre_b
    FROM matches m
    JOIN desportistas da ON da.id = m.desportista_a_id
    JOIN desportistas db ON db.id = m.desportista_b_id
    WHERE m.desportista_a_id=? OR m.desportista_b_id=?
    ORDER BY m.iniciado_em DESC
'); $matches->execute([$uid,$uid]); $matches = $matches->fetchAll();

// Chamados
$chamados = $pdo->prepare('SELECT * FROM chamados WHERE desportista_id=? ORDER BY criado_em DESC'); $chamados->execute([$uid]); $chamados = $chamados->fetchAll();

$modalAll  = ['Corrida','Natação','Ciclismo','Musculação','Crossfit','Yoga','Futebol','Basquete','Tênis','Boxe','Artes Marciais','Vôlei'];
$diasAll   = ['Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'];
$modSalvas = json_decode($perfil['modalidades'] ?? '[]', true) ?: [];
$diasSalvos= json_decode($perfil['disponibilidade'] ?? '[]', true) ?: [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eterna Forma — Dashboard</title>
  <link rel="stylesheet" href="eterna_forma.css">
</head>
<body class="dash">
<div class="dash-wrap">

  <!-- Header -->
  <div class="dash-header">
    <div>
      <div class="dash-title">Olá, <?= h($nome) ?> 👋</div>
      <div class="dash-sub">Painel do Desportista</div>
    </div>
    <div class="dash-nav">
      <a href="?aba=perfil"    class="<?= $aba==='perfil'?'active':'' ?>">Perfil Fitness</a>
      <a href="?aba=busca"     class="<?= $aba==='busca'?'active':'' ?>">Buscar</a>
      <a href="?aba=matches"   class="<?= $aba==='matches'?'active':'' ?>">Matches</a>
      <a href="?aba=chamados"  class="<?= $aba==='chamados'?'active':'' ?>">Chamados</a>
      <a href="logout.php">Sair</a>
    </div>
  </div>

  <?php if ($msg['texto']): ?>
    <div class="alert-box <?= h($msg['tipo']) ?>" style="margin-bottom:20px;"><?= h($msg['texto']) ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Matches Ativos</div><div class="stat-value"><?= $totalMatches ?></div></div>
    <div class="stat-card"><div class="stat-label">Chamados Abertos</div><div class="stat-value"><?= $totalChamados ?></div></div>
    <div class="stat-card"><div class="stat-label">Similares Encontrados</div><div class="stat-value"><?= count($similares) ?></div></div>
    <div class="stat-card"><div class="stat-label">Perfil</div><div class="stat-value" style="font-size:1.1rem"><?= $perfil ? ucfirst($perfil['nivel']) : 'Incompleto' ?></div></div>
  </div>

  <!-- ══════════════ ABA: PERFIL FITNESS ══════════════ -->
  <?php if ($aba === 'perfil'): ?>
  <div class="form-card">
    <div class="form-title">Perfil Fitness</div>
    <form method="POST" data-sm-form>
      <input type="hidden" name="acao" value="salvar_perfil">
      <div class="field-row">
        <div class="field-wrap">
          <label class="field-label">Idade</label>
          <input class="field-input" type="number" name="idade" min="10" max="99" value="<?= h($perfil['idade']??'') ?>">
        </div>
        <div class="field-wrap">
          <label class="field-label">Sexo</label>
          <select class="field-select" name="sexo">
            <option value="">-- Selecione --</option>
            <?php foreach(['M'=>'Masculino','F'=>'Feminino','outro'=>'Outro'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($perfil['sexo']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-wrap">
          <label class="field-label">Peso (kg)</label>
          <input class="field-input" type="number" step="0.1" name="peso_kg" value="<?= h($perfil['peso_kg']??'') ?>">
        </div>
        <div class="field-wrap">
          <label class="field-label">Altura (cm)</label>
          <input class="field-input" type="number" name="altura_cm" value="<?= h($perfil['altura_cm']??'') ?>">
        </div>
      </div>
      <div class="field-row">
        <div class="field-wrap">
          <label class="field-label">Nível</label>
          <select class="field-select" name="nivel">
            <?php foreach(['iniciante','intermediario','avancado'] as $n): ?>
            <option value="<?= $n ?>" <?= ($perfil['nivel']??'')===$n?'selected':'' ?>><?= ucfirst($n) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-wrap">
          <label class="field-label">Objetivo</label>
          <select class="field-select" name="objetivo">
            <?php foreach(['emagrecimento','hipertrofia','resistencia','saude','performance'] as $o): ?>
            <option value="<?= $o ?>" <?= ($perfil['objetivo']??'')===$o?'selected':'' ?>><?= ucfirst($o) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-wrap">
          <label class="field-label">Localização</label>
          <input class="field-input" type="text" name="localizacao" placeholder="Cidade, Estado" value="<?= h($perfil['localizacao']??'') ?>">
        </div>
      </div>
      <div class="field-wrap">
        <label class="field-label">Modalidades praticadas</label>
        <div class="check-grid">
          <?php foreach($modalAll as $m): ?>
          <label class="check-item">
            <input type="checkbox" name="modalidades[]" value="<?= $m ?>" <?= in_array($m,$modSalvas)?'checked':'' ?>>
            <?= $m ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="field-wrap">
        <label class="field-label">Disponibilidade</label>
        <div class="check-grid">
          <?php foreach($diasAll as $d): ?>
          <label class="check-item">
            <input type="checkbox" name="disponibilidade[]" value="<?= $d ?>" <?= in_array($d,$diasSalvos)?'checked':'' ?>>
            <?= $d ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="field-wrap">
        <label class="field-label">Bio / Apresentação</label>
        <textarea class="field-textarea" name="bio" placeholder="Conte um pouco sobre você e seus objetivos..."><?= h($perfil['bio']??'') ?></textarea>
      </div>
      <button class="btn-primary" type="submit"><span class="btn-label">Salvar perfil</span><div class="spinner"></div></button>
    </form>
  </div>

  <!-- ══════════════ ABA: BUSCA ══════════════ -->
  <?php elseif ($aba === 'busca'): ?>
  <?php if (!$perfil): ?>
    <div class="alert-box error">Complete seu perfil fitness primeiro para buscar similares.</div>
  <?php elseif (empty($similares)): ?>
    <div class="alert-box error" style="margin-bottom:0">Nenhum desportista similar encontrado ainda. Aguarde mais cadastros!</div>
  <?php else: ?>
  <div class="profile-grid">
    <?php foreach($similares as $s): ?>
    <div class="profile-card">
      <div style="display:flex;gap:12px;align-items:center">
        <div class="profile-avatar"><?= strtoupper(substr($s['nome'],0,1).substr($s['sobrenome'],0,1)) ?></div>
        <div>
          <div class="profile-name"><?= h($s['nome'].' '.$s['sobrenome']) ?></div>
          <div class="profile-sub"><?= h($s['localizacao']??'—') ?></div>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <span class="badge badge-<?= $s['nivel'] ?>"><?= ucfirst($s['nivel']) ?></span>
        <span class="match-score">⚡ <?= $s['score'] ?>% similar</span>
      </div>
      <div style="font-size:.82rem;color:var(--muted)"><strong>Objetivo:</strong> <?= ucfirst($s['objetivo']) ?></div>
      <?php $mods=json_decode($s['modalidades']??'[]',true)?:[];
            if($mods): ?>
      <div style="font-size:.8rem;color:#888"><?= implode(' · ', array_slice($mods,0,4)) ?></div>
      <?php endif; ?>
      <form method="POST">
        <input type="hidden" name="acao" value="solicitar_match">
        <input type="hidden" name="alvo_id" value="<?= $s['did'] ?>">
        <button class="btn-secondary" type="submit" style="width:100%;margin-top:4px">Solicitar Match</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ══════════════ ABA: MATCHES ══════════════ -->
  <?php elseif ($aba === 'matches'): ?>
  <?php if (empty($matches)): ?>
    <div class="alert-box error">Você ainda não tem matches. <a href="?aba=busca">Busque desportistas!</a></div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Parceiro</th><th>Score</th><th>Status</th><th>Data</th><th>Ação</th></tr></thead>
      <tbody>
      <?php foreach($matches as $m):
        $sou_a     = $m['desportista_a_id'] == $uid;
        $parceiro  = $sou_a ? h($m['nome_b'].' '.$m['sobre_b']) : h($m['nome_a'].' '.$m['sobre_a']);
        $posso_agir= !$sou_a && $m['status'] === 'pendente';
      ?>
      <tr>
        <td><?= $parceiro ?></td>
        <td><span class="match-score">⚡ <?= $m['score_similaridade'] ?>%</span></td>
        <td><span class="badge badge-<?= $m['status'] ?>"><?= ucfirst(str_replace('_',' ',$m['status'])) ?></span></td>
        <td><?= date('d/m/Y', strtotime($m['iniciado_em'])) ?></td>
        <td>
          <?php if($posso_agir): ?>
          <form method="POST" style="display:flex;gap:6px">
            <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
            <button name="acao" value="aceitar_match" class="btn-secondary" style="padding:6px 12px;font-size:.8rem">✓ Aceitar</button>
            <button name="acao" value="recusar_match" class="btn-secondary" style="padding:6px 12px;font-size:.8rem;color:var(--red);border-color:var(--red)">✗ Recusar</button>
          </form>
          <?php elseif($sou_a && $m['status']==='pendente'): ?>
            <span style="font-size:.8rem;color:var(--muted)">Aguardando...</span>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- ══════════════ ABA: CHAMADOS ══════════════ -->
  <?php elseif ($aba === 'chamados'): ?>
  <div class="form-card" style="margin-bottom:24px">
    <div class="form-title">Abrir Chamado</div>
    <form method="POST" data-sm-form>
      <input type="hidden" name="acao" value="abrir_chamado">
      <div class="field-wrap">
        <label class="field-label">Título</label>
        <input class="field-input" type="text" id="titulo" name="titulo" placeholder="Descreva brevemente o problema" required>
        <span class="field-error" id="err-titulo"></span>
      </div>
      <div class="field-row">
        <div class="field-wrap">
          <label class="field-label">Categoria</label>
          <select class="field-select" name="categoria">
            <?php foreach(['tecnico'=>'Técnico','conta'=>'Conta','fitness'=>'Fitness','match'=>'Match','outro'=>'Outro'] as $v=>$l): ?>
            <option value="<?= $v ?>"><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-wrap">
          <label class="field-label">Prioridade</label>
          <select class="field-select" name="prioridade">
            <?php foreach(['baixa'=>'Baixa','media'=>'Média','alta'=>'Alta','critica'=>'Crítica'] as $v=>$l): ?>
            <option value="<?= $v ?>"><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field-wrap">
        <label class="field-label">Descrição</label>
        <textarea class="field-textarea" id="descricao" name="descricao" placeholder="Descreva o problema detalhadamente..." required></textarea>
        <span class="field-error" id="err-descricao"></span>
      </div>
      <button class="btn-primary" type="submit"><span class="btn-label">Enviar Chamado</span><div class="spinner"></div></button>
    </form>
  </div>

  <?php if (!empty($chamados)): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Título</th><th>Categoria</th><th>Prioridade</th><th>Status</th><th>Resposta</th><th>Data</th><th></th></tr></thead>
      <tbody>
      <?php foreach($chamados as $c): ?>
      <tr>
        <td><?= h($c['titulo']) ?></td>
        <td><?= ucfirst($c['categoria']) ?></td>
        <td><span class="badge badge-<?= $c['prioridade'] ?>"><?= ucfirst($c['prioridade']) ?></span></td>
        <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst(str_replace('_',' ',$c['status'])) ?></span></td>
        <td style="max-width:200px;font-size:.82rem"><?= $c['resposta'] ? h(substr($c['resposta'],0,80)).'...' : '<span style="color:var(--muted)">Aguardando</span>' ?></td>
        <td><?= date('d/m/Y', strtotime($c['criado_em'])) ?></td>
        <td>
          <?php if(!in_array($c['status'],['fechado','resolvido'])): ?>
          <form method="POST"><input type="hidden" name="acao" value="fechar_chamado"><input type="hidden" name="chamado_id" value="<?= $c['id'] ?>"><button class="btn-secondary" style="padding:5px 10px;font-size:.78rem">Encerrar</button></form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>
<script src="eterna_forma.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

// Filtro por estado
$filtro = $_GET['estado'] ?? 'todos';

$where = '';
$params = [];
if ($filtro !== 'todos') {
    $where = 'WHERE estado = ?';
    $params[] = $filtro;
}

$stmt = $pdo->prepare("SELECT * FROM pedidos $where ORDER BY fecha_creacion DESC");
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Contadores
$stmtCount = $pdo->query("SELECT estado, COUNT(*) as c FROM pedidos GROUP BY estado");
$contadores = ['solicitado'=>0,'pagado'=>0,'no_pagado'=>0,'todos'=>0];
foreach($stmtCount as $row){
    $contadores[$row['estado']] = $row['c'];
    $contadores['todos'] += $row['c'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Admin – SurtiPaez</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{--verde:#2d6a2d;--verde-claro:#4caf50;--amarillo:#f9c22e;--naranja:#e8650a;--cafe:#5c3a1e;--crema:#fdf6e3;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Nunito',sans-serif;background:#f0f4f0;min-height:100vh;}

    /* TOPBAR */
    .topbar{
      background:var(--verde);color:white;
      display:flex;align-items:center;justify-content:space-between;
      padding:16px 32px;box-shadow:0 2px 10px rgba(0,0,0,0.2);
    }
    .topbar-logo{font-family:'Playfair Display',serif;font-size:1.4rem;color:var(--amarillo);}
    .topbar-right{display:flex;align-items:center;gap:16px;}
    .topbar-user{font-size:0.9rem;opacity:0.85;}
    .logout-btn{
      background:rgba(255,255,255,0.15);color:white;
      border:1px solid rgba(255,255,255,0.3);border-radius:20px;
      padding:6px 16px;text-decoration:none;font-size:0.85rem;
      transition:background 0.2s;
    }
    .logout-btn:hover{background:rgba(255,255,255,0.25);}

    /* CONTENT */
    .content{max-width:1200px;margin:0 auto;padding:32px 24px;}

    /* STATS */
    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:32px;}
    .stat-card{
      background:white;border-radius:16px;padding:20px 24px;
      text-align:center;box-shadow:0 2px 12px rgba(0,0,0,0.06);
      cursor:pointer;transition:transform 0.2s,box-shadow 0.2s;
      text-decoration:none;display:block;
    }
    .stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.1);}
    .stat-card.active{border-2px solid var(--verde);box-shadow:0 0 0 3px var(--verde-claro);}
    .stat-number{font-size:2.5rem;font-weight:800;line-height:1;}
    .stat-label{font-size:0.85rem;color:#888;margin-top:4px;font-weight:600;}
    .stat-todos .stat-number{color:#555;}
    .stat-solicitado .stat-number{color:var(--naranja);}
    .stat-pagado .stat-number{color:var(--verde);}
    .stat-no_pagado .stat-number{color:#e53935;}

    /* GRID */
    .section-title{
      font-family:'Playfair Display',serif;
      color:var(--verde);font-size:1.6rem;margin-bottom:20px;
    }
    .pedidos-grid{
      display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
      gap:20px;
    }
    .pedido-card{
      background:white;border-radius:20px;
      padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.06);
      transition:transform 0.2s,box-shadow 0.2s;
      cursor:pointer;border-left:5px solid #ddd;
    }
    .pedido-card:hover{transform:translateY(-4px);box-shadow:0 8px 28px rgba(0,0,0,0.12);}
    .pedido-card.solicitado{border-left-color:var(--naranja);}
    .pedido-card.pagado{border-left-color:var(--verde);}
    .pedido-card.no_pagado{border-left-color:#e53935;}

    .pedido-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;}
    .pedido-nombre{font-weight:800;font-size:1.05rem;color:var(--cafe);}
    .pedido-celular{font-size:0.85rem;color:#888;margin-top:2px;}
    .badge{
      font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:50px;
      white-space:nowrap;
    }
    .badge.solicitado{background:#fff3e0;color:var(--naranja);}
    .badge.pagado{background:#e8f5e9;color:var(--verde);}
    .badge.no_pagado{background:#ffebee;color:#e53935;}

    .pedido-productos{margin:12px 0;}
    .prod-item{display:flex;justify-content:space-between;font-size:0.85rem;padding:4px 0;border-bottom:1px dashed #eee;}
    .prod-item:last-child{border:none;}
    .prod-name{color:#444;}
    .prod-cant{color:#888;}

    .pedido-footer{display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:12px;border-top:2px solid #f0f0f0;}
    .pedido-total{font-weight:800;color:var(--verde);font-size:1.05rem;}
    .pedido-fecha{font-size:0.78rem;color:#aaa;}
    .btn-ver{
      background:var(--verde);color:white;
      border:none;border-radius:10px;padding:8px 18px;
      font-family:'Nunito',sans-serif;font-weight:700;font-size:0.85rem;
      cursor:pointer;transition:background 0.2s;
    }
    .btn-ver:hover{background:#1a4a1a;}

    .empty-state{
      grid-column:1/-1;text-align:center;padding:60px 20px;
      color:#aaa;font-size:1.1rem;
    }
    .empty-state .big{font-size:4rem;margin-bottom:12px;}

    /* MODAL */
    .modal-overlay{
      position:fixed;inset:0;background:rgba(0,0,0,0.5);
      z-index:200;display:flex;align-items:center;justify-content:center;
      padding:16px;opacity:0;pointer-events:none;transition:opacity 0.3s;
    }
    .modal-overlay.open{opacity:1;pointer-events:all;}
    .modal{
      background:white;border-radius:24px;
      max-width:580px;width:100%;max-height:90vh;overflow-y:auto;
      padding:36px;box-shadow:0 30px 80px rgba(0,0,0,0.3);
      transform:scale(0.95);transition:transform 0.3s;
    }
    .modal-overlay.open .modal{transform:scale(1);}
    .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;}
    .modal-header h2{font-family:'Playfair Display',serif;color:var(--verde);font-size:1.5rem;}
    .modal-close{
      background:none;border:none;font-size:1.5rem;cursor:pointer;
      color:#aaa;line-height:1;transition:color 0.2s;
    }
    .modal-close:hover{color:#333;}

    .modal-info{background:#f8f9fa;border-radius:12px;padding:16px 20px;margin-bottom:20px;}
    .modal-info p{font-size:0.9rem;color:#444;margin-bottom:4px;}
    .modal-info strong{color:var(--cafe);}

    .prod-table{width:100%;border-collapse:collapse;margin-bottom:20px;}
    .prod-table th{text-align:left;font-size:0.8rem;color:#888;font-weight:700;padding:8px;border-bottom:2px solid #eee;text-transform:uppercase;letter-spacing:0.5px;}
    .prod-table td{padding:10px 8px;border-bottom:1px solid #f0f0f0;font-size:0.9rem;}
    .prod-table td:last-child{text-align:right;}
    .price-input{
      border:2px solid #ddd;border-radius:8px;
      padding:6px 10px;width:100px;font-family:'Nunito',sans-serif;
      font-size:0.9rem;text-align:right;outline:none;
      transition:border-color 0.2s;
    }
    .price-input:focus{border-color:var(--verde-claro);}
    .subtotal-cell{font-weight:700;color:var(--cafe);}

    .total-row{
      display:flex;justify-content:space-between;align-items:center;
      background:var(--verde);color:white;
      border-radius:12px;padding:14px 20px;margin-bottom:24px;
      font-size:1.1rem;font-weight:800;
    }

    .estado-select{
      border:2px solid #ddd;border-radius:12px;
      padding:10px 16px;font-family:'Nunito',sans-serif;font-size:0.95rem;
      outline:none;flex:1;transition:border-color 0.2s;
    }
    .estado-select:focus{border-color:var(--verde-claro);}
    .modal-actions{display:flex;gap:12px;flex-wrap:wrap;}
    .btn-guardar{
      background:linear-gradient(135deg,var(--amarillo),var(--naranja));
      color:var(--cafe);border:none;border-radius:50px;
      padding:12px 28px;font-family:'Nunito',sans-serif;
      font-weight:800;font-size:0.95rem;cursor:pointer;
      transition:transform 0.2s,box-shadow 0.2s;
      box-shadow:0 4px 16px rgba(233,101,10,0.3);
    }
    .btn-guardar:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(233,101,10,0.4);}
    .save-msg{
      font-size:0.85rem;color:var(--verde);font-weight:700;
      display:none;align-self:center;
    }
    .save-msg.show{display:block;}
  </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-logo">🌿 SurtiPaez — Panel</div>
  <div class="topbar-right">
    <span class="topbar-user">👤 <?= htmlspecialchars($_SESSION['admin_nombre']) ?></span>
    <a href="logout.php" class="logout-btn">Cerrar sesión</a>
  </div>
</div>

<!-- CONTENT -->
<div class="content">
  <!-- STATS -->
  <div class="stats">
    <?php
    $estados = ['todos'=>['🗂️','Todos','stat-todos'],
                'solicitado'=>['⏳','Solicitados','stat-solicitado'],
                'pagado'=>['✅','Pagados','stat-pagado'],
                'no_pagado'=>['❌','No Pagados','stat-no_pagado']];
    foreach($estados as $k=>[$icon,$label,$cls]):
    ?>
    <a href="?estado=<?=$k?>" class="stat-card <?=$cls?> <?=($filtro===$k?'active':'')?>">
      <div class="stat-number"><?=$contadores[$k]?></div>
      <div class="stat-label"><?=$icon?> <?=$label?></div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- GRID -->
  <h2 class="section-title">
    <?= $filtro==='todos' ? 'Todos los Pedidos' : 'Pedidos: '.ucfirst(str_replace('_',' ',$filtro)) ?>
  </h2>

  <div class="pedidos-grid">
    <?php if(empty($pedidos)): ?>
      <div class="empty-state">
        <div class="big">📭</div>
        <p>No hay pedidos en esta categoría.</p>
      </div>
    <?php else: ?>
      <?php foreach($pedidos as $p):
        $prods = json_decode($p['productos'], true) ?: [];
        $fecha = date('d/m/Y H:i', strtotime($p['fecha_creacion']));
      ?>
      <div class="pedido-card <?= $p['estado'] ?>" onclick="abrirModal(<?= $p['id'] ?>)">
        <div class="pedido-header">
          <div>
            <div class="pedido-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
            <div class="pedido-celular">📱 <?= htmlspecialchars($p['celular']) ?></div>
          </div>
          <span class="badge <?= $p['estado'] ?>">
            <?= $p['estado']==='solicitado'?'⏳ Solicitado':($p['estado']==='pagado'?'✅ Pagado':'❌ No Pagado') ?>
          </span>
        </div>
        <div class="pedido-productos">
          <?php foreach(array_slice($prods,0,3) as $prod): ?>
            <div class="prod-item">
              <span class="prod-name"><?= htmlspecialchars($prod['nombre'] ?? '') ?></span>
              <span class="prod-cant">x<?= $prod['cantidad'] ?? 0 ?></span>
            </div>
          <?php endforeach; ?>
          <?php if(count($prods)>3): ?>
            <div class="prod-item" style="color:#aaa;font-size:0.8rem;">+<?=count($prods)-3?> más...</div>
          <?php endif; ?>
        </div>
        <div class="pedido-footer">
          <span class="pedido-total">
            <?= $p['total']>0 ? '$'.number_format($p['total'],0,',','.') : 'Sin precio' ?>
          </span>
          <div style="display:flex;align-items:center;gap:10px;">
            <span class="pedido-fecha"><?= $fecha ?></span>
            <button class="btn-ver" onclick="event.stopPropagation();abrirModal(<?= $p['id'] ?>)">Ver →</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="cerrarModal(event)">
  <div class="modal" id="modal">
    <div class="modal-header">
      <h2 id="modalTitle">Detalle del Pedido</h2>
      <button class="modal-close" onclick="cerrarModal()">✕</button>
    </div>
    <div id="modalContent">Cargando...</div>
  </div>
</div>

<!-- DATA JSON para JS -->
<script>
const PEDIDOS = <?= json_encode(array_map(function($p){
    $p['productos_arr'] = json_decode($p['productos'], true) ?: [];
    return $p;
}, $pedidos), JSON_UNESCAPED_UNICODE) ?>;

function abrirModal(id){
    const p = PEDIDOS.find(x => x.id == id);
    if(!p) return;
    const prods = p.productos_arr;
    document.getElementById('modalTitle').textContent = '📦 Pedido de '+p.nombre;

    let rows = prods.map((pr,i) => `
      <tr>
        <td>${escHtml(pr.nombre||'')}</td>
        <td style="text-align:center">${pr.cantidad||0}</td>
        <td><input class="price-input" type="number" id="precio_${p.id}_${i}" 
                   value="${pr.precio||0}" min="0" oninput="calcTotal(${p.id})" placeholder="0"></td>
        <td class="subtotal-cell" id="sub_${p.id}_${i}">
          $${fmt((pr.precio||0)*(pr.cantidad||0))}
        </td>
      </tr>`).join('');

    document.getElementById('modalContent').innerHTML = `
      <div class="modal-info">
        <p><strong>Cliente:</strong> ${escHtml(p.nombre)}</p>
        <p><strong>Celular:</strong> ${escHtml(p.celular)}</p>
        ${p.ciudad?`<p><strong>Ciudad:</strong> ${escHtml(p.ciudad)}</p>`:''}
        <p><strong>Fecha:</strong> ${p.fecha_creacion}</p>
      </div>

      <table class="prod-table">
        <thead><tr><th>Producto</th><th>Cant.</th><th>Precio unit. ($)</th><th>Subtotal</th></tr></thead>
        <tbody>${rows}</tbody>
      </table>

      <div class="total-row">
        <span>💰 TOTAL</span>
        <span id="totalDisplay_${p.id}">$${fmt(p.total||0)}</span>
      </div>

      <div class="modal-actions">
        <select class="estado-select" id="estado_${p.id}">
          <option value="solicitado" ${p.estado==='solicitado'?'selected':''}>⏳ Solicitado</option>
          <option value="pagado" ${p.estado==='pagado'?'selected':''}>✅ Pagado</option>
          <option value="no_pagado" ${p.estado==='no_pagado'?'selected':''}>❌ No Pagado</option>
        </select>
        <button class="btn-guardar" onclick="guardar(${p.id})">💾 Guardar</button>
        <span class="save-msg" id="saveMsg_${p.id}">✔ Guardado!</span>
      </div>`;

    document.getElementById('modalOverlay').classList.add('open');
}

function calcTotal(id){
    const p = PEDIDOS.find(x=>x.id==id);
    let total=0;
    p.productos_arr.forEach((pr,i)=>{
        const precio = parseFloat(document.getElementById(`precio_${id}_${i}`)?.value)||0;
        const sub = precio*(pr.cantidad||0);
        const el = document.getElementById(`sub_${id}_${i}`);
        if(el) el.textContent = '$'+fmt(sub);
        total+=sub;
    });
    const el = document.getElementById(`totalDisplay_${id}`);
    if(el) el.textContent = '$'+fmt(total);
}

function guardar(id){
    const p = PEDIDOS.find(x=>x.id==id);
    const prods = p.productos_arr.map((pr,i)=>({
        nombre:pr.nombre, cantidad:pr.cantidad,
        precio: parseFloat(document.getElementById(`precio_${id}_${i}`)?.value)||0
    }));
    let total = prods.reduce((s,pr)=>s+pr.precio*pr.cantidad,0);
    const estado = document.getElementById(`estado_${id}`)?.value;

    fetch('actualizar_pedido.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id,productos:prods,total,estado})
    }).then(r=>r.json()).then(data=>{
        if(data.ok){
            const msg = document.getElementById(`saveMsg_${id}`);
            if(msg){msg.classList.add('show');setTimeout(()=>msg.classList.remove('show'),2500);}
            // actualizar localmente
            p.productos_arr = prods;
            p.total = total;
            p.estado = estado;
        }
    }).catch(()=>alert('Error al guardar'));
}

function cerrarModal(e){
    if(e && e.target !== document.getElementById('modalOverlay')) return;
    document.getElementById('modalOverlay').classList.remove('open');
}

function escHtml(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(n){return Number(n).toLocaleString('es-CO',{minimumFractionDigits:0,maximumFractionDigits:0});}

// Cerrar con Esc
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.getElementById('modalOverlay').classList.remove('open');});
</script>
</body>
</html>

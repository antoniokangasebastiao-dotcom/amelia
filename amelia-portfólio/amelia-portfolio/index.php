<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

$conn = getConnection();

// Buscar estatísticas
$stats = [];
if ($conn) {
    // Total de mensagens
    $stmt = $conn->query("SELECT COUNT(*) as total FROM mensagens_contato");
    $stats['mensagens'] = $stmt->fetch()['total'];
    
    // Mensagens não lidas
    $stmt = $conn->query("SELECT COUNT(*) as total FROM mensagens_contato WHERE lida = 0");
    $stats['nao_lidas'] = $stmt->fetch()['total'];
    
    // Total de solicitações
    $stmt = $conn->query("SELECT COUNT(*) as total FROM solicitacoes_servico");
    $stats['solicitacoes'] = $stmt->fetch()['total'];
    
    // Solicitações pendentes
    $stmt = $conn->query("SELECT COUNT(*) as total FROM solicitacoes_servico WHERE status = 'pendente'");
    $stats['pendentes'] = $stmt->fetch()['total'];
    
    // Solicitações em andamento
    $stmt = $conn->query("SELECT COUNT(*) as total FROM solicitacoes_servico WHERE status = 'em_andamento'");
    $stats['em_andamento'] = $stmt->fetch()['total'];
    
    // Solicitações concluídas
    $stmt = $conn->query("SELECT COUNT(*) as total FROM solicitacoes_servico WHERE status = 'concluido'");
    $stats['concluidos'] = $stmt->fetch()['total'];
}

// Buscar mensagens recentes
$mensagens = [];
if ($conn) {
    $stmt = $conn->query("SELECT * FROM mensagens_contato ORDER BY data_envio DESC LIMIT 10");
    $mensagens = $stmt->fetchAll();
}

// Buscar solicitações recentes
$solicitacoes = [];
if ($conn) {
    $stmt = $conn->query("SELECT * FROM solicitacoes_servico ORDER BY data_solicitacao DESC LIMIT 10");
    $solicitacoes = $stmt->fetchAll();
}

// Buscar últimos 30 dias de atividade
$ultimosMeses = [];
if ($conn) {
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(data_envio, '%Y-%m') as mes,
            COUNT(*) as total
        FROM mensagens_contato 
        WHERE data_envio >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_envio, '%Y-%m')
        ORDER BY mes DESC
    ");
    $ultimosMeses = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kumosi Impact Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0a1929;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        
        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #0a1929 0%, #1e3a5f 100%);
            min-height: 100vh;
            padding: 20px;
            position: fixed;
            width: 260px;
            border-right: 1px solid rgba(245, 124, 0, 0.2);
        }
        
        .sidebar .logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(245, 124, 0, 0.3);
            margin-bottom: 20px;
        }
        
        .sidebar .logo h3 {
            color: white;
        }
        
        .sidebar .logo span {
            color: #f57c00;
        }
        
        .sidebar .user-info {
            text-align: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .sidebar .user-info i {
            font-size: 3rem;
            color: #f57c00;
            margin-bottom: 10px;
        }
        
        .sidebar .nav-link {
            color: white;
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(245, 124, 0, 0.2);
            color: #f57c00;
        }
        
        .sidebar .nav-link.active {
            background: #f57c00;
            color: #0a1929;
        }
        
        .sidebar .nav-link i {
            width: 24px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        
        /* Stats Cards */
        .stat-card {
            background: rgba(30, 58, 95, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(245, 124, 0, 0.2);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #f57c00;
        }
        
        .stat-card i {
            font-size: 2rem;
            color: #f57c00;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        /* Tables */
        .table-custom {
            background: rgba(30, 58, 95, 0.3);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table-custom thead th {
            background: #f57c00;
            color: #0a1929;
            border: none;
            padding: 12px;
        }
        
        .table-custom tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .table-custom tbody tr:hover {
            background: rgba(245, 124, 0, 0.1);
        }
        
        .table-custom td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .badge-pendente { background: #ffc107; color: #333; }
        .badge-em_andamento { background: #17a2b8; color: white; }
        .badge-concluido { background: #28a745; color: white; }
        .badge-cancelado { background: #dc3545; color: white; }
        
        .btn-sm {
            padding: 5px 10px;
            border-radius: 8px;
            margin: 0 2px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h3><span>Kumosi</span> Admin</h3>
        </div>
        
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <p><strong><?= $_SESSION['admin_usuario'] ?? 'Admin' ?></strong></p>
            <small>Administrador</small>
        </div>
        
        <nav>
            <a href="#" class="nav-link active" onclick="showSection('dashboard'); return false;">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#" class="nav-link" onclick="showSection('mensagens'); return false;">
                <i class="fas fa-envelope"></i> Mensagens
                <?php if ($stats['nao_lidas'] > 0): ?>
                    <span class="badge bg-danger float-end"><?= $stats['nao_lidas'] ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="nav-link" onclick="showSection('solicitacoes'); return false;">
                <i class="fas fa-clipboard-list"></i> Solicitações
                <?php if ($stats['pendentes'] > 0): ?>
                    <span class="badge bg-warning float-end"><?= $stats['pendentes'] ?></span>
                <?php endif; ?>
            </a>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Section -->
        <div id="dashboard-section">
            <h2 class="mb-4">Dashboard</h2>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <i class="fas fa-envelope"></i>
                        <h3><?= $stats['mensagens'] ?></h3>
                        <p>Total de Mensagens</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <i class="fas fa-envelope-open"></i>
                        <h3><?= $stats['nao_lidas'] ?></h3>
                        <p>Não Lidas</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <i class="fas fa-clipboard-list"></i>
                        <h3><?= $stats['solicitacoes'] ?></h3>
                        <p>Solicitações</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <i class="fas fa-clock"></i>
                        <h3><?= $stats['pendentes'] ?></h3>
                        <p>Pendentes</p>
                    </div>
                </div>
            </div>
            
            <!-- Últimas Mensagens -->
            <div class="card bg-transparent border-0 mb-5">
                <div class="card-header bg-transparent border-bottom border-warning mb-3">
                    <h4 class="text-warning mb-0">Últimas Mensagens</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Assunto</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mensagens)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhuma mensagem encontrada</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($mensagens as $msg): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($msg['data_envio'])) ?></td>
                                        <td><?= htmlspecialchars($msg['nome']) ?></td>
                                        <td><?= htmlspecialchars($msg['email']) ?></td>
                                        <td><?= htmlspecialchars($msg['assunto']) ?></td>
                                        <td>
                                            <span class="badge <?= $msg['lida'] ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $msg['lida'] ? 'Lida' : 'Não lida' ?>
                                            </span>
                                         </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="verMensagem(<?= $msg['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="excluirMensagem(<?= $msg['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                         </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Últimas Solicitações -->
            <div class="card bg-transparent border-0">
                <div class="card-header bg-transparent border-bottom border-warning mb-3">
                    <h4 class="text-warning mb-0">Últimas Solicitações</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Serviço</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($solicitacoes)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhuma solicitação encontrada</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($solicitacoes as $sol): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($sol['data_solicitacao'])) ?></td>
                                        <td><?= htmlspecialchars($sol['nome']) ?></td>
                                        <td><?= htmlspecialchars($sol['email']) ?></td>
                                        <td><?= htmlspecialchars($sol['servico']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $sol['status'] ?>">
                                                <?= str_replace('_', ' ', $sol['status']) ?>
                                            </span>
                                         </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="verSolicitacao(<?= $sol['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <select class="form-select form-select-sm d-inline-block w-auto" 
                                                    onchange="atualizarStatus(<?= $sol['id'] ?>, this.value)">
                                                <option value="pendente" <?= $sol['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                                <option value="em_andamento" <?= $sol['status'] == 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                                                <option value="concluido" <?= $sol['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                                <option value="cancelado" <?= $sol['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                            </select>
                                         </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mensagens Section -->
        <div id="mensagens-section" style="display: none;">
            <h2 class="mb-4">Todas as Mensagens</h2>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Buscar todas as mensagens
                        $stmt = $conn->query("SELECT * FROM mensagens_contato ORDER BY data_envio DESC");
                        $todasMensagens = $stmt->fetchAll();
                        ?>
                        <?php foreach ($todasMensagens as $msg): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($msg['data_envio'])) ?></td>
                            <td><?= htmlspecialchars($msg['nome']) ?></td>
                            <td><?= htmlspecialchars($msg['email']) ?></td>
                            <td><?= htmlspecialchars($msg['telefone'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($msg['assunto']) ?></td>
                            <td>
                                <span class="badge <?= $msg['lida'] ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $msg['lida'] ? 'Lida' : 'Não lida' ?>
                                </span>
                             </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="verMensagem(<?= $msg['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="excluirMensagem(<?= $msg['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Solicitações Section -->
        <div id="solicitacoes-section" style="display: none;">
            <h2 class="mb-4">Todas as Solicitações</h2>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Serviço</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Buscar todas as solicitações
                        $stmt = $conn->query("SELECT * FROM solicitacoes_servico ORDER BY data_solicitacao DESC");
                        $todasSolicitacoes = $stmt->fetchAll();
                        ?>
                        <?php foreach ($todasSolicitacoes as $sol): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($sol['data_solicitacao'])) ?></td>
                            <td><?= htmlspecialchars($sol['nome']) ?></td>
                            <td><?= htmlspecialchars($sol['email']) ?></td>
                            <td><?= htmlspecialchars($sol['telefone'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($sol['servico']) ?></td>
                            <td>
                                <span class="badge badge-<?= $sol['status'] ?>">
                                    <?= str_replace('_', ' ', $sol['status']) ?>
                                </span>
                             </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="verSolicitacao(<?= $sol['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <select class="form-select form-select-sm d-inline-block w-auto" 
                                        onchange="atualizarStatus(<?= $sol['id'] ?>, this.value)">
                                    <option value="pendente" <?= $sol['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="em_andamento" <?= $sol['status'] == 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                                    <option value="concluido" <?= $sol['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                    <option value="cancelado" <?= $sol['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <button class="btn btn-sm btn-danger" onclick="excluirSolicitacao(<?= $sol['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal para detalhes -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: #0a1929; color: white;">
                <div class="modal-header" style="border-bottom-color: #f57c00;">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody"></div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(section) {
            document.getElementById('dashboard-section').style.display = 'none';
            document.getElementById('mensagens-section').style.display = 'none';
            document.getElementById('solicitacoes-section').style.display = 'none';
            
            document.getElementById(section + '-section').style.display = 'block';
            
            // Atualizar active class na sidebar
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.closest('.nav-link').classList.add('active');
        }
        
        function verMensagem(id) {
            fetch(`../get-mensagem.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').innerHTML = `Mensagem de ${data.nome}`;
                    document.getElementById('modalBody').innerHTML = `
                        <p><strong><i class="fas fa-user"></i> Nome:</strong> ${data.nome}</p>
                        <p><strong><i class="fas fa-envelope"></i> Email:</strong> ${data.email}</p>
                        <p><strong><i class="fas fa-phone"></i> Telefone:</strong> ${data.telefone || 'Não informado'}</p>
                        <p><strong><i class="fas fa-tag"></i> Assunto:</strong> ${data.assunto}</p>
                        <p><strong><i class="fas fa-calendar"></i> Data:</strong> ${data.data_envio}</p>
                        <hr>
                        <p><strong><i class="fas fa-comment"></i> Mensagem:</strong></p>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                            ${data.mensagem.replace(/\n/g, '<br>')}
                        </div>
                        <hr>
                        <p><strong><i class="fas fa-network-wired"></i> IP:</strong> ${data.ip_address || 'Não registrado'}</p>
                        <button class="btn btn-warning mt-3" onclick="window.location.href='mailto:${data.email}'">
                            <i class="fas fa-reply"></i> Responder por Email
                        </button>
                    `;
                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                });
        }
        
        function verSolicitacao(id) {
            fetch(`../get-solicitacao.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').innerHTML = `Solicitação - ${data.servico}`;
                    document.getElementById('modalBody').innerHTML = `
                        <p><strong><i class="fas fa-user"></i> Nome:</strong> ${data.nome}</p>
                        <p><strong><i class="fas fa-envelope"></i> Email:</strong> ${data.email}</p>
                        <p><strong><i class="fas fa-phone"></i> Telefone:</strong> ${data.telefone || 'Não informado'}</p>
                        <p><strong><i class="fas fa-briefcase"></i> Serviço:</strong> ${data.servico}</p>
                        <p><strong><i class="fas fa-coins"></i> Orçamento:</strong> ${data.orcamento || 'Não informado'}</p>
                        <p><strong><i class="fas fa-calendar"></i> Prazo:</strong> ${data.prazo || 'Não informado'}</p>
                        <p><strong><i class="fas fa-calendar-alt"></i> Data:</strong> ${data.data_solicitacao}</p>
                        <hr>
                        <p><strong><i class="fas fa-file-alt"></i> Descrição:</strong></p>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                            ${data.descricao.replace(/\n/g, '<br>')}
                        </div>
                        <hr>
                        <p><strong><i class="fas fa-tasks"></i> Status:</strong> 
                            <span class="badge badge-${data.status}">${data.status.replace('_', ' ')}</span>
                        </p>
                        <button class="btn btn-warning mt-3" onclick="window.location.href='mailto:${data.email}'">
                            <i class="fas fa-reply"></i> Responder por Email
                        </button>
                    `;
                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                });
        }
        
        function atualizarStatus(id, status) {
            fetch(`../atualizar-status.php?id=${id}&status=${status}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao atualizar status');
                    }
                });
        }
        
        function excluirMensagem(id) {
            if (confirm('Tem certeza que deseja excluir esta mensagem?')) {
                fetch(`../excluir.php?tipo=mensagem&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro ao excluir');
                        }
                    });
            }
        }
        
        function excluirSolicitacao(id) {
            if (confirm('Tem certeza que deseja excluir esta solicitação?')) {
                fetch(`../excluir.php?tipo=solicitacao&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro ao excluir');
                        }
                    });
            }
        }
    </script>
</body>
</html>
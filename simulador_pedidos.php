<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🍕 Simulador de Pedidos - App de Entrega</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍕 Simulador de Pedidos</h1>
            <p>Sistema de Gerenciamento - App de Entrega</p>
        </div>

        <div class="content">
            <!-- FORMULÁRIO -->
            <form method="POST" class="controls">
                <div class="input-group">
                    <label for="numPedidos">Número de Pedidos para Simular:</label>
                    <input type="number" id="numPedidos" name="numPedidos" value="15" min="1" max="100">
                </div>
                <button type="submit" class="btn" name="acao" value="simular">
                    🚀 Iniciar Simulação
                </button>
                <button type="submit" class="btn" name="acao" value="listar">
                    📋 Listar Pedidos
                </button>
                <button type="submit" class="btn" name="acao" value="limpar">
                    🗑️ Limpar Resultados
                </button>
            </form>

            <?php
            class SimuladorPedidos {
                private $conexao;
                
                public function __construct() {
                    $this->conectarBanco();
                }
                
                private function conectarBanco() {
                    $host = 'localhost';
                    $usuario = 'root';
                    $senha = '';
                    $banco = 'app_entrega';
                    
                    try {
                        $this->conexao = new PDO("mysql:host=$host;dbname=$banco", $usuario, $senha);
                        $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch(PDOException $e) {
                        die("❌ Erro na conexão: " . $e->getMessage());
                    }
                }
                
                public function simularPedidos($numeroPedidos = 10) {
                    $logs = [];
                    $logs[] = "🚀 Iniciando simulação de $numeroPedidos pedidos...";
                    
                    for ($i = 1; $i <= $numeroPedidos; $i++) {
                        $logs[] = "📦 Processando pedido $i de $numeroPedidos...";
                        
                        // AGORA CRIA UM USUÁRIO NOVO PARA CADA PEDIDO
                        $idUsuario = $this->criarUsuarioAleatorio();
                        $produtos = $this->selecionarProdutosAleatorios();
                        $endereco = $this->gerarEnderecoAleatorio();
                        $formaPagamento = $this->gerarFormaPagamentoAleatoria();
                        
                        $idPedido = $this->criarPedido($idUsuario, $endereco, $formaPagamento, $produtos);
                        
                        if ($idPedido) {
                            $logs[] = "✅ Pedido #$idPedido criado com sucesso para usuário #$idUsuario!";
                        } else {
                            $logs[] = "❌ Erro ao criar pedido $i";
                        }
                    }
                    
                    return $logs;
                }
                
                // NOVO MÉTODO: CRIAR USUÁRIO ALEATÓRIO
                private function criarUsuarioAleatorio() {
                    $nomes = ['João', 'Maria', 'Pedro', 'Ana', 'Carlos', 'Juliana', 'Fernando', 'Patricia', 'Ricardo', 'Amanda'];
                    $sobrenomes = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Costa'];
                    $domains = ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'icloud.com'];
                    
                    $nome = $nomes[array_rand($nomes)] . ' ' . $sobrenomes[array_rand($sobrenomes)];
                    $email = strtolower($nomes[array_rand($nomes)]) . '.' . strtolower($sobrenomes[array_rand($sobrenomes)]) . rand(1, 999) . '@' . $domains[array_rand($domains)];
                    $telefone = '(' . rand(11, 99) . ') ' . rand(90000, 99999) . '-' . rand(1000, 9999);
                    $endereco = $this->gerarEnderecoAleatorio();
                    
                    try {
                        $query = "INSERT INTO usuarios (nome, email, telefone, endereco) 
                                 VALUES (:nome, :email, :telefone, :endereco)";
                        
                        $stmt = $this->conexao->prepare($query);
                        $stmt->execute([
                            ':nome' => $nome,
                            ':email' => $email,
                            ':telefone' => $telefone,
                            ':endereco' => $endereco
                        ]);
                        
                        return $this->conexao->lastInsertId();
                        
                    } catch(PDOException $e) {
                        // Se der erro (email duplicado), tenta com um email diferente
                        $email = strtolower($nomes[array_rand($nomes)]) . '.' . strtolower($sobrenomes[array_rand($sobrenomes)]) . rand(1000, 9999) . '@' . $domains[array_rand($domains)];
                        
                        $stmt->execute([
                            ':nome' => $nome,
                            ':email' => $email,
                            ':telefone' => $telefone,
                            ':endereco' => $endereco
                        ]);
                        
                        return $this->conexao->lastInsertId();
                    }
                }
                
                private function selecionarProdutosAleatorios() {
                    $produtos = [];
                    $query = $this->conexao->query("SELECT id_produto, preco FROM produtos WHERE disponivel = TRUE");
                    $produtosDisponiveis = $query->fetchAll(PDO::FETCH_ASSOC);
                    
                    $numProdutos = rand(1, 3);
                    shuffle($produtosDisponiveis);
                    
                    for ($j = 0; $j < $numProdutos; $j++) {
                        if (isset($produtosDisponiveis[$j])) {
                            $produto = $produtosDisponiveis[$j];
                            $produtos[] = [
                                'id_produto' => $produto['id_produto'],
                                'quantidade' => rand(1, 3),
                                'preco' => $produto['preco']
                            ];
                        }
                    }
                    
                    return $produtos;
                }
                
                private function criarPedido($idUsuario, $endereco, $formaPagamento, $produtos) {
                    try {
                        $this->conexao->beginTransaction();
                        
                        $total = 0;
                        foreach ($produtos as $produto) {
                            $total += $produto['preco'] * $produto['quantidade'];
                        }
                        
                        $queryPedido = "INSERT INTO pedidos (id_usuario, total_pedido, endereco_entrega, forma_pagamento, status) 
                                       VALUES (:id_usuario, :total, :endereco, :pagamento, 'pendente')";
                        
                        $stmtPedido = $this->conexao->prepare($queryPedido);
                        $stmtPedido->execute([
                            ':id_usuario' => $idUsuario,
                            ':total' => $total,
                            ':endereco' => $endereco,
                            ':pagamento' => $formaPagamento
                        ]);
                        
                        $idPedido = $this->conexao->lastInsertId();
                        
                        foreach ($produtos as $produto) {
                            $queryItem = "INSERT INTO pedido_itens (id_pedido, id_produto, quantidade, preco_unitario) 
                                         VALUES (:id_pedido, :id_produto, :quantidade, :preco)";
                            
                            $stmtItem = $this->conexao->prepare($queryItem);
                            $stmtItem->execute([
                                ':id_pedido' => $idPedido,
                                ':id_produto' => $produto['id_produto'],
                                ':quantidade' => $produto['quantidade'],
                                ':preco' => $produto['preco']
                            ]);
                        }
                        
                        $this->conexao->commit();
                        return $idPedido;
                        
                    } catch(PDOException $e) {
                        $this->conexao->rollBack();
                        return false;
                    }
                }
                
                private function gerarEnderecoAleatorio() {
                    $ruas = ['Rua das Flores', 'Avenida Central', 'Rua do Comércio', 'Alameda dos Anjos', 'Rua das Palmeiras', 'Avenida Paulista', 'Rua Augusta', 'Alameda Santos'];
                    $numeros = ['123', '456', '789', '100', '200', '300', '45A', '67B', '89C', '150'];
                    $bairros = ['Centro', 'Jardim Paulista', 'Vila Madalena', 'Moema', 'Pinheiros', 'Itaim Bibi', 'Perdizes', 'Brooklin'];
                    
                    $rua = $ruas[array_rand($ruas)];
                    $numero = $numeros[array_rand($numeros)];
                    $bairro = $bairros[array_rand($bairros)];
                    
                    return "$rua, $numero - $bairro - São Paulo/SP";
                }
                
                private function gerarFormaPagamentoAleatoria() {
                    $formas = ['Cartão de Crédito', 'Cartão de Débito', 'Dinheiro', 'PIX'];
                    return $formas[array_rand($formas)];
                }
                
                public function obterEstatisticas() {
                    $estatisticas = [];
                    
                    $queryTotal = $this->conexao->query("SELECT COUNT(*) as total FROM pedidos");
                    $estatisticas['total_pedidos'] = $queryTotal->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $queryUsuarios = $this->conexao->query("SELECT COUNT(*) as total FROM usuarios");
                    $estatisticas['total_usuarios'] = $queryUsuarios->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $queryStatus = $this->conexao->query("SELECT status, COUNT(*) as quantidade FROM pedidos GROUP BY status");
                    $estatisticas['status'] = $queryStatus->fetchAll(PDO::FETCH_ASSOC);
                    
                    $queryValor = $this->conexao->query("SELECT SUM(total_pedido) as total_vendido FROM pedidos");
                    $estatisticas['total_vendido'] = $queryValor->fetch(PDO::FETCH_ASSOC)['total_vendido'];
                    
                    return $estatisticas;
                }
                
                public function listarPedidos() {
                    $query = $this->conexao->query("
                        SELECT p.id_pedido, u.nome as cliente, u.email, u.telefone, p.data_pedido, p.status, p.total_pedido, p.endereco_entrega, p.forma_pagamento
                        FROM pedidos p
                        JOIN usuarios u ON p.id_usuario = u.id_usuario
                        ORDER BY p.data_pedido DESC
                        LIMIT 50
                    ");
                    return $query->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            // PROCESSAMENTO DAS AÇÕES
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $acao = $_POST['acao'] ?? '';
                $numPedidos = $_POST['numPedidos'] ?? 15;
                
                $simulador = new SimuladorPedidos();
                
                switch ($acao) {
                    case 'simular':
                        $logs = $simulador->simularPedidos($numPedidos);
                        $estatisticas = $simulador->obterEstatisticas();
                        $pedidos = $simulador->listarPedidos();
                        break;
                        
                    case 'listar':
                        $pedidos = $simulador->listarPedidos();
                        $estatisticas = $simulador->obterEstatisticas();
                        break;
                        
                    case 'limpar':
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit;
                        break;
                }
            }
            ?>

            <!-- ÁREA DE LOGS -->
            <div class="output">
                <?php if (isset($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <div class="log-entry <?php echo strpos($log, '✅') !== false ? 'success' : (strpos($log, '❌') !== false ? 'error' : 'info'); ?>">
                            <?php echo htmlspecialchars($log); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="log-entry info">
                        👋 Bem-vindo ao Simulador de Pedidos! Clique em "Iniciar Simulação" para começar.
                    </div>
                <?php endif; ?>
            </div>

            <!-- ESTATÍSTICAS -->
            <?php if (isset($estatisticas)): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $estatisticas['total_pedidos']; ?></span>
                    <span class="stat-label">Total de Pedidos</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $estatisticas['total_usuarios']; ?></span>
                    <span class="stat-label">Total de Usuários</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">R$ <?php echo number_format($estatisticas['total_vendido'], 2, ',', '.'); ?></span>
                    <span class="stat-label">Valor Total Vendido</span>
                </div>
                <?php foreach ($estatisticas['status'] as $status): ?>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $status['quantidade']; ?></span>
                    <span class="stat-label"><?php echo ucfirst($status['status']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- LISTA FINAL DE PEDIDOS PROCESSADOS -->
            <?php if (isset($pedidos) && !empty($pedidos)): ?>
            <div class="pedidos-list">
                <h3>📋 LISTA FINAL DE PEDIDOS PROCESSADOS</h3>
                
                <div class="resumo-execucao">
                    <strong>✅ SIMULAÇÃO CONCLUÍDA - <?php echo count($pedidos); ?> PEDIDOS PROCESSADOS</strong>
                </div>
                
                <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-item status-<?php echo $pedido['status']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong>Pedido #<?php echo $pedido['id_pedido']; ?></strong>
                        <span style="background: #3498db; color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;">
                            <?php echo strtoupper($pedido['status']); ?>
                        </span>
                    </div>
                    <div style="margin-top: 8px;">
                        <small>👤 <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente']); ?></small><br>
                        <small>📧 <strong>Email:</strong> <?php echo htmlspecialchars($pedido['email']); ?></small><br>
                        <small>📞 <strong>Telefone:</strong> <?php echo htmlspecialchars($pedido['telefone']); ?></small><br>
                        <small>💰 <strong>Total:</strong> R$ <?php echo number_format($pedido['total_pedido'], 2, ',', '.'); ?></small><br>
                        <small>🏠 <strong>Endereço:</strong> <?php echo htmlspecialchars($pedido['endereco_entrega']); ?></small><br>
                        <small>💳 <strong>Pagamento:</strong> <?php echo htmlspecialchars($pedido['forma_pagamento']); ?></small><br>
                        <small>📅 <strong>Data:</strong> <?php echo $pedido['data_pedido']; ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- RESUMO FINAL -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: center;">
                    <h4>📊 RESUMO DA EXECUÇÃO</h4>
                    <p><strong>Total de pedidos processados:</strong> <?php echo count($pedidos); ?></p>
                    <p><strong>Total de usuários cadastrados:</strong> <?php echo isset($estatisticas) ? $estatisticas['total_usuarios'] : '0'; ?></p>
                    <p><strong>Data/hora da simulação:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

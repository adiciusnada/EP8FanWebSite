<?php
// --- LÓGICA PHP DO PAINEL ---
// Coloque esta parte no topo do arquivo.
    // --- 1. CONFIGURAÇÃO DO BANCO DE DADOS ---
    $host = 'localhost';
    $port = '5432';
    $dbname = 'alef';
    $user = 'adicius';
    $password = '131192Nada';

    if(!empty($_GET['usernamecoins'])){
        $username = $_GET['usernamecoins'];
        // --- 2. CONEXÃO COM O BANCO DE DADOS ---
        $conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
        $conn = pg_connect($conn_string);
        if (!$conn) {
            die("Erro: Não foi possível conectar ao banco de dados PostgreSQL.");
        }

        // --- 3. EXECUÇÃO DA CONSULTA ---
        // Assumindo que a coluna com os dados binários se chama 'data'
        $sql = "SELECT data FROM public.accounts WHERE username = '$username'";
        $result = pg_query($conn, $sql);

        if (!$result) {
            echo "Ocorreu um erro na consulta.\n";
            pg_close($conn); // Fecha a conexão
            exit;
        }

        // --- 4. EXTRAÇÃO E DECODIFICAÇÃO DOS DADOS ---
        // Verifica se a consulta retornou algum resultado
        if (pg_num_rows($result) > 0) {
            // Pega a primeira linha do resultado
            $row = pg_fetch_assoc($result);

            // O tipo bytea do PostgreSQL retorna uma string no formato '\x[dados_hex]'.
            // Primeiro, pegamos o valor bruto da coluna 'data'.
            $raw_bytea_data = $row['data'];

            // Removemos o prefixo '\x' do início para ter apenas os dados hexadecimais.
            $hex_data_from_db = ltrim($raw_bytea_data, '\\x');

            // Agora, usamos a mesma lógica de antes:
            // a. Converter a string hexadecimal para binário bruto.
            $binary_data = hex2bin($hex_data_from_db);

            // b. Definir o formato para extrair APENAS os campos de email e moedas.
            $format_string = '@64/A128email/@208/Lcoins';

            // c. Extrair os dados.
            $data = unpack($format_string, $binary_data);

            // d. Limpar o email e atribuir os valores.
            $email = trim($data['email']);
            $coins = $data['coins'];

        } else {
            echo "<p>Usuário 'adicius2' não encontrado no banco de dados.</p>";
        }

        // --- 6. FECHAR A CONEXÃO COM O BANCO DE DADOS ---
        pg_close($conn);
    }else{
        $email = "Nenhum usuario selecionado";
        $coins = "Nenhum usuario selecionado";
    }

// Incluímos a função sendCommand que já usamos antes.
// Adapte o IP e a Porta se necessário.
function sendCommand($command) {
    $host = '177.129.176.49'; // seu IP
    $port = 11010;           // sua porta
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if (!$fp) {
        return "Erro de conexão com o servidor do jogo.";
    }
    stream_set_timeout($fp, 2);
    fwrite($fp, $command . "\x00");
    $response = fread($fp, 2048);
    fclose($fp);
    return trim(str_replace("\x00", '', $response));
}

$msg = null; // Inicializa a variável de mensagem

// Adiciona moeda
if (isset($_POST['add_balance'])) {
    $balance = 100000;
    $usernamecoins = trim($_POST['usernamecoins']);

    // Validação para garantir que o username não está vazio
    if (!empty($usernamecoins)) {
        $resp = sendCommand("ADD_BALANCE:$usernamecoins:$balance\x00");
        
        // Sua lógica original tinha uma pequena inconsistência no redirect.
        // Simplifiquei para uma mensagem clara e um redirect.
        if ($resp == 'OK') {
            $msg = "Adicionado " . number_format($balance, 0, ',', '.') . " de coins para: " . htmlspecialchars($usernamecoins);
            // Redireciona para o painel do mesmo usuário após 2 segundos
            header("refresh:1;url=userpanel.php?usernamecoins=" . htmlspecialchars($usernamecoins));
        } else {
            $msg = "Erro ao adicionar coins: " . htmlspecialchars($resp);
        }
    } else {
        $msg = "O nome de usuário não pode estar vazio.";
    }
}
// Adiciona moeda
if (isset($_POST['login'])) {
    $usernamecoins = trim($_POST['usernamecoins']);
    // Validação para garantir que o username não está vazio
    if (!empty($usernamecoins)) {
            header("refresh:0;url=userpanel.php?usernamecoins=" . htmlspecialchars($usernamecoins));
    }
}

// Pega o nome de usuário da URL para personalização
$currentUser = !empty($_GET["usernamecoins"]) ? htmlspecialchars($_GET["usernamecoins"]) : '';

?>
<!DOCTYPE html>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archlord - Painel do Usuário</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
                <div class="logo">
                <a href="index.php">
                    <h1>Archlord</h1>
                </a>
                </div>
            <?php include "options.php"; ?>
        </div>
    </header>

    <main>
        <div class="panel-container">
            <h2>Painel do Usuário</h2>
            <p class="welcome-message">Bem-vindo, Lorde <?php echo $currentUser; ?>.</p>
            <?php if(empty($currentUser)){ ?>
                <div class="user-info">
                    <h3>Não tem cadastro?</h3>
                    <a href="register.php" class="btn">Junte-se à Batalha</a>
                </div>
            <?php } ?>
            <form method="POST">
            <div class="user-info">
                <h3>Suas Informações</h3>
                <?php if(!empty($_GET['usernamecoins'])){ ?>
                    <p><strong>Usuário:</strong> <?php echo $currentUser; ?></p>
                    <input type="hidden" name="usernamecoins" value="<?php echo $currentUser; ?>">
                <?php }else{ ?>
                    <div class="form-group">
                        <label for="char-name">Digite sua conta</label>
                            <input 
                                type="text" 
                                id="char-name" 
                                name="usernamecoins" 
                                class="archlord-input" 
                                placeholder="Seu Usuario"
                            >
                    </div>
                <?php } ?>
                <p><strong>Coins Atuais:</strong> <?php echo htmlspecialchars($coins); ?></p>

            </div>

            <div class="action-section">
                <h3>Adicionar Coins (Créditos)</h3>
                
                <?php if ($msg): ?>
                    <div class="message">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>
                    <p>Clique no botão abaixo para adicionar 100.000 coins à sua conta.</p>
                    <?php if(!empty($currentUser)){ ?>
                        <button type="submit" name="add_balance" class="btn">Adicionar 100.000 Coins</button>
                    <?php }else{ ?>
                        <button type="submit" name="login" class="btn">Entrar</button>
                    <?php } ?>
                    
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Crônicas de Archlord. Inspirado no universo criado pela Webzen.</p>
        </div>
    </footer>
</body>
</html>
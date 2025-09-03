<?php
// SEU CÓDIGO PHP VAI AQUI NO TOPO
// Isso garante que o processamento do formulário e possíveis redirecionamentos
// ocorram antes de qualquer HTML ser renderizado.

$msg = null; // Inicializa a variável de mensagem

function sendCommand($command) {
    $host = '177.129.176.49'; // seu IP
    $port = 11010;           // sua porta

    // O '@' suprime erros de conexão nativos do PHP, permitindo que a gente lide com eles
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if (!$fp) {
        // Retorna uma mensagem de erro mais amigável
        return "Erro de conexão com o servidor do jogo. Tente novamente mais tarde.";
    }

    stream_set_timeout($fp, 2);
    fwrite($fp, $command . "\x00");
    $response = fread($fp, 2048);
    fclose($fp);

    return trim(str_replace("\x00", '', $response));
}

// Verifica se o formulário foi enviado
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email    = trim($_POST['email']);

    // Validação simples para evitar campos vazios
    if (empty($username) || empty($password) || empty($email)) {
        $msg = "Todos os campos são obrigatórios.";
    } else {
        $check = sendCommand("CHECK_USERNAME:$username\x00");
        
        // A lógica do seu backend parece retornar 'OK' quando o usuário NÃO existe.
        if ($check != 'OK'){
            $msg = "Este nome de usuário já está em uso.";
        } else {
            // Nota: Sua lógica original para $add parecia invertida.
            // Eu a ajustei para o que parece ser o comportamento esperado:
            // SUCESSO quando o retorno é 'OK'.
            $add = sendCommand("ADD_ACCOUNT:$username:$password:$email\x00");
            
            if ($add == 'OK'){
                $msg = "Conta criada com sucesso! Você será redirecionado.";
                // O header() deve ser chamado antes de qualquer saída HTML.
                // É por isso que este bloco de código está no topo do arquivo.
                header("refresh:3;url=userpanel.php?usernamecoins=".$username);
            } else {
                // Se o comando ADD_ACCOUNT falhar por outro motivo
                $msg = "Ocorreu um erro ao criar a conta: " . htmlspecialchars($add);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archlord - Criar Conta</title>
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
        <div class="form-container">
            <h2>Crie sua Conta</h2>
            <p>Forje seu destino em Chantra. Junte-se à batalha pelo poder supremo.</p>

            <?php if ($msg): ?>
                <div class="message">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Nome de Usuário</label>
                    <input type="text" id="username" name="username" required placeholder="Seu nome de guerreiro">
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required placeholder="Seu e-mail para contato">
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres">
                </div>
                <button type="submit" name="register" class="btn">Registrar e Lutar</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Crônicas de Archlord. Inspirado no universo criado pela Webzen.</p>
        </div>
    </footer>
</body>
</html>
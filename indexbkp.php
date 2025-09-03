<?php
$usernamecoin = "";
if(!empty($_GET['user'])){
$usernamecoin = $_GET['user'];
}
function sendCommand($command) {
    $host = '177.129.176.49'; // seu IP
    $port = 11010;           // sua porta

    $fp = fsockopen($host, $port, $errno, $errstr, 5);
    if (!$fp) {
        return "Erro: $errstr ($errno)";
    }

    stream_set_timeout($fp, 2);

    // ? envia comando com terminador NUL real
    fwrite($fp, $command . "\x00");

    // Lê até 2 KB de resposta
    $response = fread($fp, 2048);
    fclose($fp);

    // Remove byte nulo final e espaços extras
    return trim(str_replace("\x00", '', $response));
}
//echo sendCommand("CHECK_USERNAME:adiciusnada\x00");

// Cadastro
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email    = trim($_POST['email']);

    $check = sendCommand("CHECK_USERNAME:$username\x00");
    if ($check !='OK'){
        $msg = "Usuário já existe.";
    } else {
        $add = sendCommand("ADD_ACCOUNT:$username:$password:$email\x00");
        if ($add !='OK'){
            $msg = "Conta criada com sucesso! Faça login.";
            header( "refresh:2;Location: index.php?user=".$username);
        } else {
            $msg = "$add";
            header( "refresh:2;Location: index.php?user=".$username);
        }
    }
}
//Adiciona moeda
if (isset($_POST['add_balance'])) {
    $balance = 100000;
    $usernamecoin = trim($_POST['usernamecoin']);
    $resp = sendCommand("ADD_BALANCE:$usernamecoin:$balance\x00");
    if ($resp != 'OK'){
        $msg = "Erro ao adicionar coins:".$resp;
    }else{
        $msg = "Adicionado ".$balance." de coins ao: ".$usernamecoin;
        header( "refresh:2;Location: index.php?user=".$usernamecoin);    
        if(!empty($_GET["user"])){
            $msg = "Adicionado mais ".$balance." de coins ao: ".$usernamecoin;
        }
    }
}
    //status dos castelos
    // --- 1. CONFIGURAÇÃO DO BANCO DE DADOS ---
    $host = 'localhost';
    $port = '5432';
    $dbname = 'alef';
    $user = 'adicius';
    $password = '131192Nada';

    // --- 2. CONEXÃO COM O BANCO DE DADOS ---
    $conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
    $conn = pg_connect($conn_string);
    if (!$conn) {
        die("Erro: Não foi possível conectar ao banco de dados PostgreSQL.");
    }

    // --- 3. EXECUÇÃO DA CONSULTA ---
    $sql = 'SELECT name, data FROM castles ORDER BY name ASC';
    $result = pg_query($conn, $sql);
    if (!$result) {
        echo "Ocorreu um erro na consulta.\n";
        exit;
    }

?>
<!DOCTYPE html>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>AdiCiuS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f9; color: #333; margin: 0; padding: 20px; }
        h1 { color: #4a4a4a; text-align: center; }
        table { width: 100%; max-width: 900px; margin: 20px auto; border-collapse: collapse; box-shadow: 0 2px 10px rgba(0,0,0,0.1); background-color: #fff; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        thead th { background-color: #5c67f2; color: #fff; font-weight: bold; }
        tbody tr:nth-of-type(even) { background-color: #f8f8f8; }
        tbody tr:hover { background-color: #e8eaf6; }
        .status-sim { color: #28a745; font-weight: bold; }
        .status-nao { color: #dc3545; font-weight: bold; }
        .no-owner { font-style: italic; color: #777; }
    </style>

<body class="bg-light">
<div class="container w-50">
    <div class="row">
        <div class="row-md-5">
            <?php if (!empty($msg)): ?>
                <div class="alert alert-info"><?= $msg ?></div>
            <?php endif; ?>
            <div class="card" >
                <div class="card-header bg-success text-white">Cadastrar</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="input-group flex-nowrap mb-3">
                                <span class="input-group-text" id="addon-wrapping">Username</span>
                                <input type="text" name="username" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="addon-wrapping" required>
                            </div>
                            <div class="input-group flex-nowrap mb-3">
                                <span class="input-group-text" id="addon-wrapping">Password</span>
                                <input type="password" name="password" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="addon-wrapping" required>
                            </div>
                            <div class="input-group flex-nowrap mb-3">
                                <span class="input-group-text" id="addon-wrapping">E-mail</span>
                                <input type="email" name="email" class="form-control" placeholder="email" aria-label="email" aria-describedby="addon-wrapping" required>
                            </div>
                            <button type="submit" name="register" class="btn btn-success">Cadastrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- moedas aos pobres -->
        <br>
        <div class="row-md-5">
            <div class="card">
                <div class="card-header bg-primary text-white">Chantra Coins (100k / click)</div>
                <div class="card-body">
                    <form method="post">
                            <div class="input-group flex-nowrap mb-3">
                                <span class="input-group-text" id="addon-wrapping">Username</span>
                                <input type="text" name="usernamecoin" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="addon-wrapping" <?php if(!empty($usernamecoin)){ echo "value = '$usernamecoin'";} ?> required>
                            </div>
                        <button type="submit" name="add_balance" class="btn btn-primary">
                            Add Coins
                        </button>
                    </form>
                </div>
            </div>
        </div>
            <h1>🏰 Status dos Castelos</h1>

            <table>
                <thead>
                    <tr>
                        <th>Nome do Castelo</th>
                        <th>Guilda Dominante</th>
                        <th>Taxa de Imposto</th>
                        <th>Ativo?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = pg_fetch_assoc($result)) {
                        echo "<tr><td>" . htmlspecialchars($row['name']) . "</td>";

                        if (is_null($row['data'])) {
                            echo "<td class='not-available' colspan='3'>Dados não definidos (NULL)</td>";
                        } else {
                            $binary_data = hex2bin(substr($row['data'], 2));
                            $data_length = strlen($binary_data);

                            // --- LÓGICA DEFINITIVA COM 3 CASOS ---

                            // CASO 1: Dados completos (Guilda, Taxa, Status)
                            if ($data_length >= 88) {
                                $guild_block = substr($binary_data, 32, 32);
                                $null_pos = strpos($guild_block, "\0");
                                $guild_name = ($null_pos !== false) ? substr($guild_block, 0, $null_pos) : $guild_block;
                                
                                $tax_rate = unpack("L", substr($binary_data, 64, 4))[1];
                                $is_active = (unpack("L", substr($binary_data, 84, 4))[1] == 1);

                                echo "<td>" . htmlspecialchars($guild_name) . "</td>";
                                echo "<td>" . htmlspecialchars($tax_rate) . "%</td>";
                                echo $is_active ? "<td class='status-sim'>Sim</td>" : "<td class='status-nao'>Não</td>";
                            
                            // CASO 2: Dados parciais (Guilda, Taxa, SEM Status) - O seu novo caso!
                            } else if ($data_length >= 68) {
                                $guild_block = substr($binary_data, 32, 32);
                                $null_pos = strpos($guild_block, "\0");
                                $guild_name = ($null_pos !== false) ? substr($guild_block, 0, $null_pos) : $guild_block;

                                $tax_rate = unpack("L", substr($binary_data, 64, 4))[1];
                                
                                echo "<td>" . htmlspecialchars($guild_name) . "</td>";
                                echo "<td>" . htmlspecialchars($tax_rate) . "%</td>";
                                echo "<td class='not-available'>N/A</td>"; // O status não existe nestes dados

                            // CASO 3: Castelo vago (SEM Guilda, SEM Taxa, COM Status)
                            } else if ($data_length >= 56) {
                                $is_active = (unpack("L", substr($binary_data, 52, 4))[1] == 1);
                                
                                echo "<td class='no-owner'>Sem Guilda</td>";
                                echo "<td class='not-available'>N/A</td>";
                                echo $is_active ? "<td class='status-sim'>Sim</td>" : "<td class='status-nao'>Não</td>";

                            // CASO 4: Dados insuficientes
                            } else {
                                echo "<td class='not-available' colspan='3'>Dados insuficientes para análise.</td>";
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php
        pg_free_result($result);
        pg_close($conn);
        ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>

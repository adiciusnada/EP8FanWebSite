<?php
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
<html lang="pt-br">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archlord - Crônicas de Chantra</title>
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
        <section class="hero">
            <div class="container">
                <h2>O Chamado ao Trono</h2>
                <p>O poder supremo de Chantra aguarda por um novo soberano. Você tem o que é preciso para se tornar o Archlord?</p>
                <a href="register.php" class="btn">Junte-se à Batalha</a>
            </div>
        </section>

        <section class="content">
            <div class="container">
                <div class="card">
                    <h3>Novas Incursões Descobertas</h3>
                    <p>Antigas masmorras foram reveladas nas terras desoladas do norte. Reúna seus aliados e desvende os mistérios e tesouros que aguardam os corajosos.</p>
                </div>
                <div class="card">
                    <h3>A Ascensão da Guilda Carmesim</h3>
                    <p>A Guilda Carmesim declarou suas intenções de conquistar o castelo central. As forças de Chantra se preparam para uma guerra de cerco sem precedentes.</p>
                </div>
                <div class="card">
                    <h3>O Equilíbrio das Classes</h3>
                    <p>Uma nova atualização trouxe mudanças significativas para as habilidades de Arqueiros e Magos. Adapte sua estratégia e domine o campo de batalha.</p>
                </div>
            </div>
        </section>
        
        <section class="castle-status">
            <div class="container">
                <h2>Domínio dos Castelos</h2>
                <?php if ($result && pg_num_rows($result) > 0): ?>
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
                                echo "<td class='not-available' colspan='3'>Dados não definidos</td>";
                            } else {
                                $binary_data = hex2bin(substr($row['data'], 2));
                                $data_length = strlen($binary_data);

                                if ($data_length >= 88) {
                                    $guild_block = substr($binary_data, 32, 32);
                                    $null_pos = strpos($guild_block, "\0");
                                    $guild_name = ($null_pos !== false) ? substr($guild_block, 0, $null_pos) : $guild_block;
                                    
                                    $tax_rate = unpack("L", substr($binary_data, 64, 4))[1];
                                    $is_active = (unpack("L", substr($binary_data, 84, 4))[1] == 1);

                                    echo "<td>" . htmlspecialchars($guild_name) . "</td>";
                                    echo "<td>" . htmlspecialchars($tax_rate) . "%</td>";
                                    echo $is_active ? "<td class='status-sim'>Sim</td>" : "<td class='status-nao'>Não</td>";
                                } else if ($data_length >= 68) {
                                    $guild_block = substr($binary_data, 32, 32);
                                    $null_pos = strpos($guild_block, "\0");
                                    $guild_name = ($null_pos !== false) ? substr($guild_block, 0, $null_pos) : $guild_block;
                                    $tax_rate = unpack("L", substr($binary_data, 64, 4))[1];
                                    
                                    echo "<td>" . htmlspecialchars($guild_name) . "</td>";
                                    echo "<td>" . htmlspecialchars($tax_rate) . "%</td>";
                                    echo "<td class='not-available'>N/A</td>";
                                } else if ($data_length >= 56) {
                                    $is_active = (unpack("L", substr($binary_data, 52, 4))[1] == 1);
                                    
                                    echo "<td class='no-owner'>Sem Guilda</td>";
                                    echo "<td class='not-available'>N/A</td>";
                                    echo $is_active ? "<td class='status-sim'>Sim</td>" : "<td class='status-nao'>Não</td>";
                                } else {
                                    echo "<td class='not-available' colspan='3'>Dados insuficientes</td>";
                                }
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>Não foi possível carregar o status dos castelos no momento.</p>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Crônicas de Archlord. Inspirado no universo criado pela Webzen.</p>
        </div>
        <?php
        // Libera os recursos e fecha a conexão
        if ($result) {
            pg_free_result($result);
        }
        if ($conn) {
            pg_close($conn);
        }
        ?>
    </footer>
</body>
</html>
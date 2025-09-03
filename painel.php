<?php

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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status dos Castelos</title>
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
</head>
<body>

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
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";

                if (is_null($row['data'])) {
                    echo "<td class='no-owner' colspan='3'>Dados não definidos (NULL)</td>";
                    echo "</tr>";
                    continue;
                }

                $hex_data = substr($row['data'], 2);
                $binary_data = hex2bin($hex_data);
                $data_length = strlen($binary_data);

                // --- LÓGICA ATUALIZADA E MAIS INTELIGENTE ---
                
                // CASO 1: Castelo COM dono (dados completos)
                if ($data_length >= 88) {
                    $guild_block = substr($binary_data, 32, 32);
                    $null_pos = strpos($guild_block, "\0");
                    $guild_name = ($null_pos !== false) ? substr($guild_block, 0, $null_pos) : $guild_block;

                    $tax_rate_data = unpack("L", substr($binary_data, 64, 4));
                    $tax_rate = $tax_rate_data[1];

                    $active_flag_data = unpack("L", substr($binary_data, 84, 4));
                    $is_active = ($active_flag_data[1] == 1);

                    echo "<td>" . htmlspecialchars($guild_name) . "</td>";
                    echo "<td>" . htmlspecialchars($tax_rate) . "%</td>";
                    echo $is_active ? "<td class='status-sim'>Sim</td>" : "<td class='status-nao'>Não</td>";

                // CASO 2: Castelo SEM dono, mas com dados de status
                } else if ($data_length >= 56) { 
                    // A nova posição do status seria 52 (84 - 32)
                    $active_flag_data = unpack("L", substr($binary_data, 52, 4));
                    $is_active = ($active_flag_data[1] == 1);
                    
                    echo "<td class='no-owner'>Sem Guilda</td>";
                    echo "<td class='no-owner'>N/A</td>";
                    echo $is_active ? "<td class='status-sim'>Sim</td>" : "<td class='status-nao'>Não</td>";

                // CASO 3: Castelo SEM dono e sem dados de status
                } else {
                    echo "<td class='no-owner'>Sem Guilda</td>";
                    echo "<td class='no-owner' colspan='2'>N/A</td>";
                }
                
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
pg_free_result($result);
pg_close($conn);
?>

<?php
/*
// Script de Investigação de Dados
$host = 'localhost'; $port = '5432'; $dbname = 'alef'; $user = 'adicius'; $password = '131192Nada';
$conn = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$password}");

if ($conn) {
    $sql = 'SELECT name, data FROM castles ORDER BY name ASC';
    $result = pg_query($conn, $sql);
}
?>
<!DOCTYPE html><html lang="pt-br"><head><title>Investigação de Castelos</title><style>body{font-family:sans-serif;padding:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:8px;text-align:left;}thead{background-color:#f0f0f0;}.hex{font-family:monospace;word-break:break-all;}</style></head>
<body>
    <h1>🕵️‍♂️ Investigação de Dados dos Castelos</h1>
    <table><thead><tr><th>Castelo</th><th>Tamanho em Bytes</th><th class="hex">Dados Hexadecimais</th></tr></thead><tbody>
    <?php
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            echo "<tr><td><b>" . htmlspecialchars($row['name']) . "</b></td>";
            if (is_null($row['data'])) {
                echo "<td colspan='2'>NULL</td>";
            } else {
                $hex_data = substr($row['data'], 2);
                $binary_data = hex2bin($hex_data);
                echo "<td>" . strlen($binary_data) . "</td>";
                echo "<td class='hex'>" . htmlspecialchars($hex_data) . "</td>";
            }
            echo "</tr>";
        }
    }
        
    ?>
    </tbody></table>
</body></html>
<?php

if ($conn) { pg_close($conn); } 

*/?>
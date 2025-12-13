<?php
header('Content-Type: application/json; charset=utf-8');

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
    echo json_encode(['status'=>'error','message'=>'Format JSON invalide']);
    exit;
}

$sender = trim($data['sender'] ?? '');
$recipient = trim($data['recipient'] ?? '');
$amount = floatval($data['amount'] ?? 0);
$note = trim($data['note'] ?? '');

if ($sender === '' || $recipient === '' || $amount <= 0) {
    echo json_encode(['status'=>'error','message'=>'Champs manquants']);
    exit;
}

// Destinataires autorisés
$allowedRecipients = ["0195810161", "0149981609"];
if (!in_array($recipient, $allowedRecipients)) {
    echo json_encode(['status'=>'error','message'=>'Destinataire non autorisé']);
    exit;
}

// Créer la transaction
$tx_id = "TX".date("YmdHis").rand(1000,9999);

$csv = __DIR__."/transactions.csv";
$exists = file_exists($csv);

$fp = fopen($csv, "a");
if (!$fp) {
    echo json_encode(['status'=>'error','message'=>'Impossible d\'écrire le fichier']);
    exit;
}

if (!$exists) {
    fputcsv($fp, ["id","timestamp","sender","recipient","amount","note"]);
}

fputcsv($fp, [
    $tx_id,
    date("c"),
    $sender,
    $recipient,
    number_format($amount,2,'.',''),
    $note
]);

fclose($fp);

echo json_encode([
    'status' => 'success',
    'tx_id' => $tx_id,
    'amount' => number_format($amount,2,'.','')
]);
exit;

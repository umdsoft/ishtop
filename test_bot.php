<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$bot = app(\SergiX44\Nutgram\Nutgram::class);

$updates = $bot->getUpdates(offset: 0, limit: 10, timeout: 5);
echo "Found " . count($updates) . " updates" . PHP_EOL;

foreach ($updates as $update) {
    echo "\n--- Update #{$update->update_id} ---" . PHP_EOL;
    echo "Type: " . ($update->getType()?->value ?? 'unknown') . PHP_EOL;
    if ($update->message) {
        echo "Text: " . ($update->message->text ?? '(no text)') . PHP_EOL;
        echo "Contact: " . ($update->message->contact ? $update->message->contact->phone_number : 'none') . PHP_EOL;
    }
    echo "Processing..." . PHP_EOL;
    try {
        $bot->processUpdate($update);
        echo "SUCCESS!" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . PHP_EOL;
        echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
    }
}

if (count($updates) > 0) {
    $lastId = end($updates)->update_id;
    $bot->getUpdates(offset: $lastId + 1, limit: 1, timeout: 1);
    echo "\nUpdates confirmed." . PHP_EOL;
}

if (count($updates) === 0) {
    echo "No pending updates. Send /start to bot and run again." . PHP_EOL;
}

<?php

/*
 * This file is apart of the DiscordPHP-BotBuilder project.
 *
 * Copyright (c) 2016 David Cole <david@team-reflex.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

include __DIR__.'/vendor/autoload.php';

use Discord\Bot\Bot;

// Check for count
if ($argc < 2) {
    echo "Usage: php {$argv[0]} <token>\r\n";
    die(1);
}

$bot = new Bot($argv[1], [
    'prefix' => ';',
]);

$bot->on('ready', function ($config, $discord, $bot) {
    $bot->getLogger()->addInfo('Bot is running.', [
        'user' => "{$discord->username}#{$discord->discriminator}",
        'prefix' => $config['prefix'],
    ]);
});

$bot->addCommand('dank', function ($params, $message) {
    $message->reply('memes');
});

$bot->start();

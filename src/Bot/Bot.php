<?php

/*
 * This file is apart of the DiscordPHP-BotBuilder project.
 *
 * Copyright (c) 2016 David Cole <david@team-reflex.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Discord\Bot;

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\WebSockets\WebSocket;
use Evenement\EventEmitter;
use React\EventLoop\Factory;

/**
 * Provides an easy interface to build your own bot.
 */
class Bot extends EventEmitter
{
    /**
     * The DiscordPHP instance.
     *
     * @var Discord The DiscordPHP instance.
     */
    protected $discord;

    /**
     * The DiscordPHP WebSocket instance.
     *
     * @var WebSocket The WebSocket instance.
     */
    protected $ws;

    /**
     * The ReactPHP event loop.
     *
     * @var LoopInterface The event loop.
     */
    protected $loop;

    /**
     * The config array.
     *
     * @var array The config array.
     */
    protected $config = [];

    /**
     * Constructs a bot instance.
     *
     * @param string             $token  The bot authentication token.
     * @param array              $config A config array.
     * @param LoopInterface|null $loop   The ReactPHP event loop.
     *
     * @return void
     */
    public function __construct($token, array $config = [], $loop = null)
    {
        $this->setupConfig($config);

        $this->loop = is_null($loop) ? Factory::create() : $loop;
        $this->discord = new Discord($token);
        $this->ws = new WebSocket($this->discord, $this->loop, $this->config['use_etf']);

        $this->ws->on('ready', function ($discord) {
            $this->emit('ready', [$this->config, $discord, $this->ws]);

            $this->ws->on(Event::MESSAGE_CREATE, function ($message, $discord, $new) {
                $params = explode(' ', $message->content);
                $command = @$params[0];
                array_shift($params); // Remove the prefix

                foreach ($this->commands as $trigger => $listener) {
                    $expected = $this->config['prefix'].$trigger;

                    if ($command == $expected) {
                        $this->emit('command-triggered', [$expected, $message->author]);
                        call_user_func_array($listener, [$params, $message, $new, $this]);
                    }
                }
            });
        });
    }

    /**
     * Adds a command listener.
     *
     * @param string   $command  The command to invoke the callback on.
     * @param callable $listener The callback to invoke.
     *
     * @return void
     */
    public function addCommand($command, callable $listener)
    {
        $this->commands[$command] = $listener;
    }

    /**
     * Updates the config.
     *
     * @param array $config The values to update.
     *
     * @return void
     */
    public function updateConfig(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Sets up the config.
     *
     * @param array $config The supplied config.
     *
     * @return void
     */
    protected function setupConfig(array $config = [])
    {
        $defaults = [
            'prefix' => '!',
            'use_etf' => true,
        ];

        $this->config = array_merge($defaults, $config);
    }

    /**
     * Starts the event loop.
     *
     * @return void
     */
    public function start()
    {
        $this->loop->run();
    }
}

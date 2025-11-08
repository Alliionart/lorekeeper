<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use App\Models\Discord\DiscordReward;
use App\Models\User\UserAlias;
use App\Models\User\UserDiscordLevel;
use App\Services\DiscordManager;
use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Command as DiscordCommand;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Illuminate\Console\Command;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DiscordBot extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord-bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the discord bot.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
        $this->token = config('lorekeeper.discord_bot.env.token');
        $this->error_channel_id = config('lorekeeper.discord_bot.env.error_channel');
        $this->log_channel_id = config('lorekeeper.discord_bot.env.log_channel');
        $this->banned_words = file_exists(public_path('files/banned_words.txt')) ? file_get_contents(public_path('files/banned_words.txt')) : null;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        // to start the bot run the following:
        // pm2 start 'php artisan discord-bot'

        if (php_sapi_name() !== 'cli') {
            exit;
        }
        if (!$this->token) {
            echo 'Please set the DISCORD_BOT_TOKEN environment variable.', PHP_EOL;
            exit;
        }
        if (!$this->error_channel_id) {
            echo 'Please set the DISCORD_ERROR_CHANNEL environment variable.', PHP_EOL;
            exit;
        }

        $logger = new Logger('discord-logger');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::ERROR));
        $discord = new Discord([
            'token'         => $this->token,
            'intents'       => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS | Intents::GUILDS | Intents::MESSAGE_CONTENT,
            'storeMessages' => true,
            'logger'        => $logger,
        ]);

        $service = new DiscordManager;

        $discord->on('ready', function (Discord $discord) use ($service) {
            echo 'Bot is ready!', PHP_EOL;
            $guild = config('lorekeeper.discord_bot.env.guild_id') ? $discord->guilds->get('id', config('lorekeeper.discord_bot.env.guild_id')) : $discord->guilds->first();
            $channel = $guild->channels->get('id', $this->error_channel_id);
            $channel->sendMessage('Bot is ready! Use `/ping` to check delay.');

            // Register commands
            foreach (config('lorekeeper.discord_bot.commands') as $command) {
                $newCommand = new DiscordCommand($discord, $command);
                $discord->application->commands->save($newCommand)->done(
                    function () use ($command) {
                        echo "Command '{$command['name']}' registered successfully.\n";
                    },
                    function ($e) use ($command) {
                        echo "Failed to register command '{$command['name']}': {$e->getMessage()}\n";
                    }
                );
            }

            // Listen for commands
            $discord->listenCommand('help', function (Interaction $interaction) use ($service) {
                $response = $service->showHelpMessage();
                if (!$response) {
                    $interaction->respondWithMessage(MessageBuilder::new()->setContent('Couldn\'t generate help message! Please try again later.'));

                    return;
                }
                $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($response));
            });

            $discord->listenCommand('ping', function (Interaction $interaction) {
                // Compare timestamps by milliseconds
                $now = Carbon::now();
                $interaction->respondWithMessage(
                    MessageBuilder::new()->setContent('Pong! Delay: '.$now->diffInMilliseconds($interaction->timestamp).'ms')
                );
            });

            $discord->listenCommand('rank', function (Interaction $interaction) use ($service) {
                $interaction->acknowledgeWithResponse(false);

                // Fetch level information
                $level = $service->getUserLevel($interaction);
                if (!$level) {
                    // Error if no corresponding on-site user
                    $interaction->updateOriginalResponse(MessageBuilder::new()->setContent('You don\'t seem to have a level! Have you linked your Discord account on site?'), true);

                    return;
                }

                // Generate and return rank card
                $response = $service->showUserInfo($level);
                $interaction->updateOriginalResponse(
                    MessageBuilder::new()->addFile(public_path('images/cards/'.$response))
                );
                // Remove the card file since it is now uploaded to Discord
                unlink(public_path('images/cards/'.$response));
            });

            $discord->listenCommand('leaderboard', function (Interaction $interaction) use ($discord, $service) {
                // See if the user has a level/rank
                $level = $service->getUserLevel($interaction);

                // Fetch top ten users
                $topTen = (new UserDiscordLevel)->topTen();
                $i = 1;
                $emojis = ['🥇', '🥈', '🥉'];
                $data = [];
                foreach ($topTen as $top) {
                    $data[] = [
                        'name'   => ($emojis[$i - 1] ?? '').' #'.$i.' '.$top->user->name,
                        'value'  => 'Level '.$top->level.' ('.$top->exp.' EXP)',
                        'inline' => false,
                    ];
                    $i++; // increment counter so that rank is correct (index 0 = rank 1)
                }

                // Assemble embed
                $level ? $footer = [
                    'text'    => 'Your position: #'.$level->relativeRank($level->user),
                    'iconUrl' => url('/images/avatars/'.$level->user->avatar),
                ]
                : $footer = null;
                $embed = $discord->factory(Embed::class, [
                    'color'        => hexdec(config('lorekeeper.discord_bot.rank_cards.exp_bar')),
                    'title'        => config('lorekeeper.settings.site_name').' ・ Leaderboard',
                    'type'         => 'rich',
                    'avatar_url'   => url('images/favicon.ico'),
                    'username'     => config('lorekeeper.settings.site_name'),
                    'fields'       => $data,
                    'footer'       => $footer,
                ]);

                $builder = MessageBuilder::new()->addEmbed($embed);
                $interaction->respondWithMessage($builder);
            });

            $discord->listenCommand('grant', function (Interaction $interaction) use ($service) {
                // Attempt to grant
                $response = $service->grant($interaction);
                if (!$response) {
                    // Error
                    $interaction->respondWithMessage(MessageBuilder::new()->setContent('Error granting level. Please check your input and try again.'));

                    return;
                }
                // response can still be error response
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($response));
            });

            $discord->listenCommand('roles', function (Interaction $interaction) {
                if (UserAlias::where('site', 'discord')->where('user_snowflake', $interaction->user->id)->exists()) {
                    $user = UserAlias::where('site', 'discord')->where('user_snowflake', $interaction->user->id)->first()->user;
                    $role = $user->characters->count() ? 'owner' : ($user->settings->is_fto ? 'fto' : 'non_owner');
                    $interaction->guild->members->fetch($interaction->user->id)->done(function (Member $member) use ($interaction, $role, $user) {
                        $promises = [];

                        // check if there are any level roles they need applied
                        $userLevel = $user->discord?->level;
                        if ($userLevel) {
                            $levelRoles = DiscordReward::where('level', '<=', $userLevel)->whereNotNull('role_reward_id')->get();
                            foreach ($levelRoles as $levelRole) {
                                if ($member->roles->has($levelRole->role_reward_id)) {
                                    continue;
                                }
                                // check role exists
                                if (!$interaction->guild->roles->has($levelRole->role_reward_id)) {
                                    continue;
                                }
                                $promises[] = $member->addRole($levelRole->role_reward_id);
                            }
                        }

                        $roles = [
                            'owner'     => config('lorekeeper.discord_bot.roles.owner'),
                            'fto'       => config('lorekeeper.discord_bot.roles.fto'),
                            'non_owner' => config('lorekeeper.discord_bot.roles.non_owner'),
                        ];
                        foreach ($roles as $key => $value) {
                            if (!isset($value) || !$value) {
                                continue;
                            }
                            if (!$interaction->guild->roles->has($levelRole->role_reward_id)) {
                                continue;
                            }
                            if ($role == $key) {
                                $promises[] = $member->addRole($value);
                            } else {
                                // check if user has role
                                if ($member->roles->has($value)) {
                                    $promises[] = $member->removeRole($value);
                                }
                            }
                        }

                        // add the adult role if the user is over 18
                        if ($user->birthday && $user->birthday->diffInYears() >= 18 && config('lorekeeper.discord_bot.roles.adult')) {
                            // check if user has role
                            if (!$member->roles->has(config('lorekeeper.discord_bot.roles.adult'))) {
                                if ($interaction->guild->roles->has(config('lorekeeper.discord_bot.roles.adult'))) {
                                    $promises[] = $member->addRole(config('lorekeeper.discord_bot.roles.adult'));
                                }
                            }
                        }

                        if (empty($promises)) {
                            $interaction->respondWithMessage(MessageBuilder::new()->setContent('No roles to apply.'));

                            return;
                        }

                        // Wait for all role changes to complete
                        \React\Promise\all($promises)->then(function () use ($interaction) {
                            $interaction->respondWithMessage(MessageBuilder::new()->setContent('Roles applied!'));
                        }, function ($error) use ($interaction) {
                            $interaction->respondWithMessage(MessageBuilder::new()->setContent('Error applying roles: '.$error->getMessage()));
                        });
                    });
                } else {
                    $interaction->respondWithMessage(MessageBuilder::new()->setContent('Could not verify invoking user on site. Have you linked your Discord account?'));
                }
            });

            $discord->listenCommand('roll', function (Interaction $interaction) use ($service) {
                $data = $service->roll($interaction);

                $interaction->respondWithMessage(
                    MessageBuilder::new()->setContent(
                        "```md\n".
                        '# '.$data['result'].
                        "\n".
                        'Details: ['.$data['quantity'].'d'.$data['sides'].' ('.implode(', ', $data['rolls']).')]'.
                        '```'
                    )
                );
            });

            $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($service) {
                // don't reply to ourselves
                if ($message->author->bot) {
                    return;
                }

                // check that the message doesn't contain any banned words
                if ($this->banned_words) {
                    $words = explode("\n", $this->banned_words);
                    foreach ($words as $word) {
                        if (stripos($message->content, $word) !== false) {
                            $message->delete();
                            $message->reply('Your message has been deleted due to containing abusive language.');

                            return;
                        }
                    }
                }

                // finally check if we can give exp to this user
                try {
                    if (in_array($message->channel_id, config('lorekeeper.discord_bot.ignored_channels'))) {
                        return;
                    }

                    $action = $service->giveExp($message->author->id, $message->timestamp);
                    // if action is string, throw error
                    if (is_string($action)) {
                        throw new \Exception($action);
                    }
                    if (isset($action['action']) && $action['action'] == 'Level') {
                        // check for rewards
                        $data = $service->checkRewards($message->author->id);

                        if (isset($data['role']) && $data['role']) {
                            // give the user the role
                            $member = $message->guild->members->get('id', $message->author->id);
                            $member->addRole($data['role']);
                        }

                        switch (Settings::get('discord_level_notif')) {
                            case 0:
                                // Send no notification
                                break;
                            case 1:
                                // DM user
                                $message->author->sendMessage('You leveled up! You are now level '.$action['level'].'!'.($data['count'] ? ' You have received '.$data['count'].' rewards!' : ''));
                                break;
                            case 2:
                                // check if we have a specific channel set
                                if (config('lorekeeper.discord_bot.level_up_channel_id')) {
                                    $channel = $message->guild->channels->get('id', config('lorekeeper.discord_bot.level_up_channel_id'));
                                    // mention user
                                    $channel->sendMessage('<@'.$message->author->id.'> You leveled up! You are now level '.$action['level'].'!'.($data['count'] ? ' You have received '.$data['count'].' rewards!' : ''));
                                } else {
                                    // Reply directly to message
                                    $message->reply('You leveled up! You are now level '.$action['level'].'!'.($data['count'] ? ' You have received '.$data['count'].' rewards!' : ''));
                                }
                                break;
                            default:
                                // Reply directly to message
                                $message->reply('You leveled up! You are now level '.$action['level'].'!'.($data['count'] ? ' You have received '.$data['count'].' rewards!' : ''));
                                break;
                        }
                    }
                } catch (\Exception $e) {
                    // this sends the error to the specified channel
                    $guild = $discord->guilds->first();
                    $channel = $guild->channels->get('id', $this->error_channel_id);

                    $channel->sendMessage('Error: '.$e->getMessage());
                }
            });

            $discord->on(Event::GUILD_MEMBER_ADD, function (Member $member) use ($discord) {
                // Check if we have a specific channel set
                if (config('lorekeeper.discord_bot.welcome_channel_id')) {
                    $channelId = config('lorekeeper.discord_bot.welcome_channel_id');
                    $channel = $discord->getChannel($channelId);

                    // Create an embed message
                    $embed = new Embed($discord);
                    $embed->setTitle('Welcome to the Server!')
                        ->setDescription('Welcome to the '.config('app.name').' Discord server!')
                        ->setColor(0x7289DA); // Green color

                    $rulesChannel = config('lorekeeper.discord_bot.rules_channel') ? '<#'.config('lorekeeper.discord_bot.rules_channel').'>' : 'the rules channel';
                    $questionsChannel = config('lorekeeper.discord_bot.questions_channel') ? '<#'.config('lorekeeper.discord_bot.questions_channel').'>' : 'the questions channel';

                    // Add fields to the embed
                    $embed->addField([
                        'name'   => 'Rules',
                        'value'  => "Please make sure to read the rules in {$rulesChannel}.",
                        'inline' => false,
                    ]);
                    $embed->addField([
                        'name'   => 'Questions',
                        'value'  => "If you have any questions, feel free to ask in {$questionsChannel}.",
                        'inline' => false,
                    ]);

                    // Mention user and send the embed message
                    $channel->sendMessage("<@{$member->id}>", false, $embed);
                }
            });

            $discord->on(Event::MESSAGE_UPDATE, function (Message $newMessage, Discord $discord, ?Message $oldMessage) use ($guild) {
                try {
                    $channel = $guild->channels->get('id', $this->log_channel_id);

                    if ($oldMessage) {
                        $oldContent = $oldMessage->content;
                    } else {
                        $oldContent = 'Unknown Content (Cache Expired)';
                    }

                    // Create an embed message
                    $embed = new Embed($discord);
                    $embed->setTitle('Message Edited')
                        ->setColor(0x4682B4)
                        ->addField([
                            'name'   => 'Old Content',
                            'value'  => $oldContent,
                            'inline' => false,
                        ])
                        ->addField([
                            'name'   => 'New Content',
                            'value'  => $newMessage->content,
                            'inline' => false,
                        ])
                        ->setAuthor($newMessage->author->username, null, $newMessage->author->avatar)
                        ->setFooter('Message ID: '.$newMessage->id)
                        ->setTimestamp();

                    $channel->sendMessage('', false, $embed);
                } catch (\Exception $e) {
                    $channel->sendMessage('Error: '.$e->getMessage());
                }
            });

            $discord->on(Event::MESSAGE_DELETE, function ($message) use ($discord, $guild) {
                if ($message instanceof Message) {
                    $oldContent = $message->content;
                } else {
                    $oldContent = 'Unknown Content (Cache Expired)';
                }
                $channel = $guild->channels->get('id', $this->log_channel_id);
                $embed = new Embed($discord);
                $embed->setTitle('Message Deleted')
                    ->setColor(0xFF0000)
                    ->addField([
                        'name'   => 'Content',
                        'value'  => $oldContent,
                        'inline' => false,
                    ])
                    ->setFooter('Message ID: '.$message->id)
                    ->setTimestamp();
                $channel->sendMessage('', false, $embed);
            });
        });
        // init loop
        $discord->run();
    }
}

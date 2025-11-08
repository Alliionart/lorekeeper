<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class GenerateRankCardBack extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-rank-card-back';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a default rank card background image to help with performance.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        // Fetch config values for convenience
        $config = [
            'bg'      => config('lorekeeper.discord_bot.rank_cards.background_color'),
            'accent'  => config('lorekeeper.discord_bot.rank_cards.accent_color'),
            'opacity' => config('lorekeeper.discord_bot.rank_cards.accent_opacity'),
            'logo'    => config('lorekeeper.discord_bot.rank_cards.logo_insert') ?? null,
        ];

        // Assemble a small inset to sit behind the text
        $inset = Image::canvas(365, 90, $config['accent'])
            ->opacity($config['opacity'])
            ->mask(public_path('images/cards/assets/rank_card_mask.png'));

        // Assemble rank card
        $card = Image::canvas(600, 200, $config['bg'])
            ->insert(public_path('images/rank_card_background.png'));

        if ($config['logo']) {
            $card
                ->insert(public_path($config['logo']), 'bottom-right');
        }

        $card
            ->mask(public_path('images/cards/assets/rank_card_mask.png'))
            ->insert($inset, 'bottom-right', 48, 75);

        // Set dir and filename
        $dir = public_path('images/cards/assets');
        $filename = 'generated-back.png';

        // Save the card itself and return the filename
        $card->save($dir.'/'.$filename);

        return 0;
    }
}

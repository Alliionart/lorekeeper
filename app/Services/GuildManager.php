<?php

namespace App\Services;

use App\Models\Guild\Guild;
use App\Models\Guild\GuildRank;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GuildManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Guild Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of prompt categories and prompts.
    |
    */

    /**
     * Creates a new prompt.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Guild\Guild|bool
     */
    public function createGuild($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $guild = Guild::create(Arr::only($data, ['name', 'summary', 'description', 'parsed_description']));

            if ($image) {
                $this->handleImage($image, $guild->imagePath, $guild->imageFileName);
            }

            //TODO: Notifications create call

            return $this->commitReturn($guild);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a prompt.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return \App\Models\Guild\Guild|bool
     */
    public function updateGuild($guild, $data, $user) {
        DB::beginTransaction();

        \Log::info(public_path('images/data/guilds/'));

        try {
            // More specific validation
            if (Guild::where('name', $data['name'])->where('id', '!=', $guild->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            \Log::info($data);

            $logo = null;
            if (isset($data['logo']) && $data['logo']) {
                $data['has_logo'] = 1;
                $logo = $data['logo'];
                unset($data['logo']);
            }

            $banner = null;
            if (isset($data['banner']) && $data['banner']) {
                $data['has_banner'] = 1;
                $logo = $data['banner'];
                unset($data['banner']);
            }

            $data = $this->populateData($data, $guild);

            $guild->update(Arr::only($data, ['name', 'summary', 'description', 'parsed_description']));

            if ($guild && $logo) {
                $this->handleImage($logo, $guild->imagePath, $guild->imageFileName);
            }
            if ($guild && $banner) {
                $this->handleImage($banner, $guild->imagePath, $guild->imageFileName);
            }

            return $this->commitReturn($guild);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Disbands a guild.
     *
     * @param \App\Models\Guild\Guild $guild
     *
     * @return bool
     */
    public function disbandGuild($guild) {
        DB::beginTransaction();

        try {
            if ($guild->members) {
                //Delete the members rows from the guild_users table here
            }
            if ($guild->characters) {
                //Delete the members rows from the guild_characters table here
            }
            //Delete other relational data besides bank/inv

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * ---------------------------------------------------------------------------
     * GUILD RANKS
     * ---------------------------------------------------------------------------.
     *
     * @param mixed $guild
     * @param mixed $data
     * @param mixed $user
     */

    /**
     * Updates guild ranks.
     */
    public function updateGuildRanks($guild, $data, $user) {
        DB::beginTransaction();

        try {
            // 1. Check if user has permission to edit ranks
            if (!$guild->getGuildEditPermissions($user)) {
                throw new \Exception('You do not have permission to edit this guild\'s ranks.');
            }
            if (!isset($data['user_ranks']) && !isset($data['character_ranks'])) {
                throw new \Exception('No rank data provided.');
            }

            // Process user ranks
            if (isset($data['user_ranks'])) {
                $userRanks = [];
                foreach ($data['user_ranks'] as $index => $rank) {
                    $currentRank = GuildRank::where('guild_id', $guild->id)
                        ->where('name', $rank['rank_name'])
                        ->where('for_user', 1)
                        ->where('for_character', 0)
                        ->first();

                    //TODO: Handle rank icons here. All rank icons should have the same naming convention: guild_{guildid}_rank_{rankid}.png

                    if (!$currentRank) {
                        GuildRank::create([
                            'guild_id'              => $guild->id,
                            'name'                  => $rank['rank_name'],
                            'reputation_threshold'  => $rank['reputation_threshold'],
                            'is_character_rank'     => 0,
                            'description'           => $rank['description'],
                        ]);
                    } else {
                        $currentRank->update([
                            'name'                  => $rank['rank_name'],
                            'reputation_threshold'  => $rank['reputation_threshold'],
                            'description'           => $rank['description'],
                        ]);
                    }
                }
                // Delete ranks that do not exist in the new data
                $newRankNames = array_map(function ($rank) {
                    return $rank['rank_name'];
                }, $data['user_ranks']);

                $guild->ranks()
                    ->where('for_user', 1)
                    ->where('for_character', 0)
                    ->whereNotIn('name', $newRankNames)
                    ->delete();
            }

            // Process character ranks
            if (isset($data['character_ranks'])) {
                $characterRanks = [];
                foreach ($data['character_ranks'] as $index => $rank) {
                    $currentRank = GuildRank::where('guild_id', $guild->id)
                        ->where('name', $rank['rank_name'])
                        ->where('for_user', 0)
                        ->where('for_character', 1)
                        ->first();

                    if (!$currentRank) {
                        GuildRank::create([
                            'guild_id'              => $guild->id,
                            'name'                  => $rank['rank_name'],
                            'reputation_threshold'  => $rank['reputation_threshold'],
                            'is_character_rank'     => 0,
                            'description'           => $rank['description'],
                        ]);
                    } else {
                        $currentRank->update([
                            'name'                  => $rank['rank_name'],
                            'reputation_threshold'  => $rank['reputation_threshold'],
                            'description'           => $rank['description'],
                        ]);
                    }
                }
                // Delete ranks that do not exist in the new data
                $newRankNames = array_map(function ($rank) {
                    return $rank['rank_name'];
                }, $data['user_ranks']);

                $guild->ranks()
                    ->where('for_user', 0)
                    ->where('for_character', 1)
                    ->whereNotIn('name', $newRankNames)
                    ->delete();
            }

            return $this->commitReturn($guild);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a guild.
     *
     * @param array                   $data
     * @param \App\Models\Guild\Guild $guild
     *
     * @return array
     */
    private function populateData($data, $guild = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['open_inventory'])) {
            $data['open_inventory'] = 0;
        }
        if (!isset($data['open_bank'])) {
            $data['open_bank'] = 0;
        }
        if (!isset($data['open_pets'])) {
            $data['open_pets'] = 0;
        }
        if (!isset($data['open_armory'])) {
            $data['open_armory'] = 0;
        }
        if (!isset($data['open_new_users'])) {
            $data['open_new_users'] = 0;
        }
        if (!isset($data['automatic_app_approval'])) {
            $data['automatic_app_approval'] = 0;
        }

        if (isset($data['remove_image'])) {
            if ($guild && $guild->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($guild->imagePath, $guild->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /*
     * ---------------------------------------------------------------------------
     * MISC FUNCTIONS
     * ---------------------------------------------------------------------------
     */
}

<?php

namespace App\Services;

use App\Models\Character\Character;
use App\Models\Guild\Guild;
use App\Models\Guild\GuildCharacter;
use App\Models\Guild\GuildInvitation;
use App\Models\Guild\GuildApplication;
use App\Models\Guild\GuildItem;
use App\Models\Guild\GuildMember;
use App\Models\Guild\GuildRank;
use App\Models\Guild\GuildShop;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Notifications;
use Settings;

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
                $data['has_logo'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_logo'] = 0;
            }

            $guild = Guild::create(Arr::only($data, [
                'name', 'description', 'parsed_description', 'location',
                'max_users', 'max_characters',
                'open_new_users', 'status',
                'has_logo', 'hash',
                'owner_id',
            ]));

            if ($guild) {
                $this->logAdminAction($user, 'Created Guild', 'Created Guild '.$guild->id);
                $this->updateGuildStaff($guild, $data, $user);
            }

            if ($image) {
                $this->handleImage($image, $guild->imagePath, $guild->logoFileName);
            }

            //TODO: Notifications create call

            return $this->commitReturn($guild);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates guild staff.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     * @param mixed|null              $previousOwnerId
     *
     * @return \App\Models\Guild\Guild|bool
     */
    public function updateGuildStaff($guild, $data, $user, $previousOwnerId = null) {
        DB::beginTransaction();

        try {
            if (!$guild) {
                throw new \Exception('Invalid guild.');
            }
            $previousOwnerId ??= $guild->owner_id;
            if ((int) $user->id !== (int) $previousOwnerId && !$user->isStaff) {
                throw new \Exception('Only the guild owner may edit staff!');
            }

            $ownerId = $data['owner_id'] ?? $guild->owner_id;
            $ownerChanged = (int) $ownerId !== (int) $previousOwnerId;
            $ownerMembership = GuildMember::where([
                ['guild_id', $guild->id],
                ['user_id', $ownerId],
            ]);

            if (!$ownerMembership->exists()) {
                GuildMember::create([
                    'guild_id'      => $guild->id,
                    'user_id'       => $ownerId,
                    'permissions'   => 2,
                    'joined_at'     => Carbon::now(),
                ]);
            } elseif ($ownerChanged) {
                $ownerMembership->update([
                    'permissions'   => 2,
                ]);
            }

            if (isset($data['mods']) && is_array($data['mods'])) {
                $mods = $guild->mods()->get();
                $newMods = $data['mods'];

                $removeMods = array_diff($mods->pluck('user_id')->toArray(), $newMods);
                $newMods = array_diff($newMods, $mods->pluck('user_id')->toArray());

                if (!empty($removeMods)) {
                    $guild->mods()
                        ->whereIn('user_id', $removeMods)
                        ->update(['permissions' => 0]);
                }
                if (!empty($newMods)) {
                    $guild->mods()
                        ->whereIn('user_id', $newMods)
                        ->update(['permissions' => 1]);
                }
            }

            return $this->commitReturn(true);
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

        try {
            // More specific validation
            if (Guild::where('name', $data['name'])->where('id', '!=', $guild->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $logo = null;
            if (isset($data['logo']) && $data['logo']) {
                $data['has_logo'] = 1;
                $logo = $data['logo'];
                unset($data['logo']);
            }

            $banner = null;
            if (isset($data['banner']) && $data['banner']) {
                $data['has_banner'] = 1;
                $banner = $data['banner'];
                unset($data['banner']);
            }

            $data = $this->populateData($data, $guild);

            $previousOwnerId = $guild->owner_id;
            $guild->update($data);

            $this->logAdminAction($user, 'Updated Guild', 'Updated Guild '.$guild->id);
            if (!$this->updateGuildStaff($guild, $data, $user, $previousOwnerId)) {
                throw new \Exception('Failed to update guild staff.');
            }

            if ($guild && $logo) {
                $this->handleImage($logo, $guild->imagePath, $guild->logoFileName);
            }
            if ($guild && $banner) {
                $this->handleImage($banner, $guild->imagePath, $guild->bannerFileName);
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
     * @param mixed                   $user
     *
     * @return bool
     */
    public function disbandGuild($guild, $user) {
        DB::beginTransaction();

        try {
            if (!$guild) {
                throw new \Exception('Invalid guild.');
            }
            if ((int) $user->id !== (int) $guild->owner_id && !$user->isStaff) {
                throw new \Exception('Only the Guild Owner or staff may disband the guild.');
            }

            $guild->members()->delete();
            $guild->characters()->delete();

            $guild->update([
                'is_disbanded'  => true,
                'status'        => 'disbanded',
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a character.
     *
     * @param \App\Models\User\User $user
     * @param mixed                 $guild
     *
     * @return bool
     */
    public function deleteGuild($guild, $user) {
        DB::beginTransaction();

        try {
            if (!$guild) {
                throw new \Exception('Invalid guild.');
            }
            if (!$user->isStaff) {
                throw new \Exception('Only staff may delete the guild.');
            }

            $guild->members()->delete();
            $guild->characters()->delete();
            if (!$this->logAdminAction($user, 'Deleted Guild', 'Deleted Guild '.$guild->id)) {
                throw new \Exception('Failed to log admin action.');
            }

            // Delete guild
            // This is a soft delete
            $guild->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * ---------------------------------------------------------------------------
     * GUILD MEMBERS & CHARACTERS
     * ---------------------------------------------------------------------------.
     *
     * @param mixed $guild
     * @param mixed $data
     * @param mixed $manage
     * @param mixed $user
     */

    /**
     * Handles an invitation based on the action supplied.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param string                  $manage
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function manageMembers($guild, $manage, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$guild || !in_array($manage, ['users', 'characters'], true)) {
                throw new \Exception('Invalid guild or member type.');
            }
            if (!$user->isStaff && !$guild->getGuildEditPermissions($user)) {
                throw new \Exception('You do not have permission to manage this guild.');
            }
            if (!isset($data['action']) || !in_array($data['action'], ['remove', 'update_rank'], true)) {
                throw new \Exception('You must provide a valid action.');
            }

            $idField = $manage === 'users' ? 'user_ids' : 'character_ids';
            $ids = $data[$idField] ?? [];
            if (!is_array($ids) || count($ids) < 1) {
                throw new \Exception('Select at least one '.($manage === 'users' ? 'member' : 'character').'.');
            }
            $ids = array_values(array_unique($ids));

            if ($data['action'] === 'update_rank') {
                $rankField = $manage === 'users' ? 'user_rank' : 'character_rank';
                $rank = GuildRank::where('guild_id', $guild->id)
                    ->where('for_user', $manage === 'users' ? 1 : 0)
                    ->where('for_character', $manage === 'characters' ? 1 : 0)
                    ->where('id', $data[$rankField] ?? null)
                    ->first();

                if (!$rank) {
                    throw new \Exception('Select a valid rank.');
                }

                if ($manage === 'users') {
                    $records = GuildMember::where('guild_id', $guild->id)->whereIn('user_id', $ids);
                } else {
                    $records = GuildCharacter::where('guild_id', $guild->id)->whereIn('character_id', $ids);
                }

                if ($records->count() !== count($ids)) {
                    throw new \Exception('One or more selected entries do not belong to this guild.');
                }
                $records->update(['rank_id' => $rank->id]);
            } elseif ($manage === 'users') {
                if (in_array((string) $guild->owner_id, array_map('strval', $ids), true)) {
                    throw new \Exception('The guild owner cannot be removed.');
                }

                $records = GuildMember::where('guild_id', $guild->id)->whereIn('user_id', $ids);
                if ($records->count() !== count($ids)) {
                    throw new \Exception('One or more selected members do not belong to this guild.');
                }
                $records->delete();
            } else {
                $records = GuildCharacter::where('guild_id', $guild->id)->whereIn('character_id', $ids);
                if ($records->count() !== count($ids)) {
                    throw new \Exception('One or more selected characters do not belong to this guild.');
                }
                $records->delete();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a new application for a guild.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function applyToGuild($guild, $data, $user) {
        $in_guild = GuildMember::where([
            ['guild_id', $guild->id],
            ['user_id', $user->id],
        ])->exists();

        DB::beginTransaction();

        try {
            if ($in_guild) {
                throw new \Exception('You are already in the guild!');
            }
            
            $alreadyApplied = GuildApplication::where([
                ['guild_id', $guild->id],
                ['user_id', $user->id],
            ])->exists();

            if ($alreadyApplied) {
                throw new \Exception('You have already applied to this guild!');
            }

            $app = GuildApplication::create([
                'guild_id'      => $guild->id,
                'user_id'       => $user->id,
                'comment'       => parse($data['application_message']) ?? null,
                'status'        => 'pending',
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Handles applications in bulk.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     * @param string                  $action
     *
     * @return bool
     */
    public function handleApplications($guild, $data, $user, $action) {
        DB::beginTransaction();

        try {
            if (!$guild || !$user || !in_array($action, ['accept', 'reject'], true)) {
                throw new \Exception('Invalid request.');
            }
            if ((int) $user->id !== (int) $guild->owner_id && !$user->isStaff) {
                throw new \Exception('Only the Guild Owner or staff may manage applications.');
            }
            if (!isset($data['application_ids']) || !is_array($data['application_ids']) || count($data['application_ids']) < 1) {
                throw new \Exception('Select at least one application.');
            }

            if ($action === 'accept') {
                foreach( $data['application_ids'] as $app_id) {
                    $app = GuildApplication::where('guild_id', $guild->id)->where('id', $app_id)->first();

                    if (!$app) {
                        throw new \Exception('One or more selected applications do not belong to this guild.');
                    }

                    $updates = ['status' => 'accepted', 'updated_at' => Carbon::now()];
                    if (isset($data['guild_comment']) && array_key_exists($app->id, $data['guild_comment'])) {
                        $updates['guild_comment'] = $data['guild_comment'][$app->id];
                    }
                    $app->update($updates);

                    GuildMember::create([
                        'guild_id'      => $guild->id,
                        'user_id'       => $app->user_id,
                        'permissions'   => 0,
                        'joined_at'     => Carbon::now(),
                    ]);
                }
            } else if ($action === 'reject') {
                foreach( $data['application_ids'] as $app_id) {
                    $app = GuildApplication::where('guild_id', $guild->id)->where('id', $app_id)->first();

                    if (!$app) {
                        throw new \Exception('One or more selected applications do not belong to this guild.');
                    }

                    $updates = ['status' => 'rejected', 'updated_at' => Carbon::now()];
                    if (isset($data['guild_comment']) && array_key_exists($app->id, $data['guild_comment'])) {
                        $updates['guild_comment'] = $data['guild_comment'][$app->id];
                    }
                    $app->update($updates);
                }
            }

            return $this->commitReturn($action == 'expired' ?: true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }


    /**
     * Handles an invitation based on the action supplied.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param string                  $action
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function handleInvitation($guild, $action, $user) {
        // We always want to remove the invite row regardless
        $in_guild = GuildMember::where([
            ['guild_id', $guild->id],
            ['user_id', $user->id],
        ])->exists();
        $invite = GuildInvitation::where([
            ['guild_id', $guild->id],
            ['user_id', $user->id],
        ])->first();

        $invite->delete();

        DB::beginTransaction();

        try {
            if ($in_guild) {
                throw new \Exception('You are already in the guild!');
            }

            if ($invite->expires_in <= Carbon::now()) {
                $action = 'expired';
            }

            if ($action === 'accept') {
                $member = GuildMember::create([
                    'guild_id'      => $guild->id,
                    'user_id'       => $user->id,
                    'permissions'   => 0,
                    'joined_at'     => Carbon::now(),
                ]);
            }

            return $this->commitReturn($action == 'expired' ?: true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Adds members to the guild.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function addMembers($guild, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$guild) {
                throw new \Exception('Invalid guild.');
            }
            if ((int) $user->id !== (int) $guild->owner_id && !$user->isStaff) {
                throw new \Exception('Only the Guild Owner or staff may edit members.');
            }
            if (!isset($data['users']) || !is_array($data['users']) || count($data['users']) < 1) {
                throw new \Exception('Members list is invalid.');
            }

            $newMembers = [];

            foreach ($data['users'] as $u_id) {
                $u = User::find($u_id);

                if (!$u) {
                    throw new \Exception('At least one user is invalid.');
                }

                if ((int) $u_id === (int) $guild->owner_id) {
                    throw new \Exception('The owner cannot be removed from the guild.');
                }

                $in_guild = GuildMember::where([
                    ['guild_id', $guild->id],
                    ['user_id', $u->id],
                ])->exists();

                if ($in_guild) {
                    continue;
                }

                $invitationQuery = GuildInvitation::where('guild_id', $guild->id)->where('user_id', $u->id);
                $hasPendingInvitation = (clone $invitationQuery)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
                    })
                    ->exists();

                if ($hasPendingInvitation) {
                    throw new \Exception($u->name.' already has a pending guild invitation.');
                }

                if (!$user->settings->allow_guild_invitations) {
                    continue;
                }

                $invitationQuery
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', Carbon::now())
                    ->delete();

                $invitation = GuildInvitation::create([
                    'guild_id'      => $guild->id,
                    'user_id'       => $u->id,
                    'expires_at'    => Carbon::now()->addDays(30),
                ]);

                if ($invitation) {
                    Notifications::create('GUILD_INVITATION', $u, [
                        'guild_name'        => $guild->name,
                        'guild_url'         => $guild->viewUrl,
                        'accept_url'        => $guild->inviteAccept,
                        'reject_url'        => $guild->rejectAccept,
                        'sender_url'        => $user->url,
                        'sender_name'       => $user->name,
                    ]);

                    $newMembers[] = $u->displayName;
                }
            }

            return $this->commitReturn($newMembers);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Removes members from the guild.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function removeMembers($guild, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$guild || (!$user->isStaff && !$guild->getGuildEditPermissions($user))) {
                throw new \Exception('Only the Guild Owner or staff may edit members.');
            }
            if (!isset($data['users']) || !is_array($data['users']) || count($data['users']) < 1) {
                throw new \Exception('Members list is invalid.');
            }

            $userIds = array_values(array_unique($data['users']));
            if (in_array((string) $guild->owner_id, array_map('strval', $userIds), true)) {
                throw new \Exception('Guild owner cannot be removed from the guild.');
            }

            $members = GuildMember::where('guild_id', $guild->id)->whereIn('user_id', $userIds);
            if ($members->count() !== count($userIds)) {
                throw new \Exception('One or more selected members do not belong to this guild.');
            }
            $members->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Removes a user from a guild and detaches their characters.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function leaveGuild($guild, $user) {
        DB::beginTransaction();

        try {
            if (!$guild) {
                throw new \Exception('Invalid guild.');
            }
            if ((int) $user->id === (int) $guild->owner_id) {
                throw new \Exception('The guild owner must transfer ownership before leaving.');
            }

            $membership = GuildMember::where([
                ['guild_id', $guild->id],
                ['user_id', $user->id],
            ])->first();

            if (!$membership) {
                throw new \Exception('You are not a member of this guild.');
            }

            $characterIds = Character::where('user_id', $user->id)->pluck('id');
            $guild->characters()->whereIn('character_id', $characterIds)->delete();
            $membership->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Adds characters to the guild.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function addCharacters($guild, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$guild || (!$user->isStaff && !$guild->getGuildEditPermissions($user))) {
                throw new \Exception('You do not have permission to edit this guild.');
            }
            if (!$data || !is_array($data['characters']) || count($data['characters']) < 1) {
                throw new \Exception('Characters list is invalid.');
            }

            $newCharacters = [];

            foreach ($data['characters'] as $c_id) {
                $c = Character::find($c_id);

                if (!$c || $c->is_myo_slot) {
                    throw new \Exception('At least one character is invalid.');
                }
                if (!$user->isStaff && !GuildMember::where([
                    ['guild_id', $guild->id],
                    ['user_id', $c->user_id],
                ])->exists()) {
                    throw new \Exception('Only characters belonging to guild members may be added.');
                }

                $in_guild = GuildCharacter::where([
                    ['guild_id', $guild->id],
                    ['character_id', $c->id],
                ])->exists();

                if ($in_guild) {
                    continue;
                }

                $member = GuildCharacter::create([
                    'guild_id'      => $guild->id,
                    'character_id'  => $c->id,
                    'joined_at'     => Carbon::now(),
                ]);

                if ($member) {
                    $newCharacters[] = $c->displayName;
                }
            }

            return $this->commitReturn($newCharacters);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Removes characters from the guild.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return bool
     */
    public function removeCharacters($guild, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$guild || (!$user->isStaff && !$guild->getGuildEditPermissions($user))) {
                throw new \Exception('You do not have permission to edit this guild.');
            }
            if (!$data || !is_array($data['characters']) || count($data['characters']) < 1) {
                throw new \Exception('Characters list is invalid.');
            }

            $removedCharacters = [];

            foreach ($data['characters'] as $c_id) {
                $c = Character::find($c_id);

                if (!$c || $c->is_myo_slot) {
                    throw new \Exception('At least one character is invalid.');
                }
                $in_guild = GuildCharacter::where([
                    ['guild_id', $guild->id],
                    ['character_id', $c->id],
                ])->exists();

                if (!$in_guild) {
                    continue;
                }

                $member = GuildCharacter::where([
                    ['character_id', $c->id],
                    ['guild_id', $guild->id],
                ])->delete();

                if ($member) {
                    $removedCharacters[] = $c->displayName;
                }
            }

            return $this->commitReturn($removedCharacters);
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
                    if (!$rank['rank_name']) {
                        continue;
                    }

                    $currentRank = GuildRank::where('guild_id', $guild->id)
                        ->where('name', $rank['rank_name'])
                        ->where('for_user', 1)
                        ->where('for_character', 0)
                        ->first();

                    $icon = null;
                    if (isset($rank['icon']) && $rank['icon']) {
                        $icon = $rank['icon'];
                    }

                    if (!$currentRank) {
                        $currentRank = GuildRank::create([
                            'guild_id'              => $guild->id,
                            'name'                  => $rank['rank_name'],
                            'required_reputation'   => $rank['rank_threshold'],
                            'is_character_rank'     => 0,
                            'description'           => $rank['description'],
                            'has_image'             => $icon ? true : false,
                            'for_character'         => false,
                            'for_user'              => true,
                        ]);
                    } else {
                        $currentRank->update([
                            'name'                  => $rank['rank_name'],
                            'required_reputation'   => $rank['rank_threshold'],
                            'description'           => $rank['description'],
                            'has_image'             => $icon ? true : false,
                        ]);
                    }
                    if ($icon) {
                        $this->handleImage($icon, $guild->imagePath, $currentRank->rankImageName);
                        $currentRank->update([
                            'has_image' => true,
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
                    if (!$rank['rank_name']) {
                        continue;
                    }

                    $currentRank = GuildRank::where('guild_id', $guild->id)
                        ->where('name', $rank['rank_name'])
                        ->where('for_user', 0)
                        ->where('for_character', 1)
                        ->first();

                    $icon = null;
                    if (isset($rank['icon']) && $rank['icon']) {
                        $icon = $rank['icon'];
                    }

                    if (!$currentRank) {
                        $currentRank = GuildRank::create([
                            'guild_id'              => $guild->id,
                            'name'                  => $rank['rank_name'],
                            'required_reputation'   => $rank['rank_threshold'],
                            'is_character_rank'     => 0,
                            'description'           => $rank['description'],
                            'for_character'         => true,
                            'for_user'              => false,
                        ]);
                    } else {
                        $currentRank->update([
                            'name'                  => $rank['rank_name'],
                            'required_reputation'   => $rank['rank_threshold'],
                            'description'           => $rank['description'],
                        ]);
                    }
                    if ($icon) {
                        $this->handleImage($icon, $guild->imagePath, $currentRank->rankImageName);
                        $currentRank->update([
                            'has_image' => true,
                        ]);
                    }
                }
                // Delete ranks that do not exist in the new data
                // $newRankNames = array_map(function ($rank) {
                //     return $rank['rank_name'];
                // }, $data['user_ranks']);

                // $guild->ranks()
                //     ->where('for_user', 0)
                //     ->where('for_character', 1)
                //     ->whereNotIn('name', $newRankNames)
                //     ->delete();
            }

            return $this->commitReturn($guild);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /*
     * ---------------------------------------------------------------------------
     * GUILD SHOPS
     * ---------------------------------------------------------------------------
     */

    /**
     * Creates a new shop.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $guild
     *
     * @return \App\Models\Shop\Shop|bool
     */
    public function createShop($guild, $data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateShopData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $data['guild_id'] = $guild->id;

            $shop = GuildShop::create($data);

            if ($image) {
                $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);
            }

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a shop.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $guild
     *
     * @return \App\Models\Guild\GuildShop|bool
     */
    public function updateShop($guild, $data, $user) {
        DB::beginTransaction();

        try {
            $shop = $guild->shop()->first();

            if (!$shop) {
                throw new \Exception('Invalid shop!');
            }
            if (GuildShop::where('name', $data['name'])->where('id', '!=', $shop->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateShopData($data, $shop);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $data['hash'] = randomString(10);
                $image = $data['image'];
                unset($data['image']);
            }

            $shop->update($data);

            if ($shop) {
                $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);
            }

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates shop stock.
     *
     * @param \App\Models\Guild\Guild $guild
     * @param array                   $data
     * @param \App\Models\User\User   $user
     *
     * @return \App\Models\Guild\GuildShop|bool
     */
    public function updateShopStock($guild, $data, $user) {
        DB::beginTransaction();

        try {
            $shop = $guild->shop()->first();
            if (!$shop) {
                throw new \Exception('Invalid shop!');
            }

            $member = $guild->members()->where('user_id', $user->id)->first();
            if (!$user->isStaff && $user->id !== $guild->owner_id && (!$member || !$member->isMod())) {
                throw new \Exception('Only guild staff or site staff may update shop stock.');
            }

            if (isset($data['item_id'])) {
                foreach ($data['item_id'] as $key => $itemId) {
                    if ($data['cost'][$key] == null) {
                        throw new \Exception('One or more of the items is missing a cost.');
                    }
                    if ($data['cost'][$key] < 0) {
                        throw new \Exception('One or more of the items has a negative cost.');
                    }
                }

                // Clear the existing shop stock
                $shop->stock()->delete();

                foreach ($data['item_id'] as $key => $itemId) {
                    $qtyRemaining = (int) ($data['quantity'][$key] ?? 1);
                    $stacks = GuildItem::where([
                        ['item_id', $itemId],
                        ['guild_id', $guild->id],
                        ['count', '>', 0],
                    ])->orderBy('id')->get();

                    $inventoryManager = new InventoryManager;

                    foreach ($stacks as $stack) {
                        if ($qtyRemaining <= 0) {
                            break;
                        }

                        $debitQuantity = min($stack->count, $qtyRemaining);
                        if ($debitQuantity <= 0) {
                            continue;
                        }

                        if (!$inventoryManager->debitStack($guild, 'Shop Stock Creation', ['data' => 'Reserved for shop stock'], $stack, $debitQuantity)) {
                            throw new \Exception('Failed to debit the guild item stack for shop stock creation.');
                        }

                        $qtyRemaining -= $debitQuantity;
                    }

                    if ($qtyRemaining > 0) {
                        throw new \Exception('Not enough guild-owned items are available to create this shop stock quantity.');
                    }

                    $shop->stock()->create([
                        'guild_shop_id'         => $shop->id,
                        'item_id'               => $itemId,
                        'currency_id'           => $data['currency_id'][$key],
                        'cost'                  => $data['cost'][$key],
                        'guild_cost'            => $data['guild_cost'][$key] ?? $data['cost'][$key],
                        'is_limited_stock'      => isset($data['is_limited_stock'][$key]),
                        'quantity'              => $data['quantity'][$key] ?? 1,
                        'purchase_limit'        => $data['purchase_limit'][$key],
                        'is_visible'            => 1,
                        'guild_only'            => isset($data['guild_only']),
                        'stock_type'            => 'Item', //Maybe change this so we can support other things?
                    ]);
                }
            } else {
                // Clear the existing shop stock
                $shop->stock()->delete();
            }

            return $this->commitReturn($shop);
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
        if (!isset($data['owner_id'])) {
            $data['owner_id'] = $guild ? $guild->owner_id : $data['owner_id'] ?? null;
        }
        if (!isset($data['status'])) {
            $data['status'] = $guild ? $guild->status : $data['status'] ?? 'inactive';
        }
        if (!isset($data['max_users'])) {
            $data['max_users'] = $guild ? $guild->max_users : Settings::get('guild_max_users');
        }
        if (!isset($data['max_characters'])) {
            $data['max_characters'] = $guild ? $guild->max_characters : Settings::get('guild_max_characters');
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

    /**
     * Processes user input for creating/updating a shop.
     *
     * @param array                 $data
     * @param \App\Models\Shop\Shop $shop
     *
     * @return array
     */
    private function populateShopData($data, $shop = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        } else {
            $data['parsed_description'] = null;
        }
        $data['is_active'] = isset($data['is_active']);

        if (isset($data['remove_image'])) {
            if ($shop && $shop->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName);
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

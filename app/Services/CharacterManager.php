<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Character\Character;
use App\Models\Character\CharacterBookmark;
use App\Models\Character\CharacterCategory;
use App\Models\Character\CharacterClass;
use App\Models\Character\CharacterCurrency;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterFeature;
use App\Models\Character\CharacterImage;
use App\Models\Character\CharacterLineage;
use App\Models\Character\CharacterStat;
use App\Models\Character\CharacterTransfer;
use App\Models\Sales\SalesCharacter;
use App\Models\Species\Subtype;
use App\Models\User\User;
use App\Models\User\UserPet;
use App\Models\WorldExpansion\FactionRankMember;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class CharacterManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Character Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation and modification of character data.
    |
    */

    /**
     * Retrieves the next number to be used for a character's masterlist code.
     *
     * @param int $categoryId
     *
     * @return string
     */
    public function pullNumber($categoryId) {
        $digits = config('lorekeeper.settings.character_number_digits');
        $result = str_pad('', $digits, '0'); // A default value, in case
        $number = 0;

        // First check if the number needs to be the overall next
        // or next in category, and retrieve the highest number
        if (config('lorekeeper.settings.character_pull_number') == 'all') {
            $character = Character::myo(0)->orderBy('number', 'DESC')->first();
            if ($character) {
                $number = ltrim($character->number, 0);
            }
            if (!strlen($number)) {
                $number = '0';
            }
        } elseif (config('lorekeeper.settings.character_pull_number') == 'category' && $categoryId) {
            $character = Character::myo(0)->where('character_category_id', $categoryId)->orderBy('number', 'DESC')->first();
            if ($character) {
                $number = ltrim($character->number, 0);
            }
            if (!strlen($number)) {
                $number = '0';
            }
        }

        $result = format_masterlist_number($number + 1, $digits);

        return $result;
    }

    /**
     * Creates a new character or MYO slot.
     *
     * @param array $data
     * @param User  $user
     * @param bool  $isMyo
     *
     * @return bool|Character
     */
    public function createCharacter($data, $user, $isMyo = false) {
        DB::beginTransaction();

        try {
            if (!$isMyo && Character::where('slug', $data['slug'])->exists()) {
                throw new \Exception('Please enter a unique character code.');
            }

            if (!(isset($data['user_id']) && $data['user_id']) && !(isset($data['owner_url']) && $data['owner_url'])) {
                throw new \Exception('Please select an owner.');
            }
            if (!$isMyo) {
                if (!(isset($data['species_id']) && $data['species_id'])) {
                    throw new \Exception('Characters require a species.');
                }
                if (!(isset($data['rarity_id']) && $data['rarity_id'])) {
                    throw new \Exception('Characters require a rarity.');
                }
            }
            if (isset($data['subtype_id']) && $data['subtype_id']) {
                $subtype = Subtype::find($data['subtype_id']);
                if (!(isset($data['species_id']) && $data['species_id'])) {
                    throw new \Exception('Species must be selected to select a subtype.');
                }
                if (!$subtype || $subtype->species_id != $data['species_id']) {
                    throw new \Exception('Selected subtype invalid or does not match species.');
                }
            } else {
                $data['subtype_id'] = null;
            }

            // Get owner info
            $url = null;
            $recipientId = null;
            if (isset($data['user_id']) && $data['user_id']) {
                $recipient = User::find($data['user_id']);
            } elseif (isset($data['owner_url']) && $data['owner_url']) {
                $recipient = checkAlias($data['owner_url']);
            }

            if (is_object($recipient)) {
                $recipientId = $recipient->id;
                $data['user_id'] = $recipient->id;
            } else {
                $url = $recipient;
            }

            // Create character
            $character = $this->handleCharacter($data, $isMyo);
            if (!$character) {
                throw new \Exception('Error happened while trying to create character.');
            }

            // Create character lineage
            $lineage = $this->handleCharacterLineage($data, $character, $isMyo);

            // Create character image
            $data['is_valid'] = true; // New image of new characters are always valid
            $image = $this->handleCharacterImage($data, $character, $isMyo);
            if (!$image) {
                throw new \Exception('Error happened while trying to create image.');
            }

            // Update the character's image ID
            $character->character_image_id = $image->id;
            $character->save();

            // Create character stats
            $character->level()->create([
                'character_id' => $character->id,
            ]);

            if (isset($data['stats'])) {
                foreach ($data['stats'] as $key=>$stat) {
                    CharacterStat::create([
                        'character_id' => $character->id,
                        'stat_id'      => $key,
                        'count'        => $stat,
                    ]);
                }
            }

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, $recipientId, $url, $character->id, $isMyo ? 'MYO Slot Created' : 'Character Created', 'Initial upload', 'character');

            // Add a log for the user
            // This logs ownership of the character
            $this->createLog($user->id, null, $recipientId, $url, $character->id, $isMyo ? 'MYO Slot Created' : 'Character Created', 'Initial upload', 'user');

            // Update the user's FTO status and character count
            if (is_object($recipient)) {
                if (!$isMyo) {
                    $recipient->settings->is_fto = 0; // MYO slots don't affect the FTO status - YMMV
                }
                $recipient->settings->save();
            }

            // If the recipient has an account, send them a notification
            if (is_object($recipient) && $user->id != $recipient->id) {
                Notifications::create($isMyo ? 'MYO_GRANT' : 'CHARACTER_UPLOAD', $recipient, [
                    'character_url' => $character->url,
                ] + (
                    $isMyo ?
                    ['name' => $character->name] :
                    ['character_slug' => $character->slug]
                ));
            }

            if (!$this->logAdminAction($user, 'Created Character', 'Created '.$character->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($character);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Crops a thumbnail for the given image.
     *
     * @param array          $points
     * @param CharacterImage $characterImage
     * @param mixed          $isMyo
     */
    public function cropThumbnail($points, $characterImage, $isMyo = false) {
        $imageProperties = getimagesize($characterImage->imagePath.'/'.$characterImage->imageFileName);
        if ($imageProperties[0] > 2000 || $imageProperties[1] > 2000) {
            // For large images (in terms of dimensions),
            // use imagick instead, as it's better at handling them
            Config::set('image.driver', 'imagick');
        }

        $image = Image::make($characterImage->imagePath.'/'.$characterImage->imageFileName);

        if (!in_array(config('lorekeeper.settings.masterlist_image_format'), ['png', 'webp']) && config('lorekeeper.settings.masterlist_image_format') != null && config('lorekeeper.settings.masterlist_image_background') != null) {
            $canvas = Image::canvas($image->width(), $image->height(), config('lorekeeper.settings.masterlist_image_background'));
            $image = $canvas->insert($image, 'center');
            $trimColor = true;
        }

        if (config('lorekeeper.settings.watermark_masterlist_thumbnails') == 1 && !$isMyo) {
            // Trim transparent parts of image.
            $image->trim(isset($trimColor) && $trimColor ? 'top-left' : 'transparent');

            if (config('lorekeeper.settings.masterlist_image_automation') == 1) {
                // Make the image be square
                if ($image->width() > $image->height()) {
                    // Landscape
                    $canvas = Image::canvas($image->width(), $image->width());
                    $image = $canvas->insert($image, 'center');
                } else {
                    // Portrait
                    $canvas = Image::canvas($image->height(), $image->height());
                    $image = $canvas->insert($image, 'center');
                }
            }

            $cropWidth = config('lorekeeper.settings.masterlist_thumbnails.width');
            $cropHeight = config('lorekeeper.settings.masterlist_thumbnails.height');

            $imageWidthOld = $image->width();
            $imageHeightOld = $image->height();

            $trimOffsetX = $imageWidthOld - $image->width();
            $trimOffsetY = $imageHeightOld - $image->height();

            if (config('lorekeeper.settings.watermark_masterlist_images') == 1) {
                // Resize image if desired, so that the watermark is applied to the correct size of image
                if (config('lorekeeper.settings.masterlist_image_dimension') != 0) {
                    if ($image->width() > $image->height()) {
                        // Landscape
                        $image->resize(null, config('lorekeeper.settings.masterlist_image_dimension'), function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    } else {
                        // Portrait
                        $image->resize(config('lorekeeper.settings.masterlist_image_dimension'), null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }
                }
                // Watermark the image
                $watermark = Image::make('images/watermark.png');

                if (config('lorekeeper.settings.watermark_resizing_thumb') == 1) {
                    $imageWidth = $image->width();
                    $imageHeight = $image->height();

                    $wmWidth = $watermark->width();
                    $wmHeight = $watermark->height();

                    $wmScale = config('lorekeeper.settings.watermark_percent');

                    //Assume Landscape by Default
                    $maxSize = $imageWidth * $wmScale;

                    if ($imageWidth > $imageHeight) {
                        //Landscape
                        $maxSize = $imageWidth * $wmScale;
                    } else {
                        // Portrait
                        $maxSize = $imageHeight * $wmScale;
                    }

                    if ($wmWidth > $wmHeight) {
                        //Landscape
                        $watermark->resize($maxSize, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else {
                        // Portrait
                        $watermark->resize(null, $maxSize, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                }
                $image->insert($watermark, 'center');
            }
            // Now shrink the image

            $imageWidth = $image->width();
            $imageHeight = $image->height();

            if ($imageWidth > $imageHeight) {
                // Landscape
                $image->resize(null, $cropWidth, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } else {
                // Portrait
                $image->resize($cropHeight, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if (config('lorekeeper.settings.masterlist_image_automation') == 0) {
                $xOffset = 0 + (($points['x0'] - $trimOffsetX) > 0 ? ($points['x0'] - $trimOffsetX) : 0);
                if (($xOffset + $cropWidth) > $image->width()) {
                    $xOffsetNew = $cropWidth - ($image->width() - $xOffset);
                }
                if (isset($xOffsetNew)) {
                    if (($xOffsetNew + $cropWidth) > $image->width()) {
                        $xOffsetNew = $image->width() - $cropWidth;
                    }
                }
                $yOffset = 0 + (($points['y0'] - $trimOffsetY) > 0 ? ($points['y0'] - $trimOffsetY) : 0);
                if (($yOffset + $cropHeight) > $image->height()) {
                    $yOffsetNew = $cropHeight - ($image->height() - $yOffset);
                }
                if (isset($yOffsetNew)) {
                    if (($yOffsetNew + $cropHeight) > $image->height()) {
                        $yOffsetNew = $image->height() - $cropHeight;
                    }
                }

                // Crop according to the selected area
                $image->crop($cropWidth, $cropHeight, $xOffsetNew ?? $xOffset, $yOffsetNew ?? $yOffset);
            }
        } else {
            $cropWidth = $points['x1'] - $points['x0'];
            $cropHeight = $points['y1'] - $points['y0'];

            if (config('lorekeeper.settings.masterlist_image_automation') == 0) {
                // Crop according to the selected area
                $image->crop($cropWidth, $cropHeight, $points['x0'], $points['y0']);
            }

            // Resize to fit the thumbnail size
            $image->resize(config('lorekeeper.settings.masterlist_thumbnails.width'), config('lorekeeper.settings.masterlist_thumbnails.height'));
        }

        // Save the thumbnail
        $image->save($characterImage->thumbnailPath.'/'.$characterImage->thumbnailFileName, 100, config('lorekeeper.settings.masterlist_image_format'));
    }

    /**
     * Creates a character log.
     *
     * @param int    $senderId
     * @param string $senderUrl
     * @param int    $recipientId
     * @param string $recipientUrl
     * @param int    $characterId
     * @param string $type
     * @param string $data
     * @param string $logType
     * @param bool   $isUpdate
     * @param string $oldData
     * @param string $newData
     *
     * @return bool
     */
    public function createLog($senderId, $senderUrl, $recipientId, $recipientUrl, $characterId, $type, $data, $logType, $isUpdate = false, $oldData = null, $newData = null) {
        return DB::table($logType == 'character' ? 'character_log' : 'user_character_log')->insert(
            [
                'sender_id'     => $senderId,
                'sender_url'    => $senderUrl,
                'recipient_id'  => $recipientId,
                'recipient_url' => $recipientUrl,
                'character_id'  => $characterId,
                'log'           => $type.($data ? ' ('.$data.')' : ''),
                'log_type'      => $type,
                'data'          => $data,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ] + ($logType == 'character' ?
                [
                    'change_log' => $isUpdate ? json_encode([
                        'old' => $oldData,
                        'new' => $newData,
                    ]) : null,
                ] : [])
        );
    }

    /**
     * Creates a character image.
     *
     * @param array     $data
     * @param Character $character
     * @param User      $user
     *
     * @return bool|Character
     */
    public function createImage($data, $character, $user) {
        DB::beginTransaction();

        try {
            if (!$character->is_myo_slot) {
                if (!(isset($data['species_id']) && $data['species_id'])) {
                    throw new \Exception('Characters require a species.');
                }
                if (!(isset($data['rarity_id']) && $data['rarity_id'])) {
                    throw new \Exception('Characters require a rarity.');
                }
            }
            if (isset($data['subtype_id']) && $data['subtype_id']) {
                $subtype = Subtype::find($data['subtype_id']);
                if (!(isset($data['species_id']) && $data['species_id'])) {
                    throw new \Exception('Species must be selected to select a subtype.');
                }
                if (!$subtype || $subtype->species_id != $data['species_id']) {
                    throw new \Exception('Selected subtype invalid or does not match species.');
                }
            } else {
                $data['subtype_id'] = null;
            }

            $data['is_visible'] = 1;

            // Create character image
            $image = $this->handleCharacterImage($data, $character);
            if (!$image) {
                throw new \Exception('Error happened while trying to create image.');
            }

            // Update the character's image ID
            $character->character_image_id = $image->id;
            $character->save();

            if (!$this->logAdminAction($user, 'Created Image', 'Created character image <a href="'.$character->url.'">#'.$image->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, $character->user_id, ($character->user_id ? null : $character->owner_url), $character->id, 'Character Image Uploaded', '[#'.$image->id.']', 'character');

            // If the recipient has an account, send them a notification
            if ($character->user && $user->id != $character->user_id && $character->is_visible) {
                Notifications::create('IMAGE_UPLOAD', $character->user, [
                    'character_url'  => $character->url,
                    'character_slug' => $character->slug,
                    'character_name' => $character->name,
                    'sender_url'     => $user->url,
                    'sender_name'    => $user->name,
                ]);
            }

            // Notify bookmarkers
            $character->notifyBookmarkers('BOOKMARK_IMAGE');

            return $this->commitReturn($character);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a character image.
     *
     * @param array          $data
     * @param CharacterImage $image
     * @param User           $user
     *
     * @return bool
     */
    public function updateImageFeatures($data, $image, $user) {
        DB::beginTransaction();

        try {
            // Check that the subtype matches
            if (isset($data['subtype_id']) && $data['subtype_id']) {
                $subtype = Subtype::find($data['subtype_id']);
                if (!(isset($data['species_id']) && $data['species_id'])) {
                    throw new \Exception('Species must be selected to select a subtype.');
                }
                if (!$subtype || $subtype->species_id != $data['species_id']) {
                    throw new \Exception('Selected subtype invalid or does not match species.');
                }
            }

            if (!$this->logAdminAction($user, 'Updated Image', 'Updated character image features on <a href="'.$image->character->url.'">#'.$image->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            // Log old features
            $old = [];
            $old['features'] = $this->generateFeatureList($image);
            $old['species'] = $image->species_id ? $image->species->displayName : null;
            $old['subtype'] = $image->subtype_id ? $image->subtype->displayName : null;
            $old['rarity'] = $image->rarity_id ? $image->rarity->displayName : null;

            // Clear old features
            $image->features()->delete();

            // Attach features
            foreach ($data['feature_id'] as $key => $featureId) {
                if ($featureId) {
                    $feature = CharacterFeature::create(['character_image_id' => $image->id, 'feature_id' => $featureId, 'data' => $data['feature_data'][$key]]);
                }
            }

            // Update other stats
            $image->species_id = $data['species_id'];
            $image->subtype_id = $data['subtype_id'] ?: null;
            $image->rarity_id = $data['rarity_id'];
            $image->save();

            $new = [];
            $new['features'] = $this->generateFeatureList($image);
            $new['species'] = $image->species_id ? $image->species->displayName : null;
            $new['subtype'] = $image->subtype_id ? $image->subtype->displayName : null;
            $new['rarity'] = $image->rarity_id ? $image->rarity->displayName : null;

            // Character also keeps track of these features
            $image->character->rarity_id = $image->rarity_id;
            $image->character->save();

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Traits Updated', '#'.$image->id, 'character', true, $old, $new);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates image data.
     *
     * @param array          $data
     * @param CharacterImage $image
     * @param User           $user
     *
     * @return bool
     */
    public function updateImageNotes($data, $image, $user) {
        DB::beginTransaction();

        try {
            $old = $image->parsed_description;

            // Update the image's notes
            $image->description = $data['description'];
            $image->parsed_description = parse($data['description']);
            $image->save();

            if (!$this->logAdminAction($user, 'Updated Image Notes', 'Updated image <a href="'.$image->character->url.'">#'.$image->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Image Notes Updated', '[#'.$image->id.']', 'character', true, $old, $image->parsed_description);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates image credits.
     *
     * @param array          $data
     * @param CharacterImage $image
     * @param User           $user
     *
     * @return bool
     */
    public function updateImageCredits($data, $image, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Updated Image Credits', 'Updated character image credits on <a href="'.$image->character->url.'">#'.$image->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            $old = $this->generateCredits($image);

            // Clear old artists/designers
            $image->creators()->delete();

            // Check if entered url(s) have aliases associated with any on-site users
            $designers = array_filter($data['designer_url']); // filter null values
            foreach ($designers as $key=> $url) {
                $recipient = checkAlias($url, false);
                if (is_object($recipient)) {
                    $data['designer_id'][$key] = $recipient->id;
                    $designers[$key] = null;
                }
            }
            $artists = array_filter($data['artist_url']);  // filter null values
            foreach ($artists as $key=> $url) {
                $recipient = checkAlias($url, false);
                if (is_object($recipient)) {
                    $data['artist_id'][$key] = $recipient->id;
                    $artists[$key] = null;
                }
            }

            // Check that users with the specified id(s) exist on site
            foreach ($data['designer_id'] as $id) {
                if (isset($id) && $id) {
                    $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more designers is invalid.');
                    }
                }
            }
            foreach ($data['artist_id'] as $id) {
                if (isset($id) && $id) {
                    $user = $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more artists is invalid.');
                    }
                }
            }

            // Attach artists/designers
            foreach ($data['designer_id'] as $key => $id) {
                if ($id || $data['designer_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $image->id,
                        'type'               => 'Designer',
                        'url'                => $data['designer_url'][$key],
                        'user_id'            => $id,
                        'credit_type'        => $data['designer_type'][$key] ?? null,
                    ]);
                }
            }
            foreach ($data['artist_id'] as $key => $id) {
                if ($id || $data['artist_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $image->id,
                        'type'               => 'Artist',
                        'url'                => $data['artist_url'][$key],
                        'user_id'            => $id,
                        'credit_type'        => $data['artist_type'][$key] ?? null,
                    ]);
                }
            }

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Image Credits Updated', '[#'.$image->id.']', 'character', true, $old, $this->generateCredits($image));

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Reuploads an image.
     *
     * @param array          $data
     * @param CharacterImage $image
     * @param User           $user
     *
     * @return bool
     */
    public function reuploadImage($data, $image, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Reuploaded Image', 'Reuploaded character image <a href="'.$image->character->url.'">#'.$image->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            if (config('lorekeeper.settings.masterlist_image_format') != null) {
                // Remove old versions so that images in various filetypes don't pile up
                if (file_exists($image->imagePath.'/'.$image->imageFileName)) {
                    unlink($image->imagePath.'/'.$image->imageFileName);
                }
                if (isset($image->fullsize_hash) ? file_exists(public_path($image->imageDirectory.'/'.$image->fullsizeFileName)) : false) {
                    if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                        unlink($image->imagePath.'/'.$image->fullsizeFileName);
                    }
                }
                if (file_exists($image->imagePath.'/'.$image->thumbnailFileName)) {
                    unlink($image->imagePath.'/'.$image->thumbnailFileName);
                }

                // Set the image's extension in the DB as defined in settings
                $image->extension = config('lorekeeper.settings.masterlist_image_format');
                $image->save();
            } else {
                // Get uploaded image's extension and save it to the DB
                $image->extension = $data['image']->getClientOriginalExtension();
                $image->save();
            }

            // Save image
            $this->handleImage($data['image'], $image->imageDirectory, $image->imageFileName);

            $isMyo = $image->character->is_myo_slot ? true : false;
            // Save thumbnail
            if (isset($data['use_cropper'])) {
                $this->cropThumbnail(Arr::only($data, ['x0', 'x1', 'y0', 'y1']), $image, $isMyo);
            } else {
                $this->handleImage($data['thumbnail'], $image->thumbnailPath, $image->thumbnailFileName);
            }

            // Process and save the image itself
            if (!$isMyo) {
                $this->processImage($image);
            }

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Image Reuploaded', '[#'.$image->id.']', 'character');

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an image.
     *
     * @param CharacterImage $image
     * @param User           $user
     * @param bool           $forceDelete
     *
     * @return bool
     */
    public function deleteImage($image, $user, $forceDelete = false) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Deleted Image', 'Deleted character image <a href="'.$image->character->url.'">#'.$image->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            if (!$forceDelete && $image->character->character_image_id == $image->id) {
                throw new \Exception("Cannot delete a character's active image.");
            }

            $image->features()->delete();

            $image->delete();

            // Delete the image files
            if (file_exists($image->imagePath.'/'.$image->imageFileName)) {
                unlink($image->imagePath.'/'.$image->imageFileName);
            }
            if (isset($image->fullsize_hash) ? file_exists(public_path($image->imageDirectory.'/'.$image->fullsizeFileName)) : false) {
                if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                    unlink($image->imagePath.'/'.$image->fullsizeFileName);
                }
            }
            if (file_exists($image->imagePath.'/'.$image->thumbnailFileName)) {
                unlink($image->imagePath.'/'.$image->thumbnailFileName);
            }

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Image Deleted', '[#'.$image->id.']', 'character');

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates image settings.
     *
     * @param array          $data
     * @param CharacterImage $image
     * @param User           $user
     *
     * @return bool
     */
    public function updateImageSettings($data, $image, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Updated Image', 'Updated character image settings on <a href="'.$image->character->url.'">#'.$image->id.'</a>')) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image->character->character_image_id == $image->id && !isset($data['is_visible'])) {
                throw new \Exception("Cannot hide a character's active image.");
            }

            $image->is_valid = isset($data['is_valid']);
            $image->is_visible = isset($data['is_visible']);
            $image->save();

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Image Visibility/Validity Updated', '[#'.$image->id.']', 'character');

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a character's active image.
     *
     * @param CharacterImage $image
     * @param User           $user
     *
     * @return bool
     */
    public function updateActiveImage($image, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Updated Image', 'Set image <a href="'.$image->character->url.'">#'.$image->id.'</a> to active image')) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image->character->character_image_id == $image->id) {
                return true;
            }
            if (!$image->is_visible) {
                throw new \Exception("Cannot set a non-visible image as the character's active image.");
            }

            $image->character->character_image_id = $image->id;
            $image->character->save();

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Active Image Updated', '[#'.$image->id.']', 'character');

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts a character's images.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $character
     *
     * @return bool
     */
    public function sortImages($data, $character, $user) {
        DB::beginTransaction();

        try {
            $ids = explode(',', $data['sort']);
            $images = CharacterImage::whereIn('id', $ids)->where('character_id', $character->id)->orderBy(DB::raw('FIELD(id, '.implode(',', $ids).')'))->get();

            if (count($images) != count($ids)) {
                throw new \Exception('Invalid image included in sorting order.');
            }
            if (!$images->first()->is_visible) {
                throw new \Exception("Cannot set a non-visible image as the character's active image.");
            }

            $count = 0;
            foreach ($images as $image) {
                //if($count == 1)
                //{
                //    // Set the first one as the active image
                //    $image->character->image_id = $image->id;
                //    $image->character->save();
                //}
                $image->sort = $count;
                $image->save();
                $count++;
            }

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $image->character_id, 'Image Order Updated', '', 'character');

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts a user's characters.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function sortCharacters($data, $user) {
        DB::beginTransaction();

        try {
            $ids = array_reverse(explode(',', $data['sort']));
            $characters = Character::myo(0)->whereIn('id', $ids)->where('user_id', $user->id)->where('is_visible', 1)->orderBy(DB::raw('FIELD(id, '.implode(',', $ids).')'))->get();

            if (count($characters) != count($ids)) {
                throw new \Exception('Invalid character included in sorting order.');
            }

            $count = 0;
            foreach ($characters as $character) {
                $character->sort = $count;
                $character->save();
                $count++;
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts a character's pets.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function sortCharacterPets($data, $user) {
        DB::beginTransaction();

        try {
            $ids = array_reverse(explode(',', $data['sort']));
            $pets = UserPet::whereIn('id', $ids)->where('user_id', $user->id)->orderBy(DB::raw('FIELD(id, '.implode(',', $ids).')'))->get();

            if (count($pets) != count($ids)) {
                throw new \Exception('Invalid pet included in sorting order.');
            }

            $count = 0;
            foreach ($pets as $pet) {
                $pet->sort = $count;
                $pet->save();
                $count++;
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a character's stats.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $character
     *
     * @return bool
     */
    public function updateCharacterStats($data, $character, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Updated Stats', 'Updated character stats on '.$character->displayname)) {
                throw new \Exception('Failed to log admin action.');
            }

            if (!$character->is_myo_slot && Character::where('slug', $data['slug'])->where('id', '!=', $character->id)->exists()) {
                throw new \Exception('Character code must be unique.');
            }

            $characterData = Arr::only($data, [
                'character_category_id',
                'number', 'slug',
            ]);
            $characterData['is_sellable'] = isset($data['is_sellable']);
            $characterData['is_tradeable'] = isset($data['is_tradeable']);
            $characterData['is_giftable'] = isset($data['is_giftable']);
            $characterData['sale_value'] = $data['sale_value'] ?? 0;
            $characterData['transferrable_at'] = $data['transferrable_at'] ?? null;
            if ($character->is_myo_slot) {
                $characterData['name'] = (isset($data['name']) && $data['name']) ? $data['name'] : null;
            }

            // Needs to be cleaned up
            $result = [];
            $old = [];
            $new = [];
            if (!$character->is_myo_slot) {
                if ($characterData['character_category_id'] != $character->character_category_id) {
                    $result[] = 'character category';
                    $old['character_category'] = $character->category->displayName;
                    $new['character_category'] = CharacterCategory::find($characterData['character_category_id'])->displayName;
                }
                if ($characterData['number'] != $character->number) {
                    $result[] = 'character number';
                    $old['number'] = $character->number;
                    $new['number'] = $characterData['number'];
                }
                if ($characterData['slug'] != $character->slug) {
                    $result[] = 'character code';
                    $old['slug'] = $character->slug;
                    $new['slug'] = $characterData['slug'];
                }
            } else {
                if ($characterData['name'] != $character->name) {
                    $result[] = 'name';
                    $old['name'] = $character->name;
                    $new['name'] = $characterData['name'];
                }
            }
            if ($characterData['is_sellable'] != $character->is_sellable) {
                $result[] = 'sellable status';
                $old['is_sellable'] = $character->is_sellable;
                $new['is_sellable'] = $characterData['is_sellable'];
            }
            if ($characterData['is_tradeable'] != $character->is_tradeable) {
                $result[] = 'tradeable status';
                $old['is_tradeable'] = $character->is_tradeable;
                $new['is_tradeable'] = $characterData['is_tradeable'];
            }
            if ($characterData['is_giftable'] != $character->is_giftable) {
                $result[] = 'giftable status';
                $old['is_giftable'] = $character->is_giftable;
                $new['is_giftable'] = $characterData['is_giftable'];
            }
            if ($characterData['sale_value'] != $character->sale_value) {
                $result[] = 'sale value';
                $old['sale_value'] = $character->sale_value;
                $new['sale_value'] = $characterData['sale_value'];
            }
            if ($characterData['transferrable_at'] != $character->transferrable_at) {
                $result[] = 'transfer cooldown';
                $old['transferrable_at'] = $character->transferrable_at;
                $new['transferrable_at'] = $characterData['transferrable_at'];
            }

            if (count($result)) {
                $character->update($characterData);

                // Add a log for the character
                // This logs all the updates made to the character
                $this->createLog($user->id, null, null, null, $character->id, 'Character Updated', ucfirst(implode(', ', $result)).' edited', 'character', true, $old, $new);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a character's description.
     *
     * @param array     $data
     * @param Character $character
     * @param User      $user
     *
     * @return bool
     */
    public function updateCharacterDescription($data, $character, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Updated Character Description', 'Updated character description on '.$character->displayname)) {
                throw new \Exception('Failed to log admin action.');
            }

            $old = $character->parsed_description;

            // Update the image's notes
            $character->description = $data['description'];
            $character->parsed_description = parse($data['description']);
            $character->save();

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $character->id, 'Character Description Updated', '', 'character', true, $old, $character->parsed_description);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a character's settings.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $character
     *
     * @return bool
     */
    public function updateCharacterSettings($data, $character, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Updated Character Settings', 'Updated character settings on '.$character->displayname)) {
                throw new \Exception('Failed to log admin action.');
            }

            $old = ['is_visible' => $character->is_visible];

            $character->is_visible = isset($data['is_visible']);
            $character->save();

            // Add a log for the character
            // This logs all the updates made to the character
            $this->createLog($user->id, null, null, null, $character->id, 'Character Visibility Updated', '', 'character', true, $old, ['is_visible' => $character->is_visible]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a character's profile.
     *
     * @param array     $data
     * @param Character $character
     * @param User      $user
     * @param bool      $isAdmin
     *
     * @return bool
     */
    public function updateCharacterProfile($data, $character, $user, $isAdmin = false) {
        DB::beginTransaction();

        try {
            $notifyTrading = false;
            $notifyGiftArt = false;
            $notifyGiftWriting = false;

            // Allow updating the gift art/trading options if the editing
            // user owns the character
            if (!$isAdmin) {
                if ($character->user_id != $user->id) {
                    throw new \Exception('You cannot edit this character.');
                }

                if ($character->is_trading != isset($data['is_trading'])) {
                    $notifyTrading = true;
                }
                if (isset($data['is_gift_art_allowed']) && $character->is_gift_art_allowed != $data['is_gift_art_allowed']) {
                    $notifyGiftArt = true;
                }
                if (isset($data['is_gift_writing_allowed']) && $character->is_gift_writing_allowed != $data['is_gift_writing_allowed']) {
                    $notifyGiftWriting = true;
                }

                $character->is_gift_art_allowed = isset($data['is_gift_art_allowed']) && $data['is_gift_art_allowed'] <= 2 ? $data['is_gift_art_allowed'] : 0;
                $character->is_gift_writing_allowed = isset($data['is_gift_writing_allowed']) && $data['is_gift_writing_allowed'] <= 2 ? $data['is_gift_writing_allowed'] : 0;
                $character->is_trading = isset($data['is_trading']);
                $character->save();
            } else {
                if (!$this->logAdminAction($user, 'Updated Character Profile', 'Updated character profile on '.$character->displayname)) {
                    throw new \Exception('Failed to log admin action.');
                }
            }

            // Update the character's profile
            if (!$character->is_myo_slot) {
                $character->name = $data['name'];
            }
            $character->save();

            // Update the character's location
            if (isset($data['location']) && $data['location']) {
                $character->home_id = (int) $data['location'];
            }
            $character->save();

            // Update the character's faction
            if (isset($data['faction']) && $data['faction']) {
                if ($character->faction_id) {
                    $old = $character->faction_id;
                }

                $character->faction_id = (int) $data['faction'];
                $character->save();

                // Reset standing/remove from closed rank
                if (isset($old) && $character->faction_id != $old) {
                    $standing = $character->getCurrencies(true)->where('id', Settings::get('WE_faction_currency'))->first();
                    if ($standing && $standing->quantity > 0) {
                        if (!$debit = (new CurrencyManager)->debitCurrency($character, null, 'Changed Factions', null, $standing, $standing->quantity)) {
                            throw new \Exception('Failed to reset standing.');
                        }
                    }

                    if (FactionRankMember::where('member_type', 'character')->where('member_id', $character->id)->first()) {
                        FactionRankMember::where('member_type', 'character')->where('member_id', $character->id)->first()->delete();
                    }
                }
            }

            if (!$character->is_myo_slot && Config::get('lorekeeper.extensions.character_TH_profile_link')) {
                $character->profile->link = $data['link'];
            }

            $character->profile->save();

            $character->profile->text = $data['text'];
            $character->profile->parsed_text = parse($data['text']);
            $character->profile->save();

            if ($isAdmin && isset($data['alert_user']) && $character->is_visible && $character->user_id) {
                Notifications::create('CHARACTER_PROFILE_EDIT', $character->user, [
                    'character_name' => $character->name,
                    'character_slug' => $character->is_myo_slot ? $character->id : $character->slug,
                    'sender_url'     => $user->url,
                    'sender_name'    => $user->name,
                ]);
            }

            if ($notifyTrading) {
                $character->notifyBookmarkers('BOOKMARK_TRADING');
            }
            if ($notifyGiftArt) {
                $character->notifyBookmarkers('BOOKMARK_GIFTS');
            }
            if ($notifyGiftWriting) {
                $character->notifyBookmarkers('BOOKMARK_GIFT_WRITING');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a character's lineage.
     *
     * @param array     $data
     * @param Character $character
     * @param User      $user
     * @param bool      $isAdmin
     *
     * @return bool
     */
    public function updateCharacterLineage($data, $character, $user, $isAdmin = false) {
        DB::beginTransaction();

        try {
            if (!$user->hasPower('manage_characters')) {
                throw new \Exception('You do not have the required permissions to do this.');
            }
            $roots = [
                'sire',
                'sire_sire', 'sire_sire_sire', 'sire_sire_dam',
                'sire_dam',  'sire_dam_sire',  'sire_dam_dam',
                'dam',
                'dam_sire',  'dam_sire_sire',  'dam_sire_dam',
                'dam_dam',   'dam_dam_sire',   'dam_dam_dam',
            ];
            // you don't need to look for great-great-grandparents
            $shortlist = [
                'sire', 'sire_sire', 'sire_dam',
                'dam', 'dam_sire', 'dam_dam',
            ];
            $line = null;
            $skipFlag = false;

            // Check if we need to create a lineage bc this character doesn't have one.
            if (!$character->lineage) {
                $line = $this->handleCharacterLineage($data, $character, $character->is_myo_slot);
                // tells us we don't need to calculate ancestors as handleCharacterLineage already does
                $skipFlag = true;
            } else {
                // Grab the character's existing lineage
                $line = $character->lineage;
            }

            // If we have a lineage already, and didn't just create one, then update it.
            if (!$skipFlag) {
                // Checking inputs ?
                for ($i = 0; $i < 14; $i++) {
                    // if isset Data key_id, set Lineage key_id and check if that character exists?
                    // else if isset Data key_name, set Lineage key_name to that.
                    if (isset($data[$roots[$i].'_id'])) {
                        $id = $data[$roots[$i].'_id'];
                        $line[$roots[$i].'_id'] = $id;
                        $char = Character::find($id);

                        // TODO Set name to be the slug of the character.
                        $line[$roots[$i].'_name'] = $char->slug;
                    } elseif (isset($data[$roots[$i].'_name'])) {
                        $line[$roots[$i].'_name'] = $data[$roots[$i].'_name'];
                    } else {
                        // EG. someone deleted it, so we erase it.
                        $line[$roots[$i].'_id'] = null;
                        $line[$roots[$i].'_name'] = null;
                    }
                }
            }

            // If generate_ancestors is set and we didn't just create a new lineage ...
            if (!$skipFlag && isset($data['generate_ancestors'])) {
                // for each of this character's shortlist of ancestors...
                for ($j = 0; $j < 6; $j++) {
                    $key = $shortlist[$j];
                    $id = $data[$key.'_id'] ?? null;

                    // check if this is a character id and not null
                    if ($id !== null) {
                        // check if this exists and has lineage
                        $char = Character::find($id);
                        if ($char->exists() && $char->lineage !== null) {
                            // go through their parents and gparents
                            for ($k = 0; $k < 6; $k++) {
                                // checks that this is a valid lineage index
                                // eg. sire_sire_sire and not sire_sire_sire_sire
                                $key2 = $key.'_'.$shortlist[$k];
                                if (in_array($key2, $roots, true)) {
                                    $line[$key2.'_id'] = $char->lineage[$shortlist[$k].'_id'];
                                    $line[$key2.'_name'] = $char->lineage[$shortlist[$k].'_name'];
                                }
                            }
                        } else {
                            for ($k = 0; $k < 6; $k++) {
                                $key2 = $key.'_'.$shortlist[$k];
                                if (in_array($key2, $roots, true)) {
                                    $line[$key2.'_id'] = null;
                                    $line[$key2.'_name'] = null;
                                }
                            }
                        }
                    }
                }
            }

            // if this request wants to update descendants
            if (isset($data['update_descendants'])) {
                // TODO: See if there's a better way to query this, because it is HELL on the database.

                // find the descendants of this character
                $children = CharacterLineage::query()
                    ->where('sire_id', $character->id)
                    ->orWhere('sire_sire_id', $character->id)
                    ->orWhere('sire_dam_id', $character->id)
                    ->orWhere('dam_id', $character->id)
                    ->orWhere('dam_dam_id', $character->id)
                    ->orWhere('dam_sire_id', $character->id)
                    ->get();

                // go through each descendant
                foreach ($children as $child) {
                    // search the lineage to find which ancestor this character is
                    for ($k = 0; $k < 6; $k++) {
                        if ($child[$shortlist[$k].'_id'] == $character->id) {
                            for ($j = 0; $j < 6; $j++) {
                                $key = $shortlist[$k].'_'.$shortlist[$j];
                                if (in_array($key, $roots, true)) {
                                    $child[$key.'_id'] = $line[$shortlist[$j].'_id'];
                                    $child[$key.'_name'] = $line[$shortlist[$j].'_name'];
                                }
                            }
                        }
                    }
                    // save the changes
                    $child->save();
                }
            }
            // and we're done!
            if (!$skipFlag) {
                $character->lineage->save();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a character.
     *
     * @param Character $character
     * @param User      $user
     *
     * @return bool
     */
    public function deleteCharacter($character, $user) {
        DB::beginTransaction();

        try {
            if (SalesCharacter::where('character_id', $character->id)->exists()) {
                throw new \Exception('This character currently exists in a previous sale post and cannot be deleted.');
            }
            if ($character->user_id) {
                $character->user->settings->save();
            }

            if (!$this->logAdminAction($user, 'Deleted Character', 'Deleted character '.$character->slug)) {
                throw new \Exception('Failed to log admin action.');
            }

            // Delete associated bookmarks
            CharacterBookmark::where('character_id', $character->id)->delete();

            // Delete associated features and images
            // Images use soft deletes
            foreach ($character->images as $image) {
                $image->features()->delete();
                $image->delete();
            }

            // Delete associated currencies
            CharacterCurrency::where('character_id', $character->id)->delete();

            // Delete associated design updates
            // Design updates use soft deletes
            CharacterDesignUpdate::where('character_id', $character->id)->delete();

            // Delete character
            // This is a soft delete, so the character still kind of exists
            $character->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a character transfer.
     *
     * @param array     $data
     * @param Character $character
     * @param User      $user
     *
     * @return bool
     */
    public function createTransfer($data, $character, $user) {
        DB::beginTransaction();

        try {
            if ($user->id != $character->user_id) {
                throw new \Exception('You do not own this character.');
            }
            if (!$character->is_sellable && !$character->is_tradeable && !$character->is_giftable) {
                throw new \Exception('This character is not transferrable.');
            }
            if ($character->transferrable_at && $character->transferrable_at->isFuture()) {
                throw new \Exception('This character is still on transfer cooldown and cannot be transferred.');
            }
            if (CharacterTransfer::active()->where('character_id', $character->id)->exists()) {
                throw new \Exception('This character is in an active transfer.');
            }
            if ($character->trade_id) {
                throw new \Exception('This character is in an active trade.');
            }

            $recipient = User::find($data['recipient_id']);
            if (!$recipient) {
                throw new \Exception('Invalid user selected.');
            }
            if ($recipient->is_banned) {
                throw new \Exception('Cannot transfer character to a banned member.');
            }

            if ($character->pets()->exists()) {
                throw new \Exception('This character has pets attached to it.');
            }
            if ($character->weapons()->exists()) {
                throw new \Exception('This character has weapons attached to it.');
            }
            if ($character->gear()->exists()) {
                throw new \Exception('This character has gear attached to it.');
            }

            // deletes any pending design drafts
            foreach ($character->designUpdate as $update) {
                if ($update->status == 'Draft') {
                    if (!(new DesignUpdateManager)->rejectRequest('Cancelled by '.$user->displayName.' in order to transfer character to another user', $update, $user, true, false)) {
                        throw new \Exception('Could not cancel pending request.');
                    }
                }
            }

            $queueOpen = Settings::get('open_transfers_queue');

            CharacterTransfer::create([
                'user_reason'  => $data['user_reason'],  // pulls from this characters user_reason collum
                'character_id' => $character->id,
                'sender_id'    => $user->id,
                'recipient_id' => $recipient->id,
                'status'       => 'Pending',

                // if the queue is closed, all transfers are auto-approved
                'is_approved'  => !$queueOpen,
            ]);

            if (!$queueOpen) {
                Notifications::create('CHARACTER_TRANSFER_RECEIVED', $recipient, [
                    'character_url'  => $character->url,
                    'character_name' => $character->slug,
                    'sender_name'    => $user->name,
                    'sender_url'     => $user->url,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Forces an admin transfer of a character.
     *
     * @param array     $data
     * @param Character $character
     * @param User      $user
     *
     * @return bool
     */
    public function adminTransfer($data, $character, $user) {
        DB::beginTransaction();

        try {
            if ($character->pets()->exists()) {
                throw new \Exception('This character has pets attached to it.');
            }
            if ($character->weapons()->exists()) {
                throw new \Exception('This character has weapons attached to it.');
            }
            if ($character->gear()->exists()) {
                throw new \Exception('This character has gear attached to it.');
            }

            if (isset($data['recipient_id']) && $data['recipient_id']) {
                $recipient = User::find($data['recipient_id']);
                if (!$recipient) {
                    throw new \Exception('Invalid user selected.');
                }
                if ($character->user_id == $recipient->id) {
                    throw new \Exception('Cannot transfer a character to the same user.');
                }
                if (!$this->logAdminAction($user, 'Admin Transfer', 'Admin transferred '.$character->displayname.' to '.$recipient->displayName)) {
                    throw new \Exception('Failed to log admin action.');
                }
            } elseif (isset($data['recipient_url']) && $data['recipient_url']) {
                // Transferring to an off-site user
                $recipient = checkAlias($data['recipient_url']);
                if (!$this->logAdminAction($user, 'Admin Transfer', 'Admin transferred '.$character->displayname.' to '.$recipient)) {
                    throw new \Exception('Failed to log admin action.');
                }
            } else {
                throw new \Exception('Please enter a recipient for the transfer.');
            }

            // If the character is in an active transfer, cancel it
            $transfer = CharacterTransfer::active()->where('character_id', $character->id)->first();
            if ($transfer) {
                $transfer->status = 'Canceled';
                $transfer->reason = 'Transfer canceled by '.$user->displayName.' in order to transfer character to another user';
                $transfer->save();
            }
            // deletes any pending design drafts
            foreach ($character->designUpdate as $update) {
                if ($update->status == 'Draft') {
                    if (!(new DesignUpdateManager)->rejectRequest('Cancelled by '.$user->displayName.' in order to transfer character to another user', $update, $user, true, false)) {
                        throw new \Exception('Could not cancel pending request.');
                    }
                }
            }

            $sender = $character->user;

            $this->moveCharacter($character, $recipient, 'Transferred by '.$user->displayName.(isset($data['reason']) ? ': '.$data['reason'] : ''), $data['cooldown'] ?? -1);

            // Add notifications for the old and new owners
            if ($sender) {
                Notifications::create('CHARACTER_SENT', $sender, [
                    'character_name' => $character->slug,
                    'character_slug' => $character->slug,
                    'sender_name'    => $user->name,
                    'sender_url'     => $user->url,
                    'recipient_name' => is_object($recipient) ? $recipient->name : prettyProfileName($recipient),
                    'recipient_url'  => is_object($recipient) ? $recipient->url : $recipient,
                ]);
            }
            if (is_object($recipient)) {
                Notifications::create('CHARACTER_RECEIVED', $recipient, [
                    'character_name' => $character->slug,
                    'character_slug' => $character->slug,
                    'sender_name'    => $user->name,
                    'sender_url'     => $user->url,
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes a character transfer.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function processTransfer($data, $user) {
        DB::beginTransaction();

        try {
            $transfer = CharacterTransfer::where('id', $data['transfer_id'])->active()->where('recipient_id', $user->id)->first();
            if (!$transfer) {
                throw new \Exception('Invalid transfer selected.');
            }

            if ($data['action'] == 'Accept') {
                $cooldown = Settings::get('transfer_cooldown');

                $transfer->status = 'Accepted';

                // Process the character move if the transfer has already been approved
                if ($transfer->is_approved) {
                    //check the cooldown saved
                    if (isset($transfer->data['cooldown'])) {
                        $cooldown = $transfer->data['cooldown'];
                    }
                    $this->moveCharacter($transfer->character, $transfer->recipient, 'User Transfer', $cooldown);
                    if (!Settings::get('open_transfers_queue')) {
                        $transfer->data = json_encode([
                            'cooldown' => $cooldown,
                            'staff_id' => null,
                        ]);
                    }

                    // Notify sender of the successful transfer
                    Notifications::create('CHARACTER_TRANSFER_ACCEPTED', $transfer->sender, [
                        'character_name' => $transfer->character->slug,
                        'character_url'  => $transfer->character->url,
                        'sender_name'    => $transfer->recipient->name,
                        'sender_url'     => $transfer->recipient->url,
                    ]);
                }
            } else {
                $transfer->status = 'Rejected';
                $transfer->data = json_encode([
                    'staff_id' => null,
                ]);

                // Notify sender that transfer has been rejected
                Notifications::create('CHARACTER_TRANSFER_REJECTED', $transfer->sender, [
                    'character_name' => $transfer->character->slug,
                    'character_url'  => $transfer->character->url,
                    'sender_name'    => $transfer->recipient->name,
                    'sender_url'     => $transfer->recipient->url,
                ]);
            }
            $transfer->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Cancels a character transfer.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function cancelTransfer($data, $user) {
        DB::beginTransaction();

        try {
            $transfer = CharacterTransfer::where('id', $data['transfer_id'])->active()->where('sender_id', $user->id)->first();
            if (!$transfer) {
                throw new \Exception('Invalid transfer selected.');
            }

            $transfer->status = 'Canceled';
            $transfer->save();

            // Notify recipient of the cancelled transfer
            Notifications::create('CHARACTER_TRANSFER_CANCELED', $transfer->recipient, [
                'character_name' => $transfer->character->slug,
                'character_url'  => $transfer->character->url,
                'sender_name'    => $transfer->sender->name,
                'sender_url'     => $transfer->sender->url,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes a character transfer in the approvals queue.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function processTransferQueue($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['transfer_id'])) {
                $transfer = CharacterTransfer::where('id', $data['transfer_id'])->active()->first();
            } else {
                $transfer = $data['transfer'];
            }
            if (!$transfer) {
                throw new \Exception('Invalid transfer selected.');
            }

            if ($data['action'] == 'Approve') {
                $transfer->is_approved = 1;
                $transfer->data = json_encode([
                    'staff_id' => $user->id,
                    'cooldown' => $data['cooldown'] ?? Settings::get('transfer_cooldown'),
                ]);

                // Process the character move if the recipient has already accepted the transfer
                if ($transfer->status == 'Accepted') {
                    if (!$this->logAdminAction($user, 'Approved Transfer', 'Approved transfer of '.$transfer->character->displayname.' to '.$transfer->recipient->displayname)) {
                        throw new \Exception('Failed to log admin action.');
                    }
                    $this->moveCharacter($transfer->character, $transfer->recipient, 'User Transfer', $data['cooldown'] ?? -1);

                    // Notify both parties of the successful transfer
                    Notifications::create('CHARACTER_TRANSFER_APPROVED', $transfer->sender, [
                        'character_name' => $transfer->character->slug,
                        'character_url'  => $transfer->character->url,
                        'sender_name'    => $user->name,
                        'sender_url'     => $user->url,
                    ]);
                    Notifications::create('CHARACTER_TRANSFER_APPROVED', $transfer->recipient, [
                        'character_name' => $transfer->character->slug,
                        'character_url'  => $transfer->character->url,
                        'sender_name'    => $user->name,
                        'sender_url'     => $user->url,
                    ]);
                } else {
                    if (!$this->logAdminAction($user, 'Approved Transfer', 'Approved transfer of '.$transfer->character->displayname.' to '.$transfer->recipient->displayname)) {
                        throw new \Exception('Failed to log admin action.');
                    }

                    // Still pending a response from the recipient
                    Notifications::create('CHARACTER_TRANSFER_ACCEPTABLE', $transfer->recipient, [
                        'character_name' => $transfer->character->slug,
                        'character_url'  => $transfer->character->url,
                        'sender_name'    => $user->name,
                        'sender_url'     => $user->url,
                    ]);
                }
            } else {
                if (!$this->logAdminAction($user, 'Rejected Transfer', 'Rejected transfer of '.$transfer->character->displayname.' to '.$transfer->recipient->displayname)) {
                    throw new \Exception('Failed to log admin action.');
                }

                $transfer->status = 'Rejected';
                $transfer->reason = $data['reason'] ?? null;
                $transfer->data = json_encode([
                    'staff_id' => $user->id,
                ]);

                // Notify both parties that the request was denied
                Notifications::create('CHARACTER_TRANSFER_DENIED', $transfer->sender, [
                    'character_name' => $transfer->character->slug,
                    'character_url'  => $transfer->character->url,
                    'sender_name'    => $user->name,
                    'sender_url'     => $user->url,
                ]);
                Notifications::create('CHARACTER_TRANSFER_DENIED', $transfer->recipient, [
                    'character_name' => $transfer->character->slug,
                    'character_url'  => $transfer->character->url,
                    'sender_name'    => $user->name,
                    'sender_url'     => $user->url,
                ]);
            }
            $transfer->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Moves a character from one user to another.
     *
     * @param Character $character
     * @param User      $recipient
     * @param string    $data
     * @param int       $cooldown
     * @param string    $logType
     */
    public function moveCharacter($character, $recipient, $data, $cooldown = -1, $logType = null) {
        $sender = $character->user;
        if (!$sender) {
            $sender = $character->owner_url;
        }

        // Update character counts if the sender has an account
        if (is_object($sender)) {
            $sender->settings->save();
        }

        if (is_object($recipient)) {
            if (!$character->is_myo_slot) {
                $recipient->settings->is_fto = 0;
            }
            $recipient->settings->save();
        }

        // Update character owner, sort order and cooldown
        $character->sort = 0;
        if (is_object($recipient)) {
            $character->user_id = $recipient->id;
            $character->owner_url = null;
        } else {
            $character->owner_url = $recipient;
            $character->user_id = null;
        }
        if ($cooldown < 0) {
            // Add the default amount from settings
            $cooldown = Settings::get('transfer_cooldown');
        }
        if ($cooldown > 0) {
            if ($character->transferrable_at && $character->transferrable_at->isFuture()) {
                $character->transferrable_at->addDays($cooldown);
            } else {
                $character->transferrable_at = Carbon::now()->addDays($cooldown);
            }
        }
        $character->save();

        // Notify bookmarkers
        $character->notifyBookmarkers('BOOKMARK_OWNER');

        if (config('lorekeeper.settings.reset_character_status_on_transfer')) {
            // Reset trading status, gift art status, and writing status
            $character->update([
                'is_gift_art_allowed'     => 0,
                'is_gift_writing_allowed' => 0,
                'is_trading'              => 0,
            ]);
        }

        if (config('lorekeeper.settings.reset_character_profile_on_transfer') && !$character->is_myo_slot) {
            // Reset name and profile
            $character->update(['name' => null]);

            // Reset profile
            $character->profile->update([
                'text'        => null,
                'parsed_text' => null,
            ]);
        }

        // Add a log for the ownership change
        $this->createLog(
            is_object($sender) ? $sender->id : null,
            is_object($sender) ? null : $sender,
            $recipient && is_object($recipient) ? $recipient->id : null,
            $recipient && is_object($recipient) ? $recipient->url : ($recipient ?: null),
            $character->id,
            $logType ? $logType : ($character->is_myo_slot ? 'MYO Slot Transferred' : 'Character Transferred'),
            $data,
            'user'
        );
    }

    /*************************************************************************************
     * CLAYMORE
     *************************************************************************************/

    /**
     * Edits a character's class.
     *
     * @param array                 $data
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     * @param bool                  $isAdmin
     *
     * @return bool
     * @return bool
     */
    public function saveRequestImage($data, $request, $isAdmin = false) {
        DB::beginTransaction();

        try {
            // Require an image to be uploaded the first time, but if an image already exists, allow user to update the other details
            if (!$isAdmin && !isset($data['image']) && !file_exists($request->imagePath.'/'.$request->imageFileName)) {
                throw new \Exception('Please upload a valid image.');
            }

            // Require a thumbnail to be uploaded the first time as well
            if (!file_exists($request->thumbnailPath.'/'.$request->thumbnailFileName)) {
                // If the crop dimensions are invalid...
                // The crop function resizes the thumbnail to fit, so we only need to check that it's not null
                if (!$isAdmin || ($isAdmin && isset($data['modify_thumbnail']))) {
                    if (isset($data['use_cropper']) && ($data['x0'] === null || $data['x1'] === null || $data['y0'] === null || $data['y1'] === null)) {
                        throw new \Exception('Invalid crop dimensions specified.');
                    }
                    if (!isset($data['use_cropper']) && !isset($data['thumbnail'])) {
                        throw new \Exception('Please upload a valid thumbnail or use the image cropper.');
                    }
                }
            }
            if (!$isAdmin || ($isAdmin && isset($data['modify_thumbnail']))) {
                $imageData = [];
                if (isset($data['use_cropper'])) {
                    $imageData = Arr::only($data, [
                        'use_cropper',
                        'x0', 'x1', 'y0', 'y1',
                    ]);
                    $imageData['use_cropper'] = isset($data['use_cropper']);
                }
                if (!$isAdmin && isset($data['image'])) {
                    $imageData['extension'] = (Config::get('lorekeeper.settings.masterlist_image_format') ? Config::get('lorekeeper.settings.masterlist_image_format') : ($data['extension'] ?? $data['image']->getClientOriginalExtension()));
                    $imageData['has_image'] = true;
                }
                $request->update($imageData);
            }

            $request->designers()->delete();
            $request->artists()->delete();

            // Check that users with the specified id(s) exist on site
            foreach ($data['designer_id'] as $id) {
                if (isset($id) && $id) {
                    $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more designers is invalid.');
                    }
                }
            }
            foreach ($data['artist_id'] as $id) {
                if (isset($id) && $id) {
                    $user = $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more artists is invalid.');
                    }
                }
            }

            // Attach artists/designers
            foreach ($data['designer_id'] as $key => $id) {
                if ($id || $data['designer_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $request->id,
                        'type'               => 'Designer',
                        'character_type'     => 'Update',
                        'url'                => $data['designer_url'][$key],
                        'user_id'            => $id,
                        'credit_type'        => $data['designer_type'][$key] ?? null,
                    ]);
                }
            }
            foreach ($data['artist_id'] as $key => $id) {
                if ($id || $data['artist_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $request->id,
                        'type'               => 'Artist',
                        'character_type'     => 'Update',
                        'url'                => $data['artist_url'][$key],
                        'user_id'            => $id,
                        'credit_type'        => $data['artist_type'][$key] ?? null,
                    ]);
                }
            }

            // Save image
            if (!$isAdmin && isset($data['image'])) {
                $this->handleImage($data['image'], $request->imageDirectory, $request->imageFileName, null, isset($data['default_image']));
            }

            // Save thumbnail
            if (!$isAdmin || ($isAdmin && isset($data['modify_thumbnail']))) {
                if (isset($data['use_cropper'])) {
                    $this->cropThumbnail(Arr::only($data, ['x0', 'x1', 'y0', 'y1']), $request);
                } elseif (isset($data['thumbnail'])) {
                    $this->handleImage($data['thumbnail'], $request->imageDirectory, $request->thumbnailFileName);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Saves the addons section of a character design update request.
     *
     * @param array $data
     * @param mixed $character
     * @param mixed $user
     *
     * @return bool
     */
    public function editClass($data, $character, $user) {
        DB::beginTransaction();

        try {
            if ($data['class_id'] != 'none') {
                $class = CharacterClass::find($data['class_id']);
                if (!$class) {
                    throw new \Exception('Invalid class.');
                }
                $character->class_id = $class->id;
                $character->save();

                if (!$this->createLog($user->id, null, $character->user_id, ($character->user_id ? null : $character->owner_url), $character->id, 'Character Class Updated', '['.$class->displayName.']', 'character')) {
                    throw new \Exception('Failed to create log.');
                }
            } else {
                $character->class_id = null;
                $character->save();

                if (!$this->createLog($user->id, null, $character->user_id, ($character->user_id ? null : $character->owner_url), $character->id, 'Character Class Removed', '[None]', 'character')) {
                    throw new \Exception('Failed to create log.');
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());

            return $this->rollbackReturn(false);
        }
    }

    /**
     * Generates a list of features for displaying.
     *
     * @param CharacterDesignUpdate $request
     *
     * @return bool
     */
    public function submitRequest($request) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Draft') {
                throw new \Exception('This request cannot be resubmitted to the queue.');
            }

            // Recheck and set update type, as insurance/in case of pre-existing drafts
            if ($request->character->is_myo_slot) {
                $request->update_type = 'MYO';
            } else {
                $request->update_type = 'Character';
            }
            // We've done validation and all section by section,
            // so it's safe to simply set the status to Pending here
            $request->status = 'Pending';
            if (!$request->submitted_at) {
                $request->submitted_at = Carbon::now();
            }
            $request->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Approves a character design update request and processes it.
     *
     * @param mixed $data
     * @param mixed $request
     * @param mixed $user
     *
     * @return string
     */
    public function approveRequest($data, $request, $user) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }
            if (!isset($data['character_category_id'])) {
                throw new \Exception('Please select a character category.');
            }
            if (!isset($data['number'])) {
                throw new \Exception('Please enter a character number.');
            }
            if (!isset($data['slug']) || Character::where('slug', $data['slug'])->where('id', '!=', $request->character_id)->exists()) {
                throw new \Exception('Please enter a unique character code.');
            }

            // Remove any added items/currency
            // Currency has already been removed, so no action required
            // However logs need to be added for each of these
            $requestData = $request->data;
            $inventoryManager = new InventoryManager;
            if (isset($requestData['user']) && isset($requestData['user']['user_items'])) {
                $stacks = $requestData['user']['user_items'];
                foreach ($requestData['user']['user_items'] as $userItemId=>$quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->update_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->update_count -= $quantity;
                    $userItemRow->save();
                }

                $staff = $user;
                foreach ($stacks as $stackId=>$quantity) {
                    $stack = UserItem::find($stackId);
                    $user = User::find($request->user_id);
                    if (!$inventoryManager->debitStack($user, $request->character->is_myo_slot ? 'MYO Design Approved' : 'Character Design Updated', ['data' => 'Item used in '.($request->character->is_myo_slot ? 'MYO design approval' : 'Character design update').' (<a href="'.$request->url.'">#'.$request->id.'</a>)'], $stack, $quantity)) {
                        throw new \Exception('Failed to create log for item stack.');
                    }
                }
                $user = $staff;
            }
            $currencyManager = new CurrencyManager;
            if (isset($requestData['user']['currencies']) && $requestData['user']['currencies']) {
                foreach ($requestData['user']['currencies'] as $currencyId=>$quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currencyManager->createLog(
                        $request->user_id,
                        'User',
                        null,
                        null,
                        $request->character->is_myo_slot ? 'MYO Design Approved' : 'Character Design Updated',
                        'Used in '.($request->character->is_myo_slot ? 'MYO design approval' : 'character design update').' (<a href="'.$request->url.'">#'.$request->id.'</a>)',
                        $currencyId,
                        $quantity
                    )) {
                        throw new \Exception('Failed to create log for user currency.');
                    }
                }
            }
            if (isset($requestData['character']['currencies']) && $requestData['character']['currencies']) {
                foreach ($requestData['character']['currencies'] as $currencyId=>$quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currencyManager->createLog(
                        $request->character_id,
                        'Character',
                        null,
                        null,
                        $request->character->is_myo_slot ? 'MYO Design Approved' : 'Character Design Updated',
                        'Used in '.($request->character->is_myo_slot ? 'MYO design approval' : 'character design update').' (<a href="'.$request->url.'">#'.$request->id.'</a>)',
                        $currencyId,
                        $quantity
                    )) {
                        throw new \Exception('Failed to create log for character currency.');
                    }
                }
            }

            $extension = Config::get('lorekeeper.settings.masterlist_image_format') != null ? Config::get('lorekeeper.settings.masterlist_image_format') : $request->extension;

            // Create a new image with the request data
            $image = CharacterImage::create([
                'character_id'  => $request->character_id,
                'is_visible'    => 1,
                'hash'          => $request->hash,
                'fullsize_hash' => $request->fullsize_hash ? $request->fullsize_hash : randomString(15),
                'extension'     => $extension,
                'use_cropper'   => $request->use_cropper,
                'x0'            => $request->x0,
                'x1'            => $request->x1,
                'y0'            => $request->y0,
                'y1'            => $request->y1,
                'species_id'    => $request->species_id,
                'subtype_id'    => ($request->character->is_myo_slot && isset($request->character->image->subtype_id)) ? $request->character->image->subtype_id : $request->subtype_id,
                'rarity_id'     => $request->rarity_id,
                'sort'          => 0,
            ]);

            // Shift the image credits over to the new image
            $request->designers()->update(['character_type' => 'Character', 'character_image_id' => $image->id]);
            $request->artists()->update(['character_type' => 'Character', 'character_image_id' => $image->id]);

            // Add the compulsory features
            if ($request->character->is_myo_slot) {
                foreach ($request->character->image->features as $feature) {
                    CharacterFeature::create(['character_image_id' => $image->id, 'feature_id' => $feature->feature_id, 'data' => $feature->data, 'character_type' => 'Character']);
                }
            }

            // Shift the image features over to the new image
            $request->rawFeatures()->update(['character_image_id' => $image->id, 'character_type' => 'Character']);

            // Make the image directory if it doesn't exist
            if (!file_exists($image->imagePath)) {
                // Create the directory.
                if (!mkdir($image->imagePath, 0755, true)) {
                    $this->setError('error', 'Failed to create image directory.');

                    return false;
                }
                chmod($image->imagePath, 0755);
            }

            // Move the image file to the new image
            File::move($request->imagePath.'/'.$request->imageFileName, $image->imagePath.'/'.$image->imageFileName);
            // Process and save the image
            $this->processImage($image);

            // The thumbnail is already generated, so it can just be moved without processing
            File::move($request->thumbnailPath.'/'.$request->thumbnailFileName, $image->thumbnailPath.'/'.$image->thumbnailFileName);

            // Set character data and other info such as cooldown time, resell cost and terms etc.
            // since those might be updated with the new design update
            if (isset($data['transferrable_at'])) {
                $request->character->transferrable_at = $data['transferrable_at'];
            }
            $request->character->character_category_id = $data['character_category_id'];
            $request->character->number = $data['number'];
            $request->character->slug = $data['slug'];
            $request->character->rarity_id = $request->rarity_id;

            $request->character->description = $data['description'];
            $request->character->parsed_description = parse($data['description']);

            $request->character->is_sellable = isset($data['is_sellable']);
            $request->character->is_tradeable = isset($data['is_tradeable']);
            $request->character->is_giftable = isset($data['is_giftable']);
            $request->character->sale_value = $data['sale_value'] ?? 0;

            // Invalidate old image if desired
            if (isset($data['invalidate_old'])) {
                $request->character->image->is_valid = 0;
                $request->character->image->save();
            }

            // Set new image if desired
            if (isset($data['set_active'])) {
                $request->character->character_image_id = $image->id;
            }

            // Final recheck and setting of update type, as insurance
            if ($request->character->is_myo_slot) {
                $request->update_type = 'MYO';
            } else {
                $request->update_type = 'Character';
            }
            $request->save();

            // Add a log for the character and user
            $this->createLog($user->id, null, $request->character->user_id, $request->character->user->alias, $request->character->id, $request->update_type == 'MYO' ? 'MYO Design Approved' : 'Character Design Updated', '[#'.$image->id.']', 'character');
            $this->createLog($user->id, null, $request->character->user_id, $request->character->user->alias, $request->character->id, $request->update_type == 'MYO' ? 'MYO Design Approved' : 'Character Design Updated', '[#'.$image->id.']', 'user');

            // If this is for a MYO, set user's FTO status and the MYO status of the slot
            // and clear the character's name
            if ($request->character->is_myo_slot) {
                if (Config::get('lorekeeper.settings.clear_myo_slot_name_on_approval')) {
                    $request->character->name = null;
                }
                $request->character->is_myo_slot = 0;
                $request->user->settings->is_fto = 0;
                $request->user->settings->save();
            }
            $request->character->save();

            // Set status to approved
            $request->staff_id = $user->id;
            $request->status = 'Approved';
            $request->save();

            // Notify the user
            Notifications::create('DESIGN_APPROVED', $request->user, [
                'design_url'    => $request->url,
                'character_url' => $request->character->url,
                'name'          => $request->character->fullName,
            ]);

            // Notify bookmarkers
            $request->character->notifyBookmarkers('BOOKMARK_IMAGE');

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $result;
    }

    /**
     * Generates a list of image credits for displaying.
     *
     * @param array                 $data
     * @param CharacterDesignUpdate $request
     * @param User                  $user
     *
     * @return bool
     */
    public function cancelRequest($data, $request, $user) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }

            // Soft removes the request from the queue -
            // it preserves all the data entered, but allows the staff member
            // to add a comment to it. Status is returned to Draft status.
            // Use when rejecting a request that just requires minor modifications to approve.

            // Set staff comment and status
            $request->staff_id = $user->id;
            $request->staff_comments = $data['staff_comments'] ?? null;
            $request->status = 'Draft';
            if (!isset($data['preserve_queue'])) {
                $request->submitted_at = null;
            }
            $request->save();

            // Notify the user
            Notifications::create('DESIGN_CANCELED', $request->user, [
                'design_url'    => $request->url,
                'character_url' => $request->character->url,
                'name'          => $request->character->fullName,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    public function deleteRequest($request) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Draft') {
                throw new \Exception('This request cannot be processed.');
            }

            // Deletes the request entirely, including images and etc.
            // This returns any attached items/currency
            // Characters with an open draft request cannot be transferred (due to attached items/currency),
            // so this is necessary to transfer a character

            $requestData = $request->data;
            // Return all added items/currency
            if (isset($requestData['user']) && isset($requestData['user']['user_items'])) {
                foreach ($requestData['user']['user_items'] as $userItemId=>$quantity) {
                    $userItemRow = UserItem::find($userItemId);
                    if (!$userItemRow) {
                        throw new \Exception('Cannot return an invalid item. ('.$userItemId.')');
                    }
                    if ($userItemRow->update_count < $quantity) {
                        throw new \Exception('Cannot return more items than was held. ('.$userItemId.')');
                    }
                    $userItemRow->update_count -= $quantity;
                    $userItemRow->save();
                }
            }

            $currencyManager = new CurrencyManager;
            if (isset($requestData['user']['currencies']) && $requestData['user']['currencies']) {
                foreach ($requestData['user']['currencies'] as $currencyId=>$quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                    }
                    if (!$currencyManager->creditCurrency(null, $request->user, null, null, $currency, $quantity)) {
                        throw new \Exception('Could not return currency to user. ('.$currencyId.')');
                    }
                }
            }
            if (isset($requestData['character']['currencies']) && $requestData['character']['currencies']) {
                foreach ($requestData['character']['currencies'] as $currencyId=>$quantity) {
                    $currency = Currency::find($currencyId);
                    if (!$currency) {
                        throw new \Exception('Cannot return an invalid currency. ('.$currencyId.')');
                    }
                    if (!$currencyManager->creditCurrency(null, $request->character, null, null, $currency, $quantity)) {
                        throw new \Exception('Could not return currency to character. ('.$currencyId.')');
                    }
                }
            }

            // Delete the request
            $request->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Votes on a a character design update request.
     *
     * @param string                $action
     * @param CharacterDesignUpdate $request
     * @param User                  $user
     *
     * @return bool
     */
    public function voteRequest($action, $request, $user) {
        DB::beginTransaction();

        try {
            if ($request->status != 'Pending') {
                throw new \Exception('This request cannot be processed.');
            }
            if (!Config::get('lorekeeper.extensions.design_update_voting')) {
                throw new \Exception('This extension is not currently enabled.');
            }

            switch ($action) {
                default:
                    flash('Invalid action.')->error();
                    break;
                case 'approve':
                    $vote = 2;
                    break;
                case 'reject':
                    $vote = 1;
                    break;
            }

            $voteData = (isset($request->vote_data) ? collect(json_decode($request->vote_data, true)) : collect([]));
            $voteData->get($user->id) ? $voteData->pull($user->id) : null;
            $voteData->put($user->id, $vote);
            $request->vote_data = $voteData->toJson();

            $request->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $result;
    }

    /**
     * Handles character lineage data.
     *
     * @param array $data
     * @param bool  $isMyo
     * @param mixed $character
     *
     * @return Character             $character
     * @return bool|CharacterLineage
     */
    private function handleCharacterLineage($data, $character, $isMyo = false) {
        try {
            // TODO take values from $data
            $lineageData = [
                'character_id'          => $character->id,
                'sire_id'               => null,
                'sire_name'             => null,
                'sire_sire_id'          => null,
                'sire_sire_name'        => null,
                'sire_sire_sire_id'     => null,
                'sire_sire_sire_name'   => null,
                'sire_sire_dam_id'      => null,
                'sire_sire_dam_name'    => null,
                'sire_dam_id'           => null,
                'sire_dam_name'         => null,
                'sire_dam_sire_id'      => null,
                'sire_dam_sire_name'    => null,
                'sire_dam_dam_id'       => null,
                'sire_dam_dam_name'     => null,
                'dam_id'                => null,
                'dam_name'              => null,
                'dam_sire_id'           => null,
                'dam_sire_name'         => null,
                'dam_sire_sire_id'      => null,
                'dam_sire_sire_name'    => null,
                'dam_sire_dam_id'       => null,
                'dam_sire_dam_name'     => null,
                'dam_dam_id'            => null,
                'dam_dam_name'          => null,
                'dam_dam_sire_id'       => null,
                'dam_dam_sire_name'     => null,
                'dam_dam_dam_id'        => null,
                'dam_dam_dam_name'      => null,
            ];
            $roots = [
                'sire',
                'sire_sire',
                'sire_sire_sire',
                'sire_sire_dam',
                'sire_dam',
                'sire_dam_sire',
                'sire_dam_dam',
                'dam',
                'dam_sire',
                'dam_sire_sire',
                'dam_sire_dam',
                'dam_dam',
                'dam_dam_sire',
                'dam_dam_dam',
            ];
            // you don't need to look for great-great-grandparents
            $shortlist = [
                'sire',
                'sire_sire',
                'sire_dam',
                'dam',
                'dam_sire',
                'dam_dam',
            ];

            // check if lineage is empty ...
            $isEmpty = true;

            // Checking inputs ?
            for ($i = 0; $i < 14; $i++) {
                // if isset Data key_id, set Lineage key_id and check if that character exists?
                // else if isset Data key_name, set Lineage key_name to that.
                if (isset($data[$roots[$i].'_id'])) {
                    $id = $data[$roots[$i].'_id'];
                    $lineageData[$roots[$i].'_id'] = $id;
                    $char = Character::find($id);

                    // TODO Set name to be the slug of the character.
                    $lineageData[$roots[$i].'_name'] = $char->slug;
                    $isEmpty = false;
                } elseif (isset($data[$roots[$i].'_name'])) {
                    $lineageData[$roots[$i].'_name'] = $data[$roots[$i].'_name'];
                    $isEmpty = $data[$roots[$i].'_name'] == '' ? $isEmpty : false;
                }
            }

            //TODO: Fill from ancestor(s) IF ancestor fill is checked.
            if (isset($data['generate_ancestors']) && !$isEmpty) {
                for ($j = 0; $j < 6; $j++) {
                    $key = $shortlist[$j];
                    $id = $data[$key.'_id'];

                    // check if this is a character id and not null
                    if ($id !== null) {
                        // check if this exists and has lineage
                        $char = Character::find($id);
                        if ($char->exists() && $char->lineage !== null) {
                            // go through their parents and gparents
                            for ($k = 0; $k < 6; $k++) {
                                // checks that this is a valid lineage index
                                // eg. sire_sire_sire and not sire_sire_sire_sire
                                $key2 = $key.'_'.$shortlist[$k];
                                if (in_array($key2, $roots, true)) {
                                    $lineageData[$key2.'_id'] = $char->lineage[$shortlist[$k].'_id'];
                                    $lineageData[$key2.'_name'] = $char->lineage[$shortlist[$k].'_name'];
                                }
                            }
                        }
                    }
                }
            }
            // throw new \Exception('Everything went right, we hope.');

            $lineage = $isEmpty ? null : CharacterLineage::create($lineageData);

            return $lineage;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        // Add background fill if destination format is not transparent
        if (!in_array(config('lorekeeper.settings.masterlist_image_format'), ['png', 'webp']) && config('lorekeeper.settings.masterlist_image_format') != null && config('lorekeeper.settings.masterlist_image_background') != null) {
            $canvas = Image::canvas($image->width(), $image->height(), config('lorekeeper.settings.masterlist_image_background'));
        }
    }

    /**
     * Trims and optionally resizes and watermarks an image.
     *
     * @param CharacterImage $characterImage
     */
    private function processImage($characterImage) {
        // Trim transparent parts of image.
        $image = Image::make($characterImage->imagePath.'/'.$characterImage->imageFileName)->trim('transparent');

        if (Config::get('lorekeeper.settings.masterlist_image_format') != 'png' && Config::get('lorekeeper.settings.masterlist_image_format') != null && Config::get('lorekeeper.settings.masterlist_image_background') != null) {
            $canvas = Image::canvas($image->width(), $image->height(), Config::get('lorekeeper.settings.masterlist_image_background'));
            $image = $canvas->insert($image, 'center');
        }

        if (config('lorekeeper.settings.store_masterlist_fullsizes') == 1) {
            // Generate fullsize hash if not already generated,
            // then save the full-sized image
            if (!$characterImage->fullsize_hash) {
                $characterImage->fullsize_hash = randomString(15);
                $characterImage->save();
            }

            if (config('lorekeeper.settings.masterlist_fullsizes_cap') != 0) {
                if ($image->width() > $image->height()) {
                    // Landscape
                    $image->resize(config('lorekeeper.settings.masterlist_fullsizes_cap'), null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } else {
                    // Portrait
                    $image->resize(null, config('lorekeeper.settings.masterlist_fullsizes_cap'), function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            }

            // Save the processed image
            $image->save($characterImage->imagePath.'/'.$characterImage->fullsizeFileName, 100, config('lorekeeper.settings.masterlist_fullsizes_format'));
        } else {
            // Delete fullsize if it was previously created.
            if (isset($characterImage->fullsize_hash) ? file_exists(public_path($characterImage->imageDirectory.'/'.$characterImage->fullsizeFileName)) : false) {
                unlink($characterImage->imagePath.'/'.$characterImage->fullsizeFileName);
            }
        }

        // Resize image if desired
        if (config('lorekeeper.settings.masterlist_image_dimension') != 0) {
            if ($image->width() > $image->height()) {
                // Landscape
                if (config('lorekeeper.settings.masterlist_image_dimension_target') == 'short') {
                    $image->resize(null, config('lorekeeper.settings.masterlist_image_dimension'), function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } else {
                    $image->resize(config('lorekeeper.settings.masterlist_image_dimension'), null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            } else {
                // Portrait
                if (config('lorekeeper.settings.masterlist_image_dimension_target') == 'short') {
                    $image->resize(config('lorekeeper.settings.masterlist_image_dimension'), null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } else {
                    $image->resize(null, config('lorekeeper.settings.masterlist_image_dimension'), function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            }
        }
        // Watermark the image if desired
        if (config('lorekeeper.settings.watermark_masterlist_images') == 1) {
            $watermark = Image::make('images/watermark.png');

            if (config('lorekeeper.settings.watermark_resizing') == 1) {
                $imageWidth = $image->width();
                $imageHeight = $image->height();

                $wmWidth = $watermark->width();
                $wmHeight = $watermark->height();

                $wmScale = config('lorekeeper.settings.watermark_percent');

                //Assume Landscape by Default
                $maxSize = $imageWidth * $wmScale;

                if ($imageWidth > $imageHeight) {
                    //Landscape
                    $maxSize = $imageWidth * $wmScale;
                } else {
                    // Portrait
                    $maxSize = $imageHeight * $wmScale;
                }

                if ($wmWidth > $wmHeight) {
                    //Landscape
                    $watermark->resize($maxSize, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } else {
                    // Portrait
                    $watermark->resize(null, $maxSize, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }
            }
            $image->insert($watermark, 'center');
        }

        // Save the processed image
        $image->save($characterImage->imagePath.'/'.$characterImage->imageFileName, 100, config('lorekeeper.settings.masterlist_image_format'));
    }

    /**
     * Handles character data.
     *
     * @param array $data
     * @param bool  $isMyo
     *
     * @return bool|Character
     */
    private function handleCharacter($data, $isMyo = false) {
        try {
            if ($isMyo) {
                $data['character_category_id'] = null;
                $data['number'] = null;
                $data['slug'] = null;
                $data['species_id'] = isset($data['species_id']) && $data['species_id'] ? $data['species_id'] : null;
                $data['subtype_id'] = isset($data['subtype_id']) && $data['subtype_id'] ? $data['subtype_id'] : null;
                $data['rarity_id'] = isset($data['rarity_id']) && $data['rarity_id'] ? $data['rarity_id'] : null;
            }

            $characterData = Arr::only($data, [
                'character_category_id', 'rarity_id', 'user_id',
                'number', 'slug', 'description',
                'sale_value', 'transferrable_at', 'is_visible',
            ]);

            $characterData['name'] = ($isMyo && isset($data['name'])) ? $data['name'] : null;
            $characterData['owner_url'] = isset($characterData['user_id']) ? null : $data['owner_url'];
            $characterData['is_sellable'] = isset($data['is_sellable']);
            $characterData['is_tradeable'] = isset($data['is_tradeable']);
            $characterData['is_giftable'] = isset($data['is_giftable']);
            $characterData['is_visible'] = isset($data['is_visible']);
            $characterData['sale_value'] = $data['sale_value'] ?? 0;
            $characterData['is_gift_art_allowed'] = 0;
            $characterData['is_gift_writing_allowed'] = 0;
            $characterData['is_trading'] = 0;
            $characterData['parsed_description'] = parse($data['description']);
            if ($isMyo) {
                $characterData['is_myo_slot'] = 1;
            }

            $character = Character::create($characterData);

            // Create character profile row
            $character->profile()->create([]);

            return $character;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return false;
    }

    /**
     * Handles character image data.
     *
     * @param array $data
     * @param bool  $isMyo
     * @param mixed $character
     *
     * @return Character           $character
     * @return bool|CharacterImage
     */
    private function handleCharacterImage($data, $character, $isMyo = false) {
        try {
            if ($isMyo) {
                $data['species_id'] = (isset($data['species_id']) && $data['species_id']) ? $data['species_id'] : null;
                $data['subtype_id'] = isset($data['subtype_id']) && $data['subtype_id'] ? $data['subtype_id'] : null;
                $data['rarity_id'] = (isset($data['rarity_id']) && $data['rarity_id']) ? $data['rarity_id'] : null;

                // Use default images for MYO slots without an image provided
                if (!isset($data['image'])) {
                    $data['image'] = public_path('images/myo.png');
                    $data['thumbnail'] = public_path('images/myo-th.png');
                    $data['extension'] = config('lorekeeper.settings.masterlist_image_format') ?? 'png';
                    $data['fullsize_extension'] = config('lorekeeper.settings.masterlist_fullsizes_format') ?? $data['extension'];
                    $data['default_image'] = true;
                    unset($data['use_cropper']);
                }
            }
            $imageData = Arr::only($data, [
                'species_id', 'subtype_id', 'rarity_id', 'use_cropper',
                'x0', 'x1', 'y0', 'y1',
            ]);
            $imageData['use_cropper'] = isset($data['use_cropper']);
            $imageData['description'] = $data['image_description'] ?? null;
            $imageData['parsed_description'] = parse($imageData['description']);
            $imageData['hash'] = randomString(10);
            $imageData['fullsize_hash'] = randomString(15);
            $imageData['sort'] = 0;
            $imageData['is_valid'] = isset($data['is_valid']);
            $imageData['is_visible'] = isset($data['is_visible']);
            $imageData['extension'] = (config('lorekeeper.settings.masterlist_image_format') ?? ($data['extension'] ?? $data['image']->getClientOriginalExtension()));
            $imageData['fullsize_extension'] = (config('lorekeeper.settings.masterlist_fullsizes_format') ?? ($data['fullsize_extension'] ?? $data['image']->getClientOriginalExtension()));
            $imageData['character_id'] = $character->id;

            $image = CharacterImage::create($imageData);

            // Check if entered url(s) have aliases associated with any on-site users
            $designers = array_filter($data['designer_url']); // filter null values
            foreach ($designers as $key=> $url) {
                $recipient = checkAlias($url, false);
                if (is_object($recipient)) {
                    $data['designer_id'][$key] = $recipient->id;
                    $designers[$key] = null;
                }
            }
            $artists = array_filter($data['artist_url']);  // filter null values
            foreach ($artists as $key=> $url) {
                $recipient = checkAlias($url, false);
                if (is_object($recipient)) {
                    $data['artist_id'][$key] = $recipient->id;
                    $artists[$key] = null;
                }
            }
            // Check that users with the specified id(s) exist on site
            foreach ($data['designer_id'] as $id) {
                if (isset($id) && $id) {
                    $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more designers is invalid.');
                    }
                }
            }
            foreach ($data['artist_id'] as $id) {
                if (isset($id) && $id) {
                    $user = $user = User::find($id);
                    if (!$user) {
                        throw new \Exception('One or more artists is invalid.');
                    }
                }
            }

            // Attach artists/designers
            foreach ($data['designer_id'] as $key => $id) {
                if ($id || $data['designer_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $image->id,
                        'type'               => 'Designer',
                        'url'                => $data['designer_url'][$key],
                        'user_id'            => $id,
                        'credit_type'        => $data['artist_type'][$key] ?? null,
                    ]);
                }
            }
            foreach ($data['artist_id'] as $key => $id) {
                if ($id || $data['artist_url'][$key]) {
                    DB::table('character_image_creators')->insert([
                        'character_image_id' => $image->id,
                        'type'               => 'Artist',
                        'url'                => $data['artist_url'][$key],
                        'user_id'            => $id,
                        'credit_type'        => $data['artist_type'][$key] ?? null,
                    ]);
                }
            }

            // Save image
            $this->handleImage($data['image'], $image->imageDirectory, $image->imageFileName, null, isset($data['default_image']));

            // Save thumbnail first before processing full image
            if (isset($data['use_cropper'])) {
                $this->cropThumbnail(Arr::only($data, ['x0', 'x1', 'y0', 'y1']), $image, $isMyo);
            } else {
                $this->handleImage($data['thumbnail'], $image->imageDirectory, $image->thumbnailFileName, null, isset($data['default_image']));
            }

            // Process and save the image itself
            if (!$isMyo) {
                $this->processImage($image);
            }

            // Attach features
            foreach ($data['feature_id'] as $key => $featureId) {
                if ($featureId) {
                    $feature = CharacterFeature::create(['character_image_id' => $image->id, 'feature_id' => $featureId, 'data' => $data['feature_data'][$key]]);
                }
            }

            return $image;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return false;
    }

    private function generateFeatureList($image) {
        $result = '';
        foreach ($image->features as $feature) {
            $result .= '<div>'.($feature->feature->category ? '<strong>'.$feature->feature->category->displayName.':</strong> ' : '').$feature->feature->displayName.'</div>';
        }
    }

    /**
     * Deletes a character design update request.
     *
     * @param mixed $image
     *
     * @return string
     */
    private function generateCredits($image) {
        $result = ['designers' => '', 'artists' => ''];
        foreach ($image->designers as $designer) {
            $result['designers'] .= '<div>'.$designer->displayLink().'</div>';
        }
        foreach ($image->artists as $artist) {
            $result['artists'] .= '<div>'.$artist->displayLink().'</div>';
        }
    }
}

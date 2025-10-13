<?php

namespace App\Services;

use App\Models\Background\Background;
use App\Models\Background\BackgroundCondition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class BackgroundService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Background Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of character backgrounds.
    |
    */

    /**
     * Creates a new background.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return Background|bool
     */
    public function createBackground($data, $user) {
        DB::beginTransaction();

        \Log::info($data);

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $background = Background::create($data);

            $this->createConditions($data, $background);

            if (!$this->logAdminAction($user, 'Created Background', 'Created '.$background->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $background->imagePath, $background->imageFileName);
            }

            return $this->commitReturn($background);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a background.
     *
     * @param Background            $background
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return Background|bool
     */
    public function updateBackground($background, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Background::where('name', $data['name'])->where('id', '!=', $background->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $background->update($data);

            $this->createConditions($data, $background);

            if (!$this->logAdminAction($user, 'Updated Background', 'Updated '.$background->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $background->imagePath, $background->imageFileName);
            }

            if (isset($data['use_cropper'])) {
                //$this->cropThumbnail(Arr::only($data, ['x0', 'x1', 'y0', 'y1']), $image, $background);
            }

            return $this->commitReturn($background);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a background.
     *
     * @param Background $background
     * @param mixed      $user
     *
     * @return bool
     */
    public function deleteBackground($background, $user) {
        DB::beginTransaction();

        try {
            // Check first if the background is currently in use
            if (DB::table('character_backgrounds')->where('background_id', $background->id)->exists()) {
                throw new \Exception('A character with this background exists. Please remove the background first.');
            }

            if (!$this->logAdminAction($user, 'Deleted Background', 'Deleted '.$background->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            if (file_exists($background->imageDirectory.'/'.$background->imageFileName)) {
                $this->deleteImage($background->imagePath, $background->imageFileName);
            }
            $background->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Crops a thumbnail for the given image.
     *
     * @param array $points
     * @param array $image
     * @param mixed $background
     */
    public function cropThumbnail($points, $image, $background) {
        $imageProperties = getimagesize($background->imageUrl);
        if ($imageProperties[0] > 2000 || $imageProperties[1] > 2000) {
            // For large images (in terms of dimensions),
            // use imagick instead, as it's better at handling them
            Config::set('image.driver', 'imagick');
        }

        $image = Image::make($background->imageUrl);

        $cropWidth = $points['x1'] - $points['x0'];
        $cropHeight = $points['y1'] - $points['y0'];

        if (config('lorekeeper.settings.masterlist_image_automation') == 0) {
            // Crop according to the selected area
            $image->crop($cropWidth, $cropHeight, $points['x0'], $points['y0']);
        }

        // Resize to fit the thumbnail size
        $image->resize(config('lorekeeper.settings.masterlist_thumbnails.width'), config('lorekeeper.settings.masterlist_thumbnails.height'));

        // Save the thumbnail
        $image->save($this->imageDirectory.'/'.$this->thumbnailFileName, 100, config('lorekeeper.settings.masterlist_image_format'));
    }

    /**
     * Processes user input for creating/updating a background.
     *
     * @param array      $data
     * @param Background $background
     *
     * @return array
     */
    private function populateData($data, $background = null) {
        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (isset($data['remove_image'])) {
            if ($background && $background->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($background->imagePath, $background->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Updates the background conditions.
     *
     * @param array      $data
     * @param Background $background
     *
     * @return array
     */
    private function createConditions($data, $background) {
        //Delete the old conditions
        BackgroundCondition::where('background_id', $background->id)->delete();
        $row_values = [
            'background_id' => $background->id,
            'location'      => $data['location'],
            'type'          => null,
            'value'         => null,
        ];
        $results = [];

        //If nothing is set then process as a "location-only" background
        if (!isset($data['user_id']) && !isset($data['status']) && !isset($data['item_id']) && !isset($data['guild_id']) && !isset($data['award_id'])) {
            $row = $this->processCondition($row_values);
            $results[] = $row;
        }

        //Process the new conditions
        if (isset($data['user_id'])) {
            $row_values['type'] = 'User';
            foreach ($data['user_id'] as $u_id) {
                if ($u_id) {
                    $row_values['value'] = $u_id;

                    $row = $this->processCondition($row_values);
                    $results[] = $row;
                }
            }
        }
        if (isset($data['status'])) {
            $row_values['type'] = 'Status';
            $row_values['value'] = $data['status'];

            $row = $this->processCondition($row_values);
            $results[] = $row;
        }
        if (isset($data['item_id'])) {
            $row_values['type'] = 'Item';
            $row_values['value'] = $data['item_id'];

            $row = $this->processCondition($row_values);
            $results[] = $row;
        }
        if (isset($data['guild_id'])) {
            $row_values['type'] = 'Guild';
            foreach ($data['guild_id'] as $g_id) {
                if ($g_id) {
                    $row_values['value'] = $g_id;

                    $row = $this->processCondition($row_values);
                    $results[] = $row;
                }
            }
        }
        if (isset($data['award_id'])) {
            $row_values['type'] = 'Award';
            foreach ($data['award_id'] as $a_id) {
                if ($a_id) {
                    $row_values['value'] = $a_id;

                    $row = $this->processCondition($row_values);
                    $results[] = $row;
                }
            }
        }

        \Log::info($results);

        return $results;
    }

    /**
     * Updates the background conditions.
     *
     * @param array $data
     *
     * @return array
     */
    private function processCondition($data) {
        $condition = BackgroundCondition::create([
            'background_id' => $data['background_id'],
            'location'      => $data['location'],
            'type'          => $data['type'] ?? null,
            'value'         => $data['value'] ?? null,
        ]);

        return $condition;
    }
}

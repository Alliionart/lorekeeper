<?php

namespace App\Services;

use App\Models\Map;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class MapManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Map Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of site maps.
    |
    */

    /* -------------------------------------------------------------------------
    | Maps
    |--------------------------------------------------------------------------*/

    /**
     * Creates a map.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Map|bool
     */
    public function createMap($data, $user) {
        DB::beginTransaction();

        try {
            \Log::info('Creating map with data: '.print_r($data, true));

            //$map = Map::create($data);

            $has_image = handleMapImageTiles($map, $data['image']);

            //return $this->commitReturn($map);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /* -------------------------------------------------------------------------
    | Map Items
    |--------------------------------------------------------------------------*/

    /**
     * Creates a site map.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Map|bool
     */
    public function createMapItem($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            }
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $map = Map::create($data);

            return $this->commitReturn($map);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a site map.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $map
     *
     * @return \App\Models\Map|bool
     */
    public function updateMapItem($map, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Map::where('key', $data['key'])->where('id', '!=', $map->id)->exists()) {
                throw new \Exception('The key has already been taken.');
            }

            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            }
            $data['user_id'] = $user->id;
            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }

            $map->update($data);

            return $this->commitReturn($map);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a site map.
     *
     * @param mixed $map
     *
     * @return bool
     */
    public function deleteMapItem($map) {
        DB::beginTransaction();

        try {
            // Specific maps such as the TOS/privacy policy cannot be deleted from the admin panel.
            if (config('lorekeeper.text_maps.'.$map->key)) {
                throw new \Exception('You cannot delete this map.');
            }

            $map->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /* -------------------------------------------------------------------------
    | Other Functions
    |--------------------------------------------------------------------------*/

    /**
     * Creates the tiles from the uploaded image OR extracts the zip and adds to the correct directory.
     *
     * @param mixed $map_id
     * @param mixed $file
     *
     * @return string
     */
    public function handleMapImageTiles($map_id, $file) {
        $extension = $file->extension();

        $tileDirectory = public_path($map->tileImageDirectory);

        if ($extension === 'zip') {
            //If its a zip file, get the directory for the tiles and then
            $tempZip = getRealPath();
            $tempExt = public_path('/temp');

            //Check to see if the folder exists and if not - create it
            if (!Storage::disk('local')->exists('/temp')) {
                Storage::disk('local')->makeDirectory('/temp');
            }
            if (!File::exists($tileDirectory)) {
                File::makeDirectory($tileDirectory, 0755, true);
            }

            //Unzip
            $zip = new ZipArchive;
            if ($zip->open($tempZip) === true) {
                $zip->extractTo($tempExt);
                $zip->close();
            //Unzip successful
            } else {
                //Failure
            }

            //Move the file contents
            $files = File::allFiles($tempExt);
            foreach ($files as $f) {
                $fileName = $f->getFilename();
                File::move($file->getPathname(), $tileDirectory.'/'.$fileName);
            }

            //Clean up the temps
            File::deleteDirectory($tempExt);
            if (File::exists($tempZip)) {
                File::delete($tempZip);
            }
        } else {
            //Else create and drop in the tiles to the directory.
            $return = handleTileCreation($tileDirectory, $file);
        }
    }

    /**
     * Use the https://github.com/jahed/maptiles functions to create the tiles.
     *
     * @param mixed $tileDirectory
     * @param mixed $file
     */
    public function handleTileCreation($tileDirectory, $file) {
        //Use the process facade
        $temp_path = $file->getPathname().'/'.$file->getFilename();

        if (!File::exists($tileDirectory)) {
            File::makeDirectory($tileDirectory, 0755, true);
        }

        $command = [
            'gdal2tiles.py',
            $temp_path,
            $tileDirectory,
        ];

        $result = Process::run($command);

        if ($result->successful()) {
            return 'Tiles created successfully.';
        } else {
            return 'Error with tile creation: '.$result->errorOutput();
        }
    }
}

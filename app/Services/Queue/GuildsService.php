<?php

namespace App\Services\Queue;

use App\Models\Guild\Guild;
use App\Models\Guild\GuildMember;
use App\Models\User\User;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class GuildsService extends Service {
    /**
     * Retrieves any data that should be used in the queue type editing form on the admin side.
     *
     * @return array
     */
    public function getEditData() {
        return [

        ];
    }

    /**
     * Retrieves any data that should be used in the queue type on the user side.
     *
     * @param mixed $queue
     *
     * @return array
     */
    public function getActData($queue) {
        return [
        ];
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function getData($data) {
        return $data;
    }

    /**
     * Processes the data attribute of the queue and returns it in the preferred format.
     *
     * @param object $queue
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($queue, $data) {
        return [
        ];
    }

    private function getTempLogoFileName() {
        return 'guild-logo.png';
    }

    private function getTempDir($submission) {
        return 'images/data/queue-submissions/'.$submission->id;
    }

    private function getTempLogoRelativePath($submission) {
        return $this->getTempDir($submission).'/'.$this->getTempLogoFileName();
    }

    private function getTempLogoAbsolutePath($submission) {
        return public_path($this->getTempLogoRelativePath($submission));
    }

    /**
     * Handle any validation on-submit to the queue.
     *
     * @param \App\Models\User\User $user
     * @param array                 $data
     * @param mixed                 $queue
     * @param mixed                 $submission
     *
     * @return bool
     */
    public function submit($queue, $data, $user, $submission) {
        try {
            //any data handled here should only be that which is required by this particular queue type, as the rest is already handled by the queue service itself

            $validator = Validator::make($data, [
                'guild_name'        => 'required|between:3,100|unique:guilds,name',
                'guild_description' => 'nullable',
                'logo'              => 'nullable|image|mimes:png,gif|max:200',
            ]);

            if ($validator->fails()) {
                throw new \Exception(implode(' ', $validator->errors()->all()));
            }

            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
                File::ensureDirectoryExists(public_path($this->getTempDir($submission)));
                $this->handleImage($data['logo'], public_path($this->getTempDir($submission)), $this->getTempLogoFileName());
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return false;
    }

    public function processSubmit($queue, $data, $user, $submission) {
        $parsed = null;
        if (isset($data['guild_description']) && $data['guild_description']) {
            $parsed = parse($data['guild_description']);
        }

        $logoUrl = null;
        if (file_exists($this->getTempLogoAbsolutePath($submission))) {
            $logoUrl = asset($this->getTempLogoRelativePath($submission));
        }

        return [
            'guild_name'               => $data['guild_name'] ?? null,
            'guild_description'        => $data['guild_description'] ?? null,
            'parsed_guild_description' => $parsed,
            'logo_url'                 => $logoUrl,
            'logo_path'                => file_exists($this->getTempLogoAbsolutePath($submission)) ? $this->getTempLogoRelativePath($submission) : null,
        ];
    }

    /**
     * Delete the associated data that is custom to this queue.
     *
     * @param \App\Models\User\User $user
     * @param array                 $data
     * @param mixed                 $queue
     * @param mixed                 $submission
     *
     * @return bool
     */
    public function delete($queue, $data, $user, $submission) {
        try {
            //handle any custom delete functions

            // remove any temporary logo for this submission
            $tempDir = public_path($this->getTempDir($submission));
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return false;
    }

    /**
     * Delete the associated data that is custom to this queue.
     *
     * @param \App\Models\User\User $user
     * @param array                 $data
     * @param mixed                 $queue
     * @param mixed                 $submission
     *
     * @return bool
     */
    public function approve($queue, $data, $user, $submission) {
        try {
            $qData = $submission->data['queue'] ?? null;
            if (!$qData) {
                throw new \Exception('Missing guild submission data.');
            }

            if (!isset($qData['guild_name']) || !$qData['guild_name']) {
                throw new \Exception('Missing guild name.');
            }

            if (Guild::where('name', $qData['guild_name'])->exists()) {
                throw new \Exception('A guild with this name already exists.');
            }

            $guild = Guild::create([
                'name'              => $qData['guild_name'],
                'owner_id'          => $submission->user_id,
                'status'            => 'Active',
                'description'       => $qData['guild_description'] ?? null,
                'parsed_description'=> $qData['parsed_guild_description'] ?? null,
                'has_logo'          => isset($qData['logo_path']) && $qData['logo_path'] ? 1 : 0,
                'has_banner'        => 0,
                'is_disbanded'      => 0,
            ]);

            GuildMember::create([
                'guild_id'    => $guild->id,
                'user_id'     => $submission->user_id,
                'rank_id'     => null,
                'reputation'  => 0,
                'joined_at'   => Carbon::now(),
                'permissions' => 2,
            ]);

            if (isset($qData['logo_path']) && $qData['logo_path']) {
                $src = public_path($qData['logo_path']);
                if (file_exists($src)) {
                    File::ensureDirectoryExists($guild->imagePath);
                    copy($src, $guild->imagePath.DIRECTORY_SEPARATOR.$guild->logoFileName);

                    $guild->update([
                        'has_logo' => 1,
                    ]);
                }
            }

            $submission->update([
                'data' => array_replace_recursive($submission->data, [
                    'queue' => array_merge($qData, [
                        'guild_id' => $guild->id,
                        'logo_url' => $guild->logoUrl,
                        'logo_path' => null,
                    ]),
                ]),
            ]);

            $tempDir = public_path($this->getTempDir($submission));
            if (is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return false;
    }
}

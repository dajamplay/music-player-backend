<?php

namespace App\Actions;

use wapmorgan\Mp3Info\Mp3Info;
use App\Models\Track;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UpdateTracksAction {

    private string $status = 'Обновлено';
    private array $errors = [];
    private int $trackUpdatedCount = 0;
    private int $trackFailUpdatedCount = 0;

    public function run($file = null): array
    {
        if ($file === null) {

            $files = Storage::files('public/music', true);

            Track::truncate();
            Storage::deleteDirectory('artwork');

            foreach ($files as $file) {
                $this->loadFile($file);
            }
        } else {
            $this->loadFile($file);
        }

        return [
            'status' => $this->status,
            'errors' => $this->errors,
            'updated' => $this->trackUpdatedCount,
            'failed' => $this->trackFailUpdatedCount,
        ];
    }

    public function loadFile($file): void
    {

        $file_url_mp3 = Storage::url($file);

        try {
            $audio = new Mp3Info(Storage::path($file), true);

            $file_url_jpg = str_replace('/storage/music', '/artwork', $file_url_mp3);

            if($image_base64 = $audio->getCover()) {
                $artworkIsSave = Storage::disk('public')->put($file_url_jpg  . '.jpg', $image_base64);

                if ($artworkIsSave) {
                    $artwork = '/storage' .$file_url_jpg  . '.jpg';
                } else {
                    $artwork = '/storage/default/no_artwork.jpg';
                }

            } else {
                $artwork = '/storage/default/no_artwork.jpg';
            }

            $track = new Track();
            $track->url = $file_url_mp3;

            $defaultTrackTitleArray = explode('/', $file_url_mp3);
            $defaultTrackTitle = array_pop($defaultTrackTitleArray);

            if (array_key_exists('song', $audio->tags)) {
                $track->title = trim($audio->tags['song'], '?');
            } else {
                $track->title = $defaultTrackTitle;
            }

            if (array_key_exists('artist', $audio->tags)) {
                $track->artist = trim($audio->tags['artist'], '?');
            } else {
                $track->artist = $defaultTrackTitle;
            }

            $track->duration = $audio->duration;
            $track->artwork = $artwork;

            $isSave = $track->save();

            if ($isSave) {
                $this->trackUpdatedCount++;
            } else {
                $this->trackFailUpdatedCount++;
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->errors[] .= $e->getMessage();
        }
    }

}

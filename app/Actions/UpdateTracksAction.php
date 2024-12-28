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
            $this->fullUpdate();
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

    private function fullUpdate(): void
    {
        Track::truncate();
        Storage::deleteDirectory('artwork');

        $files = Storage::files('public/music', true);

        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }

    public function loadFile($file): void
    {
        try {
            $file_url_mp3 = Storage::url($file);
            $audio = new Mp3Info(Storage::path($file), true);
            $file_url_jpg = str_replace('/storage/music', '/artwork', $file_url_mp3);
            $defaultTrackTitle = $this->getDefaultTitle($file_url_mp3);

            $track = new Track();

            $track->url = $file_url_mp3;
            $track->title = $this->getTitle($audio, $defaultTrackTitle);
            $track->artist = $this->getArtist($audio, $defaultTrackTitle);
            $track->duration = $this->getDuration($audio);
            $track->artwork = $this->getArtwork($file_url_jpg, $audio);

            if ($track->save()) $this->trackUpdatedCount++;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->errors[] .= $e->getMessage();
            $this->trackFailUpdatedCount++;
        }
    }

    private function getDuration($audio) {
        return $audio->duration;
    }

    private function getTitle($audio, $defaultTrackTitle) {
        if (array_key_exists('song', $audio->tags)) {
            return trim($audio->tags['song'], '?');
        }
        return $defaultTrackTitle;
    }

    private function getArtist($audio, $defaultTrackTitle) {
        if (array_key_exists('artist', $audio->tags)) {
            return trim($audio->tags['artist'], '?');
        }
        return $defaultTrackTitle;
    }

    private function getDefaultTitle($file_url_mp3): ?string
    {
        $defaultTrackTitleArray = explode('/', $file_url_mp3);
        return array_pop($defaultTrackTitleArray);
    }

    private function getArtwork($file_url_jpg, $audio): string
    {
        if($image_base64 = $audio->getCover()) {
            $artworkIsSave = Storage::disk('public')
                ->put($file_url_jpg  . '.jpg', $image_base64);

            if ($artworkIsSave) {
                return '/storage' .$file_url_jpg  . '.jpg';
            }
        }

        return '/storage/default/no_artwork.jpg';
    }

}

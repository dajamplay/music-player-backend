<?php

namespace App\Http\Controllers;

use App\Actions\UpdateTracksAction;
use App\Http\Requests\UploadedFileRequest;
use App\Models\Track;
use Illuminate\Database\Eloquent\Collection;

class TrackController extends Controller
{
    public function index(): Collection
    {
        return Track::all();
    }

    public function updateTracks(UpdateTracksAction $updateTracksAction): array
    {
        return $updateTracksAction->run();
    }

    public function lastUpdate()
    {
        return Track::query()->orderBy('updated_at','DESC')->first()->updated_at;
    }

    public function addTrack(UploadedFileRequest $request, UpdateTracksAction $updateTracksAction): array
    {
        $request->validated();
        $file = $request->file('file');
        $file = $file->storeAs('public/music/1_all/' . $file->getClientOriginalName());
        return $updateTracksAction->run($file);
    }
}

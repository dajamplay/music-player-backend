<?php

namespace App\Http\Controllers;

use App\Actions\UpdateTracksAction;
use App\Http\Requests\UploadedFileRequest;
use App\Models\Track;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function addTrack(UploadedFileRequest $request): string
    {
        $request->validated();
        $file = $request->file('file');
        return $file->storeAs('public/music/1_all/' . $file->getClientOriginalName());
    }
}

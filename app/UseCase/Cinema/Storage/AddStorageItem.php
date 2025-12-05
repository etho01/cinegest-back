<?php 

namespace App\UseCase\Cinema\Storage;

use App\Exceptions\Cinema\Storage\NeedOriginException;
use App\Exceptions\Cinema\Storage\NoneStorageSelectedException;
use App\Exceptions\Cinema\Storage\NotEnoughStorage;
use App\Exceptions\Cinema\Storage\RessouceItemNotExistInOriginStorage;
use App\Exceptions\Cinema\Storage\StorageItemExist;
use App\Models\Cinema\Settings\Storage;
use App\Models\Cinema\StorageItem;
use App\Models\Movie;
use App\Models\Movie\MovieVersion;
use App\Models\Room;
use Illuminate\Support\Collection;

class AddStorageItem
{
    public function __construct(
        private ?int $roomId,
        private ?int $storageId,
        private ?int $originId,
        private array $movieVersions
    )
    {
    }

    public function execute()
    {
        if ($this->roomId == null && $this->storageId == null) 
        {
            throw new NoneStorageSelectedException();
        }

        if ($this->roomId != null && $this->originId == null) 
        {
            throw new NeedOriginException();
        }

        $movieVersionsToInsert = MovieVersion::with('movie')->whereIn('id', $this->movieVersions)->get();
        $movieVersionsInserted = new \Illuminate\Support\Collection();
        $movieVersionsOrigin = collect();

        $sizeStorageElement = 0; 

        if ($this->roomId != null) 
        {
            $movieVersionsInserted = MovieVersion::
                with('movie')
                ->whereIn('id', 
                    StorageItem::where('roomId', $this->roomId)->select('movieVersionId')
                )->get();

            $sizeStorageElement = Room::find($this->roomId)?->serveurSize ?? 0;
        }
        else if ($this->storageId != null)
        {
            $movieVersionsInserted = MovieVersion::
                with('movie')
                ->whereIn('id', 
                    StorageItem::where('storageId', $this->storageId)->select('movieVersionId')
                )->get();

            $sizeStorageElement = Storage::find($this->storageId)?->capacity ?? 0;
        }

        if ($this->originId != null) 
        {
            $movieVersionsOrigin = MovieVersion::
                with('movie')
                ->whereIn('id', 
                    StorageItem::where('storageId', $this->originId)->select('movieVersionId')
                )->get();
        }

        foreach ($movieVersionsToInsert as $movieVersion) 
        {
            if ($movieVersionsInserted->contains('id', $movieVersion->id)) 
            {
                throw new StorageItemExist($movieVersion);
            }
            else
            {
                $movieVersionsInserted->push($movieVersion);
            }

            if ($this->originId != null && !$movieVersionsOrigin->contains('id', $movieVersion->id)) 
            {
                throw new RessouceItemNotExistInOriginStorage($movieVersion);
            }
        }

        // convert To en Go
        $sizeStorageElement = $sizeStorageElement * 1000;

        $storageNeeded = $this->getStorageUse($movieVersionsInserted);
        if ($storageNeeded > $sizeStorageElement) 
        {
            throw new NotEnoughStorage($storageNeeded, $sizeStorageElement);
        }

        foreach ($this->movieVersions as $versionId) 
        {
            $movieVersion = MovieVersion::find($versionId);
            if (!$movieVersion) {
                continue;
            }
            $movie = $movieVersion->movie;
            \App\Models\Cinema\StorageItem::create([
                'roomId' => $this->roomId,
                'storageId' => $this->storageId,
                'originId' => $this->originId,
                'movieVersionId' => $versionId,
                'movieId' => $movie->id,
            ]);
        }
    }

    private function getStorageUse(Collection $movieVersions): float
    {
        $listMovie = [];
        $totalSize = 0;
        foreach ($movieVersions as $movieVersion) {
            $totalSize += $movieVersion->size;
            if (!in_array($movieVersion->movie->id, $listMovie)) {
                $listMovie[] = $movieVersion->movie->id;
                $totalSize += $movieVersion->movie->size;
            }
        }
        return $totalSize;
    }

    public static function handle(
        ?int $roomId,
        ?int $storageId,
        ?int $originId,
        array $movieVersions
    )
    {

        $instance = new self(
            roomId: $roomId,
            storageId: $storageId,
            originId: $originId,
            movieVersions: $movieVersions
        );
        return $instance->execute();
    }
}
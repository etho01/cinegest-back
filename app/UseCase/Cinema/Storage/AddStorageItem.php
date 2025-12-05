<?php 

namespace App\UseCase\Cinema\Storage;

use App\Exceptions\Cinema\Storage\NeedOriginException;
use App\Models\Movie\MovieVersion;
use NoneStorageSelectedException;

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
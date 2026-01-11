<?php

namespace App\Repository;

use App\Models\Entity\Cinema;
use Illuminate\Database\Eloquent\Collection;

class CinemaRepository
{
    /**
     * Find cinema by ID
     */
    public function find(int $id): ?Cinema
    {
        return Cinema::find($id);
    }

    /**
     * Find cinema with relationships
     */
    public function findWithRelations(int $id, array $relations = []): ?Cinema
    {
        return Cinema::with($relations)->find($id);
    }

    /**
     * Get cinemas by IDs with relationships
     */
    public function getByIdsWithRelations(array $ids, array $relations = []): Collection
    {
        return Cinema::with($relations)->whereIn('id', $ids)->get();
    }

    /**
     * Create a new cinema
     */
    public function create(array $data): Cinema
    {
        return Cinema::create($data);
    }

    /**
     * Update a cinema
     */
    public function update(Cinema $cinema, array $data): bool
    {
        return $cinema->update($data);
    }

    /**
     * Delete a cinema
     */
    public function delete(Cinema $cinema): bool
    {
        return $cinema->delete();
    }
}

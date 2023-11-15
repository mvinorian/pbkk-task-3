<?php

namespace App\Infrastrucutre\Repository;

use App\Core\Domain\Models\SeriGenre\SeriGenre;
use App\Core\Domain\Models\SeriGenre\SeriGenreId;
use App\Core\Domain\Models\User\UserId;
use Exception;
use Illuminate\Support\Facades\DB;

class SqlSeriGenreRepository
{
    public function persist(SeriGenre $seri_genre): void
    {
        DB::table('seri_genre')->upsert([
            'id' => $seri_genre->getId(),
            'seri_id' => $seri_genre->getSeriId(),
            'genre_id' => $seri_genre->getGenreId(),
        ], 'id');
    }

    /**
     * @throws Exception
     */
    public function find(int $id): ?SeriGenre
    {
        $row = DB::table('seri_genre')->where('id', $id)->first();

        if (!$row) {
            return null;
        }

        return $this->constructFromRows([$row])[0];
    }

    /**
     * @throws Exception
     */
    public function constructFromRows(array $rows): array
    {
        $seri_genre = [];
        foreach ($rows as $row) {
            $seri_genre[] = new SeriGenre(
                $row->id,
                $row->seri_id,
                $row->genre_id,
            );
        }
        return $seri_genre;
    }
}

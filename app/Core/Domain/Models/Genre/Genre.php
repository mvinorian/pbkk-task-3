<?php

namespace App\Core\Domain\Models\Genre;

class Genre
{
    private int $id;
    private string $nama;

    /**
     * @param int $id
     * @param string $nama
     */
    public function __construct(int $id, string $nama)
    {
        $this->id = $id;
        $this->nama = $nama;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNama(): string
    {
        return $this->nama;
    }
}

<?php

namespace App\Http\Controllers;

use Throwable;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Service\CreateSeri\CreateSeriRequest;
use App\Core\Application\Service\CreateSeri\CreateSeriService;
use App\Core\Application\Service\DeleteSeri\DeleteSeriService;
use App\Core\Application\Service\CreateGenre\CreateGenreService;
use App\Core\Application\Service\GetSeriList\GetSeriListRequest;
use App\Core\Application\Service\GetSeriList\GetSeriListService;
use App\Core\Application\Service\CreatePenulis\CreatePenulisService;
use App\Core\Application\Service\GetDetailSeri\GetDetailSeriService;
use App\Core\Application\Service\CreatePenerbit\CreatePenerbitService;
use App\Core\Application\Service\CreateSeriView\CreateSeriViewService;

class SeriController extends Controller
{
    public function getSeriList(Request $request, GetSeriListService $service)
    {
        $request->validate([
            'per_page' => 'numeric',
            'page' => 'numeric',
            'filter' => ['sometimes', function ($attr, $val, $fail) {
                if (!is_array($val)) {
                    $fail($attr . ' must be an array of numbers');
                }
                if (is_array($val)) {
                    foreach ($val as $number) {
                        if (!is_numeric($number)) {
                            $fail($attr . ' must be an array of numbers');
                        }
                    }
                }
            }],
            'search' => 'string',
        ]);

        $req = new GetSeriListRequest(
            $request->input('per_page') ?? 12,
            $request->input('page') ?? 1,
            $request->input('filter'),
            $request->input('search')
        );
        $response = $service->execute($req);

        return Inertia::render('seri/index', $this->successWithDataProps($response, 'Berhasil mendapatkan list seri'));
    }

    public function getDetailSeri(Request $request, GetDetailSeriService $service)
    {
        $response = $service->execute($request->route('id'));

        return Inertia::render('seri/detail', $this->successWithDataProps($response, 'Berhasil mendapatkan detail seri'));
    }

    public function createSeri(Request $request, CreateSeriService $service)
    {
        $request->validate([
            'judul' => 'required',
            'sinopsis' => 'required',
            'tahun_terbit' => 'required',
            'foto' => 'required',
            'penerbit_id' => 'required',
            'penulis_id' => 'required',
            'genre_id' => 'required',
        ]);
        $req = new CreateSeriRequest(
            $request->input('judul'),
            $request->input('sinopsis'),
            $request->input('tahun_terbit'),
            $request->file('foto'),
            $request->input('penerbit_id'),
            $request->input('penulis_id'),
            $request->input('genre_id')
        );

        DB::beginTransaction();
        try {
            $seri_id = $service->execute($req);
        } catch (Throwable $e) {
            DB::rollBack();
            return Inertia::render('seri/create', $this->errorProps($e->getCode(), $e->getMessage()));
        }
        DB::commit();

        return redirect()->route('seri.detail', $seri_id);
    }

    public function createGenre(Request $request, CreateGenreService $service)
    {
        $request->validate([
            'nama' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $service->execute($request->input('nama'));
        } catch (Throwable $e) {
            DB::rollBack();
            return Inertia::render('auth/register', $this->errorProps($e->getCode(), $e->getMessage()));
        }
        DB::commit();

        return redirect()->route('dashboard');
    }

    public function createPenerbit(Request $request, CreatePenerbitService $service)
    {
        $request->validate([
            'nama' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $service->execute($request->input('nama'));
        } catch (Throwable $e) {
            DB::rollBack();
            return Inertia::render('auth/register', $this->errorProps($e->getCode(), $e->getMessage()));
        }
        DB::commit();

        return redirect()->route('dashboard');
    }

    public function createPenulis(Request $request, CreatePenulisService $service)
    {
        $request->validate([
            'nama_depan' => 'required',
            'nama_belakang' => 'required',
            'peran' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $service->execute($request->input('nama_depan'), $request->input('nama_belakang'), $request->input('peran'));
        } catch (Throwable $e) {
            DB::rollBack();
            return Inertia::render('auth/register', $this->errorProps($e->getCode(), $e->getMessage()));
        }
        DB::commit();

        return redirect()->route('dashboard');
    }

    public function createSeriView(CreateSeriViewService $service)
    {
        $response = $service->execute();
        return Inertia::render('seri/create', $this->successWithDataProps($response, 'Berhasil mendapatkan data untuk membuat seri'));
    }

    public function deleteSeri(Request $request, DeleteSeriService $service)
    {
        $request->validate([
            'id' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $service->execute($request->input('id'));
        } catch (Throwable $e) {
            DB::rollBack();
            return Inertia::render('auth/register', $this->errorProps($e->getCode(), $e->getMessage()));
        }
        DB::commit();

        return redirect()->back();
    }
}

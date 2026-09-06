<?php

namespace App\Http\Controllers;

use App\DataTables\ThanasDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\GeoLocation\StoreGeoThanaRequest;
use App\Http\Requests\GeoLocation\UpdateGeoThanaRequest;
use App\Models\GeoDistrict;
use App\Models\GeoDivision;
use App\Models\GeoThana;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;

class ThanasController extends Controller
{
    /**
     * @param ThanasDataTable $thanasDataTable
     * @return mixed
     */
    public function index(ThanasDataTable $thanasDataTable)
    {
        return $thanasDataTable->render('thana.index');
    }

    /**
     * @return View
     */
    public function create() : View
    {
        $data['divisions'] = GeoDivision::select('id', 'name_en', 'name_bn')->get();
        $data['districts'] = GeoDistrict::select('id', 'name_en', 'name_bn')->get();
        return view('thana.create', $data);
    }

    /**
     * @param StoreGeoThanaRequest $request
     * @param GeoThana $thana
     * @return RedirectResponse
     * @throws GeneralException
     */
    public function store(StoreGeoThanaRequest $request)
    {
        $divisionID     = $request->input('division_id');
        $districtID     = $request->input('district_id');
        $nameEn         = $request->input('name_en');
        $nameBn         = $request->input('name_bn');
        $descriptionEn  = $request->input('description_en');
        $descriptionBn  = $request->input('description_bn');
        $latitude       = $request->input('latitude');
        $longitude      = $request->input('longitude');

        try {
            DB::beginTransaction();
            $thana = GeoThana::create([
                'division_id'     => $divisionID,
                'district_id'     => $districtID,
                'name_en'         => $nameEn ?? null,
                'name_bn'         => $nameBn ?? null,
                'description_en'  => $descriptionEn ?? null,
                'description_bn'  => $descriptionBn ?? null,
                'latitude'        => $latitude ?? null,
                'longitude'       => $longitude ?? null,
                'is_active'       => 1,
                'created_by'      => auth()->user()->name,
                'updated_by'      => auth()->user()->name,
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating the Thana.'));
        }

        if ($thana) {
            DB::commit();
            //event(new ThanaCreated($thana));
            return redirect()->route('thana.index')->with('success', __('Thana Created Successfully.'));
        } else {
            return back()->withInput()->withErrors('error', __('Thana Creation Failed.'));
        }
    }

    /**
     * @param GeoThana $thana
     * @return Factory|\Illuminate\Contracts\View\View|Application|object
     */
    public function show(GeoThana $thana)
    {
        $data['thana'] = $thana;
        return view('thana.show', $data);
    }

    /**
     * @param GeoThana $thana
     * @return mixed
     */
    public function edit(GeoThana $thana) : View
    {
        $data['thana'] = $thana;
        $data['divisions'] = GeoDivision::select('id', 'name_en', 'name_bn')->get();
        $data['districts'] = GeoDistrict::select('id', 'name_en', 'name_bn')->get();
        return view('thana.edit', $data);
    }

    public function update(UpdateGeoThanaRequest $request, GeoThana $thana)
    {
        $divisionID    = $request->input('division_id');
        $districtID    = $request->input('district_id');
        $nameEn        = $request->input('name_en');
        $nameBn        = $request->input('name_bn');
        $descriptionEn = $request->input('description_en');
        $descriptionBn = $request->input('description_bn');
        $latitude      = $request->input('latitude');
        $longitude     = $request->input('longitude');

        try {
            DB::beginTransaction();
            $thana->update([
                'division_id'    => $divisionID ?? null,
                'district_id'    => $districtID ?? null,
                'name_en'        => $nameEn ?? null,
                'name_bn'        => $nameBn ?? null,
                'description_en' => $descriptionEn ?? null,
                'description_bn' => $descriptionBn ?? null,
                'latitude'       => $latitude ?? null,
                'longitude'      => $longitude ?? null,
                'updated_by'     => auth()->user()->name,
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem Updating the Thana.'));
        }

        if ($thana) {
            DB::commit();
            //event(new ThanaUpdated($thana));
            return redirect()->route('thana.index')->with('success', __('Thana Updated Successfully.'));
        } else {
            return back()->withInput()->withErrors('error', __('Thana Update Failed.'));
        }
    }

    /**
     * @param GeoThana $thana
     * @return mixed
     *
     * @throws \Exception
     */
    public function destroy($id) : JsonResponse
    {
        try {
            $thana = GeoThana::find($id);
            if ($thana) {
                //event(new ThanaDeleted($permission)); // TODO: Manage the ThanaDeleteEvent
                // Delete the Thana
                $thana->delete();
                return response()->json(['message' => 'Thana Deleted Successfully.']);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['message' => 'There was a Problem on Deleting the Thana.']);
        }
    }


    public function getThanasByDistrict(Request $request) : JsonResponse
    {
        $districtID = $request->district_id;
        $thanas = GeoThana::where('district_id', $districtID)->get(['id', 'name_en', 'name_bn']);
        return response()->json($thanas);
    }
}

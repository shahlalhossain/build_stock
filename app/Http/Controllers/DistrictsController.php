<?php

namespace App\Http\Controllers;

use App\DataTables\DistrictsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\GeoLocation\StoreGeoDistrictRequest;
use App\Http\Requests\GeoLocation\UpdateGeoDistrictRequest;
use Illuminate\Http\Request;
use App\Models\GeoDistrict;
use App\Models\GeoDivision;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;

class DistrictsController extends Controller
{
    /**
     * @param DistrictsDataTable $districtsDataTable
     * @return mixed
     */
    public function index(DistrictsDataTable $districtsDataTable)
    {
        return $districtsDataTable->render('district.index');
    }

    /**
     * @return View
     */
    public function create() : View
    {
        $data['divisions'] = GeoDivision::select('id', 'name_en', 'name_bn')->get();
        return view('district.create', $data);
    }

    /**
     * @param StoreGeoDistrictRequest $request
     * @param GeoDistrict $district
     * @return RedirectResponse
     * @throws GeneralException
     */
    public function store(StoreGeoDistrictRequest $request)
    {
        $divisionID     = $request->input('division_id');
        $nameEn         = $request->input('name_en');
        $nameBn         = $request->input('name_bn');
        $descriptionEn  = $request->input('description_en');
        $descriptionBn  = $request->input('description_bn');
        $latitude       = $request->input('latitude');
        $longitude      = $request->input('longitude');

        try {
            DB::beginTransaction();
            $district = GeoDistrict::create([
                'division_id'     => $divisionID,
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
            throw new GeneralException(__('There was a Problem on Creating the District.'));
        }

        if ($district) {
            DB::commit();
            //event(new DistrictCreated($district));
            return redirect()->route('admin.district.index')->with('success', __('District Created Successfully.'));
        } else {
            return back()->withInput()->withErrors('error', __('District Creation Failed.'));
        }
    }

    /**
     * @param GeoDistrict $district
     * @return Factory|\Illuminate\Contracts\View\View|Application|object
     */
    public function show(GeoDistrict $district)
    {
        $data['district'] = $district;
        return view('district.show', $data);
    }

    /**
     * @param GeoDistrict $district
     * @return mixed
     */
    public function edit(GeoDistrict $district) : View
    {
        $data['district'] = $district;
        $data['divisions'] = GeoDivision::select('id', 'name_en', 'name_bn')->get();
        return view('district.edit', $data);
    }

    public function update(UpdateGeoDistrictRequest $request, GeoDistrict $district)
    {
        $nameEn        = $request->input('name_en');
        $nameBn        = $request->input('name_bn');
        $descriptionEn = $request->input('description_en');
        $descriptionBn = $request->input('description_bn');
        $latitude      = $request->input('latitude');
        $longitude     = $request->input('longitude');

        try {
            DB::beginTransaction();
            $district->update([
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
            throw new GeneralException(__('There was a Problem Updating the District.'));
        }

        if ($district) {
            DB::commit();
            //event(new DistrictUpdated($district));
            return redirect()->route('admin.district.index')->with('success', __('District Updated Successfully.'));
        } else {
            return back()->withInput()->withErrors('error', __('District Update Failed.'));
        }
    }

    /**
     * @param GeoDistrict $district
     * @return mixed
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        try {
            $district = GeoDistrict::find($id);
            if ($district) {
                //event(new DistrictDeleted($permission)); // TODO: Manage the DistrictDeleteEvent
                // Delete the District
                $district->delete();
                return response()->json(['message' => 'District Deleted Successfully.']);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['message' => 'There was a Problem on Deleting the District.']);
        }
    }

    public function getDistrictsByDivision(Request $request) : JsonResponse
    {
        $divisionID = $request->division_id;
        $districts = GeoDistrict::where('division_id', $divisionID)->get(['id', 'name_en', 'name_bn']);
        return response()->json($districts);
    }
}

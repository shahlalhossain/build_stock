<?php

namespace App\Http\Controllers;

use App\DataTables\DivisionsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\GeoLocation\StoreGeoDivisionRequest;
use App\Http\Requests\GeoLocation\UpdateGeoDivisionRequest;
use App\Models\GeoDistrict;
use App\Models\GeoDivision;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;

class DivisionsController extends Controller
{
    /**
     * @param DivisionsDataTable $divisionsDataTable
     * @return mixed
     */
    public function index(DivisionsDataTable $divisionsDataTable)
    {
        return $divisionsDataTable->render('division.index');
    }

    /**
     * @return View
     */
    public function create() : View
    {
        return view('division.create');
    }

    /**
     * @param StoreGeoDivisionRequest $request
     * @param GeoDivision $division
     * @return RedirectResponse
     * @throws GeneralException
     */
    public function store(StoreGeoDivisionRequest $request)
    {
        $nameEn           = $request->input('name_en');
        $nameBn           = $request->input('name_bn');
        $descriptionEn    = $request->input('description_en');
        $descriptionBn    = $request->input('description_bn');
        $latitude         = $request->input('latitude');
        $longitude        = $request->input('longitude');

        try {
            DB::beginTransaction();
            $division = GeoDivision::create([
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
            throw new GeneralException(__('There was a Problem on Creating the Division.'));
        }

        if ($division) {
            DB::commit();
            //event(new DivisionCreated($division));
            return redirect()->route('division.index')->with('success', __('Division Created Successfully.'));
        } else {
            return back()->withInput()->withErrors('error', __('Division Creation Failed.'));
        }
    }

    /**
     * @param GeoDivision $division
     * @return Factory|\Illuminate\Contracts\View\View|Application|object
     */
    public function show(GeoDivision $division)
    {
        $data['division'] = $division;
        return view('division.show', $data);
    }

    /**
     * @param GeoDivision $division
     * @return mixed
     */
    public function edit(GeoDivision $division) : View
    {
        $data['division'] = $division;
        return view('division.edit', $data);
    }

    public function update(UpdateGeoDivisionRequest $request, GeoDivision $division)
    {
        $nameEn        = $request->input('name_en');
        $nameBn        = $request->input('name_bn');
        $descriptionEn = $request->input('description_en');
        $descriptionBn = $request->input('description_bn');
        $latitude      = $request->input('latitude');
        $longitude     = $request->input('longitude');

        try {
            DB::beginTransaction();
            $division->update([
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
            throw new GeneralException(__('There was a Problem Updating the Division.'));
        }

        if ($division) {
            DB::commit();
            //event(new DivisionUpdated($division));
            return redirect()->route('division.index')->with('success', __('Division Updated Successfully.'));
        } else {
            return back()->withInput()->withErrors('error', __('Division Update Failed.'));
        }
    }

    /**
     * @param GeoDivision $division
     * @return mixed
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        try {
            $division = GeoDivision::find($id);
            if ($division) {
                //event(new DivisionDeleted($permission)); // TODO: Manage the DivisionDeleteEvent
                // Delete the Division
                $division->delete();
                return response()->json(['message' => 'Division Deleted Successfully.']);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return response()->json(['message' => 'There was a Problem on Deleting the Division.']);
        }
    }
}

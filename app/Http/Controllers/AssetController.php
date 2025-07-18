<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Employee;
use App\Models\AssetType;
use App\Http\Requests\StoreAssetRequest;


class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function create()
{
    return view('assets.create', [
        'types' => AssetType::all(),
        'locations' => Location::all(),
        'employees' => Employee::all()
    ]);
}

public function store(StoreAssetRequest $request)
{
    $data = $request->validated();
    $data['remarked_by'] = auth()->user()->full_name ?? auth()->user()->email;

    Asset::create($data);

    return redirect('/admin')->with('success', 'Asset added successfully.');
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

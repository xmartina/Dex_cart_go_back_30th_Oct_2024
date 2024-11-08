<?php

namespace App\Http\Controllers\Admin;

use App\Common\Authorizable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Manufacturer\ManufacturerRepository;
use App\Http\Requests\Validations\CreateManufacturerRequest;
use App\Http\Requests\Validations\UpdateManufacturerRequest;

class ManufacturerController extends Controller
{
    use Authorizable;

    private $model_name;

    private $manufacturer;

    /**
     * construct
     */
    public function __construct(ManufacturerRepository $manufacturer)
    {
        parent::__construct();

        $this->model_name = trans('app.model.manufacturer');

        $this->manufacturer = $manufacturer;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $manufacturers = $this->manufacturer->all();

        $trashes = $this->manufacturer->trashOnly();

        return view('admin.manufacturer.index', compact('manufacturers', 'trashes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.manufacturer._create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateManufacturerRequest $request)
    {
        $this->manufacturer->store($request);

        return back()->with('success', trans('messages.created', ['model' => $this->model_name]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $manufacturer = $this->manufacturer->find($id);

        return view('admin.manufacturer._show', compact('manufacturer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Manufacturer  $manufacturer
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $manufacturer = $this->manufacturer->find($id);

        return view('admin.manufacturer._edit', compact('manufacturer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateManufacturerRequest $request, $id)
    {
        $this->manufacturer->update($request, $id);

        $this->clearBrandCache($id);

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
    }

    /**
     * Trash the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function trash(Request $request, $id)
    {
        $this->manufacturer->trash($id);

        $this->clearBrandCache($id);

        return back()->with('success', trans('messages.trashed', ['model' => $this->model_name]));
    }

    /**
     * Restore the specified resource from soft delete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $this->manufacturer->restore($id);

        $this->clearBrandCache($id);

        return back()->with('success', trans('messages.restored', ['model' => $this->model_name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $this->manufacturer->destroy($id);

        return back()->with('success', trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Trash the mass resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massTrash(Request $request)
    {
        $this->manufacturer->massTrash($request->ids);

        $this->clearBrandCache($id);

        if ($request->ajax()) {
            return response()->json(['success' => trans('messages.trashed', ['model' => $this->model_name])]);
        }

        return back()->with('success', trans('messages.trashed', ['model' => $this->model_name]));
    }

    /**
     * Trash the mass resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(Request $request)
    {
        $this->manufacturer->massDestroy($request->ids);

        $this->clearBrandCache($id);

        if ($request->ajax()) {
            return response()->json(['success' => trans('messages.deleted', ['model' => $this->model_name])]);
        }

        return back()->with('success', trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Empty the Trash the mass resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function emptyTrash(Request $request)
    {
        $this->manufacturer->emptyTrash($request);

        if ($request->ajax()) {
            return response()->json(['success' => trans('messages.deleted', ['model' => $this->model_name])]);
        }

        return back()->with('success', trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Clear relevant cached values
     *
     * @param int $id
     * @return void
     */
    private function clearBrandCache($id = null)
    {
        // Clear when the id is not given, in case of mass delete
        if (!$id || in_array($id, get_featured_brand_ids())) {
            // Clear featured_brands from cache
            Cache::forget('featured_brand_ids');
            Cache::forget('featured_brands');
        }

        return null;
    }
}

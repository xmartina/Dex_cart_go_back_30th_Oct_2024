<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Common\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Attribute\AttributeRepository;
use App\Http\Requests\Validations\CreateAttributeRequest;
use App\Http\Requests\Validations\UpdateAttributeRequest;

class AttributeController extends Controller
{
    use Authorizable;

    private $model_name;

    private $attribute;

    /**
     * construct
     */
    public function __construct(AttributeRepository $attribute)
    {
        parent::__construct();

        $this->model_name = trans('app.model.attribute');

        $this->attribute = $attribute;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Attribute::with('attributeType')
            ->withCount(['attributeValues', 'categories']);

        $attributes = $query->get();

        $trashes = $this->attribute->trashOnly();

        return view('admin.attribute.index', compact('attributes', 'trashes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.attribute._create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAttributeRequest $request)
    {
        $attribute = Attribute::create($request->except(['categories', '_token']));

        if ($request->has('categories')) {
            DB::transaction(function () use ($attribute, $request) {
                $attribute->categories()->sync($request->categories);
            });
        }

        return back()->with('success', trans('messages.created', ['model' => $this->model_name]));
    }

    /**
     * Display all Attribute Values the specified Attribute.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function entities($id)
    {
        $entities = $this->attribute->entities($id);

        return view('admin.attribute.entities', $entities);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $attribute = $this->attribute->find($id);
        $selectedCategories = $attribute->categories->pluck('id', 'name')->toArray();

        return view('admin.attribute._edit', compact('attribute', 'selectedCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Attribute  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttributeRequest $request, $id)
    {
        $attribute = $this->attribute->update($request, $id);

        if ($request->has('categories')) {
            DB::transaction(function () use ($attribute, $request) {
                $attribute->categories()->sync($request->categories);
            });
        }

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
    }

    public function showTranslationForm(Attribute $attribute,string $selected_language)
    {
        $available_languages = \App\Helpers\ListHelper::availableTranslationLocales();
        
        if ($selected_language == config('system_settings.default_language')) {
            return redirect()->route('admin.catalog.attribute.translate.form',['attribute' =>$attribute,'language' => $available_languages->first()->code]);
        }

        $attribute_translation = $attribute->translations()->where('lang', $selected_language)->firstOrNew([
            'attribute_id' => $attribute->id,
            'lang' => $selected_language,
            'translation' => []
        ]);

        return view('admin.attribute._translation', compact('attribute','attribute_translation','available_languages', 'selected_language'));
    }

    public function storeTranslation(Attribute $attribute, Request $request)
    {
        $attribute_translation = $attribute->translations()->where('lang', $request->lang)->firstOrNew([
            'attribute_id' => $attribute->id,
            'lang' => $request->lang,
        ]);

        $attribute_translation->translation = [
            'name' => $request->input('name'),
        ];

        $attribute_translation->save();

        return back()->with('success', trans('messages.updated', ['model' => 'Attribute Translation']));
    }

    /**
     * Trash the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function trash(Request $request, $id)
    {
        $this->attribute->trash($id);

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
        $this->attribute->restore($id);

        return back()->with('success', trans('messages.restored', ['model' => $this->model_name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $this->attribute->destroy($id);

        return back()->with('success', trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Save sorting order for attributes by ajax
     */
    public function reorder(Request $request)
    {
        $this->attribute->reorder($request->all());

        return response('success!', 200);
    }

    /**
     * Trash the mass resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massTrash(Request $request)
    {
        $this->attribute->massTrash($request->ids);

        if ($request->ajax()) {
            return response()->json([
                'success' => trans('messages.trashed', ['model' => $this->model_name])
            ]);
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
        $this->attribute->massDestroy($request->ids);

        if ($request->ajax()) {
            return response()->json([
                'success' => trans('messages.deleted', ['model' => $this->model_name])
            ]);
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
        $this->attribute->emptyTrash($request);

        if ($request->ajax()) {
            return response()->json([
                'success' => trans('messages.deleted', ['model' => $this->model_name])
            ]);
        }

        return back()->with('success', trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Response AJAX call to check if the attribute is a color/pattern type or not
     */
    public function ajaxGetParentAttributeType(Request $request)
    {
        if ($request->ajax()) {
            $type_id = $this->attribute->getAttributeTypeId($request->input('id'));

            if ($type_id) {
                return response("$type_id", 200);
            }
        }

        return response('Not found!', 404);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCategoryRequest;
use App\Category;
use App\Traits\TraitModel;

class CategoriesController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('categories_access'), 403);
        $categories = Category::all();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('category');

        $code = acc_code_generate($last_code, 8, 3);

        abort_unless(\Gate::allows('categories_create'), 403);
        return view('admin.categories.create', compact('code'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('categories_create'), 403);
        $category = Category::create($request->all());

        return redirect()->route('admin.categories.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('categories_edit'), 403);
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request,Category $category)
    {
        abort_unless(\Gate::allows('categories_edit'), 403);
        $category->update($request->all());
        return redirect()->route('admin.categories.index');
    }

    public function destroy(Category $category)
    {
        abort_unless(\Gate::allows('categories_delete'), 403);

        $category->delete();

        return back();
    }

    public function massDestory(MassDestroyCategoriesRequest $request)
    {
        Category::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}

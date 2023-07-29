<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $layout_status = ['public', 'unpublic'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['name', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'public';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');

        $query = Category::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        $categories = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleResponseSuccess($categories, 'Get All Categories');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('create_category')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'name' => 'required|max:255',
            'status' => 'required|in:pending,published',
            'upload_ids' => 'array'
        ]);

        $category = new Category();
        $category->user_ID = Auth::id();
        $category->name = $request->name;
        $category->slug = Str::of($request->name)->slug('-');
        $category->description = $request->description;
        $category->status = $request->status;
        if ($request->upload_ids){
            $category->upload_id = $request->upload_ids;
            deleteImage($request->upload_ids);
        }
        $category->type = $request->type;
        if ($request->hasFile('image')) {
            $image = $request->image;
            $imagePath = $image->storeAs('public/upload/' . date('Y/m/d'), Str::random(10));
            $category->image_url = asset(Storage::url($imagePath));
        }
        $category->save();

        return $this->handleResponseSuccess($category, 'Create Category successfully!');
    }

    public function show(Category $category)
    {
        $category->posts = $category->posts()->where('status', 'public');
        return $this->handleResponseSuccess($category, 'Get Category Successfully!');
    }

    public function update(Request $request, Category $category)
    {
        if (!Auth::user()->hasPermission('update_category')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'name' => 'required|max:255',
            'status' => 'required|in:pending,published',
            'upload_ids' => 'array'
        ]);

        $category->name = $request->name;
        $category->slug = Str::of($request->name)->slug('-');
        $category->description = $request->description;
        $category->status = $request->status;
        $category->type = $request->type;
        $category->upload_id = $request->upload_ids;
        if ($request->upload_ids){
            deleteImage($request->upload_ids);
        }
        $category->update();
        return $this->handleResponseSuccess($category, 'Update Category Successfully!');
    }

    public function restore(Request $request)
    {

        if (!Auth::user()->hasRole('admin')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'id' => 'required',
        ]);

        $id = $request->input('id');
        $id = is_array($id) ? $id : [$id];
        $dataRestore = Category::onlyTrashed()->whereIn('id', $id)->restore();

        return $this->handleResponseSuccess($dataRestore, 'Restore data successfully',);
    }

        public function destroy(Request $request, Category $category)
    {
        if (!Auth::user()->hasRole('admin')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'ids' => 'required',
            'option' => 'in:softDelete,hardDelete',
        ]);

        $option = $request->option ?? config('app.option_delete');
        $ID_delete = $request->input('ids');
        $path = parse_url($category->url, PHP_URL_PATH);
        $old_path = str_replace('/storage', '/public', $path);
        Storage::delete($old_path);
        if ($option === 'softDelete') {
            $category->whereIn('id', $ID_delete)->delete();
            return $this->handleResponseSuccess([],'Post softDelete successfully!',);
        }
        if ($option === 'hardDelete') {
            $category->withTrashed()->whereIn('id', $ID_delete)->forceDelete();
            Storage::delete($old_path);
            return $this->handleResponseSuccess([],'Post hardDelete successfully!');
        }
    }
}

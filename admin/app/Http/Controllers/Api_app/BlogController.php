<?php

namespace App\Http\Controllers\Api_app;

use App\Http\Resources\BlogCollection;
use App\Models\Blog;
use App\Http\Resources\Blog as BlogResource;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BlogController extends AuthController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function getListBlog() {
        $current_page = 1;
        $current_page_hot = 1;
        $id = !empty($this->request->input('id')) ? $this->request->input('id') : 0;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        if ($this->request->query('current_page')) {
            $current_page = $this->request->query('current_page');
        }
        if ($this->request->query('current_page_hot')) {
            $current_page_hot = $this->request->query('current_page_hot');
        }
        $per_page = $this->request->query('per_page', 3);
        $per_page_hot = $this->request->query('per_page_hot', 3);
        
        
        $blog = Blog::with(['blogCategory', 'creator'])->select('id','image','title','descption','active','type_blog','hot','staff_create','view','created_at','updated_at')
            ->selectRaw(DB::raw("'1' as 'check'"))
            ->where(function ($query) use ($id) {
                $query->where('active', 1);
                if (!empty($id)){
                    $query->where('id', '!=', $id);
                }
            })
            ->orderByRaw('id desc')->paginate($per_page, ['*'], '', $current_page);

        $blog = new BlogCollection($blog);
        $nextBlog = !empty($blog->hasMorePages()) ? 1 : 0;

        $blogHot = Blog::with(['blogCategory', 'creator'])->select('id','image','title','descption','active','type_blog','hot','staff_create','view','created_at','updated_at')->where(function ($query) use ($id, $search) {
                $query->where('active', 1);
                $query->where('hot', 1);
                if (!empty($id)){
                    $query->where('id', '!=', $id);
                }
                if (!empty($search)){
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })
            ->orderByRaw('id desc')
            ->paginate($per_page_hot, ['*'], '', $current_page_hot);
        $blogHot = new BlogCollection($blogHot);
        $nextBlogHot = !empty($blogHot->hasMorePages()) ? 1 : 0;

        $data['blog'] = [
            'data' => $blog,
            'next' => $nextBlog
        ];
        $data['blogHot'] = [
            'data' => $blogHot,
            'next' => $nextBlogHot
        ];
        return response()->json($data);
    }

    public function getListBlogNext()
    {
        $current_page = 1;
        $per_page = 10;

        if ($this->request->input('current_page')) {
            $current_page = $this->request->input('current_page');
        }
        if ($this->request->input('per_page')) {
            $per_page = $this->request->input('per_page');
        }
        $id = !empty($this->request->input('id')) ? $this->request->input('id') : 0;
        $search = !empty($this->request->input('search')) ? $this->request->input('search') : null;
        $hot = !empty($this->request->input('hot')) ? $this->request->input('hot') : 0; 
        $is_view = !empty($this->request->input('is_view')) ? $this->request->input('is_view') : 0; 
        
        $blogQuery = Blog::with(['blogCategory', 'creator'])->where(function ($query) use ($id, $search, $hot) {
                $query->where('active', 1);
                if ($hot == 1){
                    $query->where('hot', 1);
                } 
                if (!empty($id)){
                    $query->where('id', '!=', $id);
                }
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            });

        if ($is_view == 1) {
            $blogQuery->orderBy('view', 'desc')->orderBy('id', 'desc');
        } else {
            $blogQuery->orderBy('id', 'desc');
        }

        $blog = $blogQuery->paginate($per_page, ['*'], '', $current_page);
        $blog = new BlogCollection($blog);
        $nextBlog = !empty($blog->hasMorePages()) ? 1 : 0;

        $data['blog'] = [
            'data' => $blog,
            'next' => $nextBlog
        ];
        return response()->json($data);
    }

    public function getDetail($id){
        $blog = Blog::with(['blogCategory', 'creator'])->where('active', 1)
            ->where('id', $id)
            ->first();
        if (!$blog) {
            return response()->json(['result' => false, 'message' => 'Không tìm thấy bài viết'], 404);
        }
        $blog->increment('view');
        return BlogResource::make($blog);
    }
}

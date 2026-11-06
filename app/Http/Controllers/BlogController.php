<?php

namespace App\Http\Controllers;

use App\Support\Blog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Blog::categories();
        $category = $categories->has($request->query('category')) ? $request->query('category') : null;

        return view('pages.blog.index', [
            'posts' => Blog::all()->when($category, fn ($posts) => $posts->where('category_slug', $category))->values(),
            'categories' => $categories,
            'category' => $category,
            'canonical' => route('blog', array_filter(['category' => $category])), // a category is its own page (C4)
        ]);
    }

    public function show(string $slug): View
    {
        $post = Blog::find($slug) ?? abort(404);

        return view('pages.blog.show', [
            'post' => $post,
            'more' => Blog::all()->where('slug', '!=', $slug)->take(3)->values(),
        ]);
    }
}

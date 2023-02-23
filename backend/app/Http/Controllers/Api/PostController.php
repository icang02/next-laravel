<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'mimes:jpg,jpeg,png|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => ucfirst($request->title),
            'content' => $request->content,
        ]);

        // return response
        return new PostResource(true, 'Data post berhasil ditambahkan!', $post);
    }

    public function show(Post $post)
    {
        // return single post as a resource
        return new PostResource(true, 'Data post ditemukan', $post);
    }

    public function update(Request $request, Post $post)
    {
        // define validation rules
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // check if image is not empty
        if ($request->hasFile('image')) {

            // upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // delete old image
            Storage::delete('public/posts/' . $post->image);

            // update post with new image
            $post->update([
                'image' => $image->hashName(),
                'title' => ucfirst($request->title),
                'content' => $request->content,
            ]);
        } else {

            // update post without image
            $post->update([
                'title' => ucfirst($request->title),
                'content' => $request->content,
            ]);
        }

        // return response
        return new PostResource(true, 'Data post berhasil diubah!', $post);
    }

    public function destroy(Post $post)
    {
        // delete image
        Storage::delete('public/posts/' . $post->image);

        // delete post
        $post->delete();

        // return response
        return new PostResource(true, 'Data post berhasil dihapus!', null);
    }
}

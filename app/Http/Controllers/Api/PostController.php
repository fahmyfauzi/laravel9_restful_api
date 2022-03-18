<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        $post =  Post::latest()->paginate();

        return new PostResource(true, 'List data post', $post);
    }

    public function store(Request $request)
    {
        //rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,giv,svg|max:2048',
            'title' => 'required',
            'content' => 'required'
        ]);

        //check if validator fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 442);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post

        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        //return
        return new PostResource(true, 'Data Post berhasil ditambahkan!', $post);
    }

    public function show(Post $post)
    {
        //return single post
        return new PostResource(true, 'Data berhasil ditemukan!', $post);
    }

    public function update(Request $request, Post $post)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required',
        ]);
        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // check image is not empty
        if ($request->hasFile('image')) {
            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());
            // delete old image
            // Storage::delete('public/posts/' . $post->image);
            Storage::delete('public/posts/' . $post->image);

            //update new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {

            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        //return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    public function destroy(Post $post)
    {
        Storage::delete('public/post/' . $post->image);

        $post->delete();

        return new PostResource(true, 'Data Post berhasil dihapus!', null);
    }
}

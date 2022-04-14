<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function create(Request $request)
    {
        $user = auth()->user();
        if($user["role_id"] !== 1)
            return response()->json(["msg"=>"Forbidden"], 403);

        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string',
            'image' => 'file',
        ]);

        if ($validator->fails()) {
            return response()->json(["msg"=>$validator->errors()->first()], 422);
        }



        $data['slug'] = SlugService::createSlug(Post::class, 'slug', $data['title']);
        $data['user_id'] = $user['id'];
        $post = Post::create($data);

        return response()->json(["msg" => "success"], 200);
    }

    public function get(Request $request){

        $validator = Validator::make($request->all(), [
            'id' => 'int',
            'user_id' => 'int',
            'slug' => 'string',
        ]);

        $data = $request->all();

        if ($validator->fails()) {
            return response()->json(["msg"=>$validator->errors()->first()], 422);
        }

        if(isset($data['id']))
            $Post = Post::where("id", $data['id'])->get();
        elseif (isset($data['user_id']))
            $Post = Post::where("user_id", $data['user_id'])->get();
        elseif (isset($data['slug']))
            $Post = Post::where("slug", $data['slug'])->get();
        else
            $Post = Post::get();

        return response()->json(
            PostResource::collection($Post),
        );

    }
}

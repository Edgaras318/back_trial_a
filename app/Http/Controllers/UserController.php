<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all()->makeHidden(['phone', 'email', 'email_verified_at']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $fields = request()->validate([
            'name' => 'required|string',
            'role' => 'required',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'phone' => 'string'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'role' => $fields['role'],
            'email' => $fields['email'],
            'phone'  => $fields['phone'],
            'password'  => bcrypt($fields['password']),
        ]);

        // if (request()->hasFile('picture')) {
        //     $path = request()->file('picture')->store('images', 's3');
        //     $imageName = basename($path);
        //     $user->update(['picture' => $imageName]);
        //     $user->save();
        // }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $respone = [
            'user' => $user,
            'token' => $token
        ];

        return response($respone, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return User::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        request()->validate([
            'name' => 'required|string',
            'role' => 'required',
            'email' => 'required|unique:users,email,' . $id,
            'phone' => 'string'
        ]);

        $wizzkid = User::Find($id);
        $oldImage = $wizzkid->picture;

        // if (request()->picture) {

        //     // if (Storage::disk('s3')->exists($oldImage)) {

        //     //  }
        //     $path = request()->file('picture')->store('images', 's3');
        //     $imageName = basename($path);
        //     $wizzkid->update(request()->all());
        //     $wizzkid->update(['picture' => $imageName]);
        //     if ($oldImage) {
        //         Storage::disk('s3')->delete('images/' . $oldImage);
        //     }
        // } else {
        $wizzkid->update(request()->all());
        $wizzkid->update(['picture' => $oldImage]);
        //}

        $response = [
            'user' => $wizzkid,
        ];

        return response($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return User::destroy($id);
    }

    /**
     * Search for a name and role
     *
     * @param  str  $name 
     * @return \Illuminate\Http\Response
     */
    public function search($name)
    {
        return User::where('name', 'like', '%' . $name . '%')->get();
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'Logged out'
        ];
    }

    public function login()
    {
        $fields = request()->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Incorrect login credentials'], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $respone = [
            'user' => $user->makeHidden(['phone', 'email', 'email_verified_at']),
            'token' => $token
        ];

        return response($respone, 200);
    }
}

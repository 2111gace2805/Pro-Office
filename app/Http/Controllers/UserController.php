<?php

namespace App\Http\Controllers;

use App\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class UserController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $users = User::where('user_type', 'user')
            ->orWhere('user_type', 'admin')
            ->orderBy('id', 'desc')
            ->get();
        return view('backend.user.list', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.user.create');
        } else {
            return view('backend.user.modal.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|max:191',
            'email'           => 'required|email|unique:users|max:191',
            'password'        => 'required|max:20|min:6|confirmed',
            'status'          => 'required',
            'user_type'       => 'required',
            'role_id'         => 'required_if:user_type,user',
            'dui'             => 'required|max:10',
            'profile_picture' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('users.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $profile_picture = "";
        if ($request->hasfile('profile_picture')) {
            $file            = $request->file('profile_picture');
            $profile_picture = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/profile/", $profile_picture);
        }

        $user                    = new User();
        $user->name              = $request->input('name');
        $user->email             = $request->input('email');
        $user->user_type         = $request->input('user_type');
        $user->status            = $request->input('status');
        $user->role_id           = $request->input('role_id');
        $user->dui               = $request->input('dui');
        $user->seller_code       = $request->input('seller_code');
        $user->company_id        = company_id();
        $user->profile_picture   = $profile_picture;
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->password          = Hash::make($request->password);

        $user->save();

        //Prefix Output
        $user->status          = user_status($user->status);
        $user->role_id         = $user->role->name;
        $user->user_type       = strtoupper($user->user_type);
        $user->profile_picture = '<img src="' . profile_picture($user->profile_picture) . '" class="thumb-sm rounded-circle mr-2">';

        if (!$request->ajax()) {
            return redirect()->route('users.create')->with('success', _lang('Saved Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $user, 'table' => '#users_table']);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $user = User::find($id);
        if (!$request->ajax()) {
            return view('backend.user.view', compact('user', 'id'));
        } else {
            return view('backend.user.modal.view', compact('user', 'id'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $user = User::find($id);
        if (!$request->ajax()) {
            return view('backend.user.edit', compact('user', 'id'));
        } else {
            return view('backend.user.modal.edit', compact('user', 'id'));
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|max:191',
            'email'           => [
                'required',
                Rule::unique('users')->ignore($id),
            ],
            'password'        => 'nullable|max:20|min:6|confirmed',
            'status'          => 'required',
            'user_type'       => 'required',
            'role_id'         => 'required_if:user_type,user',
            'dui'             => 'required|max:10',
            'profile_picture' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('users.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        if ($request->hasfile('profile_picture')) {
            $file            = $request->file('profile_picture');
            $profile_picture = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/profile/", $profile_picture);
        }

        $user               = User::find($id);
        $user->name         = $request->input('name');
        $user->email        = $request->input('email');
        $user->user_type    = $request->input('user_type');
        $user->status       = $request->input('status');
        $user->role_id      = $request->input('role_id');
        $user->dui          = $request->input('dui');
        $user->seller_code  = $request->input('seller_code');
		$user->company_id = company_id();

        if ($request->hasfile('profile_picture')) {
            $user->profile_picture = $profile_picture;
        }
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        //Prefix Output
        $user->status          = user_status($user->status);
        $user->role_id         = $user->role->name;
        $user->user_type       = strtoupper($user->user_type);
        $user->profile_picture = '<img src="' . profile_picture($user->profile_picture) . '" class="thumb-sm rounded-circle mr-2">';

        if (!$request->ajax()) {
            return redirect()->route('users.index')->with('success', _lang('Updated Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Sucessfully'), 'data' => $user, 'table' => '#users_table']);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $user = User::find($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', _lang('Deleted Sucessfully'));
    }

}
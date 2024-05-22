<?php

namespace App\Http\Controllers;

use App\ContactGroup;
use Illuminate\Http\Request;
use Validator;

class ContactGroupController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $contactgroups = ContactGroup::orderBy("id", "desc")->get();
        return view('backend.accounting.contacts.contact_group.list', compact('contactgroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!$request->ajax()) {
            return view('backend.accounting.contacts.contact_group.create');
        } else {
            return view('backend.accounting.contacts.contact_group.modal.create');
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
            'name' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('contact_groups.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $contactgroup       = new ContactGroup();
        $contactgroup->name = $request->input('name');
        $contactgroup->note = $request->input('note');

        $contactgroup->save();

        if (!$request->ajax()) {
            return redirect()->route('contact_groups.index')->with('success', _lang('Information has been added sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Information has been added sucessfully'), 'data' => $contactgroup]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $contactgroup = ContactGroup::find($id);
        if (!$request->ajax()) {
            return view('backend.accounting.contacts.contact_group.view', compact('contactgroup', 'id'));
        } else {
            return view('backend.accounting.contacts.contact_group.modal.view', compact('contactgroup', 'id'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $contactgroup = ContactGroup::find($id);

        if (!$request->ajax()) {
            return view('backend.accounting.contacts.contact_group.edit', compact('contactgroup', 'id'));
        } else {
            return view('backend.accounting.contacts.contact_group.modal.edit', compact('contactgroup', 'id'));
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
            'name' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('contact_groups.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $contactgroup       = ContactGroup::find($id);
        $contactgroup->name = $request->input('name');
        $contactgroup->note = $request->input('note');

        $contactgroup->save();

        if (!$request->ajax()) {
            return redirect()->route('contact_groups.index')->with('success', _lang('Information has been updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Information has been updated sucessfully'), 'data' => $contactgroup]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $contactgroup = ContactGroup::find($id);
        $contactgroup->delete();
        return back()->with('success', _lang('Information has been deleted sucessfully'));
    }
}
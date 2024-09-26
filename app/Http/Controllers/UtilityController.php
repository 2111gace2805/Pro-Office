<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Setting;
use Artisan;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class UtilityController extends Controller {
    /**
     * Show the Settings Page.
     *
     * @return Response
     */

    public function __construct() {
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    public function settings(Request $request, $store = '') {
        if ($store == '') {
            return view('backend.administration.general_settings.settings');
        } else {
            foreach ($_POST as $key => $value) {
                if ($key == "_token") {
                    continue;
                }

                $data               = array();
                $data['value']      = $value;
                $data['updated_at'] = Carbon::now();
                if (Setting::where('name', $key)->exists()) {
                    Setting::where('name', '=', $key)->update($data);
                    \Cache::forget($key);
                } else {
                    $data['name']       = $key;
                    $data['created_at'] = Carbon::now();
                    Setting::insert($data);
                }
                \Cache::put($key, $value);
            } //End Loop

            foreach ($_FILES as $key => $value) {
                $this->upload_file($key, $request);
            }

            //Update Currency exchange Rate
            //update_currency_exchange_rate();

            \Cache::forget('currency_position');
            \Cache::forget('currency');
            \Cache::forget('date_format');
            \Cache::forget('time_format');
            \Cache::forget('language');

            if (!$request->ajax()) {
                return redirect()->route('settings.update_settings')->with('success', _lang('Saved Sucessfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Saved Sucessfully')]);
            }
        }
    }

    public function upload_logo(Request $request) {
        $this->validate($request, [
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:8192',
        ]);

        if ($request->hasFile('logo')) {
            $image           = $request->file('logo');
            $name            = 'logo.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/media');
            $image->move($destinationPath, $name);

            $data               = array();
            $data['value']      = $name;
            $data['updated_at'] = Carbon::now();

            if (Setting::where('name', "logo")->exists()) {
                Setting::where('name', '=', "logo")->update($data);
            } else {
                $data['name']       = "logo";
                $data['created_at'] = Carbon::now();
                Setting::insert($data);
            }

            \Cache::put("logo", $name);

            if (!$request->ajax()) {
                return redirect()->route('settings.update_settings')->with('success', _lang('Saved Sucessfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Logo Upload successfully')]);
            }

        }
    }

    public function upload_file($file_name, Request $request) {

        if ($request->hasFile($file_name)) {
            $file            = $request->file($file_name);
            $name            = 'file_' . time() . "." . $file->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/media');
            $file->move($destinationPath, $name);

            $data               = array();
            $data['value']      = $name;
            $data['updated_at'] = Carbon::now();

            if (Setting::where('name', $file_name)->exists()) {
                Setting::where('name', '=', $file_name)->update($data);
            } else {
                $data['name']       = $file_name;
                $data['created_at'] = Carbon::now();
                Setting::insert($data);
            }
            \Cache::put($file_name, $name);
        }
    }

    /**
     * Display a list of database backup
     *
     * @return \Illuminate\Http\Response
     */
    public function database_backup_list() {
        $databasebackups = \App\DatabaseBackup::all()->sortByDesc("id");
        return view('backend.administration.database_backup.list', compact('databasebackups'));
    }

    public function create_database_backup() {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $return   = "";
        $database = 'Tables_in_' . DB::getDatabaseName();
        $tables   = array();
        $result   = DB::select("SHOW TABLES");

        foreach ($result as $table) {
            $tables[] = $table->$database;
        }

        //loop through the tables
        foreach ($tables as $table) {
            $return .= "DROP TABLE IF EXISTS $table;";

            $result2 = DB::select("SHOW CREATE TABLE $table");
            $row2    = $result2[0]->{'Create Table'};

            $return .= "\n\n" . $row2 . ";\n\n";

            $result = DB::select("SELECT * FROM $table");

            foreach ($result as $row) {
                $return .= "INSERT INTO $table VALUES(";
                foreach ($row as $key => $val) {
                    $return .= "'" . addslashes($val) . "',";
                }
                $return = substr_replace($return, "", -1);
                $return .= ");\n";
            }

            $return .= "\n\n\n";
        }

        //save file
        $file_name = 'DB-BACKUP-' . time() . '.sql';
        $file      = 'public/backup/DB-BACKUP-' . $file_name;
        $handle    = fopen($file, 'w+');
        fwrite($handle, $return);
        fclose($handle);

        $databasebackup          = new \App\DatabaseBackup();
        $databasebackup->file    = $file_name;
        $databasebackup->user_id = Auth::id();

        $databasebackup->save();

        return back()->with('success', _lang('Backup Created Sucessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_database_backup($id) {
        $databasebackup = \App\DatabaseBackup::find($id);
        $file           = 'public/backup/DB-BACKUP-' . $databasebackup->file;
        return response()->download($file);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_database_backup($id) {
        $databasebackup = \App\DatabaseBackup::find($id);
        $file           = 'public/backup/DB-BACKUP-' . $databasebackup->file;
        $databasebackup->delete();
        unlink($file);

        return redirect()->route('database_backups.list')->with('success', _lang('Deleted Sucessfully'));
    }

    public function remove_cache(Request $request) {
        $this->validate($request, [
            'cache' => 'required',
        ]);

        if (isset($_POST['cache']['view_cache'])) {
            Artisan::call('view:clear');
        }

        if (isset($_POST['cache']['application_cache'])) {
            Artisan::call('cache:clear');
        }

        return back()->with('success', _lang('Cache Removed Sucessfully'));
    }

}
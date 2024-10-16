<?php

use App\Cash;
use App\Stock;
use App\Company;
use App\Invoice;
use App\BlockDte;
use App\TipoDocumento;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

if (!function_exists('_lang')) {
    function _lang($string = '') {

        $target_lang = get_language();

        if ($target_lang == '') {
            $target_lang = "language";
        }

        if (file_exists(resource_path() . "/language/$target_lang.php")) {
            include resource_path() . "/language/$target_lang.php";
        } else {
            include resource_path() . "/language/language.php";
        }

        if (array_key_exists($string, $language)) {
            return $language[$string];
        } else {
            return $string;
        }
    }
}

if (!function_exists('_dlang')) {
    function _dlang($string = '') {

        //Get Target language
        $target_lang = get_option('language');

        if ($target_lang == '') {
            $target_lang = 'language';
        }

        if (file_exists(resource_path() . "/language/$target_lang.php")) {
            include resource_path() . "/language/$target_lang.php";
        } else {
            include resource_path() . "/language/language.php";
        }

        if (array_key_exists($string, $language)) {
            return $language[$string];
        } else {
            return $string;
        }
    }
}

if (!function_exists('startsWith')) {
    function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}

if (!function_exists('company_id')) {
    function company_id() {
        
        if (Auth::user()->company_id != null || Auth::user()->company_id != 0) {

            if (Session::get('company')==null) {
                session()->put('company', Company::find(Auth::user()->company_id));
            }
            return Session::get('company')->id;
        }
        throw new Exception('Sucursal ID not found');
        // return Auth::user()->id;
    }
}


if (!function_exists('get_initials')) {
    function get_initials($string) {
        $words    = explode(" ", $string);
        $initials = null;
        foreach ($words as $w) {
            $initials .= $w[0];
        }
        return $initials;
    }
}

if (!function_exists('create_option')) {
    function create_option($table, $value, $display, $selected = '', $where = NULL, $customdata = "") {
        $options   = '';
        $condition = '';
        if ($where != NULL) {
            $condition .= "WHERE ";
            foreach ($where as $key => $v) {
                $condition .= $key . "'" . $v . "' ";
            }
        }

        if (is_array($display)) {
            $display_array = $display;
            $display       = $display_array[0];
            $display1      = $display_array[1];
        }

        $query = DB::select("SELECT * FROM $table $condition");
        
        foreach ($query as $d) {
            $custom = $customdata != ""? "data-".$customdata."='".$d->{$customdata}."'":"";

            if ($selected != '' && $selected == $d->$value) {
                if (!isset($display_array)) {
                    $options .= "<option value='" . $d->$value . "' selected='true' ".$custom.">" . ucwords($d->$display) . "</option>";
                } else {
                    $options .= "<option value='" . $d->$value . "' selected='true' ".$custom.">" . ucwords($d->$display . ' - ' . $d->$display1) . "</option>";
                }
            } else {
                if (!isset($display_array)) {
                    $options .= "<option value='" . $d->$value . "' ".$custom.">" . ucwords($d->$display) . "</option>";
                } else {
                    $options .= "<option value='" . $d->$value . "' ".$custom.">" . ucwords($d->$display . ' - ' . $d->$display1) . "</option>";
                }
            }
        }
        echo $options;
    }
}

if (!function_exists('object_to_string')) {
    function object_to_string($object, $col, $quote = false) {
        $string = "";
        foreach ($object as $data) {
            if ($quote == true) {
                $string .= "'" . $data->$col . "', ";
            } else {
                $string .= $data->$col . ", ";
            }
        }
        $string = substr_replace($string, "", -2);
        return $string;
    }
}

if (!function_exists('get_table')) {
    function get_table($table, $where = NULL) {
        $condition = "";
        if ($where != NULL) {
            $condition .= "WHERE ";
            foreach ($where as $key => $v) {
                $condition .= $key . "'" . $v . "' ";
            }
        }
        $query = DB::select("SELECT * FROM $table $condition");
        return $query;
    }
}

if (!function_exists('user_count')) {
    function user_count($user_type) {
        $count = \App\User::where("user_type", $user_type)
            ->selectRaw("COUNT(id) as total")
            ->first()->total;
        return $count;
    }
}

if (!function_exists('has_permission')) {
    function has_permission($name) {
        if (Auth::user()->user_type == 'admin') {
            return true;
        }else{
            $permission_list = \Auth::user()->role->permissions;
            $permission      = $permission_list->firstWhere('permission', $name);

            if ($permission != null) {
                return true;
            }

            return false;
        }
    }
}

if (!function_exists('get_logo')) {
    function get_logo() {
        $logo = get_option("logo");
        if ($logo == "") {
            return asset("public/backend/images/company-logo.png");
        }
        return asset("public/uploads/media/logo.jpg");
    }
}

if (!function_exists('get_pdf_logo')) {
    function get_pdf_logo() {
        $logo = get_option("logo");
        if ($logo == "") {
            return public_path("backend/images/company-logo.png");
        }
        return public_path("uploads/company/$logo");
    }
}

if (!function_exists('get_favicon')) {
    function get_favicon() {
        $favicon = get_option("favicon");
        if ($favicon == "") {
            // return asset("public/backend/images/favicon.png");
            return asset("public/uploads/media/logo.jpg");
        }
        return asset("public/uploads/media/$favicon");
    }
}

if (!function_exists('profile_picture')) {
    function profile_picture($profile_picture = '') {
        if ($profile_picture == '') {
            $profile_picture = Auth::user()->profile_picture;
        }

        if ($profile_picture == '') {
            return asset('public/backend/images/avatar.png');
        }

        return asset('public/uploads/profile/' . $profile_picture);
    }
}

if (!function_exists('sql_escape')) {
    function sql_escape($unsafe_str) {
        if (get_magic_quotes_gpc()) {
            $unsafe_str = stripslashes($unsafe_str);
        }
        return $escaped_str = str_replace("'", "", $unsafe_str);
    }
}

if (!function_exists('get_option')) {
    function get_option($name, $optional = '') {
        $value = Cache::get($name);

        if ($value == "") {
            $setting = DB::table('settings')->where('name', $name)->get();
            if (!$setting->isEmpty()) {
                $value = $setting[0]->value;
                Cache::put($name, $value);
            } else {
                $value = $optional;
            }
        }
        return $value;
    }
}

if (!function_exists('get_setting_name_tipodoc')) {
    function get_setting_name_tipodoc($tipodoc_id) {
        $nameSetting = '';
        switch (strval($tipodoc_id)) {
            case '03': // 03 comprobante de credito fiscal
                    $nameSetting = 'ccf_starting';
                break;
            case '01': // 01 factura consumidor final
                    $nameSetting = 'invoice_starting';
                break;
            case '11': // 11 factura exportacion
                    $nameSetting = 'fex_starting';
                break;
            case '05': // 05 NOTA DE CREDITO
                $nameSetting = 'nc_starting';
            break;
            case '06': // 06 Nota de debito
                $nameSetting = 'nd_starting';
            break;
            case '04': // 06 Nota de remision
                $nameSetting = 'nr_starting';
            break;
            case '11': // 11 Factura de exportación
                $nameSetting = 'fex_starting';
            break;
            case '14': // 11 Factura de sujeto excluido
                $nameSetting = 'fse_starting';
            break;
            default:
                $nameSetting = '';
                break;
        }
        return $nameSetting;
    }
}

if (!function_exists('get_invoice_number')) {
    function get_invoice_number($tipodoc_id, $concat_prefix = true) {
        $setting_name_prefix = '';
        switch (strval($tipodoc_id)) {
            case '03': // 03 comprobante de credito fiscal
                    $setting_name_prefix = 'ccf_prefix';
                break;
            case '01': // 01 factura consumidor final
                    $setting_name_prefix = 'invoice_prefix';
                break;
            case '11': // 11 factura exportacion
                    $setting_name_prefix = 'fex_prefix';
                break;
            case '05': // 05 Nota de credito
                $setting_name_prefix = 'nc_prefix';
            break;
            case '06': // 06 Nota de debito
                $setting_name_prefix = 'nd_prefix';
            break;
            case '04': // 06 Nota de remision
                $setting_name_prefix = 'nr_prefix';
            break;
            case '11': // 11 Factura de exportación
                $setting_name_prefix = 'fex_prefix';
            break;
            case '14': // 11 Factura de sujeto excluido
                $setting_name_prefix = 'fse_prefix';
            break;
            default:
                $setting_name_prefix = '';
                break;
        }
        $invoice_number = '';
        if($concat_prefix){
            $invoice_number = get_option($setting_name_prefix); 
        }
        $invoice_number .= get_option(get_setting_name_tipodoc($tipodoc_id));
        return $invoice_number;
    }
}

if (!function_exists('get_setting')) {
    function get_setting($settings, $name, $optional = '') {
        $row = $settings->firstWhere('name', $name);
        if ($row != null) {
            return $row->value;
        }
        return $optional;

    }
}

if (!function_exists('get_array_option')) {
    function get_array_option($name, $key = '', $optional = '') {
        if ($key == '') {
            if (session('language') == '') {
                $key = get_option('language');
                session(['language' => $key]);
            } else {
                $key = session('language');
            }
        }
        $setting = DB::table('settings')->where('name', $name)->get();
        if (!$setting->isEmpty()) {

            $value = $setting[0]->value;
            if (@unserialize($value) !== false) {
                $value = @unserialize($setting[0]->value);

                return isset($value[$key]) ? $value[$key] : $value[array_key_first($value)];
            }

            return $value;
        }
        return $optional;
    }
}

if (!function_exists('get_array_data')) {
    function get_array_data($data, $key = '') {
        if ($key == '') {
            if (session('language') == '') {
                $key = get_option('language');
                session(['language' => $key]);
            } else {
                $key = session('language');
            }
        }

        if (@unserialize($data) !== false) {
            $value = @unserialize($data);
            return isset($value[$key]) ? $value[$key] : $value[array_key_first($value)];
        }

        return $data;

    }
}

if (!function_exists('update_option')) {
    function update_option($name, $value) {
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));

        $data               = array();
        $data['value']      = $value;
        $data['updated_at'] = \Carbon\Carbon::now();
        if (\App\Setting::where('name', $name)->exists()) {
            \App\Setting::where('name', $name)->update($data);
        } else {
            $data['name']       = $name;
            $data['created_at'] = \Carbon\Carbon::now();
            \App\Setting::insert($data);
        }
    }
}

if (!function_exists('timezone_list')) {

    function timezone_list() {
        $zones_array = array();
        $timestamp   = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['ZONE'] = $zone;
            $zones_array[$key]['GMT']  = 'UTC/GMT ' . date('P', $timestamp);
        }
        return $zones_array;
    }

}

if (!function_exists('create_timezone_option')) {

    function create_timezone_option($old = "") {
        $option    = "";
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $selected = $old == $zone ? "selected" : "";
            $option .= '<option value="' . $zone . '"' . $selected . '>' . 'GMT ' . date('P', $timestamp) . ' ' . $zone . '</option>';
        }
        echo $option;
    }

}

if (!function_exists('get_country_list')) {
    function get_country_list($old_data = '') {
        if ($old_data == '') {
            echo file_get_contents(app_path() . '/Helpers/country.txt');
        } else {
            $pattern      = '<option value="' . $old_data . '">';
            $replace      = '<option value="' . $old_data . '" selected="selected">';
            $country_list = file_get_contents(app_path() . '/Helpers/country.txt');
            $country_list = str_replace($pattern, $replace, $country_list);
            echo $country_list;
        }
    }
}

/* Method use for Global amount only */
if (!function_exists('g_decimal_place')) {
    function g_decimal_place($number, $symbol = '', $format = '') {
        if ($symbol == '') {
            return money_format_2($number, $format);
        }

        if ($currency_position == 'left') {
            return $symbol . ' ' . money_format_2($number, $format);
        } else {
            return money_format_2($number, $format) . ' ' . $symbol;
        }
    }
}

if (!function_exists('decimalPlace')) {
    function decimalPlace($number, $symbol = '') {
        if ($symbol == '') {
            return money_format_2($number);
        }

        if (get_currency_position() == 'right') {
            return money_format_2($number) . ' ' . $symbol;
        } else {
            return $symbol . ' ' . money_format_2($number);
        }
    }
}

if (!function_exists('money_format_2')) {
    function money_format_2($floatcurr) {
        $floatcurr = is_numeric($floatcurr) ? (float) $floatcurr : 0.0;
        $decimal_place = get_option('decimal_places', 2);
        $decimal_sep   = get_option('decimal_sep', '.');
        $thousand_sep  = get_option('thousand_sep', ',');

        return number_format($floatcurr, $decimal_place, $decimal_sep, $thousand_sep);
    }
}

if (!function_exists('load_language')) {
    function load_language($active = '') {
        $path    = resource_path() . "/language";
        $files   = scandir($path);
        $options = "";

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($name == "." || $name == "" || $name == "language") {
                continue;
            }

            $selected = "";
            if ($active == $name) {
                $selected = "selected";
            } else {
                $selected = "";
            }

            $options .= "<option value='$name' $selected>" . $name . "</option>";

        }
        echo $options;
    }
}

if (!function_exists('get_language_list')) {
    function get_language_list() {
        $path  = resource_path() . "/language";
        $files = scandir($path);
        $array = array();

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($name == "." || $name == "" || $name == "language" || $name == "flags") {
                continue;
            }

            $array[] = $name;

        }
        return $array;
    }
}

if (!function_exists('process_string')) {

    function process_string($search_replace, $string) {
        $result = $string;
        foreach ($search_replace as $key => $value) {
            $result = str_replace($key, $value, $result);
        }
        return $result;
    }

}

if (!function_exists('permission_list')) {
    function permission_list() {

        $permission_list = \App\AccessControl::where("role_id", Auth::user()->role_id)
            ->pluck('permission')->toArray();
        return $permission_list;
    }
}

if (!function_exists('get_currency_list')) {
    function get_currency_list($old_data = '', $serialize = false) {
        $currency_list = file_get_contents(app_path() . '/Helpers/currency.txt');

        if ($old_data == "") {
            echo $currency_list;
        } else {
            if ($serialize == true) {
                $old_data = unserialize($old_data);
                for ($i = 0; $i < count($old_data); $i++) {
                    $pattern       = '<option value="' . $old_data[$i] . '">';
                    $replace       = '<option value="' . $old_data[$i] . '" selected="selected">';
                    $currency_list = str_replace($pattern, $replace, $currency_list);
                }
                echo $currency_list;
            } else {
                $pattern       = '<option value="' . $old_data . '">';
                $replace       = '<option value="' . $old_data . '" selected="selected">';
                $currency_list = str_replace($pattern, $replace, $currency_list);
                echo $currency_list;
            }
        }
    }
}

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($currency_code) {
        include app_path() . '/Helpers/currency_symbol.php';

        if (array_key_exists($currency_code, $currency_symbols)) {
            return $currency_symbols[$currency_code];
        }
        return "";

    }
}

if (!function_exists('status')) {
    function status($status, $class = 'success') {
        if ($class == 'danger') {
            return "<span class='badge badge-danger'>$status</span>";
        } else if ($class == 'success') {
            return "<span class='badge badge-success'>$status</span>";
        } else if ($class == 'info') {
            return "<span class='badge badge-dark'>$status</span>";
        }
    }
}

if (!function_exists('user_status')) {
    function user_status($status) {
        if ($status == 1) {
            return "<span class='badge badge-danger'>" . _lang('Active') . "</span>";
        } else if ($status == 0) {
            return "<span class='badge badge-success'>" . _lang('In Active') . "</span>";
        }
    }
}

if (!function_exists('file_icon')) {
    function file_icon($mime_type) {
        static $font_awesome_file_icon_classes = [
            // Images
            'image'                                                                     => 'fa-file-image',
            // Audio
            'audio'                                                                     => 'fa-file-audio',
            // Video
            'video'                                                                     => 'fa-file-video',
            // Documents
            'application/pdf'                                                           => 'fa-file-pdf',
            'application/msword'                                                        => 'fa-file-word',
            'application/vnd.ms-word'                                                   => 'fa-file-word',
            'application/vnd.oasis.opendocument.text'                                   => 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml'            => 'fa-file-word',
            'application/vnd.ms-excel'                                                  => 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml'               => 'fa-file-excel',
            'application/vnd.oasis.opendocument.spreadsheet'                            => 'fa-file-excel',
            'application/vnd.ms-powerpoint'                                             => 'fa-file-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml'              => 'ffa-file-powerpoint',
            'application/vnd.oasis.opendocument.presentation'                           => 'fa-file-powerpoint',
            'text/plain'                                                                => 'fa-file-alt',
            'text/html'                                                                 => 'fa-file-code',
            'application/json'                                                          => 'fa-file-code',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fa-file-powerpoint',
            // Archives
            'application/gzip'                                                          => 'fa-file-archive',
            'application/zip'                                                           => 'fa-file-archive',
            'application/x-zip-compressed'                                              => 'fa-file-archive',
            // Misc
            'application/octet-stream'                                                  => 'fa-file-archive',
        ];

        if (isset($font_awesome_file_icon_classes[$mime_type])) {
            return $font_awesome_file_icon_classes[$mime_type];
        }

        $mime_group = explode('/', $mime_type, 2)[0];
        return (isset($font_awesome_file_icon_classes[$mime_group])) ? $font_awesome_file_icon_classes[$mime_group] : 'fa-file';
    }
}

if (!function_exists('xss_clean')) {
    function xss_clean($data) {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data     = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }
}

// convert seconds into time
if (!function_exists('time_from_seconds')) {
    function time_from_seconds($seconds) {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds - ($h * 3600) - ($m * 60);
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}

if (!function_exists('current_day_income')) {
    function current_day_income() {
        $company_id = company_id();
        $date       = date("Y-m-d");

        $query = DB::select("SELECT IFNULL(SUM(amount),0) as total FROM transactions
		WHERE trans_date='$date' AND dr_cr='cr'");
        return $query[0]->total;
    }
}

if (!function_exists('current_day_expense')) {
    function current_day_expense() {
        $company_id = company_id();
        $date       = date("Y-m-d");

        $query = DB::select("SELECT IFNULL(SUM(amount),0) as total FROM transactions
		WHERE trans_date='$date' AND dr_cr='dr'");
        return $query[0]->total;
    }
}

if (!function_exists('current_month_income')) {
    function current_month_income() {
        $company_id = company_id();
        $month      = date('m');
        $year       = date('Y');

        $monthly_income = \App\Transaction::selectRaw("IFNULL(SUM(amount),0) as total")
            ->where("dr_cr", "cr")
            ->whereMonth("trans_date", $month)
            ->whereYear("trans_date", $year)
            ->first()->total;
        return $monthly_income;
    }
}

if (!function_exists('current_month_expense')) {
    function current_month_expense() {
        $company_id = company_id();
        $month      = date('m');
        $year       = date('Y');

        $monthly_expense = \App\Transaction::selectRaw("IFNULL(SUM(amount),0) as total")
            ->where("dr_cr", "dr")
            ->whereMonth("trans_date", $month)
            ->whereYear("trans_date", $year)
            ->first()->total;
        return $monthly_expense;
    }
}

if (!function_exists('get_financial_balance')) {

    function get_financial_balance() {

        $result = DB::select("SELECT b.*,((SELECT IFNULL(opening_balance,0)
   FROM accounts WHERE id = b.id) + (SELECT IFNULL(SUM(amount), 0)
   FROM transactions WHERE dr_cr = 'cr' AND account_id = b.id)) - (SELECT IFNULL(SUM(amount),0)
   FROM transactions WHERE dr_cr = 'dr' AND account_id = b.id) as balance
   FROM accounts as b");
        return $result;

    }

}

if (!function_exists('invoice_status')) {
    function invoice_status($status) {
        if ($status == 'Unpaid') {
            return "<span class='badge badge-danger'>"._lang($status)."</span>";
        } else if ($status == 'Paid') {
            return "<span class='badge badge-success'>"._lang($status)."</span>";
        } else if ($status == 'Partially_Paid') {
            return "<span class='badge badge-info'>"._lang($status)."</span>";
            // return "<span class='badge badge-info'>" . str_replace('_', ' ', $status) . "</span>";
        } else if ($status == 'Canceled') {
            return "<span class='badge badge-danger'>"._lang($status)."</span>";
        }
    }
}

if (!function_exists('create_payment_method')) {
    function create_payment_method($methodName) {
        $payment_method = \App\PaymentMethod::where('name', $methodName);
        if ($payment_method->exists()) {
            return $payment_method->first()->id;
        } else {
            $payment_method       = new \App\PaymentMethod();
            $payment_method->name = $methodName;
            $payment_method->save();
            return $payment_method->id;
        }
    }
}

if (!function_exists('increment_invoice_number')) {
    function increment_invoice_number($tipodoc_id, $nextValue = null) {
        if ($tipodoc_id == '01') {
            $data               = array();
            $data['value']      = intval(get_option('ticket_starting', 1001)) + 1;
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (\App\Setting::where('name', 'ticket_starting')->exists()) {
                \App\Setting::where('name', 'ticket_starting')->update($data);
            } else {
                $data['name']       = 'ticket_starting';
                $data['created_at'] = date('Y-m-d H:i:s');
                \App\Setting::insert($data);
            }
            \Cache::forget('ticket_starting');
        }

        $setting_name = get_setting_name_tipodoc($tipodoc_id);
        $data               = array();
        $data['value']      = $nextValue==null?(intval(get_option($setting_name, 1001)) + 1):$nextValue;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (\App\Setting::where('name', $setting_name)->exists()) {
            \App\Setting::where('name', $setting_name)->update($data);
        } else {
            $data['name']       = $setting_name;
            $data['created_at'] = date('Y-m-d H:i:s');
            \App\Setting::insert($data);
        }
        \Cache::forget($setting_name);
    }
}

if (!function_exists('increment_quotation_number')) {
    function increment_quotation_number() {
        $data               = array();
        $data['value']      = get_option('quotation_starting', 1001) + 1;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (\App\Setting::where('name', "quotation_starting")->exists()) {
            \App\Setting::where('name', 'quotation_starting')->update($data);
        } else {
            $data['name']       = 'quotation_starting';
            $data['created_at'] = date('Y-m-d H:i:s');
            \App\Setting::insert($data);
        }
        \Cache::forget('quotation_starting');
    }
}

if (!function_exists('update_stock')) {
    function update_stock($product_id, $quantity, $sign = '+', $company_id = null) {
        $company_id = $company_id??company_id();

        // $purchase = DB::table('purchase_order_items')->where('product_id', $product_id)->sum('quantity');

        // $purchaseReturn = DB::table('purchase_return_items')->where('product_id', $product_id)->sum('quantity');

        // $sales = DB::table('invoice_items')->where('item_id', $product_id)->sum('quantity');

        // $salesReturn = DB::table('sales_return_items')->where('product_id', $product_id)->sum('quantity');

        //Update Stock
        $stock = \App\Stock::where("product_id", $product_id)->where('company_id', $company_id)->first();
        // $stock = Stock::where("item_id", $product_id)->where('company_id', $company_id)
        // ->join('products as p', 'p.id', 'product_id')->select('current_stocks.*')->first();
        
        if ($stock == null) {
            // $stock->quantity = ($purchase + $salesReturn) - ($sales + $purchaseReturn);
            $stock = new Stock(['product_id'=>$product_id, 'quantity'=>0, 'company_id'=>$company_id]);
        }

        if ($sign == '+') {
            $stock->quantity = $stock->quantity+$quantity;
        }else{
            $stock->quantity = $stock->quantity-$quantity;
        }
        $stock->save();

    }
}

if (!function_exists('object_to_tax')) {
    function object_to_tax($object, $col, $quote = false) {
        if ($object->isEmpty()) {
            return _lang('N/A');
        }

        $string = "";
        foreach ($object as $data) {
            if ($quote == true) {
                $string .= "'" . $data->$col . "'<br>";
            } else {
                $string .= $data->$col . "<br>";
            }
        }
        return $string;
    }
}

/* Intelligent Functions */
if (!function_exists('get_language')) {
    function get_language($force = false) {

        $language = $force == false ? session('language') : '';

        if ($language == '') {
            $language = Cache::get('language');
        }

        if ($language == '') {
            $language = get_option('language');
            if ($language == '') {
                \Cache::put('language', 'language');
            } else {
                \Cache::put('language', $language);
            }

        }
        return $language;
    }
}

if (!function_exists('get_currency_position')) {
    function get_currency_position() {
        $currency_position = Cache::get('currency_position');

        if ($currency_position == '') {
            $currency_position = get_option('currency_position');
            \Cache::put('currency_position', $currency_position);
        }

        return $currency_position;
    }
}

if (!function_exists('currency')) {
    function currency($currency = '') {

        if ($currency == '') {
            $currency = get_option('currency', get_option('currency', 'USD'));
        }

        return html_entity_decode(get_currency_symbol($currency), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_date_format')) {
    function get_date_format() {
        $date_format = Cache::get('date_format');

        if ($date_format == '') {
            $date_format = get_option('date_format');
            \Cache::put('date_format', $date_format);
        }

        return $date_format;
    }
}

if (!function_exists('get_time_format')) {
    function get_time_format() {
        $time_format = Cache::get('time_format');

        if ($time_format == '') {
            $time_format = get_option('time_format');
            \Cache::put('time_format', $time_format);
        }

        $time_format = $time_format == 24 ? 'H:i' : 'h:i A';

        return $time_format;
    }
}

if(!function_exists('SubirImagen')){

    function SubirImagen($files, $carpeta) {

        $ext = $files->getClientOriginalExtension();
        $nombre = str_replace(['.' . $ext, ' '], ['', ''],$files->getClientOriginalName());
        $identificador = $nombre . '.' . $ext;
        $files->move(public_path("/uploads/$carpeta"), $identificador);

        return "public/uploads/$carpeta/$identificador";
    }
}

if(!function_exists('GetCompanies')){
    function GetCompanies() {
        if(in_array('companies.change', permission_list()) || Auth::user()->user_type == 'admin'){
            return Company::all();
        }else{
            return [App\Company::find(company_id())];
        }
    }
}

if(!function_exists('dollarToText')){

    function dollarToText($numero, $local = 'es') {
        $entera = intval($numero);
        $decimal = $numero - $entera;
        $decimal = substr_replace(strval(round($decimal, 2)), '', 0, 2);
        $decimal = str_pad($decimal, 2, '0', STR_PAD_RIGHT);
        $digit = new NumberFormatter($local, NumberFormatter::SPELLOUT);
        return strtoupper($digit->format($entera)).' CON '.($decimal==''?'0':$decimal).'/100';
    }
}

if(!function_exists('generateUUID')){

    function generateUUID() {
        return Str::uuid();
    }
}

if(!function_exists('generateNumeroControl')){
    /**
     * parametro id de tipo de documento
     */
    function generateNumeroControl($tipodoc_id) {
        $company_id = company_id();
        $parte1 = 'DTE';
        $parte2 = $tipodoc_id;
        $sucursal = (str_pad($company_id, 4, '0', STR_PAD_LEFT));
        $puntoVenta = (str_pad($company_id, 4, '0', STR_PAD_LEFT));
        $parte3 = $sucursal.$puntoVenta;
        $parte4 = "";

        // $lastInvoice = DB::table('invoices')->orderBy('id', 'desc')
        //                 ->selectRaw("year(invoice_date) as year, CAST(SUBSTRING_INDEX(numero_control, '-', -1) as unsigned) as correlativo")
        //                 ->where('tipodoc_id', '=', $tipodoc_id)
        //                 ->where(function ($query) {
        //                     $query->where('status_mh', 1)
        //                         ->orWhereNull('status_mh');
        //                 })
        //                 ->first();

        // if ($lastInvoice == null) {
        //     $parte4 = '000000000000001';
        // }else{
        //     if ($lastInvoice->correlativo == 999999999999999 || date('Y') != $lastInvoice->year) {
        //         $parte4 = '000000000000001';
        //     }else{
        //         $parte4 = str_pad(($lastInvoice->correlativo+1), 15, '0', STR_PAD_LEFT);
        //     }
        // }

        $year = Invoice::where(['tipodoc_id'=> $tipodoc_id])->selectRaw("YEAR(invoice_date) as year")->orderByRaw("invoice_date desc, invoice_time asc")->first()->year??date('Y');
        if(date('Y')>$year){
            $contador = 1;
            BlockDte::where('type_dte', '=', $tipodoc_id)->update(['correlativo'=>$contador]);
        }else{
            $contador = BlockDte::where('type_dte', '=', $tipodoc_id)->pluck('correlativo')->first();
        }

        $parte4 = date('y').str_pad(($contador), 13, '0', STR_PAD_LEFT);
        
        return $parte1.'-'.$parte2.'-'.$parte3.'-'.$parte4;
    }
}

if(!function_exists('calculaExentoSujetoGravado')){
    function calculaExentoSujetoGravado($taxes, $sub_total, & $noSujetoSum, &$exentoSum, &$gravadoSum, 
    &$noSujeto, &$exento, &$gravado) {
        $currency = currency();
        if(count($taxes)==0){
            $noSujeto = decimalPlace($sub_total, $currency);
            $noSujetoSum += $sub_total;
        }   else if(count($taxes) == 1 && $taxes[0]->amount == 0){
            $exento = decimalPlace($sub_total, $currency);
            $exentoSum += $sub_total;
        }else {
            $gravado = decimalPlace($sub_total, $currency);
            $gravadoSum += $sub_total;
        }
    }
}

if(!function_exists('get_cash')){
    function get_cash() {
        return Cash::where('company_id', company_id())->first();
    }
}

if (!function_exists('generateUrl')) {
    function generateUrl($invoice)
    {
        $ambiente = env('API_AMBIENTE_MH');
        $codigo_generacion = $invoice->codigo_generacion;
        $fechaEmi = date('Y-m-d', strtotime($invoice->created_at));

        // Construye la URL con los parámetros necesarios
        $url = "https://admin.factura.gob.sv/consultaPublica?ambiente=$ambiente&codGen=$codigo_generacion&fechaEmi=$fechaEmi";

        return $url;
    }
}

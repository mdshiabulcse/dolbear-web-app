<?php

namespace App\Repositories\Admin;

use App\Models\Addon;
use App\Repositories\Interfaces\Admin\AddonInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AddonRepository implements AddonInterface
{
    public function all()
    {
        return Addon::latest();
    }

    public function paginate($limit)
    {
        return $this->all()->paginate($limit);
    }

    public function get($id)
    {
        return Addon::find($id);
    }

    public function install($request)
    {
        $rand_str_dir = Str::random(10);
        $verify_code = $this->valid_purchase_code($request->purchase_code);
        if ($verify_code == 'unverified') {
            return __('There is a problem with your purchase code.Please contact with Envato support team.');
        }
        if (!class_exists('ZipArchive')) {
            return __('ZipArchive class is not available on your server.Please contact with your hosting provider.');
        }
        $dir = 'public/addons';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        //checking zip file
        $base_path = base_path('public/addons/' . $rand_str_dir);
        $storage_path = Storage::disk('local')->put('addons', $request->addon_zip_file);
        $zip = new ZipArchive();
        $open_able = $zip->open(storage_path('app/' . $storage_path));
        if ($open_able !== true) {
            return __('The zip archive is corrupt.');
        }
        $zip->extractTo($base_path);
        $zip->close();
        Storage::disk('local')->delete($storage_path);

        //checking config.json file
        $read_json = file_get_contents(base_path('public/addons/' . $rand_str_dir . '/config.json'));
        $decoded_json = json_decode($read_json, true);

        if ($decoded_json['required_cms_version'] > settingHelper('current_version')) {
            File::deleteDirectory($base_path);
            return __('This version is not capable of installing , Please update your CMS.');
        }
        $errors = [];
        if (!empty($decoded_json['directories'])) {
            foreach ($decoded_json['directories'] as $directory) {
                if (!is_dir(base_path($directory))) {
                    try {
                        mkdir(base_path($directory), 0777, true);
                    } catch (\Exception $e) {
                        $message = 'Unable to create directory ' . base_path($directory) . ' Please check your permission.';
                        $errors[] = $message;
                    }
                }
            }
            if (count($errors) > 0) {
                File::deleteDirectory($base_path);
                return implode('<br>', $errors);
            }
        }
        if (!empty($decoded_json['files'])) {
            foreach ($decoded_json['files'] as $file) {
                try {
                    File::copy(base_path('public/addons/' . $rand_str_dir . '/' . $file['from_directory']), base_path($file['to_directory']));
                } catch (\Exception $e) {
                    $message = 'Unable to create file ' . base_path($file) . ' Please check your permission.';
                    $errors[] = $message;
                }
            }
            if (count($errors) > 0) {
                File::deleteDirectory($base_path);
                return implode('<br>', $errors);
            }
        }
        $message = __('Addon Updated successfully');
        $addon = Addon::where('addon_identifier', $decoded_json['addon_identifier'])->first();

        if (!$addon) {
            $message = __('Addon Updated successfully');
            $addon = new Addon();
            $addon->name = $decoded_json['name'];
            $addon->addon_identifier = $decoded_json['addon_identifier'];
            $addon->status = 1;
        }
        $addon->purchase_code = $request->purchase_code;
        $addon->version = $decoded_json['version'];
        $addon->image = $decoded_json['addon_banner'];
        $addon->save();
        File::deleteDirectory($base_path);
        Artisan::call('migrate', ['--path' => 'database/migrations/' . $decoded_json['addon_identifier']]);
        Cache::forget('addons');
        return [
            'message' => $message,
            'type' => 'success'
        ];
    }

    public function statusChange($request)
    {
        $addon = $this->get($request['id']);
        $addon->status = $request['status'];
        $addon->save();
        Cache::forget('addons');
        return true;
    }

    public function activePlugin()
    {
        return Addon::where('status', 1)->pluck('addon_identifier')->toArray();
    }

    public function activeAddons()
    {
        return Addon::where('status', 1)->selectRaw('id,name,addon_identifier,version')->get();
    }

    function valid_purchase_code($purchase_code = '')
    {
        $purchase_code = urlencode($purchase_code);
        $verified = "unverified";
        if (!empty($purchase_code) && $purchase_code != '' && $purchase_code != NULL && strlen($purchase_code) > 24):
            $url = 'https://api.envato.com/v3/market/author/sale?code=' . $purchase_code;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Envato API Wrapper PHP)');

            $header = array();
            $header[] = 'Content-length: 0';
            $header[] = 'Content-type: application/json';
            $header[] = 'Authorization: Bearer 5CZXrrM34RPf7ukUzCKqod2BAcQJNKE6';

            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $data = curl_exec($ch);
            curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (!empty($data)):
                $result = json_decode($data, true);
                if (isset($result['buyer']) && isset($result['item']['id'])):
                    return $result;
                endif;
            endif;
        endif;
        return $verified;
    }
}

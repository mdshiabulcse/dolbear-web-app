<?php

namespace App\Http\Controllers\Admin\Setup;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\SettingInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PreferenceController extends Controller
{
    private $settings;

    public function __construct(SettingInterface $settings)
    {
        $this->settings     = $settings;
    }

    public function index(){
        return view('admin.system-setup.preference');
    }

    public function update(Request $request)
    {
        $data = $request->all();
        if (arrayCheck('maintenance_secret', $data)):
            $command = $request['maintenance_secret'];
            if ($this->settings->update($request)):
                if (config('app.demo_mode')):
                    Toastr::info(__('This function is disabled in demo server.'));
                    return redirect()->back();
                endif;

                Artisan::call('down --refresh=15 --secret='.$command);
                Toastr::success(__('Updated Successfully'));
                $url = route('home').'/'.$request['maintenance_secret'];
                return redirect('/'.$command);
            else:
                Toastr::error(__('Something went wrong, please try again'));
                return back();
            endif;
        endif;
        if (arrayCheck('live_currency_access_key', $data)):
            if ($this->settings->update($request)):
                if (config('app.demo_mode')):
                    Toastr::info(__('This function is disabled in demo server.'));
                    return redirect()->back();
                endif;

                Toastr::success(__('Updated Successfully'));
            else:
                Toastr::error(__('Something went wrong, please try again'));
            endif;
            return back();
        endif;
        if (config('app.demo_mode')):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;
        if($this->settings->statusChange($request['data'])):
            if ($request['data']['id'] == 'maintenance_mode'):
                Artisan::call('up');
            endif;

            if ($request['data']['id'] == 'migrate_web'):
                if (is_dir('resources/views/admin/store-front')):
                    envWrite('MOBILE_MODE', true);
                    Artisan::call('optimize:clear');
                else:
                    $response['message']    = __('migrate_permission');
                    $response['title']      = __('error');
                    $response['status']     = 'error';
                    $response['type']       = 'migrate_error';
                    return response()->json($response);
                endif;
            endif;

            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            if (getArrayValue('id', $request['data']) == 'live_api_currency'):
                $response['type']       = 'live_currency';
            endif;
        else:
            $response['message']    = __('Something went wrong, please try again');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
        endif;
        return response()->json($response);

    }
}

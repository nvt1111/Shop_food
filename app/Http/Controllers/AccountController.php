<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPassword;
use App\Mail\VerifyAccount;
use App\Models\Customer;
use App\Models\CustomerResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Mail;

class AccountController extends Controller
{
    public function login()
    {
        return view('account.login');
    }

    public function logout()
    {
        auth('cus')->logout();
        return redirect()->route('account.login')->with('ok', 'Logout successgully');
    }

    public function check_login(Request $req)
    {
        $req->validate([
            'email' => 'required|exists:customers',
            'password' => 'required',
        ]);

        $data = $req->only('email', 'password');

        $check = auth('cus')->attempt($data); // check trong bảng custom

        if ($check) {
            if (auth('cus')->user()->email_verified_at == '') {
                auth('cus')->logout();
                return redirect()->back()->with('No', 'Your account is not verify');
            }
            return redirect()->route('home.index')->with('ok', 'Welcome back');
        }

        return redirect()->back()->with('no', 'Your accout invalid');
    }

    public function register()
    {
        return view('account.register');
    }

    public function check_register(Request $req)
    {
        $req->validate([
            'name' => 'required|min:6|max:100',
            'email' => 'required|email|min:6|max:100|unique:customers',
            'password' => 'required|min:4',
            'confirm_password' => 'required|same:password',
        ], [
            'name.required' => 'Họ tên không được để trống',
            'name.min' => 'Họ tên tối thiểu là 6 ký tự'
        ]);

        $data = $req->only('name', 'email', 'phone', 'address', 'gender');

        $data['password'] = bcrypt($req->password);

        if ($acc = Customer::create($data)) {
            Mail::to($acc->email)->send(new VerifyAccount($acc));
            return redirect()->route('account.login')->with('ok', 'Register successfully, please check your email to verify account');
        }
        return redirect()->back()->with('no', 'Something error');
    }

    public function verify($email)
    {
        $acc = Customer::where('email', $email)->whereNull('email_verified_at')->firstOrFail(); //có thì lấy không thì là 404

        Customer::where('email', $email)->update(['email_verified_at' => date('Y-m-d')]);

        return redirect()->route('account.login')->with('ok', 'Verify successfully, you can login');
    }

    public function change_password()
    {
        return view('account.change_password');
    }

    public function check_change_password(Request $req)
    {
        $auth = auth('cus')->user(); // du lieu customer 
        $req->validate([
            'old_password' => ['required', function ($attr, $value, $fail)  use ($auth) { // atttr: cái hiện tại, $value trên form

                if (!Hash::check($value, $auth->password)) {
                    $fail('Your password it not match');
                }
            }],
            'password' => 'required|min:4',
            'confirm_password' => 'required|same:password'
        ]);

        $data['password'] = bcrypt($req->password);
        $check = $auth->update($data);
        if ($check) {
            auth('cus')->logout();
            return redirect()->route('account.login')->with('ok', 'Thành công update');
        }
        return redirect()->back()->with('ok', 'Lỗi update');
    }

    public function forgot_password()
    {
        return view('account.change_password');
    }

    public function check_forgot_password(Request $req)
    {
        $req->validate([
            'email' => 'required|exists:customers',
        ]);

        $customer = Customer::where('email', $req->email)->first();

        $token = \Str::random(40);

        $tokenData = [
            'email' => $req->email,
            'token' => $token
        ];

        if(CustomerResetToken::create($tokenData)){
            Mail::to($req->email)->send(new ForgotPassword($customer, $token))->with('ok', 'Send mail successfully');
        }

        return redirect()->back()->with('no', 'Something error, please check again');
    }

    public function profile()
    {
        $auth = auth('cus')->user();
        return view('account.profile', compact('auth'));
    }

    public function check_profile(Request $req)
    {

        $auth = auth('cus')->user();

        $req->validate([
            'name' => 'required|min:6|max:100',
            'email' => 'required|email|min:6|max:100|unique:customers,email,' . $auth->id, // loại id này ra
            'password' => ['required', function ($attr, $value, $fail) {
                $auth = auth('cus')->user();
                if (!Hash::check($value, $auth->password)) {
                    return $fail('Your password is not match');
                }
            }],
        ], [
            'name.required' => 'Họ tên không được để trống',
            'name.min' => 'Họ tên tối thiểu là 6 ký tự'
        ]);
        $data = $req->only('name', 'email', 'phone', 'address', 'gender');

        $check = $auth->update($data);
        if ($check) {
            return redirect()->back()->with('no', 'Some things erorr');
        }
        return redirect()->back()->with('ok', 'Update your profile');
    }

    public function reset_password($token)
    {
        $tokenData = CustomerResetToken::CheckToken($token);

        return view('account.reset_password');
    }

    public function check_reset_password($token)
    {
        request()->validate([
            'password' => 'required|min:4',
            'confirm_password' => 'required|same:password'
        ]);
        $tokenData = CustomerResetToken::CheckToken($token);
        $customer = $tokenData->customer;
        $data = [
            'password' => bcrypt(request('password'))
        ];
        $check = $customer->update($data);
        if ($check) {
            return redirect()->back()->with('ok', 'Updat success');
        }
        return redirect()->back()->with('ono', 'Lỗi');
    }
}

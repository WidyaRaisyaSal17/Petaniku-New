<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $credentials = request(['email', 'password']);
    
        if (auth()->attempt($credentials)) {
            $token = Auth::guard('api')->attempt($credentials);
    
            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil',
                'token' => $token,
                'redirect_url' => '/dashboard' // Tambahkan URL dashboard di sini
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah'
        ]);
    }
    

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_member' => 'required',
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'detail_alamat' => 'required',
            'no_hp' => 'required',
            'email' => 'required|email',
            'password' => 'required|same:konfirmasi_password',
            'konfirmasi_password' => 'required|same:password',
        ]);
        if($validator->fails()) {
            return response()->json(
                $validator->errors(),
                422
            );
        
        }
        $input = $request->all();
        $input['password'] = bcrypt($request->password);
        unset($input['konfirmasi_password']);
        $Member = Member::create($input);
        return response() -> json([
            'data' => $Member
        ]);
    }

    public function login_member()
    {
        return view('auth.login_member');
    }

    public function login_member_action(Request $request)
    {   
        //variabel digunakan untuk menerima inputan dari user 
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if($validator->fails()) {
            Session::flash('errors',$validator->errors()->toArray());
            return redirect('/login_member');
        }
        
        $credentials = $request->only('email','password');
       $member = Member::where('email', $request->email)->first();
       if($member){
        if(Auth::guard('webmember')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/');
        } else {
            Session::flash('failed',"Password salah");
            return redirect('/login_member');
        }
        }else {
            Session::flash('failed',"Email tidak ditemukan");
            return redirect('/login_member');
       }
    }

    public function register_member()
    {
        return view('auth.register_member');
    }

    public function register_member_action(Request $request)
{ 
    $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'username' => 'required',
        'email' => 'required|email|unique:users', // Adjusted to validate against 'users' table
        'password' => 'required|same:konfirmasi_password',
        'konfirmasi_password' => 'required|same:password',
        'alamat' => 'required', // Assuming address is required
        'gambar' => 'required|image|mimes:jpg,png,jpeg,webp,svg'
    ]);

    if ($validator->fails()) {
        Session::flash('errors', $validator->errors()->toArray());
        return redirect('/register_member');
    }

    $input = $request->all();
    $input['password'] = Hash::make($request->password);
    unset($input['konfirmasi_password']);

    // Set additional fields
    $input['level'] = 'admin'; // Default to 'admin'
    $input['remember_token'] = Str::random(60); // Generate a random remember token
    $input['created_at'] = now();
    $input['updated_at'] = now();

    // Handle file upload for 'gambar'
    if ($request->has('gambar')) {
        $gambar = $request->file('gambar');
        $nama_gambar = time() . rand(1,9) . '.' . $gambar->getClientOriginalExtension();
        $gambar->move('uploads/profile' , $nama_gambar);
        $input['gambar'] = $nama_gambar;
    }

    $users = User::create($input);

    Session::flash('success', 'Account successfully created!');
    return redirect('/login');
}


    public function logout()
    {
        Session::flush();
        return redirect('/login');
        
    }

    public function logout_member()
    {
        Auth::guard('webmember')->logout();
        Session::flush();
        return redirect('/login_member');
    }
}


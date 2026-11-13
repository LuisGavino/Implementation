<?php

namespace App\Http\Controllers;

use App\Blog;
use App\Category;
use App\Destinations;
use App\Tag;
use App\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Post-login landing route (/home). It is available to any authenticated
     * user and simply sends each role to the right place:
     *   admin    -> administrator dashboard
     *   customer -> customer-facing site
     */
    public function index()
    {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect('/');
    }

    /**
     * Show the administrator dashboard.
     * Protected by the 'role:admin' middleware in routes/web.php.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard()
    {
        $stats = [
            'destinations' => Destinations::count(),
            'categories' => Category::count(),
            'tags' => Tag::count(),
            'blogs' => Blog::count(),
            'users' => User::count(),
        ];

        $recentDestinations = Destinations::latest()->take(5)->get();
        $recentBlogs = Blog::latest()->take(5)->get();

        return view('home', compact('stats', 'recentDestinations', 'recentBlogs'));
    }
}

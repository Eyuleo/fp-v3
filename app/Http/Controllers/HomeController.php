<?php
namespace App\Http\Controllers;

use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        // Get featured services (active services with good ratings)
        $featuredServices = Service::where('is_active', true)
            ->with(['student', 'category'])
            ->withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(6)
            ->get();

        return view('home', compact('featuredServices'));
    }
}

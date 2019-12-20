<?php

namespace App\Http\Controllers\Library;

use App\Category;
use App\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $categories_count = Category::count();
        $books_count = Book::count();

        return view('dashboard.welcome', compact('categories_count', 'books_count'));
    }
}

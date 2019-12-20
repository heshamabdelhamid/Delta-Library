<?php

namespace App\Http\Controllers\Library;

use App\Book;
use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        $books = Book::when($request->search, function ($q) use ($request) {

            return $q->where('name', 'like', '%' . $request->search . '%');
            
            })->when($request->category_id, function ($q) use ($request) {

            return $q->where('category_id', $request->category_id);            

        })->latest()->paginate(5);

        return view('dashboard.books.index', compact('books','categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('dashboard.books.create', compact('categories'));
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'  => 'required',
            'name'  => 'required',
            'image' => 'mimes:jpeg,bmp,png',
            'body' => 'required|mimes:pdf',
            'description' => 'required',
        ]);

        $request_data = $request->except(['image']);

        if ($request->image) {

            Image::make($request->image)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(public_path('uploads/book_images/' . $request->image->hashName()));

            $request_data['image'] = $request->image->hashName();

        }//end of if

        $request_data['body'] = time().'.'. $request->body->getClientOriginalExtension();
        $request->body->move(public_path('uploads/book_files/'), $request_data['body']);

        $book = Book::create($request_data);
        session()->flash('success', __('site.added_successfully'));
        return redirect()->route('dashboard.books.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('dashboard.books.edit', compact('book','categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        $request_data = $request->except(['image','body']);

        if ($request->image) {

            if ($book->image != 'default.png') {

                Storage::disk('public_uploads')->delete('/book_images/' . $book->image);

            }//end of inner if

            Image::make($request->image)
                ->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(public_path('uploads/book_images/' . $request->image->hashName()));

            $request_data['image'] = $request->image->hashName();

        }//end of external if

        if ($request->body) {

            $request_data['body'] = time().'.'. $request->body->getClientOriginalExtension();
            $request->body->move(public_path('uploads/book_files/'), $request_data['body']);

        }

        $book->update($request_data);
        session()->flash('success', __('site.updated_successfully'));
        return redirect()->route('dashboard.books.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        if ($book->image != 'default.png') {

            Storage::disk('public_uploads')->delete('/book_images/' . $book->image);

        }//end of if
        Storage::disk('public_uploads')->delete('/book_files/' . $book->body);

        $book->delete();
        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.books.index');
    }
}

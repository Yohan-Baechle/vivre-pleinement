<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class BookController extends Controller
{
    public function show(): View
    {
        return view('book.index');
    }

    public function checkout(string $offer): View
    {
        abort_unless(in_array($offer, ['livre', 'livre-coaching'], true), 404);

        return view('book.checkout-soon', ['offer' => $offer]);
    }
}

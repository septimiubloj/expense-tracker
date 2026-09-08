<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ManageTransfer;
use App\Http\Requests\Transfers\SaveTransferRequest;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TransfersController extends Controller
{
    public function store(SaveTransferRequest $request, Book $book, ManageTransfer $transfers): RedirectResponse
    {
        $transfers->save($request->user(), $book, $request->validated());

        return to_route('books.transactions.index', $book)->with('status', 'Transfer created.');
    }

    public function update(SaveTransferRequest $request, Book $book, string $transfer, ManageTransfer $transfers): RedirectResponse
    {
        $transfers->save($request->user(), $book, $request->validated(), $transfer);

        return to_route('books.transactions.index', $book)->with('status', 'Transfer updated.');
    }

    public function destroy(Request $request, Book $book, string $transfer, ManageTransfer $transfers): RedirectResponse
    {
        $transfers->delete($request->user(), $book, $transfer);

        return to_route('books.transactions.index', $book)->with('status', 'Transfer deleted.');
    }
}

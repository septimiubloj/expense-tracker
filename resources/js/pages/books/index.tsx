import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import DeleteLedgerItem from '@/components/delete-ledger-item';
import Heading from '@/components/heading';
import LedgerField from '@/components/ledger-field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { destroy, index, store, update } from '@/routes/books';
import { index as accountsIndex } from '@/routes/books/accounts';
import type { Book } from '@/types/ledger';

function BookEditor({ book }: { book?: Book }) {
    const [open, setOpen] = useState(false);
    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant={book ? 'outline' : 'default'}>
                    {book ? 'Edit' : 'Create book'}
                    {book && <span className="sr-only"> {book.name}</span>}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{book ? 'Edit book' : 'Create book'}</DialogTitle>
                    <DialogDescription>
                        Keep a separate ledger for each household or project. Changing currency does not convert
                        existing amounts.
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...(book ? update.form(book.id) : store.form())}
                    onSuccess={() => setOpen(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <LedgerField
                                id="book-name"
                                name="name"
                                label="Name"
                                defaultValue={book?.name}
                                error={errors.name}
                                required
                                maxLength={255}
                                autoFocus
                            />
                            <LedgerField
                                id="book-currency"
                                name="currency_code"
                                label="Currency code"
                                defaultValue={book?.currency_code ?? 'RON'}
                                error={errors.currency_code}
                                required
                                minLength={3}
                                maxLength={3}
                                pattern="[A-Z]{3}"
                                placeholder="RON"
                            />
                            <LedgerField
                                id="book-timezone"
                                name="timezone"
                                label="Timezone"
                                defaultValue={book?.timezone ?? Intl.DateTimeFormat().resolvedOptions().timeZone}
                                error={errors.timezone}
                                required
                                placeholder="Europe/Bucharest"
                            />
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                                    Cancel
                                </Button>
                                <Button disabled={processing}>{book ? 'Save changes' : 'Create book'}</Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

export default function BooksIndex({ books, status }: { books: Book[]; status?: string }) {
    return (
        <>
            <Head title="Books" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading title="Books" description="Choose a ledger to manage its accounts." />
                    <BookEditor />
                </div>
                {status && (
                    <p role="status" className="rounded-lg border p-3 text-sm">
                        {status}
                    </p>
                )}
                {books.length === 0 && (
                    <div className="rounded-xl border border-dashed p-8 text-center">
                        <h2 className="font-medium">Your first book starts here</h2>
                        <p className="text-muted-foreground mt-2 text-sm">
                            Create a book, then add your bank and cash accounts.
                        </p>
                    </div>
                )}
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {books.map((book) => (
                        <article key={book.id} className="flex flex-col gap-4 rounded-xl border p-5">
                            <h2 className="text-lg font-medium">
                                <Link href={accountsIndex(book.id)} className="hover:underline">
                                    {book.name}
                                </Link>
                            </h2>
                            <p className="text-muted-foreground text-sm">
                                {book.currency_code} · {book.timezone}
                            </p>
                            <div className="flex flex-wrap items-center gap-2">
                                <Button asChild>
                                    <Link href={accountsIndex(book.id)}>Open accounts</Link>
                                </Button>
                                <BookEditor book={book} />
                                <DeleteLedgerItem
                                    name={book.name}
                                    action={destroy.url(book.id)}
                                    description="This permanently deletes the book, its categories, and its budget periods. Remove its accounts first."
                                />
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

BooksIndex.layout = { breadcrumbs: [{ title: 'Books', href: index() }] };

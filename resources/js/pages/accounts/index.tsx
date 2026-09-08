import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import DeleteLedgerItem from '@/components/delete-ledger-item';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import { Label } from '@/components/ui/label';
import { currencyPrecision, decimalToMinor, minorToDecimal } from '@/lib/money';
import { index as booksIndex } from '@/routes/books';
import { destroy, store, update } from '@/routes/books/accounts';
import type { Account, Book } from '@/types/ledger';

function AccountEditor({ book, account }: { book: Book; account?: Account }) {
    const [open, setOpen] = useState(false);
    const precision = currencyPrecision(book.currency_code);
    const today = new Intl.DateTimeFormat('en-CA', {
        timeZone: book.timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date());
    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant={account ? 'outline' : 'default'}>
                    {account ? 'Edit' : 'Add account'}
                    {account && <span className="sr-only"> {account.name}</span>}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{account ? 'Edit account' : 'Add account'}</DialogTitle>
                    <DialogDescription>
                        Enter the balance on the opening date in {book.currency_code}. Use a negative amount for debt.
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...(account ? update.form({ book: book.id, account: account.id }) : store.form(book.id))}
                    transform={(data) => ({
                        ...data,
                        opening_balance_minor: decimalToMinor(String(data.opening_balance), precision) ?? 'invalid',
                    })}
                    onSuccess={() => setOpen(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <LedgerField
                                id="account-name"
                                name="name"
                                label="Name"
                                defaultValue={account?.name}
                                error={errors.name}
                                required
                                maxLength={255}
                                autoFocus
                            />
                            <div className="grid gap-2">
                                <Label htmlFor="account-type">Type</Label>
                                <select
                                    id="account-type"
                                    name="type"
                                    defaultValue={account?.type ?? 'checking'}
                                    className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-invalid={Boolean(errors.type)}
                                    aria-describedby="account-type-error"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="checking">Checking</option>
                                    <option value="savings">Savings</option>
                                    <option value="credit">Credit</option>
                                    <option value="other">Other</option>
                                </select>
                                <InputError id="account-type-error" message={errors.type} />
                            </div>
                            <LedgerField
                                id="account-balance"
                                name="opening_balance"
                                label={`Opening balance (${book.currency_code})`}
                                inputMode="decimal"
                                defaultValue={minorToDecimal(account?.opening_balance_minor ?? 0, precision)}
                                error={
                                    errors.opening_balance_minor
                                        ? `Enter a valid amount with at most ${precision} decimal places, within the supported money limit.`
                                        : undefined
                                }
                                required
                            />
                            <LedgerField
                                id="account-date"
                                name="opened_on"
                                label="Opening date"
                                type="date"
                                defaultValue={account?.opened_on.slice(0, 10) ?? today}
                                error={errors.opened_on}
                                required
                            />
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                                    Cancel
                                </Button>
                                <Button disabled={processing}>{account ? 'Save changes' : 'Add account'}</Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

export default function AccountsIndex({
    book,
    accounts,
    status,
}: {
    book: Book;
    accounts: Account[];
    status?: string;
}) {
    const precision = currencyPrecision(book.currency_code);
    return (
        <>
            <Head title={`${book.name} accounts`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <nav aria-label="Book navigation">
                    <Link href={booksIndex()} className="text-sm underline underline-offset-4">
                        All books / Switch book
                    </Link>
                </nav>
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading title={`${book.name} accounts`} description={`${book.currency_code} · ${book.timezone}`} />
                    <AccountEditor key={book.id} book={book} />
                </div>
                {status && (
                    <p role="status" className="rounded-lg border p-3 text-sm">
                        {status}
                    </p>
                )}
                {accounts.length === 0 ? (
                    <div className="rounded-xl border border-dashed p-8 text-center">
                        <h2 className="font-medium">No accounts yet</h2>
                        <p className="text-muted-foreground mt-2 text-sm">
                            Add a bank account, credit card, or cash wallet to this book.
                        </p>
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-xl border">
                        <table className="w-full text-left text-sm">
                            <caption className="sr-only">Accounts in {book.name}</caption>
                            <thead className="bg-muted/50 text-muted-foreground">
                                <tr>
                                    {[
                                        'Name',
                                        'Type',
                                        'Opening date',
                                        `Opening balance (${book.currency_code})`,
                                        'Actions',
                                    ].map((label) => (
                                        <th key={label} scope="col" className="px-4 py-3 font-medium">
                                            {label}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {accounts.map((account) => (
                                    <tr key={account.id}>
                                        <th scope="row" className="px-4 py-3 font-medium">
                                            {account.name}
                                        </th>
                                        <td className="px-4 py-3 capitalize">{account.type}</td>
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            {account.opened_on.slice(0, 10)}
                                        </td>
                                        <td className="px-4 py-3 text-right tabular-nums">
                                            {minorToDecimal(account.opening_balance_minor, precision)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex gap-2">
                                                <AccountEditor
                                                    key={`${account.id}-${account.opened_on}-${account.opening_balance_minor}-${account.name}-${account.type}`}
                                                    book={book}
                                                    account={account}
                                                />
                                                <DeleteLedgerItem
                                                    name={account.name}
                                                    action={destroy.url({ book: book.id, account: account.id })}
                                                    description="This permanently deletes the account. Accounts with transactions must be kept to preserve the ledger."
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

AccountsIndex.layout = { breadcrumbs: [{ title: 'Books', href: booksIndex() }] };

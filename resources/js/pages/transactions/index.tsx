import { Head, Link } from '@inertiajs/react';
import { Search, ArrowLeftRight } from 'lucide-react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import DeleteLedgerItem from '@/components/delete-ledger-item';
import Heading from '@/components/heading';
import TransactionEditor from '@/components/transaction-editor';
import { Button } from '@/components/ui/button';
import { currencyPrecision, minorToDecimal } from '@/lib/money';
import { index as accountsIndex } from '@/routes/books/accounts';
import { destroy } from '@/routes/books/transactions';
import { destroy as destroyTransfer } from '@/routes/books/transfers';
import type { Account, Book, Category, Transaction } from '@/types/ledger';

export default function TransactionsIndex({
    book,
    transactions,
    accounts,
    categories,
    status,
}: {
    book: Book;
    transactions: Transaction[];
    accounts: Account[];
    categories: Category[];
    status?: string;
}) {
    const [search, setSearch] = useState('');
    const visibleTransactions = transactions.filter((transaction) =>
        [
            transaction.payee,
            transaction.account?.name,
            transaction.category?.name,
            transaction.transfer_group_id ? 'transfer' : '',
            transaction.memo,
            transaction.reference,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase()
            .includes(search.trim().toLowerCase()),
    );
    const destinations = new Map(
        transactions
            .filter((entry) => entry.transfer_group_id && entry.amount_minor > 0)
            .map((entry) => [entry.transfer_group_id, entry]),
    );
    return (
        <>
            <Head title={`${book.name} transactions`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Transactions"
                        description={`Ledger entries for ${book.name} · ${book.currency_code}.`}
                    />
                    <div className="flex flex-wrap gap-2">
                        {accounts.length > 0 && (
                            <TransactionEditor
                                key={`transaction-${book.id}`}
                                book={book}
                                accounts={accounts}
                                categories={categories}
                            />
                        )}
                        {accounts.filter((account) => !account.archived_at).length > 1 && (
                            <TransactionEditor
                                key={`transfer-${book.id}`}
                                book={book}
                                accounts={accounts}
                                categories={categories}
                                transfer
                            />
                        )}
                    </div>
                </div>
                {status && (
                    <p role="status" className="rounded-lg border p-3 text-sm">
                        {status}
                    </p>
                )}
                {accounts.length === 0 ? (
                    <div className="flex flex-col items-center gap-4 rounded-xl border border-dashed p-8 text-center">
                        <h2 className="font-medium">Create an account first</h2>
                        <Button asChild>
                            <Link href={accountsIndex(book.id)}>Manage accounts</Link>
                        </Button>
                    </div>
                ) : (
                    transactions.length === 0 && (
                        <div className="rounded-xl border border-dashed p-8 text-center">
                            No transactions yet. Add a transaction to start your ledger.
                        </div>
                    )
                )}
                {accounts.length > 0 && accounts.filter((account) => !account.archived_at).length < 2 && (
                    <p className="text-muted-foreground text-sm">
                        Transfers need two active accounts.{' '}
                        <Link href={accountsIndex(book.id)} className="underline">
                            Manage accounts
                        </Link>
                    </p>
                )}
                <div className="bg-card overflow-hidden rounded-2xl border">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
                        <div className="flex items-center gap-2 text-sm font-medium">
                            <ArrowLeftRight className="text-primary size-4" /> All entries{' '}
                            <span className="bg-muted text-muted-foreground rounded-md px-2 py-0.5 text-xs">
                                {visibleTransactions.length}
                            </span>
                        </div>
                        <div className="relative w-full sm:w-64">
                            <Search className="text-muted-foreground pointer-events-none absolute top-2.5 left-3 size-4" />
                            <Input
                                type="search"
                                aria-label="Search transactions"
                                placeholder="Find a transaction…"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                className="bg-muted/60 h-9 border-transparent pl-9 shadow-none"
                            />
                        </div>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[42rem] text-left text-sm">
                            <thead className="bg-muted/50 text-muted-foreground">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Date</th>
                                    <th className="px-4 py-3 font-medium">Payee</th>
                                    <th className="px-4 py-3 font-medium">Account</th>
                                    <th className="px-4 py-3 font-medium">Category</th>
                                    <th className="px-4 py-3 text-right font-medium">Amount</th>
                                    <th className="px-4 py-3 font-medium">Status</th>
                                    <th className="px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {visibleTransactions.map((transaction) => (
                                    <tr key={transaction.id}>
                                        <td className="px-4 py-3">{transaction.occurred_on.slice(0, 10)}</td>
                                        <td className="px-4 py-3">{transaction.payee ?? '—'}</td>
                                        <td className="px-4 py-3">{transaction.account?.name ?? '—'}</td>
                                        <td className="px-4 py-3">
                                            {transaction.transfer_group_id
                                                ? 'Transfer'
                                                : (transaction.category?.name ?? 'Uncategorized')}
                                        </td>
                                        <td
                                            className={`px-4 py-3 text-right font-medium tabular-nums ${transaction.amount_minor > 0 ? 'text-primary' : ''}`}
                                        >
                                            {minorToDecimal(
                                                transaction.amount_minor,
                                                currencyPrecision(book.currency_code),
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span
                                                className={`inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-xs capitalize ${transaction.status === 'cleared' ? 'bg-secondary text-secondary-foreground' : 'bg-muted text-muted-foreground'}`}
                                            >
                                                <span className="size-1.5 rounded-full bg-current" />
                                                {transaction.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            {!transaction.transfer_group_id ? (
                                                <div className="flex gap-2">
                                                    <TransactionEditor
                                                        book={book}
                                                        accounts={accounts}
                                                        categories={categories}
                                                        transaction={transaction}
                                                    />
                                                    <DeleteLedgerItem
                                                        name={`transaction on ${transaction.occurred_on.slice(0, 10)}`}
                                                        action={destroy.url({
                                                            book: book.id,
                                                            transaction: transaction.id,
                                                        })}
                                                        description="This permanently removes the transaction and updates derived balances and actuals."
                                                    />
                                                </div>
                                            ) : transaction.amount_minor < 0 ? (
                                                <div className="flex gap-2">
                                                    <TransactionEditor
                                                        book={book}
                                                        accounts={accounts}
                                                        categories={categories}
                                                        transaction={transaction}
                                                        transfer
                                                        destinationAccountId={
                                                            destinations.get(transaction.transfer_group_id)?.account_id
                                                        }
                                                    />
                                                    <DeleteLedgerItem
                                                        name="transfer"
                                                        action={destroyTransfer.url({
                                                            book: book.id,
                                                            transfer: transaction.transfer_group_id,
                                                        })}
                                                        description="This permanently removes both sides of the transfer and updates both account balances."
                                                    />
                                                </div>
                                            ) : (
                                                <span className="text-muted-foreground">
                                                    Manage from the outgoing entry
                                                </span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                                {search.trim() && visibleTransactions.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="text-muted-foreground p-10 text-center"
                                            role="status"
                                        >
                                            No entries match “{search}”. Try another payee, account, or category.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </>
    );
}

import { Head, Link } from '@inertiajs/react';
import BudgetPeriodEditor from '@/components/budget-period-editor';
import DeleteLedgerItem from '@/components/delete-ledger-item';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { index as booksIndex } from '@/routes/books';
import { destroy } from '@/routes/books/budget-periods';
import { index as allocationsIndex } from '@/routes/books/budget-periods/allocations';
import type { Book, BudgetPeriod } from '@/types/ledger';

export default function BudgetPeriodsIndex({
    book,
    periods,
    status,
}: {
    book: Book;
    periods: BudgetPeriod[];
    status?: string;
}) {
    return (
        <>
            <Head title={`${book.name} budget periods`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <nav aria-label="Book navigation">
                    <Link href={booksIndex()} className="text-sm underline underline-offset-4">
                        All books / Switch book
                    </Link>
                </nav>
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title={`${book.name} budget periods`}
                        description="Choose a period to view its budget allocations."
                    />
                    <BudgetPeriodEditor key={book.id} book={book} />
                </div>
                {status && (
                    <p role="status" className="rounded-lg border p-3 text-sm">
                        {status}
                    </p>
                )}
                {periods.length === 0 && (
                    <div className="rounded-xl border border-dashed p-8 text-center">
                        <h2 className="font-medium">No budget periods yet</h2>
                        <p className="text-muted-foreground mt-2 text-sm">
                            Create a weekly, monthly, or custom period to start planning.
                        </p>
                    </div>
                )}
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {periods.map((period) => (
                        <article key={period.id} className="flex flex-col gap-4 rounded-xl border p-4">
                            <div className="flex items-start justify-between gap-3">
                                <h2 className="font-medium">
                                    <Link
                                        href={allocationsIndex({ book: book.id, budget_period: period.id })}
                                        className="hover:underline"
                                    >
                                        {period.name}
                                    </Link>
                                </h2>
                                <span className="text-muted-foreground text-xs capitalize">{period.status}</span>
                            </div>
                            <p className="text-muted-foreground mt-2 text-sm">
                                {period.starts_on.slice(0, 10)} – {period.ends_on.slice(0, 10)}
                            </p>
                            <div className="flex flex-wrap items-center gap-2">
                                <Button asChild>
                                    <Link href={allocationsIndex({ book: book.id, budget_period: period.id })}>
                                        Open period
                                    </Link>
                                </Button>
                                <BudgetPeriodEditor book={book} period={period} />
                                <DeleteLedgerItem
                                    name={period.name}
                                    action={destroy.url({ book: book.id, budget_period: period.id })}
                                    description="This permanently deletes this period and its budget allocations. Account transactions are kept."
                                />
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

BudgetPeriodsIndex.layout = { breadcrumbs: [{ title: 'Books', href: booksIndex() }] };

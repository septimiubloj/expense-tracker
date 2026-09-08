import { Head, Link } from '@inertiajs/react';
import BudgetAllocationEditor from '@/components/budget-allocation-editor';
import DeleteLedgerItem from '@/components/delete-ledger-item';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { currencyPrecision, minorToDecimal } from '@/lib/money';
import { index as periodsIndex } from '@/routes/books/budget-periods';
import { destroy } from '@/routes/books/budget-periods/allocations';
import { index as categoriesIndex } from '@/routes/books/categories';
import type { Book, BudgetAllocation, BudgetPeriod, Category } from '@/types/ledger';

export default function BudgetAllocationsIndex({
    book,
    period,
    allocations,
    categories,
    status,
}: {
    book: Book;
    period: BudgetPeriod;
    allocations: BudgetAllocation[];
    categories: Category[];
    status?: string;
}) {
    const allocatedCategoryIds = new Set(allocations.map((allocation) => allocation.category_id));
    const availableCategories = categories.filter((category) => !allocatedCategoryIds.has(category.id));

    return (
        <>
            <Head title={`${period.name} allocations`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <nav aria-label="Period navigation">
                    <Link href={periodsIndex(book.id)} className="text-sm underline underline-offset-4">
                        All periods / Switch period
                    </Link>
                </nav>
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title={`${period.name} allocations`}
                        description={`${book.name} · ${period.starts_on.slice(0, 10)} – ${period.ends_on.slice(0, 10)} · ${period.status}`}
                    />
                    {availableCategories.length > 0 && (
                        <BudgetAllocationEditor
                            key={period.id}
                            book={book}
                            period={period}
                            categories={availableCategories}
                        />
                    )}
                </div>
                {status && (
                    <p role="status" className="rounded-lg border p-3 text-sm">
                        {status}
                    </p>
                )}
                {categories.length === 0 ? (
                    <div className="flex flex-col items-center gap-4 rounded-xl border border-dashed p-8 text-center">
                        <h2 className="font-medium">Create categories first</h2>
                        <p className="text-muted-foreground text-sm">
                            Add income and expense categories to this book before planning amounts.
                        </p>
                        <Button asChild>
                            <Link href={categoriesIndex(book.id)}>Manage categories</Link>
                        </Button>
                    </div>
                ) : allocations.length === 0 ? (
                    <div className="rounded-xl border border-dashed p-8 text-center">
                        <h2 className="font-medium">No allocations yet</h2>
                        <p className="text-muted-foreground mt-2 text-sm">
                            Add an allocation to plan income or expenses for this period.
                        </p>
                    </div>
                ) : (
                    availableCategories.length === 0 && (
                        <p className="text-muted-foreground text-sm">
                            Every category has an allocation. Edit an amount below or{' '}
                            <Link href={categoriesIndex(book.id)} className="underline underline-offset-4">
                                manage categories
                            </Link>
                            .
                        </p>
                    )
                )}
                <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    {allocations.map((allocation) => (
                        <article key={allocation.id} className="flex flex-col gap-3 rounded-xl border p-4">
                            <h2 className="font-medium">{allocation.category.name}</h2>
                            <p className="text-muted-foreground text-sm capitalize">{allocation.category.type}</p>
                            <p className="text-muted-foreground mt-1 text-sm tabular-nums">
                                {minorToDecimal(allocation.planned_amount_minor, currencyPrecision(book.currency_code))}{' '}
                                {book.currency_code}
                            </p>
                            <div className="flex flex-wrap items-center gap-2">
                                <BudgetAllocationEditor
                                    book={book}
                                    period={period}
                                    categories={categories.filter(
                                        (category) =>
                                            category.id === allocation.category_id ||
                                            !allocatedCategoryIds.has(category.id),
                                    )}
                                    allocation={allocation}
                                />
                                <DeleteLedgerItem
                                    name={`${allocation.category.name} allocation`}
                                    action={destroy.url({
                                        book: book.id,
                                        budget_period: period.id,
                                        allocation: allocation.id,
                                    })}
                                    description="This removes the planned amount from this period. The category and its transactions are kept."
                                />
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

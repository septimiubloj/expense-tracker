import { Head, Link } from '@inertiajs/react';
import CategoryEditor from '@/components/category-editor';
import DeleteLedgerItem from '@/components/delete-ledger-item';
import Heading from '@/components/heading';
import { index as booksIndex } from '@/routes/books';
import { destroy } from '@/routes/books/categories';
import type { Book, Category } from '@/types/ledger';

export default function CategoriesIndex({
    book,
    categories,
    status,
}: {
    book: Book;
    categories: Category[];
    status?: string;
}) {
    return (
        <>
            <Head title={`${book.name} categories`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <nav aria-label="Book navigation">
                    <Link href={booksIndex()} className="text-sm underline underline-offset-4">
                        All books / Switch book
                    </Link>
                </nav>
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title={`${book.name} categories`}
                        description="Manage income, expenses, and their parent categories."
                    />
                    <CategoryEditor key={book.id} book={book} categories={categories} />
                </div>
                {status && (
                    <p role="status" className="rounded-lg border p-3 text-sm">
                        {status}
                    </p>
                )}
                {categories.length === 0 && (
                    <div className="rounded-xl border border-dashed p-8 text-center">
                        <h2 className="font-medium">No categories yet</h2>
                        <p className="text-muted-foreground mt-2 text-sm">
                            Add categories such as Salary, Groceries, or Housing to organize your budget.
                        </p>
                    </div>
                )}
                <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    {categories.map((category) => (
                        <article key={category.id} className="flex flex-col gap-3 rounded-xl border p-4">
                            <h2 className="font-medium">{category.name}</h2>
                            <p className="text-muted-foreground mt-1 text-sm capitalize">{category.type}</p>
                            <p className="text-muted-foreground text-sm">
                                Parent: {category.parent?.name ?? 'None (top level)'}
                            </p>
                            <p className="text-muted-foreground text-sm">Sort order: {category.sort_order}</p>
                            <div className="flex flex-wrap items-center gap-2">
                                <CategoryEditor book={book} categories={categories} category={category} />
                                <DeleteLedgerItem
                                    name={category.name}
                                    action={destroy.url({ book: book.id, category: category.id })}
                                    description="This permanently deletes the category. Its transactions become uncategorized and its child categories become top level. Remove any budget allocations first."
                                />
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

CategoriesIndex.layout = { breadcrumbs: [{ title: 'Books', href: booksIndex() }] };

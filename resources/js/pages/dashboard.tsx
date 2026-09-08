import { Head, Link, usePage } from "@inertiajs/react";
import { ArrowUpRight, BookOpen, Plus, Sprout } from "lucide-react";
import type { CSSProperties } from "react";
import { index as transactionsIndex } from "@/routes/books/transactions";
import { index as accountsIndex } from "@/routes/books/accounts";
import { index as periodsIndex } from "@/routes/books/budget-periods";
import { index as categoriesIndex } from "@/routes/books/categories";
import type { Book } from "@/types/ledger";
import { Button } from "@/components/ui/button";
import { index as booksIndex } from "@/routes/books";
import { dashboard } from "@/routes";

export default function Dashboard({ books }: { books: Book[] }) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Workspace" />
            <div className="workspace-home">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <span className="eyebrow">YOUR PERSONAL MONEY SPACE</span>
                    <Button asChild variant="outline">
                        <Link href={booksIndex()}>
                            <Plus className="size-4" /> Manage books
                        </Link>
                    </Button>
                </div>
                <section className="workspace-welcome">
                    <div className="relative z-10 max-w-xl xl:max-w-[55%]">
                        <p className="text-muted-foreground mb-4 text-sm">
                            Welcome back, {auth.user.name.split(" ")[0]}.
                        </p>
                        <h1 className="text-4xl leading-[1.1] tracking-tight sm:text-5xl">
                            A little clarity.
                            <br />
                            <span className="text-primary font-serif italic">A lot more calm.</span>
                        </h1>
                        <p className="text-muted-foreground mt-5 max-w-sm text-sm leading-7">
                            A home for the everyday ins and outs. Open a book, settle the details,
                            and get on with your day.
                        </p>
                        <Button asChild className="mt-7">
                            <Link
                                href={
                                    books.length === 1
                                        ? transactionsIndex(books[0].id)
                                        : booksIndex()
                                }
                            >
                                {books.length === 1 ? "Open transactions" : "Open your books"}
                                <ArrowUpRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                    <div className="paper-still-life" aria-hidden="true">
                        <div className="paper-note paper-note-back">
                            <span>ROOM TO BREATHE</span>
                            <Sprout className="size-16" strokeWidth={1} />
                        </div>
                        <div className="paper-note paper-note-front">
                            <span>ONE THING AT A TIME</span>
                            <div className="note-line" />
                            <div className="note-line" />
                            <div className="note-line w-2/3" />
                            <span className="note-check">✓</span>
                        </div>
                    </div>
                </section>
                <section aria-labelledby="books-title" className="space-y-5">
                    <div className="flex items-end justify-between gap-4">
                        <div>
                            <p className="eyebrow mb-2">PICK UP WHERE YOU LEFT OFF</p>
                            <h2 id="books-title" className="text-xl font-semibold tracking-tight">
                                Your books{" "}
                                <span className="text-muted-foreground ml-2 text-sm font-normal">
                                    {books.length}
                                </span>
                            </h2>
                        </div>
                        <Link
                            href={booksIndex()}
                            className="text-primary text-sm underline-offset-4 hover:underline"
                        >
                            Manage books →
                        </Link>
                    </div>
                    {books.length === 0 ? (
                        <div className="bg-card rounded-2xl border border-dashed p-8 sm:p-12">
                            <BookOpen className="text-primary mb-5 size-8" strokeWidth={1.5} />
                            <h3 className="text-lg font-medium">A fresh page for your finances.</h3>
                            <p className="text-muted-foreground mt-2 mb-6 text-sm">
                                Create your first book, then add an account to start recording
                                transactions.
                            </p>
                            <Button asChild>
                                <Link href={booksIndex()}>
                                    Create your first book
                                    <Plus className="size-4" />
                                </Link>
                            </Button>
                        </div>
                    ) : (
                        <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                            {books.map((book, index) => (
                                <article
                                    key={book.id}
                                    className="book-card"
                                    style={
                                        {
                                            "--book-accent": [
                                                "var(--sage)",
                                                "var(--lilac)",
                                                "var(--apricot)",
                                            ][index % 3],
                                        } as CSSProperties
                                    }
                                >
                                    <Link
                                        href={transactionsIndex(book.id)}
                                        prefetch
                                        className="book-cover group"
                                    >
                                        <div className="flex items-center justify-between">
                                            <BookOpen className="size-6" strokeWidth={1.5} />
                                            <span className="rounded-full border border-current/20 px-2.5 py-1 text-[10px] font-semibold tracking-widest">
                                                {book.currency_code}
                                            </span>
                                        </div>
                                        <h3 className="mt-8 truncate text-xl font-semibold tracking-tight">
                                            {book.name}
                                        </h3>
                                        <div className="mt-2 flex items-center justify-between text-xs">
                                            <span>Open transactions</span>
                                            <ArrowUpRight className="size-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                        </div>
                                    </Link>
                                    <div className="flex flex-wrap gap-1 p-3">
                                        <Button asChild variant="ghost" size="sm">
                                            <Link href={accountsIndex(book.id)}>Accounts</Link>
                                        </Button>
                                        <Button asChild variant="ghost" size="sm">
                                            <Link href={periodsIndex(book.id)}>Budget periods</Link>
                                        </Button>
                                        <Button asChild variant="ghost" size="sm">
                                            <Link href={categoriesIndex(book.id)}>Categories</Link>
                                        </Button>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>
                <p className="text-muted-foreground flex items-center gap-2 text-xs">
                    <Sprout className="size-4" /> Small habits. Clearer days.
                </p>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: "Workspace",
            href: dashboard(),
        },
    ],
};

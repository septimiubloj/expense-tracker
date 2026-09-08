import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { index as booksIndex } from '@/routes/books';
import { dashboard } from '@/routes';

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Heading
                    title="Your expense tracker"
                    description="Choose a book to manage your accounts and opening balances."
                />
                <div className="flex flex-col items-start gap-4 rounded-xl border p-6">
                    <h2 className="text-lg font-medium">Start with your books</h2>
                    <p className="text-muted-foreground text-sm">
                        Keep household and project finances in separate ledgers, each with its own currency and
                        timezone.
                    </p>
                    <Button asChild>
                        <Link href={booksIndex()}>Open books</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};

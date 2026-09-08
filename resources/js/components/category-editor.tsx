import { Form } from '@inertiajs/react';
import { useState } from 'react';
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
import { store, update } from '@/routes/books/categories';
import type { Book, Category } from '@/types/ledger';

export default function CategoryEditor({
    book,
    categories,
    category,
}: {
    book: Book;
    categories: Category[];
    category?: Category;
}) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant={category ? 'outline' : 'default'}>
                    {category ? 'Edit' : 'Add category'}
                    {category && <span className="sr-only"> {category.name}</span>}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{category ? 'Edit category' : 'Add category'}</DialogTitle>
                    <DialogDescription>
                        Organize income and expenses in {book.name}. Parent categories group related categories; totals
                        use direct assignments.
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...(category ? update.form({ book: book.id, category: category.id }) : store.form(book.id))}
                    onSuccess={() => setOpen(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <LedgerField
                                id="category-name"
                                name="name"
                                label="Name"
                                defaultValue={category?.name}
                                error={errors.name}
                                required
                                maxLength={255}
                                autoFocus
                            />
                            <div className="grid gap-2">
                                <Label htmlFor="category-type">Type</Label>
                                <select
                                    id="category-type"
                                    name="type"
                                    defaultValue={category?.type ?? 'expense'}
                                    className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-invalid={Boolean(errors.type)}
                                    aria-describedby="category-type-error"
                                >
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                                <InputError id="category-type-error" message={errors.type} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="category-parent">Parent category</Label>
                                <select
                                    id="category-parent"
                                    name="parent_id"
                                    defaultValue={category?.parent_id ?? ''}
                                    className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-invalid={Boolean(errors.parent_id)}
                                    aria-describedby="category-parent-error"
                                >
                                    <option value="">None (top level)</option>
                                    {categories
                                        .filter((parent) => parent.id !== category?.id)
                                        .map((parent) => (
                                            <option key={parent.id} value={parent.id}>
                                                {parent.name} ({parent.type})
                                                {parent.parent ? ` · ${parent.parent.name}` : ''}
                                            </option>
                                        ))}
                                </select>
                                <InputError id="category-parent-error" message={errors.parent_id} />
                            </div>
                            <LedgerField
                                id="category-order"
                                name="sort_order"
                                label="Sort order"
                                type="number"
                                min={0}
                                step={1}
                                defaultValue={category?.sort_order ?? 0}
                                error={errors.sort_order}
                                required
                            />
                            <p className="text-muted-foreground text-sm">
                                Lower numbers appear first; names break ties.
                            </p>
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                                    Cancel
                                </Button>
                                <Button disabled={processing}>{category ? 'Save changes' : 'Add category'}</Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

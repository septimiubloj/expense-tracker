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
import { currencyPrecision, decimalToMinor, minorToDecimal } from '@/lib/money';
import { store, update } from '@/routes/books/budget-periods/allocations';
import type { Book, BudgetAllocation, BudgetPeriod, Category } from '@/types/ledger';

export default function BudgetAllocationEditor({
    book,
    period,
    categories,
    allocation,
}: {
    book: Book;
    period: BudgetPeriod;
    categories: Category[];
    allocation?: BudgetAllocation;
}) {
    const [open, setOpen] = useState(false);
    const precision = currencyPrecision(book.currency_code);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant={allocation ? 'outline' : 'default'}>
                    {allocation ? 'Edit' : 'Add allocation'}
                    {allocation && <span className="sr-only"> for {allocation.category.name}</span>}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{allocation ? 'Edit allocation' : 'Add allocation'}</DialogTitle>
                    <DialogDescription>
                        Plan income or expenses for {period.name} in {book.currency_code}. Enter zero or a positive
                        amount.
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...(allocation
                        ? update.form({ book: book.id, budget_period: period.id, allocation: allocation.id })
                        : store.form({ book: book.id, budget_period: period.id }))}
                    transform={(data) => ({
                        ...data,
                        planned_amount_minor: decimalToMinor(String(data.planned_amount), precision) ?? 'invalid',
                    })}
                    onSuccess={() => setOpen(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="allocation-category">Category</Label>
                                <select
                                    id="allocation-category"
                                    name="category_id"
                                    defaultValue={allocation?.category_id ?? ''}
                                    required
                                    autoFocus
                                    className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-invalid={Boolean(errors.category_id)}
                                    aria-describedby="allocation-category-error"
                                >
                                    <option value="" disabled>
                                        Choose a category
                                    </option>
                                    {categories.map((category) => (
                                        <option key={category.id} value={category.id}>
                                            {category.name} ({category.type})
                                            {category.parent ? ` · ${category.parent.name}` : ''}
                                        </option>
                                    ))}
                                </select>
                                <InputError id="allocation-category-error" message={errors.category_id} />
                            </div>
                            <LedgerField
                                id="allocation-amount"
                                name="planned_amount"
                                label={`Planned amount (${book.currency_code})`}
                                inputMode="decimal"
                                defaultValue={minorToDecimal(allocation?.planned_amount_minor ?? 0, precision)}
                                required
                                error={
                                    errors.planned_amount_minor
                                        ? `Enter a nonnegative amount with at most ${precision} decimal places, within the supported money limit.`
                                        : undefined
                                }
                            />
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                                    Cancel
                                </Button>
                                <Button disabled={processing}>{allocation ? 'Save changes' : 'Add allocation'}</Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

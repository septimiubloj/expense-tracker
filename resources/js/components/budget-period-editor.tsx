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
import { store, update } from '@/routes/books/budget-periods';
import type { Book, BudgetPeriod } from '@/types/ledger';

export default function BudgetPeriodEditor({ book, period }: { book: Book; period?: BudgetPeriod }) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant={period ? 'outline' : 'default'}>
                    {period ? 'Edit' : 'Create period'}
                    {period && <span className="sr-only"> {period.name}</span>}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{period ? 'Edit budget period' : 'Create budget period'}</DialogTitle>
                    <DialogDescription>
                        Choose a planning window for {book.name}. Both dates are included, using {book.timezone}.
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...(period ? update.form({ book: book.id, budget_period: period.id }) : store.form(book.id))}
                    onSuccess={() => setOpen(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <LedgerField
                                id="period-name"
                                name="name"
                                label="Name"
                                defaultValue={period?.name}
                                error={errors.name}
                                required
                                maxLength={255}
                                autoFocus
                                placeholder="September 2026"
                            />
                            <div className="grid gap-4 sm:grid-cols-2">
                                <LedgerField
                                    id="period-start"
                                    name="starts_on"
                                    label="Start date"
                                    type="date"
                                    defaultValue={period?.starts_on.slice(0, 10)}
                                    error={errors.starts_on}
                                    required
                                />
                                <LedgerField
                                    id="period-end"
                                    name="ends_on"
                                    label="End date"
                                    type="date"
                                    defaultValue={period?.ends_on.slice(0, 10)}
                                    error={errors.ends_on}
                                    required
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="period-status">Status</Label>
                                <select
                                    id="period-status"
                                    name="status"
                                    defaultValue={period?.status ?? 'open'}
                                    className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-invalid={Boolean(errors.status)}
                                    aria-describedby="period-status-error period-status-help"
                                >
                                    <option value="open">Open</option>
                                    <option value="closed">Closed</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <p id="period-status-help" className="text-muted-foreground text-sm">
                                    Status helps organize periods; it does not lock editing.
                                </p>
                                <InputError id="period-status-error" message={errors.status} />
                            </div>
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                                    Cancel
                                </Button>
                                <Button disabled={processing}>{period ? 'Save changes' : 'Create period'}</Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

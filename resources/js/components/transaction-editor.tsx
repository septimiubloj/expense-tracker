import { Form } from '@inertiajs/react';
import { useId, useState } from 'react';
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
import { store, update } from '@/routes/books/transactions';
import { store as storeTransfer, update as updateTransfer } from '@/routes/books/transfers';
import type { Account, Book, Category, Transaction } from '@/types/ledger';

export default function TransactionEditor({
    book,
    accounts,
    categories,
    transaction,
    transfer = false,
    destinationAccountId,
}: {
    book: Book;
    accounts: Account[];
    categories: Category[];
    transaction?: Transaction;
    transfer?: boolean;
    destinationAccountId?: number;
}) {
    const [open, setOpen] = useState(false);
    const id = useId();
    const precision = currencyPrecision(book.currency_code);
    const kind = transfer ? 'transfer' : 'transaction';
    const action = transfer
        ? transaction?.transfer_group_id
            ? updateTransfer.form({ book: book.id, transfer: transaction.transfer_group_id })
            : storeTransfer.form(book.id)
        : transaction
          ? update.form({ book: book.id, transaction: transaction.id })
          : store.form(book.id);
    const selectableAccounts = transfer ? accounts.filter((account) => !account.archived_at) : accounts;

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant={transaction || transfer ? 'outline' : 'default'}>
                    {transaction ? `Edit ${kind}` : `Add ${kind}`}
                    {transaction && (
                        <span className="sr-only">
                            {' '}
                            on {transaction.occurred_on.slice(0, 10)} in {transaction.account.name}
                        </span>
                    )}
                </Button>
            </DialogTrigger>
            <DialogContent className="max-h-[90dvh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{transaction ? `Edit ${kind}` : `Add ${kind}`}</DialogTitle>
                    <DialogDescription>
                        {transfer
                            ? 'Move a positive amount between two accounts. Both entries are saved together.'
                            : 'Use negative amounts for money out and positive amounts for money in, including refunds.'}
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...action}
                    transform={(data) => ({
                        ...data,
                        amount_minor: decimalToMinor(String(data.amount), precision) ?? 'invalid',
                    })}
                    onSuccess={() => setOpen(false)}
                    className="grid gap-4"
                >
                    {({ errors, processing }) => (
                        <>
                            {(transfer ? ['source_account_id', 'destination_account_id'] : ['account_id']).map(
                                (field) => (
                                    <div key={field} className="grid gap-2">
                                        <Label htmlFor={`${id}-${field}`}>
                                            {field === 'destination_account_id'
                                                ? 'To account'
                                                : transfer
                                                  ? 'From account'
                                                  : 'Account'}
                                        </Label>
                                        <select
                                            id={`${id}-${field}`}
                                            name={field}
                                            required
                                            defaultValue={
                                                (field === 'destination_account_id'
                                                    ? destinationAccountId
                                                    : transaction?.account_id) ?? ''
                                            }
                                            className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                            aria-invalid={Boolean(errors[field])}
                                            aria-describedby={`${id}-${field}-error`}
                                        >
                                            <option value="" disabled>
                                                Choose an account
                                            </option>
                                            {selectableAccounts.map((account) => (
                                                <option key={account.id} value={account.id}>
                                                    {account.name}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError id={`${id}-${field}-error`} message={errors[field]} />
                                    </div>
                                ),
                            )}
                            {!transfer && (
                                <div className="grid gap-2">
                                    <Label htmlFor={`${id}-category`}>Category</Label>
                                    <select
                                        id={`${id}-category`}
                                        name="category_id"
                                        defaultValue={transaction?.category_id ?? ''}
                                        className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                        aria-invalid={Boolean(errors.category_id)}
                                        aria-describedby={`${id}-category-error`}
                                    >
                                        <option value="">Uncategorized</option>
                                        {categories.map((category) => (
                                            <option key={category.id} value={category.id}>
                                                {category.name} ({category.type})
                                                {category.parent ? ` · ${category.parent.name}` : ''}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError id={`${id}-category-error`} message={errors.category_id} />
                                </div>
                            )}
                            <LedgerField
                                id={`${id}-amount`}
                                name="amount"
                                label={`Amount (${book.currency_code})`}
                                inputMode="decimal"
                                required
                                defaultValue={
                                    transaction
                                        ? minorToDecimal(
                                              transfer ? Math.abs(transaction.amount_minor) : transaction.amount_minor,
                                              precision,
                                          )
                                        : ''
                                }
                                error={
                                    errors.amount_minor
                                        ? `Enter a ${transfer ? 'positive' : 'nonzero'} amount with at most ${precision} decimal places, within the supported money limit.`
                                        : undefined
                                }
                            />
                            <LedgerField
                                id={`${id}-date`}
                                name="occurred_on"
                                label="Date"
                                type="date"
                                required
                                defaultValue={transaction?.occurred_on.slice(0, 10)}
                                error={errors.occurred_on}
                            />
                            <div className="grid gap-2">
                                <Label htmlFor={`${id}-status`}>Status</Label>
                                <select
                                    id={`${id}-status`}
                                    name="status"
                                    defaultValue={transaction?.status ?? 'cleared'}
                                    className="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                    aria-invalid={Boolean(errors.status)}
                                    aria-describedby={`${id}-status-error`}
                                >
                                    <option value="pending">Pending</option>
                                    <option value="cleared">Cleared</option>
                                    <option value="void">Void</option>
                                </select>
                                <InputError id={`${id}-status-error`} message={errors.status} />
                            </div>
                            {!transfer && (
                                <>
                                    <LedgerField
                                        id={`${id}-payee`}
                                        name="payee"
                                        label="Payee"
                                        maxLength={255}
                                        defaultValue={transaction?.payee ?? ''}
                                        error={errors.payee}
                                    />
                                    <LedgerField
                                        id={`${id}-reference`}
                                        name="reference"
                                        label="Reference"
                                        maxLength={255}
                                        defaultValue={transaction?.reference ?? ''}
                                        error={errors.reference}
                                    />
                                </>
                            )}
                            <LedgerField
                                id={`${id}-memo`}
                                name="memo"
                                label="Memo"
                                maxLength={65535}
                                defaultValue={transaction?.memo ?? ''}
                                error={errors.memo}
                            />
                            <InputError message={errors.transfer} />
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                                    Cancel
                                </Button>
                                <Button disabled={processing}>{transaction ? 'Save changes' : `Add ${kind}`}</Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

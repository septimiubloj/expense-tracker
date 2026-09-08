export type Book = {
    id: number;
    name: string;
    currency_code: string;
    timezone: string;
};

export type Account = {
    id: number;
    name: string;
    type: 'cash' | 'checking' | 'savings' | 'credit' | 'other';
    opening_balance_minor: number;
    opened_on: string;
    archived_at: string | null;
};

export type BudgetPeriod = {
    id: number;
    name: string;
    starts_on: string;
    ends_on: string;
    status: 'open' | 'closed' | 'archived';
};

export type Category = {
    id: number;
    name: string;
    type: 'income' | 'expense';
    parent_id: number | null;
    parent?: { id: number; name: string } | null;
    sort_order: number;
};

export type BudgetAllocation = {
    id: number;
    category_id: number;
    planned_amount_minor: number;
    category: Category;
};

export type Transaction = {
    id: number;
    account_id: number;
    category_id: number | null;
    occurred_on: string;
    payee: string | null;
    reference: string | null;
    memo: string | null;
    amount_minor: number;
    status: 'pending' | 'cleared' | 'void';
    transfer_group_id: string | null;
    account: Account;
    category: Category | null;
};

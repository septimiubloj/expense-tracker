import { Link, usePage } from '@inertiajs/react';
import { ArrowLeftRight, BookOpen, CalendarDays, LayoutGrid, Tags, Wallet } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as booksIndex } from '@/routes/books';
import { index as accountsIndex } from '@/routes/books/accounts';
import { index as periodsIndex } from '@/routes/books/budget-periods';
import { index as categoriesIndex } from '@/routes/books/categories';
import { index as transactionsIndex } from '@/routes/books/transactions';
import type { Book } from '@/types/ledger';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Books', href: booksIndex(), icon: BookOpen },
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

export function AppSidebar() {
    const { book } = usePage<{ book?: Book }>().props;
    const items = book
        ? [
              ...mainNavItems,
              { title: 'Accounts', href: accountsIndex(book.id), icon: Wallet },
              { title: 'Transactions', href: transactionsIndex(book.id), icon: ArrowLeftRight },
              { title: 'Categories', href: categoriesIndex(book.id), icon: Tags },
              { title: 'Budget periods', href: periodsIndex(book.id), icon: CalendarDays },
          ]
        : mainNavItems;
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

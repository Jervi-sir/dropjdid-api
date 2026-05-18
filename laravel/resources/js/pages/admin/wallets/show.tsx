import * as React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import ShowWalletController from '@/actions/App/Http/Controllers/Admin/Wallets/ShowWalletController';
import {
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    IconArrowLeft,
    IconWallet,
    IconUser,
    IconCalendar,
    IconCircleCheck,
    IconAlertTriangle,
    IconClock,
    IconBan,
    IconArrowsUpDown,
    IconCoin,
    IconReceipt,
    IconEdit,
    IconCheck,
} from '@tabler/icons-react';

interface User {
    id: number;
    full_name: string;
    username: string;
    email: string;
    image: string | null;
}

interface Wallet {
    id: number;
    user: User | null;
    type: string;
    type_raw: number;
    balance: string;
    pending_balance: string;
    is_identity_verified: boolean;
    status: string;
    status_raw: number;
    currency: string;
    created_at: string;
}

interface Transaction {
    id: number;
    direction: string;
    direction_raw: number;
    type: string;
    type_raw: number;
    status: string;
    status_raw: number;
    amount: string;
    balance_before: string;
    balance_after: string;
    title: string;
    reference: string;
    created_at: string;
}

interface Withdrawal {
    id: number;
    amount: string;
    method: string;
    method_raw: number;
    status: string;
    status_raw: number;
    transaction_id: number | null;
    payment_details: any;
    admin_note: string | null;
    identity_checked_at: string | null;
    approved_at: string | null;
    paid_at: string | null;
    created_at: string;
}

interface WalletShowProps {
    wallet: Wallet;
    transactions: Transaction[];
    withdrawals: Withdrawal[];
}

export default function WalletShow({
    wallet,
    transactions,
    withdrawals,
}: WalletShowProps) {
    const [activeTab, setActiveTab] = React.useState<
        'transactions' | 'withdrawals'
    >('transactions');

    // Wallet Form
    const walletForm = useForm({
        status: wallet.status_raw,
        is_identity_verified: wallet.is_identity_verified,
    });

    // Withdrawal Edit State
    const [selectedWithdrawal, setSelectedWithdrawal] =
        React.useState<Withdrawal | null>(null);
    const [withdrawalDialogOpen, setWithdrawalDialogOpen] =
        React.useState(false);

    // Withdrawal status modifier form
    const withdrawalForm = useForm({
        status: 0,
        admin_note: '',
    });

    const handleWalletSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        walletForm.put(ShowWalletController.update.url(wallet.id), {
            preserveScroll: true,
        });
    };

    const openWithdrawalDialog = (withdrawal: Withdrawal) => {
        setSelectedWithdrawal(withdrawal);
        withdrawalForm.setData({
            status: withdrawal.status_raw,
            admin_note: withdrawal.admin_note || '',
        });
        withdrawalForm.clearErrors();
        setWithdrawalDialogOpen(true);
    };

    const handleWithdrawalSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedWithdrawal) return;

        withdrawalForm.put(
            ShowWalletController.updateWithdrawal.url({
                wallet: wallet.id,
                withdrawalRequest: selectedWithdrawal.id,
            }),
            {
                onSuccess: () => {
                    setWithdrawalDialogOpen(false);
                },
            },
        );
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'verified':
                return (
                    <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Verified
                    </Badge>
                );
            case 'pending':
                return (
                    <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                        Pending
                    </Badge>
                );
            case 'blocked':
                return (
                    <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400">
                        Blocked
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="border border-red-500/20 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                        Rejected
                    </Badge>
                );
            default:
                return (
                    <Badge
                        variant="outline"
                        className="border-slate-300 bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        New
                    </Badge>
                );
        }
    };

    const getWithdrawalStatusBadge = (status: string) => {
        switch (status) {
            case 'paid':
                return (
                    <Badge className="border border-emerald-500/25 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                        Paid
                    </Badge>
                );
            case 'approved':
                return (
                    <Badge className="border border-cyan-500/25 bg-cyan-50 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-400">
                        Approved
                    </Badge>
                );
            case 'pending':
                return (
                    <Badge className="border border-amber-500/25 bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                        Pending
                    </Badge>
                );
            case 'pending_identity_check':
                return (
                    <Badge className="border border-sky-500/25 bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">
                        Identity Check
                    </Badge>
                );
            case 'rejected':
                return (
                    <Badge className="border border-rose-500/25 bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                        Rejected
                    </Badge>
                );
            case 'cancelled':
                return (
                    <Badge className="border border-slate-300 bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Cancelled
                    </Badge>
                );
            default:
                return (
                    <Badge className="border border-red-500/25 bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400">
                        Failed
                    </Badge>
                );
        }
    };

    const formatPayoutDetails = (details: any) => {
        if (!details) return 'None';
        if (typeof details === 'string') return details;

        // Renders key-value list elegantly
        return (
            <div className="flex flex-col gap-0.5 text-[11px] text-muted-foreground">
                {Object.entries(details).map(([key, val]) => (
                    <div key={key} className="flex gap-1">
                        <span className="font-bold capitalize">
                            {key.replace('_', ' ')}:
                        </span>
                        <span>{String(val)}</span>
                    </div>
                ))}
            </div>
        );
    };

    return (
        <>
            <Head
                title={`Wallet details - Merchant ${wallet.user?.full_name || 'N/A'}`}
            />
            <div className="flex flex-col gap-6 p-4 lg:p-8">
                {/* Back Link */}
                <div>
                    <Link
                        href="/admin/wallets"
                        className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <IconArrowLeft className="size-4" />
                        <span>Back to Wallets</span>
                    </Link>
                </div>

                {/* Header Summary */}
                <div className="flex flex-col justify-between gap-4 border-b pb-6 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                            Financial Audit Room
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Verify ledger transitions, confirm withdrawal logs,
                            and adjust account limits.
                        </p>
                    </div>
                </div>

                {/* Top Panels: Overview & Moderator settings */}
                <div className="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                    {/* Column 1: Profile & Wallet Stats Summary (lg:col-span-8) */}
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:col-span-8">
                        {/* Owner Info Profile */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconUser className="size-5 shrink-0 text-primary" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Owner Account
                                </h3>
                            </div>

                            <div className="flex items-center gap-4 py-2">
                                <div className="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-primary/10 text-xl font-black text-primary uppercase">
                                    {wallet.user?.image ? (
                                        <img
                                            src={wallet.user.image}
                                            alt={wallet.user.full_name}
                                            className="h-full w-full object-cover"
                                        />
                                    ) : (
                                        wallet.user?.full_name
                                            ?.charAt(0)
                                            .toUpperCase() || 'M'
                                    )}
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="text-sm font-black text-foreground">
                                        {wallet.user?.full_name || 'N/A'}
                                    </span>
                                    <span className="text-xs font-semibold text-muted-foreground">
                                        @{wallet.user?.username || 'unknown'}
                                    </span>
                                    <span className="mt-0.5 text-xs font-medium text-muted-foreground">
                                        {wallet.user?.email || 'N/A'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Wallet Balance widget */}
                        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs">
                            <div className="flex items-center gap-2 border-b pb-3.5">
                                <IconWallet className="size-5 shrink-0 text-primary" />
                                <h3 className="text-sm font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Ledger Balance
                                </h3>
                            </div>

                            <div className="grid grid-cols-2 gap-4 py-1">
                                <div className="flex flex-col">
                                    <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                        Available Balance
                                    </span>
                                    <span className="mt-0.5 text-lg font-black text-foreground">
                                        {wallet.balance} {wallet.currency}
                                    </span>
                                </div>
                                <div className="flex flex-col">
                                    <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                        Pending Balance
                                    </span>
                                    <span className="mt-0.5 text-lg font-black text-amber-600 dark:text-amber-400">
                                        {wallet.pending_balance}{' '}
                                        {wallet.currency}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Column 2: Moderator settings card (lg:col-span-4) */}
                    <div className="lg:col-span-4">
                        <form
                            onSubmit={handleWalletSubmit}
                            className="flex flex-col gap-4 rounded-2xl border bg-card p-5 shadow-xs"
                        >
                            <div className="flex items-center justify-between border-b pb-3">
                                <span className="text-xs font-extrabold tracking-wider text-muted-foreground uppercase">
                                    Wallet Moderator
                                </span>
                                {getStatusBadge(wallet.status)}
                            </div>

                            {/* Status */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                    Operational Status
                                </label>
                                <Select
                                    value={String(walletForm.data.status)}
                                    onValueChange={(val) =>
                                        walletForm.setData(
                                            'status',
                                            Number(val),
                                        )
                                    }
                                >
                                    <SelectTrigger className="h-9 w-full bg-background">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">
                                            New (Unverified)
                                        </SelectItem>
                                        <SelectItem value="1">
                                            Pending Approval
                                        </SelectItem>
                                        <SelectItem value="2">
                                            Verified (Active)
                                        </SelectItem>
                                        <SelectItem value="3">
                                            Blocked (Frozen Funds)
                                        </SelectItem>
                                        <SelectItem value="4">
                                            Rejected
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Identity toggle */}
                            <div className="flex items-center space-x-2 py-2">
                                <Checkbox
                                    id="identity_check"
                                    checked={
                                        walletForm.data.is_identity_verified
                                    }
                                    onCheckedChange={(checked) =>
                                        walletForm.setData(
                                            'is_identity_verified',
                                            Boolean(checked),
                                        )
                                    }
                                />
                                <label
                                    htmlFor="identity_check"
                                    className="cursor-pointer text-xs leading-none font-bold text-foreground"
                                >
                                    Verify Legal Identity Documents
                                </label>
                            </div>

                            <Button
                                type="submit"
                                disabled={walletForm.processing}
                                size="sm"
                                className="w-full font-bold"
                            >
                                {walletForm.processing
                                    ? 'Syncing...'
                                    : 'Sync Wallet Settings'}
                            </Button>

                            {walletForm.recentlySuccessful && (
                                <div className="flex items-center justify-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 py-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                    <IconCheck className="size-3.5 shrink-0" />
                                    <span>
                                        Wallet synchronized successfully
                                    </span>
                                </div>
                            )}
                        </form>
                    </div>
                </div>

                {/* Tabbed Ledgers / Withdrawals section */}
                <div className="mt-6 flex flex-col overflow-hidden rounded-2xl border bg-card shadow-xs">
                    {/* Tab Selector */}
                    <div className="flex items-center border-b bg-muted/15 px-6">
                        <button
                            onClick={() => setActiveTab('transactions')}
                            className={`border-b-2 px-4 py-4 text-xs font-extrabold tracking-wider uppercase transition-all outline-none ${
                                activeTab === 'transactions'
                                    ? 'border-primary text-foreground'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <div className="flex items-center gap-1.5">
                                <IconArrowsUpDown className="size-4" />
                                <span>
                                    Transactions Ledger ({transactions.length})
                                </span>
                            </div>
                        </button>

                        <button
                            onClick={() => setActiveTab('withdrawals')}
                            className={`border-b-2 px-4 py-4 text-xs font-extrabold tracking-wider uppercase transition-all outline-none ${
                                activeTab === 'withdrawals'
                                    ? 'border-primary text-foreground'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <div className="flex items-center gap-1.5">
                                <IconCoin className="size-4" />
                                <span>
                                    Withdrawal Requests ({withdrawals.length})
                                </span>
                            </div>
                        </button>
                    </div>

                    {/* Tab 1 Content: Transactions Table */}
                    {activeTab === 'transactions' && (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader className="border-b bg-muted/5">
                                    <TableRow>
                                        <TableHead className="py-4 pl-6">
                                            Transaction Code / Ref
                                        </TableHead>
                                        <TableHead className="py-4">
                                            Details
                                        </TableHead>
                                        <TableHead className="py-4 text-center">
                                            Direction
                                        </TableHead>
                                        <TableHead className="py-4 text-center">
                                            Type
                                        </TableHead>
                                        <TableHead className="py-4 text-right">
                                            Amount
                                        </TableHead>
                                        <TableHead className="py-4 text-right">
                                            Balance Change
                                        </TableHead>
                                        <TableHead className="py-4 text-center">
                                            Status
                                        </TableHead>
                                        <TableHead className="py-4 pr-6 text-right">
                                            Settled Date
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {transactions.length > 0 ? (
                                        transactions.map((tx) => (
                                            <TableRow
                                                key={tx.id}
                                                className="transition-colors hover:bg-muted/5"
                                            >
                                                {/* Transaction Ref */}
                                                <TableCell className="py-4 pl-6 font-mono text-[11px] font-extrabold tracking-wider text-foreground uppercase">
                                                    {tx.reference || 'N/A'}
                                                </TableCell>

                                                {/* Title */}
                                                <TableCell className="max-w-[200px] truncate py-4 text-xs font-semibold text-foreground">
                                                    {tx.title}
                                                </TableCell>

                                                {/* Direction */}
                                                <TableCell className="py-4 text-center">
                                                    {tx.direction === 'in' ? (
                                                        <span className="rounded-full border border-emerald-500/15 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-600 uppercase dark:text-emerald-400">
                                                            Credit (IN)
                                                        </span>
                                                    ) : (
                                                        <span className="rounded-full border border-rose-500/15 bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-600 uppercase dark:text-rose-400">
                                                            Debit (OUT)
                                                        </span>
                                                    )}
                                                </TableCell>

                                                {/* Type */}
                                                <TableCell className="py-4 text-center">
                                                    <span className="text-[10px] font-bold tracking-wide text-muted-foreground uppercase">
                                                        {tx.type}
                                                    </span>
                                                </TableCell>

                                                {/* Amount */}
                                                <TableCell
                                                    className={`py-4 text-right text-xs font-black ${
                                                        tx.direction === 'in'
                                                            ? 'text-emerald-600 dark:text-emerald-400'
                                                            : 'text-rose-600 dark:text-rose-400'
                                                    }`}
                                                >
                                                    {tx.direction === 'in'
                                                        ? '+'
                                                        : '-'}
                                                    {tx.amount}{' '}
                                                    {wallet.currency}
                                                </TableCell>

                                                {/* Balance Change */}
                                                <TableCell className="py-4 text-right text-[10px] font-semibold text-muted-foreground">
                                                    {tx.balance_before} ➔{' '}
                                                    {tx.balance_after}{' '}
                                                    {wallet.currency}
                                                </TableCell>

                                                {/* Status */}
                                                <TableCell className="py-4 text-center">
                                                    {tx.status ===
                                                    'completed' ? (
                                                        <Badge className="border border-emerald-500/20 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                            Completed
                                                        </Badge>
                                                    ) : tx.status ===
                                                      'pending' ? (
                                                        <Badge className="border border-amber-500/20 bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                                            Pending
                                                        </Badge>
                                                    ) : (
                                                        <Badge className="border border-rose-500/20 bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                                            {tx.status}
                                                        </Badge>
                                                    )}
                                                </TableCell>

                                                {/* Date */}
                                                <TableCell className="py-4 pr-6 text-right text-[10px] font-medium text-muted-foreground">
                                                    {tx.created_at
                                                        ? new Date(
                                                              tx.created_at,
                                                          ).toLocaleString()
                                                        : 'N/A'}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={8}
                                                className="py-16 text-center text-muted-foreground"
                                            >
                                                <div className="flex flex-col items-center justify-center gap-3">
                                                    <IconReceipt className="size-11 stroke-[1.5] text-muted-foreground/50" />
                                                    <div className="flex flex-col gap-0.5">
                                                        <p className="text-sm font-semibold text-foreground">
                                                            No transactions
                                                            settled
                                                        </p>
                                                        <p className="text-xs">
                                                            This wallet ledger
                                                            has zero settled
                                                            transition logs.
                                                        </p>
                                                    </div>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    )}

                    {/* Tab 2 Content: Withdrawal Requests Table */}
                    {activeTab === 'withdrawals' && (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader className="border-b bg-muted/5">
                                    <TableRow>
                                        <TableHead className="py-4 pl-6">
                                            Request Amount
                                        </TableHead>
                                        <TableHead className="py-4">
                                            Method
                                        </TableHead>
                                        <TableHead className="py-4">
                                            Details / Destination
                                        </TableHead>
                                        <TableHead className="py-4 text-center">
                                            Status
                                        </TableHead>
                                        <TableHead className="py-4">
                                            Auditor Note
                                        </TableHead>
                                        <TableHead className="py-4">
                                            Requested Date
                                        </TableHead>
                                        <TableHead className="w-[120px] py-4 pr-6 text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {withdrawals.length > 0 ? (
                                        withdrawals.map((wr) => (
                                            <TableRow
                                                key={wr.id}
                                                className="transition-colors hover:bg-muted/5"
                                            >
                                                {/* Amount */}
                                                <TableCell className="py-4 pl-6 text-xs font-black text-foreground">
                                                    {wr.amount}{' '}
                                                    {wallet.currency}
                                                </TableCell>

                                                {/* Method */}
                                                <TableCell className="py-4 text-[10px] font-extrabold text-indigo-600 uppercase dark:text-indigo-400">
                                                    {wr.method}
                                                </TableCell>

                                                {/* Details */}
                                                <TableCell className="py-4">
                                                    {formatPayoutDetails(
                                                        wr.payment_details,
                                                    )}
                                                </TableCell>

                                                {/* Status */}
                                                <TableCell className="py-4 text-center">
                                                    {getWithdrawalStatusBadge(
                                                        wr.status,
                                                    )}
                                                </TableCell>

                                                {/* Auditor Note */}
                                                <TableCell className="max-w-[200px] truncate py-4 text-xs text-muted-foreground">
                                                    {wr.admin_note || '-'}
                                                </TableCell>

                                                {/* Date */}
                                                <TableCell className="py-4 text-[10px] font-medium text-muted-foreground">
                                                    {wr.created_at
                                                        ? new Date(
                                                              wr.created_at,
                                                          ).toLocaleString()
                                                        : 'N/A'}
                                                </TableCell>

                                                {/* Action Edit */}
                                                <TableCell className="py-4 pr-6 text-right">
                                                    <Button
                                                        variant="outline"
                                                        size="xs"
                                                        className="h-7 px-2 font-bold"
                                                        onClick={() =>
                                                            openWithdrawalDialog(
                                                                wr,
                                                            )
                                                        }
                                                    >
                                                        <IconEdit className="mr-1 size-3.5" />
                                                        <span>Audit</span>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell
                                                colSpan={7}
                                                className="py-16 text-center text-muted-foreground"
                                            >
                                                <div className="flex flex-col items-center justify-center gap-3">
                                                    <IconCoin className="size-11 stroke-[1.5] text-muted-foreground/50" />
                                                    <div className="flex flex-col gap-0.5">
                                                        <p className="text-sm font-semibold text-foreground">
                                                            No withdrawals
                                                            logged
                                                        </p>
                                                        <p className="text-xs">
                                                            No payout withdrawal
                                                            requests exist for
                                                            this merchant
                                                            wallet.
                                                        </p>
                                                    </div>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </div>
            </div>

            {/* Withdrawal Request Audit Dialog */}
            <Dialog
                open={withdrawalDialogOpen}
                onOpenChange={setWithdrawalDialogOpen}
            >
                <DialogContent className="bg-card sm:max-w-md">
                    <form
                        onSubmit={handleWithdrawalSubmit}
                        className="flex flex-col gap-4"
                    >
                        <DialogHeader>
                            <DialogTitle className="text-lg font-black text-foreground">
                                Audit Payout request
                            </DialogTitle>
                            <DialogDescription className="text-xs text-muted-foreground">
                                Approve, decline, or mark payout requests as
                                settled once transfers complete.
                            </DialogDescription>
                        </DialogHeader>

                        {selectedWithdrawal && (
                            <div className="grid grid-cols-1 gap-4 py-2">
                                {/* Meta details */}
                                <div className="flex items-center justify-between rounded-xl border bg-muted/15 p-3.5">
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Amount Requested
                                        </span>
                                        <span className="text-sm font-black text-foreground">
                                            {selectedWithdrawal.amount}{' '}
                                            {wallet.currency}
                                        </span>
                                    </div>
                                    <div className="flex flex-col items-end gap-0.5">
                                        <span className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                            Method
                                        </span>
                                        <span className="text-xs font-black text-indigo-600 uppercase dark:text-indigo-400">
                                            {selectedWithdrawal.method}
                                        </span>
                                    </div>
                                </div>

                                {/* Status Dropdown */}
                                <div className="flex flex-col gap-1.5">
                                    <label className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                        Payout status
                                    </label>
                                    <Select
                                        value={String(
                                            withdrawalForm.data.status,
                                        )}
                                        onValueChange={(val) =>
                                            withdrawalForm.setData(
                                                'status',
                                                Number(val),
                                            )
                                        }
                                    >
                                        <SelectTrigger className="h-10 w-full bg-background">
                                            <SelectValue placeholder="Payout Status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="0">
                                                Pending Identity Check
                                            </SelectItem>
                                            <SelectItem value="1">
                                                Pending Approval
                                            </SelectItem>
                                            <SelectItem value="2">
                                                Approved (Queued for Payout)
                                            </SelectItem>
                                            <SelectItem value="3">
                                                Rejected (Refund Amount)
                                            </SelectItem>
                                            <SelectItem value="4">
                                                Paid (Settled Transfer)
                                            </SelectItem>
                                            <SelectItem value="5">
                                                Cancelled
                                            </SelectItem>
                                            <SelectItem value="6">
                                                Failed
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {withdrawalForm.errors.status && (
                                        <span className="text-xs font-semibold text-rose-500">
                                            {withdrawalForm.errors.status}
                                        </span>
                                    )}
                                </div>

                                {/* Admin Note */}
                                <div className="flex flex-col gap-1.5">
                                    <label className="text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                        Moderator Note / Remarks
                                    </label>
                                    <Input
                                        placeholder="Enter transfer reference number or rejection reason..."
                                        value={withdrawalForm.data.admin_note}
                                        onChange={(e) =>
                                            withdrawalForm.setData(
                                                'admin_note',
                                                e.target.value,
                                            )
                                        }
                                        className="h-10 bg-background text-xs"
                                    />
                                    {withdrawalForm.errors.admin_note && (
                                        <span className="text-xs font-semibold text-rose-500">
                                            {withdrawalForm.errors.admin_note}
                                        </span>
                                    )}
                                </div>
                            </div>
                        )}

                        <DialogFooter className="mt-2 border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setWithdrawalDialogOpen(false)}
                                disabled={withdrawalForm.processing}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={withdrawalForm.processing}
                                className="font-bold shadow-xs"
                            >
                                {withdrawalForm.processing
                                    ? 'Syncing...'
                                    : 'Sync Payout status'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

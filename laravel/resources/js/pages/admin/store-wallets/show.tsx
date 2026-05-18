import * as React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import ShowStoreWalletController from '@/actions/App/Http/Controllers/Admin/StoreWallets/ShowStoreWalletController';
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
  IconCalendar,
  IconCircleCheck,
  IconAlertTriangle,
  IconArrowsUpDown,
  IconCoin,
  IconReceipt,
  IconEdit,
  IconCheck,
  IconBuildingStore,
} from '@tabler/icons-react';

interface Store {
  id: number;
  store_name: string;
  phone_number: string;
  logo: string | null;
}

interface StoreWallet {
  id: number;
  store: Store | null;
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

interface StoreWalletShowProps {
  store_wallet: StoreWallet;
  transactions: Transaction[];
  withdrawals: Withdrawal[];
}

export default function StoreWalletShow({ store_wallet, transactions, withdrawals }: StoreWalletShowProps) {
  const [activeTab, setActiveTab] = React.useState<'transactions' | 'withdrawals'>('transactions');

  // Wallet Form
  const walletForm = useForm({
    status: store_wallet.status_raw,
    is_identity_verified: store_wallet.is_identity_verified,
  });

  // Withdrawal Edit State
  const [selectedWithdrawal, setSelectedWithdrawal] = React.useState<Withdrawal | null>(null);
  const [withdrawalDialogOpen, setWithdrawalDialogOpen] = React.useState(false);

  // Withdrawal status modifier form
  const withdrawalForm = useForm({
    status: 0,
    admin_note: '',
  });

  const handleWalletSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    walletForm.put(ShowStoreWalletController.update.url(store_wallet.id), {
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
      ShowStoreWalletController.updateWithdrawal.url({ store_wallet: store_wallet.id, storeWithdrawalRequest: selectedWithdrawal.id }),
      {
        onSuccess: () => {
          setWithdrawalDialogOpen(false);
        },
      }
    );
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'verified':
        return <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">Verified</Badge>;
      case 'pending':
        return <Badge className="bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-500/20 border dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30">Pending</Badge>;
      case 'blocked':
        return <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">Blocked</Badge>;
      case 'rejected':
        return <Badge className="bg-red-50 text-red-700 hover:bg-red-100 border-red-500/20 border dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30">Rejected</Badge>;
      default:
        return <Badge variant="outline" className="bg-slate-50 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200">New</Badge>;
    }
  };

  const getWithdrawalStatusBadge = (status: string) => {
    switch (status) {
      case 'paid':
        return <Badge className="bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-500/25">Paid</Badge>;
      case 'approved':
        return <Badge className="bg-cyan-50 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-400 border border-cyan-500/25">Approved</Badge>;
      case 'pending':
        return <Badge className="bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-500/25">Pending</Badge>;
      case 'pending_identity_check':
        return <Badge className="bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400 border border-sky-500/25">Identity Check</Badge>;
      case 'rejected':
        return <Badge className="bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-500/25">Rejected</Badge>;
      case 'cancelled':
        return <Badge className="bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-200 border border-slate-300">Cancelled</Badge>;
      default:
        return <Badge className="bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-500/25">Failed</Badge>;
    }
  };

  const formatPayoutDetails = (details: any) => {
    if (!details) return 'None';
    if (typeof details === 'string') return details;
    
    return (
      <div className="flex flex-col gap-0.5 text-[11px] text-muted-foreground">
        {Object.entries(details).map(([key, val]) => (
          <div key={key} className="flex gap-1">
            <span className="font-bold capitalize">{key.replace('_', ' ')}:</span>
            <span>{String(val)}</span>
          </div>
        ))}
      </div>
    );
  };

  return (
    <>
      <Head title={`Store Wallet details - ${store_wallet.store?.store_name || 'N/A'}`} />
      <div className="flex flex-col gap-6 p-4 lg:p-8">

        <div>
          <Link
            href="/admin/store-wallets"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
          >
            <IconArrowLeft className="size-4" />
            <span>Back to Store Wallets</span>
          </Link>
        </div>

        <div className="border-b pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
              Store Financial Auditor
            </h1>
            <p className="text-sm text-muted-foreground mt-1">
              Verify vendor balance operations, authorize payout requests, and freeze ledger limits.
            </p>
          </div>
        </div>

        {/* Top Panels: Overview & Moderator settings */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          <div className="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {/* Store Profile Card */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconBuildingStore className="size-5 text-primary shrink-0" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Store Account</h3>
              </div>

              <div className="flex items-center gap-4 py-2">
                <div className="size-14 rounded-xl bg-primary/10 border text-primary flex items-center justify-center font-black text-xl uppercase overflow-hidden shrink-0">
                  {store_wallet.store?.logo ? (
                    <img src={store_wallet.store.logo} alt={store_wallet.store.store_name} className="w-full h-full object-cover" />
                  ) : (
                    <IconBuildingStore className="size-8 stroke-[1.8]" />
                  )}
                </div>
                <div className="flex flex-col gap-0.5">
                  <span className="font-black text-foreground text-sm">{store_wallet.store?.store_name || 'N/A'}</span>
                  <span className="text-xs text-muted-foreground font-semibold">{store_wallet.store?.phone_number || 'N/A'}</span>
                </div>
              </div>
            </div>

            {/* Wallet Balance widget */}
            <div className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center gap-2 border-b pb-3.5">
                <IconWallet className="size-5 text-primary shrink-0" />
                <h3 className="font-extrabold text-sm uppercase tracking-wider text-muted-foreground">Ledger Balance</h3>
              </div>

              <div className="grid grid-cols-2 gap-4 py-1">
                <div className="flex flex-col">
                  <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Available Balance</span>
                  <span className="text-lg font-black text-foreground mt-0.5">{store_wallet.balance} {store_wallet.currency}</span>
                </div>
                <div className="flex flex-col">
                  <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Pending Balance</span>
                  <span className="text-lg font-black text-amber-600 dark:text-amber-400 mt-0.5">{store_wallet.pending_balance} {store_wallet.currency}</span>
                </div>
              </div>
            </div>

          </div>

          <div className="lg:col-span-4">
            <form onSubmit={handleWalletSubmit} className="bg-card border rounded-2xl p-5 shadow-xs flex flex-col gap-4">
              <div className="flex items-center justify-between border-b pb-3">
                <span className="font-extrabold text-xs uppercase tracking-wider text-muted-foreground">Wallet Moderator</span>
                {getStatusBadge(store_wallet.status)}
              </div>

              <div className="flex flex-col gap-1.5">
                <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Operational Status</label>
                <Select value={String(walletForm.data.status)} onValueChange={(val) => walletForm.setData('status', Number(val))}>
                  <SelectTrigger className="w-full h-9 bg-background">
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="0">New (Unverified)</SelectItem>
                    <SelectItem value="1">Pending Approval</SelectItem>
                    <SelectItem value="2">Verified (Active)</SelectItem>
                    <SelectItem value="3">Blocked (Frozen Funds)</SelectItem>
                    <SelectItem value="4">Rejected</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="flex items-center space-x-2 py-2">
                <Checkbox
                  id="identity_check"
                  checked={walletForm.data.is_identity_verified}
                  onCheckedChange={(checked) => walletForm.setData('is_identity_verified', Boolean(checked))}
                />
                <label
                  htmlFor="identity_check"
                  className="text-xs font-bold text-foreground leading-none cursor-pointer"
                >
                  Verify Business legal documents
                </label>
              </div>

              <Button type="submit" disabled={walletForm.processing} size="sm" className="w-full font-bold">
                {walletForm.processing ? 'Syncing...' : 'Sync Wallet Settings'}
              </Button>

              {walletForm.recentlySuccessful && (
                <div className="flex items-center gap-1.5 justify-center text-[10px] text-emerald-600 dark:text-emerald-400 font-bold py-1 bg-emerald-500/10 rounded-lg border border-emerald-500/20">
                  <IconCheck className="size-3.5 shrink-0" />
                  <span>Wallet settings synchronized</span>
                </div>
              )}
            </form>
          </div>

        </div>

        {/* Tabbed Ledgers / Withdrawals section */}
        <div className="bg-card border rounded-2xl shadow-xs overflow-hidden mt-6 flex flex-col">
          
          <div className="flex items-center border-b bg-muted/15 px-6">
            <button
              onClick={() => setActiveTab('transactions')}
              className={`py-4 px-4 text-xs uppercase tracking-wider font-extrabold border-b-2 transition-all outline-none ${
                activeTab === 'transactions'
                  ? 'border-primary text-foreground'
                  : 'border-transparent text-muted-foreground hover:text-foreground'
              }`}
            >
              <div className="flex items-center gap-1.5">
                <IconArrowsUpDown className="size-4" />
                <span>Transactions Ledger ({transactions.length})</span>
              </div>
            </button>

            <button
              onClick={() => setActiveTab('withdrawals')}
              className={`py-4 px-4 text-xs uppercase tracking-wider font-extrabold border-b-2 transition-all outline-none ${
                activeTab === 'withdrawals'
                  ? 'border-primary text-foreground'
                  : 'border-transparent text-muted-foreground hover:text-foreground'
              }`}
            >
              <div className="flex items-center gap-1.5">
                <IconCoin className="size-4" />
                <span>Store Withdrawal Requests ({withdrawals.length})</span>
              </div>
            </button>
          </div>

          {/* Tab 1 Content: Transactions Table */}
          {activeTab === 'transactions' && (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader className="bg-muted/5 border-b">
                  <TableRow>
                    <TableHead className="pl-6 py-4">Transaction Code / Ref</TableHead>
                    <TableHead className="py-4">Details</TableHead>
                    <TableHead className="py-4 text-center">Direction</TableHead>
                    <TableHead className="py-4 text-center">Type</TableHead>
                    <TableHead className="py-4 text-right">Amount</TableHead>
                    <TableHead className="py-4 text-right">Balance Change</TableHead>
                    <TableHead className="py-4 text-center">Status</TableHead>
                    <TableHead className="py-4 text-right pr-6">Settled Date</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {transactions.length > 0 ? (
                    transactions.map((tx) => (
                      <TableRow key={tx.id} className="hover:bg-muted/5 transition-colors">
                        
                        <TableCell className="pl-6 py-4 font-mono font-extrabold text-[11px] uppercase tracking-wider text-foreground">
                          {tx.reference || 'N/A'}
                        </TableCell>

                        <TableCell className="py-4 text-xs font-semibold text-foreground max-w-[200px] truncate">
                          {tx.title}
                        </TableCell>

                        <TableCell className="py-4 text-center">
                          {tx.direction === 'in' ? (
                            <span className="text-[10px] uppercase font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/15">
                              Credit (IN)
                            </span>
                          ) : (
                            <span className="text-[10px] uppercase font-bold text-rose-600 dark:text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-full border border-rose-500/15">
                              Debit (OUT)
                            </span>
                          )}
                        </TableCell>

                        <TableCell className="py-4 text-center">
                          <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">
                            {tx.type}
                          </span>
                        </TableCell>

                        <TableCell className={`py-4 text-right font-black text-xs ${
                          tx.direction === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'
                        }`}>
                          {tx.direction === 'in' ? '+' : '-'}{tx.amount} {store_wallet.currency}
                        </TableCell>

                        <TableCell className="py-4 text-right text-[10px] text-muted-foreground font-semibold">
                          {tx.balance_before} ➔ {tx.balance_after} {store_wallet.currency}
                        </TableCell>

                        <TableCell className="py-4 text-center">
                          {tx.status === 'completed' ? (
                            <Badge className="bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-500/20">Completed</Badge>
                          ) : tx.status === 'pending' ? (
                            <Badge className="bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-500/20">Pending</Badge>
                          ) : (
                            <Badge className="bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-500/20">{tx.status}</Badge>
                          )}
                        </TableCell>

                        <TableCell className="py-4 text-right pr-6 text-[10px] text-muted-foreground font-medium">
                          {tx.created_at ? new Date(tx.created_at).toLocaleString() : 'N/A'}
                        </TableCell>

                      </TableRow>
                    ))
                  ) : (
                    <TableRow>
                      <TableCell colSpan={8} className="py-16 text-center text-muted-foreground">
                        <div className="flex flex-col items-center justify-center gap-3">
                          <IconReceipt className="size-11 text-muted-foreground/50 stroke-[1.5]" />
                          <div className="flex flex-col gap-0.5">
                            <p className="font-semibold text-sm text-foreground">No transactions settled</p>
                            <p className="text-xs">This wallet ledger has zero settled transition logs.</p>
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
                <TableHeader className="bg-muted/5 border-b">
                  <TableRow>
                    <TableHead className="pl-6 py-4">Request Amount</TableHead>
                    <TableHead className="py-4">Method</TableHead>
                    <TableHead className="py-4">Details / Destination</TableHead>
                    <TableHead className="py-4 text-center">Status</TableHead>
                    <TableHead className="py-4">Auditor Note</TableHead>
                    <TableHead className="py-4">Requested Date</TableHead>
                    <TableHead className="py-4 text-right pr-6 w-[120px]">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {withdrawals.length > 0 ? (
                    withdrawals.map((wr) => (
                      <TableRow key={wr.id} className="hover:bg-muted/5 transition-colors">
                        
                        <TableCell className="pl-6 py-4 font-black text-foreground text-xs">
                          {wr.amount} {store_wallet.currency}
                        </TableCell>

                        <TableCell className="py-4 font-extrabold text-[10px] uppercase text-indigo-600 dark:text-indigo-400">
                          {wr.method}
                        </TableCell>

                        <TableCell className="py-4">
                          {formatPayoutDetails(wr.payment_details)}
                        </TableCell>

                        <TableCell className="py-4 text-center">
                          {getWithdrawalStatusBadge(wr.status)}
                        </TableCell>

                        <TableCell className="py-4 text-xs text-muted-foreground max-w-[200px] truncate">
                          {wr.admin_note || '-'}
                        </TableCell>

                        <TableCell className="py-4 text-[10px] text-muted-foreground font-medium">
                          {wr.created_at ? new Date(wr.created_at).toLocaleString() : 'N/A'}
                        </TableCell>

                        <TableCell className="py-4 text-right pr-6">
                          <Button
                            variant="outline"
                            size="xs"
                            className="h-7 px-2 font-bold"
                            onClick={() => openWithdrawalDialog(wr)}
                          >
                            <IconEdit className="size-3.5 mr-1" />
                            <span>Audit</span>
                          </Button>
                        </TableCell>

                      </TableRow>
                    ))
                  ) : (
                    <TableRow>
                      <TableCell colSpan={7} className="py-16 text-center text-muted-foreground">
                        <div className="flex flex-col items-center justify-center gap-3">
                          <IconCoin className="size-11 text-muted-foreground/50 stroke-[1.5]" />
                          <div className="flex flex-col gap-0.5">
                            <p className="font-semibold text-sm text-foreground">No withdrawals logged</p>
                            <p className="text-xs">No payout withdrawal requests exist for this store wallet.</p>
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
      <Dialog open={withdrawalDialogOpen} onOpenChange={setWithdrawalDialogOpen}>
        <DialogContent className="sm:max-w-md bg-card">
          <form onSubmit={handleWithdrawalSubmit} className="flex flex-col gap-4">
            <DialogHeader>
              <DialogTitle className="text-lg font-black text-foreground">Audit Payout Request</DialogTitle>
              <DialogDescription className="text-xs text-muted-foreground">
                Approve, decline, or mark payout requests as settled once transfers complete.
              </DialogDescription>
            </DialogHeader>

            {selectedWithdrawal && (
              <div className="grid grid-cols-1 gap-4 py-2">
                
                <div className="bg-muted/15 border p-3.5 rounded-xl flex items-center justify-between">
                  <div className="flex flex-col gap-0.5">
                    <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Amount Requested</span>
                    <span className="text-sm font-black text-foreground">{selectedWithdrawal.amount} {store_wallet.currency}</span>
                  </div>
                  <div className="flex flex-col items-end gap-0.5">
                    <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Method</span>
                    <span className="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase">{selectedWithdrawal.method}</span>
                  </div>
                </div>

                <div className="flex flex-col gap-1.5">
                  <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Payout Status</label>
                  <Select value={String(withdrawalForm.data.status)} onValueChange={(val) => withdrawalForm.setData('status', Number(val))}>
                    <SelectTrigger className="w-full h-10 bg-background">
                      <SelectValue placeholder="Payout Status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="0">Pending Identity Check</SelectItem>
                      <SelectItem value="1">Pending Approval</SelectItem>
                      <SelectItem value="2">Approved (Queued for Payout)</SelectItem>
                      <SelectItem value="3">Rejected (Refund Amount)</SelectItem>
                      <SelectItem value="4">Paid (Settled Transfer)</SelectItem>
                      <SelectItem value="5">Cancelled</SelectItem>
                      <SelectItem value="6">Failed</SelectItem>
                    </SelectContent>
                  </Select>
                  {withdrawalForm.errors.status && (
                    <span className="text-xs text-rose-500 font-semibold">{withdrawalForm.errors.status}</span>
                  )}
                </div>

                <div className="flex flex-col gap-1.5">
                  <label className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Moderator Note / Remarks</label>
                  <Input
                    placeholder="Enter transfer reference number or rejection reason..."
                    value={withdrawalForm.data.admin_note}
                    onChange={(e) => withdrawalForm.setData('admin_note', e.target.value)}
                    className="h-10 bg-background text-xs"
                  />
                  {withdrawalForm.errors.admin_note && (
                    <span className="text-xs text-rose-500 font-semibold">{withdrawalForm.errors.admin_note}</span>
                  )}
                </div>

              </div>
            )}

            <DialogFooter className="mt-2 border-t pt-4">
              <Button type="button" variant="outline" onClick={() => setWithdrawalDialogOpen(false)} disabled={withdrawalForm.processing}>
                Cancel
              </Button>
              <Button type="submit" disabled={withdrawalForm.processing} className="font-bold shadow-xs">
                {withdrawalForm.processing ? 'Syncing...' : 'Sync Payout Status'}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </>
  );
}

import * as React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import ListFriendshipsController from '@/actions/App/Http/Controllers/Admin/Friendships/ListFriendshipsController';
import ActionFriendshipController from '@/actions/App/Http/Controllers/Admin/Friendships/ActionFriendshipController';
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
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
  SheetFooter,
} from '@/components/ui/sheet';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  IconSearch,
  IconUsers,
  IconArrowRight,
  IconLoader2,
  IconRefresh,
  IconChevronLeft,
  IconChevronRight,
  IconHeart,
  IconMessageCircle,
  IconUser,
  IconCircleCheck,
  IconAlertTriangle,
  IconBan,
  IconTrash,
} from '@tabler/icons-react';

interface UserProfile {
  id: number;
  full_name: string;
  username: string;
  email: string;
  image: string | null;
}

interface Friendship {
  id: number;
  sender: UserProfile | null;
  receiver: UserProfile | null;
  status: string;
  status_raw: number;
  accepted_at: string | null;
  rejected_at: string | null;
  blocked_at: string | null;
  created_at: string;
}

interface ConversationStats {
  id: number;
  type: string;
  type_raw: number;
  messages_count: number;
  created_at: string;
  updated_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedFriendships {
  data: Friendship[];
  links: PaginationLink[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface ListFriendshipsProps {
  friendships: PaginatedFriendships;
  kpis: {
    total_count: number;
    accepted_count: number;
    pending_count: number;
    blocked_count: number;
  };
  filters: {
    search: string;
    status: string;
    per_page: number;
  };
}

export default function ListFriendships({ friendships, kpis, filters }: ListFriendshipsProps) {
  const [searchTerm, setSearchTerm] = React.useState(filters.search || '');
  const [statusFilter, setStatusFilter] = React.useState(filters.status || 'all');

  // Sheet Preview States
  const [previewOpen, setPreviewOpen] = React.useState(false);
  const [previewFriendship, setPreviewFriendship] = React.useState<Friendship | null>(null);
  const [conversationStats, setConversationStats] = React.useState<ConversationStats | null>(null);
  const [loadingDetails, setLoadingDetails] = React.useState(false);

  // Form hook for saving friendship status
  const { data, setData, put, processing, errors, clearErrors } = useForm({
    status: 0,
  });

  // Fetch individual friendship details (including conversation stats) for preview
  const handleOpenPreview = async (friendship: Friendship) => {
    setPreviewOpen(true);
    setLoadingDetails(true);
    setPreviewFriendship(friendship);
    setConversationStats(null);
    clearErrors();

    setData({
      status: friendship.status_raw,
    });

    try {
      const response = await fetch(ActionFriendshipController.show.url(friendship.id), {
        headers: { Accept: 'application/json' },
      });
      if (response.ok) {
        const result = await response.json();
        setPreviewFriendship(result.friendship);
        setConversationStats(result.conversation);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoadingDetails(false);
    }
  };

  const handleUpdateStatus = (e: React.FormEvent) => {
    e.preventDefault();
    if (!previewFriendship) return;

    put(ActionFriendshipController.update.url(previewFriendship.id), {
      preserveScroll: true,
      onSuccess: () => {
        setPreviewOpen(false);
      },
    });
  };

  const handleDeleteFriendship = (friendshipId: number) => {
    if (confirm('Are you sure you want to terminate this friendship relationship completely?')) {
      router.delete(ActionFriendshipController.destroy.url(friendshipId), {
        preserveScroll: true,
      });
    }
  };

  const applyFilters = () => {
    router.get(
      '/admin/friendships',
      {
        search: searchTerm || undefined,
        status: statusFilter !== 'all' ? statusFilter : undefined,
      },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  };

  React.useEffect(() => {
    const timer = setTimeout(() => {
      applyFilters();
    }, 450);
    return () => clearTimeout(timer);
  }, [searchTerm, statusFilter]);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'accepted':
        return <Badge className="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-500/20 border dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">Accepted</Badge>;
      case 'pending':
        return <Badge className="bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-500/20 border dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30">Pending</Badge>;
      case 'rejected':
        return <Badge className="bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-500/20 border dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30">Rejected</Badge>;
      case 'blacked':
      case 'blocked':
        return <Badge className="bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-300 border dark:bg-slate-800 dark:text-slate-200">Blocked</Badge>;
      default:
        return <Badge variant="outline">{status}</Badge>;
    }
  };

  return (
    <>
      <Head title="Friendships Management" />
      <div className="flex flex-col gap-6 p-4 lg:p-8">

        {/* Page Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-foreground">Friendships</h1>
            <p className="text-sm text-muted-foreground mt-1">
              Audit peer-to-peer user relationships, verify active direct message channels, and manage connection states.
            </p>
          </div>
          <Button variant="outline" size="sm" onClick={applyFilters} className="self-start md:self-auto gap-1">
            <IconRefresh className="size-4" />
            <span>Sync Directory</span>
          </Button>
        </div>

        {/* KPI Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          
          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
              <IconUsers className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Total Connections</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.total_count}</span>
            </div>
          </div>

          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
              <IconHeart className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Active Friends</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.accepted_count} Pairs</span>
            </div>
          </div>

          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
              <IconLoader2 className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Pending Requests</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.pending_count} Sent</span>
            </div>
          </div>

          <div className="bg-card border rounded-2xl p-5 shadow-xs flex items-center gap-4">
            <div className="size-11 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
              <IconBan className="size-6 stroke-[1.8]" />
            </div>
            <div className="flex flex-col">
              <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Blocked Pairs</span>
              <span className="text-xl font-black text-foreground mt-0.5">{kpis.blocked_count} Blocked</span>
            </div>
          </div>
        </div>

        {/* Filter bar and Table */}
        <div className="bg-card border rounded-xl shadow-xs overflow-hidden flex flex-col">
          
          <div className="p-4 border-b flex flex-col lg:flex-row items-center gap-4 bg-muted/15">
            <div className="relative w-full lg:max-w-xs shrink-0">
              <IconSearch className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search sender or receiver..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-9 h-10 w-full bg-background"
              />
            </div>

            <div className="flex flex-wrap items-center gap-3 w-full lg:justify-end">
              <div className="flex items-center gap-1.5 shrink-0">
                <span className="text-xs font-semibold text-muted-foreground">Status:</span>
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger className="w-[140px] h-9 bg-background">
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="0">Pending</SelectItem>
                    <SelectItem value="1">Accepted</SelectItem>
                    <SelectItem value="2">Rejected</SelectItem>
                    <SelectItem value="3">Blocked</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>

          {/* Table Container */}
          <div className="overflow-x-auto">
            <Table>
              <TableHeader className="bg-muted/5 border-b">
                <TableRow>
                  <TableHead className="pl-6 py-4">Sender User</TableHead>
                  <TableHead className="py-4 text-center">Direction</TableHead>
                  <TableHead className="py-4">Receiver User</TableHead>
                  <TableHead className="py-4 text-center">Status</TableHead>
                  <TableHead className="py-4">Initiated Date</TableHead>
                  <TableHead className="py-4 text-right pr-6 w-[200px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {friendships.data.length > 0 ? (
                  friendships.data.map((friendship) => (
                    <TableRow key={friendship.id} className="hover:bg-muted/5 group/row transition-colors">
                      
                      <TableCell className="pl-6 py-4">
                        <div className="flex items-center gap-3">
                          <div className="size-8 rounded-full bg-primary/10 border text-primary flex items-center justify-center font-bold text-xs overflow-hidden shrink-0">
                            {friendship.sender?.image ? (
                              <img src={friendship.sender.image} alt={friendship.sender.username} className="w-full h-full object-cover" />
                            ) : (
                              <IconUser className="size-4 stroke-[1.8]" />
                            )}
                          </div>
                          <div className="flex flex-col gap-0.5">
                            <span className="font-extrabold text-foreground text-xs">{friendship.sender?.full_name || 'N/A'}</span>
                            <span className="text-[10px] text-muted-foreground font-semibold">@{friendship.sender?.username || 'N/A'}</span>
                          </div>
                        </div>
                      </TableCell>

                      <TableCell className="py-4 text-center">
                        <span className="text-muted-foreground/60 font-bold text-xs">➔</span>
                      </TableCell>

                      <TableCell className="py-4">
                        <div className="flex items-center gap-3">
                          <div className="size-8 rounded-full bg-primary/10 border text-primary flex items-center justify-center font-bold text-xs overflow-hidden shrink-0">
                            {friendship.receiver?.image ? (
                              <img src={friendship.receiver.image} alt={friendship.receiver.username} className="w-full h-full object-cover" />
                            ) : (
                              <IconUser className="size-4 stroke-[1.8]" />
                            )}
                          </div>
                          <div className="flex flex-col gap-0.5">
                            <span className="font-extrabold text-foreground text-xs">{friendship.receiver?.full_name || 'N/A'}</span>
                            <span className="text-[10px] text-muted-foreground font-semibold">@{friendship.receiver?.username || 'N/A'}</span>
                          </div>
                        </div>
                      </TableCell>

                      <TableCell className="py-4 text-center">
                        {getStatusBadge(friendship.status)}
                      </TableCell>

                      <TableCell className="py-4 text-xs text-muted-foreground font-medium">
                        {friendship.created_at ? new Date(friendship.created_at).toLocaleString() : 'N/A'}
                      </TableCell>

                      <TableCell className="py-4 text-right pr-6">
                        <div className="flex items-center justify-end gap-2">
                          <Button
                            variant="outline"
                            size="xs"
                            className="h-7 px-2.5 font-bold"
                            onClick={() => handleOpenPreview(friendship)}
                          >
                            Manage
                          </Button>
                          <Button
                            variant="ghost"
                            size="xs"
                            className="h-7 px-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-500/10"
                            onClick={() => handleDeleteFriendship(friendship.id)}
                          >
                            <IconTrash className="size-3.5" />
                          </Button>
                        </div>
                      </TableCell>

                    </TableRow>
                  ))
                ) : (
                  <TableRow>
                    <TableCell colSpan={6} className="py-16 text-center text-muted-foreground">
                      <div className="flex flex-col items-center justify-center gap-3">
                        <IconUsers className="size-12 text-muted-foreground/50 stroke-[1.5]" />
                        <div className="flex flex-col gap-0.5">
                          <p className="font-semibold text-sm text-foreground">No friendships found</p>
                          <p className="text-xs">Adjust filter settings or try a different search phrase.</p>
                        </div>
                      </div>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>

          {/* Pagination */}
          {friendships.total > 0 && (
            <div className="p-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4 bg-muted/10">
              <span className="text-xs text-muted-foreground font-medium">
                Showing {friendships.from} to {friendships.to} of {friendships.total} friendships
              </span>

              <div className="flex items-center gap-1.5">
                {friendships.links.map((link, idx) => {
                  const isPrev = link.label.includes('Previous');
                  const isNext = link.label.includes('Next');

                  let label = link.label;
                  if (isPrev) label = 'Previous';
                  if (isNext) label = 'Next';

                  const isDisabled = !link.url;

                  return (
                    <Link
                      key={idx}
                      href={link.url || '#'}
                      disabled={isDisabled}
                      preserveScroll
                      preserveState
                      className={`
                        inline-flex h-8 items-center justify-center gap-1 rounded-md px-3 text-xs font-semibold transition-all outline-none 
                        ${isDisabled ? 'pointer-events-none opacity-50' : 'hover:bg-accent hover:text-accent-foreground'}
                        ${link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm' : 'border border-border bg-card text-foreground'}
                      `}
                    >
                      {isPrev && <IconChevronLeft className="size-3.5 -ml-0.5" />}
                      <span>{label}</span>
                      {isNext && <IconChevronRight className="size-3.5 -mr-0.5" />}
                    </Link>
                  );
                })}
              </div>
            </div>
          )}

        </div>
      </div>

      {/* Slide-over Friendship Manager Sheet */}
      <Sheet open={previewOpen} onOpenChange={setPreviewOpen}>
        <SheetContent className="sm:max-w-md flex flex-col h-full bg-card">
          <SheetHeader className="border-b pb-4">
            <SheetTitle className="text-lg font-black text-foreground">Friendship Auditor</SheetTitle>
            <SheetDescription className="text-xs text-muted-foreground">
              Review connections, verify direct message status, and change relationship states.
            </SheetDescription>
          </SheetHeader>

          {loadingDetails ? (
            <div className="flex-grow flex flex-col items-center justify-center py-16 gap-3">
              <IconLoader2 className="size-8 text-primary animate-spin" />
              <span className="text-xs text-muted-foreground font-semibold">Auditing connections...</span>
            </div>
          ) : previewFriendship ? (
            <form onSubmit={handleUpdateStatus} className="flex-grow flex flex-col justify-between">
              <div className="flex flex-col p-4 gap-6 overflow-y-auto">

                {/* Sender & Receiver Card */}
                <div className="flex flex-col gap-4 border p-4 rounded-2xl bg-muted/20">
                  <div className="flex items-center gap-3">
                    <div className="size-9 rounded-full bg-primary/10 border text-primary flex items-center justify-center font-bold text-sm overflow-hidden shrink-0">
                      {previewFriendship.sender?.image ? (
                        <img src={previewFriendship.sender.image} alt={previewFriendship.sender.username} className="w-full h-full object-cover" />
                      ) : (
                        <IconUser className="size-5 stroke-[1.8]" />
                      )}
                    </div>
                    <div className="flex flex-col gap-0.5">
                      <span className="text-[10px] font-bold text-primary uppercase tracking-wider">Sender (Initiator)</span>
                      <span className="font-extrabold text-foreground text-xs">{previewFriendship.sender?.full_name || 'N/A'}</span>
                      <span className="text-[10px] text-muted-foreground font-semibold">@{previewFriendship.sender?.username || 'N/A'}</span>
                    </div>
                  </div>

                  <div className="border-t border-dashed my-1 flex justify-center py-1">
                    <span className="text-muted-foreground/60 text-xs font-bold font-mono">➔ CONNECTED ➔</span>
                  </div>

                  <div className="flex items-center gap-3">
                    <div className="size-9 rounded-full bg-primary/10 border text-primary flex items-center justify-center font-bold text-sm overflow-hidden shrink-0">
                      {previewFriendship.receiver?.image ? (
                        <img src={previewFriendship.receiver.image} alt={previewFriendship.receiver.username} className="w-full h-full object-cover" />
                      ) : (
                        <IconUser className="size-5 stroke-[1.8]" />
                      )}
                    </div>
                    <div className="flex flex-col gap-0.5">
                      <span className="text-[10px] font-bold text-primary uppercase tracking-wider">Receiver</span>
                      <span className="font-extrabold text-foreground text-xs">{previewFriendship.receiver?.full_name || 'N/A'}</span>
                      <span className="text-[10px] text-muted-foreground font-semibold">@{previewFriendship.receiver?.username || 'N/A'}</span>
                    </div>
                  </div>
                </div>

                {/* Conversation Stats Panel */}
                <div className="border rounded-xl p-4 flex flex-col gap-3 bg-background/50">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Direct Message Audit</span>
                  
                  {conversationStats ? (
                    <div className="flex flex-col gap-2">
                      <div className="flex items-center justify-between border-b pb-2">
                        <span className="text-xs text-muted-foreground font-semibold">Conversation Channel:</span>
                        <div className="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 text-xs font-bold bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                          <IconCircleCheck className="size-3.5" />
                          <span>Active Channel</span>
                        </div>
                      </div>

                      <div className="flex items-center justify-between">
                        <span className="text-xs text-muted-foreground font-semibold">Total Exchanged Messages:</span>
                        <div className="inline-flex items-center gap-1 text-primary font-black text-xs">
                          <IconMessageCircle className="size-4 shrink-0" />
                          <span>{conversationStats.messages_count} Messages</span>
                        </div>
                      </div>
                    </div>
                  ) : (
                    <div className="flex items-center gap-2 p-2 bg-slate-500/5 rounded-lg border border-border">
                      <IconAlertTriangle className="size-5 text-amber-500 shrink-0" />
                      <div className="flex flex-col">
                        <span className="text-xs text-foreground font-extrabold">No Private Conversation</span>
                        <span className="text-[10px] text-muted-foreground">These two users have not exchanged direct messages.</span>
                      </div>
                    </div>
                  )}
                </div>

                {/* Moderator Dropdown */}
                <div className="flex flex-col gap-2">
                  <label className="text-xs font-bold text-muted-foreground uppercase tracking-wider">Relationship Status</label>
                  <Select value={String(data.status)} onValueChange={(val) => setData('status', Number(val))}>
                    <SelectTrigger className="w-full h-10 bg-background">
                      <SelectValue placeholder="Update status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="0">Pending Request</SelectItem>
                      <SelectItem value="1">Accepted (Friends)</SelectItem>
                      <SelectItem value="2">Rejected</SelectItem>
                      <SelectItem value="3">Blocked</SelectItem>
                    </SelectContent>
                  </Select>
                  {errors.status && <span className="text-xs text-rose-500 font-semibold">{errors.status}</span>}
                </div>

              </div>

              <SheetFooter className="border-t p-4 flex flex-col gap-2 bg-muted/10">
                <Button type="submit" disabled={processing} className="w-full font-bold shadow-xs">
                  {processing ? 'Saving...' : 'Sync Relationship'}
                </Button>
                
                <Button
                  type="button"
                  variant="outline"
                  className="w-full text-rose-600 hover:text-rose-700 hover:bg-rose-50 border-rose-500/20 font-bold"
                  onClick={() => {
                    setPreviewOpen(false);
                    handleDeleteFriendship(previewFriendship.id);
                  }}
                >
                  <IconTrash className="size-4 mr-1.5" />
                  <span>Delete Relationship Relation</span>
                </Button>
              </SheetFooter>
            </form>
          ) : null}
        </SheetContent>
      </Sheet>
    </>
  );
}

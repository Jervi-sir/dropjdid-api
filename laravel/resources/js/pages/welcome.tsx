import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';
import { IconArrowUpRight, IconInnerShadowTop } from '@tabler/icons-react';
import AdminDashboardController from '@/actions/App/Http/Controllers/Admin/Dashboard/AdminDashboardController';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;

    return (
        <div className="relative flex min-h-screen flex-col justify-between overflow-hidden bg-[#FAFAFA] font-sans text-[#18181B] transition-colors duration-500 selection:bg-indigo-500/10 dark:bg-[#09090B] dark:text-[#F4F4F5]">
            <Head title="Welcome to Dropjdid" />

            {/* Glowing background accent radial flare */}
            <div className="pointer-events-none absolute top-[-10%] left-[50%] h-[500px] w-[1000px] -translate-x-[50%] rounded-full bg-gradient-to-b from-indigo-500/5 via-violet-500/2 to-transparent blur-[120px]" />

            {/* Top Navigation */}
            <header className="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <div className="flex items-center gap-2">
                    <IconInnerShadowTop className="size-6 shrink-0 rotate-45 text-indigo-500" />
                    <span className="text-base font-extrabold tracking-wider text-foreground uppercase">
                        Dropjdid
                    </span>
                </div>

                <nav className="flex items-center gap-4">
                    {auth.user ? (
                        <Link
                            href={AdminDashboardController.url()}
                            className="text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-foreground transition-colors duration-250 flex items-center gap-1 bg-muted px-4 py-2 rounded-xl border hover:shadow-xs"
                        >
                            Dashboard <IconArrowUpRight className="size-3" />
                        </Link>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Link
                                href={login()}
                                className="rounded-xl px-4 py-2 text-xs font-bold tracking-wider text-muted-foreground uppercase transition-colors duration-250 hover:text-foreground"
                            >
                                Log In
                            </Link>
                            {canRegister && (
                                <Link
                                    href={register()}
                                    className="rounded-xl bg-foreground px-4 py-2 text-xs font-bold tracking-wider text-background uppercase shadow-xs transition-opacity duration-250 hover:opacity-90"
                                >
                                    Get Started
                                </Link>
                            )}
                        </div>
                    )}
                </nav>
            </header>

            {/* Hero Main Content */}
            <main className="relative z-10 flex-1 flex flex-col justify-center items-center px-6 py-12 text-center max-w-4xl mx-auto">
                <div className="flex flex-col gap-6 lg:gap-8 items-center">

                    {/* Minimalist Micro Tag */}
                    <div className="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-[10px] font-extrabold tracking-widest text-indigo-600 uppercase dark:text-indigo-400">
                        Peer Commerce Drop Engine
                    </div>

                    {/* Headline */}
                    <h1 className="bg-gradient-to-b from-foreground via-foreground/90 to-muted-foreground/60 bg-clip-text text-5xl leading-[0.9] font-black tracking-tight text-transparent select-none lg:text-8xl">
                        Drop jdid.
                    </h1>

                    {/* Subtitle description */}
                    <p className="max-w-xl text-sm leading-relaxed font-medium text-muted-foreground lg:text-lg">
                        A clean, minimalist platform designed for launching
                        exclusive drops, managing peer-to-peer relationships,
                        and processing instant merchant payouts.
                    </p>

                    {/* Action button */}
                    <div className="mt-4 flex items-center gap-3">
                        {auth.user ? (
                            <Link
                                href={AdminDashboardController.url()}
                                className="inline-flex items-center justify-center h-11 px-6 rounded-2xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-500 shadow-lg shadow-indigo-600/10 active:scale-[0.98] transition-all duration-200"
                            >
                                Enter Console
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-flex h-11 items-center justify-center rounded-2xl bg-foreground px-6 text-sm font-semibold text-background transition-all duration-200 hover:opacity-90 active:scale-[0.98]"
                                >
                                    Access Console
                                </Link>
                                {canRegister && (
                                    <Link
                                        href={register()}
                                        className="inline-flex h-11 items-center justify-center rounded-2xl border bg-muted px-6 text-sm font-semibold text-foreground transition-all duration-200 hover:bg-muted/80 active:scale-[0.98]"
                                    >
                                        Register Store
                                    </Link>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </main>

            {/* Footer */}
            <footer className="relative z-10 mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-4 border-t border-indigo-500/5 px-6 py-8 text-[10px] font-bold tracking-widest text-muted-foreground/80 uppercase sm:flex-row lg:px-8">
                <span>© {new Date().getFullYear()} Dropjdid Technologies.</span>
                <div className="flex items-center gap-6">
                    <span className="cursor-pointer transition-colors duration-250 hover:text-foreground">
                        Terms
                    </span>
                    <span className="cursor-pointer transition-colors duration-250 hover:text-foreground">
                        Privacy
                    </span>
                    <span className="cursor-pointer transition-colors duration-250 hover:text-foreground">
                        Contact
                    </span>
                </div>
            </footer>
        </div>
    );
}

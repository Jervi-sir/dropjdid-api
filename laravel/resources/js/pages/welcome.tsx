import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';
import { IconArrowUpRight, IconInnerShadowTop } from '@tabler/icons-react';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage().props;

    return (
        <div className="relative min-h-screen bg-[#FAFAFA] dark:bg-[#09090B] text-[#18181B] dark:text-[#F4F4F5] flex flex-col justify-between selection:bg-indigo-500/10 transition-colors duration-500 overflow-hidden font-sans">
            <Head title="Welcome to Dropjdid" />

            {/* Glowing background accent radial flare */}
            <div className="absolute top-[-10%] left-[50%] -translate-x-[50%] w-[1000px] h-[500px] bg-gradient-to-b from-indigo-500/5 via-violet-500/2 to-transparent rounded-full blur-[120px] pointer-events-none" />

            {/* Top Navigation */}
            <header className="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 lg:px-8 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <IconInnerShadowTop className="size-6 text-indigo-500 rotate-45 shrink-0" />
                    <span className="font-extrabold tracking-wider text-base uppercase text-foreground">
                        Dropjdid
                    </span>
                </div>

                <nav className="flex items-center gap-4">
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-foreground transition-colors duration-250 flex items-center gap-1 bg-muted px-4 py-2 rounded-xl border hover:shadow-xs"
                        >
                            Dashboard <IconArrowUpRight className="size-3" />
                        </Link>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Link
                                href={login()}
                                className="text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-foreground px-4 py-2 rounded-xl transition-colors duration-250"
                            >
                                Log In
                            </Link>
                            {canRegister && (
                                <Link
                                    href={register()}
                                    className="text-xs font-bold uppercase tracking-wider bg-foreground text-background hover:opacity-90 px-4 py-2 rounded-xl shadow-xs transition-opacity duration-250"
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
                    <div className="inline-flex items-center gap-1.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-extrabold uppercase tracking-widest px-3 py-1 rounded-full border border-indigo-500/20">
                        Peer Commerce Drop Engine
                    </div>

                    {/* Headline */}
                    <h1 className="text-5xl lg:text-8xl font-black tracking-tight leading-[0.9] text-transparent bg-clip-text bg-gradient-to-b from-foreground via-foreground/90 to-muted-foreground/60 select-none">
                        Drop jdid.
                    </h1>

                    {/* Subtitle description */}
                    <p className="text-sm lg:text-lg text-muted-foreground font-medium max-w-xl leading-relaxed">
                        A clean, minimalist platform designed for launching exclusive drops, managing peer-to-peer relationships, and processing instant merchant payouts.
                    </p>

                    {/* Action button */}
                    <div className="flex items-center gap-3 mt-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-flex items-center justify-center h-11 px-6 rounded-2xl bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-500 shadow-lg shadow-indigo-600/10 active:scale-[0.98] transition-all duration-200"
                            >
                                Enter Console
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-flex items-center justify-center h-11 px-6 rounded-2xl bg-foreground text-background font-semibold text-sm hover:opacity-90 active:scale-[0.98] transition-all duration-200"
                                >
                                    Access Console
                                </Link>
                                {canRegister && (
                                    <Link
                                        href={register()}
                                        className="inline-flex items-center justify-center h-11 px-6 rounded-2xl bg-muted border font-semibold text-sm text-foreground hover:bg-muted/80 active:scale-[0.98] transition-all duration-200"
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
            <footer className="relative z-10 w-full max-w-7xl mx-auto px-6 py-8 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-indigo-500/5 text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80">
                <span>© {new Date().getFullYear()} Dropjdid Technologies.</span>
                <div className="flex items-center gap-6">
                    <span className="hover:text-foreground transition-colors duration-250 cursor-pointer">Terms</span>
                    <span className="hover:text-foreground transition-colors duration-250 cursor-pointer">Privacy</span>
                    <span className="hover:text-foreground transition-colors duration-250 cursor-pointer">Contact</span>
                </div>
            </footer>
        </div>
    );
}

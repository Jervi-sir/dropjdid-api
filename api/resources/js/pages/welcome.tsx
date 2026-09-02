import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';

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
            <div className="pointer-events-none absolute top-[-10%] left-[50%] h-[500px] w-[1000px] animate-pulse-slow rounded-full bg-gradient-to-b from-indigo-500/8 via-violet-500/3 to-transparent blur-[120px]" />

            {/* Top Navigation */}
            <header className="relative z-10 mx-auto flex w-full max-w-7xl animate-fade-in items-center justify-between px-6 py-6 lg:px-8">
                <div className="flex items-center gap-2"></div>
            </header>

            {/* Hero Main Content */}
            <main className="relative z-10 mx-auto flex max-w-4xl flex-1 flex-col items-center justify-center px-6 py-12 text-center">
                <div className="flex flex-col items-center gap-6 lg:gap-8">
                    {/* Headline */}
                    <h1 className="animate-fade-in-up animate-text-shine bg-gradient-to-r from-[#FF0000] via-[#FF00B8] to-[#FF0000] bg-[length:200%_auto] bg-clip-text text-5xl leading-[0.9] font-black tracking-tight text-transparent select-none lg:text-8xl">
                        Drop jdid.
                    </h1>

                    {/* Mobile App Download Badges */}
                    <div className="flex flex-col items-center gap-4">
                        <span
                            className="animate-fade-in-up text-[10px] font-extrabold tracking-widest text-muted-foreground/60 uppercase"
                            style={{ animationDelay: '200ms' }}
                        >
                            Available on
                        </span>
                        <div className="flex flex-wrap items-center justify-center gap-3">
                            <a
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="group inline-flex h-12 animate-fade-in-up items-center gap-3 rounded-xl border border-neutral-200 bg-white px-4 text-neutral-900 transition-all duration-300 hover:border-indigo-500 hover:bg-neutral-50 hover:shadow-lg hover:shadow-indigo-500/5 active:scale-[0.98] dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50 dark:hover:border-indigo-400 dark:hover:bg-neutral-900"
                                style={{ animationDelay: '400ms' }}
                            >
                                <svg
                                    className="h-5 w-5 fill-current transition-transform duration-300 group-hover:scale-110"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-.96.04-2.13.64-2.82 1.45-.6.7-1.13 1.84-.99 2.94.1.08.2.12.31.12.87 0 1.95-.57 2.51-1.45z" />
                                </svg>
                                <div className="flex flex-col items-start text-left">
                                    <span className="text-[9px] leading-none font-bold tracking-tight text-neutral-500 uppercase dark:text-neutral-400">
                                        Download on the
                                    </span>
                                    <span className="text-sm leading-tight font-semibold tracking-tight">
                                        App Store
                                    </span>
                                </div>
                            </a>
                            <a
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="group inline-flex h-12 animate-fade-in-up items-center gap-3 rounded-xl border border-neutral-200 bg-white px-4 text-neutral-900 transition-all duration-300 hover:border-indigo-500 hover:bg-neutral-50 hover:shadow-lg hover:shadow-indigo-500/5 active:scale-[0.98] dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-50 dark:hover:border-indigo-400 dark:hover:bg-neutral-900"
                                style={{ animationDelay: '600ms' }}
                            >
                                <svg
                                    className="h-5 w-5 fill-current transition-transform duration-300 group-hover:scale-110"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M5 3.004c-.31.32-.48.86-.48 1.54v14.91c0 .68.17 1.22.48 1.54L5.07 21.1l8.94-8.94v-.32L5.07 2.91 5 3.004zM17.27 15.65l-3.26-3.26v-.78l3.26-3.26.07.04 3.73 2.12c1.06.6 1.06 1.59 0 2.19l-3.73 2.12-.07.03zM14.01 12l-8.94 8.94c.32.34.85.38 1.49.02l10.66-6.06L14.01 12zM14.01 12l3.22-3.22L6.56 2.72c-.64-.36-1.17-.32-1.49.02L14.01 12z" />
                                </svg>
                                <div className="flex flex-col items-start text-left">
                                    <span className="text-[9px] leading-none font-bold tracking-tight text-neutral-500 uppercase dark:text-neutral-400">
                                        Get it on
                                    </span>
                                    <span className="text-sm leading-tight font-semibold tracking-tight">
                                        Google Play
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            {/* Footer */}
            <footer
                className="relative z-10 mx-auto flex w-full max-w-7xl animate-fade-in flex-col items-center justify-between gap-4 border-t border-indigo-500/5 px-6 py-8 text-[10px] font-bold tracking-widest text-muted-foreground/80 uppercase sm:flex-row lg:px-8"
                style={{ animationDelay: '800ms' }}
            >
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
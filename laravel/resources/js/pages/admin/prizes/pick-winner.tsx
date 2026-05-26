import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    IconArrowLeft,
    IconTrophy,
    IconUsers,
    IconPlayerPlay,
    IconConfetti,
    IconSparkles,
} from '@tabler/icons-react';

interface Participant {
    joining_id: number;
    user_id: number;
    full_name: string;
    username: string;
    image: string | null;
}

interface Prize {
    id: number;
    title: string;
    image: string | null;
    description: string | null;
    status: string;
}

interface PickWinnerProps {
    prize: Prize;
    participants: Participant[];
    winner: Participant | null;
}

export default function PickWinner({ prize, participants, winner }: PickWinnerProps) {
    const [isSpinning, setIsSpinning] = React.useState(false);
    const [activeIndex, setActiveIndex] = React.useState<number | null>(null);
    const [cycleWinner, setCycleWinner] = React.useState<Participant | null>(null);
    const [showCelebration, setShowCelebration] = React.useState(!!winner);

    // Track state to handle animation slowdown
    const animationRef = React.useRef<number | null>(null);
    const targetWinnerRef = React.useRef<Participant | null>(null);

    React.useEffect(() => {
        if (winner) {
            targetWinnerRef.current = winner;
            setCycleWinner(winner);
            setShowCelebration(true);
        }
    }, [winner]);

    const runRaffleAnimation = (targetWinner: Participant) => {
        let speed = 50; // Milliseconds per cycle
        let currentIdx = 0;
        let elapsed = 0;

        const cycle = () => {
            currentIdx = (currentIdx + 1) % participants.length;
            setActiveIndex(currentIdx);
            elapsed += speed;

            // Slow down the spinning once we reach 2 seconds
            if (elapsed > 2000) {
                speed += 40;
            }

            // Once the speed is slow enough and we land on the actual winner, stop
            if (speed > 400 && participants[currentIdx].user_id === targetWinner.user_id) {
                setIsSpinning(false);
                setShowCelebration(true);
                if (animationRef.current) {
                    clearTimeout(animationRef.current);
                }
                return;
            }

            animationRef.current = window.setTimeout(cycle, speed);
        };

        cycle();
    };

    const handleStartDraw = () => {
        if (participants.length === 0) return;

        setIsSpinning(true);
        setShowCelebration(false);
        setActiveIndex(0);

        // Call the backend to pick the winner
        router.post(
            `/admin/prizes/${prize.id}/pick-winner`,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    // Check if a winner came back from the backend
                    const serverWinner = page.props.winner as Participant | null;
                    if (serverWinner) {
                        runRaffleAnimation(serverWinner);
                    } else {
                        setIsSpinning(false);
                    }
                },
                onError: () => {
                    setIsSpinning(false);
                },
            },
        );
    };

    React.useEffect(() => {
        return () => {
            if (animationRef.current) {
                clearTimeout(animationRef.current);
            }
        };
    }, []);

    return (
        <>
            <Head title={`Draw Winner: ${prize.title}`} />
            <div className="flex flex-col gap-6 p-4 lg:p-8 min-h-screen bg-slate-950 text-white">
                {/* Header */}
                <div className="flex flex-col gap-2">
                    <Link
                        href={`/admin/prizes/${prize.id}`}
                        className="inline-flex w-fit items-center gap-1 text-xs font-semibold text-slate-400 hover:text-white"
                    >
                        <IconArrowLeft className="size-3.5" />
                        <span>Back to prize details</span>
                    </Link>
                    <h1 className="text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        <IconTrophy className="size-8 text-amber-400 animate-bounce" />
                        <span>Raffle Room</span>
                    </h1>
                    <p className="text-sm text-slate-400">
                        Visual interactive drawing machine for prize campaign campaigns.
                    </p>
                </div>

                {/* Main Content Grid */}
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    {/* Visualizer Area */}
                    <div className="lg:col-span-2 flex flex-col items-center justify-center border border-slate-800 rounded-2xl bg-slate-900/60 p-8 relative overflow-hidden shadow-2xl min-h-[480px]">
                        {/* Background glowing gradients */}
                        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none" />
                        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none" />

                        {showCelebration && cycleWinner ? (
                            /* Winner announcement card */
                            <div className="flex flex-col items-center text-center gap-6 animate-scale-in relative z-10">
                                <div className="relative">
                                    <div className="absolute inset-0 bg-amber-500/30 rounded-full blur-xl animate-pulse" />
                                    <div className="relative size-32 rounded-full border-4 border-amber-400 overflow-hidden shadow-xl bg-slate-800">
                                        {cycleWinner.image ? (
                                            <img
                                                src={cycleWinner.image}
                                                alt={cycleWinner.full_name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <div className="h-full w-full flex items-center justify-center text-3xl font-black text-amber-400">
                                                {cycleWinner.full_name.charAt(0).toUpperCase()}
                                            </div>
                                        )}
                                    </div>
                                    <div className="absolute -bottom-2 right-2 bg-amber-400 text-slate-950 p-1.5 rounded-full shadow-lg">
                                        <IconTrophy className="size-5 stroke-[2.5]" />
                                    </div>
                                </div>

                                <div className="flex flex-col gap-1.5">
                                    <span className="text-xs font-bold text-amber-400 tracking-widest uppercase flex items-center justify-center gap-1">
                                        <IconSparkles className="size-4" />
                                        <span>Winner Drawn</span>
                                        <IconSparkles className="size-4" />
                                    </span>
                                    <h2 className="text-3xl font-black tracking-tight text-white">{cycleWinner.full_name}</h2>
                                    <p className="text-sm text-slate-400 font-mono">@{cycleWinner.username}</p>
                                </div>

                                <div className="flex items-center gap-2 mt-2">
                                    <Button
                                        variant="outline"
                                        className="border-slate-700 text-white hover:bg-slate-800"
                                        asChild
                                    >
                                        <Link href={`/admin/prizes/${prize.id}`}>
                                            View Campaign
                                        </Link>
                                    </Button>
                                    {participants.length > 0 && !winner && (
                                        <Button
                                            onClick={handleStartDraw}
                                            className="bg-indigo-600 hover:bg-indigo-500 text-white font-bold"
                                        >
                                            Draw Again
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ) : (
                            /* Live picker loop screen */
                            <div className="flex flex-col items-center justify-center gap-8 relative z-10 w-full">
                                {participants.length === 0 ? (
                                    <div className="flex flex-col items-center text-center gap-3">
                                        <IconUsers className="size-16 text-slate-600" />
                                        <p className="text-lg font-bold text-slate-400">No participants entered yet</p>
                                        <p className="text-sm text-slate-500 max-w-sm">
                                            This campaign currently has no enrolled eligible participants to pick a winner from.
                                        </p>
                                    </div>
                                ) : (
                                    <>
                                        {/* Board visualizer of participants */}
                                        <div className="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4 max-h-[280px] overflow-y-auto w-full p-4 border border-slate-800/80 rounded-xl bg-slate-950/40">
                                            {participants.map((p, idx) => {
                                                const isHighlighted = idx === activeIndex;
                                                return (
                                                    <div
                                                        key={p.joining_id}
                                                        className={`relative flex items-center justify-center size-12 rounded-full overflow-hidden border-2 transition-all duration-75 ${isHighlighted ? 'border-amber-400 scale-125 shadow-lg shadow-amber-400/20 z-10' : 'border-slate-800 opacity-40'}`}
                                                    >
                                                        {p.image ? (
                                                            <img
                                                                src={p.image}
                                                                alt={p.full_name}
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            <div className="h-full w-full bg-slate-800 text-xs font-bold flex items-center justify-center text-slate-400">
                                                                {p.full_name.charAt(0).toUpperCase()}
                                                            </div>
                                                        )}
                                                    </div>
                                                );
                                            })}
                                        </div>

                                        <div className="flex flex-col items-center gap-4">
                                            <Button
                                                onClick={handleStartDraw}
                                                disabled={isSpinning}
                                                size="lg"
                                                className="bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold px-8 py-6 rounded-full shadow-xl shadow-indigo-600/20 text-lg flex items-center gap-2 group"
                                            >
                                                <IconPlayerPlay className="size-5 fill-white group-hover:scale-110 transition-transform" />
                                                <span>{isSpinning ? 'Selecting...' : 'Start Raffle'}</span>
                                            </Button>
                                            <span className="text-xs text-slate-500 font-medium">
                                                Drawing from {participants.length} eligible participants.
                                            </span>
                                        </div>
                                    </>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Prize Info Card Sidebar */}
                    <div className="flex flex-col gap-4">
                        <Card className="border-slate-800 bg-slate-900 text-white p-6 flex flex-col gap-4">
                            <h3 className="text-sm font-bold tracking-wider text-slate-400 uppercase">Target Campaign</h3>
                            <div className="h-36 rounded-lg overflow-hidden border border-slate-800 bg-slate-950 flex items-center justify-center">
                                {prize.image ? (
                                    <img
                                        src={prize.image}
                                        alt={prize.title}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <IconTrophy className="size-8 text-slate-700" />
                                )}
                            </div>
                            <div className="flex flex-col gap-1">
                                <h4 className="font-bold text-lg text-white">{prize.title}</h4>
                                <p className="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                                    {prize.description || 'No description provided.'}
                                </p>
                            </div>
                            <div className="flex items-center gap-2 text-xs font-semibold text-slate-400">
                                <span>Status:</span>
                                <span className="capitalize text-white">{prize.status}</span>
                            </div>
                        </Card>

                        {/* Raffle instructions */}
                        <div className="border border-slate-800 bg-slate-900/40 rounded-xl p-6 text-sm text-slate-400 flex flex-col gap-3">
                            <h4 className="font-bold text-white text-xs uppercase tracking-wider">How it works</h4>
                            <ul className="list-decimal pl-4 flex flex-col gap-2 text-xs leading-relaxed">
                                <li>The drawing selects exactly one random winner from the list of joined users.</li>
                                <li>All status changes (winner/lost) are processed securely in a single database transaction.</li>
                                <li>The target prize status is automatically set to "ended".</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

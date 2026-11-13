import { useEffect, useState } from 'react';

const parts = (ms) => {
    const s = Math.max(0, Math.floor(ms / 1000));
    return {
        days: Math.floor(s / 86400),
        hours: Math.floor((s % 86400) / 3600),
        minutes: Math.floor((s % 3600) / 60),
        seconds: s % 60,
        expired: s === 0,
    };
};

// Replaces jquery.countdown. Pass an end date (string, number or Date).
export default function useCountdown(endAt) {
    const end = new Date(endAt).getTime();
    const [left, setLeft] = useState(() => parts(end - Date.now()));

    useEffect(() => {
        const id = setInterval(() => setLeft(parts(end - Date.now())), 1000);
        return () => clearInterval(id);
    }, [end]);

    return left;
}
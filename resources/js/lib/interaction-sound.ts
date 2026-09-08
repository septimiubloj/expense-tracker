let audioContext: AudioContext | undefined;

export function soundsEnabled(): boolean {
    try {
        return localStorage.getItem("interaction-sounds") === "on";
    } catch {
        return false;
    }
}

export function setSoundsEnabled(enabled: boolean): void {
    try {
        localStorage.setItem("interaction-sounds", enabled ? "on" : "off");
    } catch {
        // Sound is optional when browser storage is unavailable.
    }
}

export async function prepareInteractionAudio(): Promise<void> {
    if (!soundsEnabled() || typeof window === "undefined" || !window.AudioContext) return;

    try {
        audioContext ??= new AudioContext();
        await audioContext.resume();
    } catch {
        // Browsers may decline audio; visual feedback remains available.
    }
}

export async function playConfirmation(): Promise<void> {
    if (!soundsEnabled() || typeof window === "undefined" || !window.AudioContext) return;

    try {
        audioContext ??= new AudioContext();
        await audioContext.resume();
        if (audioContext.state !== "running") return;

        const start = audioContext.currentTime;
        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();
        oscillator.type = "sine";
        oscillator.frequency.setValueAtTime(660, start);
        oscillator.frequency.setValueAtTime(880, start + 0.07);
        gain.gain.setValueAtTime(0, start);
        gain.gain.linearRampToValueAtTime(0.035, start + 0.012);
        gain.gain.exponentialRampToValueAtTime(0.001, start + 0.19);
        oscillator.connect(gain);
        gain.connect(audioContext.destination);
        oscillator.start(start);
        oscillator.stop(start + 0.2);
        oscillator.onended = () => {
            oscillator.disconnect();
            gain.disconnect();
        };
    } catch {
        // An unavailable audio device must never interrupt saving.
    }
}

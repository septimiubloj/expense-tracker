import { Moon, Sun, Volume2, VolumeX } from "lucide-react";
import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { useAppearance } from "@/hooks/use-appearance";
import {
    playConfirmation,
    prepareInteractionAudio,
    setSoundsEnabled,
    soundsEnabled,
} from "@/lib/interaction-sound";

export function WorkspacePreferences() {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const [sound, setSound] = useState(soundsEnabled);

    useEffect(() => {
        const prepareAudio = () => {
            void prepareInteractionAudio();
        };
        document.addEventListener("pointerdown", prepareAudio);
        document.addEventListener("keydown", prepareAudio);
        return () => {
            document.removeEventListener("pointerdown", prepareAudio);
            document.removeEventListener("keydown", prepareAudio);
        };
    }, []);

    function toggleSound() {
        const enabled = !sound;
        setSoundsEnabled(enabled);
        setSound(enabled);
        if (enabled) void playConfirmation();
    }

    return (
        <div className="ml-auto flex items-center gap-1">
            <Button
                variant="ghost"
                size="icon"
                onClick={toggleSound}
                aria-label="Interaction sounds"
                aria-pressed={sound}
                title={sound ? "Turn sounds off" : "Turn sounds on"}
            >
                {sound ? <Volume2 /> : <VolumeX />}
            </Button>
            <Button
                variant="ghost"
                size="icon"
                onClick={() => updateAppearance(resolvedAppearance === "light" ? "dark" : "light")}
                aria-label={
                    resolvedAppearance === "light" ? "Use dark appearance" : "Use light appearance"
                }
            >
                {resolvedAppearance === "light" ? <Sun /> : <Moon />}
            </Button>
        </div>
    );
}

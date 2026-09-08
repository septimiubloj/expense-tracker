# Design foundation

Direction: a personal money notebook. Warm paper, generous margins, quiet navigation,
and tactile surfaces. Keep everyday ledger work dense enough to scan. No invented
balances, decorative charts, or placeholder financial data.

## Visual language

- Warm off-white canvas; white paper surfaces; forest-green primary actions.
- Sage, lilac, and apricot book covers distinguish spaces without implying financial
  meaning. All colors live in `resources/css/app.css`; dark mode remains available.
- Instrument Sans for controls and data. Serif italic is reserved for the workspace
  greeting. Amounts use tabular numerals and retain exact minor-unit formatting.
- Rounded paper panels, fine borders, subtle lift on book cards. Avoid heavy shadows.
- Navigation always names the current book. Workspace cards open transactions directly.
- Default to light for new preferences; retain an explicitly stored appearance.

## Interaction

- Use existing Radix primitives for focus management and keyboard interactions,
  Tailwind 4 for styling, Lucide for icons, and Sonner where toast feedback is used.
- CSS handles short 150–260 ms transitions. Respect `prefers-reduced-motion`.
  Introduce a motion library only when shared-layout or gesture interactions need it.
- Web Audio generates a quiet 200 ms confirmation after a successful transaction or
  transfer save. Sounds are off by default; the header toggle persists the preference
  locally and previews the sound. Never play sounds for errors, typing, or navigation.
- Audio failure must never interrupt saving. Audio is unlocked from user interaction;
  successful saves retain their existing visual feedback.
- Transaction search filters loaded entries by payee, account, category, memo,
  reference, or transfer. Preserve horizontal table scrolling on small screens.

## Next design layer

Inline editing, keyboard cell navigation, command search, and reusable ledger blocks
remain separate work. Preserve current working dialogs until inline editing has clear
save, cancel, validation, pending, and focus-restoration behavior.

## Review before extending

Review the home with zero, one, and several books; long names; narrow screens; light
and dark appearance; keyboard-only use; reduced motion; and muted/enabled audio.
The initial implementation has automated type, build, lint, and ownership checks;
visual layout and sound playback have not yet been verified in a browser.

References: [Radix accessibility](https://www.radix-ui.com/primitives/docs/overview/accessibility)
and [Web Audio best practices](https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API/Best_practices).

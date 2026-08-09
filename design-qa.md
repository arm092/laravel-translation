# Design QA

- Reference: selected Apricode translation-manager concept, adapted to the explicit requirement to remove the left sidebar.
- Desktop: passed at the browser default 1280 x 720 viewport. Top navigation, page heading, primary action, locale badge, and wide data surface align with the selected visual direction.
- Mobile: passed at 390 x 844. Navigation, heading actions, search, and selects wrap without page-level horizontal overflow; wide translation data scrolls inside its panel.
- Selects: passed. Each filter group contains one select and one custom caret; computed `appearance` and `background-image` are both `none`, so the native indicator is suppressed.
- Accessibility: passed for landmark structure, accessible select labels, visible focus-ring CSS, readable contrast, and responsive control sizing.
- Interaction: passed for manager navigation and the add-translation route. Browser console and warning logs were empty.
- Palette: passed. Source styles use the eight approved Apricode tokens and derived `color-mix()` states.
- Intentional deviation: the reference's left sidebar is replaced by a sticky graphite top navigation bar, as requested.

final result: passed

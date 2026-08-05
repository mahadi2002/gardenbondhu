# Font Licenses

All three self-hosted fonts are SIL Open Font License 1.1 — free for
commercial use, redistribution, and self-hosting without attribution being
legally required. Noted here anyway, same spirit as PHOTO_CREDITS.md.

| Font | Designer / foundry | License | Source |
|---|---|---|---|
| Noto Sans Bengali | Google, in collaboration with Ek Type | SIL OFL 1.1 | [Google Fonts](https://fonts.google.com/noto/specimen/Noto+Sans+Bengali) |
| Baloo Da 2 | Ek Type | SIL OFL 1.1 | [Google Fonts](https://fonts.google.com/specimen/Baloo+Da+2) |
| IBM Plex Mono | IBM, designed by Mike Abbink / Bold Monday | SIL OFL 1.1 | [Google Fonts](https://fonts.google.com/specimen/IBM+Plex+Mono) |

Files live in `public/assets/fonts/`, split by script subset (Bengali /
Latin) to match how the browser actually requests them — a visitor only
downloads the subset their page needs. Sourced once from Google Fonts'
CDN and self-hosted from here on; the app itself never calls out to Google
at runtime, which is the whole point (no CSP exception needed, no visitor
IP leaked to a third party on every page load).

One detail worth knowing if these ever get re-sourced: Noto Sans Bengali's
400 and 600 weight files are byte-identical at the version pulled — this
family didn't ship a distinct semibold static cut at the time, so anything
styled at font-weight 600 renders at the same visual weight as 400. Not a
bug, just how the font was built. Baloo Da 2 (headings) carries the actual
visual weight contrast instead.

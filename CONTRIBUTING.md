# Contributing

Thanks for taking a look at this. A few things worth knowing before you send
a PR.

## Before you write code

For anything bigger than a typo fix, open an issue first (or comment on an
existing one) describing what you want to change and why. Saves everyone
the disappointment of a big PR getting rejected because it went a direction
that didn't fit — plant-care content especially, see below.

## Setup

See the [README](README.md#running-it-locally) — PHP 8.2+, MySQL or
MariaDB, no `npm install`, no build step. If you can run `php -S`, you can
run this.

## Conventions

The short version is in [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) — read
it, it's not long. The two rules that matter most:

- **No Composer packages.** If you're reaching for one, check whether PHP's
  standard library already does it — it usually does. `app/Support/Totp.php`
  (RFC 6238 on nothing but `hash_hmac`) is the kind of thing we mean.
- **Every DB call is a prepared statement, every echoed variable goes
  through `e()`.** No exceptions, no "just this once."

## Tests

`php tests/smoke.php` — no framework, just a `check()` helper. If you touch
`Crypto`, `Csrf`, `Validator`, `Operators`, `Totp`, a Repository's
teaser/full column split, or `SubscriptionService`, add a check for it in
the same PR. CI runs `php -l` across every file and `php tests/smoke.php`
against a real MySQL service on every push — see
[`.github/workflows/ci.yml`](.github/workflows/ci.yml).

## Plant/problem/guide content

`database/seeds/content.php` is a plain PHP array — copy the shape of an
existing entry. Write it in actual conversational Bangla, second person
(আপনি), like you're explaining it to someone standing in their own garden —
not a literal translation of an English gardening article. Native speakers
can tell immediately when it's not, so if Bangla isn't your first language,
say so in the PR and expect a native-speaker review pass before it merges
(see TODO.md — this applies to the existing content too, not just new PRs).

Do not touch a `toxic_to_pets` value without a source you'd be comfortable
citing — a few of them (marigold, tuberose, neem, eggplant) are flagged in
TODO.md as genuinely unresolved, not just unwritten. Getting this wrong
isn't a typo, it's a safety claim someone might act on.

## Security

Found something that looks like an actual vulnerability rather than a bug?
Please don't open a public issue for it — see
[`docs/SECURITY.md`](docs/SECURITY.md) for what's already protected and why,
and reach out privately first so it can be fixed before it's public.

## Pull requests

- Keep them focused — one concern per PR is easier to review and easier to
  revert if it turns out to be wrong.
- Explain the *why* in the description, not just the *what*. The codebase
  has a lot of comments explaining why something non-obvious is done a
  particular way; PRs should carry the same habit.
- If it's a behavior change, not just a refactor, say what you tested it
  against (which flow, which browser, which edge case) — there's no CI
  browser suite here to catch UI regressions for you.
